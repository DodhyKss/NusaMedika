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
        Schema::create('antrian_system_detail', function (Blueprint $table) {
            $table->integer('antrian_system_detail_id')->primary();
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

            $table->index(['antrian_system_detail_id', 'antrian_system_id', 'loket', 'ip', 'mac', 'input_user_id', 'input_time'], 'antrian_system_detail_antrian_system_detail_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('antrian_system_detail');
    }
};
