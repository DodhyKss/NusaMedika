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
        Schema::create('pesan_bedah', function (Blueprint $table) {
            $table->integer('pesan_bedah_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('pasien_id')->nullable();
            $table->integer('nasabah_id')->nullable();
            $table->integer('bagian_id')->nullable();
            $table->integer('kelas_id')->nullable();
            $table->integer('pegawai_id')->nullable();
            $table->integer('registrasi_detail_id')->nullable();
            $table->string('diagnosa_pre_operasi', 200)->nullable();
            $table->integer('tindakan_id')->nullable();
            $table->string('ket_tindakan', 200)->nullable();
            $table->string('jenis_operasi', 12)->nullable();
            $table->timestamp('tgl_rencana_operasi', 6)->nullable();
            $table->timestamp('tgl_rawat', 6)->nullable();
            $table->string('no_hp', 13)->nullable();
            $table->smallInteger('alat_khusus')->nullable();
            $table->string('ket_alat_khusus', 200)->nullable();
            $table->string('keterangan', 200)->nullable();
            $table->string('ic', 20)->nullable();
            $table->string('prabedah', 20)->nullable();
            $table->string('jenis_anestesi', 20)->nullable();
            $table->string('berat_badan', 5)->nullable();
            $table->string('tinggi_badan', 5)->nullable();

            $table->index(['pesan_bedah_id', 'pasien_id', 'nasabah_id', 'bagian_id', 'kelas_id', 'pegawai_id', 'registrasi_detail_id', 'tindakan_id'], 'pesan_bedah_pesan_bedah_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pesan_bedah');
    }
};
