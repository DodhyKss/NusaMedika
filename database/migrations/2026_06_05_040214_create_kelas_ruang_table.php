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
        Schema::create('kelas_ruang', function (Blueprint $table) {
            $table->integer('kelas_ruang_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->string('nama_kelas_ruang')->nullable();
            $table->string('kelas_khusus', 10)->nullable();
            $table->smallInteger('kelas_bpjs')->nullable();

            $table->index(['kelas_ruang_id', 'kelas_bpjs'], 'idx_kelas_ruang01');
            $table->index(['kelas_ruang_id', 'kelas_bpjs', 'kelas_khusus', 'nama_kelas_ruang'], 'kelas_ruang_kelas_ruang_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kelas_ruang');
    }
};
