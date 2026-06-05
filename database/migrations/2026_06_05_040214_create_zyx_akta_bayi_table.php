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
        Schema::create('zyx_akta_bayi', function (Blueprint $table) {
            $table->increments('zyx_akta_bayi_id');
            $table->integer('akta_bayi_id');
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('pasien_id')->nullable();
            $table->integer('bayi_id')->nullable();
            $table->string('kartu_keluarga', 16)->nullable();
            $table->string('nama_bayi')->nullable();
            $table->timestamp('tgl_lahir', 6)->nullable();
            $table->string('jenis_kelamin', 10)->nullable();
            $table->integer('anak_ke')->nullable();
            $table->integer('jenis_kelahiran')->nullable();
            $table->string('ktp_ayah', 16)->nullable();
            $table->string('nama_ayah')->nullable();
            $table->string('pekerjaan_ayah', 100)->nullable();
            $table->string('handphone_ayah', 13)->nullable();
            $table->string('alamat_ayah', 250)->nullable();
            $table->bigInteger('rt_ayah')->nullable();
            $table->bigInteger('rw_ayah')->nullable();
            $table->bigInteger('kelurahan_ayah')->nullable();
            $table->string('ktp_ibu', 16)->nullable();
            $table->string('nama_ibu')->nullable();
            $table->string('pekerjaan_ibu', 100)->nullable();
            $table->string('handphone_ibu', 13)->nullable();
            $table->string('alamat_ibu', 250)->nullable();
            $table->bigInteger('rt_ibu')->nullable();
            $table->bigInteger('rw_ibu')->nullable();
            $table->bigInteger('kelurahan_ibu')->nullable();
            $table->string('ktp_saksi1', 16)->nullable();
            $table->string('nama_saksi1')->nullable();
            $table->string('pekerjaan_saksi1', 100)->nullable();
            $table->string('handphone_saksi1', 13)->nullable();
            $table->string('alamat_saksi1', 250)->nullable();
            $table->bigInteger('rt_saksi1')->nullable();
            $table->bigInteger('rw_saksi1')->nullable();
            $table->bigInteger('kelurahan_saksi1')->nullable();
            $table->string('ktp_saksi2', 16)->nullable();
            $table->string('nama_saksi2')->nullable();
            $table->string('pekerjaan_saksi2', 100)->nullable();
            $table->string('handphone_saksi2', 13)->nullable();
            $table->string('alamat_saksi2', 250)->nullable();
            $table->bigInteger('rt_saksi2')->nullable();
            $table->bigInteger('rw_saksi2')->nullable();
            $table->bigInteger('kelurahan_saksi2')->nullable();
            $table->timestamp('cetak_time', 6)->nullable();
            $table->integer('cetak_user_id')->nullable();
            $table->timestamp('mod_change', 6)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zyx_akta_bayi');
    }
};
