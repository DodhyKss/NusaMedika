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
        Schema::create('tindakan', function (Blueprint $table) {
            $table->integer('tindakan_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable()->index('tindakan_status_batal_idx');
            $table->string('nama_tindakan')->nullable()->index('tindakan_nama_tindakan_idx');
            $table->integer('jenis_tindakan_id')->nullable()->index('tindakan_jenis_tindakan_id_idx');
            $table->smallInteger('schema_tarif')->nullable();
            $table->smallInteger('schema_bmhp')->nullable();
            $table->string('tindakan_id_old', 100)->nullable();
            $table->string('kategori_tindakan_1')->nullable();
            $table->string('kategori_tindakan_2')->nullable();
            $table->string('nama_loinc')->nullable();
            $table->string('kode_loinc')->nullable();
            $table->string('spesimen')->nullable();
            $table->string('permintaan')->nullable();
            $table->string('satuan')->nullable();
            $table->string('nama_pemeriksaan')->nullable();
            $table->string('metode_analisis')->nullable();

            $table->index(['tindakan_id', 'status_batal'], 'idx_tindakan01');
            $table->index(['tindakan_id', 'nama_tindakan', 'jenis_tindakan_id', 'tindakan_id_old'], 'tindakan_tindakan_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tindakan');
    }
};
