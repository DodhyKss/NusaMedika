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
        Schema::create('zyx_antrian_system_detail', function (Blueprint $table) {
            $table->increments('zyx_antrian_system_detail_id');
            $table->integer('antrian_system_detail_id')->nullable();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('antrian_system_id')->nullable();
            $table->string('ip')->nullable();
            $table->string('mac')->nullable();
            $table->integer('loket')->nullable();
            $table->string('tittle')->nullable();
            $table->char('initial', 1)->nullable();
            $table->timestamp('mod_change', 6)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zyx_antrian_system_detail');
    }
};
