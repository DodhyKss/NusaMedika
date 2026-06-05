<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tarif', function (Blueprint $table) {
            $table->integer('tarif_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('tindakan_detail_id')->nullable();
            $table->integer('kelas_ruang_id')->nullable();
            $table->decimal('biaya', 18)->nullable();
            $table->smallInteger('status_aktif')->nullable();

            $table->index(['tarif_id', 'status_batal', 'tindakan_detail_id'], 'idx_tarif');
            $table->index(['tarif_id', 'tindakan_detail_id', 'kelas_ruang_id'], 'tarif_tarif_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tarif');
    }
};
