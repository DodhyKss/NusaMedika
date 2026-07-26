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
        Schema::create('master_tv_info', function (Blueprint $table) {
            $table->integer('master_tv_info_id');
            $table->integer('input_user_id')->nullable();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->json('informasi')->nullable();
            $table->json('link_video')->nullable();
            $table->text('running_text')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_tv_info');
    }
};
