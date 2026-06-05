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
        Schema::create('zyx_pesan_slot_bedah', function (Blueprint $table) {
            $table->increments('zyx_pesan_slot_bedah_id');
            $table->integer('pesan_slot_bedah_id');
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('emr_id')->nullable();
            $table->integer('registrasi_detail_id')->nullable();
            $table->string('tgl_rencana_operasi', 10)->nullable();
            $table->timestamp('tgl_jam_bedah', 6)->nullable();
            $table->string('estimasi_lama_bedah', 4)->nullable();
            $table->string('slot_kamar_bedah', 6)->nullable();
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
            $table->timestamp('mod_change', 6)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zyx_pesan_slot_bedah');
    }
};
