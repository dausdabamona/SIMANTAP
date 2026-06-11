<?php

namespace App\Http\Controllers;

use App\Models\SkPenerima;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class SkPenerimaController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return DataTables::eloquent(SkPenerima::query())
                ->addIndexColumn()
                ->addColumn('periode', fn ($sk) => $sk->periode_mulai->format('d/m/Y') . ' – ' . $sk->periode_selesai->format('d/m/Y'))
                ->addColumn('file_link', fn ($sk) => $sk->file_sk
                    ? '<a href="' . Storage::url($sk->file_sk) . '" target="_blank" class="btn btn-xs btn-outline-primary btn-sm"><i class="bi bi-file-pdf"></i> Buka</a>'
                    : '<span class="text-muted small">Tidak ada</span>')
                ->addColumn('action', fn ($sk) => $this->actionButtons($sk))
                ->rawColumns(['file_link', 'action'])
                ->make(true);
        }

        return view('sk-penerima.index');
    }

    public function create(): View
    {
        return view('sk-penerima.form', ['sk' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        if ($request->hasFile('file_sk')) {
            $data['file_sk'] = $request->file('file_sk')->store('sk-penerima', 'public');
        }
        SkPenerima::create($data);
        return redirect()->route('sk-penerima.index')->with('success', 'SK Penerima berhasil disimpan.');
    }

    public function show(SkPenerima $skPenerima): View
    {
        return view('sk-penerima.show', ['sk' => $skPenerima]);
    }

    public function edit(SkPenerima $skPenerima): View
    {
        return view('sk-penerima.form', ['sk' => $skPenerima]);
    }

    public function update(Request $request, SkPenerima $skPenerima): RedirectResponse
    {
        $data = $this->validatedData($request, $skPenerima->id);
        if ($request->hasFile('file_sk')) {
            if ($skPenerima->file_sk) {
                Storage::disk('public')->delete($skPenerima->file_sk);
            }
            $data['file_sk'] = $request->file('file_sk')->store('sk-penerima', 'public');
        }
        $skPenerima->update($data);
        return redirect()->route('sk-penerima.index')->with('success', 'SK Penerima berhasil diperbarui.');
    }

    public function destroy(SkPenerima $skPenerima): RedirectResponse
    {
        if ($skPenerima->file_sk) {
            Storage::disk('public')->delete($skPenerima->file_sk);
        }
        $skPenerima->delete();
        return redirect()->route('sk-penerima.index')->with('success', 'SK Penerima berhasil dihapus.');
    }

    private function validatedData(Request $request, ?int $ignoreId = null): array
    {
        $nomorRule = 'required|string|max:100|unique:sk_penerima,nomor_sk' . ($ignoreId ? ",$ignoreId" : '');
        return $request->validate([
            'nomor_sk'        => $nomorRule,
            'judul'           => 'required|string|max:255',
            'penerbit'        => 'required|string|max:255',
            'tanggal_sk'      => 'required|date',
            'periode_mulai'   => 'required|date',
            'periode_selesai' => 'required|date|after_or_equal:periode_mulai',
            'jenis_sk'        => 'required|string|max:50',
            'keterangan'      => 'nullable|string|max:1000',
            'file_sk'         => 'nullable|mimes:pdf|max:10240',
        ]);
    }

    private function actionButtons(SkPenerima $sk): string
    {
        $del = '<form id="del-sk-' . $sk->id . '" method="POST" action="' . route('sk-penerima.destroy', $sk) . '" class="d-inline">'
            . csrf_field() . method_field('DELETE')
            . '<button type="button" class="btn btn-sm btn-outline-danger" onclick="konfirmasiHapus(\'del-sk-' . $sk->id . '\', \'' . addslashes($sk->nomor_sk) . '\')">'
            . '<i class="bi bi-trash"></i></button></form>';

        return '<a href="' . route('sk-penerima.show', $sk) . '" class="btn btn-sm btn-outline-info me-1"><i class="bi bi-eye"></i></a>'
            . '<a href="' . route('sk-penerima.edit', $sk) . '" class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-pencil"></i></a>'
            . $del;
    }
}
