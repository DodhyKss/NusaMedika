<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PeresepanObatDetail extends Model
{
    protected $table = 'peresepan_obat_detail';

    protected $primaryKey = 'peresepan_obat_detail_id';

    public $timestamps = false;

    protected $fillable = [
        'peresepan_obat_id',
        'barang_id',
        'jumlah',
        's_1',
        's_2',
        'aturan_pakai',
        'rute_pemberian',
        'flag_dispense',
        'jumlah_dispense',
        'no_batch',
        'bagian_dispense_id',
        'waktu_dispense',
    ];

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereNull('status_batal')->orWhere('status_batal', 0);
        });
    }

    public function peresepanObat(): BelongsTo
    {
        return $this->belongsTo(PeresepanObat::class, 'peresepan_obat_id', 'peresepan_obat_id');
    }

    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class, 'barang_id', 'barang_id');
    }

    public function bagianDispense(): BelongsTo
    {
        return $this->belongsTo(Bagian::class, 'bagian_dispense_id', 'bagian_id');
    }
}
