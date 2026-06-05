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
        Schema::create('zyx_tindakan_group', function (Blueprint $table) {
            $table->increments('zyx_tindakan_group_id');
            $table->integer('tindakan_group_id');
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->string('nama_group_tindakan')->nullable();
            $table->integer('referensi_tindakan_group')->nullable();
            $table->integer('bagian_id')->nullable();
            $table->string('rename_group', 50)->nullable();
            $table->integer('number_urutan')->nullable();
            $table->timestamp('mod_change', 6)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zyx_tindakan_group');
    }
};
