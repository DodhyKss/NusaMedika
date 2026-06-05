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
        Schema::create('konfigurasi_pembagi_jasmed_3', function (Blueprint $table) {
            $table->integer('konfigurasi_pembagi_jasmed_3_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('konfigurasi_pembagi_jasmed_2_id')->nullable();
            $table->string('nama_jenis_jasmed', 25)->nullable();
            $table->string('nama_jasa_3', 100)->nullable();
            $table->decimal('persentase_3', 18)->nullable();

            $table->index(['konfigurasi_pembagi_jasmed_3_id', 'konfigurasi_pembagi_jasmed_2_id', 'status_batal'], 'konfigurasi_pembagi_jasmed_3_konfigurasi_pembagi_jasmed_3_id_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('konfigurasi_pembagi_jasmed_3');
    }
};
