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
        Schema::create('order_rad', function (Blueprint $table) {
            $table->integer('order_rad_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('registrasi_detail_id')->nullable();
            $table->integer('pasien_id')->nullable();
            $table->integer('bagian_id')->nullable();
            $table->integer('kirim_user_id')->nullable();
            $table->string('jenis_rawat', 3)->nullable();
            $table->integer('flag_cito')->nullable();
            $table->timestamp('tgl_order_rad', 6)->nullable();
            $table->text('diagnosa')->nullable();
            $table->integer('status_verif')->nullable();
            $table->integer('user_id_verif')->nullable();
            $table->timestamp('tgl_verif', 6)->nullable();
            $table->integer('user_id_dokter_rad')->nullable();
            $table->text('keterangan_catatan')->nullable();

            $table->index(['order_rad_id', 'registrasi_detail_id', 'pasien_id', 'bagian_id', 'kirim_user_id'], 'order_rad_order_rad_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_rad');
    }
};
