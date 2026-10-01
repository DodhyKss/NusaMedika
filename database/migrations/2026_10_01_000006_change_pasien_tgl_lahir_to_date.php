<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `pasien.tgl_lahir` sebelumnya bertipe `timestamp(6)`.
 *
 * MySQL hanya menerima rentang 1970-01-01 s.d. 2038-01-19 untuk kolom TIMESTAMP,
 * sehingga pasien yang lahir sebelum 1970 (kelompok usia lanjut yang justru memakai
 * instrumen Timed "Up and Go" pada pengkajian risiko jatuh) gagal disimpan:
 *   ERROR 1292 (22007): Incorrect datetime value for column 'tgl_lahir'
 *
 * `date` adalah tipe yang tepat untuk tanggal lahir: rentang 1000-01-01 s.d.
 * 9999-12-31 dan bebas batas tahun 2038. Konversi timestamp -> date aman (bagian jam
 * dipotong) dan tidak mengubah tanggal yang sudah tersimpan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pasien', function (Blueprint $table) {
            $table->date('tgl_lahir')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('pasien', function (Blueprint $table) {
            $table->timestamp('tgl_lahir', 6)->nullable()->change();
        });
    }
};
