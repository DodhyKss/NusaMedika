<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

/**
 * Prefill diagnosa medis terakhir untuk satu kunjungan.
 *
 * Legacy memakai `diagnosisDokter($registrasiId)` (order/DPJP) untuk form
 * Rekam Medis Rujukan (58) dan SPRI/PRB (128). Fungsinya tidak ada di
 * codebase baru, jadi sumber terdekat yang tersedia adalah nilai objek 40
 * "Diagnosa Medis" terakhir pada `registrasi_detail` yang sama (diisi form
 * 1/58 dsb). Dipakai untuk PREFILL SAAT BARU saja — nilai browser tetap
 * yang disimpan dan boleh diedit (KONSEP_FORMULIR_RUJUKAN.md §Catatan).
 *
 * Catatan: form 2 (SOAP) TIDAK punya variabel `diagnosis` di mapping
 * (hanya subjective/objective/assessment/planning/instruksi), jadi prefill
 * "dari SOAP" diimplementasikan lewat objek 40 ini — deviasi terdokumentasi
 * pada TASK_FORM EMR-058 / EMR-128.
 */
class DiagnosisHelper
{
    /**
     * Nilai diagnosa medis (objek 40) terakhir & non-kosong pada kunjungan.
     */
    public static function terakhir(int $registrasiDetailId): string
    {
        $value = DB::table('emr_detail as d')
            ->join('emr as e', 'e.emr_id', '=', 'd.emr_id')
            ->where('d.objek_id', 40)
            ->where('e.registrasi_detail_id', $registrasiDetailId)
            ->whereNotNull('d.value')
            ->where('d.value', '!=', '')
            ->where(function ($q) {
                $q->whereNull('d.status_batal')->orWhere('d.status_batal', 0);
            })
            ->where(function ($q) {
                $q->whereNull('e.status_batal')->orWhere('e.status_batal', 0);
            })
            ->orderByDesc('e.input_time')
            ->orderByDesc('d.emr_detail_id')
            ->value('d.value');

        return is_string($value) ? $value : '';
    }
}
