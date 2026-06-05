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
        Schema::create('bill_obat_otc', function (Blueprint $table) {
            $table->integer('bill_obat_otc_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('bill_temp_detail_id')->nullable();
            $table->integer('no_pembayaran')->nullable();
            $table->integer('jumlah_tebus')->nullable();
            $table->decimal('harga_jual', 18)->nullable();

            $table->index(['bill_obat_otc_id', 'bill_temp_detail_id'], 'bill_obat_otc_bill_obat_otc_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bill_obat_otc');
    }
};
