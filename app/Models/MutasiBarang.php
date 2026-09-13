<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MutasiBarang extends Model
{
    protected $table = 'mutasi_barang';

    protected $primaryKey = 'mutasi_barang_id';

    public $timestamps = false;

    protected $fillable = [
        'no_mutasi',
        'bagian_asal_id',
        'bagian_tujuan_id',
        'tanggal_mutasi',
        'keterangan',
    ];

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereNull('status_batal')->orWhere('status_batal', 0);
        });
    }

    public function bagianAsal(): BelongsTo
    {
        return $this->belongsTo(Bagian::class, 'bagian_asal_id', 'bagian_id');
    }

    public function bagianTujuan(): BelongsTo
    {
        return $this->belongsTo(Bagian::class, 'bagian_tujuan_id', 'bagian_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(MutasiBarangDetail::class, 'mutasi_barang_id', 'mutasi_barang_id');
    }
}
