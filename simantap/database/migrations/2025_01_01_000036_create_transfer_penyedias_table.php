<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transfer_penyedias', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('periode_bulan');
            $table->unsignedSmallInteger('periode_tahun');
            $table->string('bank_group', 10);
            $table->foreignId('senat_account_id')->constrained('senat_accounts');
            $table->foreignId('rekening_penyedia_id')->constrained('rekening_penyedias');
            $table->decimal('total_nilai', 15, 2)->default(0);
            $table->unsignedTinyInteger('jumlah_kelas')->default(0);
            $table->unsignedInteger('jumlah_taruna')->default(0);
            $table->date('tanggal_transfer')->nullable();
            $table->string('bukti_transfer')->nullable();
            $table->string('status', 30)->default('menunggu')
                ->comment('menunggu, disetujui_wadir, ditransfer, dikonfirmasi_penyedia');
            $table->text('catatan')->nullable();
            $table->foreignId('disetujui_wadir_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('disetujui_wadir_at')->nullable();
            $table->foreignId('ditransfer_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('dikonfirmasi_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('dikonfirmasi_at')->nullable();
            $table->timestamps();

            $table->unique(['periode_bulan', 'periode_tahun', 'bank_group'], 'unique_transfer_bank_periode');
            $table->index(['periode_bulan', 'periode_tahun']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transfer_penyedias');
    }
};
