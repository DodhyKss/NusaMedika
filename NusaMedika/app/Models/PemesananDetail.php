<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PemesananDetail extends Model
{
    protected $table = 'pemesanan_detail';

    protected $primaryKey = 'pemesanan_detail_id';

    public $timestamps = false;

    protected $fillable = [
        'pemesanan_id',
        'barang_id',
        'supplier_id',
        'distributor_id',
        'jumlah_pesan',
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

    public function pemesanan(): BelongsTo
    {
        return $this->belongsTo(Pemesanan::class, 'pemesanan_id', 'pemesanan_id');
    }

    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class, 'barang_id', 'barang_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id', 'supplier_id');
    }

    public function distributor(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'distributor_id', 'supplier_id');
    }
}
