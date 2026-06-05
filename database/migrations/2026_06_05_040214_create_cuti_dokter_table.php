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
        Schema::create('cuti_dokter', function (Blueprint $table) {
            $table->integer('cuti_dokter_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('cuti_user_id')->nullable();
            $table->timestamp('tanggal_awal', 6)->nullable();
            $table->timestamp('tanggal_akhir', 6)->nullable();
            $table->string('keterangan', 250)->nullable();

            $table->index(['cuti_dokter_id', 'cuti_user_id'], 'cuti_dokter_cuti_dokter_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cuti_dokter');
    }
};
