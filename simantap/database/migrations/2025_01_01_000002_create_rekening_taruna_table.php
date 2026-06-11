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
            $table->foreignId('taruna_id')->constrained('taruna')->cascadeOnDelete();
            $table->string('bank', 50);
            $table->string('nomor_rekening', 30)->unique();
            $table->string('nama_pemilik', 100);
            $table->timestamps();
            $table->softDeletes();

            $table->index('taruna_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rekening_taruna');
    }
};
