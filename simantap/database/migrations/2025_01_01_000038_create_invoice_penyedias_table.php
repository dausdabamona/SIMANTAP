<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_penyedias', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('periode_bulan');
            $table->unsignedSmallInteger('periode_tahun');
            $table->foreignId('penyedia_id')->constrained('penyedia_makan');
            $table->string('nomor_invoice', 50)->nullable();
            $table->date('tanggal_invoice')->nullable();
            $table->decimal('total_nilai', 15, 2)->default(0);
            $table->string('file_invoice')->nullable();
            $table->string('status', 30)->default('menunggu')
                ->comment('menunggu, diterima, diverifikasi_ppk');
            $table->text('catatan')->nullable();
            $table->foreignId('diverifikasi_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('diverifikasi_at')->nullable();
            $table->timestamps();

            $table->unique(['periode_bulan', 'periode_tahun', 'penyedia_id'], 'unique_invoice_penyedia_periode');
            $table->index(['periode_bulan', 'periode_tahun']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_penyedias');
    }
};
