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
        Schema::create('cicilan_detail', function (Blueprint $table) {
            $table->integer('cicilan_detail_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('cicilan_id')->nullable();
            $table->integer('kuitansi_id')->nullable();
            $table->integer('tenor_ke')->nullable();
            $table->timestamp('tgl_bayar_cicilan', 6)->nullable();
            $table->decimal('nominal_pembayaran', 18)->nullable();

            $table->index(['cicilan_detail_id', 'cicilan_id', 'kuitansi_id'], 'cicilan_detail_cicilan_detail_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cicilan_detail');
    }
};
