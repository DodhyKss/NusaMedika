<?php

namespace App\Helpers;

/**
 * Pusat definisi & perhitungan skala nyeri.
 *
 * FLACC dipakai di tiga form (27 Penilaian Nyeri, 36 Metode Nyeri, 35
 * Pengkajian Keperawatan Kritis) dan VAS/NRS di form 27 & 36, sehingga definisi
 * butir FLACC TIDAK boleh diduplikasi di tiap controller. Definisi butirnya
 * diambil dari `AsesmenKeperawatanHelper::flaccItems()` (satu-satunya sumber),
 * sedangkan ambang kategorinya hidup di sini.
 *
 * Semua perhitungan dijalankan di SERVER. Nilai yang dikirim browser untuk field
 * turunan (`skor_flacc`, `interpretasi_nyeri`, `total_nips`) selalu dibuang dan
 * dihitung ulang di `filteredData()` masing-masing controller.
 */
class NyeriHelper
{
    /**
     * 5 butir FLACC (Face, Legs, Activity, Cry, Consolability), nilai 0-2.
     *
     * Sumber tunggal definisi butir + label opsi lives di
     * `AsesmenKeperawatanHelper::flaccItems()` supaya tidak ada dua tempat yang
     * bisa berbeda (lihat docs/KONSEP_NYERI.md R11).
     */
    public static function flaccItems(): array
    {
        return AsesmenKeperawatanHelper::flaccItems();
    }

    /**
     * Total FLACC 0-10 beserta kategorinya.
     *
     * Skor HANYA dikembalikan bila 5/5 butir terisi. Skor parsial selalu terlalu
     * rendah dan menyesatkan — pola yang sama dipakai `EwsHelper` untuk EWS.
     *
     * @param  array<string, mixed>  $a  jawaban mentah per butir FLACC
     * @return array{total: int|null, kategori: string}
     */
    public static function flacc(array $a): array
    {
        $items = self::flaccItems();
        $total = 0;
        $terisi = 0;

        foreach (array_keys($items) as $variabel) {
            $nilai = $a[$variabel] ?? null;

            if ($nilai === null || $nilai === '') {
                continue;
            }

            $total += (int) $nilai;
            $terisi++;
        }

        if ($terisi < count($items)) {
            return ['total' => null, 'kategori' => ''];
        }

        return ['total' => $total, 'kategori' => self::kategori($total)];
    }

    /**
     * 6 parameter NIPS (Neonatal Infant Pain Scale), rentang 0-2.
     * Total maksimum 7. Kriteria verbatim dari legacy `skrining_nyeri_perina.php`.
     */
    public static function nipsItems(): array
    {
        return [
            'nips_ekspresi_wajah' => [
                'label' => 'Ekspresi Wajah (Facial Expression)',
                'opsi' => [
                    ['value' => '0', 'label' => '0 - Relaksasi, wajah tenang, ekspresi netral'],
                    ['value' => '1', 'label' => '1 - Meringis, otot wajah tegang, alis berkerut'],
                ],
            ],
            'nips_tangisan' => [
                'label' => 'Tangisan (Cry)',
                'opsi' => [
                    ['value' => '0', 'label' => '0 - Tidak menangis, tenang'],
                    ['value' => '1', 'label' => '1 - Meringis, merengek lemah, intermiten'],
                    ['value' => '2', 'label' => '2 - Menangis, menjerit, menangis terus menerus'],
                ],
            ],
            'nips_pola_napas' => [
                'label' => 'Pola Napas (Respiration Pattern)',
                'opsi' => [
                    ['value' => '0', 'label' => '0 - Relaksasi, bernapas biasa'],
                    ['value' => '1', 'label' => '1 - Perubahan pola napas, tarikan irregular, lebih cepat dari biasa'],
                ],
            ],
            'nips_gerakan_lengan' => [
                'label' => 'Gerakan Lengan (Arm Motion)',
                'opsi' => [
                    ['value' => '0', 'label' => '0 - Relaksasi, tidak ada kekuatan otot / gerakan'],
                    ['value' => '1', 'label' => '1 - Fleksi / ekstensi, tegang, kaku'],
                ],
            ],
            'nips_gerakan_tungkai' => [
                'label' => 'Gerakan Tungkai (Leg Motion)',
                'opsi' => [
                    ['value' => '0', 'label' => '0 - Relaksasi, tidak ada kekuatan otot / gerakan'],
                    ['value' => '1', 'label' => '1 - Fleksi / ekstensi, tegang, kaku'],
                ],
            ],
            'nips_status_jaga' => [
                'label' => 'Status Jaga (State of Alertness)',
                'opsi' => [
                    ['value' => '0', 'label' => '0 - Tidur / bangun, tenang'],
                    ['value' => '1', 'label' => '1 - Rewel, gelisah'],
                ],
            ],
        ];
    }

    /**
     * Total NIPS 0-7 beserta interpretasinya.
     *
     * Total HANYA dikembalikan bila 6/6 parameter terisi (pola EWS: skor parsial
     * menyesatkan).
     *
     * @param  array<string, mixed>  $a  jawaban mentah per parameter NIPS
     * @return array{total: int|null, interpretasi: string}
     */
    public static function nips(array $a): array
    {
        $items = self::nipsItems();
        $total = 0;
        $terisi = 0;

        foreach (array_keys($items) as $variabel) {
            $nilai = $a[$variabel] ?? null;

            if ($nilai === null || $nilai === '') {
                continue;
            }

            $total += (int) $nilai;
            $terisi++;
        }

        if ($terisi < count($items)) {
            return ['total' => null, 'interpretasi' => ''];
        }

        return [
            'total' => $total,
            'interpretasi' => self::interpretasiNips($total),
        ];
    }

    /**
     * Interpretasi total NIPS 0-7.
     */
    public static function interpretasiNips(int $total): string
    {
        return match (true) {
            $total <= 2 => 'Tidak Nyeri - Nyeri Ringan',
            $total <= 4 => 'Nyeri Ringan - Sedang',
            default => 'Nyeri Berat',
        };
    }

    /**
     * Kategori skor nyeri 0-10. Dipakai VAS/NRS maupun FLACC supaya kedua
     * metode punya label yang konsisten (0 tidak nyeri, 1-3 ringan, 4-6 sedang,
     * 7-10 berat).
     */
    public static function kategori(int $skor): string
    {
        return match (true) {
            $skor <= 0 => 'Tidak Nyeri',
            $skor <= 3 => 'Nyeri Ringan',
            $skor <= 6 => 'Nyeri Sedang',
            default => 'Nyeri Berat',
        };
    }

    /**
     * Kategori VAS/NRS. Alias eksplisit supaya maksud pemanggil jelas.
     */
    public static function kategoriVas(int $skor): string
    {
        return self::kategori($skor);
    }

    /**
     * Normalisasi data URL gambar body map.
     *
     * Nilai yang bukan PNG data URL (mis. teks kosong atau kiriman yang bukan
     * gambar) dibuang menjadi NULL supaya `emr_detail` tidak pernah menyimpan
     * sampah pada kolom TEXT 64 KB.
     */
    public static function normalisasiGambar(?string $gambar): ?string
    {
        if ($gambar === null) {
            return null;
        }

        $gambar = trim($gambar);

        if ($gambar === '') {
            return null;
        }

        return str_starts_with($gambar, 'data:image/png;base64,') ? $gambar : null;
    }
}
