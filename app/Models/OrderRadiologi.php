<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderRadiologi extends Model
{
    protected $table = 'order_radiologi';

    protected $primaryKey = 'order_radiologi_id';

    public $timestamps = false;

    protected $fillable = [
        'no_order',
        'pasien_id',
        'registrasi_id',
        'registrasi_detail_id',
        'registrasi_detail_tujuan_id',
        'bagian_asal_id',
        'bagian_tujuan_id',
        'dokter_id',
        'prioritas',
        'status_order',
        'tanggal_order',
        'tanggal_terima',
        'tanggal_hasil',
        'petugas_pelaksana_id',
        'kelas_id',
        'hak_kelas_id',
        'keterangan',
    ];

    public const STATUS_LABEL = [
        0 => 'Menunggu',
        1 => 'Diproses',
        2 => 'Selesai',
        3 => 'Batal',
    ];

    public const STATUS_CLASS = [
        0 => 'bg-amber-100 text-amber-700',
        1 => 'bg-blue-100 text-blue-700',
        2 => 'bg-emerald-100 text-emerald-700',
        3 => 'bg-red-100 text-red-700',
    ];

    public const PRIORITAS = ['BIASA', 'CITO', 'SEGERA'];

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereNull('status_batal')->orWhere('status_batal', 0);
        });
    }

    public function details(): HasMany
    {
        return $this->hasMany(OrderRadiologiDetail::class, 'order_radiologi_id', 'order_radiologi_id')
            ->aktif();
    }

    public function pasien(): BelongsTo
    {
        return $this->belongsTo(Pasien::class, 'pasien_id', 'pasien_id');
    }

    public function dokter(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'dokter_id', 'pegawai_id');
    }

    public function petugasPelaksana(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'petugas_pelaksana_id', 'pegawai_id');
    }

    public function bagianAsal(): BelongsTo
    {
        return $this->belongsTo(Bagian::class, 'bagian_asal_id', 'bagian_id');
    }

    public function bagianTujuan(): BelongsTo
    {
        return $this->belongsTo(Bagian::class, 'bagian_tujuan_id', 'bagian_id');
    }

    public function registrasiDetail(): BelongsTo
    {
        return $this->belongsTo(RegistrasiDetail::class, 'registrasi_detail_id', 'registrasi_detail_id');
    }

    public function registrasiDetailTujuan(): BelongsTo
    {
        return $this->belongsTo(RegistrasiDetail::class, 'registrasi_detail_tujuan_id', 'registrasi_detail_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABEL[$this->status_order ?? 0] ?? '-';
    }

    public function getStatusClassAttribute(): string
    {
        return self::STATUS_CLASS[$this->status_order ?? 0] ?? 'bg-slate-100 text-slate-600';
    }
}
