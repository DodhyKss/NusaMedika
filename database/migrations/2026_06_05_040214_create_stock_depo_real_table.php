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
        Schema::create('stock_depo_real', function (Blueprint $table) {
            $table->integer('stock_depo_real_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable()->index('stock_depo_real_mod_user_id_idx');
            $table->smallInteger('status_batal')->nullable()->index('stock_depo_real_status_batal_idx');
            $table->integer('bagian_id')->nullable();
            $table->integer('barang_id')->nullable()->index('stock_depo_real_barang_id_idx');
            $table->decimal('jumlah_stock', 18)->nullable();
            $table->string('nomor_batch', 100)->nullable()->index('stock_depo_real_nomor_batch_idx');
            $table->timestamp('tgl_expired', 6)->nullable()->index('stock_depo_real_tgl_expired_idx');
            $table->decimal('harga_jual', 18)->nullable()->index('stock_depo_real_harga_jual_idx');
            $table->integer('flag_tampil')->nullable();
            $table->timestamp('tgl_terima')->nullable();

            $table->index(['stock_depo_real_id', 'bagian_id', 'barang_id', 'nomor_batch', 'tgl_expired'], 'stock_depo_real_stock_depo_real_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_depo_real');
    }
};
