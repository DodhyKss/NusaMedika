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
        Schema::create('bed_log_hd', function (Blueprint $table) {
            $table->integer('bed_log_hd_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('pasien_id')->nullable();
            $table->integer('registrasi_detail_id')->nullable();
            $table->integer('bed_id')->nullable();
            $table->timestamp('check_in_time', 6)->nullable();
            $table->integer('check_in_user_id')->nullable();
            $table->integer('estimasi_time')->nullable();
            $table->timestamp('check_out_time', 6)->nullable();
            $table->integer('check_out_user_id')->nullable();

            $table->index(['bed_log_hd_id', 'pasien_id', 'registrasi_detail_id', 'bed_id'], 'bed_log_hd_bed_log_hd_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bed_log_hd');
    }
};
