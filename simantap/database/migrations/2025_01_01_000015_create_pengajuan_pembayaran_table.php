<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengajuan_pembayaran', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_pengajuan', 50)->unique();
            $table->unsignedTinyInteger('periode_bulan');
            $table->year('periode_tahun');
            $table->unsignedInteger('total_taruna')->default(0);
            $table->unsignedInteger('total_porsi')->default(0);
            $table->decimal('total_nilai', 15, 2)->default(0);
            $table->enum('status', [
                'draft',
                'diproses_ppk',
                'disetujui_kpa',
                'permohonan_kppn',
                'sp2d',
                'transfer_kppn',
                'debit_bank',
                'transfer_penyedia',
                'konfirmasi_penyedia',
                'lpj_ppk',
                'lpj_kpa',
                'selesai',
            ])->default('draft');
            $table->string('nomor_sp2d', 50)->nullable();
            $table->date('tanggal_sp2d')->nullable();
            $table->string('invoice_penyedia', 255)->nullable();
            $table->string('bukti_transfer_kppn', 255)->nullable();
            $table->string('bukti_debit_bank', 255)->nullable();
            $table->string('bukti_transfer_penyedia', 255)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['periode_bulan', 'periode_tahun']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengajuan_pembayaran');
    }
};
