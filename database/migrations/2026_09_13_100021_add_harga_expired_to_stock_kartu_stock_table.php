<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Stock kini unik per (barang, bagian, no_batch, harga_jual): barang/batch sama
 * tapi harga jual berbeda = row stock baru (tidak menambah stok row lama).
 * Tambah tgl_expired (barang medis) + snapshot harga di stock & kartu_stock.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (['stock', 'kartu_stock'] as $tabel) {
            if (! Schema::hasTable($tabel)) {
                continue;
            }

            Schema::table($tabel, function (Blueprint $table) {
                if (! Schema::hasColumn($table->getTable(), 'harga_beli')) {
                    $table->decimal('harga_beli', 15, 2)->nullable();
                }

                if (! Schema::hasColumn($table->getTable(), 'harga_jual')) {
                    $table->decimal('harga_jual', 15, 2)->nullable();
                }

                if (! Schema::hasColumn($table->getTable(), 'tgl_expired')) {
                    $table->date('tgl_expired')->nullable();
                }
            });
        }

        if (Schema::hasTable('stock')) {
            $indeks = Schema::getIndexListing('stock');

            if (in_array('stock_barang_id_bagian_id_no_batch_unique', $indeks, true)) {
                Schema::table('stock', fn (Blueprint $table) => $table->dropUnique('stock_barang_id_bagian_id_no_batch_unique'));
            }

            if (! in_array('stock_barang_id_bagian_id_no_batch_harga_jual_unique', $indeks, true)) {
                Schema::table('stock', fn (Blueprint $table) => $table->unique(['barang_id', 'bagian_id', 'no_batch', 'harga_jual'], 'stock_barang_id_bagian_id_no_batch_harga_jual_unique'));
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('stock')) {
            $indeks = Schema::getIndexListing('stock');

            if (in_array('stock_barang_id_bagian_id_no_batch_harga_jual_unique', $indeks, true)) {
                Schema::table('stock', fn (Blueprint $table) => $table->dropUnique('stock_barang_id_bagian_id_no_batch_harga_jual_unique'));
            }

            if (! in_array('stock_barang_id_bagian_id_no_batch_unique', $indeks, true)) {
                Schema::table('stock', fn (Blueprint $table) => $table->unique(['barang_id', 'bagian_id', 'no_batch'], 'stock_barang_id_bagian_id_no_batch_unique'));
            }
        }

        foreach (['stock', 'kartu_stock'] as $tabel) {
            if (! Schema::hasTable($tabel)) {
                continue;
            }

            $drop = array_filter(
                ['harga_beli', 'harga_jual', 'tgl_expired'],
                fn ($kolom) => Schema::hasColumn($tabel, $kolom)
            );

            if ($drop) {
                Schema::table($tabel, fn (Blueprint $table) => $table->dropColumn(...$drop));
            }
        }
    }
};
