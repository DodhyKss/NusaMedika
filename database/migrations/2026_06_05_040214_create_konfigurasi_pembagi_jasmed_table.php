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
        Schema::create('konfigurasi_pembagi_jasmed', function (Blueprint $table) {
            $table->integer('konfigurasi_pembagi_jasmed_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->string('nama_konfigurasi_jasmed', 100)->nullable();
            $table->string('jenis_rawat', 3)->nullable();
            $table->string('nama_jasa', 100)->nullable();
            $table->decimal('persentase', 18)->nullable();
            $table->integer('bagian_id')->nullable();
            $table->integer('referensi_bagian')->nullable();
            $table->string('nama_jenis_jasmed')->nullable();

            $table->index(['bagian_id', 'jenis_rawat', 'referensi_bagian', 'konfigurasi_pembagi_jasmed_id', 'status_batal'], 'konfigurasi_pembagi_jasmed_bagian_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('konfigurasi_pembagi_jasmed');
    }
};
