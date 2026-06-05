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
        Schema::create('flash_news', function (Blueprint $table) {
            $table->integer('flash_news_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->string('flash_news', 600)->nullable();
            $table->timestamp('tgl_flash_news', 6)->nullable();
            $table->integer('flag_flash_news')->nullable();

            $table->index(['flash_news_id', 'tgl_flash_news', 'flag_flash_news'], 'flash_news_flash_news_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flash_news');
    }
};
