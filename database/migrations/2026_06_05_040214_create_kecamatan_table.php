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
        Schema::create('kecamatan', function (Blueprint $table) {
            $table->integer('kecamatan_id')->primary();
            $table->integer('kabupaten_id')->nullable();
            $table->string('nama_kecamatan')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('kode_wilayah_kecamatan')->nullable();

            $table->index(['kecamatan_id', 'kabupaten_id'], 'kecamatan_kecamatan_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kecamatan');
    }
};
