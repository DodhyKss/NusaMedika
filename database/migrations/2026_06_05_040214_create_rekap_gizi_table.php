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
        Schema::create('rekap_gizi', function (Blueprint $table) {
            $table->integer('rekap_gizi_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('bagian_id')->nullable();
            $table->string('uraian', 50)->nullable();
            $table->time('pagi')->nullable();
            $table->string('petugas_pagi', 30)->nullable();
            $table->time('snack_pagi')->nullable();
            $table->string('petugas_snack_pagi', 30)->nullable();
            $table->time('siang')->nullable();
            $table->string('petugas_siang', 30)->nullable();
            $table->time('snack_siang')->nullable();
            $table->string('petugas_snack_siang', 30)->nullable();
            $table->time('sore')->nullable();
            $table->string('petugas_sore', 30)->nullable();

            $table->index(['rekap_gizi_id', 'bagian_id'], 'rekap_gizi_rekap_gizi_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rekap_gizi');
    }
};
