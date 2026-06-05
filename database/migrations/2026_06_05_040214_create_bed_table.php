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
        Schema::create('bed', function (Blueprint $table) {
            $table->integer('bed_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable()->index('bed_status_batal_idx');
            $table->integer('bagian_id')->nullable()->index('bed_bagian_id_idx');
            $table->integer('no_kamar')->nullable()->index('bed_no_kamar_idx');
            $table->string('nama_bed', 30)->nullable();
            $table->integer('status_bed')->nullable()->index('bed_status_bed_idx');
            $table->integer('kelas_id')->nullable()->index('bed_kelas_id_idx');
            $table->timestamp('tgl_masuk', 6)->nullable();
            $table->integer('pasien_id_1')->nullable()->index('bed_pasien_id_1_idx');
            $table->integer('pasien_id_2')->nullable()->index('bed_pasien_id_2_idx');
            $table->integer('flag_isolasi')->nullable()->comment('1 = Isolasi
2 = Normal
3 = Covid 19');
            $table->string('keterangan', 250)->nullable();
            $table->integer('flag_isolasi_pressure')->nullable()->comment('1 = Negative Pressure
2 = Non Negative Pressure');
            $table->integer('flag_ventilator')->nullable()->comment('1 = Dengan Ventilator
2 = Tanpa Ventilator');
            $table->integer('lokasi')->nullable();
            $table->integer('flag_neonatus')->nullable()->comment('0 = Tidak Neonatus
1 = Neonatus');
            $table->smallInteger('siap_kirim')->nullable()->index('bed_siap_kirim_idx');
            $table->string('kodekelas', 10)->nullable();
            $table->string('namakelas', 50)->nullable();
            $table->integer('flag_extra')->nullable()->index('bed_flag_extra_idx');
            $table->integer('bor')->nullable()->index('bed_bor_idx');

            $table->index(['bed_id', 'bagian_id', 'no_kamar', 'kelas_id', 'pasien_id_1'], 'bed_bed_id_idx');
            $table->index(['bed_id', 'pasien_id_1', 'status_batal'], 'idx_bed');
            $table->index(['pasien_id_1'], 'idx_bed_pasien');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bed');
    }
};
