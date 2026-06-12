<?php

namespace App\Http\Controllers;

use App\Models\InvoicePenyedia;
use App\Models\KontrakMakan;
use App\Models\LaporanBama;
use App\Models\PaguAnggaran;
use App\Models\PengajuanPembayaran;
use App\Models\RekapBulanan;
use App\Models\SesiPenerimaanMakan;
use App\Models\Taruna;
use App\Models\TransferPenyedia;
use App\Exports\Laporan\TarunaLaporanExport;
use App\Exports\Laporan\RekapBulananLaporanExport;
use App\Exports\Laporan\PembayaranLsLaporanExport;
use App\Exports\Laporan\MonitoringLaporanExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class LaporanController extends Controller
{
    // ── Laporan Taruna ───────────────────────────────────────

    public function taruna(Request $request)
    {
        abort_unless(auth()->check(), 403);

        $tahun        = (int) $request->get('tahun', now()->year);
        $angkatan     = $request->get('angkatan');
        $prodi        = $request->get('prodi');
        $status_taruna = $request->get('status_taruna');

        $query = Taruna::query();

        if ($angkatan) {
            $query->where('angkatan', $angkatan);
        }
        if ($prodi) {
            $query->where('prodi', $prodi);
        }
        if ($status_taruna) {
            $query->where('status_taruna', $status_taruna);
        }

        $taruna = $query->orderBy('angkatan')->orderBy('kelas')->orderBy('nama')->get();

        $stats = [
            'total_aktif'        => $taruna->where('status_taruna', 'aktif')->count(),
            'total_penerima'     => $taruna->where('penerima_bantuan', true)->count(),
            'total_tidak_eligible' => $taruna->filter(fn ($t) => !$t->is_eligible_bantuan)->count(),
            'total_angkatan'     => $taruna->pluck('angkatan')->unique()->count(),
            'total_per_angkatan' => $taruna->groupBy('angkatan')->map->count(),
            'total_per_prodi'    => $taruna->groupBy('prodi')->map->count(),
        ];

        $angkatanList = Taruna::select('angkatan')->distinct()->orderBy('angkatan', 'desc')->pluck('angkatan');
        $prodiList    = Taruna::select('prodi')->distinct()->orderBy('prodi')->pluck('prodi');

        $filter = compact('tahun', 'angkatan', 'prodi', 'status_taruna');

        return view('laporan.taruna', compact('taruna', 'stats', 'filter', 'angkatanList', 'prodiList'));
    }

    public function tarunaPdf(Request $request)
    {
        abort_unless(auth()->check(), 403);

        $tahun        = (int) $request->get('tahun', now()->year);
        $angkatan     = $request->get('angkatan');
        $prodi        = $request->get('prodi');
        $status_taruna = $request->get('status_taruna');

        $query = Taruna::query();
        if ($angkatan) $query->where('angkatan', $angkatan);
        if ($prodi)    $query->where('prodi', $prodi);
        if ($status_taruna) $query->where('status_taruna', $status_taruna);

        $taruna = $query->orderBy('angkatan')->orderBy('nama')->get();
        $generatedAt = now()->format('d/m/Y H:i');

        $pdf = Pdf::loadView('pdf.laporan.taruna', compact('taruna', 'tahun', 'generatedAt'));
        $pdf->setPaper('a4', 'landscape');

        return $pdf->download("laporan-taruna-{$tahun}.pdf");
    }

    public function tarunaExcel(Request $request)
    {
        abort_unless(auth()->check(), 403);

        $tahun        = (int) $request->get('tahun', now()->year);
        $angkatan     = $request->get('angkatan');
        $prodi        = $request->get('prodi');
        $status_taruna = $request->get('status_taruna');

        return Excel::download(
            new TarunaLaporanExport($tahun, $angkatan, $prodi, $status_taruna),
            "laporan-taruna-{$tahun}.xlsx"
        );
    }

    // ── Laporan Rekap Bulanan ────────────────────────────────

    public function rekap(Request $request)
    {
        abort_unless(auth()->check(), 403);

        $bulan      = (int) $request->get('bulan', now()->month);
        $tahun      = (int) $request->get('tahun', now()->year);
        $kontrak_id = $request->get('kontrak_id');

        $query = RekapBulanan::with('taruna')
            ->where('periode_bulan', $bulan)
            ->where('periode_tahun', $tahun);

        if ($kontrak_id) {
            $query->where('kontrak_id', $kontrak_id);
        }

        $rekaps      = $query->orderBy('id')->get();
        $totalNilai  = $rekaps->sum('nilai_bantuan');
        $kontrakList = KontrakMakan::orderBy('id', 'desc')->get();
        $filter      = compact('bulan', 'tahun', 'kontrak_id');

        return view('laporan.rekap', compact('rekaps', 'totalNilai', 'filter', 'kontrakList'));
    }

    public function rekapPdf(Request $request)
    {
        abort_unless(auth()->check(), 403);

        $bulan      = (int) $request->get('bulan', now()->month);
        $tahun      = (int) $request->get('tahun', now()->year);
        $kontrak_id = $request->get('kontrak_id');

        $query = RekapBulanan::with('taruna')
            ->where('periode_bulan', $bulan)
            ->where('periode_tahun', $tahun);
        if ($kontrak_id) $query->where('kontrak_id', $kontrak_id);

        $rekaps      = $query->orderBy('id')->get();
        $totalNilai  = $rekaps->sum('nilai_bantuan');
        $generatedAt = now()->format('d/m/Y H:i');

        $pdf = Pdf::loadView('pdf.laporan.rekap-bulanan', compact('rekaps', 'totalNilai', 'bulan', 'tahun', 'generatedAt'));
        $pdf->setPaper('a4', 'landscape');

        return $pdf->download("laporan-rekap-{$bulan}-{$tahun}.pdf");
    }

    public function rekapExcel(Request $request)
    {
        abort_unless(auth()->check(), 403);

        $bulan      = (int) $request->get('bulan', now()->month);
        $tahun      = (int) $request->get('tahun', now()->year);
        $kontrak_id = $request->get('kontrak_id');

        return Excel::download(
            new RekapBulananLaporanExport($bulan, $tahun, $kontrak_id),
            "laporan-rekap-{$bulan}-{$tahun}.xlsx"
        );
    }

    // ── Laporan Pembayaran LS ────────────────────────────────

    public function pembayaran(Request $request)
    {
        abort_unless(auth()->check(), 403);

        $tahun = (int) $request->get('tahun', now()->year);

        $pengajuanAll = PengajuanPembayaran::where('periode_tahun', $tahun)->get();
        $pagu         = PaguAnggaran::byTahun($tahun)->first();
        $realisasi    = $pengajuanAll->where('status', 'selesai')->sum('total_nilai');

        $transferAll = TransferPenyedia::where('periode_tahun', $tahun)->get();
        $invoiceAll  = InvoicePenyedia::where('periode_tahun', $tahun)->get();
        $bamaAll     = LaporanBama::where('periode_tahun', $tahun)->get();

        $namaBulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        $rows = [];
        for ($m = 1; $m <= 12; $m++) {
            $pengajuanBulan  = $pengajuanAll->where('periode_bulan', $m);
            $transferBulan   = $transferAll->where('periode_bulan', $m);
            $invoiceBulan    = $invoiceAll->where('periode_bulan', $m);
            $bama            = $bamaAll->where('periode_bulan', $m)->first();

            $rows[] = [
                'bulan'                   => $namaBulan[$m],
                'bulan_ke'                => $m,
                'jumlah_pengajuan'        => $pengajuanBulan->count(),
                'total_nilai_pengajuan'   => $pengajuanBulan->sum('total_nilai'),
                'total_transfer_penyedia' => $transferBulan->sum('total_nilai'),
                'total_invoice_penyedia'  => $invoiceBulan->sum('total_nilai'),
                'status_laporan_bama'     => $bama ? $bama->status : null,
            ];
        }

        $filter = compact('tahun');

        return view('laporan.pembayaran', compact('rows', 'tahun', 'pagu', 'realisasi', 'filter'));
    }

    public function pembayaranPdf(Request $request)
    {
        abort_unless(auth()->check(), 403);

        $tahun = (int) $request->get('tahun', now()->year);

        $pengajuanAll = PengajuanPembayaran::where('periode_tahun', $tahun)->get();
        $pagu         = PaguAnggaran::byTahun($tahun)->first();
        $realisasi    = $pengajuanAll->where('status', 'selesai')->sum('total_nilai');

        $transferAll = TransferPenyedia::where('periode_tahun', $tahun)->get();
        $invoiceAll  = InvoicePenyedia::where('periode_tahun', $tahun)->get();
        $bamaAll     = LaporanBama::where('periode_tahun', $tahun)->get();

        $namaBulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        $rows = [];
        for ($m = 1; $m <= 12; $m++) {
            $pengajuanBulan  = $pengajuanAll->where('periode_bulan', $m);
            $transferBulan   = $transferAll->where('periode_bulan', $m);
            $invoiceBulan    = $invoiceAll->where('periode_bulan', $m);
            $bama            = $bamaAll->where('periode_bulan', $m)->first();

            $rows[] = [
                'bulan'                   => $namaBulan[$m],
                'bulan_ke'                => $m,
                'jumlah_pengajuan'        => $pengajuanBulan->count(),
                'total_nilai_pengajuan'   => $pengajuanBulan->sum('total_nilai'),
                'total_transfer_penyedia' => $transferBulan->sum('total_nilai'),
                'total_invoice_penyedia'  => $invoiceBulan->sum('total_nilai'),
                'status_laporan_bama'     => $bama ? $bama->status : null,
            ];
        }

        $generatedAt = now()->format('d/m/Y H:i');
        $pdf = Pdf::loadView('pdf.laporan.pembayaran-ls', compact('rows', 'tahun', 'pagu', 'realisasi', 'generatedAt'));
        $pdf->setPaper('a4', 'landscape');

        return $pdf->download("laporan-pembayaran-ls-{$tahun}.pdf");
    }

    public function pembayaranExcel(Request $request)
    {
        abort_unless(auth()->check(), 403);

        $tahun = (int) $request->get('tahun', now()->year);

        return Excel::download(
            new PembayaranLsLaporanExport($tahun),
            "laporan-pembayaran-ls-{$tahun}.xlsx"
        );
    }

    // ── Laporan Monitoring Sesi ──────────────────────────────

    public function monitoring(Request $request)
    {
        abort_unless(auth()->check(), 403);

        $tanggalDari    = $request->get('tanggal_dari', now()->startOfMonth()->format('Y-m-d'));
        $tanggalSampai  = $request->get('tanggal_sampai', now()->format('Y-m-d'));
        $sesiFilter     = $request->get('sesi');

        $query = SesiPenerimaanMakan::query()
            ->whereBetween('tanggal', [$tanggalDari, $tanggalSampai])
            ->orderBy('tanggal')
            ->orderBy('sesi');

        if ($sesiFilter && $sesiFilter !== 'semua') {
            $query->where('sesi', $sesiFilter);
        }

        $sesis = $query->get();

        $totalDiterima = $sesis->where('status', 'diterima')->count();
        $totalMasalah  = $sesis->where('status', 'ada_masalah')->count();
        $totalValid    = $sesis->filter(fn ($s) => $s->rekonsiliasiValid())->count();
        $pctValid      = $sesis->count() > 0 ? round($totalValid / $sesis->count() * 100, 1) : 0;

        $stats = [
            'total_sesi'              => $sesis->count(),
            'total_diterima'          => $totalDiterima,
            'total_masalah'           => $totalMasalah,
            'pct_rekonsiliasi_valid'  => $pctValid,
        ];

        $filter = [
            'tanggal_dari'   => $tanggalDari,
            'tanggal_sampai' => $tanggalSampai,
            'sesi'           => $sesiFilter,
        ];

        return view('laporan.monitoring', compact('sesis', 'stats', 'filter'));
    }

    public function monitoringPdf(Request $request)
    {
        abort_unless(auth()->check(), 403);

        $tanggalDari   = $request->get('tanggal_dari', now()->startOfMonth()->format('Y-m-d'));
        $tanggalSampai = $request->get('tanggal_sampai', now()->format('Y-m-d'));
        $sesiFilter    = $request->get('sesi');

        $query = SesiPenerimaanMakan::query()
            ->whereBetween('tanggal', [$tanggalDari, $tanggalSampai])
            ->orderBy('tanggal')->orderBy('sesi');
        if ($sesiFilter && $sesiFilter !== 'semua') $query->where('sesi', $sesiFilter);

        $sesis       = $query->get();
        $totalValid  = $sesis->filter(fn ($s) => $s->rekonsiliasiValid())->count();
        $pctValid    = $sesis->count() > 0 ? round($totalValid / $sesis->count() * 100, 1) : 0;

        $stats = [
            'total_sesi'             => $sesis->count(),
            'total_diterima'         => $sesis->where('status', 'diterima')->count(),
            'total_masalah'          => $sesis->where('status', 'ada_masalah')->count(),
            'pct_rekonsiliasi_valid' => $pctValid,
        ];

        $generatedAt = now()->format('d/m/Y H:i');
        $pdf = Pdf::loadView('pdf.laporan.monitoring', compact('sesis', 'stats', 'tanggalDari', 'tanggalSampai', 'generatedAt'));
        $pdf->setPaper('a4', 'landscape');

        return $pdf->download('laporan-monitoring.pdf');
    }

    public function monitoringExcel(Request $request)
    {
        abort_unless(auth()->check(), 403);

        $tanggalDari   = $request->get('tanggal_dari', now()->startOfMonth()->format('Y-m-d'));
        $tanggalSampai = $request->get('tanggal_sampai', now()->format('Y-m-d'));
        $sesiFilter    = $request->get('sesi');

        return Excel::download(
            new MonitoringLaporanExport($tanggalDari, $tanggalSampai, $sesiFilter),
            'laporan-monitoring.xlsx'
        );
    }

    // ── Money Bulanan (Ringkasan Keuangan) ───────────────────

    public function moneyBulanan(Request $request)
    {
        abort_unless(auth()->check(), 403);

        $tahun = (int) $request->get('tahun', now()->year);

        $pengajuanAll = PengajuanPembayaran::where('periode_tahun', $tahun)->get();
        $transferAll  = TransferPenyedia::where('periode_tahun', $tahun)->get();
        $invoiceAll   = InvoicePenyedia::where('periode_tahun', $tahun)->get();
        $bamaAll      = LaporanBama::where('periode_tahun', $tahun)->get();
        $pagu         = PaguAnggaran::byTahun($tahun)->first();

        $namaBulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        $rows = [];
        for ($m = 1; $m <= 12; $m++) {
            $pengajuanBulan = $pengajuanAll->where('periode_bulan', $m);
            $transferBulan  = $transferAll->where('periode_bulan', $m);
            $invoiceBulan   = $invoiceAll->where('periode_bulan', $m);
            $bama           = $bamaAll->where('periode_bulan', $m)->first();

            $rows[] = [
                'bulan'                   => $namaBulan[$m],
                'bulan_ke'                => $m,
                'jumlah_pengajuan'        => $pengajuanBulan->count(),
                'total_nilai_pengajuan'   => $pengajuanBulan->sum('total_nilai'),
                'total_transfer_penyedia' => $transferBulan->sum('total_nilai'),
                'total_invoice_penyedia'  => $invoiceBulan->sum('total_nilai'),
                'status_laporan_bama'     => $bama ? $bama->status : null,
            ];
        }

        return view('laporan.money-bulanan', compact('rows', 'tahun', 'pagu'));
    }

    public function moneyBulananPdf(Request $request)
    {
        abort_unless(auth()->check(), 403);

        $tahun = (int) $request->get('tahun', now()->year);

        $pengajuanAll = PengajuanPembayaran::where('periode_tahun', $tahun)->get();
        $transferAll  = TransferPenyedia::where('periode_tahun', $tahun)->get();
        $invoiceAll   = InvoicePenyedia::where('periode_tahun', $tahun)->get();
        $bamaAll      = LaporanBama::where('periode_tahun', $tahun)->get();
        $pagu         = PaguAnggaran::byTahun($tahun)->first();

        $namaBulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        $rows = [];
        for ($m = 1; $m <= 12; $m++) {
            $pengajuanBulan = $pengajuanAll->where('periode_bulan', $m);
            $transferBulan  = $transferAll->where('periode_bulan', $m);
            $invoiceBulan   = $invoiceAll->where('periode_bulan', $m);
            $bama           = $bamaAll->where('periode_bulan', $m)->first();

            $rows[] = [
                'bulan'                   => $namaBulan[$m],
                'bulan_ke'                => $m,
                'jumlah_pengajuan'        => $pengajuanBulan->count(),
                'total_nilai_pengajuan'   => $pengajuanBulan->sum('total_nilai'),
                'total_transfer_penyedia' => $transferBulan->sum('total_nilai'),
                'total_invoice_penyedia'  => $invoiceBulan->sum('total_nilai'),
                'status_laporan_bama'     => $bama ? $bama->status : null,
            ];
        }

        $generatedAt = now()->format('d/m/Y H:i');
        $pdf = Pdf::loadView('pdf.laporan.money-bulanan', compact('rows', 'tahun', 'pagu', 'generatedAt'));
        $pdf->setPaper('a4', 'landscape');

        return $pdf->download("laporan-money-bulanan-{$tahun}.pdf");
    }
}
