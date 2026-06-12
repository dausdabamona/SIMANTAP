<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengajuan_pembayaran', function (Blueprint $table) {
            $table->string('kelas', 20)->nullable()->after('periode_tahun')
                ->comment('Kelas taruna: X-A, XI-B, XII-A, dst');
            $table->unsignedTinyInteger('tingkat')->nullable()->after('kelas')
                ->comment('Snapshot tingkat saat SPM dibuat: 1, 2, atau 3');
            $table->string('bank_group', 10)->nullable()->after('tingkat')
                ->comment('BSI, BNI — diisi otomatis dari bank taruna di kelas ini');
            $table->foreignId('rekening_senat_id')->nullable()->after('bank_group')
                ->constrained('senat_accounts')->nullOnDelete()
                ->comment('Rekening Senat tujuan transfer untuk kelas ini');

            // Index untuk query agregasi multi-kelas per bulan
            $table->index(['periode_bulan', 'periode_tahun', 'bank_group'], 'idx_pp_periode_bank');
        });

        // Tambah unique constraint: 1 SPM per kelas per periode
        if (DB::getDriverName() !== 'sqlite') {
            Schema::table('pengajuan_pembayaran', function (Blueprint $table) {
                $table->unique(['periode_bulan', 'periode_tahun', 'kelas'], 'unique_spm_kelas');
            });
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            Schema::table('pengajuan_pembayaran', function (Blueprint $table) {
                $table->dropUnique('unique_spm_kelas');
            });
        }

        Schema::table('pengajuan_pembayaran', function (Blueprint $table) {
            $table->dropIndex('idx_pp_periode_bank');
            $table->dropForeign(['rekening_senat_id']);
            $table->dropColumn(['kelas', 'tingkat', 'bank_group', 'rekening_senat_id']);
        });
    }
};
