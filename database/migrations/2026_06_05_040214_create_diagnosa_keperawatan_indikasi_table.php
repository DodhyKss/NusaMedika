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
        Schema::create('diagnosa_keperawatan_indikasi', function (Blueprint $table) {
            $table->integer('diagnosa_keperawatan_indikasi_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('diagnosa_keperawatan_id')->nullable();
            $table->string('nama_indikasi', 150)->nullable();
            $table->integer('hiperglikemi')->nullable();
            $table->integer('hipoglikemi')->nullable();

            $table->index(['diagnosa_keperawatan_indikasi_id', 'diagnosa_keperawatan_id'], 'diagnosa_keperawatan_indikasi_diagnosa_keperawatan_indikasi_id_');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diagnosa_keperawatan_indikasi');
    }
};
