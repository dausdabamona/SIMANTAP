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
            $table->foreignId('kontrak_id')->constrained('kontrak_makan')->restrictOnDelete();
            $table->unsignedInteger('jumlah_taruna_hadir');
            // jumlah_porsi = jumlah_taruna_hadir × porsi_per_hari (default 3)
            $table->unsignedInteger('jumlah_porsi');
            $table->decimal('harga_porsi_snapshot', 10, 2)
                ->comment('Snapshot harga porsi dari kontrak saat pemesanan dibuat');
            $table->decimal('nilai_total', 15, 2)
                ->comment('= jumlah_porsi × harga_porsi_snapshot');
            // Menu Level 2: konfirmasi atau perubahan dari jadwal_menu (Level 1)
            $table->text('catatan_menu')->nullable()
                ->comment('Menu aktual yang dipesan — bisa berbeda dari jadwal_menu rencana');
            $table->boolean('menu_sesuai_jadwal')->default(true)
                ->comment('Flag: apakah menu sesuai jadwal kontrak');
            $table->enum('status', [
                'draft',
                'diverifikasi_pembina',
                'dikirim_penyedia',
                'perubahan',
                'disajikan',
                'selesai',
            ])->default('draft');
            $table->text('catatan')->nullable();
            // Tanda tangan Senat Taruna
            $table->foreignId('ttd_senat_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('ttd_senat_at')->nullable();
            // Verifikasi & tanda tangan Pembina Karakter
            $table->foreignId('ttd_pembina_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('ttd_pembina_at')->nullable();
            $table->text('catatan_pembina')->nullable();
            // Timestamp kirim ke penyedia (harus ≤ H-1)
            $table->timestamp('dikirim_at')->nullable();
            $table->foreignId('dikirim_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tanggal', 'kontrak_id'], 'unique_pemesanan_per_hari');
            $table->index('status');
            $table->index('tanggal');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pemesanan_harian');
    }
};
