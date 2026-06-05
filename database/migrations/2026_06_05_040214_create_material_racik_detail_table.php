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
        Schema::create('material_racik_detail', function (Blueprint $table) {
            $table->integer('material_racik_detail_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('material_racik_id')->nullable();
            $table->integer('peresepan_obat_id')->nullable();
            $table->integer('barang_id')->nullable();
            $table->string('dosis', 10)->nullable();
            $table->integer('jumlah_dispense')->nullable();
            $table->integer('jumlah_stok')->nullable();
            $table->decimal('harga_beli', 18)->nullable();
            $table->decimal('harga_persediaan', 18)->nullable();
            $table->decimal('harga_jual', 18)->nullable();
            $table->integer('service')->nullable();
            $table->string('nama_barang', 10)->nullable();
            $table->integer('bagian_id')->nullable();
            $table->string('nomor_batch', 100)->nullable();
            $table->timestamp('tgl_expired', 6)->nullable();
            $table->integer('status_transaksi')->nullable();

            $table->index(['material_racik_detail_id', 'material_racik_id', 'peresepan_obat_id', 'barang_id', 'bagian_id'], 'material_racik_detail_material_racik_detail_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_racik_detail');
    }
};
