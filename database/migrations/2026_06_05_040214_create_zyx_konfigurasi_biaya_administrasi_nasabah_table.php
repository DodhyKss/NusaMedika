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
        Schema::create('zyx_konfigurasi_biaya_administrasi_nasabah', function (Blueprint $table) {
            $table->increments('zyx_konfigurasi_biaya_administrasi_nasabah_id');
            $table->integer('konfigurasi_biaya_administrasi_nasabah_id');
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('konfigurasi_biaya_administrasi_id')->nullable();
            $table->integer('nasabah_id')->nullable();
            $table->timestamp('mod_change', 6)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zyx_konfigurasi_biaya_administrasi_nasabah');
    }
};
