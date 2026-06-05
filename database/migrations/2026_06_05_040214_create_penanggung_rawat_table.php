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
        Schema::create('penanggung_rawat', function (Blueprint $table) {
            $table->integer('penanggung_rawat_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable()->index('penanggung_rawat_status_batal_idx');
            $table->integer('registrasi_id')->nullable()->index('penanggung_rawat_de_idx_registrasi_id');
            $table->integer('kirim_user_id')->nullable()->index('penanggung_rawat_kirim_user_id_idx');
            $table->integer('rawat_user_id')->nullable()->index('penanggung_rawat_rawat_user_id_idx');

            $table->index(['rawat_user_id', 'status_batal'], 'idx_penanggung_rawat01');
            $table->index(['penanggung_rawat_id', 'registrasi_id', 'kirim_user_id', 'rawat_user_id'], 'penanggung_rawat_penanggung_rawat_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penanggung_rawat');
    }
};
