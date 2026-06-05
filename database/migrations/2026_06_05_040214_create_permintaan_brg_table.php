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
        Schema::create('permintaan_brg', function (Blueprint $table) {
            $table->integer('permintaan_brg_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->string('kode_mutasi', 100)->nullable();
            $table->integer('urutan_mutasi')->nullable();
            $table->timestamp('tgl_mutasi', 6)->nullable();
            $table->integer('minta_bagian_id')->nullable();
            $table->integer('kirim_bagian_id')->nullable();
            $table->timestamp('tgl_acc', 6)->nullable();
            $table->integer('acc_user_id')->nullable();
            $table->timestamp('tgl_terima', 6)->nullable();
            $table->integer('terima_user_id')->nullable();
            $table->integer('status')->nullable()->comment('SISI BAGIAN MINTA :
status = NULL artinya belum diacc oleh si bagian kirim (bagian tujuan) (PROSES ACC)
status = 1 artinya barang pada permintaan sudah diacc tapi belum diinput jumlah kirimnya  (SUDAH ACC / ON PROCESS)
status = 2 artinya barang pada permintaan sudah diacc dan sudah diinput jumlah kirimnya namun belum diterima oleh bagian minta atau baru diacc dan diinput jumlah kirim sebagian barangnya (DIPROSES SEBAGIAN).
status = 3 artinya barang pada permintaan sudah diterima oleh bagian minta (SUDAH DITERIMA / DONE)


SISI BAGIAN KIRIM :
status = NULL artinya belum diproses oleh si bagian kirim. (BELUM PROSES)
status = 1 artinya barang pada permintaan sudah diacc tapi belum diinput jumlah kirimnya (DIPROSES SEBAGIAN)
status = 2 artinya barang pada permintaan sudah diacc dan sudah diinput jumlah kirimnya namun belum dilakukan penerimaan oleh bagian minta (COMPLETE MENUNGGU DITERIMA)
status = 3 artinya barang pada permintaan sudah diterima oleh bagian minta (COMPLETE)');
            $table->string('kategori_permintaan', 10)->nullable();
            $table->integer('flag_kirim')->nullable();
            $table->string('kebutuhan_mutasi', 10)->nullable();

            $table->index(['permintaan_brg_id', 'urutan_mutasi', 'tgl_mutasi', 'minta_bagian_id', 'kirim_bagian_id', 'tgl_acc', 'acc_user_id', 'tgl_terima', 'terima_user_id'], 'permintaan_brg_permintaan_brg_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permintaan_brg');
    }
};
