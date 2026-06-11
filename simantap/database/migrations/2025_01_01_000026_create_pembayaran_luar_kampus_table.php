<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembayaran_luar_kampus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kegiatan_id')->constrained('kegiatan_luar_kampus')->cascadeOnDelete();
            $table->enum('tahap', ['I', 'II', 'III']);
            $table->decimal('nilai_diajukan', 15, 2)->default(0);
            $table->decimal('nilai_disetujui', 15, 2)->default(0);
            $table->string('nomor_sp2d', 100)->nullable();
            $table->date('tanggal_sp2d')->nullable();
            $table->string('file_sp2d')->nullable();
            $table->enum('status', [
                'draft',
                'diverifikasi_ppk',
                'diajukan_kppn',
                'sp2d_terbit',
                'transfer_selesai',
                'dikonfirmasi_taruna',
            ])->default('draft');
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['kegiatan_id', 'tahap']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayaran_luar_kampus');
    }
};
