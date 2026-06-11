<?php

namespace App\Http\Controllers;

use App\Models\PengajuanPembayaran;
use App\Models\PemesananHarian;
use App\Models\RekapBulanan;
use App\Models\PemblokiranUangMakan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PdfController extends Controller
{
    /** Rekap bulanan per taruna */
    public function rekapBulanan(RekapBulanan $rekap): Response
    {
        $rekap->load(['taruna', 'kontrak', 'approvals.user']);
        $pdf = Pdf::loadView('pdf.rekap-bulanan', compact('rekap'))
            ->setPaper('a4', 'portrait');
        return $pdf->download('rekap-' . $rekap->taruna?->nit . '-' . $rekap->periode_label . '.pdf');
    }

    /** Rekap bulanan semua taruna dalam satu periode */
    public function rekapPeriode(Request $request): Response
    {
        $bulan = $request->bulan ?? now()->month;
        $tahun = $request->tahun ?? now()->year;

        $rekapList = RekapBulanan::with(['taruna', 'kontrak'])
            ->byPeriode($bulan, $tahun)
            ->orderBy('taruna_id')
            ->get();

        $pdf = Pdf::loadView('pdf.rekap-periode', compact('rekapList', 'bulan', 'tahun'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('rekap-periode-' . $bulan . '-' . $tahun . '.pdf');
    }

    /** Pemesanan harian — Surat Pesanan Makan */
    public function pemesananHarian(PemesananHarian $pemesanan): Response
    {
        $pemesanan->load(['kontrak.penyedia', 'ttdSenat', 'ttdPembina']);
        $pdf = Pdf::loadView('pdf.pemesanan-harian', compact('pemesanan'))
            ->setPaper('a4', 'portrait');
        return $pdf->download('surat-pesanan-' . $pemesanan->tanggal->format('Ymd') . '.pdf');
    }

    /** Pengajuan pembayaran — Dokumen pengajuan LS */
    public function pengajuanPembayaran(PengajuanPembayaran $pembayaran): Response
    {
        $pembayaran->load('workflow.user');
        $pdf = Pdf::loadView('pdf.pengajuan-pembayaran', compact('pembayaran'))
            ->setPaper('a4', 'portrait');
        return $pdf->download('pengajuan-' . $pembayaran->nomor_pengajuan . '.pdf');
    }

    /** Surat pemblokiran */
    public function pemblokiran(PemblokiranUangMakan $pemblokiranUangMakan): Response
    {
        $pemblokiranUangMakan->load(['taruna', 'senatAccount', 'diusulkanOleh']);
        $pdf = Pdf::loadView('pdf.surat-pemblokiran', ['pemblokiran' => $pemblokiranUangMakan])
            ->setPaper('a4', 'portrait');
        return $pdf->download('surat-pemblokiran-' . $pemblokiranUangMakan->taruna?->nit . '.pdf');
    }
}
