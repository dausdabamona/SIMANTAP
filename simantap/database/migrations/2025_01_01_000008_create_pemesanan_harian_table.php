<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pemesanan_harian', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->foreignId('kontrak_id')->constrained('kontrak_makan')->cascadeOnDelete();
            $table->unsignedInteger('jumlah_taruna_hadir');
            $table->unsignedInteger('jumlah_porsi');
            $table->decimal('nilai_total', 15, 2);
            $table->enum('status', [
                'draft',
                'diverifikasi_pembina',
                'dikirim_penyedia',
                'perubahan',
                'disajikan',
                'selesai',
            ])->default('draft');
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tanggal', 'kontrak_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pemesanan_harian');
    }
};
