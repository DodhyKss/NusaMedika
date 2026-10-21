<?php

namespace App\Helpers;

/**
 * Helper untuk form ICU & Hemodialisa (Fase P5).
 *
 * Sumber rancangan: docs/KONSEP_ICU_HEMODIALISA.md.
 * Skor/kesimpulan SELALU dihitung di server — nilai browser dibuang.
 */
class IcuHelper
{
    /** Jumlah butir kriteria masuk ICU per grup (prefix => n). */
    public const KRITERIA_MASUK = [
        'resp' => 7,
        'td' => 1,
        'nadi' => 6,
        'ekg' => 2,
        'pasca_bedah' => 1,
        'obat' => 1,
    ];

    /** Butir kelayakan life support kriteria keluar ICU (8 item). */
    public const BUTIR_LIFE_SUPPORT = [
        'kr1_masker_nrm', 'kr1_masker_rm', 'kr1_jackson_rees', 'kr1_ventilator',
        'kr1_dopamin', 'kr1_dobutamin', 'kr1_vascon', 'kr1_adrenalin',
    ];

    /**
     * GCS jumlah = E + M + V. Null bila ada komponen yang tidak terisi.
     */
    public static function gcsJumlah($e, $m, $v): ?int
    {
        if ($e === null || $e === '' || $m === null || $m === '' || $v === null || $v === '') {
            return null;
        }

        return (int) $e + (int) $m + (int) $v;
    }

    /**
     * Jumlah kriteria masuk ICU yang terpenuhi (*_status === 'YA').
     *
     * Mengembalikan null bila ADA status yang belum dipilih — angka parsial
     * menyesatkan (pola sama dengan EwsHelper::$lengkap).
     */
    public static function jumlahKriteriaTerpenuhi(array $data): ?int
    {
        $jumlah = 0;
        foreach (self::KRITERIA_MASUK as $prefix => $n) {
            for ($i = 1; $i <= $n; $i++) {
                $status = $data[$prefix.'_'.$i.'_status'] ?? null;
                if ($status === null || $status === '') {
                    return null;
                }
                if (strtoupper((string) $status) === 'YA') {
                    $jumlah++;
                }
            }
        }

        return $jumlah;
    }

    /**
     * Kesimpulan kriteria masuk ICU.
     *
     * Legacy tidak menyimpan ambang (lihat KONSEP §3.3 / §12.6) — angka
     * kriteria ditampilkan apa adanya; kesimpulan teks dikembalikan null
     * agar TIDAK disimpan sebagai kesimpulan karangan klinis.
     */
    public static function kesimpulanMasuk(?int $terpenuhi): ?string
    {
        return null;
    }

    /**
     * Status kelayakan keluar ICU kriteria I: 'YA' bila SEMUA butir life
     * support bernilai 'TIDAK' (pasien sudah tidak membutuhkan), null bila
     * ada butir yang belum dipilih.
     */
    public static function kr1Status(array $butir): ?string
    {
        foreach (self::BUTIR_LIFE_SUPPORT as $key) {
            $nilai = $butir[$key] ?? null;
            if ($nilai === null || $nilai === '') {
                return null;
            }
            if (strtoupper((string) $nilai) !== 'TIDAK') {
                return 'TIDAK';
            }
        }

        return 'YA';
    }

    /**
     * Kesimpulan keluar ICU:
     * - 'LAYAK' bila kr1 = YA (tidak lagi butuh life support) ATAU kr3 = YA
     *   (stabil normal);
     * - 'TIDAK LAYAK' bila kr2 = YA atau kr4 = YA (terapi gagal / terminal);
     * - null bila data belum lengkap (kr1/kr2/kr3/kr4 belum semua terisi).
     *
     * Bila LAYAK dan kr2/kr4 juga YA, prioritas 'TIDAK LAYAK' — dokumen
     * keputusan akhir tidak boleh menyimpulkan layak saat withdrawal aktif.
     */
    public static function kesimpulanKeluar(array $kriteria): ?string
    {
        $kr1 = $kriteria['kr1_status'] ?? null;
        $kr2 = $kriteria['kr2_status'] ?? null;
        $kr3 = $kriteria['kr3_status'] ?? null;
        $kr4 = $kriteria['kr4_status'] ?? null;

        if ($kr1 === null || $kr1 === '' || $kr2 === null || $kr2 === ''
            || $kr3 === null || $kr3 === '' || $kr4 === null || $kr4 === '') {
            return null;
        }

        $up = fn ($v) => strtoupper((string) $v);
        if ($up($kr2) === 'YA' || $up($kr4) === 'YA') {
            return 'TIDAK LAYAK';
        }
        if ($up($kr1) === 'YA' || $up($kr3) === 'YA') {
            return 'LAYAK';
        }

        return 'TIDAK LAYAK';
    }
}
