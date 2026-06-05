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
        Schema::create('bill_temp', function (Blueprint $table) {
            $table->integer('bill_temp_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable()->index('bill_temp_status_batal_idx');
            $table->integer('registrasi_detail_id')->nullable();
            $table->integer('pasien_id')->nullable()->index('bill_temp_pasien_id_idx');
            $table->integer('bagian_id')->nullable()->index('bill_temp_bagian_id_idx');
            $table->integer('nasabah_id')->nullable()->index('bill_temp_nasabah_id_idx');
            $table->integer('kelas_ruang_id')->nullable();
            $table->integer('hak_kelas_ruang_id')->nullable();
            $table->timestamp('tgl_bill', 6)->nullable();
            $table->string('sep', 20)->nullable();
            $table->smallInteger('flag_cito')->nullable();
            $table->smallInteger('status_selesai')->nullable();
            $table->smallInteger('bill_temp_jenis')->nullable();
            $table->integer('peresepan_obat_id')->nullable();
            $table->smallInteger('flag_tampil')->nullable()->index('bill_temp_flag_tampil_idx');
            $table->integer('status_selisih_kelas')->nullable();

            $table->index(['bill_temp_id', 'registrasi_detail_id', 'pasien_id', 'bagian_id', 'nasabah_id', 'kelas_ruang_id', 'hak_kelas_ruang_id', 'peresepan_obat_id'], 'bill_temp_bill_temp_id_idx');
            $table->index(['registrasi_detail_id', 'status_batal'], 'bill_temp_de_registrasi_detail_id');
            $table->index(['bill_temp_id', 'status_batal', 'registrasi_detail_id', 'nasabah_id', 'bagian_id'], 'idx_bill_temp01');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bill_temp');
    }
};
