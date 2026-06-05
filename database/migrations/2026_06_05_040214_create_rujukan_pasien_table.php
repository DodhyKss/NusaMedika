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
        Schema::create('rujukan_pasien', function (Blueprint $table) {
            $table->integer('rujukan_pasien_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('pasien_id')->nullable();
            $table->string('no_peserta', 20)->nullable();
            $table->string('no_rujukan', 20)->nullable();
            $table->timestamp('tgl_rujukan', 6)->nullable();
            $table->string('kode_provider', 10)->nullable();
            $table->string('nama_provider')->nullable();
            $table->string('kode_diagnosa', 10)->nullable();
            $table->string('nama_diagnosa')->nullable();
            $table->string('faskes', 3)->nullable();
            $table->string('jenis_peserta')->nullable();
            $table->string('prolanis_prb')->nullable();
            $table->json('json_data')->nullable();
            $table->string('kode_poli_bpjs', 10)->nullable();

            $table->index(['rujukan_pasien_id', 'pasien_id', 'no_rujukan', 'kode_poli_bpjs'], 'idx_rujukan_pasien01');
            $table->index(['rujukan_pasien_id', 'pasien_id', 'no_peserta', 'no_rujukan', 'tgl_rujukan', 'kode_poli_bpjs'], 'rujukan_pasien_rujukan_pasien_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rujukan_pasien');
    }
};
