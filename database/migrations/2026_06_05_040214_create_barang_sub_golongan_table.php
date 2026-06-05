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
        Schema::create('barang_sub_golongan', function (Blueprint $table) {
            $table->integer('barang_sub_golongan_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('barang_golongan_id')->nullable();
            $table->string('nama_sub_golongan', 100)->nullable();

            $table->index(['barang_sub_golongan_id', 'barang_golongan_id'], 'barang_sub_golongan_barang_sub_golongan_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barang_sub_golongan');
    }
};
