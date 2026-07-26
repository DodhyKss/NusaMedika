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
        Schema::create('diagnosa_keperawatan_rj', function (Blueprint $table) {
            $table->integer('diagnosa_keperawatan_rj_id');
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->string('kode_diagnosa', 10)->nullable();
            $table->string('nama_diagnosa', 100)->nullable();
            $table->smallInteger('diagnosa_umum')->nullable();
            $table->smallInteger('diagnosa_obgyn')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diagnosa_keperawatan_rj');
    }
};
