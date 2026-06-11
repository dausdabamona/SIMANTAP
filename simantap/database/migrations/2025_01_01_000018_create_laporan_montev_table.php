<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laporan_montev', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('periode_bulan');
            $table->year('periode_tahun');
            $table->text('menu_dievaluasi');
            $table->decimal('nilai_gizi_rata', 5, 2)->nullable();
            $table->text('catatan_prosedur')->nullable();
            $table->text('hasil_evaluasi');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['periode_bulan', 'periode_tahun']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan_montev');
    }
};
