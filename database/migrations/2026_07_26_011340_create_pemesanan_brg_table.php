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
        Schema::create('pemesanan_brg', function (Blueprint $table) {
            $table->integer('pemesanan_brg_id');
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->string('kode_pesanan', 100)->nullable();
            $table->integer('urutan_pesanan')->nullable();
            $table->timestamp('tgl_pesanan', 6)->nullable();
            $table->integer('supplier_id')->nullable();
            $table->timestamp('tgl_acc', 6)->nullable();
            $table->smallInteger('status_terima')->nullable();
            $table->integer('acc_user_id')->nullable();
            $table->timestamp('tgl_revisi')->nullable();
            $table->integer('revisi_user_id')->nullable();
            $table->text('catatan_po')->nullable();
            $table->integer('kebutuhan_po')->nullable()->comment('1 = Reguler
2 = BPJS
3 = Fopi');
            $table->integer('kirim_email')->nullable();
            $table->integer('jenis_permintaan')->nullable();
            $table->smallInteger('kategori_harga')->nullable();
            $table->integer('supplier_barang_id')->nullable();
            $table->decimal('selisih_bayar', 18)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pemesanan_brg');
    }
};
