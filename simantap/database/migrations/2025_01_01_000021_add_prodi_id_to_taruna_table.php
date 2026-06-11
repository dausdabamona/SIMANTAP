<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('taruna', function (Blueprint $table) {
            $table->foreignId('prodi_id')
                ->nullable()
                ->after('kelas')
                ->constrained('prodis')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('taruna', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\Prodi::class);
            $table->dropColumn('prodi_id');
        });
    }
};
