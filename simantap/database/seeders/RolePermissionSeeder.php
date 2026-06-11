<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // ── Semua permission ──────────────────────────────────────────
        $allPermissions = [
            // Taruna
            'taruna.view', 'taruna.create', 'taruna.edit', 'taruna.delete',
            'taruna.import', 'taruna.export',

            // Rekening taruna
            'rekening.view', 'rekening-taruna.create', 'rekening-taruna.edit',

            // SK Penerima
            'sk_penerima.view', 'sk-penerima.create', 'sk-penerima.edit',

            // Penyedia
            'penyedia.view', 'penyedia.create', 'penyedia.edit',

            // Kontrak
            'kontrak.view', 'kontrak.create', 'kontrak.edit',

            // Jadwal menu
            'jadwal_menu.view', 'jadwal-menu.create',

            // Pemesanan harian
            'pemesanan.view', 'pemesanan.create', 'pemesanan.ttd-senat',
            'pemesanan.verifikasi', 'pemesanan.kirim',

            // Penerimaan makan
            'penerimaan.view', 'penerimaan.create',

            // Monitoring foto
            'monitoring_foto.view', 'monitoring.create', 'monitoring.delete',

            // Rekap bulanan
            'rekap.view', 'rekap.hitung', 'rekap.verifikasi_nilai',
            'rekap.setujui',
            'rekap.tandatangani_pembina', 'rekap.tandatangani_ppk',
            'rekap.tandatangani_kpa', 'rekap.finalize',

            // Pemblokiran
            'pemblokiran.view', 'pemblokiran.create', 'pemblokiran.proses',

            // Pembayaran LS
            'pembayaran.view', 'pembayaran.create',
            'pembayaran.proses_ppk', 'pembayaran.setujui_kpa',
            'pembayaran.permohonan_kppn', 'pembayaran.input_sp2d',
            'pembayaran.upload', 'pembayaran.konfirmasi',
            'pembayaran.lpj', 'pembayaran.selesai',

            // Pagu DIPA
            'pagu.view', 'pagu.create', 'pagu.manage',

            // Senat account
            'senat_account.view', 'senat_account.kelola',

            // Transfer (aliran uang)
            'transfer.senat.mengetahui', 'transfer.penyedia.setujui',
            'transfer.konfirmasi',

            // Laporan BAMA
            'laporan_bama.buat', 'laporan_bama.view',
            'laporan_bama.finalisasi', 'laporan_bama.setujui',

            // Kegiatan luar kampus
            'kegiatan_luar.view', 'kegiatan_luar.buat', 'kegiatan_luar.usulkan',
            'kegiatan_luar.verifikasi', 'kegiatan_luar.setujui',

            // Pembayaran kegiatan luar
            'pembayaran_luar.view', 'pembayaran_luar.usulkan',
            'pembayaran_luar.proses', 'pembayaran_luar.input_sp2d',

            // Penyedia — pesanan & invoice
            'pesanan.lihat', 'pesanan.konfirmasi',
            'invoice.upload',

            // Kaprodi — peserta luar
            'peserta_luar.input', 'daftar_hadir_luar.upload',

            // Dashboard khusus per role
            'dashboard.wadir', 'dashboard.penyedia', 'dashboard.kaprodi',

            // Persetujuan pusdik
            'persetujuan_pusdik.input',

            // Monev
            'montev.view', 'montev.create', 'montev.update', 'montev.delete',

            // Laporan umum
            'laporan.view',

            // Audit log
            'auditlog.view',

            // User management
            'user.manage', 'users.create',
        ];

        foreach ($allPermissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // ── super_admin — semua permission ───────────────────────────
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions($allPermissions);

        // ── kpa (Direktur / KPA) ─────────────────────────────────────
        $kpa = Role::firstOrCreate(['name' => 'kpa', 'guard_name' => 'web']);
        $kpa->syncPermissions([
            'taruna.view', 'taruna.export',
            'rekening.view',
            'sk_penerima.view',
            'penyedia.view',
            'kontrak.view',
            'pemesanan.view',
            'penerimaan.view',
            'rekap.view', 'rekap.tandatangani_kpa', 'rekap.finalize',
            'pemblokiran.view',
            'pembayaran.view', 'pembayaran.setujui_kpa', 'pembayaran.selesai',
            'pagu.view',
            'senat_account.view',
            'laporan_bama.view', 'laporan_bama.setujui',
            'kegiatan_luar.view', 'kegiatan_luar.setujui',
            'pembayaran_luar.view',
            'persetujuan_pusdik.input',
            'montev.view',
            'laporan.view',
            'auditlog.view',
        ]);

        // ── ppk ──────────────────────────────────────────────────────
        $ppk = Role::firstOrCreate(['name' => 'ppk', 'guard_name' => 'web']);
        $ppk->syncPermissions([
            'taruna.view', 'taruna.export',
            'rekening.view',
            'sk_penerima.view',
            'penyedia.view', 'penyedia.create', 'penyedia.edit',
            'kontrak.view', 'kontrak.create', 'kontrak.edit',
            'jadwal_menu.view',
            'pemesanan.view', 'pemesanan.kirim',
            'penerimaan.view',
            'rekap.view', 'rekap.hitung', 'rekap.verifikasi_nilai',
            'rekap.tandatangani_ppk',
            'pemblokiran.view', 'pemblokiran.proses',
            'pembayaran.view', 'pembayaran.create',
            'pembayaran.proses_ppk', 'pembayaran.permohonan_kppn',
            'pembayaran.input_sp2d', 'pembayaran.upload',
            'pembayaran.konfirmasi', 'pembayaran.lpj',
            'pagu.view', 'pagu.manage',
            'senat_account.view', 'senat_account.kelola',
            'laporan_bama.view', 'laporan_bama.buat', 'laporan_bama.finalisasi',
            'kegiatan_luar.view', 'kegiatan_luar.verifikasi',
            'pembayaran_luar.view', 'pembayaran_luar.proses',
            'pembayaran_luar.input_sp2d',
            'montev.view', 'montev.create', 'montev.update', 'montev.delete',
            'laporan.view',
        ]);

        // ── pembina_karakter ─────────────────────────────────────────
        $pembinaKarakter = Role::firstOrCreate(['name' => 'pembina_karakter', 'guard_name' => 'web']);
        $pembinaKarakter->syncPermissions([
            'taruna.view',
            'kontrak.view',
            'jadwal_menu.view',
            'pemesanan.view', 'pemesanan.verifikasi',
            'penerimaan.view',
            'rekap.view', 'rekap.tandatangani_pembina',
            'pemblokiran.view',
            'montev.view', 'montev.create', 'montev.update',
            'laporan.view',
        ]);

        // ── senat_taruna ─────────────────────────────────────────────
        $senatTaruna = Role::firstOrCreate(['name' => 'senat_taruna', 'guard_name' => 'web']);
        $senatTaruna->syncPermissions([
            'taruna.view',
            'rekening.view', 'rekening-taruna.create', 'rekening-taruna.edit',
            'sk_penerima.view',
            'kontrak.view',
            'jadwal_menu.view', 'jadwal-menu.create',
            'pemesanan.view', 'pemesanan.create', 'pemesanan.ttd-senat',
            'penerimaan.view', 'penerimaan.create',
            'monitoring_foto.view', 'monitoring.create', 'monitoring.delete',
            'rekap.view',
            'pemblokiran.view', 'pemblokiran.create',
            'laporan.view',
        ]);

        // ── wadir_iii (Wakil Direktur III) ───────────────────────────
        $wadirIii = Role::firstOrCreate(['name' => 'wadir_iii', 'guard_name' => 'web']);
        $wadirIii->syncPermissions([
            'taruna.view', 'taruna.export',
            'rekening.view',
            'sk_penerima.view',
            'penyedia.view',
            'kontrak.view',
            'pemesanan.view',
            'penerimaan.view',
            'rekap.view', 'rekap.setujui',
            'pemblokiran.view',
            'pembayaran.view',
            'pagu.view',
            'senat_account.view',
            'transfer.senat.mengetahui', 'transfer.penyedia.setujui',
            'laporan_bama.buat', 'laporan_bama.view',
            'kegiatan_luar.view',
            'pembayaran_luar.view',
            'dashboard.wadir',
            'laporan.view',
        ]);

        // ── penyedia (akun login untuk mitra penyedia makan) ─────────
        $penyedia = Role::firstOrCreate(['name' => 'penyedia', 'guard_name' => 'web']);
        $penyedia->syncPermissions([
            'pesanan.lihat', 'pesanan.konfirmasi',
            'invoice.upload',
            'transfer.konfirmasi',
            'dashboard.penyedia',
        ]);

        // ── kaprodi ──────────────────────────────────────────────────
        $kaprodi = Role::firstOrCreate(['name' => 'kaprodi', 'guard_name' => 'web']);
        $kaprodi->syncPermissions([
            'taruna.view',
            'kegiatan_luar.buat', 'kegiatan_luar.usulkan',
            'peserta_luar.input', 'daftar_hadir_luar.upload',
            'pembayaran_luar.usulkan',
            'dashboard.kaprodi',
        ]);

        // ── auditor ──────────────────────────────────────────────────
        $auditor = Role::firstOrCreate(['name' => 'auditor', 'guard_name' => 'web']);
        $auditor->syncPermissions([
            'taruna.view', 'taruna.export',
            'rekening.view',
            'penyedia.view',
            'kontrak.view',
            'pemesanan.view',
            'penerimaan.view',
            'rekap.view',
            'pemblokiran.view',
            'pembayaran.view',
            'pagu.view',
            'senat_account.view',
            'montev.view',
            'laporan.view',
            'auditlog.view',
        ]);

        // ── viewer ───────────────────────────────────────────────────
        $viewer = Role::firstOrCreate(['name' => 'viewer', 'guard_name' => 'web']);
        $viewer->syncPermissions([
            'taruna.view',
            'kontrak.view',
            'pemesanan.view',
            'penerimaan.view',
            'rekap.view',
            'pembayaran.view',
            'laporan.view',
        ]);
    }
}
