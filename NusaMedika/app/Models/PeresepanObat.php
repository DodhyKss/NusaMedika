<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PeresepanObat extends Model
{
    protected $table = 'peresepan_obat';

    protected $primaryKey = 'peresepan_obat_id';

    public $timestamps = false;

    protected $fillable = [
        'no_resep',
        'dokter_id',
        'pasien_id',
        'registrasi_detail_id',
        'status_resep',
        'tanggal_resep',
        'keterangan',
    ];

    public const STATUS_LABEL = [
        0 => 'Menunggu',
        1 => 'Selesai',
        2 => 'Batal',
        3 => 'Dispense Sebagian',
    ];

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereNull('status_batal')->orWhere('status_batal', 0);
        });
    }

    public function details(): HasMany
    {
        return $this->hasMany(PeresepanObatDetail::class, 'peresepan_obat_id', 'peresepan_obat_id')
            ->aktif();
    }

    public function dokter(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'dokter_id', 'pegawai_id');
    }

    public function pasien(): BelongsTo
    {
        return $this->belongsTo(Pasien::class, 'pasien_id', 'pasien_id');
    }

    public function registrasiDetail(): BelongsTo
    {
        return $this->belongsTo(RegistrasiDetail::class, 'registrasi_detail_id', 'registrasi_detail_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABEL[$this->status_resep ?? 0] ?? '-';
    }
}
