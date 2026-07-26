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
        Schema::create('nasabah', function (Blueprint $table) {
            $table->integer('nasabah_id');
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->string('nama_nasabah')->nullable();
            $table->string('jenis_nasabah', 20)->nullable()->comment('PRIBADI                     				= PRI
JAMINAN KESEHATAN NASIONAL  	= JKN
ASURANSI                    				= ASU
COB                        					= PER
PERSEROAN TERBATAS          		= PT
RUMAH SAKIT                 			= RS
PENERIMA BANTUAN IURAN      	= PBI
KLINIK                      					= KLI');
            $table->string('email_nasabah', 100)->nullable();
            $table->string('alamat_nasabah', 250)->nullable();
            $table->string('nama_file_nasabah', 150)->nullable();
            $table->integer('tipe_biaya')->nullable();
            $table->decimal('biaya_administrasi', 18)->nullable();
            $table->decimal('batas_atas', 18)->nullable();
            $table->string('telp_nasabah', 20)->nullable();
            $table->string('telp_nasabah_2', 20)->nullable();
            $table->json('instalasi')->nullable();
            $table->string('cp_nama')->nullable();
            $table->string('cp_telp', 20)->nullable();
            $table->string('cp_nama_2')->nullable();
            $table->string('cp_telp_2', 20)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nasabah');
    }
};
