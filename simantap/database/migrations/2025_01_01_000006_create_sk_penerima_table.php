<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sk_penerima', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_sk', 100)->unique();
            $table->string('judul', 200);
            $table->string('penerbit', 100);
            $table->date('tanggal_sk');
            $table->date('periode_mulai');
            $table->date('periode_selesai');
            $table->enum('jenis_sk', ['sk_kpa', 'sk_kepala_bppsdmkp'])->default('sk_kpa');
            $table->string('file_sk', 255)->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['periode_mulai', 'periode_selesai']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sk_penerima');
    }
};
