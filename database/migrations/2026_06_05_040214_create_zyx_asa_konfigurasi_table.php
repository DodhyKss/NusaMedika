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
        Schema::create('zyx_asa_konfigurasi', function (Blueprint $table) {
            $table->increments('zyx_asa_konfigurasi_id');
            $table->integer('asa_konfigurasi_id');
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->string('nama_asa', 100)->nullable();
            $table->decimal('persentase_asa', 18)->nullable();
            $table->smallInteger('flag_aktif')->nullable();
            $table->timestamp('mod_change', 6)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zyx_asa_konfigurasi');
    }
};
