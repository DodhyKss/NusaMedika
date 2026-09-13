<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Redesain inventory berbasis batch:
 *  - harga beli/jual & stok minimum dihapus dari master barang (beda per batch).
 *  - master harga baru: harga_barang (barang_id + no_batch + harga_beli/harga_jual).
 *  - kolom no_batch ditambahkan ke penerimaan_detail, mutasi_barang_detail,
 *    stock, dan kartu_stock; harga_jual ditambahkan ke pemesanan_detail.
 * Idempotent: jalankan juga pada DB fresh (kolom & tabel sudah dibuat oleh
 * migration base hasil edit), sehingga semua operasi di-guard.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('harga_barang')) {
            $this->dropKolomHargaBarang();
            $this->tambahKolomBatch();
            $this->susunUniqueStock();

            return;
        }

        Schema::create('harga_barang', function (Blueprint $table) {
            $table->increments('harga_id');
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('barang_id');
            $table->string('no_batch', 50);
            $table->decimal('harga_beli', 15, 2)->nullable();
            $table->decimal('harga_jual', 15, 2)->nullable();

            $table->unique(['barang_id', 'no_batch']);
        });

        $this->dropKolomHargaBarang();
        $this->tambahKolomBatch();
        $this->susunUniqueStock();
    }

    public function down(): void
    {
        $this->dropUniqueStock();

        foreach (['pemesanan_detail' => ['harga_jual'], 'penerimaan_detail' => ['no_batch', 'harga_jual'], 'mutasi_barang_detail' => ['no_batch'], 'stock' => ['no_batch'], 'kartu_stock' => ['no_batch']] as $tabel => $kolom) {
            if (Schema::hasTable($tabel)) {
                $drop = array_filter($kolom, fn ($k) => Schema::hasColumn($tabel, $k));
                if ($drop) {
                    Schema::table($tabel, fn (Blueprint $table) => $table->dropColumn(...$drop));
                }
            }
        }

        Schema::table('barang', function (Blueprint $table) {
            $table->decimal('harga_beli', 15, 2)->nullable();
            $table->decimal('harga_jual', 15, 2)->nullable();
            $table->decimal('stok_minimum', 18, 2)->nullable()->default(0);
        });

        Schema::dropIfExists('harga_barang');

        $this->susunUniqueStock();
    }

    private function dropKolomHargaBarang(): void
    {
        $drop = array_filter(
            ['harga_beli', 'harga_jual', 'stok_minimum'],
            fn ($kolom) => Schema::hasTable('barang') && Schema::hasColumn('barang', $kolom)
        );

        if ($drop) {
            Schema::table('barang', fn (Blueprint $table) => $table->dropColumn(...$drop));
        }
    }

    private function tambahKolomBatch(): void
    {
        $tambahan = [
            'pemesanan_detail' => ['harga_jual' => 'decimal'],
            'penerimaan_detail' => ['no_batch' => 'string', 'harga_jual' => 'decimal'],
            'mutasi_barang_detail' => ['no_batch' => 'string'],
            'stock' => ['no_batch' => 'string'],
            'kartu_stock' => ['no_batch' => 'string'],
        ];

        foreach ($tambahan as $tabel => $kolom) {
            if (! Schema::hasTable($tabel)) {
                continue;
            }

            Schema::table($tabel, function (Blueprint $table) use ($kolom) {
                foreach ($kolom as $nama => $jenis) {
                    if (Schema::hasColumn($table->getTable(), $nama)) {
                        continue;
                    }

                    if ($jenis === 'decimal') {
                        $table->decimal($nama, 15, 2)->nullable();
                    } else {
                        $table->string($nama, 50)->nullable();
                    }
                }
            });
        }
    }

    private function susunUniqueStock(): void
    {
        if (! Schema::hasTable('stock')) {
            return;
        }

        $indeks = Schema::getIndexListing('stock');

        if (in_array('stock_barang_id_bagian_id_unique', $indeks, true)) {
            Schema::table('stock', fn (Blueprint $table) => $table->dropUnique('stock_barang_id_bagian_id_unique'));
        }

        if (! in_array('stock_barang_id_bagian_id_no_batch_unique', $indeks, true)) {
            Schema::table('stock', fn (Blueprint $table) => $table->unique(['barang_id', 'bagian_id', 'no_batch'], 'stock_barang_id_bagian_id_no_batch_unique'));
        }
    }

    private function dropUniqueStock(): void
    {
        $indeks = Schema::getIndexListing('stock');

        if (in_array('stock_barang_id_bagian_id_no_batch_unique', $indeks, true)) {
            Schema::table('stock', fn (Blueprint $table) => $table->dropUnique('stock_barang_id_bagian_id_no_batch_unique'));
        }

        if (! in_array('stock_barang_id_bagian_id_unique', $indeks, true)) {
            Schema::table('stock', fn (Blueprint $table) => $table->unique(['barang_id', 'bagian_id'], 'stock_barang_id_bagian_id_unique'));
        }
    }
};
