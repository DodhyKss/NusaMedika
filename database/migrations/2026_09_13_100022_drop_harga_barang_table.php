<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * Master harga_barang dihapus: harga beli/jual kini disimpan di stock & kartu_stock
 * (per barang + no_batch + harga_jual). Pada fresh DB, tabel dibuat oleh migration
 * 100013 lalu di-drop di sini.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('harga_barang');
    }

    public function down(): void
    {
        if (Schema::hasTable('harga_barang')) {
            return;
        }

        Schema::create('harga_barang', function ($table) {
            $table->increments('harga_id');
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('barang_id');
            $table->string('no_batch', 50);
            $table->decimal('harga_beli', 15, 2)->nullable();
            $table->decimal('harga_jual', 15, 2)->nullable();

            $table->unique(['barang_id', 'no_batch']);
        });
    }
};
