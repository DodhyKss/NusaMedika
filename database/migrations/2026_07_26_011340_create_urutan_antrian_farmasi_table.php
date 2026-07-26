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
        Schema::create('urutan_antrian_farmasi', function (Blueprint $table) {
            $table->integer('urutan_antrian_farmasi_id');
            $table->timestamp('input_time', 6)->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('pasien_id');
            $table->json('registrasi_detail_id');
            $table->integer('no_antrian');
            $table->timestamp('tgl_antrian', 6);
            $table->string('status_antrian')->nullable();
            $table->string('jenis_antrian');
            $table->json('no_resep');
            $table->integer('status_panggil')->nullable();
            $table->smallInteger('cetak')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('urutan_antrian_farmasi');
    }
};
