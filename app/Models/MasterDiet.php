<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Master jenis diet pasien (form 43 "Diet Pasien", prasyarat PRE-05).
 *
 * `nama_diet` adalah nilai yang disimpan di `emr_detail` pada variabel
 * `jenis_diet`, jadi form 43 memvalidasinya dengan
 * `exists:master_diet,nama_diet`.
 *
 * PK kustom `master_diet_id` (bukan `id`) —WAJIB ditulis eksplisit pada
 * `Rule::unique(...)->ignore($id, 'master_diet_id')` karena default Laravel
 * menebak kolom `id` yang tidak ada.
 */
class MasterDiet extends Model
{
    protected $table = 'master_diet';

    protected $primaryKey = 'master_diet_id';

    public $timestamps = false;

    protected $fillable = [
        'kode_diet',
        'nama_diet',
        'keterangan',
    ];

    /** Record aktif (soft delete via status_batal, bukan deleted_at). */
    public function scopeAktif(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereNull('status_batal')->orWhere('status_batal', 0);
        });
    }
}
