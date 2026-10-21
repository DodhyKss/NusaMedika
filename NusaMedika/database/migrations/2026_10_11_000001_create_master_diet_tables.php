<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Master jenis diet pasien (form 43 "Diet Pasien" / prasyarat PRE-05).
 *
 * Dual-driver: dipakai MySQL 8 (default) maupun PostgreSQL. Hanya tipe
 * portabel yang dipakai — `increments()` (MySQL AUTO_INCREMENT / PG serial)
 * dan kolom audit standar `input_time`/`mod_time` + `status_batal`.
 *
 * `nama_diet` adalah nilai yang disimpan di `emr_detail.variabel
 * jenis_diet`, jadi form 43 memvalidasinya dengan
 * `exists:master_diet,nama_diet`.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_diet', function (Blueprint $table) {
            $table->increments('master_diet_id');
            $table->string('kode_diet', 20)->unique();
            $table->string('nama_diet', 100);
            $table->string('keterangan', 255)->nullable();

            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->default(0);
        });

        Schema::create('master_diagnosa_diet', function (Blueprint $table) {
            $table->increments('master_diagnosa_diet_id');
            $table->string('kode_diagnosa_diet', 20)->unique();
            $table->string('nama_diagnosa_diet', 150);
            $table->string('keterangan', 255)->nullable();

            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_diagnosa_diet');
        Schema::dropIfExists('master_diet');
    }
};
