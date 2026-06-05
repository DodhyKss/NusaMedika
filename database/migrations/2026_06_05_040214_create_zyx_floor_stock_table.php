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
        Schema::create('zyx_floor_stock', function (Blueprint $table) {
            $table->increments('zyx_floor_stock_id');
            $table->integer('floor_stock_id');
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('barang_id')->nullable();
            $table->integer('bagian_id')->nullable();
            $table->smallInteger('flag_fast_moving')->nullable()->comment('1 = barang fast moving
2 = bukan barang fast moving');
            $table->decimal('minimal_stock', 18)->nullable();
            $table->decimal('maksimal_stock', 18)->nullable();
            $table->decimal('margin_stock', 18)->nullable();
            $table->timestamp('mod_change', 6)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zyx_floor_stock');
    }
};
