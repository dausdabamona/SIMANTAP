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

    // Profil & Password
    Route::get('/profile',    [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',  [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/password/change', [ProfileController::class, 'editPassword'])->name('password.change');

    // ── Master Data ──────────────────────────────────────────
    Route::resource('taruna',          \App\Http\Controllers\TarunaController::class);
    Route::resource('rekening-taruna', \App\Http\Controllers\RekeningTarunaController::class);
    Route::resource('penyedia',        \App\Http\Controllers\PenyediaMakanController::class);
    Route::resource('kontrak',         \App\Http\Controllers\KontrakMakanController::class);
    Route::resource('sk-penerima',     \App\Http\Controllers\SkPenerimaController::class);
    Route::resource('jadwal-menu',     \App\Http\Controllers\JadwalMenuController::class);

    // ── Operasional Harian ───────────────────────────────────
    Route::resource('pemesanan',   \App\Http\Controllers\PemesananHarianController::class);
    Route::resource('penerimaan',  \App\Http\Controllers\PenerimaanMakanController::class);
    Route::resource('monitoring',  \App\Http\Controllers\MonitoringController::class);

    // ── Rekap & Pembayaran ───────────────────────────────────
    Route::resource('rekap',       \App\Http\Controllers\RekapBulananController::class);
    Route::resource('pemblokiran', \App\Http\Controllers\PemblokiranController::class);
    Route::resource('pembayaran',  \App\Http\Controllers\PengajuanPembayaranController::class);

    // ── Laporan ──────────────────────────────────────────────
    Route::prefix('laporan')->name('laporan.')->group(function () {
        Route::get('/taruna',    fn () => view('laporan.taruna'))->name('taruna');
        Route::get('/rekap',     fn () => view('laporan.rekap'))->name('rekap');
        Route::get('/pembayaran',fn () => view('laporan.pembayaran'))->name('pembayaran');
        Route::get('/monitoring',fn () => view('laporan.monitoring'))->name('monitoring');
    });

    // ── Monev & Audit ────────────────────────────────────────
    Route::resource('montev',    \App\Http\Controllers\MonteVController::class);
    Route::get('/audit-log',     \App\Http\Controllers\AuditLogController::class)->name('audit-log.index');

    // ── Pengaturan ───────────────────────────────────────────
    Route::resource('users',     \App\Http\Controllers\UserController::class);
    Route::resource('pagu',      \App\Http\Controllers\PaguAnggaranController::class);
});

require __DIR__.'/auth.php';
