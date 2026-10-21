<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MutasiBarangDetail extends Model
{
    protected $table = 'mutasi_barang_detail';

    protected $primaryKey = 'mutasi_barang_detail_id';

    public $timestamps = false;

    protected $fillable = [
        'mutasi_barang_id',
        'barang_id',
        'no_batch',
        'jumlah',
    ];

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereNull('status_batal')->orWhere('status_batal', 0);
        });
    }

    public function mutasiBarang(): BelongsTo
    {
        return $this->belongsTo(MutasiBarang::class, 'mutasi_barang_id', 'mutasi_barang_id');
    }

    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class, 'barang_id', 'barang_id');
    }
}
