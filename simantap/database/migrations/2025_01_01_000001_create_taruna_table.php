<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('taruna', function (Blueprint $table) {
            $table->id();
            $table->string('nit', 20)->unique();
            $table->string('nama', 100);
            $table->string('nik', 16)->unique()->nullable();
            $table->year('angkatan');
            $table->string('prodi', 100);
            $table->string('kelas', 20)->nullable();
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->enum('status_taruna', [
                'aktif',
                'cuti',
                'pesiar',
                'sakit_di_kampus',
                'sakit_di_rumah_keluarga',
                'penundaan_studi',
            ])->default('aktif');
            $table->boolean('penerima_bantuan')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status_taruna', 'penerima_bantuan']);
            $table->index('angkatan');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('taruna');
    }
};
