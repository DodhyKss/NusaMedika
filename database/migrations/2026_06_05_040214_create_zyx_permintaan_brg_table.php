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
        Schema::create('zyx_permintaan_brg', function (Blueprint $table) {
            $table->increments('zyx_permintaan_brg_id');
            $table->integer('permintaan_brg_id');
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->string('kode_mutasi', 100)->nullable();
            $table->integer('urutan_mutasi')->nullable();
            $table->timestamp('tgl_mutasi', 6)->nullable();
            $table->integer('minta_bagian_id')->nullable();
            $table->integer('kirim_bagian_id')->nullable();
            $table->timestamp('tgl_acc', 6)->nullable();
            $table->integer('acc_user_id')->nullable();
            $table->timestamp('tgl_terima', 6)->nullable();
            $table->integer('terima_user_id')->nullable();
            $table->integer('status')->nullable();
            $table->string('kategori_permintaan', 10)->nullable();
            $table->timestamp('mod_change', 6)->nullable();
            $table->integer('flag_kirim')->nullable();
            $table->string('kebutuhan_mutasi', 10)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zyx_permintaan_brg');
    }
};
