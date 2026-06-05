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
        Schema::create('i_care_log', function (Blueprint $table) {
            $table->integer('i_care_log_id')->primary();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('input_time')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->string('status')->nullable();
            $table->integer('registrasi_detail_id')->nullable();
            $table->json('response')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('i_care_log');
    }
};
