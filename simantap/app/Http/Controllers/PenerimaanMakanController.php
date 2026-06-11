<?php

namespace App\Http\Controllers;

use App\Models\KegiatanLuarKampus;
use App\Models\PenerimaanMakan;
use App\Models\Taruna;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class PenerimaanMakanController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = PenerimaanMakan::with('taruna')
                ->when($request->tanggal, fn ($q, $d) => $q->where('tanggal', $d))
                ->when($request->jenis_makan, fn ($q, $j) => $q->where('jenis_makan', $j));

            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->addColumn('nit', fn ($p) => $p->taruna?->nit)
                ->addColumn('nama_taruna', fn ($p) => $p->taruna?->nama)
                ->addColumn('eligibilitas_badge', fn ($p) => $this->eligibilitasBadge($p))
                ->addColumn('action', fn ($p) => $this->actionButtons($p))
                ->rawColumns(['eligibilitas_badge', 'action'])
                ->make(true);
        }

        return view('penerimaan.index', [
            'tanggal' => request('tanggal', today()->format('Y-m-d')),
        ]);
    }

    public function create(): View
    {
        $tanggal = request('tanggal', today()->format('Y-m-d'));

        // Taruna sedang PKL aktif pada tanggal ini → tidak eligible makan dalam kampus
        $tarunaLuarKampus = $this->tarunaAktifLuarKampus($tanggal);

        $tarunaEligible = Taruna::eligibleBantuan()
            ->when($tarunaLuarKampus->isNotEmpty(), fn ($q) => $q->whereNotIn('id', $tarunaLuarKampus))
            ->orderBy('nama')
            ->get();

        return view('penerimaan.form', [
            'penerimaan'   => null,
            'tarunaList'   => $tarunaEligible,
        ]);
    }

    private function tarunaAktifLuarKampus(string $tanggal): \Illuminate\Support\Collection
    {
        return \App\Models\PesertaKegiatanLuarKampus::whereHas('kegiatan', function ($q) use ($tanggal) {
            $q->whereIn('status', [
                KegiatanLuarKampus::STATUS_DISETUJUI_PUSDIK,
                KegiatanLuarKampus::STATUS_PROSES_PEMBAYARAN,
            ])
            ->whereDate('tanggal_mulai', '<=', $tanggal)
            ->whereDate('tanggal_selesai', '>=', $tanggal);
        })->pluck('taruna_id');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);

        if ($request->hasFile('file_lampiran_pengecualian')) {
            $data['file_lampiran_pengecualian'] = $request->file('file_lampiran_pengecualian')
                ->store('penerimaan/lampiran', 'public');
        }

        PenerimaanMakan::create($data);

        return redirect()->route('penerimaan.index')->with('success', 'Penerimaan makan berhasil dicatat.');
    }

    public function show(PenerimaanMakan $penerimaan): View
    {
        $penerimaan->load(['taruna', 'foto']);
        return view('penerimaan.show', compact('penerimaan'));
    }

    public function edit(PenerimaanMakan $penerimaan): View
    {
        $tarunaList = Taruna::orderBy('nama')->get();
        return view('penerimaan.form', compact('penerimaan', 'tarunaList'));
    }

    public function update(Request $request, PenerimaanMakan $penerimaan): RedirectResponse
    {
        $data = $this->validatedData($request, $penerimaan->id);
        if ($request->hasFile('file_lampiran_pengecualian')) {
            if ($penerimaan->file_lampiran_pengecualian) {
                Storage::disk('public')->delete($penerimaan->file_lampiran_pengecualian);
            }
            $data['file_lampiran_pengecualian'] = $request->file('file_lampiran_pengecualian')
                ->store('penerimaan/lampiran', 'public');
        }
        $penerimaan->update($data);
        return redirect()->route('penerimaan.index')->with('success', 'Data penerimaan berhasil diperbarui.');
    }

    public function destroy(PenerimaanMakan $penerimaan): RedirectResponse
    {
        if ($penerimaan->file_lampiran_pengecualian) {
            Storage::disk('public')->delete($penerimaan->file_lampiran_pengecualian);
        }
        $penerimaan->delete();
        return redirect()->route('penerimaan.index')->with('success', 'Data penerimaan berhasil dihapus.');
    }

    private function validatedData(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'tanggal'                    => 'required|date',
            'taruna_id'                  => 'required|exists:taruna,id',
            'jenis_makan'                => 'required|in:sarapan,makan_siang,makan_malam',
            'jumlah_porsi_diterima'      => 'required|integer|min:0|max:10',
            'status_eligibilitas'        => 'required|in:dapat,tidak_dapat',
            'alasan_pengecualian'        => 'nullable|string|max:500',
            'file_lampiran_pengecualian' => 'nullable|mimes:pdf,jpg,jpeg,png|max:5120',
            'lat'                        => 'nullable|numeric|between:-90,90',
            'long'                       => 'nullable|numeric|between:-180,180',
        ]);
    }

    private function eligibilitasBadge(PenerimaanMakan $p): string
    {
        return $p->status_eligibilitas === 'dapat'
            ? '<span class="badge bg-success">Dapat</span>'
            : '<span class="badge bg-danger">Tidak Dapat</span>';
    }

    private function actionButtons(PenerimaanMakan $p): string
    {
        $del = '<form id="del-pen-' . $p->id . '" method="POST" action="' . route('penerimaan.destroy', $p) . '" class="d-inline">'
            . csrf_field() . method_field('DELETE')
            . '<button type="button" class="btn btn-sm btn-outline-danger" onclick="konfirmasiHapus(\'del-pen-' . $p->id . '\', \'data penerimaan\')">'
            . '<i class="bi bi-trash"></i></button></form>';

        return '<a href="' . route('penerimaan.show', $p) . '" class="btn btn-sm btn-outline-info me-1"><i class="bi bi-eye"></i></a>'
            . '<a href="' . route('penerimaan.edit', $p) . '" class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-pencil"></i></a>'
            . $del;
    }
}
