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
        Schema::create('pesan_makanan_monitoring_asupan', function (Blueprint $table) {
            $table->integer('pesan_makanan_monitoring_asupan_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('registrasi_id')->nullable();
            $table->integer('makanan_id')->nullable();
            $table->timestamp('tanggal_pesanan', 6)->nullable();
            $table->time('jam_pesanan')->nullable();
            $table->string('diit', 250)->nullable();
            $table->string('waktu_pemberian', 10)->nullable();
            $table->string('makanan_pokok', 100)->nullable();
            $table->string('lauk_hewani', 100)->nullable();
            $table->string('lauk_nabati', 100)->nullable();
            $table->string('susu', 100)->nullable();
            $table->string('sayur', 100)->nullable();
            $table->string('buah', 100)->nullable();
            $table->string('nama_petugas', 100)->nullable();
            $table->decimal('kalori', 18)->nullable()->default(0);

            $table->index(['pesan_makanan_monitoring_asupan_id', 'registrasi_id', 'makanan_id'], 'pesan_makanan_monitoring_asupan_pesan_makanan_monitoring_asupan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pesan_makanan_monitoring_asupan');
    }
};
