<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('senat_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('nama_akun', 100);
            $table->string('bank', 50);
            $table->string('nomor_rekening', 30)->unique();
            $table->string('nama_pemilik', 100);
            $table->boolean('is_aktif')->default(true);
            $table->text('keterangan')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('senat_accounts');
    }
};
