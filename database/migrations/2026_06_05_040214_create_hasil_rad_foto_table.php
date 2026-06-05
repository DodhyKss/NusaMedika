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
        Schema::create('hasil_rad_foto', function (Blueprint $table) {
            $table->integer('hasil_rad_foto_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('pasien_id')->nullable();
            $table->integer('registrasi_detail_id')->nullable();
            $table->integer('tindakan_id')->nullable();
            $table->timestamp('tgl_upload_foto', 6)->nullable();
            $table->string('nama_file', 250)->nullable();
            $table->integer('flag_kritis')->nullable();

            $table->index(['hasil_rad_foto_id', 'pasien_id', 'registrasi_detail_id', 'tindakan_id'], 'hasil_rad_foto_hasil_rad_foto_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hasil_rad_foto');
    }
};
