<?php

namespace App\Http\Controllers\EMR\GigiMcu;

use App\Http\Controllers\EMR\McuSpesialistikController;

/**
 * Form Gigi MCU (form MCU spesialis).
 *
 * Tidak mendefinisikan apa pun sendiri — seluruh alur index/store/update/
 * destroy, validasi pola "Tidak Ada / Ada", dan `filteredData()` diwarisi dari
 * `McuSpesialistikController`. Daftar parameter organ diambil dari
 * `McuSpesialistikHelper::organ('gigi_mcu')` yang sudah diverifikasi terhadap
 * `objek_form_control`.
 */
class GigiMcuController extends McuSpesialistikController
{
    protected $slug = 'gigi_mcu';

    protected $judulPanel = 'PEMERIKSAAN GIGI';
}
