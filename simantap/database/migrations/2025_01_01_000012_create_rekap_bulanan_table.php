<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rekap_bulanan', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('periode_bulan');
            $table->year('periode_tahun');
            $table->foreignId('taruna_id')->constrained('taruna')->cascadeOnDelete();
            $table->unsignedInteger('total_porsi')->default(0);
            $table->decimal('nilai_bantuan', 15, 2)->default(0);
            $table->foreignId('kontrak_id')->constrained('kontrak_makan')->cascadeOnDelete();
            $table->enum('status', [
                'draft',
                'disetujui_wadir',
                'dihitung_ppk',
                'ditandatangani_pembina',
                'ditandatangani_ppk',
                'ditandatangani_kpa',
                'final',
            ])->default('draft');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['taruna_id', 'periode_bulan', 'periode_tahun']);
            $table->index(['periode_bulan', 'periode_tahun']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rekap_bulanan');
    }
};
