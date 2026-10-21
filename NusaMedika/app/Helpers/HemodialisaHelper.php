<?php

namespace App\Helpers;

/**
 * Helper form Hemodialisa (Fase P5 — form 102–105).
 *
 * Sumber rancangan: docs/KONSEP_ICU_HEMODIALISA.md §9.
 * Semua fungsi mengembalikan null (bukan 0) bila input kosong/tidak wajar —
 * konsisten dengan EwsHelper::skor().
 */
class HemodialisaHelper
{
    public const AKSES_VASKULER = [
        'akses_fistula' => 'AV. Fistula',
        'akses_graft' => 'AV. Graft',
        'akses_sndl' => 'SN',
        'akses_cdl' => 'CDL',
        'akses_cdl_thermo' => 'CDL Long Therm',
        'akses_femoral' => 'Femoral',
    ];

    public const DIALYSAT = [
        'Bicarbonate' => 'Bicarbonate',
        'Acetate' => 'Acetate',
        'Citrate' => 'Citrate',
    ];

    public const DIALYZER = ['F6HPS', 'F8 HPS', 'ELISIO 15 H', 'FX 80 CLASSIX'];

    /**
     * Lama dialisis dalam menit; menangani jam selesai < jam mulai
     * (sesi lintas tengah malam ⇒ +1440). Null bila salah satu kosong.
     */
    public static function lamaDialisis(?string $jamMulai, ?string $jamSelesai): ?int
    {
        if ($jamMulai === null || $jamMulai === '' || $jamSelesai === null || $jamSelesai === '') {
            return null;
        }

        $mulai = strtotime('1970-01-01 '.$jamMulai);
        $selesai = strtotime('1970-01-01 '.$jamSelesai);
        if ($mulai === false || $selesai === false) {
            return null;
        }

        $selisih = intdiv($selesai - $mulai, 60);
        if ($selisih <= 0) {
            $selisih += 1440;
        }

        return $selisih;
    }

    /**
     * Peringatan gain/selisih berat badan terhadap BB kering.
     *
     * @return array{selisih_kg: float|null, kategori: string}
     */
    public static function selisihBb(?float $bbSekarang, ?float $bbKering): array
    {
        if ($bbSekarang === null || $bbKering === null) {
            return ['selisih_kg' => null, 'kategori' => 'Data Tidak Lengkap'];
        }

        $selisih = round($bbSekarang - $bbKering, 2);
        $kategori = match (true) {
            $selisih > 3 => 'Gain BB',
            $selisih < 0 => 'Susut BB',
            default => 'Ideal',
        };

        return ['selisih_kg' => $selisih, 'kategori' => $kategori];
    }

    /**
     * Kt/V & efektivitas HD (SNKF: target >= 1,2).
     *
     * @return array{nilai: float|null, adequat: bool|null, label: string}
     */
    public static function ktv($ktv): array
    {
        if ($ktv === null || $ktv === '' || ! is_numeric($ktv)) {
            return ['nilai' => null, 'adequat' => null, 'label' => '-'];
        }

        $nilai = (float) $ktv;
        $adequat = $nilai >= 1.2;

        return [
            'nilai' => $nilai,
            'adequat' => $adequat,
            'label' => $adequat ? 'Adekuat' : 'Belum Adekuat',
        ];
    }

    /**
     * Tekanan arteri rata-rata (MAP) dari sistolik & diastolik.
     */
    public static function meanArterialPressure($sistolik, $diastolik): ?float
    {
        if ($sistolik === null || $sistolik === '' || $diastolik === null || $diastolik === ''
            || ! is_numeric($sistolik) || ! is_numeric($diastolik)) {
            return null;
        }

        $s = (float) $sistolik;
        $d = (float) $diastolik;
        if ($s < 30 || $s > 300 || $d < 10 || $d > 200) {
            return null;
        }

        return round($d + ($s - $d) / 3, 1);
    }
}
