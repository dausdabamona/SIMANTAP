<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rekap_bulanan', function (Blueprint $table) {
            $table->unsignedInteger('hari_hadir')->default(0)->after('total_porsi');
            $table->unsignedInteger('total_sesi')->default(0)->after('hari_hadir');
        });
    }

    public function down(): void
    {
        if (\Illuminate\Support\Facades\DB::getDriverName() !== 'sqlite') {
            Schema::table('rekap_bulanan', function (Blueprint $table) {
                $table->dropColumn(['hari_hadir', 'total_sesi']);
            });
        }
    }
};
