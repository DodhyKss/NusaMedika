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
        Schema::create('pemesanan_brg_detail', function (Blueprint $table) {
            $table->integer('pemesanan_brg_detail_id');
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('pemesanan_brg_id')->nullable();
            $table->integer('barang_id')->nullable();
            $table->decimal('jumlah', 18)->nullable();
            $table->decimal('harga_satuan', 18)->nullable();
            $table->decimal('diskon', 18)->nullable();
            $table->decimal('ppn', 18)->nullable();
            $table->decimal('total', 18)->nullable();
            $table->string('satuan', 20)->nullable();
            $table->integer('principal_id')->nullable();
            $table->string('satuan_order', 20)->nullable();
            $table->decimal('jumlah_permintaan', 18)->nullable();
            $table->string('tipe_diskon', 20)->nullable();
            $table->decimal('besaran_diskon', 18)->nullable();
            $table->integer('bagian_id')->nullable();
            $table->decimal('stok_tersedia', 18, 0)->nullable();
            $table->decimal('stok_all_rs', 18, 0)->nullable();
            $table->decimal('ppn_persen', 6)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pemesanan_brg_detail');
    }
};
