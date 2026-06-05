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
        Schema::create('konfigurasi_jasa_non_medis', function (Blueprint $table) {
            $table->integer('konfigurasi_jasa_non_medis_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->string('nama_jasa', 50)->nullable();
            $table->decimal('persentase', 18);

            $table->index(['konfigurasi_jasa_non_medis_id', 'persentase', 'nama_jasa', 'status_batal'], 'konfigurasi_jasa_non_medis_konfigurasi_jasa_non_medis_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('konfigurasi_jasa_non_medis');
    }
};
