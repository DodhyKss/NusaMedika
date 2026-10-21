<?php

namespace App\Helpers;

/**
 * Pusat definisi telaah farmasi (resep & obat) dan kode status pemberian obat.
 *
 * Dua form memakai helper ini:
 * - 47 Telaah Resep → 7 kriteria `KRITERIA_TELAAH_RESEP`
 * - 48 Telaah Obat  → 5 kriteria `KRITERIA_TELAAH_OBAT`
 *
 * Telaah bersifat **ya/tidak per butir**, bukan skor numerik — yang dihitung
 * server hanyalah jumlah butir tercentang dan daftar butir yang GAGAL.
 * Bila ada butir gagal, catatan telaah WAJIB diisi — dicek di server
 * (`filteredData()` tiap controller), bukan hanya Validasi client, karena
 * checkbox bisa saja kosong seluruhnya saat terkirim.
 *
 * Baris ke-8 yang dikomentari di legacy (`// "Sesuai Fornas"`) **tidak
 * diaktifkan** sampai ada keputusan klinis tertulis.
 */
class FarmasiHelper
{
    /** 7 kriteria telaah resep (verbatim dari helper legacy `telaah_resep()`). */
    public const KRITERIA_TELAAH_RESEP = [
        1 => 'Kejelasan Penulisan Resep',
        2 => 'Tepat Cara Pemberian',
        3 => 'Tepat Dosis',
        4 => 'Tepat Frekuensi Pemberian',
        5 => 'Tepat Obat',
        6 => 'Tidak Ada Duplikasi',
        7 => 'Tidak Ada Interaksi Obat',
    ];

    /** 5 kriteria telaah obat (verbatim dari helper legacy `telaah_obat()`). */
    public const KRITERIA_TELAAH_OBAT = [
        1 => 'Tepat Pasien',
        2 => 'Tepat Obat',
        3 => 'Tepat Dosis',
        4 => 'Tepat Cara Pemberian',
        5 => 'Tepat Waktu Pemberian',
    ];

    /**
     * Kode status pemberian obat (verbatim dari `daftar_terapi_obat.php`).
     *
     * Key = kode yang disimpan di `emr_detail`, value = tooltip/arti.
     */
    public const STATUS_PEMBERIAN = [
        '1' => 'Setelah Obat Diberikan',
        'T' => 'Pasien Menolak',
        'K' => 'Kondisi Pasien Menyebabkan di Tundanya Pemberian Suatu Obat',
        'A' => 'Reaksi Alergi',
        'ESO' => 'Reaksi Efek Samping Obat Setelah Pemberian',
        'TAP' => 'Obat Tidak Tersedia',
        'stop' => 'Obat Sudah di Stop',
    ];

    /**
     * Status yang MEMBUTUHKAN alasan stop/tunda.
     *
     * Bila baris obat berstatus salah satu dari ini, `alasan_stop_N` wajib diisi
     * (docs/KONSEP_FARMAKASI.md §3.9).
     */
    public const STATUS_PERLU_ALASAN = ['stop', 'K', 'A', 'TAP'];

    /**
     * Hitung hasil telaah dari jawaban mentah.
     *
     * `$awalan` = 'telaah_' (form 47) atau 'tobat_' (form 48).
     * Nilai butir dianggap TERSCENTANG bila bernilai 'on' / '1' / 'ya'.
     *
     * @param  array<string, mixed>  $jawaban
     * @return array{tercentang: int, total: int, lengkap: bool, gagal: array<int, string>}
     */
    public static function telaah(string $awalan, array $kriteria, array $jawaban): array
    {
        $tercentang = 0;
        $gagal = [];

        foreach ($kriteria as $nomor => $label) {
            $nilai = $jawaban[$awalan.$nomor] ?? null;
            if (self::tercentang($nilai)) {
                $tercentang++;
            } else {
                $gagal[] = $label;
            }
        }

        return [
            'tercentang' => $tercentang,
            'total' => count($kriteria),
            'lengkap' => $gagal === [],
            'gagal' => $gagal,
        ];
    }

    /**
     * Apakah nilai butir telaah berarti "tercentang".
     *
     * Checkbox HTML mengirim 'on' bila dicentang; pola legacy memakai hidden
     * 'off' + checkbox 'on'. Angka 1 & string 'ya' juga diterima supaya
     * data lama / input lain tidak dianggap gagal diam-diam.
     */
    public static function tercentang($nilai): bool
    {
        if ($nilai === null || $nilai === '') {
            return false;
        }
        if (is_bool($nilai)) {
            return $nilai;
        }

        return in_array(strtolower(trim((string) $nilai)), ['on', '1', 'ya', 'true'], true);
    }

    /**
     * Normalisasi nilai butir telaah menjadi 'on' / 'off'.
     */
    public static function nilaiTelaah($nilai): string
    {
        return self::tercentang($nilai) ? 'on' : 'off';
    }
}
