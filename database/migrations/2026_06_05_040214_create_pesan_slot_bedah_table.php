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
        Schema::create('pesan_slot_bedah', function (Blueprint $table) {
            $table->integer('pesan_slot_bedah_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('emr_id')->nullable()->index('idx_pesan_slot_bedah_emr');
            $table->integer('registrasi_detail_id')->nullable()->index('pesan_slot_bedah_registrasi_detail_id_idx');
            $table->string('tgl_rencana_operasi', 10)->nullable()->index('pesan_slot_bedah_tgl_rencana_operasi_idx');
            $table->timestamp('tgl_jam_bedah', 6)->nullable()->index('idx_pesan_slot_bedah_tgl');
            $table->string('estimasi_lama_bedah', 4)->nullable();
            $table->string('slot_kamar_bedah', 6)->nullable()->index('pesan_slot_bedah_slot_kamar_bedah_idx');
            $table->integer('tim_bedah_id')->nullable();
            $table->text('diagnosa_pra_operasi')->nullable();
            $table->string('jenis_tindakan_bedah')->nullable();
            $table->string('jenis_operasi', 10)->nullable();
            $table->string('emergency_operasi', 10)->nullable();
            $table->integer('bagian_id')->nullable();
            $table->integer('kelas_id')->nullable();
            $table->string('no_handphone', 13)->nullable();
            $table->string('no_handphone_keluarga', 13)->nullable();
            $table->string('alat_khusus_bedah', 150)->nullable();
            $table->integer('dokter_bedah')->nullable();
            $table->string('jenis_anestesi', 10)->nullable();
            $table->string('keterangan_bedah', 1000)->nullable();
            $table->integer('status')->nullable();
            $table->timestamp('input_time_mulai')->nullable();
            $table->timestamp('input_time_selesai')->nullable();
            $table->text('comment_slot')->nullable();
            $table->timestamp('input_time_dipanggil')->nullable();
            $table->timestamp('input_time_masuk')->nullable();
            $table->smallInteger('jenis_tindakan')->nullable();
            $table->integer('dokter_anestesi')->nullable();

            $table->index(['pesan_slot_bedah_id', 'emr_id', 'registrasi_detail_id', 'tgl_rencana_operasi', 'tim_bedah_id', 'slot_kamar_bedah', 'kelas_id', 'bagian_id'], 'pesan_slot_bedah_pesan_slot_bedah_id_idx');
            $table->index(['tgl_jam_bedah'], 'pesan_slot_bedah_tgl_jam_bedah_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pesan_slot_bedah');
    }
};
