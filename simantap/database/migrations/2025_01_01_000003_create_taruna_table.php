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
            $table->string('nit', 20)->unique()->comment('Nomor Induk Taruna');
            $table->string('nama', 255);
            $table->string('nik', 20)->nullable()->comment('NIK KTP');
            $table->year('angkatan')->comment('Tahun angkatan masuk');
            $table->string('prodi', 100)->comment('Program Studi');
            $table->string('kelas', 10)->nullable();
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->enum('status_taruna', [
                'aktif',
                'cuti',
                'pesiar',
                'sakit_di_kampus',
                'sakit_di_rumah_keluarga',
                'penundaan_studi',
            ])->default('aktif');
            $table->boolean('penerima_bantuan')->default(true)
                ->comment('Ditetapkan berdasarkan SK KPA/Kepala BPPSDMKP');
            $table->string('foto', 255)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('angkatan');
            $table->index('prodi');
            $table->index('status_taruna');
            $table->index('penerima_bantuan');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('taruna');
    }
};
