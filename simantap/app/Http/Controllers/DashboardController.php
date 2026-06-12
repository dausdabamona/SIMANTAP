<?php

namespace App\Http\Controllers;

use App\Models\KegiatanLuarKampus;
use App\Models\LaporanMontev;
use App\Models\PaguAnggaran;
use App\Models\PembayaranLuarKampus;
use App\Models\PemblokiranUangMakan;
use App\Models\PemesananHarian;
use App\Models\PenerimaanMakan;
use App\Models\PengajuanPembayaran;
use App\Models\InvoicePenyedia;
use App\Models\PenyediaMakan;
use App\Models\RekapBulanan;
use App\Models\TransferPenyedia;
use App\Models\Taruna;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        return redirect()->route($user->dashboardRoute());
    }

    public function superAdmin()
    {
        abort_unless(auth()->user()->hasRole('super_admin'), 403);

        $totalUserPerRole = DB::table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->select('roles.name as role', DB::raw('count(*) as total'))
            ->groupBy('roles.name')
            ->pluck('total', 'role');

        return view('dashboard.super-admin', [
            'totalUser'        => User::count(),
            'totalUserAktif'   => User::where('is_active', true)->count(),
            'totalUserPerRole' => $totalUserPerRole,
            'aktivitasTerbaru' => User::orderBy('last_login_at', 'desc')->take(10)->get(),
            'totalTaruna'      => Taruna::count(),
            'totalPenyedia'    => PenyediaMakan::count(),
        ]);
    }

    public function kpa()
    {
        abort_unless(auth()->user()->hasRole('kpa'), 403);

        $tahun = now()->year;
        $bulan = now()->month;

        $pagu = PaguAnggaran::where('tahun_anggaran', $tahun)->sum('nilai_pagu');
        $realisasi = PengajuanPembayaran::where('status', 'selesai')
            ->whereYear('created_at', $tahun)
            ->sum('nilai_pengajuan');
        $persenRealisasi = $pagu > 0 ? round(($realisasi / $pagu) * 100, 1) : 0;

        $grafikRealisasi = $this->grafikRealisasi6Bulan();

        return view('dashboard.kpa', [
            'pagu'               => $pagu,
            'realisasi'          => $realisasi,
            'persenRealisasi'    => $persenRealisasi,
            'grafikRealisasi'    => $grafikRealisasi,
            'totalTaruna'        => Taruna::where('status_taruna', 'aktif')->count(),
            'antrianPersetujuan' => RekapBulanan::where('status', RekapBulanan::STATUS_DITANDATANGANI_KPA)->count(),
            'kegiatanLuarAktif'  => KegiatanLuarKampus::whereNotIn('status', [
                KegiatanLuarKampus::STATUS_SELESAI,
                KegiatanLuarKampus::STATUS_DIBATALKAN,
            ])->count(),
        ]);
    }

    public function ppk()
    {
        abort_unless(auth()->user()->hasRole('ppk'), 403);

        $tahun = now()->year;
        $bulan = now()->month;

        $pagu = PaguAnggaran::where('tahun_anggaran', $tahun)->sum('nilai_pagu');
        $realisasi = PengajuanPembayaran::where('status', 'selesai')
            ->whereYear('created_at', $tahun)
            ->sum('nilai_pengajuan');
        $persenPagu = $pagu > 0 ? round(($realisasi / $pagu) * 100, 1) : 0;

        $grafikRealisasi = $this->grafikRealisasi6Bulan();

        return view('dashboard.ppk', [
            'antrianRekap'               => RekapBulanan::where('status', RekapBulanan::STATUS_DISETUJUI_WADIR)->count(),
            'pembayaranAktif'            => PengajuanPembayaran::whereNotIn('status', ['selesai', 'dibatalkan'])->count(),
            'pagu'                       => $pagu,
            'realisasi'                  => $realisasi,
            'persenPagu'                 => $persenPagu,
            'alertPagu'                  => $persenPagu >= 80,
            'grafikRealisasi'            => $grafikRealisasi,
            'kegiatanLuarAktif'          => KegiatanLuarKampus::whereNotIn('status', [
                KegiatanLuarKampus::STATUS_SELESAI, KegiatanLuarKampus::STATUS_DIBATALKAN,
            ])->count(),
            'pemesananHariIni'           => PemesananHarian::whereDate('tanggal', today())->first(),
            'totalTaruna'                => Taruna::where('status_taruna', 'aktif')->count(),
            'invoicePendingVerifikasi'   => InvoicePenyedia::where('status', InvoicePenyedia::STATUS_DITERIMA)->count(),
            'transferPendingKonfirmasi'  => TransferPenyedia::where('status', TransferPenyedia::STATUS_DITRANSFER)->count(),
        ]);
    }

    public function wadirIii()
    {
        abort_unless(auth()->user()->hasRole('wadir_iii'), 403);

        return view('dashboard.wadir-iii', [
            'rekapMenunggu'         => RekapBulanan::where('status', RekapBulanan::STATUS_DRAFT)->count(),
            'kegiatanLuarAktif'     => KegiatanLuarKampus::whereNotIn('status', [
                KegiatanLuarKampus::STATUS_SELESAI, KegiatanLuarKampus::STATUS_DIBATALKAN,
            ])->with('kaprodi')->latest()->take(5)->get(),
            'totalTaruna'           => Taruna::where('status_taruna', 'aktif')->count(),
            'totalKegiatanBulanIni' => KegiatanLuarKampus::whereMonth('tanggal_mulai', now()->month)
                ->whereYear('tanggal_mulai', now()->year)
                ->count(),
            'transferMenungguWadir' => TransferPenyedia::where('status', TransferPenyedia::STATUS_MENUNGGU)->count(),
        ]);
    }

    public function pembinaKarakter()
    {
        abort_unless(auth()->user()->hasRole('pembina_karakter'), 403);

        return view('dashboard.pembina-karakter', [
            'pemesananHariIni'    => PemesananHarian::whereDate('tanggal', today())->first(),
            'antrianVerifikasi'   => PemesananHarian::where('status', 'draft')->count(),
            'tidakEligibleHariIni'=> PenerimaanMakan::whereDate('tanggal', today())
                ->where('status_eligibilitas', 'tidak_dapat')
                ->with('taruna')
                ->get(),
            'totalRekapTtd'       => RekapBulanan::where('status', RekapBulanan::STATUS_DIHITUNG_PPK)->count(),
        ]);
    }

    public function senatTaruna()
    {
        abort_unless(auth()->user()->hasRole('senat_taruna'), 403);

        $bulan = now()->month;
        $tahun = now()->year;

        return view('dashboard.senat-taruna', [
            'statusPesananHariIni' => PemesananHarian::whereDate('tanggal', today())->first(),
            'notifikasiPemblokiran'=> PemblokiranUangMakan::where('status', 'diusulkan')->count(),
            'transferPending'      => PengajuanPembayaran::where('status', 'transfer_selesai')->count(),
            'totalRekapBulanIni'   => RekapBulanan::where('periode_bulan', $bulan)
                ->where('periode_tahun', $tahun)
                ->count(),
        ]);
    }

    public function kaprodi()
    {
        abort_unless(auth()->user()->hasRole('kaprodi'), 403);

        $userId = auth()->id();

        $kegiatanAktif = KegiatanLuarKampus::where('kaprodi_id', $userId)
            ->whereNotIn('status', [KegiatanLuarKampus::STATUS_SELESAI, KegiatanLuarKampus::STATUS_DIBATALKAN])
            ->latest()
            ->get();

        $statusCount = KegiatanLuarKampus::where('kaprodi_id', $userId)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $pembayaranDraft = PembayaranLuarKampus::whereHas('kegiatan', fn ($q) => $q->where('kaprodi_id', $userId))
            ->where('status', PembayaranLuarKampus::STATUS_DRAFT)
            ->with('kegiatan')
            ->get();

        return view('dashboard.kaprodi', [
            'kegiatanAktif'     => $kegiatanAktif,
            'statusCount'       => $statusCount,
            'pembayaranDraft'   => $pembayaranDraft,
        ]);
    }

    public function penyedia()
    {
        abort_unless(auth()->user()->hasRole('penyedia'), 403);

        $penyedia = PenyediaMakan::where('user_id', auth()->id())->first();

        $bulan = now()->month;
        $tahun = now()->year;

        $transferBulanIni = TransferPenyedia::where([
            'periode_bulan' => $bulan,
            'periode_tahun' => $tahun,
        ])->get()->keyBy('bank_group');

        $invoiceBulanIni = InvoicePenyedia::where([
            'periode_bulan' => $bulan,
            'periode_tahun' => $tahun,
        ])->first();

        return view('dashboard.penyedia', [
            'penyedia'           => $penyedia,
            'pesananHariIni'     => PemesananHarian::whereDate('tanggal', today())->first(),
            'riwayatPesanan'     => PemesananHarian::whereIn('status', ['dikirim_penyedia', 'selesai'])
                ->latest()
                ->take(10)
                ->get(),
            'transferBulanIni'   => $transferBulanIni,
            'invoiceBulanIni'    => $invoiceBulanIni,
            'bulan'              => $bulan,
            'tahun'              => $tahun,
        ]);
    }

    public function auditor()
    {
        abort_unless(auth()->user()->hasRole('auditor'), 403);

        return view('dashboard.auditor', [
            'ringkasan' => [
                'taruna'      => Taruna::count(),
                'pembayaran'  => PengajuanPembayaran::count(),
                'kegiatan'    => KegiatanLuarKampus::count(),
                'montev'      => LaporanMontev::count(),
                'user'        => User::count(),
            ],
            'loginTerbaru' => User::whereNotNull('last_login_at')
                ->orderBy('last_login_at', 'desc')
                ->take(15)
                ->get(),
        ]);
    }

    // ── Helper ───────────────────────────────────────────────────────

    private function grafikRealisasi6Bulan(): array
    {
        $bulan = now()->month;
        $tahun = now()->year;

        $rows = RekapBulanan::select(
                'periode_bulan',
                'periode_tahun',
                DB::raw('SUM(nilai_bantuan) as total')
            )
            ->where(function ($q) use ($bulan, $tahun) {
                for ($i = 5; $i >= 0; $i--) {
                    $tgl = now()->subMonths($i);
                    $q->orWhere(function ($q2) use ($tgl) {
                        $q2->where('periode_bulan', $tgl->month)
                           ->where('periode_tahun', $tgl->year);
                    });
                }
            })
            ->groupBy('periode_tahun', 'periode_bulan')
            ->orderBy('periode_tahun')->orderBy('periode_bulan')
            ->get()
            ->keyBy(fn ($r) => $r->periode_tahun . '-' . str_pad($r->periode_bulan, 2, '0', STR_PAD_LEFT));

        $namaBulan = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
                          'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

        $labels = [];
        $values = [];

        for ($i = 5; $i >= 0; $i--) {
            $tgl    = now()->subMonths($i);
            $key    = $tgl->year . '-' . str_pad($tgl->month, 2, '0', STR_PAD_LEFT);
            $labels[] = $namaBulan[$tgl->month] . ' ' . $tgl->year;
            $values[] = (float) ($rows[$key]->total ?? 0);
        }

        return ['labels' => $labels, 'values' => $values];
    }
}
