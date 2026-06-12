<?php

namespace App\Http\Controllers;

use App\Models\InvoicePenyedia;
use App\Models\PengajuanPembayaran;
use App\Models\TransferPenyedia;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransferMonitorController extends Controller
{
    public function index(): View
    {
        abort_unless(auth()->user()->canAny(['rekap.view', 'pembayaran.view']), 403);

        $tahun = now()->year;

        // Panel 1: Transfer KPPN → Rekening Taruna (per SPM)
        $transferKppn = PengajuanPembayaran::whereIn('status', [
            'sp2d', 'transfer_kppn', 'debit_bank', 'debit_selesai', 'lpj_ppk', 'lpj_kpa', 'selesai',
        ])
        ->orderByDesc('tanggal_sp2d')
        ->paginate(15, ['*'], 'kppn');

        // Panel 2 (baru): Transfer Senat → Penyedia per bank_group
        $transferPenyediaSummary = TransferPenyedia::where('periode_tahun', $tahun)
            ->with(['senatAccount', 'rekeningPenyedia'])
            ->orderByDesc('periode_bulan')
            ->orderBy('bank_group')
            ->get()
            ->groupBy('periode_bulan');

        // Panel 3: Invoice Penyedia
        $invoices = InvoicePenyedia::with('penyedia')
            ->where('periode_tahun', $tahun)
            ->orderByDesc('periode_bulan')
            ->get();

        return view('transfer-monitor.index', compact(
            'transferKppn', 'transferPenyediaSummary', 'invoices', 'tahun'
        ));
    }

    public function mengetahuiKppn(PengajuanPembayaran $pembayaran): RedirectResponse
    {
        abort_unless(auth()->user()->hasRole('wadir_iii'), 403);
        return back()->with('success', 'Transfer KPPN periode ' .
            $pembayaran->periode_bulan . '/' . $pembayaran->periode_tahun . ' telah diketahui.');
    }

    public function setujuiTransferPenyedia(PengajuanPembayaran $pembayaran): RedirectResponse
    {
        return redirect()->route('transfer-penyedia.index')
            ->with('info', 'Gunakan halaman Transfer Penyedia untuk menyetujui transfer.');
    }
}
