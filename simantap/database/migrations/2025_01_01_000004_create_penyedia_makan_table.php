<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penyedia_makan', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 150);
            $table->string('npwp', 20)->unique();
            $table->text('alamat');
            $table->string('telp', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('bank', 50);
            $table->string('nomor_rekening', 30);
            $table->string('nama_pemilik_rekening', 100)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penyedia_makan');
    }
};
