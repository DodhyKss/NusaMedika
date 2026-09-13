<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PenerimaanDetail extends Model
{
    protected $table = 'penerimaan_detail';

    protected $primaryKey = 'penerimaan_detail_id';

    public $timestamps = false;

    protected $fillable = [
        'penerimaan_id',
        'barang_id',
        'no_batch',
        'jumlah_terima',
        'harga_beli',
        'harga_jual',
        'subtotal',
    ];

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereNull('status_batal')->orWhere('status_batal', 0);
        });
    }

    public function penerimaan(): BelongsTo
    {
        return $this->belongsTo(Penerimaan::class, 'penerimaan_id', 'penerimaan_id');
    }

    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class, 'barang_id', 'barang_id');
    }
}
