<?php

namespace App\Http\Controllers;

use App\Models\LaporanMontev;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class MonteVController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = LaporanMontev::with('user')
                ->when($request->tahun, fn ($q, $t) => $q->byTahun((int) $t));

            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->addColumn('periode', fn ($m) => $m->nama_bulan . ' ' . $m->periode_tahun)
                ->addColumn('oleh', fn ($m) => $m->user?->name)
                ->addColumn('action', fn ($m) => $this->actionButtons($m))
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('montev.index', ['tahun' => request('tahun', now()->year)]);
    }

    public function create(): View
    {
        return view('montev.form', ['montev' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'periode_bulan'    => 'required|integer|between:1,12',
            'periode_tahun'    => 'required|integer|min:2020|max:2099',
            'menu_dievaluasi'  => 'required|string|max:2000',
            'nilai_gizi_rata'  => 'nullable|numeric|between:0,100',
            'catatan_prosedur' => 'nullable|string|max:2000',
            'hasil_evaluasi'   => 'required|string|max:5000',
        ]);

        $data['user_id'] = auth()->id();

        LaporanMontev::create($data);

        return redirect()->route('montev.index')->with('success', 'Laporan monev berhasil disimpan.');
    }

    public function show(LaporanMontev $montev): View
    {
        $montev->load('user');
        return view('montev.show', compact('montev'));
    }

    public function edit(LaporanMontev $montev): View
    {
        return view('montev.form', compact('montev'));
    }

    public function update(Request $request, LaporanMontev $montev): RedirectResponse
    {
        $data = $request->validate([
            'periode_bulan'    => 'required|integer|between:1,12',
            'periode_tahun'    => 'required|integer|min:2020|max:2099',
            'menu_dievaluasi'  => 'required|string|max:2000',
            'nilai_gizi_rata'  => 'nullable|numeric|between:0,100',
            'catatan_prosedur' => 'nullable|string|max:2000',
            'hasil_evaluasi'   => 'required|string|max:5000',
        ]);

        $montev->update($data);

        return redirect()->route('montev.show', $montev)->with('success', 'Laporan monev berhasil diperbarui.');
    }

    public function destroy(LaporanMontev $montev): RedirectResponse
    {
        $montev->delete();
        return redirect()->route('montev.index')->with('success', 'Laporan monev dihapus.');
    }

    private function actionButtons(LaporanMontev $m): string
    {
        $del = '<form id="del-mv-' . $m->id . '" method="POST" action="' . route('montev.destroy', $m) . '" class="d-inline">'
            . csrf_field() . method_field('DELETE')
            . '<button type="button" class="btn btn-sm btn-outline-danger" onclick="konfirmasiHapus(\'del-mv-' . $m->id . '\', \'laporan ini\')">'
            . '<i class="bi bi-trash"></i></button></form>';

        return '<a href="' . route('montev.show', $m) . '" class="btn btn-sm btn-outline-info me-1"><i class="bi bi-eye"></i></a>'
            . '<a href="' . route('montev.edit', $m) . '" class="btn btn-sm btn-outline-warning me-1"><i class="bi bi-pencil"></i></a>'
            . $del;
    }
}
