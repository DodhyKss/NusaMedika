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
        Schema::create('provinsi', function (Blueprint $table) {
            $table->integer('provinsi_id')->primary();
            $table->string('nama_provinsi')->nullable();
            $table->integer('status_batal')->nullable();
            $table->integer('kode_wilayah_provinsi')->nullable();

            $table->index(['provinsi_id', 'nama_provinsi'], 'provinsi_provinsi_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('provinsi');
    }
};
