<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pemblokiran_uang_makan', function (Blueprint $table) {
            // Pemblokiran berubah dari per-taruna → per bank_group per periode
            // taruna_id dijadikan nullable (data lama tetap valid)
            $table->string('bank_group', 10)->nullable()->after('periode_tahun')
                ->comment('BSI atau BNI — satu surat pemblokiran per bank per periode');
            $table->unsignedInteger('jumlah_taruna_terdampak')->nullable()->after('bank_group');
            $table->decimal('total_nilai_diblokir', 15, 2)->nullable()->after('jumlah_taruna_terdampak');

            $table->index(['periode_bulan', 'periode_tahun', 'bank_group'], 'idx_pemblokiran_periode_bank');
        });

        // Buat taruna_id nullable (SQLite tidak support modify — skip)
        if (DB::getDriverName() !== 'sqlite') {
            Schema::table('pemblokiran_uang_makan', function (Blueprint $table) {
                $table->foreignId('taruna_id')->nullable()->change();
            });
        }

        // Drop unique lama (taruna_id, periode_bulan, periode_tahun)
        if (DB::getDriverName() !== 'sqlite') {
            Schema::table('pemblokiran_uang_makan', function (Blueprint $table) {
                $table->dropUnique('unique_pemblokiran');
                // Tambah unique baru: satu pemblokiran per bank_group per periode
                $table->unique(['periode_bulan', 'periode_tahun', 'bank_group'], 'unique_pemblokiran_bank');
            });
        }
    }

    public function down(): void
    {
        Schema::table('pemblokiran_uang_makan', function (Blueprint $table) {
            $table->dropIndex('idx_pemblokiran_periode_bank');
            $table->dropColumn(['bank_group', 'jumlah_taruna_terdampak', 'total_nilai_diblokir']);
        });

        if (DB::getDriverName() !== 'sqlite') {
            Schema::table('pemblokiran_uang_makan', function (Blueprint $table) {
                $table->dropUnique('unique_pemblokiran_bank');
                $table->unique(['taruna_id', 'periode_bulan', 'periode_tahun'], 'unique_pemblokiran');
            });
        }
    }
};
