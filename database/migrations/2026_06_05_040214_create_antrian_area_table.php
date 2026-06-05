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
        Schema::create('antrian_area', function (Blueprint $table) {
            $table->integer('antrian_area_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->string('nama_area')->nullable();
            $table->string('spesifikasi_antrian')->nullable();
            $table->json('data_area')->nullable();

            $table->index(['antrian_area_id', 'spesifikasi_antrian', 'input_user_id', 'input_time'], 'antrian_area_antrian_area_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('antrian_area');
    }
};
