<?php

namespace App\Helpers;

/**
 * Skor pemulihan pasca-anestesi (form 87 Asesmen Post-Anestesi & Sedasi).
 *
 * Semua total dihitung ulang di server — nilai browser dibuang di
 * filteredData(). Ambang kriteria pemulihan mengikuti standar Aldrete.
 */
class SkorPemulihanHelper
{
    /**
     * Menghitung seluruh skor pemulihan dari data mentah.
     *
     * @return array{alderete:int|null,bromage:int|null,steward:int|null,padss:int|null,kriteria:?string,kategori_alderete:?string}
     */
    public static function hitung(array $data): array
    {
        $alderete = self::total([
            $data['skor_alderete_kesadaran'] ?? null,
            $data['skor_alderete_aktivitas'] ?? null,
            $data['skor_alderete_ventilasi'] ?? null,
            $data['skor_alderete_warna_kulit'] ?? null,
        ], 0, 2);

        $bromage = self::total([
            $data['skor_bromage_tangan'] ?? null,
            $data['skor_bromage_lengan'] ?? null,
            $data['skor_bromage_kaki'] ?? null,
        ], 0, 3);

        $steward = self::total([
            $data['skor_steward_pergerakan'] ?? null,
            $data['skor_steward_pernapasan'] ?? null,
            $data['skor_steward_kesadaran'] ?? null,
        ], 0, 3);

        $padss = self::total([
            $data['skor_padss_vas'] ?? null,
            $data['skor_padss_aktivitas'] ?? null,
        ], 0, 3);

        // Ambang Aldrete (> 8 layak ke ruang; <= 5 rujuk ICU).
        $kategori = null;
        $kriteria = null;
        if ($alderete !== null) {
            if ($alderete <= 5) {
                $kategori = 'merah';
                $kriteria = 'Rujuk ICU';
            } elseif ($alderete <= 8) {
                $kategori = 'kuning';
                $kriteria = 'Perlu Pemantauan';
            } else {
                $kategori = 'hijau';
                $kriteria = 'Layak ke Ruang';
            }
        }

        return [
            'alderete' => $alderete,
            'bromage' => $bromage,
            'steward' => $steward,
            'padss' => $padss,
            'kategori_alderete' => $kategori,
            'kriteria' => $kriteria,
        ];
    }

    /**
     * Jumlah item yang semuanya terisi dan dalam rentang min..max.
     * Mengembalikan null bila ada item kosong (skor parsial menyesatkan).
     */
    private static function total(array $items, int $min, int $max): ?int
    {
        $sum = 0;
        foreach ($items as $item) {
            if ($item === null || $item === '' || ! is_numeric($item)) {
                return null;
            }
            $v = (int) $item;
            if ($v < $min || $v > $max) {
                return null;
            }
            $sum += $v;
        }

        return $sum;
    }

    /**
     * Format "H:MM" / "HH:MM" menjadi menit sejak tengah malam.
     */
    public static function jamKeMenit(?string $jam): ?int
    {
        if ($jam === null || $jam === '') {
            return null;
        }
        $parts = array_map('intval', explode(':', $jam));
        if (count($parts) < 2) {
            return null;
        }

        return $parts[0] * 60 + $parts[1];
    }

    /**
     * Durasi antara dua jam (menit), menangani lintas tengah malam.
     * Mengembalikan null bila salah satu jam kosong.
     */
    public static function durasiMenit(?string $mulai, ?string $selesai): ?int
    {
        $m = self::jamKeMenit($mulai);
        $s = self::jamKeMenit($selesai);
        if ($m === null || $s === null) {
            return null;
        }
        $d = $s - $m;
        if ($d < 0) {
            $d += 24 * 60;
        }

        return $d;
    }

    /**
     * Format durasi menit menjadi "H jam M menit" / "M menit".
     */
    public static function formatDurasi(?int $menit): string
    {
        if ($menit === null) {
            return '';
        }
        $h = intdiv($menit, 60);
        $m = $menit % 60;
        if ($h === 0) {
            return $m.' menit';
        }

        return $h.' jam '.$m.' menit';
    }
}
