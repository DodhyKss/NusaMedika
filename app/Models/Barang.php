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
        'kode_barang',
        'nama_barang',
        'jenis_barang_id',
        'satuan_id',
        'is_racikan',
        'is_fornas',
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

    public function satuan(): BelongsTo
    {
        return $this->belongsTo(Satuan::class, 'satuan_id', 'satuan_id');
    }

    public const FORNAS_LABEL = [
        0 => 'Non Fornas',
        1 => 'Fornas Nasional',
        2 => 'Fornas Rumah Sakit',
    ];

    public function getRacikanLabelAttribute(): string
    {
        return $this->is_racikan ? 'Racikan' : 'Non Racikan';
    }

    public function getFornasLabelAttribute(): string
    {
        return self::FORNAS_LABEL[$this->is_fornas ?? 0] ?? 'Non Fornas';
    }

    public function textSatuan(): string
    {
        return $this->satuan ? $this->satuan->singkatan ?: $this->satuan->nama_satuan : '-';
    }
}
