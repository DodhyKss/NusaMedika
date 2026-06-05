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
        Schema::create('peresepan_obat_dispense_apotek', function (Blueprint $table) {
            $table->integer('peresepan_obat_dispense_apotek_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('peresepan_obat_detail_id')->nullable();
            $table->integer('sigma_1')->nullable();
            $table->integer('sigma_2')->nullable();
            $table->integer('dispense')->nullable();
            $table->integer('barang_id')->nullable();
            $table->string('nomor_batch')->nullable();
            $table->string('rute_pemberian')->nullable();
            $table->smallInteger('status_kirim')->nullable();
            $table->json('response')->nullable();
            $table->string('type_obat')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peresepan_obat_dispense_apotek');
    }
};
