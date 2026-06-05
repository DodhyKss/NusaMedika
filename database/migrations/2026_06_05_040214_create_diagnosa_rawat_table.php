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
        Schema::create('diagnosa_rawat', function (Blueprint $table) {
            $table->integer('diagnosa_rawat_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('registrasi_id')->nullable()->index();
            $table->integer('icd_id')->nullable()->index();
            $table->smallInteger('jenis_diagnosa')->nullable();

            $table->index(['diagnosa_rawat_id', 'registrasi_id', 'icd_id', 'jenis_diagnosa'], 'diagnosa_rawat_diagnosa_rawat_id_idx');
            $table->index(['diagnosa_rawat_id', 'registrasi_id', 'icd_id', 'status_batal'], 'idx_diagnosa_rawat01');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diagnosa_rawat');
    }
};
