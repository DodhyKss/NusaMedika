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
        Schema::create('urut_visum', function (Blueprint $table) {
            $table->integer('urut_visum_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('emr_id')->nullable();
            $table->integer('urut_visum_no')->nullable();
            $table->string('bulan_visum', 10)->nullable();
            $table->string('tahun_visum', 10)->nullable();
            $table->string('inisial_no_visum', 10)->nullable();
            $table->string('kode_visum', 30)->nullable();

            $table->index(['urut_visum_id', 'emr_id', 'kode_visum'], 'urut_visum_urut_visum_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('urut_visum');
    }
};
