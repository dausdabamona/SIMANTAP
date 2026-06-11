<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('nip', 30)->nullable()->after('name')->comment('NIP ASN/pegawai');
            $table->string('jabatan', 100)->nullable()->after('nip');
            $table->string('telepon', 20)->nullable()->after('jabatan');
            $table->string('avatar', 255)->nullable()->after('telepon');
            $table->boolean('is_active')->default(true)->after('avatar');
            $table->timestamp('last_login_at')->nullable()->after('is_active');
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['nip', 'jabatan', 'telepon', 'avatar', 'is_active', 'last_login_at', 'deleted_at']);
        });
    }
};
