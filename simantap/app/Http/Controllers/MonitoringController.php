<?php

namespace App\Http\Controllers;

use App\Models\MonitoringFoto;
use App\Models\PemesananHarian;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class MonitoringController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = MonitoringFoto::with('penerimaan.taruna')
                ->when($request->tanggal, fn ($q, $d) => $q->whereHas('penerimaan', fn ($pq) => $pq->where('tanggal', $d)));

            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->addColumn('tanggal', fn ($m) => $m->penerimaan?->tanggal?->format('d/m/Y'))
                ->addColumn('taruna', fn ($m) => $m->penerimaan?->taruna?->nama)
                ->addColumn('foto_preview', fn ($m) => '<img src="' . Storage::url($m->file_path) . '" style="height:40px;border-radius:4px;" onerror="this.src=\'\'">')
                ->addColumn('captured_at', fn ($m) => $m->captured_at?->format('d/m/Y H:i'))
                ->addColumn('action', fn ($m) => $this->actionButtons($m))
                ->rawColumns(['foto_preview', 'action'])
                ->make(true);
        }

        return view('monitoring.index', ['tanggal' => request('tanggal', today()->format('Y-m-d'))]);
    }

    public function create(): View
    {
        $pemesananList = PemesananHarian::orderByDesc('tanggal')->take(30)->get();
        return view('monitoring.form', ['monitoring' => null, 'pemesananList' => $pemesananList]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'penerimaan_id' => 'required|exists:penerimaan_makan,id',
            'foto'          => 'required|array|min:1|max:5',
            'foto.*'        => 'required|image|max:5120',
            'lat'           => 'nullable|numeric|between:-90,90',
            'long'          => 'nullable|numeric|between:-180,180',
        ]);

        // Check max 5 photos per penerimaan
        $existing = MonitoringFoto::where('penerimaan_id', $request->penerimaan_id)->count();
        $newCount  = count($request->file('foto'));

        if ($existing + $newCount > 5) {
            return back()->withErrors(['foto' => 'Maksimal 5 foto per penerimaan. Sudah ada ' . $existing . ' foto.']);
        }

        foreach ($request->file('foto') as $i => $foto) {
            $path = $foto->store('monitoring', 'public');
            MonitoringFoto::create([
                'penerimaan_id' => $request->penerimaan_id,
                'file_path'     => $path,
                'urutan'        => $existing + $i + 1,
                'lat'           => $request->lat,
                'long'          => $request->long,
                'captured_at'   => now(),
            ]);
        }

        return redirect()->route('monitoring.index')->with('success', 'Foto monitoring berhasil diunggah.');
    }

    public function show(MonitoringFoto $monitoring): View
    {
        $monitoring->load('penerimaan.taruna');
        return view('monitoring.show', compact('monitoring'));
    }

    public function edit(MonitoringFoto $monitoring): View
    {
        return view('monitoring.show', compact('monitoring'));
    }

    public function update(Request $r, MonitoringFoto $monitoring): RedirectResponse
    {
        return back();
    }

    public function destroy(MonitoringFoto $monitoring): RedirectResponse
    {
        Storage::disk('public')->delete($monitoring->file_path);
        $monitoring->delete();
        return redirect()->route('monitoring.index')->with('success', 'Foto monitoring dihapus.');
    }

    private function actionButtons(MonitoringFoto $m): string
    {
        $del = '<form id="del-mf-' . $m->id . '" method="POST" action="' . route('monitoring.destroy', $m) . '" class="d-inline">'
            . csrf_field() . method_field('DELETE')
            . '<button type="button" class="btn btn-sm btn-outline-danger" onclick="konfirmasiHapus(\'del-mf-' . $m->id . '\', \'foto ini\')">'
            . '<i class="bi bi-trash"></i></button></form>';

        return '<a href="' . route('monitoring.show', $m) . '" class="btn btn-sm btn-outline-info me-1"><i class="bi bi-eye"></i></a>'
            . $del;
    }
}
