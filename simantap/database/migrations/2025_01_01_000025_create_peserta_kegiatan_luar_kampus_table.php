<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('peserta_kegiatan_luar_kampus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kegiatan_id')->constrained('kegiatan_luar_kampus')->cascadeOnDelete();
            $table->foreignId('taruna_id')->constrained('taruna')->restrictOnDelete();
            $table->unsignedSmallInteger('hari_hadir')->default(0);
            $table->decimal('nilai_bantuan', 15, 2)->default(0);
            $table->string('file_daftar_hadir')->nullable();
            $table->timestamps();

            $table->unique(['kegiatan_id', 'taruna_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('peserta_kegiatan_luar_kampus');
    }
};
