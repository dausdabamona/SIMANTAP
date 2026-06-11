<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laporan_bamas', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('periode_bulan');
            $table->year('periode_tahun');
            $table->enum('status', [
                'draft',
                'disetujui_wadir',
                'disetujui_kpa',
                'dikirim_pusdik',
            ])->default('draft');
            $table->text('ringkasan_eksekutif')->nullable();
            $table->json('rekomendasi')->nullable();
            $table->json('permasalahan')->nullable();
            $table->string('file_pdf')->nullable();
            $table->string('file_docx')->nullable();
            $table->string('tautan_gdrive')->nullable();
            $table->foreignId('dibuat_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('disetujui_wadir_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('disetujui_wadir_at')->nullable();
            $table->foreignId('disetujui_kpa_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('disetujui_kpa_at')->nullable();
            $table->timestamp('dikirim_pusdik_at')->nullable();
            $table->timestamps();

            $table->unique(['periode_bulan', 'periode_tahun']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan_bamas');
    }
};
