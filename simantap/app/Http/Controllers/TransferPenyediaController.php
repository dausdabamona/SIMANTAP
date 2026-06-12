<?php

namespace App\Http\Controllers;

use App\Models\InvoicePenyedia;
use App\Models\TransferPenyedia;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class TransferPenyediaController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()->canAny([
            'pembayaran.view', 'rekap.view', 'transfer_penyedia.view',
        ]), 403);

        if ($request->ajax()) {
            $query = TransferPenyedia::with(['senatAccount', 'rekeningPenyedia']);

            if ($tahun = $request->input('tahun')) {
                $query->where('periode_tahun', $tahun);
            }

            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->addColumn('periode_label', fn ($t) => $t->periode_label)
                ->addColumn('rekening_senat', fn ($t) => $t->senatAccount?->nama_akun ?? '-')
                ->addColumn('rekening_penyedia', fn ($t) => $t->rekeningPenyedia
                    ? $t->rekeningPenyedia->bank . ' ' . $t->rekeningPenyedia->nomor_rekening
                    : '-')
                ->addColumn('bank_badge', fn ($t) => $this->bankBadge($t->bank_group))
                ->addColumn('nilai_fmt', fn ($t) => 'Rp ' . number_format($t->total_nilai, 0, ',', '.'))
                ->addColumn('status_badge', fn ($t) => $this->statusBadge($t))
                ->addColumn('action', fn ($t) => $this->actionButtons($t))
                ->rawColumns(['bank_badge', 'status_badge', 'action'])
                ->make(true);
        }

        return view('transfer-penyedia.index', ['tahun' => now()->year]);
    }

    public function show(TransferPenyedia $transferPenyedia): View
    {
        abort_unless(auth()->user()->canAny([
            'pembayaran.view', 'rekap.view', 'transfer_penyedia.view',
        ]), 403);

        $transferPenyedia->load([
            'senatAccount', 'rekeningPenyedia',
            'spms', 'disetujuiWadirOleh', 'ditransferOleh', 'dikonfirmasiOleh',
        ]);

        return view('transfer-penyedia.show', compact('transferPenyedia'));
    }

    public function setujuiWadir(TransferPenyedia $transferPenyedia): RedirectResponse
    {
        abort_unless(auth()->user()->hasRole('wadir_iii'), 403);
        abort_unless($transferPenyedia->status === TransferPenyedia::STATUS_MENUNGGU, 422);

        $transferPenyedia->update([
            'status'              => TransferPenyedia::STATUS_DISETUJUI_WADIR,
            'disetujui_wadir_by'  => auth()->id(),
            'disetujui_wadir_at'  => now(),
        ]);

        return back()->with('success', 'Transfer ' . $transferPenyedia->bank_group . ' ' . $transferPenyedia->periode_label . ' disetujui.');
    }

    public function uploadBukti(Request $request, TransferPenyedia $transferPenyedia): RedirectResponse
    {
        abort_unless(auth()->user()->hasRole('senat_taruna'), 403);
        abort_unless($transferPenyedia->status === TransferPenyedia::STATUS_DISETUJUI_WADIR, 422);

        $request->validate([
            'bukti_transfer'   => 'required|mimes:pdf,jpg,jpeg,png|max:5120',
            'tanggal_transfer' => 'required|date',
            'catatan'          => 'nullable|string|max:500',
        ]);

        $path = $request->file('bukti_transfer')->store('transfer-penyedia', 'public');

        $transferPenyedia->update([
            'status'           => TransferPenyedia::STATUS_DITRANSFER,
            'bukti_transfer'   => $path,
            'tanggal_transfer' => $request->tanggal_transfer,
            'catatan'          => $request->catatan,
            'ditransfer_by'    => auth()->id(),
        ]);

        return back()->with('success', 'Bukti transfer berhasil diupload.');
    }

    public function konfirmasi(TransferPenyedia $transferPenyedia): RedirectResponse
    {
        abort_unless(auth()->user()->can('pembayaran.konfirmasi'), 403);
        abort_unless($transferPenyedia->status === TransferPenyedia::STATUS_DITRANSFER, 422);

        $transferPenyedia->update([
            'status'          => TransferPenyedia::STATUS_DIKONFIRMASI_PENYEDIA,
            'dikonfirmasi_by' => auth()->id(),
            'dikonfirmasi_at' => now(),
        ]);

        // Coba generate invoice jika BSI + BNI sudah dikonfirmasi
        try {
            InvoicePenyedia::generateUntukPeriode(
                $transferPenyedia->periode_bulan,
                $transferPenyedia->periode_tahun
            );
        } catch (\Throwable) {
            // Penyedia dengan kontrak aktif belum ada — abaikan
        }

        return back()->with('success', 'Transfer dikonfirmasi. Invoice penyedia akan dibuat otomatis jika kedua bank sudah dikonfirmasi.');
    }

    // ── Private helpers ──────────────────────────────────────────────

    private function bankBadge(string $bankGroup): string
    {
        $color = $bankGroup === 'BSI' ? 'success' : 'primary';
        return '<span class="badge bg-' . $color . '">' . e($bankGroup) . '</span>';
    }

    private function statusBadge(TransferPenyedia $t): string
    {
        return '<span class="badge bg-' . $t->status_badge_color . '">' . e($t->status_label) . '</span>';
    }

    private function actionButtons(TransferPenyedia $t): string
    {
        $btn = '<a href="' . route('transfer-penyedia.show', $t) . '" class="btn btn-sm btn-outline-info me-1"><i class="bi bi-eye"></i></a>';
        return $btn;
    }
}
