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
        Schema::create('order_mikrobiologi_detail', function (Blueprint $table) {
            $table->integer('order_mikrobiologi_detail_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('order_mikrobiologi_id')->nullable();
            $table->integer('tindakan_id')->nullable();
            $table->string('nama_tindakan')->nullable();
            $table->integer('tindakan_group_id')->nullable();
            $table->smallInteger('flag_selesai')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_mikrobiologi_detail');
    }
};
