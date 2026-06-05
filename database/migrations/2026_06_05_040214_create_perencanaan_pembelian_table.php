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
        Schema::create('perencanaan_pembelian', function (Blueprint $table) {
            $table->integer('perencanaan_pembelian_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->string('thn', 4)->nullable();
            $table->integer('nasabah_id')->nullable();
            $table->integer('barang_id')->nullable();
            $table->integer('qty_pakai')->nullable();
            $table->integer('qty_kebutuhan')->nullable();
            $table->integer('bagian_id')->nullable();
            $table->string('keterangan')->nullable();

            $table->index(['perencanaan_pembelian_id', 'nasabah_id', 'barang_id', 'bagian_id'], 'perencanaan_pembelian_perencanaan_pembelian_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('perencanaan_pembelian');
    }
};
