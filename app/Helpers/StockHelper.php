<?php

namespace App\Helpers;

use App\Models\KartuStock;
use App\Models\Stock;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class StockHelper
{
    /**
     * Mencatat stok masuk: update saldo tabel `stock` + tulis baris kartu_stock.
     *
     * @param  array{jenis_mutasi?: int, ref_penerimaan_detail_id?: int|null, ref_mutasi_barang_detail_id?: int|null, ref_peresepan_obat_detail_id?: int|null, tanggal?: \DateTimeInterface|Carbon|null, keterangan?: string|null, harga_beli?: float|null, harga_jual?: float|null, tgl_expired?: string|null}  $opts
     */
    public static function tambahMasuk(int $bagianId, int $barangId, ?string $noBatch, float $qty, array $opts = []): void
    {
        self::catat($bagianId, $barangId, $noBatch, $qty, 0, $opts);
    }

    /**
     * Mencatat stok keluar: update saldo tabel `stock` + tulis baris kartu_stock.
     *
     * @param  array{jenis_mutasi?: int, ref_penerimaan_detail_id?: int|null, ref_mutasi_barang_detail_id?: int|null, ref_peresepan_obat_detail_id?: int|null, tanggal?: \DateTimeInterface|Carbon|null, keterangan?: string|null, harga_beli?: float|null, harga_jual?: float|null, tgl_expired?: string|null}  $opts
     */
    public static function tambahKeluar(int $bagianId, int $barangId, ?string $noBatch, float $qty, array $opts = []): void
    {
        self::catat($bagianId, $barangId, $noBatch, 0, $qty, $opts);
    }

    private static function catat(int $bagianId, int $barangId, ?string $noBatch, float $masuk, float $keluar, array $opts): void
    {
        $hargaBeli = isset($opts['harga_beli']) ? (float) $opts['harga_beli'] : null;
        $hargaJual = isset($opts['harga_jual']) ? (float) $opts['harga_jual'] : null;

        $stock = Stock::where('barang_id', $barangId)
            ->where('bagian_id', $bagianId)
            ->where('no_batch', $noBatch)
            ->where('harga_jual', $hargaJual)
            ->first() ?? new Stock;

        if (! $stock->exists) {
            $stock->barang_id = $barangId;
            $stock->no_batch = $noBatch;
            $stock->bagian_id = $bagianId;
            $stock->harga_beli = $hargaBeli;
            $stock->harga_jual = $hargaJual;
            $stock->tgl_expired = $opts['tgl_expired'] ?? null;
            $stock->input_time = now();
            $stock->input_user_id = Auth::id();
            $stock->status_batal = 0;
        }

        $sebelum = (float) ($stock->jumlah ?? 0);
        $sesudah = $sebelum + $masuk - $keluar;
        $stock->jumlah = $sesudah;
        $stock->mod_time = now();
        $stock->mod_user_id = Auth::id();
        $stock->save();

        $kartu = new KartuStock;
        $kartu->barang_id = $barangId;
        $kartu->no_batch = $noBatch;
        $kartu->bagian_id = $bagianId;
        $kartu->tanggal = $opts['tanggal'] ?? now();
        $kartu->jenis_mutasi = $opts['jenis_mutasi'] ?? ($masuk > 0 ? 1 : 3);
        $kartu->ref_penerimaan_detail_id = $opts['ref_penerimaan_detail_id'] ?? null;
        $kartu->ref_mutasi_barang_detail_id = $opts['ref_mutasi_barang_detail_id'] ?? null;
        $kartu->ref_peresepan_obat_detail_id = $opts['ref_peresepan_obat_detail_id'] ?? null;
        $kartu->qty_masuk = $masuk;
        $kartu->qty_keluar = $keluar;
        $kartu->saldo_sebelum = $sebelum;
        $kartu->saldo_sesudah = $sesudah;
        $kartu->harga_beli = $hargaBeli;
        $kartu->harga_jual = $hargaJual;
        $kartu->tgl_expired = $opts['tgl_expired'] ?? null;
        $kartu->keterangan = $opts['keterangan'] ?? null;
        $kartu->input_time = now();
        $kartu->input_user_id = Auth::id();
        $kartu->status_batal = 0;
        $kartu->save();
    }
}
