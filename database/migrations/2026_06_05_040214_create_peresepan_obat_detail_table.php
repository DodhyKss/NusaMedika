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
        Schema::create('peresepan_obat_detail', function (Blueprint $table) {
            $table->integer('peresepan_obat_detail_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable()->index('peresepan_obat_detail_status_batal_idx');
            $table->integer('peresepan_obat_id')->nullable()->index('peresepan_obat_detail_de_peresepan_obat_id');
            $table->integer('barang_id')->nullable()->index('peresepan_obat_detail_barang_id_idx');
            $table->string('sigma_1', 100)->nullable();
            $table->string('sigma_2', 100)->nullable();
            $table->integer('jumlah')->nullable();
            $table->integer('dispense')->nullable();
            $table->integer('substitusi_barang_id')->nullable();
            $table->smallInteger('barang_jenis_id')->nullable()->index('peresepan_obat_detail_barang_jenis_id_idx');
            $table->string('obat_racikan')->nullable();
            $table->string('nomor_batch', 100)->nullable();
            $table->timestamp('tgl_expired', 6)->nullable();
            $table->string('aturan_pakai', 100)->nullable();
            $table->string('satuan_aturan_pakai', 100)->nullable();
            $table->string('rute_pemberian', 100)->nullable();
            $table->string('aturan_jam', 100)->nullable();
            $table->string('catatan', 250)->nullable();
            $table->integer('flag_stop')->nullable();
            $table->integer('stop_user_id')->nullable();
            $table->timestamp('flag_stop_time')->nullable();
            $table->integer('flag_copy_resep')->nullable();
            $table->integer('flag_kronis')->nullable();
            $table->integer('jml_inacbg')->nullable();
            $table->integer('jml_mutasi')->nullable();
            $table->integer('jumlah_copy_resep')->nullable();
            $table->string('med_req_id')->nullable();
            $table->string('med_disp_id')->nullable();

            $table->index(['peresepan_obat_detail_id', 'peresepan_obat_id', 'barang_id', 'substitusi_barang_id', 'barang_jenis_id', 'stop_user_id'], 'peresepan_obat_detail_peresepan_obat_detail_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peresepan_obat_detail');
    }
};
