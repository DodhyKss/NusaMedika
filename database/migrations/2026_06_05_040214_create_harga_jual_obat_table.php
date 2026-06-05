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
        Schema::create('harga_jual_obat', function (Blueprint $table) {
            $table->integer('harga_jual_obat_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable()->index('harga_jual_obat_status_batal_idx');
            $table->integer('barang_id')->nullable()->index('harga_jual_obat_barang_id_idx');
            $table->decimal('harga_jual', 18)->nullable();
            $table->integer('penerimaan_brg_detail_id')->nullable()->index('harga_jual_obat_penerimaan_brg_detail_id_idx');
            $table->decimal('jumlah_barang', 18)->nullable();
            $table->decimal('jumlah_pakai', 18)->nullable();
            $table->smallInteger('status_selesai')->nullable();
            $table->string('nomor_batch', 100)->nullable()->index('harga_jual_obat_nomor_batch_idx');
            $table->timestamp('tgl_expired', 6)->nullable()->index('harga_jual_obat_tgl_expired_idx');
            $table->decimal('harga_beli', 18)->nullable();

            $table->index(['harga_jual_obat_id', 'barang_id', 'penerimaan_brg_detail_id', 'tgl_expired', 'status_selesai'], 'harga_jual_obat_harga_jual_obat_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('harga_jual_obat');
    }
};
