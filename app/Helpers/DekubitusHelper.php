<?php

namespace App\Helpers;

/**
 * Pusat logika instrumenPenilaian Risiko Dekubitus (Norton) dan Barthel Index.
 *
 * Dua instrumen ini sama-sama punya arah skor yang BERLAWANAN dengan Morse
 * Fall Scale: **skor RENDAH berarti risiko/ketergantungan TINGGI**. Safer
 * point, hai ini yang paling sering terbalik di layar, jadi:
 *
 *   - Norton   : total 5-20.  < 12 = Risiko Tinggi, 12-15 = Risiko Sedang,
 *                 16-20 = Tidak Berisiko / Risiko Rendah.
 *   - Barthel  : total 0-20.  0-4 = Ketergantungan Total, 5-8 Berat,
 *                 9-11 Sedang, 12-19 Ringan, 20 = Mandiri.
 *
 * Skor dan kategori SELALU dihitung ulang di server dari jawaban item mentah
 * (`hitungNorton()` / `hitungBarthel()`); nilai yang dikirim browser untuk
 * `skor_norton`, `kategori_dekubitus`, `barthel_skor`, dan
 * `tingkat_ketergantungan` dibuang tanpa dibacakan. Skor parsial TIDAK
 * disimpan (NULL) karena 3 dari 5 butir bisa menghasilkan skor yang terlihat
 * aman padahal belum lengkap - pola yang sama dengan `EwsHelper`.
 *
 * REFERENSI:
 *   - Norton Scale (Norton 1961; lihat legacy `score_norton.php`): 5 item x 4
 *     opsi, skor per item menurun dari 4 ke 1.
 *   - Barthel Index (Mahoney & Barthel 1965; legacy `barthel_index.php`):
 *     10 item ADL, skor maksimum 20.
 */
class DekubitusHelper
{
    /** Rentang total skor Norton (5 item x skor 1-4). */
    public const SKOR_NORTON_MIN = 5;

    public const SKOR_NORTON_MAKS = 20;

    /** Skor maksimum Barthel Index: 3+3+1+1+2+2+2+2+2+2. */
    public const SKOR_BARTHEL_MAKS = 20;

    // =====================================================================
    // 1. NORTON - RISIKO DEKUBITUS
    // =====================================================================

    /**
     * 5 item Skala Norton, keyed by variabel di emr_detail.
     *
     * Setiap item: ['label' => ..., 'opsi' => [['value' => ..., 'label' => ...]]].
     * Opsi diurutkan dari skor TERTINGGI ke terendah supaya tabel di blade
     * langsung terbaca "4 = Baik" di baris pertama.
     */
    public static function nortonItems(): array
    {
        return [
            'norton_kondisi_fisik_umum' => [
                'label' => 'Kondisi Fisik Umum',
                'opsi' => [
                    ['value' => '4', 'label' => '4 - Baik'],
                    ['value' => '3', 'label' => '3 - Lumayan'],
                    ['value' => '2', 'label' => '2 - Buruk'],
                    ['value' => '1', 'label' => '1 - Sangat Buruk'],
                ],
            ],
            'norton_kesadaran' => [
                'label' => 'Kesadaran',
                'opsi' => [
                    ['value' => '4', 'label' => '4 - Compos Mentis'],
                    ['value' => '3', 'label' => '3 - Apatis'],
                    ['value' => '2', 'label' => '2 - Konfus / Sopor'],
                    ['value' => '1', 'label' => '1 - Stupor / Koma'],
                ],
            ],
            'norton_aktivitas' => [
                'label' => 'Aktivitas',
                'opsi' => [
                    ['value' => '4', 'label' => '4 - Ambulansi Normal'],
                    ['value' => '3', 'label' => '3 - Ambulansi dengan Bantuan'],
                    ['value' => '2', 'label' => '2 - Hanya Bisa Duduk'],
                    ['value' => '1', 'label' => '1 - Tiduran'],
                ],
            ],
            'norton_mobilitas' => [
                'label' => 'Mobilitas',
                'opsi' => [
                    ['value' => '4', 'label' => '4 - Bergerak Bebas'],
                    ['value' => '3', 'label' => '3 - Sedikit Terbatas'],
                    ['value' => '2', 'label' => '2 - Sangat Terbatas'],
                    ['value' => '1', 'label' => '1 - Tidak Bisa Bergerak'],
                ],
            ],
            'norton_inkontinensia' => [
                'label' => 'Inkontinensia',
                'opsi' => [
                    ['value' => '4', 'label' => '4 - Tidak Ada'],
                    ['value' => '3', 'label' => '3 - Kadang-kadang'],
                    ['value' => '2', 'label' => '2 - Sering Inkontinensia Urine'],
                    ['value' => '1', 'label' => '1 - Inkontinensia Alvi dan Urine'],
                ],
            ],
        ];
    }

    /**
     * Ambang kategori Norton, verbatim `score_norton.php:132-155`.
     *
     * Perhatikan arahnya: skor RENDAH = risiko TINGGI.
     */
    public static function kategoriNorton(): array
    {
        return [
            [
                'label' => 'Risiko Tinggi Terjadi Dekubitus',
                'min' => self::SKOR_NORTON_MIN,
                'max' => 11,
                'warna' => 'red',
                'warna_class' => 'text-red-600',
                'keterangan' => 'Pagliare dekubitus, rep Positions sesuai program, terapi Matlabrium, dapil around.',
            ],
            [
                'label' => 'Risiko Sedang Terjadi Dekubitus',
                'min' => 12,
                'max' => 15,
                'warna' => 'amber',
                'warna_class' => 'text-amber-600',
                'keterangan' => 'Pencegahan dekubitus rutin, jaga kulit tetap kering, lembap seimbang, dan monitor nyeri.',
            ],
            [
                'label' => 'Tidak Berisiko / Risiko Rendah Terjadi Dekubitus',
                'min' => 16,
                'max' => self::SKOR_NORTON_MAKS,
                'warna' => 'lime',
                'warna_class' => 'text-lime-600',
                'keterangan' => 'Perawatan rutin, tidak memerlukan intervensi khusus dekubitus.',
            ],
        ];
    }

    /**
     * Kategori risiko dekubitus dari total skor Norton (skor rendah = risiko
     * tinggi). `null` bila skor belum lengkap / di luar rentang.
     */
    public static function interpretasiNorton(?int $skor): ?string
    {
        if ($skor === null || $skor < self::SKOR_NORTON_MIN || $skor > self::SKOR_NORTON_MAKS) {
            return null;
        }

        foreach (self::kategoriNorton() as $kat) {
            if ($skor >= $kat['min'] && $skor <= $kat['max']) {
                return $kat['label'];
            }
        }

        return null;
    }

    /**
     * Warna badge untuk sebuah total skor Norton (dipakai blade & JS).
     */
    public static function warnaNorton(?int $skor): string
    {
        if ($skor === null) {
            return 'text-slate-600';
        }

        foreach (self::kategoriNorton() as $kat) {
            if ($skor >= $kat['min'] && $skor <= $kat['max']) {
                return $kat['warna_class'];
            }
        }

        return 'text-slate-600';
    }

    /**
     * Hitung total skor & kategori Norton dari 5 item jawaban mentah.
     *
     * Kategori hanyaproduced bila 5/5 butir terisi; selain itu NULL, karena
     * skor parsial selalu terlihat lebih aman dari kenyataan (R5 doc).
     *
     * @param  array<string, mixed>  $data  jawaban mentah (nilai string "1".."4")
     * @return array{skor: int|null, kategori: string|null, lengkap: bool, terisi: int, total: int}
     */
    public static function hitungNorton(array $data): array
    {
        $items = self::nortonItems();
        $total = 0;
        $terisi = 0;

        foreach (array_keys($items) as $variabel) {
            $nilai = $data[$variabel] ?? null;

            if ($nilai === null || $nilai === '') {
                continue;
            }

            $total += (int) $nilai;
            $terisi++;
        }

        $lengkap = $terisi === count($items);

        return [
            'skor' => $lengkap ? $total : null,
            'kategori' => $lengkap ? self::interpretasiNorton($total) : null,
            'lengkap' => $lengkap,
            'terisi' => $terisi,
            'total' => $total,
        ];
    }

    // =====================================================================
    // 2. BARTHEL INDEX - KEMANDIRIAN AKTIVITAS SEHARI-HARI
    // =====================================================================

    /**
     * 10 item Barthel Index baku, keyed by variabel di emr_detail.
     *
     * PENTING: arah inkontinensia Barthel berlawanan dengan tabel status
     * fungsional (form 34 `asesmen_status_fungsional`). Di sini skor 1 =
     * KADANG inkontinen dan skor 0 = inkontinen; di form 34 justru sebaliknya.
     * Jangan menyamakan keduanya.
     */
    public static function barthelItems(): array
    {
        return [
            'barthel_transfer' => [
                'label' => 'Transfer (tidur ke duduk)',
                'opsi' => [
                    ['value' => '3', 'label' => '3 - Mandiri'],
                    ['value' => '2', 'label' => '2 - Dibantu Satu Orang'],
                    ['value' => '1', 'label' => '1 - Dibantu Dua Orang'],
                    ['value' => '0', 'label' => '0 - Tidak Mampu'],
                ],
            ],
            'barthel_mobilisasi' => [
                'label' => 'Mobilisasi',
                'opsi' => [
                    ['value' => '3', 'label' => '3 - Mandiri'],
                    ['value' => '2', 'label' => '2 - Dibantu Satu Orang / Walker'],
                    ['value' => '1', 'label' => '1 - Dengan Kursi Roda'],
                    ['value' => '0', 'label' => '0 - Tergantung Orang Lain'],
                ],
            ],
            'barthel_toilet' => [
                'label' => 'Penggunaan Toilet',
                'opsi' => [
                    ['value' => '1', 'label' => '1 - Mandiri'],
                    ['value' => '0', 'label' => '0 - Perlu pertolongan orang lain'],
                ],
            ],
            'barthel_bersih' => [
                'label' => 'Kebersihan Diri',
                'opsi' => [
                    ['value' => '1', 'label' => '1 - Mandiri'],
                    ['value' => '0', 'label' => '0 - Perlu pertolongan orang lain'],
                ],
            ],
            'barthel_bab' => [
                'label' => 'Buang Air Besar',
                'opsi' => [
                    ['value' => '2', 'label' => '2 - Kontinen teratur'],
                    ['value' => '1', 'label' => '1 - Kadang-kadang inkontinen'],
                    ['value' => '0', 'label' => '0 - Inkontinen'],
                ],
            ],
            'barthel_bak' => [
                'label' => 'Buang Air Kecil',
                'opsi' => [
                    ['value' => '2', 'label' => '2 - Kontinen teratur'],
                    ['value' => '1', 'label' => '1 - Kadang-kadang inkontinen'],
                    ['value' => '0', 'label' => '0 - Inkontinen'],
                ],
            ],
            'barthel_mandi' => [
                'label' => 'Mandi',
                'opsi' => [
                    ['value' => '2', 'label' => '2 - Mandiri'],
                    ['value' => '1', 'label' => '1 - Perlu pertolongan'],
                    ['value' => '0', 'label' => '0 - Tergantung pertolongan orang'],
                ],
            ],
            'barthel_berpakaian' => [
                'label' => 'Berpakaian',
                'opsi' => [
                    ['value' => '2', 'label' => '2 - Mandiri'],
                    ['value' => '1', 'label' => '1 - Sebagian dibantu'],
                    ['value' => '0', 'label' => '0 - Tergantung orang lain'],
                ],
            ],
            'barthel_makan' => [
                'label' => 'Makan',
                'opsi' => [
                    ['value' => '2', 'label' => '2 - Mandiri'],
                    ['value' => '1', 'label' => '1 - Perlu pertolongan'],
                    ['value' => '0', 'label' => '0 - Tergantung orang lain'],
                ],
            ],
            'barthel_tangga' => [
                'label' => 'Naik Turun Tangga',
                'opsi' => [
                    ['value' => '2', 'label' => '2 - Mandiri'],
                    ['value' => '1', 'label' => '1 - Perlu pertolongan'],
                    ['value' => '0', 'label' => '0 - Tidak Mampu'],
                ],
            ],
        ];
    }

    /**
     * Ambang tingkat ketergantungan Barthel.
     *
     * Sama seperti Norton: skor RENDAH = ketergantungan TINGGI.
     */
    public static function kategoriBarthel(): array
    {
        return [
            [
                'label' => 'Ketergantungan Total',
                'min' => 0,
                'max' => 4,
                'warna' => 'red',
                'warna_class' => 'text-red-600',
                'keterangan' => 'Butuh bantuan total untuk seluruh aktivitas sehari-hari.',
            ],
            [
                'label' => 'Ketergantungan Berat',
                'min' => 5,
                'max' => 8,
                'warna' => 'yellow',
                'warna_class' => 'text-yellow-600',
                'keterangan' => 'Sebagian besar aktivitas masih memerlukan bantuan orang lain.',
            ],
            [
                'label' => 'Ketergantungan Sedang',
                'min' => 9,
                'max' => 11,
                'warna' => 'slate',
                'warna_class' => 'text-slate-600',
                'keterangan' => 'Membutuhkan bantuan pada beberapa aktivitas,sisanya mandiri.',
            ],
            [
                'label' => 'Tingkat Ketergantungan Ringan',
                'min' => 12,
                'max' => 19,
                'warna' => 'blue',
                'warna_class' => 'text-blue-600',
                'keterangan' => 'Mandiri pada sebagian besar aktivitas, masih memerlukan sedikit bantuan.',
            ],
            [
                'label' => 'Mandiri',
                'min' => self::SKOR_BARTHEL_MAKS,
                'max' => self::SKOR_BARTHEL_MAKS,
                'warna' => 'green',
                'warna_class' => 'text-emerald-600',
                'keterangan' => 'Mampu melakukan seluruh aktivitas sehari-hari tanpa bantuan.',
            ],
        ];
    }

    /**
     * Tingkat ketergantungan dari total skor Barthel (skor rendah =
     * ketergantungan tinggi). `null` bila skor belum lengkap / di luar rentang.
     */
    public static function interpretasiBarthel(?int $skor): ?string
    {
        if ($skor === null || $skor < 0 || $skor > self::SKOR_BARTHEL_MAKS) {
            return null;
        }

        foreach (self::kategoriBarthel() as $kat) {
            if ($skor >= $kat['min'] && $skor <= $kat['max']) {
                return $kat['label'];
            }
        }

        return null;
    }

    /**
     * Warna badge untuk sebuah total skor Barthel (dipakai blade & JS).
     */
    public static function warnaBarthel(?int $skor): string
    {
        if ($skor === null) {
            return 'text-slate-600';
        }

        foreach (self::kategoriBarthel() as $kat) {
            if ($skor >= $kat['min'] && $skor <= $kat['max']) {
                return $kat['warna_class'];
            }
        }

        return 'text-slate-600';
    }

    /**
     * Hitung total skor & tingkat ketergantungan Barthel dari 10 item.
     *
     * Tingkat hanya disimpan bila 10/10 butir terisi.
     *
     * @param  array<string, mixed>  $data  jawaban mentah (nilai string "0".."3")
     * @return array{skor: int|null, tingkat: string|null, lengkap: bool, terisi: int, total: int}
     */
    public static function hitungBarthel(array $data): array
    {
        $items = self::barthelItems();
        $total = 0;
        $terisi = 0;

        foreach (array_keys($items) as $variabel) {
            $nilai = $data[$variabel] ?? null;

            if ($nilai === null || $nilai === '') {
                continue;
            }

            $total += (int) $nilai;
            $terisi++;
        }

        $lengkap = $terisi === count($items);

        return [
            'skor' => $lengkap ? $total : null,
            'tingkat' => $lengkap ? self::interpretasiBarthel($total) : null,
            'lengkap' => $lengkap,
            'terisi' => $terisi,
            'total' => $total,
        ];
    }
}
