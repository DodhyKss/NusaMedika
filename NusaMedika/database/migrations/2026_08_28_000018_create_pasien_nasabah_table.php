<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pasien_nasabah', function (Blueprint $table) {
            $table->increments('pasien_nasabah_id');
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('pasien_id')->nullable();
            $table->integer('nasabah_id')->nullable();
            $table->string('no_peserta', 20)->nullable();
            $table->integer('hak_kelas_id')->nullable();
            $table->string('nama_jenis_kepesertaan')->nullable();
            $table->string('kode_jenis_kepesertaan')->nullable();
            $table->string('catatan')->nullable();
            $table->json('finansial_risk')->nullable();

            $table->index('pasien_id');
            $table->index('nasabah_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pasien_nasabah');
    }
};
