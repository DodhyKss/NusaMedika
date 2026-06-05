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
        Schema::create('zyx_index_remunerasi_detail', function (Blueprint $table) {
            $table->increments('zyx_index_remunerasi_detail_id');
            $table->integer('index_remunerasi_detail_id');
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('index_remunerasi_id')->nullable();
            $table->integer('profesi_id')->nullable();
            $table->integer('bagian_id')->nullable();
            $table->integer('jabatan_id')->nullable();
            $table->integer('relation_id')->nullable();
            $table->timestamp('mod_change', 6)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zyx_index_remunerasi_detail');
    }
};
