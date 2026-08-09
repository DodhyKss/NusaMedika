<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ICD extends Model
{
    protected $table = 'icd';

    protected $primaryKey = 'icd_id';

    public $timestamps = false;

    protected $fillable = [
        'kode_diagnosa',
        'nama_diagnosa',
        'kategori',
        'jenis_diagnosa',
        'penyakit_id',
    ];

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->where('status_batal', '!=', 1)->orWhereNull('status_batal');
        });
    }
}
