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
        Schema::create('peresepan_obat_dispense_head', function (Blueprint $table) {
            $table->integer('peresepan_obat_dispense_head_id');
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('peresepan_obat_id')->nullable();
            $table->integer('bagian_id_penanggung')->nullable();
            $table->integer('tot_bill_resep')->nullable();
            $table->integer('no_resep_bpjs')->nullable();
            $table->integer('tipe_transaksi')->nullable();
            $table->integer('dokter_id_kirim')->nullable();
            $table->string('pembeli')->nullable();
            $table->integer('nrp')->nullable();
            $table->integer('bagian_id')->nullable();
            $table->string('jenis_beli', 10)->nullable();
            $table->integer('pasien_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peresepan_obat_dispense_head');
    }
};
