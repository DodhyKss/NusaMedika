<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bed_log', function (Blueprint $table) {
            $table->increments('bed_log_id');
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('pasien_id')->nullable();
            $table->integer('registrasi_detail_id')->nullable();
            $table->integer('bed_asal_id')->nullable();
            $table->integer('bagian_asal_id')->nullable();
            $table->integer('bed_id')->nullable();
            $table->integer('bagian_id')->nullable();
            $table->string('aksi', 30)->nullable();
            $table->timestamp('tgl_pindah')->nullable();

            $table->index('pasien_id');
            $table->index('registrasi_detail_id');
            $table->index('bed_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bed_log');
    }
};