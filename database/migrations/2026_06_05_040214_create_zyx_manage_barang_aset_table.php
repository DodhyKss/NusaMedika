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
        Schema::create('zyx_manage_barang_aset', function (Blueprint $table) {
            $table->increments('zyx_manage_barang_aset_id');
            $table->integer('manage_barang_aset_id');
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('aset_detail_id')->nullable();
            $table->integer('barang_id')->nullable();
            $table->decimal('kuantitas_kirim', 18)->nullable();
            $table->timestamp('tgl_kirim', 6)->nullable();
            $table->integer('kirim_user_id')->nullable();
            $table->decimal('kuantitas_terima', 18)->nullable();
            $table->timestamp('tgl_terima', 6)->nullable();
            $table->integer('terima_user_id')->nullable();
            $table->decimal('kuantitas_kirim_balik', 18)->nullable();
            $table->timestamp('tgl_kirim_balik', 6)->nullable();
            $table->integer('kirim_balik_user_id')->nullable();
            $table->decimal('kuantitas_terima_balik', 18)->nullable();
            $table->timestamp('tgl_terima_balik', 6)->nullable();
            $table->integer('terima_balik_user_id')->nullable();
            $table->timestamp('mod_change', 6)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zyx_manage_barang_aset');
    }
};
