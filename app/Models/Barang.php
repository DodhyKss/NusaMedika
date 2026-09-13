<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Barang extends Model
{
    protected $table = 'barang';

    protected $primaryKey = 'barang_id';

    public $timestamps = false;

    protected $fillable = [
        'nama_barang',
        'jenis_barang_id',
    ];

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereNull('status_batal')->orWhere('status_batal', 0);
        });
    }

    public function jenis(): BelongsTo
    {
        return $this->belongsTo(BarangJenis::class, 'jenis_barang_id', 'barang_jenis_id');
    }
}
