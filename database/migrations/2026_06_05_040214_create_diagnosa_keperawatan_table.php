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
        Schema::create('diagnosa_keperawatan', function (Blueprint $table) {
            $table->integer('diagnosa_keperawatan_id')->primary();
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
            $table->integer('flag_hubungan')->nullable();

            $table->index(['diagnosa_keperawatan_id', 'kode_diagnosa', 'diagnosa_anak', 'diagnosa_bayi'], 'diagnosa_keperawatan_diagnosa_keperawatan_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diagnosa_keperawatan');
    }
};
