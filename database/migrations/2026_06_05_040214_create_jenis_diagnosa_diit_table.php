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
        Schema::create('jenis_diagnosa_diit', function (Blueprint $table) {
            $table->integer('jenis_diagnosa_diit_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->string('nama_diagnosa_diit', 20)->nullable();
            $table->string('keterangan', 100)->nullable();

            $table->index(['jenis_diagnosa_diit_id', 'status_batal', 'nama_diagnosa_diit'], 'jenis_diagnosa_diit_jenis_diagnosa_diit_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jenis_diagnosa_diit');
    }
};
