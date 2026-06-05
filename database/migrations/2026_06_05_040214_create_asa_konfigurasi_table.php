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
        Schema::create('asa_konfigurasi', function (Blueprint $table) {
            $table->integer('asa_konfigurasi_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->string('nama_asa', 100)->nullable();
            $table->decimal('persentase_asa', 18)->nullable();
            $table->smallInteger('flag_aktif')->nullable();

            $table->index(['asa_konfigurasi_id', 'flag_aktif', 'input_time'], 'asa_konfigurasi_asa_konfigurasi_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asa_konfigurasi');
    }
};
