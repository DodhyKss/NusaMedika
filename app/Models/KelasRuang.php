<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class KelasRuang extends Model
{
    protected $table = 'kelas_ruang';

    protected $primaryKey = 'kelas_ruang_id';

    public $timestamps = false;

    protected $fillable = [
        'nama_kelas_ruang',
        'kelas_khusus',
        'kelas_bpjs',
    ];

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->where('status_batal', '!=', 1)->orWhereNull('status_batal');
        });
    }
}
