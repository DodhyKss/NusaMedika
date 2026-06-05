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
        Schema::create('riwayat_kll', function (Blueprint $table) {
            $table->integer('riwayat_kll_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('registrasi_id')->nullable();
            $table->string('sep', 20)->nullable();
            $table->string('no_jenis_kejadian', 1)->nullable();
            $table->string('nama_jenis_kejadian', 50)->nullable();
            $table->string('no_lp', 25)->nullable();
            $table->timestamp('tgl_kejadian', 6)->nullable();
            $table->string('provinsi_id', 100)->nullable();
            $table->string('kabupaten_id', 100)->nullable();
            $table->string('kecamatan_id', 100)->nullable();
            $table->text('keterangan_kejadian')->nullable();
            $table->string('nama_provinsi', 30)->nullable();
            $table->string('nama_kabupaten', 30)->nullable();
            $table->string('nama_kecamatan', 30)->nullable();

            $table->index(['riwayat_kll_id', 'registrasi_id', 'provinsi_id', 'kabupaten_id', 'kecamatan_id'], 'riwayat_kll_riwayat_kll_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riwayat_kll');
    }
};
