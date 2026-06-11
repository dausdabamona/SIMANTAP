<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // MODIFY COLUMN is MySQL-only syntax.
        // SQLite (used in tests) stores enums as TEXT and does not enforce values,
        // so no ALTER is needed — new values work out of the box.
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE rekap_bulanan MODIFY COLUMN status ENUM(
                'draft',
                'disetujui_wadir',
                'dihitung_ppk',
                'ditandatangani_pembina',
                'ditandatangani_ppk',
                'ditandatangani_kpa',
                'final'
            ) NOT NULL DEFAULT 'draft'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE rekap_bulanan MODIFY COLUMN status ENUM(
                'draft',
                'dihitung_ppk',
                'ditandatangani_pembina',
                'ditandatangani_ppk',
                'ditandatangani_kpa',
                'final'
            ) NOT NULL DEFAULT 'draft'");
        }
    }
};
