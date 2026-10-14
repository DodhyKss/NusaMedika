<?php

namespace App\Helpers;

/**
 * Pusat perhitungan APGAR Score neonatus (form 66 "Penilaian Bayi dengan APGAR Score").
 *
 * APGAR dinilai pada menit ke-1, 5, 10, 15, dan 20. Lima tanda: frekuensi
 * jantung, usaha napas, tonus otot, refleks, dan warna — masing-masing 0–2,
 * total 0–10.
 *
 * Mengikuti pola `EwsHelper`/`NyeriHelper`:
 * - Nilai `apgar_total_*` dari browser SELALU dibuang dan dihitung ulang di
 *   `filteredData()` controller.
 * - Skor hanya dihitung bila kelima tanda pada menit tersebut terisi; menit
 *   1 dan 5 WAJIB, menit 10/15/20 opsional. Menit yang kosong tidak ikut
 *   dihitung dan interpretasinya kosong.
 *
 * Sufiks memakai MENIT (`_1`, `_5`, `_10`, `_15`, `_20`), bukan indeks 1..5,
 * agar variabel tetap terbaca saat form dibuka lagi.
 */
class ApgarHelper
{
    /** Menit penilaian APGAR yang dipakai form ini. */
    public const MENIT = [1, 5, 10, 15, 20];

    /** Menit yang wajib terisi (menit 1 dan 5). */
    public const MENIT_WAJIB = [1, 5];

    /** Lima tanda APGAR + label di view. */
    public const TANDA = [
        'frekuensi_jantung' => 'Frekuensi Jantung',
        'usaha_napas' => 'Usaha Napas',
        'tonus_otot' => 'Tonus Otot',
        'refleks' => 'Refleks',
        'warna' => 'Warna',
    ];

    /** Kunci variabel per tanda & menit, mis. `apgar_frekuensi_jantung_5`. */
    public static function variabel(string $tanda, int $menit): string
    {
        return 'apgar_'.$tanda.'_'.$menit;
    }

    /** Kunci variabel total per menit, mis. `apgar_total_5`. */
    public static function variabelTotal(int $menit): string
    {
        return 'apgar_total_'.$menit;
    }

    /**
     * Hitung skor APGAR untuk satu menit dari lima tanda.
     *
     * Mengembalikan null bila ada tanda yang kosong pada menit wajib — skor
     * parsial selalu terlalu rendah dan menyesatkan (pola `EwsHelper::skor()`).
     *
     * @param  array<string, mixed>  $data
     */
    public static function hitung(array $data, int $menit): ?int
    {
        $total = 0;
        foreach (array_keys(self::TANDA) as $tanda) {
            $nilai = $data[self::variabel($tanda, $menit)] ?? null;
            if ($nilai === null || $nilai === '') {
                // Menit 10/15/20 boleh kosong → belum ada penilaian.
                if (! in_array($menit, self::MENIT_WAJIB, true)) {
                    return null;
                }

                return null;
            }
            $nilai = (int) $nilai;
            if ($nilai < 0 || $nilai > 2) {
                return null;
            }
            $total += $nilai;
        }

        return $total;
    }

    /**
     * Hitung skor APGAR untuk semua menit dari jawaban mentah.
     *
     * @param  array<string, mixed>  $data
     * @return array<int, int|null> menit => skor
     */
    public static function hitungSemua(array $data): array
    {
        $hasil = [];
        foreach (self::MENIT as $menit) {
            $hasil[$menit] = self::hitung($data, $menit);
        }

        return $hasil;
    }

    /**
     * Interpretasi APGAR untuk satu menit.
     *
     * Ambang lazim (bukan WHO) — dipakai untuk badge warna di view.
     * Mengembalikan null bila skor null (belum dinilai lengkap).
     */
    public static function interpretasi(?int $skor): ?string
    {
        return match (true) {
            $skor === null => null,
            $skor >= 8 => 'Normal',
            $skor >= 6 => 'Perlu Perhatian',
            default => 'Asfiksia Ringan',
        };
    }

    /**
     * Warna badge interpretasi (dipakai di view & JS agar konsisten).
     *
     * @return array<string, array{warna: string, kelas: string}>
     */
    public static function warna(): array
    {
        return [
            'Normal' => ['warna' => 'emerald', 'kelas' => 'bg-emerald-100 text-emerald-700'],
            'Perlu Perhatian' => ['warna' => 'amber', 'kelas' => 'bg-amber-100 text-amber-700'],
            'Asfiksia Ringan' => ['warna' => 'red', 'kelas' => 'bg-red-100 text-red-700'],
        ];
    }
}
