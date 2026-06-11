<?php

namespace App\Http\Controllers;

use App\Models\KontrakMakan;
use App\Models\PenyediaMakan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class KontrakMakanController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return DataTables::eloquent(KontrakMakan::with('penyedia'))
                ->addIndexColumn()
                ->addColumn('penyedia_nama', fn ($k) => $k->penyedia?->nama)
                ->addColumn('nilai_fmt', fn ($k) => 'Rp ' . number_format($k->nilai_kontrak, 0, ',', '.'))
                ->addColumn('status_badge', fn ($k) => $this->statusBadge($k))
                ->addColumn('action', fn ($k) => $this->actionButtons($k))
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        return view('kontrak.index');
    }

    public function create(): View
    {
        return view('kontrak.form', [
            'kontrak'  => null,
            'penyedia' => PenyediaMakan::orderBy('nama')->get(),
            'ppkList'  => User::role('ppk')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);

        foreach (['file_kontrak', 'file_addendum', 'file_berita_acara_penunjukan', 'file_notulensi_rapat'] as $field) {
            if ($request->hasFile($field)) {
                $data[$field] = $request->file($field)->store('kontrak', 'public');
            }
        }

        KontrakMakan::create($data);

        return redirect()->route('kontrak.index')->with('success', 'Kontrak berhasil disimpan.');
    }

    public function show(KontrakMakan $kontrak): View
    {
        $kontrak->load('penyedia', 'disetujuiPpk');
        return view('kontrak.show', compact('kontrak'));
    }

    public function edit(KontrakMakan $kontrak): View
    {
        return view('kontrak.form', [
            'kontrak'  => $kontrak,
            'penyedia' => PenyediaMakan::orderBy('nama')->get(),
            'ppkList'  => User::role('ppk')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, KontrakMakan $kontrak): RedirectResponse
    {
        $data = $this->validatedData($request, $kontrak->id);

        foreach (['file_kontrak', 'file_addendum', 'file_berita_acara_penunjukan', 'file_notulensi_rapat'] as $field) {
            if ($request->hasFile($field)) {
                if ($kontrak->$field) {
                    Storage::disk('public')->delete($kontrak->$field);
                }
                $data[$field] = $request->file($field)->store('kontrak', 'public');
            }
        }

        $kontrak->update($data);

        return redirect()->route('kontrak.index')->with('success', 'Kontrak berhasil diperbarui.');
    }

    public function destroy(KontrakMakan $kontrak): RedirectResponse
    {
        if ($kontrak->status === 'aktif') {
            return back()->with('error', 'Kontrak aktif tidak dapat dihapus.');
        }

        foreach (['file_kontrak', 'file_addendum', 'file_berita_acara_penunjukan', 'file_notulensi_rapat'] as $field) {
            if ($kontrak->$field) {
                Storage::disk('public')->delete($kontrak->$field);
            }
        }

        $kontrak->delete();
        return redirect()->route('kontrak.index')->with('success', 'Kontrak berhasil dihapus.');
    }

    public function updateStatus(Request $request, KontrakMakan $kontrak): RedirectResponse
    {
        $request->validate(['status' => 'required|in:draft,aktif,berakhir,dibatalkan']);
        $kontrak->update(['status' => $request->status]);
        return back()->with('success', 'Status kontrak diperbarui.');
    }

    // ── Helpers ────────────────────────────────────────────────

    private function validatedData(Request $request, ?int $ignoreId = null): array
    {
        $nomorRule = 'required|string|max:100|unique:kontrak_makan,nomor_kontrak' . ($ignoreId ? ",$ignoreId" : '');

        return $request->validate([
            'nomor_kontrak'                  => $nomorRule,
            'tanggal_kontrak'                => 'required|date',
            'tanggal_mulai'                  => 'required|date',
            'tanggal_selesai'                => 'required|date|after:tanggal_mulai',
            'nilai_kontrak'                  => 'required|numeric|min:0',
            'harga_porsi'                    => 'required|numeric|min:0',
            'penyedia_id'                    => 'required|exists:penyedia_makan,id',
            'status'                         => 'required|in:draft,aktif,berakhir,dibatalkan',
            'disetujui_ppk_id'               => 'nullable|exists:users,id',
            'tgl_persetujuan_ppk'            => 'nullable|date',
            'catatan'                        => 'nullable|string|max:1000',
            'file_kontrak'                   => 'nullable|mimes:pdf|max:10240',
            'file_addendum'                  => 'nullable|mimes:pdf|max:10240',
            'file_berita_acara_penunjukan'   => 'nullable|mimes:pdf|max:10240',
            'file_notulensi_rapat'           => 'nullable|mimes:pdf|max:10240',
        ]);
    }

    private function statusBadge(KontrakMakan $k): string
    {
        $map = ['draft' => 'secondary', 'aktif' => 'success', 'berakhir' => 'warning', 'dibatalkan' => 'danger'];
        $color = $map[$k->status] ?? 'secondary';
        return '<span class="badge bg-' . $color . '">' . ucfirst($k->status) . '</span>';
    }

    private function actionButtons(KontrakMakan $k): string
    {
        $del = $k->status !== 'aktif'
            ? '<form id="del-k-' . $k->id . '" method="POST" action="' . route('kontrak.destroy', $k) . '" class="d-inline">'
              . csrf_field() . method_field('DELETE')
              . '<button type="button" class="btn btn-sm btn-outline-danger" onclick="konfirmasiHapus(\'del-k-' . $k->id . '\', \'' . addslashes($k->nomor_kontrak) . '\')">'
              . '<i class="bi bi-trash"></i></button></form>'
            : '';

        return '<a href="' . route('kontrak.show', $k) . '" class="btn btn-sm btn-outline-info me-1"><i class="bi bi-eye"></i></a>'
            . '<a href="' . route('kontrak.edit', $k) . '" class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-pencil"></i></a>'
            . $del;
    }
}
