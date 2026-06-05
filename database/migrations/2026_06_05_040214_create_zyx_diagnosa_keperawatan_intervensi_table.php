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
        Schema::create('zyx_diagnosa_keperawatan_intervensi', function (Blueprint $table) {
            $table->increments('zyx_diagnosa_keperawatan_intervensi_id');
            $table->integer('diagnosa_keperawatan_intervensi_id');
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('diagnosa_keperawatan_id')->nullable();
            $table->string('kode_intervensi', 10)->nullable();
            $table->string('nama_intervensi', 150)->nullable();
            $table->timestamp('mod_change', 6)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zyx_diagnosa_keperawatan_intervensi');
    }
};
