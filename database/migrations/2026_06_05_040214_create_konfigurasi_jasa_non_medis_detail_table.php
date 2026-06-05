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
        Schema::create('konfigurasi_jasa_non_medis_detail', function (Blueprint $table) {
            $table->integer('konfigurasi_jasa_non_medis_detail_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('konfigurasi_jasa_non_medis_id')->nullable();
            $table->integer('nasabah_id')->nullable();

            $table->index(['nasabah_id', 'konfigurasi_jasa_non_medis_id', 'konfigurasi_jasa_non_medis_detail_id', 'status_batal'], 'konfigurasi_jasa_non_medis_detail_nasabah_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('konfigurasi_jasa_non_medis_detail');
    }
};
