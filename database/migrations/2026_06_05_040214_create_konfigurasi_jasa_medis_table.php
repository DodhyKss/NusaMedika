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
        Schema::create('konfigurasi_jasa_medis', function (Blueprint $table) {
            $table->integer('konfigurasi_jasa_medis_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->string('nama_jasa', 50)->nullable();
            $table->string('layanan_id', 3)->nullable();
            $table->integer('nasabah_id')->nullable();
            $table->decimal('persentase', 18);
            $table->integer('jenis_tindakan')->nullable();
            $table->integer('jumlah_dokter')->nullable()->comment('1 :  jumlah dokter > 1
[null] : jumlah dokter = 1');

            $table->index(['konfigurasi_jasa_medis_id', 'layanan_id', 'nasabah_id', 'jenis_tindakan'], 'konfigurasi_jasa_medis_konfigurasi_jasa_medis_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('konfigurasi_jasa_medis');
    }
};
