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
        Schema::create('selisih_kelas', function (Blueprint $table) {
            $table->integer('selisih_kelas_id');
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('registrasi_id')->nullable();
            $table->integer('pasien_id')->nullable();
            $table->integer('bagian_id')->nullable();
            $table->smallInteger('jenis_selisih')->nullable();
            $table->json('dokter')->nullable();
            $table->json('tindakan')->nullable();
            $table->integer('tarif_inacbg_kelas_1')->nullable();
            $table->integer('tarif_inacbg_kelas_2')->nullable();
            $table->integer('tarif_inacbg_kelas_3')->nullable();
            $table->integer('jasa_sarana')->nullable();
            $table->integer('jasa_medik')->nullable();
            $table->integer('jasa_keperawatan')->nullable();
            $table->integer('jasa_lab')->nullable();
            $table->integer('jasa_radiologi')->nullable();
            $table->integer('jasa_fisioterapi')->nullable();
            $table->integer('jasa_administrasi')->nullable();
            $table->integer('jasa_remunerasi')->nullable();
            $table->string('lama_rawat_txt')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('selisih_kelas');
    }
};
