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
        Schema::create('berkas', function (Blueprint $table) {
            $table->integer('berkas_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('pasien_id')->nullable();
            $table->integer('user_id_kirim')->nullable();
            $table->integer('bagian_id_kirim')->nullable();
            $table->timestamp('tgl_jam_kirim', 6)->nullable();
            $table->integer('user_id_terima')->nullable();
            $table->integer('bagian_id_terima')->nullable();
            $table->timestamp('tgl_jam_terima', 6)->nullable();
            $table->integer('status_berkas')->nullable();
            $table->integer('registrasi_detail_id')->nullable();

            $table->index(['berkas_id', 'pasien_id', 'user_id_kirim', 'bagian_id_kirim', 'user_id_terima', 'bagian_id_terima'], 'berkas_berkas_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('berkas');
    }
};
