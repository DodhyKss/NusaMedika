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
        Schema::create('zyx_sub_menu', function (Blueprint $table) {
            $table->increments('zyx_sub_menu_id');
            $table->integer('sub_menu_id');
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('menu_id')->nullable();
            $table->string('nama_sub_menu', 100)->nullable();
            $table->string('file_sub_menu', 100)->nullable();
            $table->integer('urutan_sub_menu')->nullable();
            $table->timestamp('mod_change', 6)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zyx_sub_menu');
    }
};
