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
        Schema::create('order_rad_detail', function (Blueprint $table) {
            $table->integer('order_rad_detail_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('order_rad_id')->nullable();
            $table->integer('tindakan_id')->nullable();
            $table->string('nama_tindakan')->nullable();
            $table->smallInteger('flag_bridging')->nullable();
            $table->timestamp('send_bridging_time', 6)->nullable();
            $table->integer('user_id_bridging')->nullable();
            $table->string('nomor_bridging')->nullable();
            $table->integer('bagian_id_pelaksana')->nullable();

            $table->index(['order_rad_detail_id', 'order_rad_id', 'tindakan_id'], 'order_rad_detail_order_rad_detail_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_rad_detail');
    }
};
