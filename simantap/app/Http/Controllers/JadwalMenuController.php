<?php

namespace App\Http\Controllers;

use App\Models\JadwalMenu;
use App\Models\KontrakMakan;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class JadwalMenuController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = JadwalMenu::with('kontrak')
                ->when($request->kontrak_id, fn ($q, $id) => $q->where('kontrak_id', $id))
                ->when($request->tanggal, fn ($q, $d) => $q->where('tanggal', $d));

            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->addColumn('kontrak_nomor', fn ($j) => $j->kontrak?->nomor_kontrak)
                ->addColumn('jenis_label', fn ($j) => $j->jenis_makan_label)
                ->addColumn('action', fn ($j) => $this->actionButtons($j))
                ->rawColumns(['action'])
                ->make(true);
        }

        $kontrakList = KontrakMakan::where('status', 'aktif')->orderByDesc('tanggal_kontrak')->get();
        return view('jadwal-menu.index', compact('kontrakList'));
    }

    public function create(): View
    {
        $kontrakList = KontrakMakan::where('status', 'aktif')->orderByDesc('tanggal_kontrak')->get();
        return view('jadwal-menu.form', ['jadwal' => null, 'kontrakList' => $kontrakList]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        $data['created_by'] = auth()->id();
        JadwalMenu::create($data);
        return redirect()->route('jadwal-menu.index')->with('success', 'Jadwal menu berhasil disimpan.');
    }

    public function show(JadwalMenu $jadwalMenu): View
    {
        $jadwalMenu->load('kontrak');
        return view('jadwal-menu.show', ['jadwal' => $jadwalMenu]);
    }

    public function edit(JadwalMenu $jadwalMenu): View
    {
        $kontrakList = KontrakMakan::where('status', 'aktif')->orderByDesc('tanggal_kontrak')->get();
        return view('jadwal-menu.form', ['jadwal' => $jadwalMenu, 'kontrakList' => $kontrakList]);
    }

    public function update(Request $request, JadwalMenu $jadwalMenu): RedirectResponse
    {
        $data = $this->validatedData($request, $jadwalMenu->id);
        $data['updated_by'] = auth()->id();
        $jadwalMenu->update($data);
        return redirect()->route('jadwal-menu.index')->with('success', 'Jadwal menu berhasil diperbarui.');
    }

    public function destroy(JadwalMenu $jadwalMenu): RedirectResponse
    {
        $jadwalMenu->delete();
        return redirect()->route('jadwal-menu.index')->with('success', 'Jadwal menu berhasil dihapus.');
    }

    private function validatedData(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'kontrak_id'       => 'required|exists:kontrak_makan,id',
            'tanggal'          => 'required|date',
            'jenis_makan'      => 'required|in:sarapan,makan_siang,makan_malam',
            'menu'             => 'required|string|max:500',
            'porsi_per_taruna' => 'required|integer|min:1|max:5',
            'catatan'          => 'nullable|string|max:500',
        ]);
    }

    private function actionButtons(JadwalMenu $j): string
    {
        $del = '<form id="del-jm-' . $j->id . '" method="POST" action="' . route('jadwal-menu.destroy', $j) . '" class="d-inline">'
            . csrf_field() . method_field('DELETE')
            . '<button type="button" class="btn btn-sm btn-outline-danger" onclick="konfirmasiHapus(\'del-jm-' . $j->id . '\', \'jadwal menu ' . e($j->tanggal->format('d/m/Y')) . '\')">'
            . '<i class="bi bi-trash"></i></button></form>';

        return '<a href="' . route('jadwal-menu.edit', $j) . '" class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-pencil"></i></a>'
            . $del;
    }
}
