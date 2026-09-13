<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BarangJenis extends Model
{
    protected $table = 'barang_jenis';

    protected $primaryKey = 'barang_jenis_id';

    public $timestamps = false;

    protected $fillable = [
        'nama_jenis_barang',
    ];

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereNull('status_batal')->orWhere('status_batal', 0);
        });
    }

    public function barang(): HasMany
    {
        return $this->hasMany(Barang::class, 'jenis_barang_id', 'barang_jenis_id');
    }
}
