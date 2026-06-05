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
        Schema::create('jadwal_dokter_igd', function (Blueprint $table) {
            $table->integer('jadwal_dokter_igd_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('pegawai_id')->nullable();
            $table->integer('hari')->nullable();
            $table->timestamp('tanggal_jaga_igd', 6)->nullable();
            $table->integer('bagian_id')->nullable();
            $table->integer('spesialisasi_id')->nullable();

            $table->index(['jadwal_dokter_igd_id', 'pegawai_id', 'bagian_id', 'spesialisasi_id', 'hari'], 'jadwal_dokter_igd_jadwal_dokter_igd_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal_dokter_igd');
    }
};
