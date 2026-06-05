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
        Schema::create('peresepan_obat_dispense', function (Blueprint $table) {
            $table->integer('peresepan_obat_dispense_id')->primary();
            $table->timestamp('input_time', 6)->nullable()->index('peresepan_obat_dispense_input_time_idx');
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('peresepan_obat_detail_id')->nullable()->index('peresepan_obat_dispense_de_peresepan_obat_detail_id');
            $table->integer('barang_id')->nullable()->index('peresepan_obat_dispense_barang_id_idx');
            $table->string('sigma_1', 100)->nullable();
            $table->string('sigma_2', 100)->nullable();
            $table->integer('dispense')->nullable();
            $table->smallInteger('barang_jenis_id')->nullable();
            $table->string('obat_racikan')->nullable();
            $table->string('nomor_batch', 100)->nullable()->index('peresepan_obat_dispense_nomor_batch_idx');
            $table->timestamp('tgl_expired', 6)->nullable();
            $table->string('aturan_pakai', 100)->nullable();
            $table->string('satuan_aturan_pakai', 100)->nullable();
            $table->string('rute_pemberian', 100)->nullable();
            $table->string('aturan_jam', 100)->nullable();
            $table->smallInteger('flag_kronis')->nullable();
            $table->decimal('harga_jual', 18)->nullable();
            $table->smallInteger('flag_stop')->nullable();
            $table->smallInteger('stop_user_id')->nullable();
            $table->timestamp('flag_stop_time')->nullable();
            $table->decimal('harga_beli', 18)->default(0);
            $table->decimal('harga_persediaan', 18)->default(0);
            $table->integer('peresepan_obat_dispense_head_id')->nullable();
            $table->integer('service')->nullable();
            $table->integer('bagian_id_dispense')->nullable();
            $table->integer('flagging_resep_items')->nullable()->comment('1 = obat akut
2 = kronis
3 = kemo
4 = alkes capd
5 = insulin
6 = inhaler
7 = racikan');
            $table->text('catatan')->nullable();

            $table->index(['peresepan_obat_dispense_id', 'peresepan_obat_detail_id', 'barang_id', 'barang_jenis_id', 'bagian_id_dispense'], 'peresepan_obat_dispense_peresepan_obat_dispense_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peresepan_obat_dispense');
    }
};
