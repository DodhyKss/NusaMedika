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
        Schema::create('bill_kasir', function (Blueprint $table) {
            $table->integer('bill_kasir_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('registrasi_detail_id')->nullable();
            $table->integer('pasien_id')->nullable();
            $table->integer('bagian_id')->nullable();
            $table->integer('nasabah_id')->nullable();
            $table->integer('kelas_ruang_id')->nullable();
            $table->integer('hak_kelas_ruang_id')->nullable();
            $table->timestamp('tgl_bill', 6)->nullable();
            $table->string('sep', 20)->nullable();
            $table->smallInteger('flag_cito')->nullable();
            $table->smallInteger('status_selesai')->nullable();
            $table->smallInteger('bill_kasir_jenis')->nullable();
            $table->integer('peresepan_obat_id')->nullable();
            $table->integer('faktur_id')->nullable();
            $table->integer('status_selisih_kelas')->nullable();
            $table->timestamp('tgl_ver', 6)->nullable();

            $table->index(['bill_kasir_id', 'mod_user_id', 'registrasi_detail_id', 'pasien_id', 'bagian_id', 'nasabah_id', 'kelas_ruang_id', 'hak_kelas_ruang_id', 'peresepan_obat_id', 'faktur_id'], 'bill_kasir_bill_kasir_id_idx');
            $table->index(['bill_kasir_id', 'status_batal', 'registrasi_detail_id', 'nasabah_id', 'pasien_id', 'bagian_id'], 'idx_bill_kasir01');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bill_kasir');
    }
};
