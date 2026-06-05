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
        Schema::create('zyx_bill_temp', function (Blueprint $table) {
            $table->increments('zyx_bill_temp_id');
            $table->integer('bill_temp_id');
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('registrasi_detail_id')->nullable();
            $table->integer('pasien_id')->nullable();
            $table->integer('bagian_id')->nullable();
            $table->integer('nasabah_id')->nullable();
            $table->integer('kelas_ruang_id')->nullable();
            $table->integer('hak_kelas_ruang_id')->nullable();
            $table->timestamp('tgl_bill', 6)->nullable();
            $table->string('sep', 20)->nullable();
            $table->smallInteger('flag_cito')->nullable();
            $table->smallInteger('status_selesai')->nullable();
            $table->smallInteger('bill_temp_jenis')->nullable();
            $table->integer('peresepan_obat_id')->nullable();
            $table->smallInteger('flag_tampil')->nullable();
            $table->integer('status_selisih_kelas')->nullable();
            $table->timestamp('mod_change', 6)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zyx_bill_temp');
    }
};
