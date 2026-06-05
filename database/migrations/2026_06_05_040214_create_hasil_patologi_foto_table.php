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
        Schema::create('hasil_patologi_foto', function (Blueprint $table) {
            $table->integer('hasil_patologi_foto_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('pasien_id')->nullable();
            $table->integer('registrasi_detail_id')->nullable();
            $table->integer('hasil_patologi_anatomi_id')->nullable();
            $table->timestamp('tgl_upload_foto', 6)->nullable();
            $table->string('nama_file')->nullable();

            $table->index(['hasil_patologi_foto_id', 'pasien_id', 'registrasi_detail_id', 'hasil_patologi_anatomi_id', 'tgl_upload_foto'], 'hasil_patologi_foto_hasil_patologi_foto_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hasil_patologi_foto');
    }
};
