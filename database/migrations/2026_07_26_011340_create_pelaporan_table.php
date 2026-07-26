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
        Schema::create('pelaporan', function (Blueprint $table) {
            $table->integer('laporan_id');
            $table->integer('input_user_id')->nullable();
            $table->string('deskripsi_masalah')->nullable();
            $table->string('jenis_masalah')->nullable();
            $table->timestamp('input_time')->nullable();
            $table->smallInteger('status_batal')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pelaporan');
    }
};
