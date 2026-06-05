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
        Schema::create('zyx_hasil_patologi_anatomi', function (Blueprint $table) {
            $table->increments('zyx_hasil_patologi_anatomi_id');
            $table->integer('hasil_patologi_anatomi_id');
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('registrasi_detail_id')->nullable();
            $table->integer('pasien_id')->nullable();
            $table->timestamp('tgl_hasil', 6)->nullable();
            $table->timestamp('tgl_hasil_dokter', 6)->nullable();
            $table->integer('tindakan_group_id')->nullable();
            $table->integer('flag_selesai')->nullable();
            $table->string('diagnosis_klinik', 1000)->nullable();
            $table->string('organ', 1000)->nullable();
            $table->string('cara_pengambilan', 1000)->nullable();
            $table->string('bahan', 1000)->nullable();
            $table->integer('flag_validasi')->nullable();
            $table->bigInteger('no_order_pesanan')->nullable();
            $table->timestamp('mod_change', 6)->nullable();
            $table->string('diagnosa', 1000)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zyx_hasil_patologi_anatomi');
    }
};
