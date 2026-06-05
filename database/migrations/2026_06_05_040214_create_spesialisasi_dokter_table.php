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
        Schema::create('spesialisasi_dokter', function (Blueprint $table) {
            $table->integer('spesialisasi_dokter_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('spesialisasi_id')->nullable();
            $table->integer('pegawai_id')->nullable();

            $table->index(['spesialisasi_dokter_id', 'spesialisasi_id', 'pegawai_id'], 'spesialisasi_dokter_spesialisasi_dokter_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spesialisasi_dokter');
    }
};
