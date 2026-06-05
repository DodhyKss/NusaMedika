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
            $table->integer('antrian_system_urutan_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('antrian_system_id')->nullable();
            $table->timestamp('tanggal', 6)->nullable()->index('antrian_system_urutan_tanggal_idx');
            $table->string('tipe')->nullable()->index('antrian_system_urutan_tipe_idx');
            $table->string('flag_panggil')->nullable();
            $table->integer('urutan')->nullable()->index('antrian_system_urutan_urutan_idx');
            $table->integer('loket')->nullable();
            $table->timestamp('time_panggil', 6)->nullable();
            $table->text('keterangan')->nullable();
            $table->integer('pasien_id')->nullable();
            $table->integer('bagian_id')->nullable();

            $table->index(['antrian_system_urutan_id', 'antrian_system_id', 'tanggal', 'urutan', 'tipe', 'loket', 'flag_panggil', 'input_time', 'time_panggil'], 'antrian_system_urutan_antrian_system_urutan_id_idx');
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
