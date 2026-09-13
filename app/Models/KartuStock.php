<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KartuStock extends Model
{
    protected $table = 'kartu_stock';

    protected $primaryKey = 'kartu_stock_id';

    public $timestamps = false;

    protected $fillable = [
        'barang_id',
        'no_batch',
        'bagian_id',
        'tanggal',
        'jenis_mutasi',
        'ref_penerimaan_detail_id',
        'ref_mutasi_barang_detail_id',
        'qty_masuk',
        'qty_keluar',
        'saldo_sebelum',
        'saldo_sesudah',
        'keterangan',
    ];

    public const JENIS_LABEL = [
        0 => 'Saldo Awal',
        1 => 'Penerimaan',
        2 => 'Mutasi Masuk',
        3 => 'Mutasi Keluar',
        4 => 'Pemakaian',
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

    public function bagian(): BelongsTo
    {
        return $this->belongsTo(Bagian::class, 'bagian_id', 'bagian_id');
    }

    public function penerimaanDetail(): BelongsTo
    {
        return $this->belongsTo(PenerimaanDetail::class, 'ref_penerimaan_detail_id', 'penerimaan_detail_id');
    }

    public function mutasiBarangDetail(): BelongsTo
    {
        return $this->belongsTo(MutasiBarangDetail::class, 'ref_mutasi_barang_detail_id', 'mutasi_barang_detail_id');
    }

    public function getJenisLabelAttribute(): string
    {
        return self::JENIS_LABEL[$this->jenis_mutasi ?? 0] ?? '-';
    }
}
