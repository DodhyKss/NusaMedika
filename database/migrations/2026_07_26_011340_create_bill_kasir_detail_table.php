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
        Schema::create('bill_kasir_detail', function (Blueprint $table) {
            $table->integer('bill_kasir_detail_id');
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('bill_kasir_id')->nullable();
            $table->integer('jenis_tindakan_id')->nullable();
            $table->integer('tarif_id')->nullable();
            $table->integer('jumlah')->nullable();
            $table->decimal('biaya', 18)->default(0);
            $table->decimal('biaya_hak', 18)->default(0);
            $table->decimal('diskon', 18)->default(0);
            $table->decimal('diskon_hak', 18)->default(0);
            $table->decimal('total', 18)->default(0);
            $table->decimal('total_hak', 18)->default(0);
            $table->integer('pegawai_id')->nullable();
            $table->integer('tindakan_id')->nullable();
            $table->smallInteger('status_proses')->nullable();
            $table->integer('peresepan_obat_detail_id')->nullable();
            $table->integer('harga_jual_obat_id')->nullable();
            $table->integer('kuitansi_id')->nullable();
            $table->integer('kuitansi_user_id')->nullable();
            $table->integer('flag_kuitansi')->nullable();
            $table->timestamp('tgl_kuitansi')->nullable();
            $table->decimal('biaya_2', 18)->default(0);
            $table->decimal('biaya_hak_2', 18)->default(0);
            $table->decimal('diskon_2', 18)->default(0);
            $table->decimal('diskon_hak_2', 18)->default(0);
            $table->decimal('total_2', 18)->default(0);
            $table->decimal('total_hak_2', 18)->default(0);
            $table->decimal('biaya_3', 18)->default(0);
            $table->decimal('biaya_hak_3', 18)->default(0);
            $table->decimal('diskon_3', 18)->default(0);
            $table->decimal('diskon_hak_3', 18)->default(0);
            $table->decimal('total_3', 18)->default(0);
            $table->decimal('total_hak_3', 18)->default(0);
            $table->decimal('biaya_administrasi', 18)->default(0);
            $table->decimal('biaya_administrasi_2', 18)->default(0);
            $table->decimal('biaya_administrasi_3', 18)->default(0);
            $table->string('no_kuitansi_1', 20)->nullable();
            $table->string('no_kuitansi_2', 20)->nullable();
            $table->string('no_kuitansi_3', 20)->nullable();
            $table->integer('asa_konfigurasi_id')->nullable();
            $table->integer('peresepan_obat_dispense_id')->nullable();
            $table->timestamp('tgl_tindakan')->nullable();
            $table->integer('emr_id')->nullable();
            $table->timestamp('tgl_ver', 6)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bill_kasir_detail');
    }
};
