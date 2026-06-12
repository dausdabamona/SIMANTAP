<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Migrasi data rekening yang sudah ada ke tabel baru.
        // Gunakan user ID 1 (super_admin) sebagai dibuat_by default.
        $fallbackUserId = DB::table('users')->value('id') ?? 1;

        DB::table('penyedia_makan')->whereNull('deleted_at')->orderBy('id')->each(function ($p) use ($fallbackUserId) {
            if (empty($p->bank) && empty($p->nomor_rekening)) {
                return;
            }
            DB::table('rekening_penyedias')->insert([
                'penyedia_id'      => $p->id,
                'label'            => 'Rekening Utama',
                'bank'             => $p->bank ?? '',
                'nomor_rekening'   => $p->nomor_rekening ?? '',
                'nama_pemilik'     => $p->nama_pemilik_rekening ?? $p->nama,
                'is_active'        => true,
                'is_default'       => true,
                'berlaku_mulai'    => null,
                'berlaku_sampai'   => null,
                'alasan_perubahan' => null,
                'dibuat_by'        => $fallbackUserId,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);
        });

        if (DB::getDriverName() === 'sqlite') {
            // SQLite tidak support dropColumn — buat kolom nullable agar tidak error saat insert tanpa field ini
            Schema::table('penyedia_makan', function (Blueprint $table) {
                $table->string('bank', 50)->nullable()->change();
                $table->string('nomor_rekening', 30)->nullable()->change();
                $table->string('nama_pemilik_rekening', 100)->nullable()->change();
            });
        } else {
            Schema::table('penyedia_makan', function (Blueprint $table) {
                $table->dropColumn(['bank', 'nomor_rekening', 'nama_pemilik_rekening']);
            });
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            Schema::table('penyedia_makan', function (Blueprint $table) {
                $table->string('bank', 50)->after('email')->default('');
                $table->string('nomor_rekening', 30)->after('bank')->default('');
                $table->string('nama_pemilik_rekening', 100)->nullable()->after('nomor_rekening');
            });
        }
    }
};
