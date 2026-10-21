<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Master Implementasi Keperawatan (tabel `implementasi`).
 *
 * Dipakai di form EMR "Implementasi Keperawatan" sebagai sumber dropdown.
 */
class Implementasi extends Model
{
    protected $table = 'implementasi';

    protected $primaryKey = 'implementasi_id';

    public $timestamps = false;

    protected $fillable = [
        'kode_implementasi',
        'nama_implementasi',
    ];

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereNull('status_batal')->orWhere('status_batal', 0);
        });
    }
}
