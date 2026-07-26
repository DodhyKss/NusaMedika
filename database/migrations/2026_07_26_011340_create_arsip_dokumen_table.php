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
        Schema::create('arsip_dokumen', function (Blueprint $table) {
            $table->integer('arsip_dokumen_id');
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('no_urut_arsip')->nullable();
            $table->integer('bagian_id')->nullable();
            $table->timestamp('tgl_upload_file', 6)->nullable();
            $table->string('no_arsip', 100)->nullable();
            $table->string('indeks_dokumen_1', 150)->nullable();
            $table->string('indeks_dokumen_2', 150)->nullable();
            $table->string('indeks_dokumen_3', 150)->nullable();
            $table->string('keterangan', 250)->nullable();
            $table->timestamp('tgl_dokumen', 6)->nullable();
            $table->string('sifat', 20)->nullable();
            $table->string('lokasi_fisik', 100)->nullable();
            $table->string('sifat_kepentingan', 20)->nullable();
            $table->string('nama_file', 150)->nullable();
            $table->smallInteger('jenis_file')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('arsip_dokumen');
    }
};
