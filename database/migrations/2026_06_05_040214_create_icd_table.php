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
        Schema::create('icd', function (Blueprint $table) {
            $table->integer('icd_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->string('kode_diagnosa', 10)->nullable()->index('icd_kode_diagnosa_idx');
            $table->string('nama_diagnosa')->nullable()->index('icd_nama_diagnosa_idx');
            $table->string('kategori', 10)->nullable()->index('icd_kategori_idx');
            $table->integer('jenis_diagnosa')->nullable();
            $table->integer('penyakit_id')->nullable();

            $table->index(['icd_id', 'kode_diagnosa'], 'icd_icd_id_idx');
            $table->index(['icd_id', 'status_batal', 'kode_diagnosa'], 'idx_icd01');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('icd');
    }
};
