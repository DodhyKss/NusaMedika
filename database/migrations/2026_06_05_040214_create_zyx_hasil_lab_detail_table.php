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
        Schema::create('zyx_hasil_lab_detail', function (Blueprint $table) {
            $table->increments('zyx_hasil_lab_detail_id');
            $table->integer('hasil_lab_detail_id');
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
            $table->timestamp('mod_change', 6)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zyx_hasil_lab_detail');
    }
};
