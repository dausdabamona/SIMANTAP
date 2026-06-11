<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rekap_bulanan_approval', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rekap_bulanan_id')->constrained('rekap_bulanan')->cascadeOnDelete();
            $table->enum('role', ['pembina', 'ppk', 'kpa']);
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->dateTime('signed_at');
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->unique(['rekap_bulanan_id', 'role']);
            $table->index('rekap_bulanan_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rekap_bulanan_approval');
    }
};
