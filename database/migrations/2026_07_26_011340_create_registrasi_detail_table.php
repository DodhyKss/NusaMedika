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
        Schema::create('registrasi_detail', function (Blueprint $table) {
            $table->integer('registrasi_detail_id');
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('registrasi_id')->nullable();
            $table->timestamp('tgl_daftar', 6)->nullable();
            $table->integer('bagian_id')->nullable();
            $table->integer('kelas_id')->nullable();
            $table->integer('hak_kelas_id')->nullable();
            $table->integer('bagian_asal_id')->nullable();
            $table->string('ket_catatan', 1000)->nullable();
            $table->string('terima_dari', 100)->nullable();
            $table->integer('flag_covid')->nullable();
            $table->timestamp('check_in')->nullable();
            $table->timestamp('check_out')->nullable();
            $table->integer('kriteria_covid')->nullable();
            $table->integer('asal_daftar')->nullable()->comment('1 = berasal dari APM');
            $table->string('masa_berlaku')->nullable();
            $table->string('limit_berobat')->nullable();
            $table->string('catatan_khusus')->nullable();
            $table->integer('verifikator_user_id')->nullable();
            $table->timestamp('verifikator_input_time', 6)->nullable();
            $table->jsonb('handover')->nullable()->comment('untuk menyimpan data handover perawat RJ');
            $table->integer('lokasi_daftar')->nullable()->comment('1. Lokasi daftar IGD Existing, 2. lokasi daftar IGD Merial');
            $table->integer('lokasi_rawat')->nullable()->comment('1. Lokasi Rawat IGD Existing, 2. Lokasi Rawat IGD Merial');
            $table->string('service_req_id')->nullable();
            $table->string('specimen_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registrasi_detail');
    }
};
