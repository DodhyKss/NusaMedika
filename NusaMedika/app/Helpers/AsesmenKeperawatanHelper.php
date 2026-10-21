<?php

namespace App\Helpers;

/**
 * Definisi & perhitungan instrument asesmen yang dipakai form Asuhan
 * Keperawatan Lanjutan:
 *
 * - Form 34 `asesmen_status_fungsional` : 10 item ADL ala Barthel (skor
 *   maksimum 20) beserta ambang interpretasinya.
 * - Form 35 `pengkajian_keperawatan_kritis` : skala nyeri FLACC (0-10).
 *
 * Semua perhitungan dijalankan di SERVER. Nilai yang dikirim browser untuk
 * field turunan (`sf_skor`, `sf_interpretasi`, `skor_nyeri_kritis`) selalu
 * dibuang dan dihitung ulang di `filteredData()` masing-masing controller.
 */
class AsesmenKeperawatanHelper
{
    /**
     * 10 item ADL form 34. Nilai = variabel di emr_detail.
     *
     * Setiap item: ['label' => ..., 'opsi' => [['value' => ..., 'label' => ...], ...]].
     * Skor maksimum = 2+1+1+2+2+2+2+3+3+2 = 20.
     */
    public static function fungsionalItems(): array
    {
        return [
            'sf_makan' => [
                'label' => 'Makan (Feeding)',
                'opsi' => [
                    ['value' => '0', 'label' => '0 - Tidak mampu'],
                    ['value' => '1', 'label' => '1 - Butuh bantuan memotong, mengoles mentega'],
                    ['value' => '2', 'label' => '2 - Mandiri'],
                ],
            ],
            'sf_mandi' => [
                'label' => 'Mandi (Bathing)',
                'opsi' => [
                    ['value' => '0', 'label' => '0 - Tergantung orang lain'],
                    ['value' => '1', 'label' => '1 - Mandiri'],
                ],
            ],
            'sf_grooming' => [
                'label' => 'Perawatan Diri (Grooming)',
                'opsi' => [
                    ['value' => '0', 'label' => '0 - Butuh bantuan orang lain'],
                    ['value' => '1', 'label' => '1 - Mandiri dalam perawatan muka, rambut gigi, bercukur'],
                ],
            ],
            'sf_berpakaian' => [
                'label' => 'Berpakaian',
                'opsi' => [
                    ['value' => '0', 'label' => '0 - Tergantung orang lain'],
                    ['value' => '1', 'label' => '1 - Sebagian dibantu (mis. mengancing baju)'],
                    ['value' => '2', 'label' => '2 - Mandiri'],
                ],
            ],
            'sf_buang_air_kecil' => [
                'label' => 'Buang Air Kecil',
                'opsi' => [
                    ['value' => '0', 'label' => '0 - Inkontinensia / kateter tidak terkontrol'],
                    ['value' => '1', 'label' => '1 - Kadang inkontinensia (maks 1x/24 jam)'],
                    ['value' => '2', 'label' => '2 - Kontinensia (teratur lebih dari 7 hari)'],
                ],
            ],
            'sf_buang_air_besar' => [
                'label' => 'Buang Air Besar',
                'opsi' => [
                    ['value' => '0', 'label' => '0 - Inkontinensia / tidak teratur / perlu enema'],
                    ['value' => '1', 'label' => '1 - Kadang inkontinensia (sekali seminggu)'],
                    ['value' => '2', 'label' => '2 - Kontinensia (teratur)'],
                ],
            ],
            'sf_toilet' => [
                'label' => 'Penggunaan Toilet',
                'opsi' => [
                    ['value' => '0', 'label' => '0 - Tergantung bantuan orang lain'],
                    ['value' => '1', 'label' => '1 - Butuh bantuan, tetapi dapat melakukan sebagian sendiri'],
                    ['value' => '2', 'label' => '2 - Mandiri'],
                ],
            ],
            'sf_transfer' => [
                'label' => 'Transfer',
                'opsi' => [
                    ['value' => '0', 'label' => '0 - Tidak mampu'],
                    ['value' => '1', 'label' => '1 - Butuh bantuan untuk bisa duduk (2 orang)'],
                    ['value' => '2', 'label' => '2 - Bantuan kecil (1 orang)'],
                    ['value' => '3', 'label' => '3 - Mandiri'],
                ],
            ],
            'sf_mobilitas' => [
                'label' => 'Mobilitas',
                'opsi' => [
                    ['value' => '0', 'label' => '0 - Immobile (tidak mampu)'],
                    ['value' => '1', 'label' => '1 - Menggunakan kursi roda'],
                    ['value' => '2', 'label' => '2 - Berjalan dengan bantuan 1 orang'],
                    ['value' => '3', 'label' => '3 - Mandiri (meski memakai tongkat)'],
                ],
            ],
            'sf_tangga' => [
                'label' => 'Naik Turun Tangga',
                'opsi' => [
                    ['value' => '0', 'label' => '0 - Tidak mampu'],
                    ['value' => '1', 'label' => '1 - Membutuhkan bantuan / alat bantu'],
                    ['value' => '2', 'label' => '2 - Mandiri'],
                ],
            ],
        ];
    }

    /**
     * Ambang interpretasi skor status fungsional (total 0-20).
     */
    public static function interpretasiFungsional(int $skor): string
    {
        return match (true) {
            $skor >= 20 => 'Mandiri',
            $skor >= 12 => 'Ketergantungan Ringan',
            $skor >= 9 => 'Ketergantungan Sedang',
            $skor >= 5 => 'Ketergantungan Berat',
            default => 'Ketergantungan Total',
        };
    }

    /**
     * Hitung skor status fungsional dari 10 item jawaban.
     *
     * Kategori HANYA disimpan bila 10/10 item terisi - sama seperti aturan
     * EWS Helper untuk EWS parsial, karena skor parsial selalu terlalu rendah
     * dan menyesatkan.
     *
     * @param  array<string, mixed>  $data  jawaban mentah (nilai string "0".."3")
     * @return array{skor: int|string, interpretasi: string, lengkap: bool}
     */
    public static function hitungFungsional(array $data): array
    {
        $items = self::fungsionalItems();
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
            'skor' => $lengkap ? $total : '',
            'interpretasi' => $lengkap ? self::interpretasiFungsional($total) : '',
            'lengkap' => $lengkap,
        ];
    }

    /**
     * 5 butir FLACC (Face, Legs, Activity, Cry, Consolability), nilai 0-2.
     * Form 35. Nilai butir ini TIDAK punya objek sendiri (lihat R6 doc
     * KONSEP_ASUHAN_KEPERAWATAN_LANJUTAN) karena sudah terwakili oleh
     * `skor_nyeri_kritis` (objek 282).
     */
    public static function flaccItems(): array
    {
        return [
            'flacc_wajah' => [
                'label' => 'Wajah (Face)',
                'opsi' => [
                    ['value' => '0', 'label' => '0 - Tersenyum / tidak ada ekspresi khusus'],
                    ['value' => '1', 'label' => '1 - Kadang menangis / menarik diri'],
                    ['value' => '2', 'label' => '2 - Sering menggetarkan dagu / mengatupkan rahang'],
                ],
            ],
            'flacc_kaki' => [
                'label' => 'Kaki (Legs)',
                'opsi' => [
                    ['value' => '0', 'label' => '0 - Gerakan normal / relaksasi'],
                    ['value' => '1', 'label' => '1 - Tidak tenang / tegang'],
                    ['value' => '2', 'label' => '2 - Kaki dibuat menendang / menarik diri'],
                ],
            ],
            'flacc_aktivitas' => [
                'label' => 'Aktivitas (Activity)',
                'opsi' => [
                    ['value' => '0', 'label' => '0 - Tidur, posisi normal, mudah bergerak'],
                    ['value' => '1', 'label' => '1 - Gelombangmenggeliat, berguling, kaku'],
                    ['value' => '2', 'label' => '2 - Melengkungkan punggung / kaku / menghentak'],
                ],
            ],
            'flacc_menangis' => [
                'label' => 'Menangis (Cry)',
                'opsi' => [
                    ['value' => '0', 'label' => '0 - Tidak menangis (bangun / tidur)'],
                    ['value' => '1', 'label' => '1 - Mengerang / merengek'],
                    ['value' => '2', 'label' => '2 - Menangis terus-menerus, terhisak, menjerit'],
                ],
            ],
            'flacc_konsolabilitas' => [
                'label' => 'Konsolabilitas (Consolability)',
                'opsi' => [
                    ['value' => '0', 'label' => '0 - Rileks'],
                    ['value' => '1', 'label' => '1 - Tenang bila dipeluk / diajak bicara'],
                    ['value' => '2', 'label' => '2 - Sulit untuk menenangkan'],
                ],
            ],
        ];
    }

    /**
     * Total FLACC 0-10 (server-side; nilai browser diabaikan).
     */
    public static function hitungFlacc(array $data): int
    {
        $total = 0;

        foreach (array_keys(self::flaccItems()) as $variabel) {
            $total += (int) ($data[$variabel] ?? 0);
        }

        return $total;
    }

    /**
     * Interpretasi skor nyeri 0-10 (dipakai VAS maupun FLACC).
     */
    public static function interpretasiNyeri(int $skor): string
    {
        return match (true) {
            $skor === 0 => 'Tidak Nyeri',
            $skor <= 3 => 'Nyeri Ringan',
            $skor <= 6 => 'Nyeri Sedang',
            default => 'Nyeri Berat',
        };
    }
}
