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
        Schema::create('konfigurasi_biaya_administrasi', function (Blueprint $table) {
            $table->integer('konfigurasi_biaya_administrasi_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->string('nama_konfigurasi_biaya_administrasi', 50)->nullable();

            $table->index(['konfigurasi_biaya_administrasi_id', 'status_batal', 'nama_konfigurasi_biaya_administrasi'], 'konfigurasi_biaya_administrasi_konfigurasi_biaya_administrasi_i');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('konfigurasi_biaya_administrasi');
    }
};
