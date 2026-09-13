<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat kolom no_pemesanan / no_mutasi nullable supaya nomor transaksi
     * dapat dihasilkan setelah insert (pola dua-langkah di controller):
     * simpan dulu baris, lalu isi nomor berdasarkan auto-increment id.
     */
    public function up(): void
    {
        Schema::table('pemesanan', function (Blueprint $table) {
            $table->string('no_pemesanan', 50)->nullable()->change();
        });

        Schema::table('mutasi_barang', function (Blueprint $table) {
            $table->string('no_mutasi', 50)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('pemesanan', function (Blueprint $table) {
            $table->string('no_pemesanan', 50)->nullable(false)->change();
        });

        Schema::table('mutasi_barang', function (Blueprint $table) {
            $table->string('no_mutasi', 50)->nullable(false)->change();
        });
    }
};
