<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Master faktor penyakit penyerta diet (form 43, prasyarat PRE-05).
 *
 * Nilainya dipakai sebagai opsi multi-pilih `jenis_diagnosa_diet` yang
 * disimpan comma-separated dalam SATU baris `emr_detail` (lihat
 * `DietPasienController::filteredData()`).
 */
class MasterDiagnosaDiet extends Model
{
    protected $table = 'master_diagnosa_diet';

    protected $primaryKey = 'master_diagnosa_diet_id';

    public $timestamps = false;

    protected $fillable = [
        'kode_diagnosa_diet',
        'nama_diagnosa_diet',
        'keterangan',
    ];

    /** Record aktif (soft delete via status_batal). */
    public function scopeAktif(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereNull('status_batal')->orWhere('status_batal', 0);
        });
    }
}
