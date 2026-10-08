<?php

namespace App\Helpers;

/**
 * Perhitungan Early Warning Score (EWS) / EVM.
 *
 * Skor memakai parametrik RCPCH (Royal College of Paediatrics & Child Health)
 * yang lazim dipakai di Indonesia: 7 parameter, masing-masing 0-3, total 0-3
 * (risiko rendah), 4-6 (sedang), >= 7 (tinggi).
 *
 * Seluruh parameter diambil dari TANDA VITAL yang sudah dicatat pada form yang
 * sama, jadi petugas tidak perlu mengisi ulang angka yang sama dua kali.
 *
 * Parameter yang TIDAK BISA diukur (mis. tidak ada pulse oximeter) boleh
 * ditandai "Tidak Diukur" lewat checkbox `ews_na[]`. Parameter bertanda
 * tersebut dihitung sebagai SUDAH SELESAI (jadi tidak lagi memblokir skor)
 * dengan kontribusi 0, dan namanya ikut disimpan ke `emr_detail.variabel`
 * `ews_tidak_diukur` supaya jejaknya tetap terbaca. Kategori risiko tetap
 * ditampilkan, dengan keterangan berapa parameter yang benar-benar terukur.
 *
 * Skor WAJIB dihitung ulang di server (nilai dari browser diabaikan) supaya
 * angka yang tersimpan tidak bisa dimanipulasi dari sisi klien.
 */
class EwsHelper
{
    /** Parameter yang dipakai untuk menghitung skor, dalam urutan tampil. */
    public const PARAMETER = ['pernapasan', 'saturasi', 'oksigen', 'td_sistolik', 'nadi', 'kesadaran', 'suhu'];

    /** Opsi "Air atau Oksigen". */
    public const OKSIGEN = ['Air', 'Oksigen'];

    /** Label untuk penanda "Tidak Diukur". */
    public const LABEL_TIDAK_DIUKUR = 'T/A';

    /**
     * Kelas Tailwind untuk badge skor per parameter (indeks = skor 0-3).
     */
    public const SKOR_WARNA = [
        0 => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        1 => 'bg-amber-50 text-amber-700 border-amber-200',
        2 => 'bg-amber-100 text-amber-800 border-amber-300',
        3 => 'bg-red-50 text-red-700 border-red-200',
    ];

    /**
     * Kelas Tailwind untuk badge kategori risiko.
     */
    public const KATEGORI_WARNA = [
        'Rendah' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'Sedang' => 'bg-amber-50 text-amber-700 border-amber-200',
        'Tinggi' => 'bg-red-50 text-red-700 border-red-200',
    ];

    /**
     * Kelas Tailwind untuk badge skor satu parameter.
     */
    public static function badgeSkor(int $skor): string
    {
        return self::SKOR_WARNA[max(0, min(3, $skor))];
    }

    /**
     * Hitung skor EWS.
     *
     * @param  array<string, mixed>  $vital  nilai tanda vital + oksigen
     * @param  array<int, string>  $tidakDiukur  nama parameter yang ditandai tidak bisa diukur
     * @return array{
     *     total:int, kategori:string, lengkap:bool, terisi:int, terukur:int,
     *     na:array<int, string>, detail:array<int, array{
     *         parameter:string, label:string, nilai:string, skor:int|null, na:bool
     *     }>
     * }
     */
    public static function hitung(array $vital, array $tidakDiukur = []): array
    {
        $na = array_values(array_intersect(self::PARAMETER, $tidakDiukur));

        $detail = [];
        $total = 0;
        $terukur = 0;
        $selesai = 0;

        foreach (self::PARAMETER as $parameter) {
            $ditandaiNa = in_array($parameter, $na, true);

            $mentah = $vital[$parameter] ?? null;
            $nilai = is_string($mentah) ? trim($mentah) : $mentah;

            // Parameter dianggap punya nilai hanya bila punya angka/opsi yang
            // bisa dinilai. Parameter bertanda "tidak diukur" diabaikan
            // sama sekali, supaya nilai sisa tidak ikut diskor.
            $ada = ! $ditandaiNa && $nilai !== null && $nilai !== '';

            $skor = $ada ? self::skor($parameter, $nilai) : null;

            if ($skor !== null) {
                $total += $skor;
                $terukur++;
                $selesai++;
            } elseif ($ditandaiNa) {
                // Sudah "diselesaikan" petugas, hanya tidak bisa diukur.
                $selesai++;
            }

            $detail[] = [
                'parameter' => $parameter,
                'label' => self::label($parameter),
                'nilai' => $ditandaiNa ? self::LABEL_TIDAK_DIUKUR : ($ada ? (string) $nilai : '-'),
                'skor' => $skor,
                'na' => $ditandaiNa,
            ];
        }

        return [
            'total' => $total,
            'kategori' => self::kategori($total),
            // Lengkap = semua parameter sudah TERISI atau ditandai tidak diukur.
            'lengkap' => $selesai === count(self::PARAMETER),
            'terisi' => $selesai,
            'terukur' => $terukur,
            'na' => $na,
            'detail' => $detail,
        ];
    }

    /**
     * Kategori risiko berdasarkan total skor.
     */
    public static function kategori(int $total): string
    {
        return match (true) {
            $total >= 7 => 'Tinggi',
            $total >= 4 => 'Sedang',
            default => 'Rendah',
        };
    }

    /**
     * Skor 0-3 untuk satu parameter. Mengembalikan null bila nilai di luar
     * rentang yang masuk akal sehingga tidak ikut dihitung sebagai 0 (nol
     * berarti "normal", sedangkan tidak diketahui bukan berarti normal).
     */
    public static function skor(string $parameter, mixed $nilai): ?int
    {
        return match ($parameter) {
            'pernapasan' => self::skorPernapasan((float) $nilai),
            'saturasi' => self::skorSaturasi((float) $nilai),
            'oksigen' => self::skorOksigen((string) $nilai),
            'td_sistolik' => self::skorSistolik((float) $nilai),
            'nadi' => self::skorNadi((float) $nilai),
            'kesadaran' => self::skorKesadaran((string) $nilai),
            'suhu' => self::skorSuhu((float) $nilai),
            default => null,
        };
    }

    /** Frekuensi pernapasan (x/menit). */
    private static function skorPernapasan(float $n): ?int
    {
        return match (true) {
            $n < 1 || $n > 60 => null,
            $n <= 8 => 3,
            $n <= 11 => 1,
            $n <= 20 => 0,
            $n <= 24 => 2,
            default => 3,
        };
    }

    /** Saturasi oksigen (%). */
    private static function skorSaturasi(float $n): ?int
    {
        return match (true) {
            $n < 50 || $n > 100 => null,
            $n <= 89 => 3,
            $n <= 91 => 2,
            $n <= 95 => 1,
            default => 0,
        };
    }

    /** Oksigen: air = 0, oksigen = 2. */
    private static function skorOksigen(string $v): ?int
    {
        return match ($v) {
            'Air' => 0,
            'Oksigen' => 2,
            default => null,
        };
    }

    /** Tekanan darah sistolik (mmHg). */
    private static function skorSistolik(float $n): ?int
    {
        return match (true) {
            $n < 50 || $n > 300 => null,
            $n <= 90 => 3,
            $n <= 100 => 2,
            $n <= 110 => 1,
            $n <= 219 => 0,
            default => 3,
        };
    }

    /** Nadi (x/menit). */
    private static function skorNadi(float $n): ?int
    {
        return match (true) {
            $n < 20 || $n > 250 => null,
            $n <= 40 => 3,
            $n <= 50 => 1,
            $n <= 90 => 0,
            $n <= 110 => 1,
            $n <= 130 => 2,
            default => 3,
        };
    }

    /** Kesadaran (AVPU): Compos Mentis = 0, selainnya 3. */
    private static function skorKesadaran(string $v): ?int
    {
        if ($v === '') {
            return null;
        }

        return $v === 'Compos Mentis' ? 0 : 3;
    }

    /** Suhu (derajat C). */
    private static function skorSuhu(float $n): ?int
    {
        return match (true) {
            $n < 25 || $n > 45 => null,
            $n <= 35.0 => 3,
            $n <= 36.0 => 1,
            $n <= 38.0 => 0,
            $n <= 39.0 => 1,
            default => 2,
        };
    }

    /** Label panjang parameter untuk tampilan. */
    public static function label(string $parameter): string
    {
        return match ($parameter) {
            'pernapasan' => 'Frekuensi Pernapasan',
            'saturasi' => 'Saturasi Oksigen',
            'oksigen' => 'Air atau Oksigen',
            'td_sistolik' => 'Tekanan Darah Sistolik',
            'nadi' => 'Nadi',
            'kesadaran' => 'Tingkat Kesadaran',
            'suhu' => 'Suhu',
            default => $parameter,
        };
    }

    /**
     * Kelas Tailwind untuk badge kategori risiko.
     */
    public static function badgeClass(string $kategori): string
    {
        return self::KATEGORI_WARNA[$kategori] ?? self::KATEGORI_WARNA['Rendah'];
    }
}
