<?php

namespace Database\Seeders;

use App\Helpers\StockHelper;
use App\Models\Bagian;
use App\Models\Barang;
use App\Models\KartuStock;
use App\Models\RegistrasiDetail;
use App\Models\Stock;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Saldo stok awal (tabel `stock` + `kartu_stock`) untuk seluruh barang aktif.
 *
 * Dipakai supaya form EMR Tindakan Medis punya nomor batch & sisa stok yang
 * bisa dipilih —ULANG: tanpa data ini semua batch kosong dan baris
 * obat/BMHP tidak bisa disimpan.
 *
 * Stok dicatat ke bagian tempat EMR dibuka, yaitu `registrasi_detail.bagian_id`
 * (poli/ruang perawatan/IGD). Lokasi yang diskalakan diambil dari bagian yang
 * benar-benar dipakai oleh `registrasi_detail`, bukan hardcode id.
 *
 * Idempotent: melewati batch yang sudah tercatat.
 */
class StokAwalSeeder extends Seeder
{
    /** Jumlah batch per barang. */
    private const BATCH_PER_BARANG = 2;

    /** Sisa stok(randomized) untuk tiap batch, dalam rentang ini. */
    private const MIN_QTY = 20;

    private const MAX_QTY = 120;

    public function run(): void
    {
        $bagianIds = $this->bagianTersedia();
        $barangs = Barang::aktif()->orderBy('barang_id')->get();

        if ($barangs->isEmpty()) {
            $this->command?->warn('StokAwalSeeder: tidak ada barang aktif, dilewati.');

            return;
        }

        $total = 0;
        foreach ($bagianIds as $bagianId) {
            foreach ($barangs as $barang) {
                $total += $this->seedBarang($bagianId, $barang);
            }
        }

        $this->command?->info('StokAwalSeeder: '.$total.' batch stok awal ditambahkan.');
    }

    /**
     * Lokasi (bagian) tempat form EMR dibuka: bagian yang dipakai oleh
     * registrasi_detail, difilter ke kategori Poli RJ / Ruang Perawatan / IGD.
     *
     * @return array<int, int>
     */
    private function bagianTersedia(): array
    {
        $ids = RegistrasiDetail::query()
            ->whereNotNull('bagian_id')
            ->distinct()
            ->pluck('bagian_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $bagianIds = Bagian::aktif()
            ->whereIn('referensi_bagian_id', [1, 2, 3]) // Poli RJ, Ruang Perawatan, IGD
            ->whereIn('bagian_id', $ids ?: [0])
            ->pluck('bagian_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if ($bagianIds === []) {
            $fallback = Bagian::aktif()->where('referensi_bagian_id', 1)->first();
            $bagianIds = $fallback ? [(int) $fallback->bagian_id] : [];
        }

        return $bagianIds;
    }

    private function seedBarang(int $bagianId, Barang $barang): int
    {
        $ditambah = 0;
        $kode = $barang->kode_barang ?: ('BRG-'.$barang->barang_id);

        for ($i = 1; $i <= self::BATCH_PER_BARANG; $i++) {
            $noBatch = $this->nomorBatch($kode, $i);

            // Idempotent: lewati batch yang sudah punya stok di lokasi ini.
            if (Stock::aktif()->where('bagian_id', $bagianId)
                ->where('barang_id', $barang->barang_id)
                ->where('no_batch', $noBatch)
                ->exists()
            ) {
                continue;
            }

            $qty = random_int(self::MIN_QTY, self::MAX_QTY);

            // Batch obat 1 tahun ke depan, BMHP 2 tahun.
            $expired = Carbon::now()->addMonths($barang->jenis?->nama_jenis_barang === 'BMHP' ? 24 : 12);

            StockHelper::tambahMasuk($bagianId, (int) $barang->barang_id, $noBatch, (float) $qty, [
                'jenis_mutasi' => KartuStock::JENIS_SALDO_AWAL,
                'tanggal' => Carbon::now()->subDays(30),
                'tgl_expired' => $expired->format('Y-m-d'),
                'keterangan' => 'Saldo awal - seeder demo',
            ]);

            $ditambah++;
        }

        return $ditambah;
    }

    private function nomorBatch(string $kode, int $urut): string
    {
        return $kode.'-'.Carbon::now()->format('ym').'-'.$urut;
    }
}
