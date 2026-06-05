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
        Schema::create('upload_fpk_pending', function (Blueprint $table) {
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->integer('mod_time')->nullable();
            $table->smallInteger('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('bulan_upload')->nullable();
            $table->integer('tahun_upload')->nullable();
            $table->json('data_upload')->nullable();
            $table->string('jenis_rawat', 3)->nullable();
            $table->string('no_verifikasi', 50)->nullable();
            $table->integer('id')->primary();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('upload_fpk_pending');
    }
};
