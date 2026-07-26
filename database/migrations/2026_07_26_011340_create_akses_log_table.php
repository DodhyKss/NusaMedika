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
        Schema::create('akses_log', function (Blueprint $table) {
            $table->integer('akses_log_id');
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->string('session_id', 100)->nullable();
            $table->timestamp('login_time', 6)->nullable();
            $table->timestamp('logout_time', 6)->nullable();
            $table->integer('user_id')->nullable();
            $table->string('ip_address', 16)->nullable();
            $table->timestamp('expired')->nullable();
            $table->smallInteger('status')->nullable();
            $table->string('token', 100)->nullable();
            $table->string('useragent', 150)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('akses_log');
    }
};
