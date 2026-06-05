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
        Schema::create('zyx_integrasi_gabung_bill_simrs_inacbg', function (Blueprint $table) {
            $table->increments('zyx_integrasi_gabung_bill_id');
            $table->integer('integrasi_gabung_bill_id');
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->string('sep', 10)->nullable();
            $table->integer('registrasi_id_head')->nullable();
            $table->decimal('pro_non_bedah', 18)->nullable();
            $table->decimal('ten_ahli', 18)->nullable();
            $table->decimal('rad', 18)->nullable();
            $table->decimal('rehab', 18)->nullable();
            $table->decimal('obat', 18)->nullable();
            $table->decimal('alkes', 18)->nullable();
            $table->decimal('pro_bed', 18)->nullable();
            $table->decimal('perawatan', 18)->nullable();
            $table->decimal('lab', 18)->nullable();
            $table->decimal('kamar', 18)->nullable();
            $table->decimal('obat_kronis', 18)->nullable();
            $table->decimal('bmhp', 18)->nullable();
            $table->decimal('konsul', 18)->nullable();
            $table->decimal('penunjang', 18)->nullable();
            $table->decimal('darah', 18)->nullable();
            $table->decimal('intensif', 18)->nullable();
            $table->decimal('obat_kemo', 18)->nullable();
            $table->decimal('sewa_alat', 18)->nullable();
            $table->timestamp('mod_change', 6)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zyx_integrasi_gabung_bill_simrs_inacbg');
    }
};
