<?php

namespace App\Http\Controllers\EMR\JantungMcu;

use App\Http\Controllers\EMR\McuSpesialistikController;

/**
 * Form Jantung MCU (form MCU spesialis).
 *
 * Tidak mendefinisikan apa pun sendiri — seluruh alur index/store/update/
 * destroy, validasi pola "Tidak Ada / Ada", dan `filteredData()` diwarisi dari
 * `McuSpesialistikController`. Daftar parameter organ diambil dari
 * `McuSpesialistikHelper::organ('jantung_mcu')` yang sudah diverifikasi terhadap
 * `objek_form_control`.
 */
class JantungMcuController extends McuSpesialistikController
{
    protected $slug = 'jantung_mcu';

    protected $judulPanel = 'PEMERIKSAAN JANTUNG';

    /**
     * Panel penunjang: EKG, treadmill, dan echocardiography.
     *
     * Field ini TIDAK mengikuti pola `_ada`, jadi harus dideklarasikan
     * eksplisit di sini — aturan di dalam loop `McuSpesialistikController`
     * hanya mencakup parameter organ.
     */
    protected function rulesTambahan(): array
    {
        return [
            'ekg_hr' => 'nullable|numeric|between:20,250',
            'ekg_rhythm' => 'nullable|string|max:100',
            'ekg_kelainan' => 'nullable|string|max:255',
            'ekg_kesimpulan' => 'nullable|string|max:255',
            'threadmill_case' => 'nullable|in:0,1',
            'threadmill_kesimpulan' => 'nullable|string|max:255',
            'echocardiography' => 'nullable|in:0,1',
            'echocardiography_kesimpulan' => 'nullable|string|max:255',
        ];
    }
}
