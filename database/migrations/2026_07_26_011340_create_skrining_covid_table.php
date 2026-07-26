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
        Schema::create('skrining_covid', function (Blueprint $table) {
            $table->integer('skrining_covid_id');
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('pasien_id')->nullable();
            $table->string('demam', 10)->nullable();
            $table->string('batuk', 10)->nullable();
            $table->string('pilek', 10)->nullable();
            $table->string('nyeri_tenggorokan', 10)->nullable();
            $table->string('mata_merah', 10)->nullable();
            $table->string('diare', 10)->nullable();
            $table->string('penurunan_kesadaran', 10)->nullable();
            $table->string('sesak_nafas', 10)->nullable();
            $table->string('gambaran_klinis', 10)->nullable();
            $table->string('transmisi_luar_negeri', 10)->nullable();
            $table->string('transmisi_dalam_negeri', 10)->nullable();
            $table->string('kontak_covid', 10)->nullable();
            $table->string('kunjungan_rs', 10)->nullable();
            $table->string('jawaban')->nullable();
            $table->string('temp_pasien')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('skrining_covid');
    }
};
