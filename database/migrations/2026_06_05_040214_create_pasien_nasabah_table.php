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
        Schema::create('pasien_nasabah', function (Blueprint $table) {
            $table->integer('pasien_nasabah_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('pasien_id')->nullable()->index();
            $table->integer('nasabah_id')->nullable()->index();
            $table->string('no_peserta', 20)->nullable();
            $table->integer('hak_kelas_id')->nullable();
            $table->string('nama_jenis_kepesertaan')->nullable();
            $table->string('kode_jenis_kepesertaan')->nullable();
            $table->string('catatan')->nullable();
            $table->json('finansial_risk')->nullable();

            $table->index(['pasien_nasabah_id', 'pasien_id', 'nasabah_id'], 'idx_pasien_nasabah01');
            $table->index(['pasien_nasabah_id', 'pasien_id', 'nasabah_id', 'hak_kelas_id', 'no_peserta'], 'pasien_nasabah_pasien_nasabah_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pasien_nasabah');
    }
};
