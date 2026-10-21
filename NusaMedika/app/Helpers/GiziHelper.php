<?php

namespace App\Helpers;

use Carbon\Carbon;

/**
 * Pusat definisi & perhitungan asesmen gizi.
 *
 * Dipakai tiga form: 39 (Asesmen Gizi — IMT/status gizi/penurunan BB),
 * 40 (Detail Nutrisi — instrumen skrining SGA / MNA-SF / MST / MST-Anak) dan
 * 44 (ADIME — reuses objek Diagnosa Gizi / Intervensi / MONEV).
 *
 * Semua perhitungan dijalankan di SERVER. Nilai yang dikirim browser untuk
 * field turunan (`bmi`, `skor`, `kesimpulan`) selalu dibuang dan dihitung ulang
 * di `filteredData()` masing-masing controller.
 *
 * Aturan yang diwarisi dari `EwsHelper`:
 * - Fungsi mengembalikan `null` (bukan 0) bila input kosong/tidak wajar,
 *   karena 0 berarti "normal" sedangkan "tidak diketahui" berbeda artinya.
 * - Skor parsial TIDAK pernah disimpan; parsial selalu terlalu rendah dan
 *   menyesatkan.
 */
class GiziHelper
{
    /** Instrumen skrining gizi yang tersedia (form 40). */
    public const INSTRUMEN = ['SGA', 'MNA-SF', 'MST', 'MST-Anak'];

    /**
     * Ambang Indeks Massa Tubuh (IMT/BMI) versi Indonesia (Belfanti).
     * Dipakai HANYA untuk pasien dewasa — anak (< 18 tahun) ditentukan dari
     * Z-score WHO yang diisi manual (objek 369), bukan dari IMT.
     *
     * @return array<int, array{0: float, 1: float, 2: string}> [batas atas, batas bawah, label]
     */
    public static function ambangImt(): array
    {
        return [
            [16.0, 0.0, 'Benar-benar Sangat Kekurangan Berat Badan'],
            [16.9, 16.0, 'Sangat Kekurangan Berat Badan'],
            [18.4, 17.0, 'Kekurangan Berat Badan'],
            [24.9, 18.5, 'Normal'],
            [29.9, 25.0, 'Kelebihan Berat Badan'],
            [34.9, 30.0, 'Obesitas Kelas I'],
            [39.9, 35.0, 'Obesitas Kelas II'],
            [PHP_INT_MAX, 40.0, 'Obesitas Kelas III'],
        ];
    }

    /**
     * Usia pasien dalam tahun (nilai bulat, floors).
     *
     * Mengembalikan null bila tanggal lahir atau tanggal daftar kosong/tidak
     * bisa di-parse — pemanggil lalu menyembunyikan badge yang bergantung usia.
     */
    public static function usia(?string $tglLahir, ?string $tglDaftar): ?int
    {
        if (blank($tglLahir) || blank($tglDaftar)) {
            return null;
        }
        try {
            $lahir = Carbon::parse($tglLahir);
            $daftar = Carbon::parse($tglDaftar);
        } catch (\Throwable) {
            return null;
        }

        return $daftar->diffInYears($lahir);
    }

    /**
     * Indeks Massa Tubuh = bb / (tb/100)^2.
     *
     * Null bila bb atau tb kosong, atau tb <= 0 (division by zero).
     * Nilai dibulatkan 1 desimal supaya tidak berdesimal panjang di UI.
     */
    public static function imt($bb, $tb): ?float
    {
        if (! is_numeric($bb) || ! is_numeric($tb)) {
            return null;
        }
        $bb = (float) $bb;
        $tb = (float) $tb;
        if ($bb <= 0 || $tb <= 0) {
            return null;
        }

        return round($bb / ((($tb / 100) ** 2)), 1);
    }

    /**
     * Status gizi dari IMT (khusus dewasa).
     *
     * Mengembalikan null bila IMT null, atau bila pasien anak — untuk anak
     * status gizi ditentukan dari Z-score WHO yang diisi manual, sehingga badge
     * IMT sengaja dikosongkan daripada menampilkan angka yang menyesatkan.
     */
    public static function statusGizi(?float $imt, ?int $umur): ?string
    {
        if ($imt === null) {
            return null;
        }
        if ($umur !== null && $umur < 18) {
            return null;
        }
        foreach (self::ambangImt() as [$atas, $bawah, $label]) {
            if ($imt >= $bawah && $imt <= $atas) {
                return $label;
            }
        }

        return null;
    }

    /**
     * Persentase penurunan berat badan terhadap berat badan biasanya.
     *
     * Mengembalikan null bila salah satu nilai kosong atau bb biasa <= 0.
     * Nilai negatif (bertambah berat) tetap dikembalikan apa adanya — form
     * menampilkan apa yang diketik petugas.
     */
    public static function penurunanBbPersen($bbSekarang, $bbBiasanya): ?float
    {
        if (! is_numeric($bbSekarang) || ! is_numeric($bbBiasanya)) {
            return null;
        }
        $bb = (float) $bbSekarang;
        $biasa = (float) $bbBiasanya;
        if ($biasa <= 0) {
            return null;
        }

        return round((($biasa - $bb) / $biasa) * 100, 1);
    }

    /**
     * Instrumen default berdasarkan usia (SGA bisa dipilih manual sebagai override).
     *
     * - 0-18 tahun  → MST-Anak
     * - 19-59 tahun → MNA-SF
     * - >= 60 tahun → MST
     *
     * Bila usia tidak diketahui → SGA (yang paling umum dipakai legacy).
     */
    public static function instrumenDefault(?int $umur): string
    {
        if ($umur === null) {
            return 'SGA';
        }

        return match (true) {
            $umur <= 18 => 'MST-Anak',
            $umur <= 59 => 'MNA-SF',
            default => 'MST',
        };
    }

    /**
     * Daftar butir + label untuk sebuah instrumen.
     *
     * @return array<string, array<int, array{skor: int|string, label: string}>>
     */
    public static function butirInstrumen(string $instrumen): array
    {
        return match ($instrumen) {
            'SGA' => [
                'sga_1' => [1 => 'Tidak ada penurunan', 2 => 'Kehilangan < 5%', 3 => 'Kehilangan 5-10%', 4 => 'Kehilangan 10-15%', 5 => 'Kehilangan > 15%'],
                'sga_2' => [1 => 'Tidak ada perubahan', 2 => 'Sub-optimal solid (½-¾ porsi)', 3 => 'Hanya cair / padat < ½ porsi', 4 => 'Hipokalori liquid', 5 => 'Tidak ada asfoten'],
                'sga_3' => [1 => 'Tidak ada gejala', 2 => 'Nausea', 3 => 'Muntah / gangguan cerna', 4 => 'Diare', 5 => 'Severe anorexia'],
                'sga_4' => [1 => 'Normal', 2 => 'Aktivitas fisik terbatas (ringan)', 3 => 'Terbatas (sedang)', 4 => 'Sangat ringan (di tempat tidur)', 5 => 'Bedrest / berdiri dengan bantuan'],
                'sga_5' => [2 => '1 penyakit penyerta', 3 => '2 penyakit penyerta', 4 => '3 penyakit penyerta', 5 => 'Komorbiditas multiple + kondisi sangat berat'],
            ],
            'MNA-SF' => [
                'mna_1' => [0 => 'Tidak', 2 => 'Ya'],
                'mna_2' => [1 => 'Penurunan 1-6 kg', 2 => 'Penurunan > 6 kg', 3 => 'Penurunan > 10 kg'],
                'mna_3' => [0 => 'Tidak ada gangguan', 1 => 'Gangguan ringan', 2 => 'Gangguan berat'],
                'mna_4' => [0 => 'Tidak', 3 => 'Ya'],
            ],
            'MST' => [
                'mst_a' => [0 => 'Penurunan BB tidak jelas dalam 3 bulan', 1 => 'Penurunan BB ringan dalam 3 bulan', 2 => 'Penurunan BB berat dalam 3 bulan'],
                'mst_b' => [0 => 'Tidak ada gangguan pencernaan', 1 => 'Gangguan pencernaan ringan', 2 => 'Gangguan pencernaan berat'],
                'mst_c' => [0 => 'Fungsi fisiologis normal', 1 => 'Sedikit lumpuh', 2 => 'Lumpuh / lumpuh berat'],
                'mst_d' => [0 => 'Aktivitas normal', 1 => 'Tidak bisa bergerak', 2 => 'Tidak bisa berdiri / berbangku'],
                'mst_e' => [0 => 'Tidak ada masalah stressor psikologis', 1 => 'Masalah psikologis sedang', 2 => 'Masalah psikologis berat'],
                'mst_f1' => [0 => 'IMT >= 21', 1 => 'IMT 19 - 20,9', 2 => 'IMT <= 19'],
                'mst_f2' => [0 => 'Lingkar betis >= 33 cm', 1 => 'Lingkar betis < 33 cm'],
            ],
            'MST-Anak' => [
                'mstak_1' => [0 => 'Tidak ada', 1 => 'Ada'],
                'mstak_2' => [0 => 'Tidak ada', 1 => 'Ada'],
                'mstak_3' => [0 => 'Tidak ada', 1 => 'Ada'],
                'mstak_4' => [0 => 'Tidak ada', 1 => 'Ada'],
            ],
            default => [],
        };
    }

    /**
     * Diagnosis khusus (checkbox) per instrumen. Nilai = skor bila dicentang.
     *
     * Dipakai untuk item D MNA-SF (skor 3 bila ada diagnosis khusus) dan 16
     * diagnosis khusus anak pada MST-Anak.
     */
    public static function diagnosisKhusus(string $instrumen): array
    {
        return match ($instrumen) {
            'MST-Anak' => [
                'mstak_diag_1' => 'Berat badan < 2 tahun tidak pernah naik',
                'mstak_diag_2' => 'Berat badan 2-5 tahun tidak pernah naik',
                'mstak_diag_3' => 'Dukungan nutrisi terus diperlukan',
                'mstak_diag_4' => 'Penyakit hemodynamic kronis',
                'mstak_diag_5' => 'Gangguan saraf pusat kronis',
                'mstak_diag_6' => 'Infeksi berulang / kronis',
                'mstak_diag_7' => 'Diabetes mellitus',
                'mstak_diag_8' => 'Gangguan saluran cerna',
                'mstak_diag_9' => 'Tumor ganas',
                'mstak_diag_10' => 'Kelainan Down syndrome',
                'mstak_diag_11' => 'Gangguan perkembangan neurologis',
                'mstak_diag_12' => 'Riwayat cedera / fraktur',
                'mstak_diag_13' => 'Riwayat rawat inap lebih dari 2 hari',
                'mstak_diag_14' => 'Penyakit sistemik lain',
                'mstak_diag_15' => 'Diet khusus (mis. juice & gain bebas)',
                'mstak_diag_16' => 'Gangguan penyerapannya',
            ],
            'MNA-SF' => [
                'mna_diag_1' => 'Nausea',
                'mna_diag_2' => 'Muntah',
                'mna_diag_3' => 'Diare',
                'mna_diag_4' => 'Hiposgeusia',
                'mna_diag_5' => 'Hipersgeusia',
                'mna_diag_6' => 'Hiposmia',
                'mna_diag_7' => 'Hipersmia',
                'mna_diag_8' => 'Kesulitan menelan',
                'mna_diag_9' => 'Kesulitan mengunyah',
                'mna_diag_10' => 'Dispepsia',
                'mna_diag_11' => 'Gangguanmotilitas GI',
                'mna_diag_12' => 'Gangguan penyerapan',
                'mna_diag_13' => 'Diabetes mellitus tipe 1',
                'mna_diag_14' => 'Kelainan tiroid',
                'mna_diag_15' => 'Kelainan presidentan',
            ],
            default => [],
        };
    }

    /**
     * Hitung skor & kesimpulan instrumen skrining gizi.
     *
     * Mengembalikan skor null bila ada butir instrumen aktif yang belum terisi,
     * supaya parse parsial tidak menghasilkan "Normal" yang menyesatkan.
     * Item di luar instrumen aktif diabaikan (sudah dibuang di filteredData).
     *
     * @param  array<string, mixed>  $jawaban  jawaban mentah per butir
     * @return array{skor: int|null, kesimpulan: string|null}
     */
    public static function hitung(string $instrumen, array $jawaban): array
    {
        $butir = self::butirInstrumen($instrumen);
        if ($butir === []) {
            return ['skor' => null, 'kesimpulan' => null];
        }

        $skor = 0;
        foreach ($butir as $variabel => $opsi) {
            $nilai = $jawaban[$variabel] ?? null;
            if ($nilai === null || $nilai === '') {
                // Ada butir kosong → skor parsial, jangan disimpan.
                return ['skor' => null, 'kesimpulan' => null];
            }
            $nilai = (int) $nilai;
            if (! array_key_exists($nilai, $opsi)) {
                return ['skor' => null, 'kesimpulan' => null];
            }
            $skor += $nilai;
        }

        // Diagnosis khusus: bila salah satu tercentang, kontribusi +
        //   MST-Anak : +3 (maksimum total 7)
        //   MNA-SF   : +3 pada butir D (sudah masuk radio mna_4, jadi di sini
        //              TIDAK ditambah dua kali)
        // Lihat docs/KONSEP_ASESMEN_GIZI.md §4.6.
        if ($instrumen === 'MST-Anak') {
            foreach (self::diagnosisKhusus($instrumen) as $variabel => $label) {
                if (! empty($jawaban[$variabel])) {
                    $skor += 3;
                    break;
                }
            }
        }

        return ['skor' => $skor, 'kesimpulan' => self::kesimpulan($instrumen, $skor)];
    }

    /**
     * Kesimpulan (interpretasi) dari skor instrumen.
     *
     * Ambang mengikuti KONSEP_ASESMEN_GIZI.md §4.2:
     * - SGA      : total 0-25, kategori Well Nourished / Mildly Malnourished /
     *               Moderately Malnourished / Severely Malnourished
     * - MNA-SF   : 0-14, >= 2 → lakukan asesmen lanjut oleh ahli gizi
     * - MST      : 12-14 Normal · 8-11 Berisiko Malnutrisi · 0-7 Malnutrisi
     * - MST-Anak : 0 Risiko Rendah · 1-3 Risiko Sedang · 4-5 Risiko Tinggi
     */
    public static function kesimpulan(string $instrumen, int $skor): ?string
    {
        // SGA SENGAJA mengembalikan null: legacy hanya menampilkan total skor
        // tanpa kategori, dan belum ada keputusan klinis tertulis mengenai
        // ambang konversinya (docs/KONSEP_ASESMEN_GIZI.md §11 risiko #3).
        // Menyimpan kategori hasil karangan justru menyesatkan — lebih baik
        // menampilkan skor saja. Form 41 (Rekap) berstatus ⛔ karena hal sama.
        if ($instrumen === 'SGA') {
            return null;
        }

        return match ($instrumen) {
            'MNA-SF' => $skor >= 2 ? 'Perlu Asesmen Lanjut oleh Ahli Gizi' : 'Gizi Baik (Normal)',
            'MST' => match (true) {
                $skor >= 12 => 'Normal',
                $skor >= 8 => 'Berisiko Malnutrisi',
                default => 'Malnutrisi',
            },
            'MST-Anak' => match (true) {
                $skor === 0 => 'Risiko Rendah',
                $skor <= 3 => 'Risiko Sedang',
                // 4-5 = Risiko Tinggi; 6-7 hanya mungkin bila diagnosis
                // khusus (+3) dan kategorinya tetap Risiko Tinggi.
                default => 'Risiko Tinggi',
            },

            default => 'Tidak Diketahui',
        };
    }

    /**
     * Daftar variabel butir yang valid untuk sebuah instrumen (untuk filter payload).
     */
    public static function variabelInstrumen(string $instrumen): array
    {
        return array_merge(
            array_keys(self::butirInstrumen($instrumen)),
            array_keys(self::diagnosisKhusus($instrumen))
        );
    }
}
