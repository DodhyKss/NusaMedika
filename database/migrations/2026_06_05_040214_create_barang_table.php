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
        Schema::create('barang', function (Blueprint $table) {
            $table->integer('barang_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable()->index('barang_status_batal_idx');
            $table->string('nama_barang')->nullable();
            $table->string('kategori_barang', 10)->nullable()->index('barang_kategori_barang_idx');
            $table->integer('barang_jenis_id')->nullable()->index('barang_barang_jenis_id_idx');
            $table->integer('barang_sub_golongan_id')->nullable();
            $table->string('satuan_besar', 20)->nullable()->index('barang_satuan_besar_idx');
            $table->string('satuan_kecil', 20)->nullable()->index('barang_satuan_kecil_idx');
            $table->string('satuan_pakai', 20)->nullable()->index('barang_satuan_pakai_idx');
            $table->string('formularium', 20)->nullable();
            $table->string('jenis_golongan', 20)->nullable();
            $table->integer('flag_fast_moving')->nullable();
            $table->integer('minimal_stock')->nullable();
            $table->integer('maksimal_stock')->nullable();
            $table->integer('margin_stock')->nullable();
            $table->string('barang_id_lama', 100)->nullable()->index('barang_barang_id_lama_idx');
            $table->string('sediaan', 50)->nullable();
            $table->string('spesifikasi', 20)->nullable();
            $table->integer('komposisi_id')->nullable();
            $table->integer('kfa_id')->nullable();
            $table->string('konsinyasi', 10)->nullable();
            $table->string('fopi_id', 20)->nullable();
            $table->string('nama_poa')->nullable();
            $table->string('id_satu_sehat')->nullable();
            $table->string('kodeobat', 20)->nullable();

            $table->index(['barang_id', 'kategori_barang', 'barang_jenis_id', 'barang_sub_golongan_id', 'jenis_golongan', 'komposisi_id'], 'barang_barang_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barang');
    }
};
