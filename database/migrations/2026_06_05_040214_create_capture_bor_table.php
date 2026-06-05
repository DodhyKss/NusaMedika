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
        Schema::create('capture_bor', function (Blueprint $table) {
            $table->integer('capture_bor_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->timestamp('tgl_bor', 6)->nullable();
            $table->integer('bagian_id')->nullable();
            $table->string('nama_bagian', 100)->nullable();
            $table->integer('kelas_ruang_id')->nullable();
            $table->string('nama_kelas_ruang', 100)->nullable();
            $table->integer('jumlah_bed')->nullable();
            $table->integer('jumlah_terisi')->nullable();
            $table->integer('pasien_masuk')->nullable();
            $table->integer('pasien_keluar')->nullable();
            $table->integer('bed_kosong')->nullable();

            $table->index(['capture_bor_id', 'bagian_id', 'kelas_ruang_id'], 'capture_bor_capture_bor_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('capture_bor');
    }
};
