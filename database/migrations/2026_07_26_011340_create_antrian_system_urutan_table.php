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
        Schema::create('antrian_system_urutan', function (Blueprint $table) {
            $table->integer('antrian_system_urutan_id');
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('antrian_system_id')->nullable();
            $table->timestamp('tanggal', 6)->nullable();
            $table->string('tipe')->nullable();
            $table->string('flag_panggil')->nullable();
            $table->integer('urutan')->nullable();
            $table->integer('loket')->nullable();
            $table->timestamp('time_panggil', 6)->nullable();
            $table->text('keterangan')->nullable();
            $table->integer('pasien_id')->nullable();
            $table->integer('bagian_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('antrian_system_urutan');
    }
};
