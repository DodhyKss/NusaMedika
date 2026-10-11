<?php

namespace App\Helpers;

use App\Models\Barang;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Helper bersama untuk form cairan (24 Intake, 25 Output, 26 Monitoring Cairan).
 *
 * Berisi perhitungan yang HARUS dijalankan di server:
 *  - kebutuhan cairan harian dari berat badan (BB x 40 s/d BB x 50),
 *  - IWL (Insensible Water Loss) formula Hudson,
 *  - total intake / total output / balance,
 *  - penulisan kolom `emr_detail.flag_abnormal` (EmrHelper belum menuliskannya).
 *
 * Nilai untuk semua field turunan diabaikan dari browser dan dihitung ulang di
 * `filteredData()` masing-masing controller.
 */
class CairanHelper
{
    /** Jumlah cabang infus maksimum (form 24 & 26). */
    public const MAKS_BARIS = 6;

    /** Awal baris snapshot nama cairan di dalam variabel `catatan`. */
    public const SNAPSHOT_PREFIX = '[Cairan Infus] ';

    /** Kunci intake tambahan (di luar cabang infus) untuk form 24. */
    public const INTAKE_TAMBAHAN_24 = ['oral', 'spoel', 'transfusi', 'intake_lainnya'];

    /** Kunci output untuk form 25 (sudah termasuk IWL). */
    public const OUTPUT_25 = ['urine', 'drain', 'ngt', 'wsd', 'output_lainnya'];

    /** Kunci output untuk form 26 (IWL dihitung terpisah). */
    public const OUTPUT_26 = ['output_urine', 'output_drain', 'output_ngt', 'output_wsd', 'output_lainnya'];

    /**
     * Daftar master barang aktif untuk dipilih sebagai cairan infus.
     * Cairan diambil dari master `barang` (sudah dipakai form 10 Tindakan Medis),
     * bukan array statis 47 elemen seperti di sistem legacy.
     */
    public static function barangs()
    {
        return Barang::aktif()
            ->orderBy('nama_barang')
            ->get(['barang_id', 'kode_barang', 'nama_barang']);
    }

    /**
     * Peta barang_id => nama_barang untuk sekumpulan id (snapshot & tampilan).
     *
     * @param  array<int, mixed>  $barangIds
     * @return array<int, string>
     */
    public static function namaBarang(array $barangIds): array
    {
        $ids = array_values(array_filter(array_map('intval', $barangIds)));

        if ($ids === []) {
            return [];
        }

        return Barang::aktif()
            ->whereIn('barang_id', $ids)
            ->pluck('nama_barang', 'barang_id')
            ->map(fn ($nama) => (string) $nama)
            ->all();
    }

    /**
     * Snapshot nama cairan untuk disimpan di variabel `catatan` (objek 77).
     *
     * @param  array<int, mixed>  $barangIds
     * @return string kosong bila tidak ada baris infus yang dipakai
     */
    public static function snapshot(array $barangIds): string
    {
        $peta = static::namaBarang($barangIds);

        $potongan = [];
        foreach ($barangIds as $id) {
            $id = (int) $id;
            if ($id > 0 && isset($peta[$id])) {
                $potongan[] = $peta[$id];
            }
        }

        return $potongan === [] ? '' : static::SNAPSHOT_PREFIX.implode(', ', $potongan);
    }

    /**
     * Buang baris snapshot dari teks catatan (dipakai saat form dibuka lagi
     * supaya baris yang sama tidak ditulis dua kali).
     */
    public static function stripSnapshot(?string $catatan): string
    {
        $catatan = trim((string) $catatan);

        if (! str_starts_with($catatan, static::SNAPSHOT_PREFIX)) {
            return $catatan;
        }

        $baris = explode("\n", $catatan, 2);

        return trim($baris[1] ?? '');
    }

    /**
     * Kebutuhan cairan harian dari berat badan: minimum BB x 40, maksimum BB x 50.
     *
     * @return array{min: float, maks: float, teks: string}|null null bila berat badan tidak diketahui
     */
    public static function kebutuhanCairan($beratBadan): ?array
    {
        $bb = (float) $beratBadan;

        if ($bb <= 0) {
            return null;
        }

        $min = $bb * 40;
        $maks = $bb * 50;

        return [
            'min' => $min,
            'maks' => $maks,
            'teks' => round($min).' - '.round($maks).' cc',
        ];
    }

    /**
     * IWL (Insensible Water Loss) per 24 jam, formula Hudson:
     * (25 + berat badan) x faktor tanda vital.
     *
     * Faktor: 1 (suhu normal) - 1,5 (suhu 38,0 - 39,0) - 2 (suhu > 39,0).
     */
    public static function hitungIWL($beratBadan, $suhu = null): float
    {
        $bb = (float) $beratBadan;

        if ($bb <= 0) {
            return 0.0;
        }

        $s = (float) $suhu;
        $faktor = $s <= 0 ? 1.0 : ($s > 39.0 ? 2.0 : ($s >= 38.0 ? 1.5 : 1.0));

        return round((25 + $bb) * $faktor, 0);
    }

    /**
     * Jumlah beberapa kunci numerik. Nilai non-numeric dianggap 0.
     *
     * @param  array<string, mixed>  $data
     * @param  array<int, string>  $kunci
     */
    public static function total(array $data, array $kunci): float
    {
        $total = 0.0;

        foreach ($kunci as $k) {
            $nilai = $data[$k] ?? null;
            if ($nilai === null || $nilai === '') {
                continue;
            }
            $total += (float) $nilai;
        }

        return round($total, 0);
    }

    /**
     * Nilai terakhir sebuah variabel EMR milik satu registrasi_detail
     * (mis. berat badan / suhu dari form Tanda Vital atau Monitoring Cairan).
     */
    public static function nilaiTerakhir(int $registrasiDetailId, string $variabel): ?float
    {
        if ($registrasiDetailId <= 0) {
            return null;
        }

        $nilai = DB::table('emr_detail as d')
            ->join('emr as e', 'e.emr_id', '=', 'd.emr_id')
            ->where('e.registrasi_detail_id', $registrasiDetailId)
            ->where('d.variabel', $variabel)
            ->where(function ($q) {
                $q->whereNull('e.status_batal')->orWhere('e.status_batal', 0);
            })
            ->where(function ($q) {
                $q->whereNull('d.status_batal')->orWhere('d.status_batal', 0);
            })
            ->orderByDesc('d.emr_detail_id')
            ->value('d.value');

        if ($nilai === null || ! is_numeric($nilai)) {
            return null;
        }

        return (float) $nilai;
    }

    /**
     * Daftar ronde observasi sebuah form pada satu registrasi_detail.
     *
     * SATU RONDE = SATU BARIS `emr`. Nomor ronde dihitung saat BACA (tabel
     * `emr_detail` tidak punya kolom ronde), diurutkan naik per waktu.
     *
     * @return array<int, array{ronde: int, emr_id: int, tgl_jam: ?string, nilai: array, abnormal: array}>
     */
    public static function ronde(int $formId, int $registrasiDetailId): array
    {
        if ($formId <= 0 || $registrasiDetailId <= 0) {
            return [];
        }

        $rows = DB::table('emr')
            ->where('form_id', $formId)
            ->where('registrasi_detail_id', $registrasiDetailId)
            ->where(function ($q) {
                $q->whereNull('status_batal')->orWhere('status_batal', 0);
            })
            ->orderBy('tgl_jam')
            ->orderBy('emr_id')
            ->get(['emr_id', 'tgl_jam']);

        $hasil = [];
        foreach ($rows as $i => $row) {
            $hasil[] = [
                'ronde' => $i + 1,
                'emr_id' => (int) $row->emr_id,
                'tgl_jam' => $row->tgl_jam,
                'nilai' => EmrHelper::emrDetailByVariabel((int) $row->emr_id)->toArray(),
                'abnormal' => static::emrFlags((int) $row->emr_id),
            ];
        }

        return $hasil;
    }

    /**
     * Daftar variabel yang ditandai ABNORMAL pada satu emr
     * (konvensi global: 1 = abnormal, 0 = normal, NULL = tidak dinilai).
     *
     * @return array<int, string>
     */
    public static function emrFlags(int $emrId): array
    {
        if ($emrId <= 0) {
            return [];
        }

        return DB::table('emr_detail')
            ->where('emr_id', $emrId)
            ->where('flag_abnormal', 1)
            ->where(function ($q) {
                $q->whereNull('status_batal')->orWhere('status_batal', 0);
            })
            ->pluck('variabel')
            ->all();
    }

    /**
     * Tulis kolom `emr_detail.flag_abnormal` untuk satu emr.
     *
     * `EmrHelper::storeDetails()` belum mengisi kolom ini, jadi pemanggilnya
     * (controller form cairan) menulisnya sendiri setelah insert/update.
     * Variabel yang tidak ada di peta `$flags` tetap NULL sehingga data lama
     * tidak berubah.
     *
     * @param  array<string, int|null>  $flags  peta [variabel => 0|1|null]
     */
    public static function tulisFlags(int $emrId, array $flags): void
    {
        if ($emrId <= 0) {
            return;
        }

        $user = Auth::user();
        $now = now();

        foreach ($flags as $variabel => $flag) {
            if ($flag === null || ! is_string($variabel)) {
                continue;
            }

            DB::table('emr_detail')
                ->where('emr_id', $emrId)
                ->where('variabel', $variabel)
                ->where(function ($q) {
                    $q->whereNull('status_batal')->orWhere('status_batal', 0);
                })
                ->update([
                    'flag_abnormal' => (int) $flag,
                    'mod_time' => $now,
                    'mod_user_id' => $user->user_id ?? null,
                ]);
        }
    }

    /**
     * Total intake seluruh ronde form 26 pada satu tanggal (kumulatif 24 jam).
     * Ronde yang sedang disimpan ($excludeEmrId) dikecualikan supaya pada
     * kasus update angka tidak terhitung dua kali.
     */
    public static function akumulasiIntakeHariIni(int $formId, int $registrasiDetailId, ?string $tanggal, int $excludeEmrId = 0): float
    {
        if ($formId <= 0 || $registrasiDetailId <= 0 || ! $tanggal) {
            return 0.0;
        }

        $query = DB::table('emr')
            ->where('form_id', $formId)
            ->where('registrasi_detail_id', $registrasiDetailId)
            ->where('emr_id', '!=', $excludeEmrId)
            ->where(function ($q) {
                $q->whereNull('status_batal')->orWhere('status_batal', 0);
            });

        $emrIds = $query->pluck('emr_id')->all();

        if ($emrIds === []) {
            return 0.0;
        }

        $rows = DB::table('emr_detail')
            ->whereIn('emr_id', $emrIds)
            ->whereIn('variabel', ['tanggal_monitoring', 'total_intake'])
            ->where(function ($q) {
                $q->whereNull('status_batal')->orWhere('status_batal', 0);
            })
            ->get(['emr_id', 'variabel', 'value']);

        $tanggalRonde = [];
        $totalRonde = [];
        foreach ($rows as $row) {
            if ($row->variabel === 'tanggal_monitoring') {
                $tanggalRonde[$row->emr_id] = (string) $row->value;
            } else {
                $totalRonde[$row->emr_id] = (float) $row->value;
            }
        }

        $total = 0.0;
        foreach ($totalRonde as $emrId => $nilai) {
            if (($tanggalRonde[$emrId] ?? null) === $tanggal) {
                $total += $nilai;
            }
        }

        return round($total, 0);
    }
}
