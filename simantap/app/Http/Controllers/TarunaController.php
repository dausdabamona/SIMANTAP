<?php

namespace App\Http\Controllers;

use App\Imports\TarunaImport;
use App\Exports\TarunaExport;
use App\Exports\TarunaTemplateExport;
use App\Models\Taruna;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class TarunaController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Taruna::withTrashed()->select('taruna.*');

            if (! $request->boolean('show_deleted')) {
                $query->whereNull('taruna.deleted_at');
            }

            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->addColumn('status_badge', fn ($t) => $this->statusBadge($t))
                ->addColumn('penerima_badge', fn ($t) => $t->penerima_bantuan
                    ? '<span class="badge bg-success"><i class="bi bi-check-lg"></i> Ya</span>'
                    : '<span class="badge bg-secondary">Tidak</span>')
                ->addColumn('eligible_badge', fn ($t) => $t->is_eligible_bantuan
                    ? '<span class="badge bg-success">Eligible</span>'
                    : '<span class="badge bg-danger">Tidak Eligible</span>')
                ->addColumn('action', fn ($t) => $this->actionButtons($t))
                ->rawColumns(['status_badge', 'penerima_badge', 'eligible_badge', 'action'])
                ->make(true);
        }

        return view('taruna.index');
    }

    public function create(): View
    {
        return view('taruna.form', ['taruna' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        Taruna::create($data);
        return redirect()->route('taruna.index')->with('success', 'Data taruna berhasil ditambahkan.');
    }

    public function show(Taruna $taruna): View
    {
        $taruna->load('rekening');
        return view('taruna.show', compact('taruna'));
    }

    public function edit(Taruna $taruna): View
    {
        return view('taruna.form', compact('taruna'));
    }

    public function update(Request $request, Taruna $taruna): RedirectResponse
    {
        $data = $this->validatedData($request, $taruna->id);
        $taruna->update($data);
        return redirect()->route('taruna.index')->with('success', 'Data taruna berhasil diperbarui.');
    }

    public function destroy(Taruna $taruna): RedirectResponse
    {
        $taruna->delete();
        return redirect()->route('taruna.index')->with('success', 'Data taruna berhasil dihapus.');
    }

    public function restore(int $id): RedirectResponse
    {
        Taruna::withTrashed()->findOrFail($id)->restore();
        return redirect()->route('taruna.index')->with('success', 'Data taruna berhasil dipulihkan.');
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls,csv|max:5120']);
        Excel::import(new TarunaImport, $request->file('file'));
        return redirect()->route('taruna.index')->with('success', 'Import data taruna berhasil.');
    }

    public function export(): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        return Excel::download(new TarunaExport, 'taruna-' . now()->format('Ymd') . '.xlsx');
    }

    public function template(): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        return Excel::download(new TarunaTemplateExport, 'template-import-taruna.xlsx');
    }

    // ── Helpers ────────────────────────────────────────────────────

    private function validatedData(Request $request, ?int $ignoreId = null): array
    {
        $nitRule = 'required|string|max:20|unique:taruna,nit' . ($ignoreId ? ",$ignoreId" : '');
        $nikRule = 'nullable|string|size:16|unique:taruna,nik' . ($ignoreId ? ",$ignoreId" : '');

        $data = $request->validate([
            'nit'              => $nitRule,
            'nama'             => 'required|string|max:100',
            'nik'              => $nikRule,
            'angkatan'         => 'required|integer|min:2000|max:2100',
            'prodi'            => 'required|string|max:100',
            'kelas'            => 'required|string|max:10',
            'jenis_kelamin'    => 'required|in:L,P',
            'status_taruna'    => 'required|in:aktif,cuti,pesiar,sakit_di_kampus,sakit_di_rumah_keluarga,penundaan_studi',
            'penerima_bantuan' => 'boolean',
        ]);

        $data['penerima_bantuan'] = $request->boolean('penerima_bantuan');
        return $data;
    }

    private function statusBadge(Taruna $t): string
    {
        $map = [
            'aktif'                   => 'success',
            'cuti'                    => 'warning',
            'pesiar'                  => 'info',
            'sakit_di_kampus'         => 'secondary',
            'sakit_di_rumah_keluarga' => 'danger',
            'penundaan_studi'         => 'dark',
        ];
        $color = $map[$t->status_taruna] ?? 'secondary';
        return '<span class="badge bg-' . $color . '">' . e($t->status_taruna_label) . '</span>';
    }

    private function actionButtons(Taruna $t): string
    {
        if ($t->deleted_at) {
            return '<form method="POST" action="' . route('taruna.restore', $t->id) . '" class="d-inline">'
                . csrf_field()
                . '<button type="submit" class="btn btn-sm btn-outline-success" title="Pulihkan">'
                . '<i class="bi bi-arrow-counterclockwise"></i></button></form>';
        }

        $del = '<form id="del-' . $t->id . '" method="POST" action="' . route('taruna.destroy', $t) . '" class="d-inline">'
            . csrf_field() . method_field('DELETE')
            . '<button type="button" class="btn btn-sm btn-outline-danger" title="Hapus" '
            . 'onclick="konfirmasiHapus(\'del-' . $t->id . '\', \'' . addslashes($t->nama) . '\')">'
            . '<i class="bi bi-trash"></i></button></form>';

        return '<a href="' . route('taruna.show', $t) . '" class="btn btn-sm btn-outline-info me-1" title="Detail"><i class="bi bi-eye"></i></a>'
            . '<a href="' . route('taruna.edit', $t) . '" class="btn btn-sm btn-outline-primary me-1" title="Edit"><i class="bi bi-pencil"></i></a>'
            . $del;
    }
}
