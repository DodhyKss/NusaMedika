<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('emr', function (Blueprint $table) {
            $table->increments('emr_id');
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('form_id')->nullable();
            $table->integer('pegawai_id')->nullable();
            $table->timestamp('tgl_jam', 6)->nullable();
            $table->integer('registrasi_detail_id')->nullable();
            $table->integer('approve_pegawai_id')->nullable();
            $table->timestamp('approve_time', 6)->nullable();
            $table->integer('pasien_id')->nullable();
            $table->integer('registrasi_id')->nullable();
            $table->json('data')->nullable();

            $table->index('registrasi_detail_id');
            $table->index('form_id');
            $table->index('pasien_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emr');
    }
};
