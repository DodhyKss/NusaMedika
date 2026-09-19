<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderRadiologiDetail extends Model
{
    protected $table = 'order_radiologi_detail';

    protected $primaryKey = 'order_radiologi_detail_id';

    public $timestamps = false;

    protected $fillable = [
        'order_radiologi_id',
        'tindakan_id',
        'nama_tindakan',
        'harga',
        'hasil',
        'flag_abnormal',
        'status',
        'petugas_id',
    ];

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereNull('status_batal')->orWhere('status_batal', 0);
        });
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(OrderRadiologi::class, 'order_radiologi_id', 'order_radiologi_id');
    }

    public function tindakan(): BelongsTo
    {
        return $this->belongsTo(Tindakan::class, 'tindakan_id', 'tindakan_id');
    }

    public function petugas(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'petugas_id', 'pegawai_id');
    }
}
