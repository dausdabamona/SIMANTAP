<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transfer_penyedia_spms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transfer_penyedia_id')->constrained('transfer_penyedias')->cascadeOnDelete();
            $table->foreignId('pengajuan_id')->constrained('pengajuan_pembayaran')->cascadeOnDelete();
            $table->decimal('nilai_kontribusi', 15, 2)->default(0);
            $table->timestamps();

            $table->unique(['transfer_penyedia_id', 'pengajuan_id'], 'unique_transfer_spm');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transfer_penyedia_spms');
    }
};
