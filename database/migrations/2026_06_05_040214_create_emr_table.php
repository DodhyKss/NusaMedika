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
        Schema::create('emr', function (Blueprint $table) {
            $table->integer('emr_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable()->index('emr_input_user_id_idx');
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable()->index('emr_de_idx_status_batal');
            $table->integer('form_id')->nullable()->index();
            $table->integer('pegawai_id')->nullable()->index();
            $table->timestamp('tgl_jam', 6)->nullable()->index('emr_de_idx_year_2023');
            $table->integer('registrasi_detail_id')->nullable()->index();
            $table->integer('approve_pegawai_id')->nullable();
            $table->timestamp('approve_time', 6)->nullable();
            $table->integer('pasien_id')->nullable()->index();
            $table->integer('registrasi_id')->nullable()->index();
            $table->json('data')->nullable();

            $table->index(['tgl_jam'], 'emr_de_idx_year_2024');
            $table->index(['tgl_jam'], 'emr_de_idx_year_2025');
            $table->index(['emr_id', 'form_id', 'pegawai_id', 'pasien_id', 'registrasi_id', 'registrasi_detail_id', 'tgl_jam'], 'emr_emr_id2_idx');
            $table->index(['emr_id', 'pasien_id', 'registrasi_detail_id', 'pegawai_id', 'form_id'], 'emr_emr_id_idx');
            $table->index(['tgl_jam'], 'emr_tgl_jam_idx');
            $table->index(['form_id', 'status_batal'], 'idx_emr_form_status');
            $table->index(['pasien_id'], 'idx_emr_pasien_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emr');
    }
};
