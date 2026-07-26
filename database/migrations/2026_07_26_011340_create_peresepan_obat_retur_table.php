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
        Schema::create('peresepan_obat_retur', function (Blueprint $table) {
            $table->integer('peresepan_obat_retur_id');
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('peresepan_obat_dispense_id')->nullable();
            $table->integer('peresepan_obat_detail_id')->nullable();
            $table->integer('peresepan_obat_id')->nullable();
            $table->integer('barang_id')->nullable();
            $table->string('nomor_batch', 100)->nullable();
            $table->timestamp('tgl_expired', 6)->nullable();
            $table->decimal('harga_jual', 18)->default(0);
            $table->decimal('jumlah_retur', 18)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peresepan_obat_retur');
    }
};
