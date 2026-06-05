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
        Schema::create('pemakaian_obat_farmasi', function (Blueprint $table) {
            $table->integer('pemakaian_obat_farmasi_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('peresepan_obat_detail_id')->nullable();
            $table->integer('barang_id')->nullable();
            $table->integer('jumlah')->nullable();
            $table->integer('jenis_pemakaian')->nullable();
            $table->string('sigma_1', 100)->nullable();
            $table->string('sigma_2', 100)->nullable();

            $table->index(['pemakaian_obat_farmasi_id', 'peresepan_obat_detail_id', 'barang_id'], 'pemakaian_obat_farmasi_pemakaian_obat_farmasi_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pemakaian_obat_farmasi');
    }
};
