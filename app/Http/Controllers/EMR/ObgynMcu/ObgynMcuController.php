<?php

namespace App\Http\Controllers\EMR\ObgynMcu;

use App\Http\Controllers\EMR\McuSpesialistikController;

/**
 * Form OBGYN MCU (form MCU spesialis).
 *
 * Tidak mendefinisikan apa pun sendiri — seluruh alur index/store/update/
 * destroy, validasi pola "Tidak Ada / Ada", dan `filteredData()` diwarisi dari
 * `McuSpesialistikController`. Daftar parameter organ diambil dari
 * `McuSpesialistikHelper::organ('obgyn_mcu')` yang sudah diverifikasi terhadap
 * `objek_form_control`.
 */
class ObgynMcuController extends McuSpesialistikController
{
    protected $slug = 'obgyn_mcu';

    protected $judulPanel = 'PEMERIKSAAN OBGYN';
}
