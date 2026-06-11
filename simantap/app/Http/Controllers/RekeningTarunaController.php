<?php

namespace App\Http\Controllers;

use App\Models\RekeningTaruna;
use App\Models\Taruna;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class RekeningTarunaController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return DataTables::eloquent(RekeningTaruna::with('taruna'))
                ->addIndexColumn()
                ->addColumn('nit', fn ($r) => $r->taruna?->nit)
                ->addColumn('nama_taruna', fn ($r) => $r->taruna?->nama)
                ->addColumn('nomor_masked', fn ($r) => $r->nomor_rekening_masked)
                ->addColumn('action', fn ($r) => $this->actionButtons($r))
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('rekening-taruna.index');
    }

    public function create(): View
    {
        $tarunaList = Taruna::aktif()->doesntHave('rekening')->orderBy('nama')->get();
        $preselect  = request('taruna_id');
        return view('rekening-taruna.form', ['rekening' => null, 'tarunaList' => $tarunaList, 'preselect' => $preselect]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        RekeningTaruna::create($data);
        return redirect()->route('rekening-taruna.index')->with('success', 'Rekening taruna berhasil ditambahkan.');
    }

    public function show(RekeningTaruna $rekeningTaruna): View
    {
        $rekeningTaruna->load('taruna');
        return view('rekening-taruna.show', ['rekening' => $rekeningTaruna]);
    }

    public function edit(RekeningTaruna $rekeningTaruna): View
    {
        $tarunaList = Taruna::aktif()->orderBy('nama')->get();
        return view('rekening-taruna.form', ['rekening' => $rekeningTaruna, 'tarunaList' => $tarunaList, 'preselect' => null]);
    }

    public function update(Request $request, RekeningTaruna $rekeningTaruna): RedirectResponse
    {
        $rekeningTaruna->update($this->validatedData($request, $rekeningTaruna->id));
        return redirect()->route('rekening-taruna.index')->with('success', 'Rekening taruna berhasil diperbarui.');
    }

    public function destroy(RekeningTaruna $rekeningTaruna): RedirectResponse
    {
        $rekeningTaruna->delete();
        return redirect()->route('rekening-taruna.index')->with('success', 'Rekening taruna berhasil dihapus.');
    }

    private function validatedData(Request $request, ?int $ignoreId = null): array
    {
        $nomorRule = 'required|string|max:30|unique:rekening_taruna,nomor_rekening' . ($ignoreId ? ",$ignoreId" : '');
        return $request->validate([
            'taruna_id'     => 'required|exists:taruna,id',
            'bank'          => 'required|string|max:50',
            'nomor_rekening'=> $nomorRule,
            'nama_pemilik'  => 'required|string|max:100',
        ]);
    }

    private function actionButtons(RekeningTaruna $r): string
    {
        $del = '<form id="del-r-' . $r->id . '" method="POST" action="' . route('rekening-taruna.destroy', $r) . '" class="d-inline">'
            . csrf_field() . method_field('DELETE')
            . '<button type="button" class="btn btn-sm btn-outline-danger" onclick="konfirmasiHapus(\'del-r-' . $r->id . '\', \'' . addslashes($r->taruna?->nama ?? '') . '\')">'
            . '<i class="bi bi-trash"></i></button></form>';

        return '<a href="' . route('rekening-taruna.edit', $r) . '" class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-pencil"></i></a>'
            . $del;
    }
}
