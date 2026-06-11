<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Define all permissions
        $permissions = [
            'taruna.view',
            'taruna.create',
            'taruna.edit',
            'taruna.delete',
            'taruna.import',
            'rekening.manage',
            'penyedia.manage',
            'kontrak.view',
            'kontrak.create',
            'kontrak.setujui',
            'sk_penerima.manage',
            'jadwal_menu.manage',
            'pemesanan.create',
            'pemesanan.verifikasi',
            'pemesanan.kirim',
            'penerimaan.input',
            'penerimaan.geotagging',
            'rekap.hitung',
            'rekap.tandatangani_pembina',
            'rekap.tandatangani_ppk',
            'rekap.tandatangani_kpa',
            'pemblokiran.usulkan',
            'pemblokiran.proses',
            'pembayaran.create',
            'pembayaran.proses_ppk',
            'pembayaran.setujui_kpa',
            'pembayaran.input_sp2d',
            'pembayaran.konfirmasi_transfer',
            'laporan.view',
            'laporan.export',
            'montev.manage',
            'auditlog.view',
            'pagu.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Create roles and assign permissions
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions($permissions); // all permissions

        $kpa = Role::firstOrCreate(['name' => 'kpa', 'guard_name' => 'web']);
        $kpa->syncPermissions([
            'taruna.view',
            'kontrak.view',
            'kontrak.setujui',
            'sk_penerima.manage',
            'rekap.tandatangani_kpa',
            'pemblokiran.proses',
            'pembayaran.setujui_kpa',
            'laporan.view',
            'laporan.export',
            'montev.manage',
            'pagu.manage',
        ]);

        $ppk = Role::firstOrCreate(['name' => 'ppk', 'guard_name' => 'web']);
        $ppk->syncPermissions([
            'taruna.view',
            'penyedia.manage',
            'kontrak.view',
            'kontrak.create',
            'rekap.hitung',
            'rekap.tandatangani_ppk',
            'pemblokiran.usulkan',
            'pembayaran.create',
            'pembayaran.proses_ppk',
            'pembayaran.input_sp2d',
            'pembayaran.konfirmasi_transfer',
            'laporan.view',
            'laporan.export',
            'montev.manage',
        ]);

        $pembinaKarakter = Role::firstOrCreate(['name' => 'pembina_karakter', 'guard_name' => 'web']);
        $pembinaKarakter->syncPermissions([
            'taruna.view',
            'kontrak.view',
            'pemesanan.verifikasi',
            'rekap.tandatangani_pembina',
            'laporan.view',
            'montev.manage',
        ]);

        $senatTaruna = Role::firstOrCreate(['name' => 'senat_taruna', 'guard_name' => 'web']);
        $senatTaruna->syncPermissions([
            'taruna.view',
            'rekening.manage',
            'kontrak.view',
            'jadwal_menu.manage',
            'pemesanan.create',
            'pemesanan.kirim',
            'penerimaan.input',
            'penerimaan.geotagging',
            'laporan.view',
        ]);

        $auditor = Role::firstOrCreate(['name' => 'auditor', 'guard_name' => 'web']);
        $auditor->syncPermissions([
            'taruna.view',
            'kontrak.view',
            'laporan.view',
            'laporan.export',
            'auditlog.view',
        ]);

        $viewer = Role::firstOrCreate(['name' => 'viewer', 'guard_name' => 'web']);
        $viewer->syncPermissions([
            'taruna.view',
            'kontrak.view',
            'laporan.view',
        ]);
    }
}
