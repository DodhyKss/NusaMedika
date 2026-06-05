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
        Schema::create('zyx_material_request_detail_partisi', function (Blueprint $table) {
            $table->increments('zyx_material_request_detail_partisi_id');
            $table->integer('material_request_detail_partisi_id');
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('material_request_detail_id')->nullable();
            $table->decimal('kuantitas_beli', 18)->nullable();
            $table->integer('pemesanan_brg_id')->nullable();
            $table->string('satuan', 20)->nullable();
            $table->string('satuan_order', 20)->nullable();
            $table->string('tipe_diskon', 20)->nullable();
            $table->decimal('besaran_diskon', 18)->nullable();
            $table->decimal('harga_beli', 18)->nullable();
            $table->integer('principal_id')->nullable();
            $table->integer('supplier_id')->nullable();
            $table->timestamp('mod_change', 6)->nullable();
            $table->smallInteger('kategori_harga')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zyx_material_request_detail_partisi');
    }
};
