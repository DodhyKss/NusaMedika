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
        Schema::create('konfigurasi_tim_dokter', function (Blueprint $table) {
            $table->integer('konfigurasi_tim_dokter_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('dpjp_user_id')->nullable();
            $table->json('detail_tim_dpjp')->nullable();

            $table->index(['konfigurasi_tim_dokter_id', 'dpjp_user_id', 'status_batal'], 'konfigurasi_tim_dokter_konfigurasi_tim_dokter_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('konfigurasi_tim_dokter');
    }
};
