<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('berita_acara_perubahan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pemesanan_id')->constrained('pemesanan_harian')->cascadeOnDelete();
            $table->text('alasan');
            $table->text('solusi');
            $table->string('file', 255)->nullable();
            $table->enum('status', ['diajukan', 'disetujui', 'ditolak'])->default('diajukan');
            $table->text('catatan_penolakan')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['pemesanan_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('berita_acara_perubahan');
    }
};
