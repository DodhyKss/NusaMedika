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
        Schema::create('index_remunerasi_pegawai', function (Blueprint $table) {
            $table->integer('index_remunerasi_pegawai_id');
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('pegawai_id')->nullable();
            $table->integer('bulan')->nullable();
            $table->integer('tahun')->nullable();
            $table->json('data_index_detail_pegawai')->nullable()->comment('nilai json terdiri dari :
- profesi_dasar
- resiko_kerja
- beban_kerja
- jadwal_pagi
- jadwal_malam
- peringatan_lisan
- peringatan_tertulis_karu
- peringatan_tertulis_kains
- peringatan_tertulis1_struktural
- peringatan_tertulis2_struktural
- peringatan_tertulis3_struktural
- tidak_apel
- tidak_rapat
- terlambat_masuk_kerja<30menit
- terlambat_masuk_kerja_>30menit
- merusak_inventaris_ringan
- merusak_inventaris_sedang
- merusak_inventaris_berat
- inventaris_hilang_kecil
- inventaris_hilang_sedang
- inventaris_hilang_berat
- salah_input
- komplain_pasien
- berpakaian_tidak_sesuai
- pencemaran_nama_rs
- delegasi_<5hari
- delegasi_>5hari
- pembicara_dalam_seminar
- surat_tugas
- mengikuti_senam');
            $table->decimal('grand_total_index', 18)->nullable();
            $table->integer('bagian_id')->nullable();
            $table->integer('jabatan_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('index_remunerasi_pegawai');
    }
};
