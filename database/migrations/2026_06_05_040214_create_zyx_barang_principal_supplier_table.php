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
        Schema::create('zyx_barang_principal_supplier', function (Blueprint $table) {
            $table->increments('zyx_barang_principal_supplier_id');
            $table->integer('barang_principal_supplier_id');
            $table->timestamp('input_tipe', 6)->nullable();
            $table->integer('input_user')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('barang_id')->nullable();
            $table->integer('supplier_id')->nullable();
            $table->integer('principal_id')->nullable();
            $table->timestamp('mod_change', 6)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zyx_barang_principal_supplier');
    }
};
