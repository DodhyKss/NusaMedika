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
        Schema::create('urutan_antrian_poli', function (Blueprint $table) {
            $table->integer('urutan_antrian_poli_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('registrasi_detail_id');
            $table->integer('pegawai_id');
            $table->integer('bagian_id');
            $table->integer('no_antrian');
            $table->timestamp('tgl_antrian', 6);
            $table->string('status_panggil')->nullable();
            $table->timestamp('check_in', 6)->nullable();
            $table->timestamp('check_out', 6)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('urutan_antrian_poli');
    }
};
