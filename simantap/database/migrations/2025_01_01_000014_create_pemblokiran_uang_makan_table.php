<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pemblokiran_uang_makan', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('periode_bulan');
            $table->year('periode_tahun');
            $table->foreignId('taruna_id')->constrained('taruna')->cascadeOnDelete();
            $table->enum('status', ['diusulkan', 'diblokir', 'didebit'])->default('diusulkan');
            $table->string('nomor_surat', 100)->nullable();
            $table->string('bukti_debit', 255)->nullable();
            $table->foreignId('target_rekening_id')->constrained('senat_accounts')->cascadeOnDelete();
            $table->decimal('jumlah', 15, 2)->default(0);
            $table->text('keterangan')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['taruna_id', 'periode_bulan', 'periode_tahun']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pemblokiran_uang_makan');
    }
};
