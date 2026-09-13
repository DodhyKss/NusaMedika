<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Satuan extends Model
{
    protected $table = 'satuan';

    protected $primaryKey = 'satuan_id';

    public $timestamps = false;

    protected $fillable = [
        'nama_satuan',
        'singkatan',
    ];

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereNull('status_batal')->orWhere('status_batal', 0);
        });
    }
}
