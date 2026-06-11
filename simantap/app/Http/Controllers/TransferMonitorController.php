<?php

namespace App\Http\Controllers;

use App\Models\PengajuanPembayaran;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransferMonitorController extends Controller
{
    public function index(): View
    {
        abort_unless(auth()->user()->canAny(['rekap.view', 'pembayaran.view']), 403);

        // Panel 1: Transfer KPPN → Rekening Taruna
        $transferKppn = PengajuanPembayaran::whereIn('status', [
            'sp2d', 'transfer_kppn', 'debit_bank', 'transfer_penyedia', 'selesai',
        ])
        ->orderByDesc('tanggal_sp2d')
        ->paginate(15, ['*'], 'kppn');

        // Panel 2: Transfer Senat → Penyedia (SP2D Terbit atau lebih, ada bukti transfer)
        $transferPenyedia = PengajuanPembayaran::where('status', 'transfer_penyedia')
            ->orderByDesc('updated_at')
            ->paginate(15, ['*'], 'penyedia');

        return view('transfer-monitor.index', compact('transferKppn', 'transferPenyedia'));
    }

    public function mengetahuiKppn(PengajuanPembayaran $pembayaran): RedirectResponse
    {
        abort_unless(auth()->user()->hasRole('wadir_iii'), 403);
        // Wadir III mencatat sudah mengetahui — tidak mengubah status
        return back()->with('success', 'Transfer KPPN periode ' .
            $pembayaran->periode_bulan . '/' . $pembayaran->periode_tahun . ' telah diketahui.');
    }

    public function setujuiTransferPenyedia(PengajuanPembayaran $pembayaran): RedirectResponse
    {
        abort_unless(auth()->user()->hasRole('wadir_iii'), 403);
        abort_unless($pembayaran->status === 'transfer_penyedia', 403);

        // Wadir III menyetujui → tandai status selesai (final)
        $pembayaran->update(['status' => 'selesai']);
        return back()->with('success', 'Transfer ke penyedia disetujui Wadir III.');
    }
}
