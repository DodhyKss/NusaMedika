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
        Schema::create('bill_temp_detail', function (Blueprint $table) {
            $table->integer('bill_temp_detail_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('bill_temp_id')->nullable();
            $table->integer('jenis_tindakan_id')->nullable();
            $table->integer('tarif_id')->nullable()->index('bill_temp_detail_tarif_id_idx');
            $table->integer('jumlah')->nullable();
            $table->decimal('biaya', 18)->default(0);
            $table->decimal('biaya_hak', 18)->default(0);
            $table->decimal('diskon', 18)->default(0);
            $table->decimal('diskon_hak', 18)->default(0);
            $table->decimal('total', 18)->default(0);
            $table->decimal('total_hak', 18)->default(0);
            $table->smallInteger('status_proses')->nullable();
            $table->integer('pegawai_id')->nullable()->index('bill_temp_detail_pegawai_id_idx');
            $table->integer('tindakan_id')->nullable()->index('bill_temp_detail_tindakan_id_idx');
            $table->integer('peresepan_obat_detail_id')->nullable();
            $table->integer('harga_jual_obat_id')->nullable()->index('bill_temp_detail_harga_jual_obat_id_idx');
            $table->smallInteger('flag_kronis')->nullable();
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
            $table->integer('peresepan_obat_dispense_id')->nullable()->index('bill_temp_detail_peresepan_obat_dispense_id_idx');
            $table->timestamp('tgl_tindakan')->nullable();
            $table->integer('asa_konfigurasi_id')->nullable();
            $table->integer('emr_id')->nullable()->index('bill_temp_detail_emr_id_idx');
            $table->decimal('persentase', 18)->nullable()->default(0);
            $table->decimal('persentase_2', 18)->nullable()->default(0);
            $table->decimal('persentase_3', 18)->nullable()->default(0);

            $table->index(['bill_temp_detail_id', 'bill_temp_id', 'jenis_tindakan_id', 'tarif_id', 'pegawai_id', 'tindakan_id', 'peresepan_obat_detail_id', 'harga_jual_obat_id'], 'bill_temp_detail_bill_temp_detail_id_idx');
            $table->index(['bill_temp_id', 'status_batal', 'tindakan_id', 'tarif_id', 'jenis_tindakan_id', 'peresepan_obat_detail_id'], 'idx_bill_temp_detail');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bill_temp_detail');
    }
};
