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
        Schema::create('zyx_barang', function (Blueprint $table) {
            $table->increments('zyx_barang_id');
            $table->integer('barang_id');
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->string('nama_barang')->nullable();
            $table->string('kategori_barang', 10)->nullable();
            $table->integer('barang_jenis_id')->nullable();
            $table->integer('barang_sub_golongan_id')->nullable();
            $table->string('satuan_besar', 20)->nullable();
            $table->string('satuan_kecil', 20)->nullable();
            $table->string('satuan_pakai', 20)->nullable();
            $table->string('formularium', 20)->nullable();
            $table->string('jenis_golongan', 20)->nullable();
            $table->integer('flag_fast_moving')->nullable();
            $table->integer('minimal_stock')->nullable();
            $table->integer('maksimal_stock')->nullable();
            $table->integer('margin_stock')->nullable();
            $table->string('barang_id_lama', 100)->nullable();
            $table->string('sediaan', 50)->nullable();
            $table->string('spesifikasi', 20)->nullable();
            $table->timestamp('mod_change', 6)->nullable();
            $table->integer('komposisi_id')->nullable();
            $table->integer('kfa_id')->nullable();
            $table->string('konsinyasi', 10)->nullable();
            $table->string('fopi_id', 20)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zyx_barang');
    }
};
