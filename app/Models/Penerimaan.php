<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Penerimaan extends Model
{
    protected $table = 'penerimaan';

    protected $primaryKey = 'penerimaan_id';

    public $timestamps = false;

    protected $fillable = [
        'pemesanan_id',
        'no_faktur',
        'supplier_id',
        'bagian_id',
        'tanggal_terima',
        'keterangan',
    ];

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereNull('status_batal')->orWhere('status_batal', 0);
        });
    }

    public function pemesanan(): BelongsTo
    {
        return $this->belongsTo(Pemesanan::class, 'pemesanan_id', 'pemesanan_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id', 'supplier_id');
    }

    public function bagian(): BelongsTo
    {
        return $this->belongsTo(Bagian::class, 'bagian_id', 'bagian_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(PenerimaanDetail::class, 'penerimaan_id', 'penerimaan_id');
    }
}
