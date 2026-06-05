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
        Schema::create('zyx_pemusnahan_barang', function (Blueprint $table) {
            $table->increments('zyx_pemusnahan_barang_id');
            $table->integer('pemusnahan_barang_id');
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('stock_depo_real_id')->nullable();
            $table->integer('bagian_id')->nullable();
            $table->integer('barang_id')->nullable();
            $table->string('nomor_batch', 100)->nullable();
            $table->timestamp('tgl_expired', 6)->nullable();
            $table->decimal('harga_jual', 18)->nullable();
            $table->decimal('jumlah_stock_awal', 18)->nullable();
            $table->decimal('jumlah_musnah', 18)->nullable();
            $table->string('satuan_pakai', 20)->nullable();
            $table->integer('surat_pemusnahan_barang_id')->nullable();
            $table->timestamp('mod_change', 6)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zyx_pemusnahan_barang');
    }
};
