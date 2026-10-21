<?php

namespace App\Http\Controllers\EMR\ThtMcu;

use App\Http\Controllers\EMR\McuSpesialistikController;

/**
 * Form THT MCU (form MCU spesialis).
 *
 * Tidak mendefinisikan apa pun sendiri — seluruh alur index/store/update/
 * destroy, validasi pola "Tidak Ada / Ada", dan `filteredData()` diwarisi dari
 * `McuSpesialistikController`. Daftar parameter organ diambil dari
 * `McuSpesialistikHelper::organ('tht_mcu')` yang sudah diverifikasi terhadap
 * `objek_form_control`.
 */
class ThtMcuController extends McuSpesialistikController
{
    protected $slug = 'tht_mcu';

    protected $judulPanel = 'PEMERIKSAAN THT';
}
