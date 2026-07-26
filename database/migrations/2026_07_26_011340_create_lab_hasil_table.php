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
        Schema::create('lab_hasil', function (Blueprint $table) {
            $table->increments('lab_hasil_id');
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->string('no_lab', 15)->nullable();
            $table->integer('order_lab_id')->nullable();
            $table->string('no_kunjungan', 15)->nullable();
            $table->integer('no_urut')->nullable();
            $table->string('kode_sir', 10)->nullable();
            $table->string('kode_pemeriksaan', 100)->nullable();
            $table->string('nama_pemeriksaan', 50)->nullable();
            $table->string('unit', 50)->nullable();
            $table->string('normal', 500)->nullable();
            $table->string('hasil', 3500)->nullable();
            $table->string('flag', 100)->nullable();
            $table->string('flag_insert', 100)->nullable();
            $table->timestamp('tgl_jam_insert', 6)->nullable();
            $table->string('flag_ambil', 100)->nullable();
            $table->timestamp('tgl_jam_ambil', 6)->nullable();
            $table->string('type', 100)->nullable();
            $table->string('no_mr', 10)->nullable();
            $table->timestamp('tgl_daftar', 6)->nullable();
            $table->timestamp('tgl_hasil', 6)->nullable();
            $table->integer('bagian_id')->nullable();
            $table->string('kode_ruang', 15)->nullable();
            $table->integer('user_id_dokter')->nullable();
            $table->string('kode_dokter', 15)->nullable();
            $table->string('kode_alat', 10)->nullable();
            $table->string('nama_alat', 25)->nullable();
            $table->timestamp('tanggal_validasi', 6)->nullable();
            $table->integer('user_id_validasi')->nullable();
            $table->string('kode_user_validasi', 10)->nullable();
            $table->string('user_validasi', 30)->nullable();
            $table->string('metode', 8000)->nullable();
            $table->integer('flag_lis')->nullable();
            $table->text('hasil_base64')->nullable();
            $table->timestamp('jam_verifikasi')->nullable();
            $table->timestamp('jam_lapor_dpjp')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lab_hasil');
    }
};
