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
        Schema::create('hasil_lab_detail', function (Blueprint $table) {
            $table->integer('hasil_lab_detail_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('hasil_lab_id')->nullable();
            $table->integer('tindakan_id')->nullable();
            $table->string('kode_pemeriksaan')->nullable();
            $table->string('nama_pemeriksaan')->nullable();
            $table->string('unit')->nullable();
            $table->string('nilai_rujukan')->nullable();
            $table->string('hasil_isian')->nullable();
            $table->string('flag_abnormal')->nullable();
            $table->smallInteger('flag_insert')->nullable();
            $table->timestamp('insert_time', 6)->nullable();
            $table->smallInteger('flag_ambil')->nullable();
            $table->timestamp('ambil_time', 6)->nullable();
            $table->string('type')->nullable();
            $table->integer('pasien_id')->nullable();
            $table->timestamp('tgl_daftar', 6)->nullable();
            $table->timestamp('tgl_hasil', 6)->nullable();
            $table->integer('urutan')->nullable();

            $table->index(['hasil_lab_detail_id', 'hasil_lab_id', 'tindakan_id', 'kode_pemeriksaan', 'pasien_id'], 'hasil_lab_detail_hasil_lab_detail_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hasil_lab_detail');
    }
};
