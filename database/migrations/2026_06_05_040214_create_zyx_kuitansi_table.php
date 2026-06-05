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
        Schema::create('zyx_kuitansi', function (Blueprint $table) {
            $table->increments('zyx_kuitansi_id');
            $table->integer('kuitansi_id');
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('kuitansi_no')->nullable();
            $table->string('kuitansi_code', 20)->nullable();
            $table->string('kuitansi_tipe', 20)->nullable();
            $table->timestamp('tanggal_kuitansi', 6)->nullable();
            $table->integer('bill_kasir_id')->nullable();
            $table->integer('registrasi_id')->nullable();
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
            $table->timestamp('mod_change', 6)->nullable();
            $table->smallInteger('flag_tarik')->nullable()->comment('0 = Ada Update, 2 = Sudah Ketarik');
            $table->timestamp('tgl_ver', 6)->nullable();
            $table->decimal('selisih_harga', 18)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zyx_kuitansi');
    }
};
