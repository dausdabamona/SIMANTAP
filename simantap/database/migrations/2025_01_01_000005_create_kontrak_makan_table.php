<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kontrak_makan', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_kontrak', 50)->unique();
            $table->date('tanggal_kontrak');
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->decimal('nilai_kontrak', 15, 2);
            $table->decimal('harga_porsi', 10, 2);
            $table->string('pihak_pertama', 100)->default('Senat Taruna');
            $table->foreignId('penyedia_id')->constrained('penyedia_makan')->cascadeOnDelete();
            $table->enum('status', ['draft', 'aktif', 'berakhir', 'dibatalkan'])->default('draft');
            $table->string('file_kontrak', 255)->nullable();
            $table->string('file_addendum', 255)->nullable();
            $table->string('berita_acara_penunjukan', 255)->nullable();
            $table->string('notulensi_rapat', 255)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index(['tanggal_mulai', 'tanggal_selesai']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kontrak_makan');
    }
};
