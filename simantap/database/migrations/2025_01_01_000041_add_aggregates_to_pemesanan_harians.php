<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pemesanan_harian', function (Blueprint $table) {
            $table->unsignedInteger('total_porsi_diterima')->nullable()->after('jumlah_porsi');
            $table->unsignedInteger('total_porsi_taruna')->nullable()->after('total_porsi_diterima');
            $table->unsignedInteger('total_porsi_redistribusi')->default(0)->after('total_porsi_taruna');
        });
    }

    public function down(): void
    {
        if (\Illuminate\Support\Facades\DB::getDriverName() !== 'sqlite') {
            Schema::table('pemesanan_harian', function (Blueprint $table) {
                $table->dropColumn(['total_porsi_diterima', 'total_porsi_taruna', 'total_porsi_redistribusi']);
            });
        }
    }
};
