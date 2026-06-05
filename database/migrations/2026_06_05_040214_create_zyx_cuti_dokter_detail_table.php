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
        Schema::create('zyx_cuti_dokter_detail', function (Blueprint $table) {
            $table->increments('zyx_cuti_dokter_detail_id');
            $table->integer('cuti_dokter_detail_id');
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('cuti_dokter_id')->nullable();
            $table->timestamp('tanggal_cuti', 6)->nullable();
            $table->integer('pengganti_user_id')->nullable();
            $table->integer('kuota')->nullable();
            $table->time('waktu_mulai', 6)->nullable();
            $table->time('waktu_akhir', 6)->nullable();
            $table->integer('registrasi_detail_id')->nullable();
            $table->timestamp('mod_change', 6)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zyx_cuti_dokter_detail');
    }
};
