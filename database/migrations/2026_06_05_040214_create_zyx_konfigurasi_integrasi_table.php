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
        Schema::create('zyx_konfigurasi_integrasi', function (Blueprint $table) {
            $table->increments('zyx_konfigurasi_integrasi_id');
            $table->integer('konfigurasi_integrasi_id');
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->string('domain_protocol', 5)->nullable();
            $table->string('domain_name', 100)->nullable();
            $table->smallInteger('domain_port')->nullable();
            $table->string('consumer_id', 32)->nullable();
            $table->string('consumer_secret', 100)->nullable();
            $table->string('service_name', 100)->nullable();
            $table->string('tipe', 20)->nullable();
            $table->string('kode_rs', 10)->nullable();
            $table->string('kelas_rs', 10)->nullable();
            $table->integer('flag_aplicares')->nullable();
            $table->integer('durasi')->nullable();
            $table->timestamp('last_update_aplicares', 6)->nullable();
            $table->string('ukey', 100)->nullable();
            $table->timestamp('mod_change', 6)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zyx_konfigurasi_integrasi');
    }
};
