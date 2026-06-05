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
        Schema::create('batch_barang', function (Blueprint $table) {
            $table->integer('batch_barang_id')->primary();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->string('nomor_batch')->nullable();
            $table->integer('barang_id')->nullable();
            $table->decimal('harga_jual', 18)->nullable();
            $table->timestamp('tgl_expired', 6)->nullable();
            $table->timestamp('tgl_masuk', 6)->nullable();
            $table->integer('penerimaan_brg_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->string('distributor')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batch_barang');
    }
};
