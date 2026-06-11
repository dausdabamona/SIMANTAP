<?php

namespace App\Http\Controllers;

use App\Models\PaguAnggaran;
use App\Models\PengajuanPembayaran;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class PaguAnggaranController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return DataTables::eloquent(PaguAnggaran::query())
                ->addIndexColumn()
                ->addColumn('nilai_fmt', fn ($p) => 'Rp ' . number_format($p->nilai_pagu, 0, ',', '.'))
                ->addColumn('realisasi_fmt', fn ($p) => 'Rp ' . number_format($this->hitungRealisasi($p), 0, ',', '.'))
                ->addColumn('persentase', fn ($p) => round($this->hitungRealisasi($p) / max($p->nilai_pagu, 1) * 100, 1) . '%')
                ->addColumn('action', fn ($p) => $this->actionButtons($p))
                ->rawColumns(['action'])
                ->make(true);
        }

        $tahunSekarang = now()->year;
        $totalPagu     = PaguAnggaran::byTahun($tahunSekarang)->sum('nilai_pagu');
        $totalRealisasi= PengajuanPembayaran::where('status', '!=', 'draft')
            ->whereYear('created_at', $tahunSekarang)
            ->sum('total_nilai');

        return view('pagu.index', compact('totalPagu', 'totalRealisasi', 'tahunSekarang'));
    }

    public function create(): View
    {
        return view('pagu.form', ['pagu' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'tahun'        => 'required|integer|min:2020|max:2100',
            'akun_belanja' => 'required|string|max:100',
            'nilai_pagu'   => 'required|numeric|min:0',
            'keterangan'   => 'nullable|string|max:500',
        ]);

        PaguAnggaran::create($data);
        return redirect()->route('pagu.index')->with('success', 'Pagu anggaran berhasil ditambahkan.');
    }

    public function show(PaguAnggaran $pagu): View
    {
        $realisasi   = $this->hitungRealisasi($pagu);
        $persentase  = round($realisasi / max($pagu->nilai_pagu, 1) * 100, 1);
        return view('pagu.show', compact('pagu', 'realisasi', 'persentase'));
    }

    public function edit(PaguAnggaran $pagu): View
    {
        return view('pagu.form', compact('pagu'));
    }

    public function update(Request $request, PaguAnggaran $pagu): RedirectResponse
    {
        $data = $request->validate([
            'tahun'        => 'required|integer|min:2020|max:2100',
            'akun_belanja' => 'required|string|max:100',
            'nilai_pagu'   => 'required|numeric|min:0',
            'keterangan'   => 'nullable|string|max:500',
        ]);
        $pagu->update($data);
        return redirect()->route('pagu.index')->with('success', 'Pagu anggaran berhasil diperbarui.');
    }

    public function destroy(PaguAnggaran $pagu): RedirectResponse
    {
        $pagu->delete();
        return redirect()->route('pagu.index')->with('success', 'Pagu anggaran berhasil dihapus.');
    }

    private function hitungRealisasi(PaguAnggaran $pagu): float
    {
        return (float) PengajuanPembayaran::where('status', '!=', 'draft')
            ->whereYear('created_at', $pagu->tahun)
            ->sum('total_nilai');
    }

    private function actionButtons(PaguAnggaran $p): string
    {
        $del = '<form id="del-pg-' . $p->id . '" method="POST" action="' . route('pagu.destroy', $p) . '" class="d-inline">'
            . csrf_field() . method_field('DELETE')
            . '<button type="button" class="btn btn-sm btn-outline-danger" onclick="konfirmasiHapus(\'del-pg-' . $p->id . '\', \'pagu anggaran ' . $p->tahun . '\')">'
            . '<i class="bi bi-trash"></i></button></form>';

        return '<a href="' . route('pagu.show', $p) . '" class="btn btn-sm btn-outline-info me-1"><i class="bi bi-eye"></i></a>'
            . '<a href="' . route('pagu.edit', $p) . '" class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-pencil"></i></a>'
            . $del;
    }
}
