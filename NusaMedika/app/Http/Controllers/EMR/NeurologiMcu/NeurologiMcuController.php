<?php

namespace App\Http\Controllers\EMR\NeurologiMcu;

use App\Http\Controllers\EMR\McuSpesialistikController;

/**
 * Form Neurologi MCU (form MCU spesialis).
 *
 * Tidak mendefinisikan apa pun sendiri — seluruh alur index/store/update/
 * destroy, validasi pola "Tidak Ada / Ada", dan `filteredData()` diwarisi dari
 * `McuSpesialistikController`. Daftar parameter organ diambil dari
 * `McuSpesialistikHelper::organ('neurologi_mcu')` yang sudah diverifikasi terhadap
 * `objek_form_control`.
 */
class NeurologiMcuController extends McuSpesialistikController
{
    protected $slug = 'neurologi_mcu';

    protected $judulPanel = 'PEMERIKSAAN NEUROLOGI';
}
