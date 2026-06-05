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
        Schema::create('user_group_detail', function (Blueprint $table) {
            $table->integer('user_group_detail_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('user_group_id')->nullable();
            $table->integer('sub_menu_id')->nullable();

            $table->index(['user_group_detail_id', 'user_group_id', 'sub_menu_id'], 'idx_user_group_dfetail01');
            $table->index(['user_group_detail_id', 'user_group_id', 'sub_menu_id'], 'user_group_detail_user_group_detail_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_group_detail');
    }
};
