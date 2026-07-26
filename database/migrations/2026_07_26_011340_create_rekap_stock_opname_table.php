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
        Schema::create('rekap_stock_opname', function (Blueprint $table) {
            $table->integer('rekap_stock_opname_id');
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('bagian_id')->nullable();
            $table->integer('barang_id')->nullable();
            $table->decimal('jumlah_stock', 18)->nullable()->comment('adalah nilai stock (jumlah) akhir dari suatu barang yang diambil dari tabel stock_depo_real dengan 3 relasi barang seperti biasa.');
            $table->decimal('jumlah_stock_opname', 18)->nullable()->comment('adalah jumlah/saldo yang dimasukkan pada aplikasi stock opname (jumlah stock fisik yang diinput).');
            $table->decimal('harga_beli', 18)->nullable();
            $table->string('nomor_batch', 100)->nullable();
            $table->timestamp('tgl_expired', 6)->nullable();
            $table->integer('flag_stock_opname')->nullable();
            $table->integer('stock_depo_real_id')->nullable();
            $table->decimal('jumlah_stock_kartu_stock', 18)->nullable()->comment('adalah jumlah akhir dari table kartu_stock suatu barang (menurut pada seluruh batch, exp, dan harga_jual');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rekap_stock_opname');
    }
};
