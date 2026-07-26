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
        Schema::create('registrasi_igd', function (Blueprint $table) {
            $table->integer('registrasi_igd_id');
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('registrasi_id')->nullable();
            $table->string('status_pengantar', 20)->nullable();
            $table->string('nama_pengantar')->nullable();
            $table->string('hp_pengantar', 30)->nullable();
            $table->string('alamat_pengantar')->nullable();
            $table->string('status_keluarga', 20)->nullable();
            $table->string('nama_keluarga')->nullable();
            $table->string('hp_keluarga', 30)->nullable();
            $table->string('alamat_keluarga')->nullable();
            $table->smallInteger('ktp')->nullable();
            $table->smallInteger('kartu_keluarga')->nullable();
            $table->smallInteger('kartu_nasabah')->nullable();
            $table->string('jenis_kejadian', 100)->nullable();
            $table->string('tempat_kejadian')->nullable();
            $table->timestamp('tgl_kejadian', 6)->nullable();
            $table->string('catatan_registrasi')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registrasi_igd');
    }
};
