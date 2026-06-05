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
        Schema::create('supplier_barang', function (Blueprint $table) {
            $table->integer('supplier_barang_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('supplier_id')->nullable();
            $table->integer('barang_id')->nullable();
            $table->decimal('harga_beli', 18)->nullable();
            $table->decimal('persen_diskon', 18)->nullable();
            $table->decimal('nominal_diskon', 18)->nullable();
            $table->string('satuan_beli', 20)->nullable();
            $table->integer('principal_id')->nullable();
            $table->integer('isi_satuan_besar')->nullable();
            $table->integer('kategori_harga')->nullable()->comment('1. REGULER,2.BPJS,3.FOPI,4.INHEALTH');
            $table->decimal('ppn', 6)->nullable();
            $table->integer('termofpayment')->nullable();

            $table->index(['supplier_barang_id', 'supplier_id', 'barang_id', 'principal_id'], 'supplier_barang_supplier_barang_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_barang');
    }
};
