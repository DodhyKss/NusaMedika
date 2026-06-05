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
        Schema::create('kartu_stock', function (Blueprint $table) {
            $table->integer('kartu_stock_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable()->index('kartu_stock_status_batal_idx');
            $table->integer('barang_id')->nullable()->index('kartu_stock_barang_id_idx');
            $table->timestamp('tgl_jam', 6)->nullable()->index('kartu_stock_tgl_jam_idx');
            $table->decimal('stock_awal', 18)->nullable();
            $table->decimal('penambahan', 18)->nullable();
            $table->decimal('pengurangan', 18)->nullable();
            $table->decimal('stock_akhir', 18)->nullable();
            $table->string('keterangan')->nullable();
            $table->string('no_bukti', 30)->nullable()->index('kartu_stock_no_bukti_idx');
            $table->integer('bagian_id')->nullable()->index('kartu_stock_bagian_id_idx');
            $table->string('kode_transaksi', 1)->nullable();
            $table->integer('bagian_id_transaksi')->nullable();
            $table->string('nomor_batch', 100)->nullable()->index('kartu_stock_nomor_batch_idx');
            $table->integer('rekap_stock_opname_id')->nullable();
            $table->integer('emr_id')->nullable();
            $table->decimal('harga_jual', 18)->nullable();
            $table->timestamp('tgl_expired', 6)->nullable();
            $table->timestamp('tgl_terima')->nullable();

            $table->index(['kartu_stock_id', 'barang_id', 'tgl_jam', 'bagian_id', 'bagian_id_transaksi', 'rekap_stock_opname_id'], 'kartu_stock_kartu_stock_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kartu_stock');
    }
};
