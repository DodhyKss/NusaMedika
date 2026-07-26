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
        Schema::create('kartu_stock_global', function (Blueprint $table) {
            $table->integer('kartu_stock_global_id');
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('barang_id')->nullable();
            $table->timestamp('tgl_jam', 6)->nullable();
            $table->decimal('stock_awal', 18)->nullable();
            $table->decimal('penambahan', 18)->nullable();
            $table->decimal('pengurangan', 18)->nullable();
            $table->decimal('stock_akhir', 18)->nullable();
            $table->string('keterangan')->nullable();
            $table->string('no_bukti', 30)->nullable();
            $table->integer('bagian_id')->nullable();
            $table->string('kode_transaksi', 1)->nullable();
            $table->integer('bagian_id_transaksi')->nullable();
            $table->integer('rekap_stock_opname_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kartu_stock_global');
    }
};
