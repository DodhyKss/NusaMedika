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
        Schema::create('bed_waitlist', function (Blueprint $table) {
            $table->integer('bed_waitlist_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('bed_log_id')->nullable()->index('bed_waitlist_bed_log_id_idx');
            $table->integer('bed_id_asal')->nullable()->index('bed_waitlist_bed_id_asal_idx');
            $table->integer('bed_id_tujuan')->nullable()->index('bed_waitlist_bed_id_tujuan_idx');
            $table->integer('pasien_id')->nullable()->index('bed_waitlist_pasien_id_idx');
            $table->integer('registrasi_detail_id')->nullable()->index('bed_waitlist_registrasi_detail_id_idx');
            $table->integer('status_bed_waitlist')->nullable()->index('bed_waitlist_status_bed_waitlist_idx');
            $table->string('keterangan_waitlist', 250)->nullable();
            $table->timestamp('tgl_approve', 6)->nullable();
            $table->integer('user_approve')->nullable();

            $table->index(['bed_waitlist_id', 'pasien_id', 'bed_log_id', 'bed_id_asal', 'bed_id_tujuan'], 'bed_waitlist_bed_waitlist_id_idx');
            $table->index(['bed_waitlist_id', 'pasien_id', 'registrasi_detail_id', 'bed_log_id', 'bed_id_asal', 'bed_id_tujuan'], 'idx_bed_waitlist01');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bed_waitlist');
    }
};
