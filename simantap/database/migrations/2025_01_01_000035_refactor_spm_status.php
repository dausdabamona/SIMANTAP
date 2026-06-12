<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Backfill: transfer_penyedia + konfirmasi_penyedia → debit_selesai
        DB::table('pengajuan_pembayaran')
            ->whereIn('status', ['transfer_penyedia', 'konfirmasi_penyedia'])
            ->update(['status' => 'debit_selesai']);

        // MySQL: ubah enum
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE pengajuan_pembayaran MODIFY COLUMN status ENUM(
                'draft','diproses_ppk','disetujui_kpa','permohonan_kppn',
                'sp2d','transfer_kppn','debit_bank','debit_selesai',
                'lpj_ppk','lpj_kpa','selesai'
            ) NOT NULL DEFAULT 'draft'");
        }
    }

    public function down(): void
    {
        // Restore: debit_selesai → transfer_penyedia
        DB::table('pengajuan_pembayaran')
            ->where('status', 'debit_selesai')
            ->update(['status' => 'transfer_penyedia']);

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE pengajuan_pembayaran MODIFY COLUMN status ENUM(
                'draft','diproses_ppk','disetujui_kpa','permohonan_kppn',
                'sp2d','transfer_kppn','debit_bank','transfer_penyedia',
                'konfirmasi_penyedia','lpj_ppk','lpj_kpa','selesai'
            ) NOT NULL DEFAULT 'draft'");
        }
    }
};
