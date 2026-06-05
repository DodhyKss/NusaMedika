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
        Schema::create('faktur_detail', function (Blueprint $table) {
            $table->integer('faktur_detail_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('faktur_id')->nullable();
            $table->integer('registrasi_id')->nullable();
            $table->integer('pasien_id')->nullable();
            $table->decimal('total_billing', 18)->nullable();
            $table->decimal('total_pembayaran_tagihan', 18)->nullable();
            $table->integer('verif_user_id')->nullable();
            $table->timestamp('verif_time', 6)->nullable();

            $table->index(['faktur_detail_id', 'faktur_id', 'registrasi_id', 'pasien_id', 'verif_user_id'], 'faktur_detail_faktur_detail_id_idx');
            $table->index(['faktur_detail_id', 'faktur_id', 'registrasi_id', 'pasien_id'], 'idx_faktur_detail_faktur_detail01');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faktur_detail');
    }
};
