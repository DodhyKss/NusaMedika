<?php

namespace App\Helpers;

use App\Models\KelasRuang;
use App\Models\OrderLaboratorium;
use App\Models\OrderRadiologi;
use App\Models\RegistrasiDetail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Pusat operasi penunjang medis (Laboratorium & Radiologi): penomoran order,
 * resolusi tarif per kelas, pembuatan order + registrasi_detail tujuan, serta
 * alur entri hasil oleh petugas (terima / simpan hasil / finalisasi / batal).
 * Dipakai lintas controller (EMR\Laboratorium, EMR\Radiologi, DaftarPesanan*).
 */
class PenunjangHelper
{
    /**
     * Penomoran order per jenis. Prefix: LAB / RAD.
     */
    public static function generateNoOrder(string $jenis): string
    {
        [$table] = static::tables($jenis);

        return GenerateHelper::generateNoOrder($jenis === 'lab' ? 'LAB' : 'RAD', $table, 'no_order');
    }

    /**
     * Resolusi tarif sebuah tindakan untuk sebuah kelas perawatan
     * (kelas_ruang_id), fallback ke tarif default (kelas_ruang_id NULL).
     *
     * @return array{kelas_ruang_id: ?int, tarif: float, tarif_bpjs: ?float}
     */
    public static function tarif(int $tindakanId, ?int $kelasRuangId): array
    {
        $harga = null;

        if ($kelasRuangId) {
            $harga = DB::table('tindakan_harga')
                ->where('tindakan_id', $tindakanId)
                ->where('kelas_ruang_id', $kelasRuangId)
                ->where(function ($q) {
                    $q->whereNull('status_batal')->orWhere('status_batal', 0);
                })
                ->first();
        }

        if (! $harga) {
            $harga = DB::table('tindakan_harga')
                ->where('tindakan_id', $tindakanId)
                ->whereNull('kelas_ruang_id')
                ->where(function ($q) {
                    $q->whereNull('status_batal')->orWhere('status_batal', 0);
                })
                ->first();
        }

        return [
            'kelas_ruang_id' => $harga && $harga->kelas_ruang_id !== null ? (int) $harga->kelas_ruang_id : null,
            'tarif' => (float) ($harga->tarif ?? 0),
            'tarif_bpjs' => $harga && $harga->tarif_bpjs !== null ? (float) $harga->tarif_bpjs : null,
        ];
    }

    /**
     * Validasi hak kelas (kelas_ruang_id) aktif; kembalikan null bila tidak ada.
     */
    public static function kelasRuangId(?int $hakKelasId): ?int
    {
        if (! $hakKelasId) {
            return null;
        }

        $exists = KelasRuang::aktif()->where('kelas_ruang_id', $hakKelasId)->exists();

        return $exists ? $hakKelasId : null;
    }

    /**
     * Buat order penunjang dalam satu transaksi:
     * 1. hitung tarif per tindakan (snapshot),
     * 2. create registrasi_detail tujuan (bagian lab/rad),
     * 3. insert header + detail order.
     * Mengembalikan order_*_id.
     */
    public static function buatOrder(string $jenis, array $data, array $items): int
    {
        $jenis = static::normalizeJenis($jenis);
        $registrasiDetail = RegistrasiDetail::with('registrasi')->findOrFail((int) ($data['registrasi_detail_id'] ?? 0));
        $registrasi = $registrasiDetail->registrasi;
        $user = Auth::user();

        $items = array_values(array_filter($items, fn ($item) => ! empty($item['tindakan_id'])));
        if ($items === []) {
            throw new \RuntimeException('Minimal satu tindakan wajib dipilih.');
        }

        [$table, $pk, $detailTable, $detailFk] = static::tables($jenis);

        return DB::transaction(function () use ($jenis, $registrasiDetail, $registrasi, $user, $data, $items, $table, $pk, $detailTable, $detailFk) {
            // Hak kelas asal → kelas_ruang id (untuk tarif) + snapshot kelas.
            $hakKelasId = static::kelasRuangId($registrasiDetail->hak_kelas_id ? (int) $registrasiDetail->hak_kelas_id : null);
            $kelasId = $registrasiDetail->kelas_id ? (int) $registrasiDetail->kelas_id : null;

            // 1. Create registrasi_detail tujuan (bagian sarana penunjang).
            $tujuanId = DB::table('registrasi_detail')->insertGetId([
                'registrasi_id' => $registrasi->registrasi_id,
                'bagian_id' => (int) $data['bagian_tujuan_id'],
                'tgl_daftar' => now(),
                'kelas_id' => $kelasId,
                'hak_kelas_id' => $hakKelasId,
                'terima_dari' => 'INTERNAL',
                'status_batal' => 0,
                'input_time' => now(),
                'input_user_id' => $user->user_id ?? null,
            ], 'registrasi_detail_id');

            // 2. Insert header order.
            $orderId = DB::table($table)->insertGetId([
                'no_order' => static::generateNoOrder($jenis),
                'pasien_id' => $registrasi->pasien_id,
                'registrasi_id' => $registrasi->registrasi_id,
                'registrasi_detail_id' => $registrasiDetail->registrasi_detail_id, // detail asal
                'registrasi_detail_tujuan_id' => $tujuanId,
                'bagian_asal_id' => (int) $data['bagian_asal_id'],
                'bagian_tujuan_id' => (int) $data['bagian_tujuan_id'],
                'dokter_id' => ! empty($data['dokter_id']) ? (int) $data['dokter_id'] : null,
                'prioritas' => $data['prioritas'] ?? null,
                'status_order' => 0,
                'tanggal_order' => now(),
                'kelas_id' => $kelasId,
                'hak_kelas_id' => $hakKelasId,
                'keterangan' => $data['keterangan'] ?? null,
                'input_time' => now(),
                'input_user_id' => $user->user_id ?? null,
                'status_batal' => 0,
            ], $pk);

            // 3. Insert detail order dengan snapshot tarif.
            static::insertDetails($jenis, $detailTable, $detailFk, $orderId, $items, $hakKelasId);

            return $orderId;
        });
    }

    /**
     * Ubah order yang masih Menunggu (status 0): perbarui header + ganti item.
     * registrasi_detail tujuan tidak dibuat ulang (tetap memakai yang sudah ada).
     */
    public static function ubahOrder(string $jenis, int $orderId, array $data, array $items): void
    {
        $jenis = static::normalizeJenis($jenis);
        $order = static::findOrder($jenis, $orderId);

        if ((int) $order->status_order !== 0) {
            throw new \RuntimeException('Hanya order dengan status Menunggu yang dapat diubah.');
        }

        $registrasiDetail = $order->registrasiDetail;
        $hakKelasId = static::kelasRuangId($registrasiDetail && $registrasiDetail->hak_kelas_id ? (int) $registrasiDetail->hak_kelas_id : null);

        $items = array_values(array_filter($items, fn ($item) => ! empty($item['tindakan_id'])));
        if ($items === []) {
            throw new \RuntimeException('Minimal satu tindakan wajib dipilih.');
        }

        [$table, $pk, $detailTable, $detailFk] = static::tables($jenis);
        $user = Auth::user();

        DB::transaction(function () use ($jenis, $orderId, $data, $items, $hakKelasId, $table, $pk, $detailTable, $detailFk, $user) {
            DB::table($table)->where($pk, $orderId)->update([
                'prioritas' => $data['prioritas'] ?? null,
                'keterangan' => $data['keterangan'] ?? null,
                'mod_time' => now(),
                'mod_user_id' => $user->user_id ?? null,
            ]);

            DB::table($detailTable)
                ->where($detailFk, $orderId)
                ->update([
                    'status_batal' => 1,
                    'mod_time' => now(),
                    'mod_user_id' => $user->user_id ?? null,
                ]);

            static::insertDetails($jenis, $detailTable, $detailFk, $orderId, $items, $hakKelasId);
        });
    }

    /**
     * Sisipkan baris detail order dengan snapshot tindakan (nama, tarif, satuan, nilai normal).
     */
    private static function insertDetails(string $jenis, string $detailTable, string $detailFk, int $orderId, array $items, ?int $hakKelasId): void
    {
        $user = Auth::user();

        foreach ($items as $item) {
            $tindakanId = (int) ($item['tindakan_id'] ?? 0);
            $tindakan = $tindakanId
                ? DB::table('tindakan')
                    ->where('tindakan_id', $tindakanId)
                    ->where(function ($q) {
                        $q->whereNull('status_batal')->orWhere('status_batal', 0);
                    })
                    ->first()
                : null;

            if ($tindakanId && ! $tindakan) {
                throw new \RuntimeException('Tindakan tidak valid pada pilihan #'.$tindakanId.'.');
            }

            $detail = [
                $detailFk => $orderId,
                'tindakan_id' => $tindakanId ?: null,
                'nama_tindakan' => $tindakan->nama_tindakan ?? (isset($item['nama_tindakan']) ? $item['nama_tindakan'] : ''),
                'harga' => $tindakan ? static::tarif((int) $tindakanId, $hakKelasId)['tarif'] : 0,
                'status' => 0,
                'input_time' => now(),
                'input_user_id' => $user->user_id ?? null,
                'status_batal' => 0,
            ];

            if ($jenis === 'lab') {
                $detail['satuan_hasil'] = $tindakan->satuan_hasil ?? null;
                $detail['nilai_normal'] = $tindakan->nilai_normal ?? null;
            }

            DB::table($detailTable)->insert($detail);
        }
    }

    /**
     * Terima order: status 0 (Menunggu) → 1 (Diproses), isi tanggal_terima.
     */
    public static function terima(string $jenis, int $orderId, ?int $petugasId): void
    {
        $jenis = static::normalizeJenis($jenis);
        $order = static::findOrder($jenis, $orderId);

        if ((int) $order->status_order !== 0) {
            throw new \RuntimeException('Hanya order dengan status Menunggu yang dapat diterima.');
        }

        [$table, $pk] = static::tables($jenis);
        static::updateOrder($jenis, $orderId, [
            'status_order' => 1,
            'tanggal_terima' => now(),
            'petugas_pelaksana_id' => $petugasId,
        ]);
    }

    /**
     * Simpan hasil entri petugas per detail order (parsial).
     * $hasilItems: [detailId => ['hasil' => ..., 'flag_abnormal' => 0|1]].
     * Hanya berlaku untuk order yang belum Selesai/Batal.
     */
    public static function simpanHasil(string $jenis, int $orderId, array $hasilItems, ?int $petugasId): void
    {
        $jenis = static::normalizeJenis($jenis);
        $order = static::findOrder($jenis, $orderId);

        if (in_array((int) $order->status_order, [2, 3], true)) {
            throw new \RuntimeException('Order yang sudah Selesai/Batal tidak dapat diubah hasilnya.');
        }

        [, , $detailTable, $detailFk] = static::tables($jenis);
        $user = Auth::user();

        DB::transaction(function () use ($detailTable, $detailFk, $orderId, $hasilItems, $petugasId, $user) {
            foreach ($hasilItems as $detailId => $value) {
                $adas = DB::table($detailTable)
                    ->where($detailFk, $orderId)
                    ->where($detailFk === 'order_laboratorium_detail_id' ? 'order_laboratorium_detail_id' : 'order_radiologi_detail_id', $detailId)
                    ->exists();

                if (! $adas) {
                    continue;
                }

                $hasil = isset($value['hasil']) ? trim((string) $value['hasil']) : null;
                $update = [
                    'hasil' => $hasil !== '' ? $hasil : null,
                    'flag_abnormal' => array_key_exists('flag_abnormal', $value) ? (int) $value['flag_abnormal'] : null,
                    'status' => $hasil !== '' ? 1 : 0,
                    'petugas_id' => $petugasId,
                    'mod_time' => now(),
                    'mod_user_id' => $user->user_id ?? null,
                ];

                $primaryKey = $detailFk === 'order_laboratorium_id' ? 'order_laboratorium_detail_id' : 'order_radiologi_detail_id';

                DB::table($detailTable)->where($primaryKey, $detailId)->update($update);
            }
        });
    }

    /**
     * Finalisasi order: status → 2 (Selesai), isi tanggal_hasil; detail tanpa hasil
     * ditandai belum selesai (status 0).
     */
    public static function finalisasi(string $jenis, int $orderId, ?int $petugasId): void
    {
        $jenis = static::normalizeJenis($jenis);
        $order = static::findOrder($jenis, $orderId);

        if (in_array((int) $order->status_order, [2, 3], true)) {
            throw new \RuntimeException('Order yang sudah Selesai/Batal tidak dapat difinalisasi.');
        }

        static::updateOrder($jenis, $orderId, [
            'status_order' => 2,
            'tanggal_hasil' => now(),
            'petugas_pelaksana_id' => $petugasId ?: $order->petugas_pelaksana_id,
        ]);
    }

    /**
     * Batalkan order: hanya status 0 (Menunggu) yang dapat dibatalkan.
     */
    public static function batalkan(string $jenis, int $orderId): void
    {
        $jenis = static::normalizeJenis($jenis);
        $order = static::findOrder($jenis, $orderId);

        if ((int) $order->status_order !== 0) {
            throw new \RuntimeException('Hanya order dengan status Menunggu yang dapat dibatalkan.');
        }

        static::updateOrder($jenis, $orderId, ['status_order' => 3]);
    }

    /**
     * Riwayat order pasien untuk sebuah registrasi (dibaca dashboard EMR).
     */
    public static function riwayatOrderPasien(string $jenis, int $registrasiId)
    {
        $jenis = static::normalizeJenis($jenis);

        return static::baseQuery($jenis)
            ->where(static::tables($jenis)[0].'.registrasi_id', $registrasiId)
            ->orderByDesc(static::tables($jenis)[1])
            ->get();
    }

    /**
     * Query dasar daftar order penunjang (untuk Daftar Pesanan Lab/Rad).
     */
    public static function daftarOrder(string $jenis, ?int $bagianTujuanId = null)
    {
        $jenis = static::normalizeJenis($jenis);

        $query = static::baseQuery($jenis);

        if ($bagianTujuanId) {
            $query->where(static::tables($jenis)[0].'.bagian_tujuan_id', $bagianTujuanId);
        }

        return $query;
    }

    /**
     * Ambil satu order beserta relasinya.
     */
    public static function findOrder(string $jenis, int $orderId): object
    {
        $jenis = static::normalizeJenis($jenis);
        $model = $jenis === 'lab' ? OrderLaboratorium::class : OrderRadiologi::class;

        $order = $model::aktif()
            ->with(['pasien', 'dokter', 'petugasPelaksana', 'bagianAsal', 'bagianTujuan', 'registrasiDetail', 'registrasiDetailTujuan', 'details'])
            ->find($orderId);

        if (! $order) {
            abort(404, 'Order penunjang tidak ditemukan.');
        }

        return $order;
    }

    // ======================== INTERNAL ========================

    private static function normalizeJenis(string $jenis): string
    {
        $jenis = strtolower(trim($jenis));

        if (! in_array($jenis, ['lab', 'rad'], true)) {
            throw new \InvalidArgumentException('Jenis penunjang tidak valid.');
        }

        return $jenis;
    }

    /**
     * @return array{0: string, 1: string, 2: string, 3: string} [table, pk, detailTable, detailFk]
     */
    private static function tables(string $jenis): array
    {
        return $jenis === 'lab'
            ? ['order_laboratorium', 'order_laboratorium_id', 'order_laboratorium_detail', 'order_laboratorium_id']
            : ['order_radiologi', 'order_radiologi_id', 'order_radiologi_detail', 'order_radiologi_id'];
    }

    private static function updateOrder(string $jenis, int $orderId, array $changes): void
    {
        [$table, $pk] = static::tables($jenis);
        $user = Auth::user();

        DB::table($table)->where($pk, $orderId)->update(array_merge($changes, [
            'mod_time' => now(),
            'mod_user_id' => $user->user_id ?? null,
        ]));
    }

    private static function baseQuery(string $jenis)
    {
        $jenis = static::normalizeJenis($jenis);

        return $jenis === 'lab'
            ? OrderLaboratorium::aktif()->with([
                'pasien', 'dokter', 'petugasPelaksana', 'bagianAsal', 'bagianTujuan',
                'registrasiDetail.registrasi', 'registrasiDetailTujuan', 'details',
            ])
            : OrderRadiologi::aktif()->with([
                'pasien', 'dokter', 'petugasPelaksana', 'bagianAsal', 'bagianTujuan',
                'registrasiDetail.registrasi', 'registrasiDetailTujuan', 'details',
            ]);
    }
}
