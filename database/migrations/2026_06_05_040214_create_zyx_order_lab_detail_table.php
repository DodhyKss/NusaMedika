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
        Schema::create('zyx_order_lab_detail', function (Blueprint $table) {
            $table->increments('zyx_order_lab_detail_id');
            $table->integer('order_lab_detail_id');
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('order_lab_id')->nullable();
            $table->integer('tindakan_id')->nullable();
            $table->string('nama_tindakan')->nullable();
            $table->timestamp('kirim_time', 6)->nullable();
            $table->smallInteger('flag_ambil')->nullable();
            $table->timestamp('ambil_time', 6)->nullable();
            $table->integer('kode_lab')->nullable();
            $table->integer('tindakan_group_id')->nullable();
            $table->string('nama_file_tind_luar')->nullable();
            $table->smallInteger('status')->nullable();
            $table->timestamp('mod_change', 6)->nullable();
            $table->integer('bagian_id_pelaksana')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zyx_order_lab_detail');
    }
};
