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
        Schema::create('penerimaan_brg', function (Blueprint $table) {
            $table->integer('penerimaan_brg_id');
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->string('kode_terima', 100)->nullable();
            $table->integer('urutan_terima')->nullable();
            $table->timestamp('tgl_terima', 6)->nullable();
            $table->integer('pemesanan_brg_id')->nullable();
            $table->integer('kirim_bagian_id')->nullable();
            $table->integer('terima_bagian_id')->nullable();
            $table->string('txt_pengirim', 100)->nullable();
            $table->string('jenis_penerimaan', 20)->nullable();
            $table->integer('permintaan_brg_id')->nullable();
            $table->decimal('biaya_ongkir', 18)->nullable();
            $table->decimal('biaya_materai', 18)->nullable();
            $table->string('no_faktur')->nullable();
            $table->string('tanggal_faktur')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penerimaan_brg');
    }
};
