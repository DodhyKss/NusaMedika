<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Master Implementasi Keperawatan: katalog intervensi/asuhan keperawatan yang
 * dapat dipilih perawat saat mencatat Implementasi Keperawatan di EMR.
 *
 * Sengaja hanya kode + nama (pola master sederhana seperti `barang_jenis`);
 * tidak ada kategori/uraian karena belum dipakai form.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('implementasi', function (Blueprint $table) {
            $table->increments('implementasi_id');
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->string('kode_implementasi', 20)->unique();
            $table->string('nama_implementasi', 255);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('implementasi');
    }
};
