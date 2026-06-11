<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagu_anggaran', function (Blueprint $table) {
            $table->id();
            $table->year('tahun');
            $table->string('akun_belanja', 20);
            $table->decimal('nilai_pagu', 15, 2);
            $table->text('keterangan')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tahun', 'akun_belanja']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagu_anggaran');
    }
};
