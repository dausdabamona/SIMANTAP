<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_pembayaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengajuan_id')->constrained('pengajuan_pembayaran')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('status_dari', 50);
            $table->string('status_ke', 50);
            $table->string('aksi', 100);
            $table->text('catatan')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('pengajuan_id');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_pembayaran');
    }
};
