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
        Schema::create('zyx_konfigurasi_satu_sehat', function (Blueprint $table) {
            $table->increments('zyx_konfigurasi_satu_sehat_id');
            $table->integer('konfigurasi_satu_sehat_id');
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->string('auth_url', 100)->nullable();
            $table->string('base_url', 100)->nullable();
            $table->string('client_id', 100)->nullable();
            $table->string('client_secret', 100)->nullable();
            $table->integer('durasi')->nullable();
            $table->timestamp('last_update_integrasi', 6)->nullable();
            $table->smallInteger('active')->nullable();
            $table->string('id_organization', 100)->nullable();
            $table->bigInteger('kode_provinsi')->nullable();
            $table->bigInteger('kode_kabupaten')->nullable();
            $table->bigInteger('kode_kecamatan')->nullable();
            $table->bigInteger('kode_kelurahan')->nullable();
            $table->timestamp('mod_change', 6)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zyx_konfigurasi_satu_sehat');
    }
};
