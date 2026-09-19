<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_laboratorium', function (Blueprint $table) {
            $table->increments('order_laboratorium_id');
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();

            $table->string('no_order', 30);
            $table->integer('pasien_id');
            $table->integer('registrasi_id');
            $table->integer('registrasi_detail_id');           // detail ASAL (poli/ruang peminta)
            $table->integer('registrasi_detail_tujuan_id');    // detail BARU di bagian lab (dibuat saat order)
            $table->integer('bagian_asal_id');                 // poli/ruang perujuk
            $table->integer('bagian_tujuan_id');               // bagian lab
            $table->integer('dokter_id')->nullable();          // pegawai peminta (utk jasa medis)
            $table->string('prioritas', 15)->nullable();       // BIASA / CITO / SEGERA
            $table->smallInteger('status_order')->default(0);  // 0 Menunggu, 1 Diproses, 2 Selesai, 3 Batal
            $table->timestamp('tanggal_order')->nullable();
            $table->timestamp('tanggal_terima')->nullable();
            $table->timestamp('tanggal_hasil')->nullable();
            $table->integer('petugas_pelaksana_id')->nullable();
            $table->smallInteger('kelas_id')->nullable();      // snapshot kelas_bpjs asal
            $table->integer('hak_kelas_id')->nullable();       // snapshot kelas_ruang asal
            $table->text('keterangan')->nullable();

            $table->index('registrasi_id');
            $table->index('registrasi_detail_id');
            $table->index('registrasi_detail_tujuan_id');
            $table->index('bagian_tujuan_id');
            $table->index('status_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_laboratorium');
    }
};
