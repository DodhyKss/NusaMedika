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
        Schema::create('zyx_permintaan_brg_detail', function (Blueprint $table) {
            $table->increments('zyx_permintaan_brg_detail_id');
            $table->integer('permintaan_brg_detail_id');
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('permintaan_brg_id')->nullable();
            $table->integer('barang_id')->nullable();
            $table->decimal('jumlah_minta', 18)->nullable();
            $table->decimal('jumlah_acc', 18)->nullable();
            $table->decimal('jumlah_terima', 18)->nullable();
            $table->decimal('konversi', 18)->nullable();
            $table->decimal('total_terima', 18)->nullable();
            $table->string('nomor_batch', 100)->nullable();
            $table->decimal('harga_jual', 18)->nullable();
            $table->timestamp('tgl_expired')->nullable();
            $table->decimal('jumlah_kirim', 18)->nullable();
            $table->timestamp('tgl_acc')->nullable();
            $table->integer('acc_user_id')->nullable();
            $table->timestamp('tgl_terima')->nullable();
            $table->integer('terima_user_id')->nullable();
            $table->decimal('kuantitas_piutang', 18)->nullable();
            $table->integer('draft_material_request_id')->nullable();
            $table->timestamp('tgl_kirim')->nullable();
            $table->integer('kirim_user_id')->nullable();
            $table->timestamp('mod_change', 6)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zyx_permintaan_brg_detail');
    }
};
