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
        Schema::create('zyx_aset_detail', function (Blueprint $table) {
            $table->increments('zyx_aset_detail_id');
            $table->integer('aset_detail_id');
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('aset_id')->nullable();
            $table->integer('barang_id')->nullable();
            $table->integer('bed_id')->nullable();
            $table->decimal('kuantitas_jumlah', 18)->nullable();
            $table->decimal('kuantitas_pakai', 18)->nullable();
            $table->decimal('kuantitas_service', 18)->nullable();
            $table->decimal('kuantitas_rusak', 18)->nullable();
            $table->timestamp('mod_change', 6)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zyx_aset_detail');
    }
};
