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
            $table->string('nomor_kontrak', 100)->unique();
            $table->date('tanggal_kontrak');
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->decimal('nilai_kontrak', 15, 2);
            $table->decimal('harga_porsi', 10, 2)->comment('Harga per porsi dalam rupiah');
            // Koreksi #2: pihak kontrak = Senat Taruna, PPK hanya menyetujui
            $table->string('pihak_pertama', 255)->default('Senat Taruna Politeknik KP Sorong')
                ->comment('Pihak yang menandatangani kontrak — Senat Taruna, bukan satker');
            $table->foreignId('penyedia_id')->constrained('penyedia_makan')->restrictOnDelete();
            $table->enum('status', ['draft', 'aktif', 'berakhir', 'dibatalkan'])->default('draft');
            // Dokumen kontrak
            $table->string('file_kontrak', 255)->nullable()->comment('PDF kontrak asli');
            $table->string('file_addendum', 255)->nullable()->comment('PDF addendum terakhir');
            $table->string('file_berita_acara_penunjukan', 255)->nullable();
            $table->string('file_notulensi_rapat', 255)->nullable();
            // Koreksi #2: PPK hanya menyetujui — bukan pihak kontrak
            $table->foreignId('disetujui_ppk_id')->nullable()->constrained('users')->nullOnDelete()
                ->comment('PPK yang menyetujui kontrak ini');
            $table->date('tgl_persetujuan_ppk')->nullable();
            $table->text('catatan')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index(['tanggal_mulai', 'tanggal_selesai']);
            $table->index('penyedia_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kontrak_makan');
    }
};
