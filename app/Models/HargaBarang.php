<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HargaBarang extends Model
{
    protected $table = 'harga_barang';

    protected $primaryKey = 'harga_id';

    public $timestamps = false;

    protected $fillable = [
        'barang_id',
        'no_batch',
        'harga_beli',
        'harga_jual',
    ];

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereNull('status_batal')->orWhere('status_batal', 0);
        });
    }

    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class, 'barang_id', 'barang_id');
    }
}
