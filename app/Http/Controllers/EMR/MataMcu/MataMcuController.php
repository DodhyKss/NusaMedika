<?php

namespace App\Http\Controllers\EMR\MataMcu;

use App\Http\Controllers\EMR\McuSpesialistikController;

/**
 * Form Mata MCU (form MCU spesialis).
 *
 * Tidak mendefinisikan apa pun sendiri — seluruh alur index/store/update/
 * destroy, validasi pola "Tidak Ada / Ada", dan `filteredData()` diwarisi dari
 * `McuSpesialistikController`. Daftar parameter organ diambil dari
 * `McuSpesialistikHelper::organ('mata_mcu')` yang sudah diverifikasi terhadap
 * `objek_form_control`.
 */
class MataMcuController extends McuSpesialistikController
{
    protected $slug = 'mata_mcu';

    protected $judulPanel = 'PEMERIKSAAN MATA';

    /**
     * Visus mata kanan & kiri di-BACA dari form 72 "Visus Mata MCU".
     *
     * Field tetap punya mapping sendiri (objek 645/646) supaya form ini tetap
     * bisa dibaca utuh walau form 72 belum diisi — dengan nilai kosong, bukan
     * error. `readonly` di view, tapi tetap divalidasi sebagai `nullable`.
     */
    protected function rulesTambahan(): array
    {
        return [
            'visus_od' => 'nullable|string|max:50',
            'visus_os' => 'nullable|string|max:50',
        ];
    }
}
