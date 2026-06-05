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
        Schema::create('bed_log', function (Blueprint $table) {
            $table->integer('bed_log_id')->primary();
            $table->timestamp('input_time', 6)->nullable()->index('bed_log_input_time_idx');
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable()->index('bed_log_status_batal_idx');
            $table->integer('pasien_id')->nullable();
            $table->integer('bed_id')->nullable();
            $table->integer('status_bed_log')->nullable()->index('bed_log_status_bed_log_idx');
            $table->string('keterangan', 200)->nullable();
            $table->timestamp('time_pulang', 6)->nullable();
            $table->string('no_hp_keluarga', 13)->nullable();
            $table->integer('registrasi_detail_id')->nullable()->index('bed_log_registrasi_detail_id_idx');
            $table->string('alasan_pulang', 200)->nullable();
            $table->integer('flag_isolasi')->nullable()->comment('1 = Isolasi
2 = Normal
3 = Covid 19');
            $table->integer('flag_isolasi_pressure')->nullable()->comment('1 = Negative Pressure
2 = Non Negative Pressure');
            $table->integer('flag_ventilator')->nullable()->comment('1 = Dengan Ventilator
2 = Tanpa Ventilator');

            $table->index(['bed_log_id', 'pasien_id', 'bed_id', 'registrasi_detail_id'], 'bed_log_bed_log_id_idx');
            $table->index(['pasien_id', 'bed_id', 'registrasi_detail_id', 'bed_log_id', 'input_time', 'status_bed_log'], 'bed_log_pasien_id_idx');
            $table->index(['bed_log_id', 'pasien_id', 'bed_id', 'registrasi_detail_id'], 'idx_bed_log01');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bed_log');
    }
};
