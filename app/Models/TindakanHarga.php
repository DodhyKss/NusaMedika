<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TindakanHarga extends Model
{
    protected $table = 'tindakan_harga';

    protected $primaryKey = 'tindakan_harga_id';

    public $timestamps = false;

    protected $fillable = [
        'tindakan_id',
        'kelas_ruang_id',
        'tarif',
        'tarif_bpjs',
    ];

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereNull('status_batal')->orWhere('status_batal', 0);
        });
    }

    public function tindakan(): BelongsTo
    {
        return $this->belongsTo(Tindakan::class, 'tindakan_id', 'tindakan_id');
    }

    public function kelasRuang(): BelongsTo
    {
        return $this->belongsTo(KelasRuang::class, 'kelas_ruang_id', 'kelas_ruang_id');
    }
}
