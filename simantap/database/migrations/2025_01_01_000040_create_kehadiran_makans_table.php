<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kehadiran_makans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sesi_id')->constrained('sesi_penerimaan_makans')->cascadeOnDelete();
            $table->foreignId('taruna_id')->constrained('taruna')->cascadeOnDelete();
            $table->boolean('hadir')->default(true);
            $table->string('sumber', 20)->default('manual'); // fingerprint, scan_nit, manual
            $table->timestamp('waktu_scan')->nullable();
            $table->foreignId('diinput_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->unique(['sesi_id', 'taruna_id'], 'unique_kehadiran_per_sesi');
            $table->index('hadir');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kehadiran_makans');
    }
};
