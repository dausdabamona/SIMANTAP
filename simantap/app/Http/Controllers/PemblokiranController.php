<?php

namespace App\Http\Controllers;

use App\Models\PemblokiranUangMakan;
use App\Models\SenatAccount;
use App\Models\Taruna;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class PemblokiranController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return DataTables::eloquent(PemblokiranUangMakan::with('taruna'))
                ->addIndexColumn()
                ->addColumn('nama_taruna', fn ($p) => $p->taruna?->nama)
                ->addColumn('nit', fn ($p) => $p->taruna?->nit)
                ->addColumn('nilai_fmt', fn ($p) => 'Rp ' . number_format($p->nilai_bantuan, 0, ',', '.'))
                ->addColumn('status_badge', fn ($p) => $this->statusBadge($p))
                ->addColumn('action', fn ($p) => $this->actionButtons($p))
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        return view('pemblokiran.index');
    }

    public function create(): View
    {
        $tarunaList   = Taruna::aktif()->penerimaBantuan()->orderBy('nama')->get();
        $senatAccounts = SenatAccount::where('is_aktif', true)->get();
        return view('pemblokiran.form', ['pemblokiran' => null, 'tarunaList' => $tarunaList, 'senatAccounts' => $senatAccounts]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        $data['status']         = PemblokiranUangMakan::STATUS_DIUSULKAN;
        $data['diusulkan_oleh'] = auth()->id();
        $data['diusulkan_at']   = now();
        $data['created_by']     = auth()->id();

        if ($request->hasFile('file_surat_pemblokiran')) {
            $data['file_surat_pemblokiran'] = $request->file('file_surat_pemblokiran')->store('pemblokiran', 'public');
        }

        PemblokiranUangMakan::create($data);

        return redirect()->route('pemblokiran.index')->with('success', 'Pemblokiran berhasil diusulkan.');
    }

    public function show(PemblokiranUangMakan $pemblokiranUangMakan): View
    {
        $pemblokiranUangMakan->load(['taruna', 'senatAccount', 'diusulkanOleh', 'diprosesOleh']);
        return view('pemblokiran.show', ['pemblokiran' => $pemblokiranUangMakan]);
    }

    public function edit(PemblokiranUangMakan $pemblokiranUangMakan): View
    {
        if ($pemblokiranUangMakan->status !== PemblokiranUangMakan::STATUS_DIUSULKAN) {
            return redirect()->route('pemblokiran.show', $pemblokiranUangMakan)
                ->with('error', 'Hanya pemblokiran berstatus Diusulkan yang dapat diedit.');
        }
        $tarunaList    = Taruna::aktif()->penerimaBantuan()->orderBy('nama')->get();
        $senatAccounts = SenatAccount::where('is_aktif', true)->get();
        return view('pemblokiran.form', ['pemblokiran' => $pemblokiranUangMakan, 'tarunaList' => $tarunaList, 'senatAccounts' => $senatAccounts]);
    }

    public function update(Request $request, PemblokiranUangMakan $pemblokiranUangMakan): RedirectResponse
    {
        if ($pemblokiranUangMakan->status !== PemblokiranUangMakan::STATUS_DIUSULKAN) {
            return back()->with('error', 'Data ini tidak dapat diubah.');
        }
        $data = $this->validatedData($request);
        if ($request->hasFile('file_surat_pemblokiran')) {
            if ($pemblokiranUangMakan->file_surat_pemblokiran) {
                Storage::disk('public')->delete($pemblokiranUangMakan->file_surat_pemblokiran);
            }
            $data['file_surat_pemblokiran'] = $request->file('file_surat_pemblokiran')->store('pemblokiran', 'public');
        }
        $pemblokiranUangMakan->update($data);
        return redirect()->route('pemblokiran.index')->with('success', 'Pemblokiran berhasil diperbarui.');
    }

    public function destroy(PemblokiranUangMakan $pemblokiranUangMakan): RedirectResponse
    {
        // Pemblokiran is immutable (no softDeletes) — only allow if still diusulkan
        if ($pemblokiranUangMakan->status !== PemblokiranUangMakan::STATUS_DIUSULKAN) {
            return back()->with('error', 'Pemblokiran yang sudah diproses tidak dapat dihapus.');
        }
        // Hard delete — by design, pemblokiran has no softDeletes
        $pemblokiranUangMakan->forceDelete();
        return redirect()->route('pemblokiran.index')->with('success', 'Usulan pemblokiran dibatalkan.');
    }

    public function proses(Request $request, PemblokiranUangMakan $pemblokiranUangMakan): RedirectResponse
    {
        $request->validate([
            'bukti_debit_bank' => 'required|mimes:pdf,jpg,jpeg,png|max:5120',
            'tanggal_debit'    => 'required|date',
            'nilai_didebit'    => 'required|numeric|min:0',
        ]);

        $buktiPath = $request->file('bukti_debit_bank')->store('pemblokiran/debit', 'public');

        $pemblokiranUangMakan->update([
            'status'          => PemblokiranUangMakan::STATUS_DIDEBIT,
            'bukti_debit_bank'=> $buktiPath,
            'tanggal_debit'   => $request->tanggal_debit,
            'nilai_didebit'   => $request->nilai_didebit,
            'diproses_oleh'   => auth()->id(),
            'diproses_at'     => now(),
        ]);

        return back()->with('success', 'Pemblokiran berhasil dicatat sebagai didebit.');
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'taruna_id'              => 'required|exists:taruna,id',
            'senat_account_id'       => 'required|exists:senat_accounts,id',
            'periode_bulan'          => 'required|integer|min:1|max:12',
            'periode_tahun'          => 'required|integer|min:2020|max:2100',
            'nilai_bantuan'          => 'required|numeric|min:0',
            'nomor_surat_pemblokiran'=> 'nullable|string|max:100',
            'tanggal_surat'          => 'nullable|date',
            'catatan'                => 'nullable|string|max:500',
            'file_surat_pemblokiran' => 'nullable|mimes:pdf|max:10240',
        ]);
    }

    private function statusBadge(PemblokiranUangMakan $p): string
    {
        $map = [
            PemblokiranUangMakan::STATUS_DIUSULKAN => 'warning',
            PemblokiranUangMakan::STATUS_DIBLOKIR  => 'info',
            PemblokiranUangMakan::STATUS_DIDEBIT   => 'success',
        ];
        $label = [
            PemblokiranUangMakan::STATUS_DIUSULKAN => 'Diusulkan',
            PemblokiranUangMakan::STATUS_DIBLOKIR  => 'Diblokir',
            PemblokiranUangMakan::STATUS_DIDEBIT   => 'Didebit',
        ];
        $color = $map[$p->status] ?? 'secondary';
        return '<span class="badge bg-' . $color . '">' . ($label[$p->status] ?? $p->status) . '</span>';
    }

    private function actionButtons(PemblokiranUangMakan $p): string
    {
        return '<a href="' . route('pemblokiran.show', $p) . '" class="btn btn-sm btn-outline-info me-1"><i class="bi bi-eye"></i></a>'
            . ($p->status === PemblokiranUangMakan::STATUS_DIUSULKAN
                ? '<a href="' . route('pemblokiran.edit', $p) . '" class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-pencil"></i></a>'
                : '');
    }
}
