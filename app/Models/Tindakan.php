<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tindakan extends Model
{
    protected $table = 'tindakan';

    protected $primaryKey = 'tindakan_id';

    public $timestamps = false;

    protected $fillable = [
        'kode_tindakan',
        'nama_tindakan',
        'bagian_id',
        'kategori',
        'satuan_hasil',
        'nilai_normal',
        'kode_bpjs',
        'kode_inacbg',
        'kode_loinc',
        'keterangan',
    ];

    public const KATEGORI = [
        'LAB' => 'Laboratorium',
        'RAD' => 'Radiologi',
        'LAIN' => 'Lainnya',
    ];

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereNull('status_batal')->orWhere('status_batal', 0);
        });
    }

    public function bagian(): BelongsTo
    {
        return $this->belongsTo(Bagian::class, 'bagian_id', 'bagian_id');
    }

    public function harga(): HasMany
    {
        return $this->hasMany(TindakanHarga::class, 'tindakan_id', 'tindakan_id')->aktif();
    }

    public function getKategoriLabelAttribute(): string
    {
        return self::KATEGORI[$this->kategori ?? 'LAIN'] ?? $this->kategori;
    }
}
