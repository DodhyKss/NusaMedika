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
        Schema::create('upload_fpk', function (Blueprint $table) {
            $table->integer('upload_fpk_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('bulan_upload')->nullable();
            $table->integer('tahun_upload')->nullable();
            $table->json('data_upload')->nullable()->comment('Terdiri dari sep, tgl_verifikasi, riil_rs, diajukan, disetujui');
            $table->string('jenis_rawat', 3)->nullable();
            $table->string('no_verifikasi', 50)->nullable();

            $table->index(['upload_fpk_id', 'jenis_rawat', 'no_verifikasi'], 'upload_fpk_upload_fpk_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('upload_fpk');
    }
};
