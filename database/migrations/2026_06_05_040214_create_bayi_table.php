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
        Schema::create('bayi', function (Blueprint $table) {
            $table->integer('bayi_id')->primary();
            $table->integer('pasien_id')->nullable();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->string('nama_ayah', 100)->nullable();
            $table->string('alamat_ayah', 250)->nullable();
            $table->string('hp_ayah', 13)->nullable();
            $table->string('ktp_ayah', 18)->nullable();
            $table->string('pekerjaan_ayah', 100)->nullable();
            $table->timestamp('tgl_lahir_ayah', 6)->nullable();
            $table->string('no_skk', 100)->nullable();
            $table->integer('user_cetak')->nullable();
            $table->timestamp('input_time_cetak', 6)->nullable();
            $table->integer('no_urut')->nullable();
            $table->string('berat_badan', 10)->nullable();
            $table->string('panjang', 10)->nullable();
            $table->string('icd_normal', 10)->nullable();
            $table->integer('user_id_penolong')->nullable();
            $table->bigInteger('kelurahan_ayah')->nullable();
            $table->string('icd_tindakan', 10)->nullable();
            $table->string('icd_kembar', 10)->nullable();
            $table->string('icd_kelainan', 10)->nullable();
            $table->string('sep_ibu')->nullable();
            $table->integer('registrasi_bayi')->nullable();
            $table->smallInteger('no_anak')->nullable();
            $table->integer('user_id_bidan')->nullable();
            $table->string('no_kk', 20)->nullable();
            $table->string('no_bayi')->nullable();

            $table->index(['bayi_id', 'pasien_id', 'no_skk', 'no_urut'], 'bayi_bayi_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bayi');
    }
};
