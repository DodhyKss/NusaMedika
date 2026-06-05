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
        Schema::create('zyx_diagnosa_keperawatan_indikasi_kriteria', function (Blueprint $table) {
            $table->increments('zyx_diagnosa_keperawatan_indikasi_kriteria_id');
            $table->integer('diagnosa_keperawatan_indikasi_kriteria_id');
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('diagnosa_keperawatan_id')->nullable();
            $table->string('kriteria', 150)->nullable();
            $table->integer('ds')->nullable();
            $table->integer('do')->nullable();
            $table->integer('hiperglikemi')->nullable();
            $table->integer('hipoglikemi')->nullable();
            $table->timestamp('mod_change', 6)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zyx_diagnosa_keperawatan_indikasi_kriteria');
    }
};
