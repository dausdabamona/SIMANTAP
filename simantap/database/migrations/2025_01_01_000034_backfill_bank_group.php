<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Backfill bank_group di rekening_taruna berdasarkan field bank yang sudah ada
        DB::table('rekening_taruna')->whereNull('deleted_at')->orderBy('id')->each(function ($r) {
            $bankUpper = strtoupper($r->bank ?? '');
            $bankGroup = str_contains($bankUpper, 'BSI') ? 'BSI'
                : (str_contains($bankUpper, 'BNI') ? 'BNI'
                : (str_contains($bankUpper, 'MANDIRI') ? 'MANDIRI'
                : (str_contains($bankUpper, 'BRI') ? 'BRI'
                : 'LAINNYA')));

            DB::table('rekening_taruna')->where('id', $r->id)
                ->update(['bank_group' => $bankGroup]);
        });
    }

    public function down(): void
    {
        DB::table('rekening_taruna')->update(['bank_group' => null]);
    }
};
