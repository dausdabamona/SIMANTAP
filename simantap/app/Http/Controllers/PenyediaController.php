<?php

namespace App\Http\Controllers;

use App\Models\PemesananHarian;
use App\Models\PengajuanPembayaran;
use App\Models\PenyediaMakan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PenyediaController extends Controller
{
    private function penyediaLogin(): PenyediaMakan
    {
        $penyedia = PenyediaMakan::where('user_id', auth()->id())->first();
        abort_unless($penyedia !== null, 403, 'Akun Anda belum terhubung ke data penyedia.');
        return $penyedia;
    }

    public function pesanan(): View
    {
        $penyedia = $this->penyediaLogin();

        $pesanan = PemesananHarian::whereIn('status', ['dikirim_penyedia', 'disajikan', 'selesai'])
            ->latest('tanggal')
            ->paginate(20);

        return view('penyedia.pesanan', compact('penyedia', 'pesanan'));
    }

    public function konfirmasiPesanan(Request $request, int $id): RedirectResponse
    {
        $this->penyediaLogin();

        $pesanan = PemesananHarian::findOrFail($id);
        abort_unless($pesanan->status === 'dikirim_penyedia', 403);

        $pesanan->update(['status' => 'disajikan']);
        return back()->with('success', 'Pesanan dikonfirmasi penyedia.');
    }

    public function invoice(): View
    {
        $penyedia = $this->penyediaLogin();

        // Invoice = pengajuan pembayaran terkait (SP2D terbit+)
        $invoiceList = PengajuanPembayaran::whereIn('status', ['sp2d', 'transfer_kppn', 'debit_bank', 'debit_selesai', 'lpj_ppk', 'lpj_kpa', 'selesai'])
            ->latest()
            ->paginate(20);

        return view('penyedia.invoice', compact('penyedia', 'invoiceList'));
    }

    public function uploadInvoice(Request $request): RedirectResponse
    {
        $this->penyediaLogin();

        $data = $request->validate([
            'pembayaran_id' => 'required|exists:pengajuan_pembayaran,id',
            'file_invoice'  => 'required|file|mimes:pdf,jpg,png|max:5120',
        ]);

        $pembayaran = PengajuanPembayaran::findOrFail($data['pembayaran_id']);
        $path = $request->file('file_invoice')->store('invoice-penyedia', 'public');
        $pembayaran->update(['invoice_penyedia' => $path]);

        return back()->with('success', 'Invoice berhasil diupload.');
    }

    public function pembayaran(): View
    {
        $penyedia = $this->penyediaLogin();

        $pembayaranList = PengajuanPembayaran::latest()->paginate(20);

        return view('penyedia.pembayaran', compact('penyedia', 'pembayaranList'));
    }

    public function konfirmasiTransfer(Request $request, int $id): RedirectResponse
    {
        $this->penyediaLogin();

        $pembayaran = PengajuanPembayaran::findOrFail($id);
        abort_unless($pembayaran->status === 'debit_selesai', 403);

        $pembayaran->update(['status' => 'lpj_ppk']);
        return back()->with('success', 'Transfer dikonfirmasi oleh penyedia.');
    }
}
