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
        Schema::create('mortalitas', function (Blueprint $table) {
            $table->integer('mortalitas_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('pasien_id')->nullable();
            $table->integer('bagian_id')->nullable();
            $table->integer('pegawai_id')->nullable();
            $table->integer('no_urut_smpk')->nullable();
            $table->string('no_smpk', 20)->nullable();
            $table->integer('registrasi_detail_id')->nullable();
            $table->timestamp('tgl_jam_masuk', 6)->nullable();
            $table->timestamp('tgl_jam_mati', 6)->nullable();
            $table->string('dnr', 10)->nullable();
            $table->smallInteger('waktu_pelayanan')->nullable();
            $table->integer('icd_id')->nullable();
            $table->string('mortalitas_jenis', 3)->nullable();
            $table->string('icd_secondary')->nullable();
            $table->string('icd_langsung')->nullable();
            $table->string('no_skk')->nullable();

            $table->index(['mortalitas_id', 'pasien_id', 'bagian_id', 'pegawai_id', 'registrasi_detail_id', 'icd_id'], 'mortalitas_mortalitas_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mortalitas');
    }
};
