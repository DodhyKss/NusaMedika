<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SuketMcu extends Model
{
    protected $table = 'suket_mcu';

    protected $primaryKey = 'suket_mcu_id';

    public $timestamps = false;

    protected $fillable = [
        'nama_suket',
        'harga',
    ];

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->where('status_batal', '!=', 1)->orWhereNull('status_batal');
        });
    }
}
