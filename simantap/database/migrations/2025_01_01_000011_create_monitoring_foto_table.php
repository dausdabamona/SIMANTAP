<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monitoring_foto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penerimaan_id')->nullable()->constrained('penerimaan_makan')->nullOnDelete();
            $table->unsignedBigInteger('monitoring_id')->nullable();
            $table->string('file_path', 255);
            $table->tinyInteger('urutan')->unsigned()->default(1)->comment('1-5');
            $table->decimal('lat', 10, 8)->nullable();
            $table->decimal('long', 11, 8)->nullable();
            $table->timestamp('captured_at')->nullable();
            $table->timestamps();

            $table->index('penerimaan_id');
            $table->index('monitoring_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monitoring_foto');
    }
};
