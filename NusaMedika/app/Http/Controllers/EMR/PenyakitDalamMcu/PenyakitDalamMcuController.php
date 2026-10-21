<?php

namespace App\Http\Controllers\EMR\PenyakitDalamMcu;

use App\Http\Controllers\EMR\McuSpesialistikController;

/**
 * Form Penyakit Dalam MCU (form MCU spesialis).
 *
 * Tidak mendefinisikan apa pun sendiri — seluruh alur index/store/update/
 * destroy, validasi pola "Tidak Ada / Ada", dan `filteredData()` diwarisi dari
 * `McuSpesialistikController`. Daftar parameter organ diambil dari
 * `McuSpesialistikHelper::organ('penyakit_dalam_mcu')` yang sudah diverifikasi terhadap
 * `objek_form_control`.
 */
class PenyakitDalamMcuController extends McuSpesialistikController
{
    protected $slug = 'penyakit_dalam_mcu';

    protected $judulPanel = 'PEMERIKSAAN PENYAKIT DALAM';

    /**
     * Anamnesis spesifik penyakit dalam (objek 633) — di luar pola `_ada`.
     *
     * `keluhan_uraian` wajib hanya bila `keluhan_saat_ini` terisi
     * (konsep form 74: "ya bila keluhan ada").
     */
    protected function rulesTambahan(): array
    {
        return [
            'keluhan_uraian' => 'required_with:keluhan_saat_ini|string|max:2000',
            'riwayat_penyakit' => 'required|string|max:2000',
        ];
    }

    protected function anamnesisEkstra(): array
    {
        return [
            'keluhan_uraian' => 'Uraian Keluhan',
            'riwayat_penyakit' => 'Riwayat Penyakit',
        ];
    }
}
