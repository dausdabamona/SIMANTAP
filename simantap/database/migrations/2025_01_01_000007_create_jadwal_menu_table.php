<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jadwal_menu', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->enum('jenis_makan', ['sarapan', 'makan_siang', 'makan_malam']);
            $table->text('menu');
            $table->text('nilai_gizi')->nullable();
            $table->tinyInteger('porsi_per_taruna')->unsigned()->default(1);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tanggal', 'jenis_makan']);
            $table->index('tanggal');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jadwal_menu');
    }
};
