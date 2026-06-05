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
        Schema::create('zyx_diagnosa_keperawatan', function (Blueprint $table) {
            $table->increments('zyx_diagnosa_keperawatan_id');
            $table->integer('diagnosa_keperawatan_id');
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->string('kode_diagnosa', 10)->nullable();
            $table->string('nama_diagnosa', 100)->nullable();
            $table->integer('diagnosa_umum')->nullable();
            $table->integer('diagnosa_obgyn')->nullable();
            $table->string('tujuan', 100)->nullable();
            $table->string('judul_intervensi', 100)->nullable();
            $table->integer('diagnosa_anak')->nullable();
            $table->integer('diagnosa_bayi')->nullable();
            $table->timestamp('mod_change', 6)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zyx_diagnosa_keperawatan');
    }
};
