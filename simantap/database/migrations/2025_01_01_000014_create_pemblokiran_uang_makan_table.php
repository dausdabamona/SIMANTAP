<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Entitas kelas satu — kontrol audit utama (SOP langkah 11 & 14)
        // Temuan Inspektorat III: mekanisme penahanan dana & debit otomatis bank
        // Aliran: KPPN → rekening_taruna → bank penahanan → debit otomatis → senat_accounts
        Schema::create('pemblokiran_uang_makan', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('periode_bulan')->comment('1-12');
            $table->year('periode_tahun');
            $table->foreignId('taruna_id')->constrained('taruna')->restrictOnDelete();
            // Koreksi #4: target rekening penampungan Senat (debit otomatis bank)
            $table->foreignId('senat_account_id')->constrained('senat_accounts')->restrictOnDelete()
                ->comment('Rekening Senat yang menerima debit otomatis dari bank');
            $table->decimal('nilai_bantuan', 15, 2)->comment('Nilai yang diblokir = dari rekap_bulanan');
            $table->enum('status', ['diusulkan', 'diblokir', 'didebit'])->default('diusulkan');
            // Surat pemblokiran
            $table->string('nomor_surat_pemblokiran', 100)->nullable();
            $table->date('tanggal_surat')->nullable();
            $table->string('file_surat_pemblokiran', 255)->nullable();
            // Koreksi #4: bukti debit bank yang diperjelas
            $table->string('bukti_debit_bank', 255)->nullable()
                ->comment('Bukti nota debit dari bank (rekening_taruna → senat_account)');
            $table->timestamp('tanggal_debit')->nullable();
            $table->decimal('nilai_didebit', 15, 2)->nullable()
                ->comment('Nilai aktual didebit — bisa berbeda jika ada biaya bank');
            $table->text('catatan')->nullable();
            // Usulan oleh Senat, diproses oleh PPK
            $table->foreignId('diusulkan_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('diusulkan_at')->nullable();
            $table->foreignId('diproses_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('diproses_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            // Dokumen keuangan — tidak pakai softDeletes (immutable)

            $table->unique(['taruna_id', 'periode_bulan', 'periode_tahun'], 'unique_pemblokiran');
            $table->index(['periode_bulan', 'periode_tahun']);
            $table->index('status');
            $table->index('senat_account_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pemblokiran_uang_makan');
    }
};
