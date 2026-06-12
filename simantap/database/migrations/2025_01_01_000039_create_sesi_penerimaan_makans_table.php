<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sesi_penerimaan_makans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pemesanan_harian_id')->constrained('pemesanan_harian')->cascadeOnDelete();
            $table->date('tanggal');
            $table->string('sesi', 10); // sarapan, siang, malam
            $table->unsignedInteger('porsi_dipesan');
            $table->unsignedInteger('porsi_diterima')->nullable();
            $table->unsignedInteger('porsi_dimakan_taruna')->nullable();
            $table->unsignedInteger('porsi_redistribusi')->default(0);
            $table->unsignedInteger('porsi_sisa')->default(0);
            $table->json('redistribusi_detail')->nullable();
            $table->string('kondisi_makanan', 20)->default('baik'); // baik, kurang_baik, buruk
            $table->text('catatan_kondisi')->nullable();
            $table->timestamp('waktu_serah_terima')->nullable();
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->json('foto')->nullable();
            $table->string('status', 20)->default('menunggu'); // menunggu, diterima, ada_masalah
            $table->foreignId('diterima_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('diterima_at')->nullable();
            $table->timestamps();

            $table->unique(['pemesanan_harian_id', 'sesi'], 'unique_sesi_per_pemesanan');
            $table->index('tanggal');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sesi_penerimaan_makans');
    }
};
