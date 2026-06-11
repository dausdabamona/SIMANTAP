<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kegiatan_luar_kampus', function (Blueprint $table) {
            $table->id();
            $table->string('kode_kegiatan', 20)->unique();
            $table->string('nama_kegiatan');
            $table->text('deskripsi')->nullable();
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->string('lokasi');
            $table->enum('jenis_kegiatan', ['pkl', 'praktek_lapangan', 'seminar', 'kunjungan', 'lainnya']);
            $table->foreignId('kaprodi_id')->constrained('users')->restrictOnDelete();
            $table->decimal('standar_biaya_per_hari', 15, 2)->default(0);
            $table->decimal('total_nilai_diusulkan', 15, 2)->default(0);
            $table->decimal('total_nilai_disetujui', 15, 2)->default(0);
            $table->string('nomor_surat_pusdik', 100)->nullable();
            $table->date('tanggal_surat_pusdik')->nullable();
            $table->string('file_surat_pusdik')->nullable();
            $table->enum('status', [
                'draft',
                'diusulkan_kaprodi',
                'disetujui_direktur',
                'menunggu_persetujuan_pusdik',
                'disetujui_pusdik',
                'proses_pembayaran',
                'selesai',
                'dibatalkan',
            ])->default('draft');
            $table->text('catatan_penolakan')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index(['tanggal_mulai', 'tanggal_selesai']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kegiatan_luar_kampus');
    }
};
