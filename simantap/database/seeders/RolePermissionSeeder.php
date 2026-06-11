<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Master Taruna
            'taruna.view', 'taruna.create', 'taruna.edit', 'taruna.delete', 'taruna.import',
            // Rekening
            'rekening.view', 'rekening.manage',
            // Penyedia
            'penyedia.view', 'penyedia.manage',
            // Kontrak
            'kontrak.view', 'kontrak.create', 'kontrak.edit', 'kontrak.delete', 'kontrak.setujui',
            // SK Penerima
            'sk_penerima.view', 'sk_penerima.manage',
            // Jadwal Menu
            'jadwal_menu.view', 'jadwal_menu.manage',
            // Pemesanan Harian
            'pemesanan.view', 'pemesanan.create', 'pemesanan.edit',
            'pemesanan.verifikasi', 'pemesanan.kirim',
            // Penerimaan & Geotagging
            'penerimaan.view', 'penerimaan.input', 'penerimaan.geotagging',
            // Monitoring Foto
            'monitoring_foto.view', 'monitoring_foto.upload',
            // Rekap Bulanan (rantai ttd: Pembina → PPK → KPA)
            'rekap.view', 'rekap.hitung',
            'rekap.tandatangani_pembina', 'rekap.tandatangani_ppk', 'rekap.tandatangani_kpa',
            // Pemblokiran Uang Makan
            'pemblokiran.view', 'pemblokiran.usulkan', 'pemblokiran.proses',
            // Pengajuan Pembayaran LS
            'pembayaran.view', 'pembayaran.create',
            'pembayaran.proses_ppk', 'pembayaran.setujui_kpa',
            'pembayaran.input_sp2d', 'pembayaran.konfirmasi_transfer', 'pembayaran.konfirmasi_lpj',
            // Pagu Anggaran DIPA
            'pagu.view', 'pagu.manage',
            // Laporan & Ekspor
            'laporan.view', 'laporan.export',
            // Monitoring & Evaluasi
            'montev.view', 'montev.manage',
            // Audit Log
            'auditlog.view',
            // Manajemen User
            'user.view', 'user.manage',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // =========================================================
        // MATRIKS ROLE × PERMISSION
        // =========================================================

        // super_admin — full access
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions($permissions);

        // kpa — Kuasa Pengguna Anggaran
        $kpa = Role::firstOrCreate(['name' => 'kpa', 'guard_name' => 'web']);
        $kpa->syncPermissions([
            'taruna.view',
            'rekening.view', 'penyedia.view', 'kontrak.view', 'kontrak.setujui',
            'sk_penerima.view', 'jadwal_menu.view',
            'pemesanan.view', 'penerimaan.view', 'monitoring_foto.view',
            'rekap.view', 'rekap.tandatangani_kpa',
            'pemblokiran.view',
            'pembayaran.view', 'pembayaran.setujui_kpa', 'pembayaran.konfirmasi_lpj',
            'pagu.view', 'pagu.manage',
            'laporan.view', 'laporan.export',
            'montev.view', 'auditlog.view',
            'user.view',
        ]);

        // ppk — Pejabat Pembuat Komitmen
        $ppk = Role::firstOrCreate(['name' => 'ppk', 'guard_name' => 'web']);
        $ppk->syncPermissions([
            'taruna.view', 'taruna.create', 'taruna.edit', 'taruna.delete', 'taruna.import',
            'rekening.view', 'rekening.manage',
            'penyedia.view', 'penyedia.manage',
            'kontrak.view', 'kontrak.create', 'kontrak.edit', 'kontrak.delete', 'kontrak.setujui',
            'sk_penerima.view', 'sk_penerima.manage',
            'jadwal_menu.view', 'jadwal_menu.manage',
            'pemesanan.view',
            'penerimaan.view', 'monitoring_foto.view',
            'rekap.view', 'rekap.hitung', 'rekap.tandatangani_ppk',
            'pemblokiran.view', 'pemblokiran.proses',
            'pembayaran.view', 'pembayaran.create', 'pembayaran.proses_ppk',
            'pembayaran.input_sp2d', 'pembayaran.konfirmasi_transfer', 'pembayaran.konfirmasi_lpj',
            'pagu.view',
            'laporan.view', 'laporan.export',
            'montev.view', 'montev.manage',
            'auditlog.view',
            'user.view',
        ]);

        // pembina_karakter — Pembina Karakter
        $pembina = Role::firstOrCreate(['name' => 'pembina_karakter', 'guard_name' => 'web']);
        $pembina->syncPermissions([
            'taruna.view',
            'rekening.view', 'penyedia.view', 'kontrak.view',
            'sk_penerima.view', 'jadwal_menu.view', 'jadwal_menu.manage',
            'pemesanan.view', 'pemesanan.verifikasi',
            'penerimaan.view',
            'monitoring_foto.view', 'monitoring_foto.upload',
            'rekap.view', 'rekap.tandatangani_pembina',
            'pemblokiran.view', 'pemblokiran.usulkan',
            'pembayaran.view',
            'laporan.view', 'laporan.export',
            'montev.view',
        ]);

        // senat_taruna — Senat Taruna
        $senat = Role::firstOrCreate(['name' => 'senat_taruna', 'guard_name' => 'web']);
        $senat->syncPermissions([
            'taruna.view',
            'rekening.view', 'penyedia.view', 'kontrak.view',
            'jadwal_menu.view',
            'pemesanan.view', 'pemesanan.create', 'pemesanan.edit', 'pemesanan.kirim',
            'penerimaan.view', 'penerimaan.input', 'penerimaan.geotagging',
            'monitoring_foto.view', 'monitoring_foto.upload',
            'rekap.view',
            'pemblokiran.view', 'pemblokiran.usulkan',
            'pembayaran.view',
            'laporan.view',
        ]);

        // auditor — Auditor (read-only + penuh audit log)
        $auditor = Role::firstOrCreate(['name' => 'auditor', 'guard_name' => 'web']);
        $auditor->syncPermissions([
            'taruna.view', 'rekening.view', 'penyedia.view', 'kontrak.view',
            'sk_penerima.view', 'jadwal_menu.view',
            'pemesanan.view', 'penerimaan.view', 'monitoring_foto.view',
            'rekap.view', 'pemblokiran.view',
            'pembayaran.view', 'pagu.view',
            'laporan.view', 'laporan.export',
            'montev.view', 'auditlog.view',
        ]);

        // viewer — Read-only terbatas
        $viewer = Role::firstOrCreate(['name' => 'viewer', 'guard_name' => 'web']);
        $viewer->syncPermissions([
            'taruna.view', 'kontrak.view', 'sk_penerima.view',
            'pemesanan.view', 'rekap.view', 'pembayaran.view',
            'laporan.view',
        ]);
    }
}
