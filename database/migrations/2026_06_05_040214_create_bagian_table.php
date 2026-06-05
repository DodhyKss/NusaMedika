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
        Schema::create('bagian', function (Blueprint $table) {
            $table->integer('bagian_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable()->index('bagian_status_batal_idx');
            $table->string('nama_bagian', 100)->nullable()->index('bagian_nama_bagian_idx');
            $table->integer('referensi_bagian')->nullable()->index('bagian_referensi_bagian_idx');
            $table->string('group_bagian', 10)->nullable();
            $table->string('seri_bagian', 100)->nullable()->index('bagian_seri_bagian_idx');
            $table->string('id_satu_sehat', 50)->nullable();
            $table->string('flag_eksekutif', 50)->nullable()->index('bagian_flag_eksekutif_idx')->comment('NULL = tidak eksekutif
1 = poli atau klinik eksekutif');
            $table->string('id_location', 50)->nullable();

            $table->index(['bagian_id', 'referensi_bagian'], 'bagian_bagian_id_idx');
            $table->index(['bagian_id', 'referensi_bagian', 'status_batal'], 'idx_bagian01');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bagian');
    }
};
