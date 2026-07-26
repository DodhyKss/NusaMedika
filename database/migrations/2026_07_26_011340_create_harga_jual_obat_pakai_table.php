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
        Schema::create('harga_jual_obat_pakai', function (Blueprint $table) {
            $table->integer('harga_jual_obat_pakai_id');
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('barang_id')->nullable();
            $table->decimal('harga_jual', 18)->nullable();
            $table->integer('permintaan_brg_detail_id')->nullable();
            $table->integer('jumlah_barang')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('harga_jual_obat_pakai');
    }
};
