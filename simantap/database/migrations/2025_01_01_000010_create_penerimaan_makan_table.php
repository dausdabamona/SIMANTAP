<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penerimaan_makan', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->foreignId('taruna_id')->constrained('taruna')->cascadeOnDelete();
            $table->enum('jenis_makan', ['sarapan', 'makan_siang', 'makan_malam']);
            $table->unsignedTinyInteger('jumlah_porsi_diterima')->default(1);
            $table->enum('status_eligibilitas', ['dapat', 'tidak_dapat'])->default('dapat');
            $table->text('alasan_pengecualian')->nullable();
            $table->string('file_lampiran_pengecualian', 255)->nullable();
            $table->decimal('lat', 10, 8)->nullable();
            $table->decimal('long', 11, 8)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['taruna_id', 'tanggal', 'jenis_makan']);
            $table->index('tanggal');
            $table->index('status_eligibilitas');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penerimaan_makan');
    }
};
