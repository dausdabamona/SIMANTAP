<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rekening_taruna', function (Blueprint $table) {
            $table->id();
            $table->foreignId('taruna_id')->unique()->constrained('taruna')->cascadeOnDelete();
            $table->string('bank', 100)->comment('Nama bank');
            $table->string('nomor_rekening', 50)->unique()->comment('Nomor rekening individual taruna');
            $table->string('nama_pemilik', 255)->comment('Nama pemilik sesuai buku tabungan');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rekening_taruna');
    }
};
