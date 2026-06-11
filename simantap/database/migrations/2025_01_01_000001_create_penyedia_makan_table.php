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
            $table->string('nama_perusahaan');
            $table->string('nama_pic');
            $table->string('telepon', 20);
            $table->string('email')->nullable();
            $table->text('alamat');
            $table->string('npwp', 30)->nullable();
            $table->string('no_rekening', 50)->nullable();
            $table->string('nama_bank', 50)->nullable();
            $table->string('nama_pemilik_rekening')->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('aktif');
            $table->index('nama_perusahaan');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penyedia_makan');
    }
};
