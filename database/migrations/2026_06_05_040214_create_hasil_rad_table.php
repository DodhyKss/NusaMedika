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
        Schema::create('hasil_rad', function (Blueprint $table) {
            $table->integer('hasil_rad_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('registrasi_detail_id')->nullable();
            $table->integer('pasien_id')->nullable();
            $table->timestamp('tgl_hasil', 6)->nullable();
            $table->integer('user_konfirmasi_id')->nullable();
            $table->timestamp('tgl_konfirmasi', 6)->nullable();

            $table->index(['hasil_rad_id', 'registrasi_detail_id', 'pasien_id', 'user_konfirmasi_id', 'tgl_hasil', 'tgl_konfirmasi'], 'hasil_rad_hasil_rad_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hasil_rad');
    }
};
