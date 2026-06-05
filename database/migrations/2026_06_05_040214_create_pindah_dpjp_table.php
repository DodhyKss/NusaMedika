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
        Schema::create('pindah_dpjp', function (Blueprint $table) {
            $table->integer('pindah_dpjp_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('registrasi_detail_id')->nullable();
            $table->timestamp('tgl_masuk', 6)->nullable();
            $table->integer('dokter_awal_dpjp')->nullable();
            $table->integer('dokter_pindah_dpjp')->nullable();
            $table->string('alasan', 250)->nullable();
            $table->string('perawatan_lanjut', 250)->nullable();
            $table->timestamp('tgl_jawab', 6)->nullable();
            $table->string('ket_tolak', 250)->nullable();
            $table->integer('flag_pindah_dpjp')->nullable();

            $table->index(['pindah_dpjp_id', 'registrasi_detail_id', 'dokter_awal_dpjp', 'dokter_pindah_dpjp', 'tgl_masuk'], 'pindah_dpjp_pindah_dpjp_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pindah_dpjp');
    }
};
