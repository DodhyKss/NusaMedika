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
        Schema::create('retur_obat_dispense', function (Blueprint $table) {
            $table->integer('retur_obat_dispense_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('pasien_id')->nullable();
            $table->integer('barang_id')->nullable();
            $table->integer('jumlah_retur')->nullable();
            $table->integer('bagian_retur')->nullable();
            $table->string('no_batch')->nullable();
            $table->integer('registrasi_detail_id')->nullable();
            $table->integer('peresepan_obat_detail_id')->nullable();
            $table->smallInteger('flag_terima')->nullable();
            $table->timestamp('tgl_terima', 6)->nullable();
            $table->integer('jumlah_terima')->nullable();
            $table->integer('user_terima')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('retur_obat_dispense');
    }
};
