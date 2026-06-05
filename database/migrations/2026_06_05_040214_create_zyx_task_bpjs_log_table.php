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
        Schema::create('zyx_task_bpjs_log', function (Blueprint $table) {
            $table->increments('zyx_task_bpjs_log_id');
            $table->integer('task_bpjs_log_id');
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->string('task_id', 2)->nullable();
            $table->timestamp('push_time', 6)->nullable();
            $table->integer('registrasi_id')->nullable();
            $table->timestamp('mod_change', 6)->nullable();
            $table->json('response')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zyx_task_bpjs_log');
    }
};
