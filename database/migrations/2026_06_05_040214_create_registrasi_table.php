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
        Schema::create('registrasi', function (Blueprint $table) {
            $table->integer('registrasi_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable()->index('registrasi_de_idx_status_batal');
            $table->integer('pasien_id')->nullable()->index();
            $table->integer('pasien_nasabah_id')->nullable()->index();
            $table->integer('referensi_registrasi_id')->nullable()->index();
            $table->timestamp('tgl_masuk', 6)->nullable()->index('registrasi_de_idx_year_2025');
            $table->timestamp('tgl_keluar', 6)->nullable()->index();
            $table->string('jenis_rawat', 3)->nullable()->index('registrasi_jenis_rawat_idx');
            $table->string('prioritas', 20)->nullable();
            $table->text('memo')->nullable();
            $table->integer('pasien_nasabah_id_2')->nullable()->index();
            $table->integer('pasien_nasabah_id_3')->nullable()->index();
            $table->smallInteger('flag_online')->nullable()->comment('1 = melalui Mobile JKN');
            $table->text('memo_adru')->nullable();
            $table->timestamp('awal_rawat_pelni')->nullable();
            $table->timestamp('akhir_rawat_pelni')->nullable();
            $table->smallInteger('status_pulang_vclaim')->nullable();
            $table->timestamp('tgl_keluar_old', 6)->nullable();
            $table->string('id_satu_sehat', 50)->nullable();
            $table->integer('peresepan_obat_id')->nullable();
            $table->integer('jumlah_iter')->nullable();
            $table->integer('flag_sirs')->nullable();
            $table->integer('id_perujuk')->nullable();

            $table->index(['registrasi_id', 'pasien_id', 'pasien_nasabah_id', 'jenis_rawat'], 'idx_registrasi03');
            $table->index(['jenis_rawat', 'status_batal'], 'registrasi_de_idx_jenis_rawat');
            $table->index(['registrasi_id', 'pasien_id', 'pasien_nasabah_id', 'referensi_registrasi_id', 'tgl_masuk', 'tgl_keluar', 'status_batal'], 'registrasi_registrasi_id_idx');
            $table->index(['tgl_masuk']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registrasi');
    }
};
