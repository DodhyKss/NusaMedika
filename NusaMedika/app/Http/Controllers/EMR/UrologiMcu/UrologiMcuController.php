<?php

namespace App\Http\Controllers\EMR\UrologiMcu;

use App\Http\Controllers\EMR\McuSpesialistikController;

/**
 * Form Urologi MCU (form MCU spesialis).
 *
 * Tidak mendefinisikan apa pun sendiri — seluruh alur index/store/update/
 * destroy, validasi pola "Tidak Ada / Ada", dan `filteredData()` diwarisi dari
 * `McuSpesialistikController`. Daftar parameter organ diambil dari
 * `McuSpesialistikHelper::organ('urologi_mcu')` yang sudah diverifikasi terhadap
 * `objek_form_control`.
 */
class UrologiMcuController extends McuSpesialistikController
{
    protected $slug = 'urologi_mcu';

    protected $judulPanel = 'PEMERIKSAAN UROLOGI';
}
