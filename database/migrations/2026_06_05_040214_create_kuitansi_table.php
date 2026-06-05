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
        Schema::create('kuitansi', function (Blueprint $table) {
            $table->integer('kuitansi_id')->primary();
            $table->timestamp('input_time', 6)->nullable()->index('kuitansi_input_time_idx');
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable()->index('kuitansi_status_batal_idx');
            $table->integer('kuitansi_no')->nullable()->index('kuitansi_kuitansi_no_idx');
            $table->string('kuitansi_code', 20)->nullable();
            $table->string('kuitansi_tipe', 20)->nullable()->index('kuitansi_kuitansi_tipe_idx');
            $table->timestamp('tanggal_kuitansi', 6)->nullable();
            $table->integer('bill_kasir_id')->nullable()->index('kuitansi_bill_kasir_id_idx');
            $table->integer('registrasi_id')->nullable()->index('kuitansi_registrasi_id_idx');
            $table->integer('nasabah_id')->nullable();
            $table->decimal('total_tagihan', 18)->nullable();
            $table->integer('tipe_bayar_id')->nullable();
            $table->integer('bagian_id')->nullable();
            $table->integer('bank_id')->nullable();
            $table->integer('status_selesai')->nullable();
            $table->integer('status_kirim')->nullable();
            $table->decimal('jumlah_bayar', 18)->nullable();
            $table->string('jumlah_kembali', 18)->nullable();
            $table->decimal('jumlah_selisih', 18)->nullable();
            $table->string('no_kartu', 50)->nullable();
            $table->timestamp('masa_belaku')->nullable();
            $table->string('provider', 15)->nullable();
            $table->string('appr_code', 20)->nullable();
            $table->string('nama_pengirim', 50)->nullable();
            $table->string('no_rekening_pengirim', 20)->nullable();
            $table->timestamp('tanggal_pencairan_cek')->nullable();
            $table->decimal('total_um', 18)->nullable();
            $table->integer('pasien_id')->nullable();
            $table->integer('cara_bayar')->nullable();
            $table->decimal('materai', 18)->nullable();
            $table->integer('uang_muka_id')->nullable();
            $table->smallInteger('flag_tarik')->nullable()->comment('0 = Ada Update, 2 = Sudah Ketarik');
            $table->timestamp('tgl_ver', 6)->nullable();
            $table->decimal('selisih_harga', 18)->nullable();

            $table->index(['kuitansi_id', 'bill_kasir_id', 'registrasi_id', 'nasabah_id', 'bagian_id', 'tipe_bayar_id', 'pasien_id'], 'idx_kuitansi01');
            $table->index(['kuitansi_id', 'kuitansi_tipe', 'tanggal_kuitansi', 'bill_kasir_id', 'registrasi_id', 'nasabah_id', 'tipe_bayar_id', 'bagian_id', 'bank_id', 'uang_muka_id'], 'kuitansi_kuitansi_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kuitansi');
    }
};
