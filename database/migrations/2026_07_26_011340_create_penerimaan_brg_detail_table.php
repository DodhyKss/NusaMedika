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
        Schema::create('penerimaan_brg_detail', function (Blueprint $table) {
            $table->integer('penerimaan_brg_detail_id');
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('penerimaan_brg_id')->nullable();
            $table->integer('barang_id')->nullable();
            $table->decimal('jumlah', 18)->nullable();
            $table->decimal('konversi', 18)->nullable();
            $table->decimal('total_terima', 18)->nullable();
            $table->string('satuan', 20)->nullable();
            $table->decimal('konversi_pakai', 18)->nullable();
            $table->decimal('total_terima_pakai', 18)->nullable();
            $table->string('nomor_batch', 100)->nullable();
            $table->timestamp('tgl_expired', 6)->nullable();
            $table->decimal('harga_jual', 18)->nullable();
            $table->decimal('jumlah_kirim', 18)->nullable();
            $table->decimal('konversi_kirim', 18)->nullable();
            $table->decimal('total_kirim', 18)->nullable();
            $table->decimal('konversi_kirim_pakai', 18)->nullable();
            $table->decimal('total_kirim_pakai', 18)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penerimaan_brg_detail');
    }
};
