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
            $table->integer('bed_waitlist_id');
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('bed_log_id')->nullable();
            $table->integer('bed_id_asal')->nullable();
            $table->integer('bed_id_tujuan')->nullable();
            $table->integer('pasien_id')->nullable();
            $table->integer('registrasi_detail_id')->nullable();
            $table->integer('status_bed_waitlist')->nullable();
            $table->string('keterangan_waitlist', 250)->nullable();
            $table->timestamp('tgl_approve', 6)->nullable();
            $table->integer('user_approve')->nullable();
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
