<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rekening_taruna', function (Blueprint $table) {
            $table->string('bank_group', 10)->nullable()->after('bank')
                ->comment('BSI, BNI, MANDIRI — diisi otomatis dari field bank saat create');
            $table->foreignId('senat_account_id')->nullable()->after('bank_group')
                ->constrained('senat_accounts')->nullOnDelete()
                ->comment('Rekening Senat tujuan debit otomatis — diisi berdasarkan tingkat taruna');

            $table->index('bank_group');
            $table->index('senat_account_id');
        });
    }

    public function down(): void
    {
        Schema::table('rekening_taruna', function (Blueprint $table) {
            $table->dropForeign(['senat_account_id']);
            $table->dropColumn(['bank_group', 'senat_account_id']);
        });
    }
};
