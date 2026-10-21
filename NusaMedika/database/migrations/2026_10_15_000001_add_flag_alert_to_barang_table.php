<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tandai barang high-alert (obat yang wajib double check dua perawat).
 * Dipakai form EMR 111 Double Check Obat High Alert — prefill <datalist>
 * daftar obat high alert (`Barang::aktif()->where('flag_alert', 1)`).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('barang', function (Blueprint $table) {
            $table->boolean('flag_alert')->default(0)->after('kategori_barang');
        });
    }

    public function down(): void
    {
        Schema::table('barang', function (Blueprint $table) {
            $table->dropColumn('flag_alert');
        });
    }
};
