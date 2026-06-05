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
        Schema::create('zyx_konfirmasi_prb', function (Blueprint $table) {
            $table->increments('zyx_konfirmasi_prb_id');
            $table->integer('konfirmasi_prb_id');
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('emr_id')->nullable();
            $table->string('faskes', 100)->nullable();
            $table->string('petugas_bpjs', 50)->nullable();
            $table->string('informasi')->nullable();
            $table->smallInteger('status_konfirmasi')->nullable()->comment('1 = Terkonfirmasi
2 = Batal Konfirmasi');
            $table->smallInteger('alasan_batal_prb')->nullable()->comment('1 = Obat Tidak Sesuai PRB
2 = Pasien Tidak Datang Ke Farmasi');
            $table->timestamp('mod_change', 6)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zyx_konfirmasi_prb');
    }
};
