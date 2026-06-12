<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('senat_accounts', function (Blueprint $table) {
            $table->string('bank_group', 10)->nullable()->after('bank')
                ->comment('BSI, BNI, MANDIRI, dll — digunakan untuk routing debit per tingkat');
            $table->string('untuk_tingkat', 30)->nullable()->after('bank_group')
                ->comment('Deskriptif: Tingkat I / Tingkat II & III');
        });
    }

    public function down(): void
    {
        Schema::table('senat_accounts', function (Blueprint $table) {
            $table->dropColumn(['bank_group', 'untuk_tingkat']);
        });
    }
};
