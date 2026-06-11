<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Redirect root ke login atau dashboard
Route::get('/', fn () => auth()->check()
    ? redirect()->route('dashboard')
    : redirect()->route('login')
);

// Dashboard
Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ── Dashboard per Role ───────────────────────────────────
    Route::middleware('role:super_admin')->prefix('admin')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'superAdmin'])->name('dashboard.super_admin');
    });
    Route::middleware('role:kpa')->prefix('kpa')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'kpa'])->name('dashboard.kpa');
    });
    Route::middleware('role:ppk')->prefix('ppk')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'ppk'])->name('dashboard.ppk');
    });
    Route::middleware('role:wadir_iii')->prefix('wadir')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'wadirIii'])->name('dashboard.wadir_iii');
    });
    Route::middleware('role:pembina_karakter')->prefix('pembina')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'pembinaKarakter'])->name('dashboard.pembina');
    });
    Route::middleware('role:senat_taruna')->prefix('senat')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'senatTaruna'])->name('dashboard.senat');
    });
    Route::middleware('role:kaprodi')->prefix('kaprodi')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'kaprodi'])->name('dashboard.kaprodi');
    });
    Route::middleware('role:penyedia')->prefix('penyedia')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'penyedia'])->name('dashboard.penyedia');
    });
    Route::middleware('role:auditor')->prefix('auditor')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'auditor'])->name('dashboard.auditor');
    });

    // Profil & Password
    Route::get('/profile',    [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',  [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/password/change', [ProfileController::class, 'editPassword'])->name('password.change');

    // ── Master Data ──────────────────────────────────────────
    Route::resource('taruna', \App\Http\Controllers\TarunaController::class);
    Route::post('taruna/{id}/restore',  [\App\Http\Controllers\TarunaController::class, 'restore'])->name('taruna.restore');
    Route::post('taruna/import',        [\App\Http\Controllers\TarunaController::class, 'import'])->name('taruna.import');
    Route::get('taruna/export',         [\App\Http\Controllers\TarunaController::class, 'export'])->name('taruna.export');
    Route::get('taruna/template',       [\App\Http\Controllers\TarunaController::class, 'template'])->name('taruna.template');
    Route::resource('rekening-taruna', \App\Http\Controllers\RekeningTarunaController::class);
    Route::resource('penyedia',        \App\Http\Controllers\PenyediaMakanController::class);
    Route::resource('kontrak', \App\Http\Controllers\KontrakMakanController::class);
    Route::patch('kontrak/{kontrak}/status', [\App\Http\Controllers\KontrakMakanController::class, 'updateStatus'])->name('kontrak.status');
    Route::resource('sk-penerima',     \App\Http\Controllers\SkPenerimaController::class);
    Route::resource('jadwal-menu',     \App\Http\Controllers\JadwalMenuController::class);

    // ── Operasional Harian ───────────────────────────────────
    Route::resource('pemesanan', \App\Http\Controllers\PemesananHarianController::class);
    Route::post('pemesanan/{pemesanan}/ttd-senat',    [\App\Http\Controllers\PemesananHarianController::class, 'tandatanganiSenat'])->name('pemesanan.ttd-senat');
    Route::post('pemesanan/{pemesanan}/verifikasi',   [\App\Http\Controllers\PemesananHarianController::class, 'verifikasiPembina'])->name('pemesanan.verifikasi');
    Route::post('pemesanan/{pemesanan}/kirim',        [\App\Http\Controllers\PemesananHarianController::class, 'kirimPenyedia'])->name('pemesanan.kirim');
    Route::resource('penerimaan',  \App\Http\Controllers\PenerimaanMakanController::class);
    Route::resource('monitoring',  \App\Http\Controllers\MonitoringController::class);

    // ── Rekap & Pembayaran ───────────────────────────────────
    Route::resource('rekap', \App\Http\Controllers\RekapBulananController::class);
    Route::post('rekap/hitung',                       [\App\Http\Controllers\RekapBulananController::class, 'hitungPeriode'])->name('rekap.hitung');
    Route::post('rekap/{rekap}/setujui-wadir',         [\App\Http\Controllers\RekapBulananController::class, 'setujuiWadir'])->name('rekap.setujui-wadir');
    Route::post('rekap/{rekap}/tandatangan',          [\App\Http\Controllers\RekapBulananController::class, 'tandatangan'])->name('rekap.tandatangan');
    Route::post('rekap/{rekap}/finalize',             [\App\Http\Controllers\RekapBulananController::class, 'finalize'])->name('rekap.finalize');
    Route::resource('pemblokiran', \App\Http\Controllers\PemblokiranController::class);
    Route::post('pemblokiran/{pemblokiranUangMakan}/proses', [\App\Http\Controllers\PemblokiranController::class, 'proses'])->name('pemblokiran.proses');
    Route::resource('pembayaran', \App\Http\Controllers\PengajuanPembayaranController::class);
    Route::post('pembayaran/{pembayaran}/transisi', [\App\Http\Controllers\PengajuanPembayaranController::class, 'transisi'])->name('pembayaran.transisi');

    // ── PDF Generation ──────────────────────────────────────
    Route::prefix('pdf')->name('pdf.')->group(function () {
        Route::get('rekap-bulanan/{rekap}',     [\App\Http\Controllers\PdfController::class, 'rekapBulanan'])->name('rekap-bulanan');
        Route::get('rekap-periode',             [\App\Http\Controllers\PdfController::class, 'rekapPeriode'])->name('rekap-periode');
        Route::get('pemesanan/{pemesanan}',     [\App\Http\Controllers\PdfController::class, 'pemesananHarian'])->name('pemesanan');
        Route::get('pengajuan/{pembayaran}',    [\App\Http\Controllers\PdfController::class, 'pengajuanPembayaran'])->name('pengajuan-pembayaran');
        Route::get('pemblokiran/{pemblokiran}', [\App\Http\Controllers\PdfController::class, 'pemblokiran'])->name('pemblokiran');
    });

    // ── Laporan ──────────────────────────────────────────────
    Route::prefix('laporan')->name('laporan.')->group(function () {
        Route::get('/taruna',    fn () => view('laporan.taruna'))->name('taruna');
        Route::get('/rekap',     fn () => view('laporan.rekap'))->name('rekap');
        Route::get('/pembayaran',fn () => view('laporan.pembayaran'))->name('pembayaran');
        Route::get('/monitoring',fn () => view('laporan.monitoring'))->name('monitoring');
    });

    // ── Kegiatan Luar Kampus ─────────────────────────────────
    Route::resource('kegiatan-luar', \App\Http\Controllers\KegiatanLuarKampusController::class);
    Route::post('kegiatan-luar/{kegiatanLuar}/usulkan',        [\App\Http\Controllers\KegiatanLuarKampusController::class, 'usulkan'])->name('kegiatan-luar.usulkan');
    Route::post('kegiatan-luar/{kegiatanLuar}/setujui-direktur', [\App\Http\Controllers\KegiatanLuarKampusController::class, 'setujuiDirektur'])->name('kegiatan-luar.setujui-direktur');
    Route::post('kegiatan-luar/{kegiatanLuar}/ajukan-pusdik',  [\App\Http\Controllers\KegiatanLuarKampusController::class, 'ajukanPusdik'])->name('kegiatan-luar.ajukan-pusdik');
    Route::post('kegiatan-luar/{kegiatanLuar}/pusdik',         [\App\Http\Controllers\KegiatanLuarKampusController::class, 'inputPersetujuanPusdik'])->name('kegiatan-luar.pusdik');
    Route::post('kegiatan-luar/{kegiatanLuar}/batalkan',       [\App\Http\Controllers\KegiatanLuarKampusController::class, 'batalkan'])->name('kegiatan-luar.batalkan');

    // Peserta kegiatan luar kampus
    Route::post('kegiatan-luar/{kegiatanLuar}/peserta',                          [\App\Http\Controllers\PesertaKegiatanController::class, 'store'])->name('peserta-kegiatan.store');
    Route::patch('kegiatan-luar/{kegiatanLuar}/peserta/{peserta}/hadir',         [\App\Http\Controllers\PesertaKegiatanController::class, 'updateHadir'])->name('peserta-kegiatan.hadir');
    Route::delete('kegiatan-luar/{kegiatanLuar}/peserta/{peserta}',              [\App\Http\Controllers\PesertaKegiatanController::class, 'destroy'])->name('peserta-kegiatan.destroy');

    // Pembayaran luar kampus
    Route::resource('pembayaran-luar', \App\Http\Controllers\PembayaranLuarKampusController::class)->only(['index','show']);
    Route::post('kegiatan-luar/{kegiatanLuar}/pembayaran',                       [\App\Http\Controllers\PembayaranLuarKampusController::class, 'buat'])->name('pembayaran-luar.buat');
    Route::post('pembayaran-luar/{pembayaranLuar}/verifikasi-ppk',               [\App\Http\Controllers\PembayaranLuarKampusController::class, 'verifikasiPpk'])->name('pembayaran-luar.verifikasi-ppk');
    Route::post('pembayaran-luar/{pembayaranLuar}/ajukan-kppn',                  [\App\Http\Controllers\PembayaranLuarKampusController::class, 'ajukanKppn'])->name('pembayaran-luar.ajukan-kppn');
    Route::post('pembayaran-luar/{pembayaranLuar}/sp2d',                         [\App\Http\Controllers\PembayaranLuarKampusController::class, 'inputSp2d'])->name('pembayaran-luar.sp2d');
    Route::post('pembayaran-luar/{pembayaranLuar}/transfer',                     [\App\Http\Controllers\PembayaranLuarKampusController::class, 'konfirmasiTransfer'])->name('pembayaran-luar.transfer');
    Route::post('pembayaran-luar/{pembayaranLuar}/konfirmasi-taruna',            [\App\Http\Controllers\PembayaranLuarKampusController::class, 'konfirmasiTaruna'])->name('pembayaran-luar.konfirmasi-taruna');

    // ── Laporan BAMA ─────────────────────────────────────────
    Route::resource('laporan-bama', \App\Http\Controllers\LaporanBamaController::class);
    Route::post('laporan-bama/{laporanBama}/generate-pdf',    [\App\Http\Controllers\LaporanBamaController::class, 'generatePdf'])->name('laporan-bama.generate-pdf');
    Route::get('laporan-bama/{laporanBama}/download-docx',    [\App\Http\Controllers\LaporanBamaController::class, 'generateDocx'])->name('laporan-bama.download-docx');
    Route::post('laporan-bama/{laporanBama}/setujui-wadir',   [\App\Http\Controllers\LaporanBamaController::class, 'setujuiWadir'])->name('laporan-bama.setujui-wadir');
    Route::post('laporan-bama/{laporanBama}/setujui-kpa',     [\App\Http\Controllers\LaporanBamaController::class, 'setujuiKpa'])->name('laporan-bama.setujui-kpa');
    Route::post('laporan-bama/{laporanBama}/kirim-pusdik',    [\App\Http\Controllers\LaporanBamaController::class, 'kirimPusdik'])->name('laporan-bama.kirim-pusdik');

    // ── Transfer Monitor ─────────────────────────────────────
    Route::get('transfer-monitor', [\App\Http\Controllers\TransferMonitorController::class, 'index'])->name('transfer-monitor.index');
    Route::post('transfer-monitor/{pembayaran}/mengetahui', [\App\Http\Controllers\TransferMonitorController::class, 'mengetahuiKppn'])->name('transfer-monitor.mengetahui');
    Route::post('transfer-monitor/{pembayaran}/setujui-penyedia', [\App\Http\Controllers\TransferMonitorController::class, 'setujuiTransferPenyedia'])->name('transfer-monitor.setujui-penyedia');

    // ── Portal Penyedia ──────────────────────────────────────
    Route::middleware('role:penyedia')->prefix('portal-penyedia')->name('penyedia.')->group(function () {
        Route::get('/pesanan',                    [\App\Http\Controllers\PenyediaController::class, 'pesanan'])->name('pesanan');
        Route::post('/pesanan/{id}/konfirmasi',   [\App\Http\Controllers\PenyediaController::class, 'konfirmasiPesanan'])->name('pesanan.konfirmasi');
        Route::get('/invoice',                    [\App\Http\Controllers\PenyediaController::class, 'invoice'])->name('invoice');
        Route::post('/invoice/upload',            [\App\Http\Controllers\PenyediaController::class, 'uploadInvoice'])->name('invoice.upload');
        Route::get('/pembayaran',                 [\App\Http\Controllers\PenyediaController::class, 'pembayaran'])->name('pembayaran');
        Route::post('/pembayaran/{id}/konfirmasi',[\App\Http\Controllers\PenyediaController::class, 'konfirmasiTransfer'])->name('pembayaran.konfirmasi');
    });

    // ── Monev & Audit ────────────────────────────────────────
    Route::resource('montev',    \App\Http\Controllers\MonteVController::class);
    Route::get('/audit-log',     \App\Http\Controllers\AuditLogController::class)->name('audit-log.index');

    // ── Pengaturan ───────────────────────────────────────────
    Route::resource('users', \App\Http\Controllers\UserController::class);
    Route::post('users/{id}/restore', [\App\Http\Controllers\UserController::class, 'restore'])->name('users.restore');
    Route::resource('pagu',      \App\Http\Controllers\PaguAnggaranController::class);
});

require __DIR__.'/auth.php';
