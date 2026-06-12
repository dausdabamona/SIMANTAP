<?php

namespace App\Http\Controllers;

use App\Models\PenyediaMakan;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class PenyediaMakanController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return DataTables::eloquent(PenyediaMakan::with('rekeningDefault'))
                ->addIndexColumn()
                ->addColumn('bank', fn ($p) => $p->rekeningDefault?->bank ?? '-')
                ->addColumn('nomor_rekening', fn ($p) => $p->rekeningDefault?->nomor_rekening ?? '-')
                ->addColumn('kontrak_aktif', fn ($p) => $p->kontrakAktif()->count())
                ->addColumn('action', fn ($p) => $this->actionButtons($p))
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('penyedia.index');
    }

    public function create(): View
    {
        return view('penyedia.form', ['penyedia' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        PenyediaMakan::create($data);
        return redirect()->route('penyedia.index')->with('success', 'Data penyedia berhasil ditambahkan.');
    }

    public function show(PenyediaMakan $penyedium): View
    {
        $penyedium->load('kontrak', 'rekening');
        return view('penyedia.show', ['penyedia' => $penyedium]);
    }

    public function edit(PenyediaMakan $penyedium): View
    {
        return view('penyedia.form', ['penyedia' => $penyedium]);
    }

    public function update(Request $request, PenyediaMakan $penyedium): RedirectResponse
    {
        $penyedium->update($this->validatedData($request));
        return redirect()->route('penyedia.index')->with('success', 'Data penyedia berhasil diperbarui.');
    }

    public function destroy(PenyediaMakan $penyedium): RedirectResponse
    {
        if ($penyedium->kontrakAktif()->exists()) {
            return back()->with('error', 'Penyedia tidak dapat dihapus karena memiliki kontrak aktif.');
        }
        $penyedium->delete();
        return redirect()->route('penyedia.index')->with('success', 'Data penyedia berhasil dihapus.');
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'nama'  => 'required|string|max:255',
            'npwp'  => 'nullable|string|max:20',
            'alamat'=> 'nullable|string|max:500',
            'telp'  => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
        ]);
    }

    private function actionButtons(PenyediaMakan $p): string
    {
        $del = '<form id="del-p-' . $p->id . '" method="POST" action="' . route('penyedia.destroy', $p) . '" class="d-inline">'
            . csrf_field() . method_field('DELETE')
            . '<button type="button" class="btn btn-sm btn-outline-danger" title="Hapus" '
            . 'onclick="konfirmasiHapus(\'del-p-' . $p->id . '\', \'' . addslashes($p->nama) . '\')">'
            . '<i class="bi bi-trash"></i></button></form>';

        return '<a href="' . route('penyedia.show', $p) . '" class="btn btn-sm btn-outline-info me-1"><i class="bi bi-eye"></i></a>'
            . '<a href="' . route('penyedia.edit', $p) . '" class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-pencil"></i></a>'
            . $del;
    }
}
