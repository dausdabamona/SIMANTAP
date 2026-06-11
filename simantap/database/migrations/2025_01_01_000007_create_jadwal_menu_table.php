<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Jadwal rencana menu (dari kontrak) — Level 1 dari dua level menu
        // Level 2: konfirmasi/perubahan menu ada di pemesanan_harian.catatan_menu
        Schema::create('jadwal_menu', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kontrak_id')->constrained('kontrak_makan')->cascadeOnDelete()
                ->comment('Menu terikat ke kontrak — berubah jika addendum');
            $table->date('tanggal');
            $table->enum('jenis_makan', ['sarapan', 'makan_siang', 'makan_malam']);
            $table->text('menu')->comment('Deskripsi nama menu makanan');
            // Nilai gizi TIDAK di-tracking (keputusan desain)
            // Field porsi_per_taruna: tidak hardcode 1, bisa 1 per jenis makan
            $table->tinyInteger('porsi_per_taruna')->unsigned()->default(1)
                ->comment('Jumlah porsi per taruna per jenis makan (dari kontrak)');
            $table->text('catatan')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['kontrak_id', 'tanggal', 'jenis_makan'], 'unique_menu');
            $table->index(['kontrak_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jadwal_menu');
    }
};
