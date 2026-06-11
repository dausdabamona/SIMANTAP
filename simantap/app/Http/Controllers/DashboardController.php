<?php

namespace App\Http\Controllers;

use App\Models\PengajuanPembayaran;
use App\Models\PemesananHarian;
use App\Models\RekapBulanan;
use App\Models\Taruna;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $bulan = now()->month;
        $tahun = now()->year;

        // Statistik taruna
        $totalTarunaAktif      = Taruna::where('status_taruna', 'aktif')->count();
        $totalTarunaTidakAktif = Taruna::whereNotIn('status_taruna', ['aktif'])->whereNull('deleted_at')->count();
        $totalPenerimaBantuan  = Taruna::where('penerima_bantuan', true)->count();
        $totalEligible         = Taruna::eligibleBantuan()->count();

        // Realisasi bulan berjalan
        $realisasiBulanIni = RekapBulanan::where('periode_bulan', $bulan)
            ->where('periode_tahun', $tahun)
            ->sum('nilai_bantuan');

        // Status pembayaran terbaru
        $pengajuanTerbaru = PengajuanPembayaran::with('kontrak')
            ->byPeriode($bulan, $tahun)
            ->latest()
            ->first();

        // Pemesanan hari ini
        $pemesananHariIni = PemesananHarian::whereDate('tanggal', today())
            ->with('kontrak')
            ->first();

        // Pemesanan pending verifikasi
        $pemesananPendingVerifikasi = PemesananHarian::where('status', 'draft')
            ->where('tanggal', '>=', today())
            ->count();

        // Grafik realisasi 6 bulan terakhir
        $grafikRealisasi = RekapBulanan::select(
                DB::raw('periode_bulan, periode_tahun, SUM(nilai_bantuan) as total')
            )
            ->where(function ($q) use ($bulan, $tahun) {
                $q->where('periode_tahun', $tahun)
                  ->where('periode_bulan', '<=', $bulan);
                if ($bulan <= 6) {
                    $q->orWhere(function ($q2) use ($bulan, $tahun) {
                        $q2->where('periode_tahun', $tahun - 1)
                           ->where('periode_bulan', '>', 12 - (6 - $bulan));
                    });
                }
            })
            ->groupBy('periode_tahun', 'periode_bulan')
            ->orderBy('periode_tahun')->orderBy('periode_bulan')
            ->get();

        // Status taruna breakdown
        $statusBreakdown = Taruna::select('status_taruna', DB::raw('count(*) as total'))
            ->whereNull('deleted_at')
            ->groupBy('status_taruna')
            ->pluck('total', 'status_taruna');

        // Riwayat pengajuan pembayaran
        $riwayatPengajuan = PengajuanPembayaran::with('kontrak.penyedia')
            ->latest()
            ->limit(5)
            ->get();

        return view('dashboard', compact(
            'totalTarunaAktif', 'totalTarunaTidakAktif',
            'totalPenerimaBantuan', 'totalEligible',
            'realisasiBulanIni', 'pengajuanTerbaru',
            'pemesananHariIni', 'pemesananPendingVerifikasi',
            'grafikRealisasi', 'statusBreakdown', 'riwayatPengajuan',
            'bulan', 'tahun',
        ));
    }
}
