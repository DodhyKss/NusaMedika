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
        Schema::create('zyx_faktur', function (Blueprint $table) {
            $table->increments('zyx_faktur_id');
            $table->integer('faktur_id');
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('nasabah_id')->nullable();
            $table->integer('no_faktur')->nullable();
            $table->integer('bulan_faktur')->nullable();
            $table->integer('tahun_faktur')->nullable();
            $table->string('kode_faktur', 100)->nullable();
            $table->timestamp('tgl_faktur', 6)->nullable();
            $table->integer('status_kirim_berkas')->nullable();
            $table->integer('status_selesai')->nullable();
            $table->string('jenis_rawat', 30)->nullable();
            $table->integer('flag_bridging')->nullable();
            $table->timestamp('input_time_bridging', 6)->nullable();
            $table->timestamp('tgl_faktur_supplier', 6)->nullable();
            $table->timestamp('mod_change', 6)->nullable();
            $table->string('no_invoice', 100)->nullable();
            $table->timestamp('tgl_ver', 6)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zyx_faktur');
    }
};
