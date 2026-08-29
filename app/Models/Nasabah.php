<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Nasabah extends Model
{
    protected $table = 'nasabah';

    protected $primaryKey = 'nasabah_id';

    public $timestamps = false;

    protected $casts = [
        'instalasi' => 'array',
    ];

    protected $fillable = [
        'nama_nasabah',
    ];

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereNull('status_batal')->orWhere('status_batal', 0);
        });
    }
}
