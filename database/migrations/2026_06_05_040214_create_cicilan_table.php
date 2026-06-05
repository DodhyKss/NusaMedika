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
        Schema::create('cicilan', function (Blueprint $table) {
            $table->integer('cicilan_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('registrasi_id')->nullable();
            $table->integer('pasien_id')->nullable();
            $table->integer('tenor')->nullable();
            $table->decimal('tenor_per_bulan', 18)->nullable();
            $table->integer('tgl_tempo_bulanan')->nullable();
            $table->decimal('total_tagihan', 18)->nullable();
            $table->integer('flag_selesai')->nullable();

            $table->index(['cicilan_id', 'registrasi_id', 'pasien_id'], 'cicilan_cicilan_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cicilan');
    }
};
