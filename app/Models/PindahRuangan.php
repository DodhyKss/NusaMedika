<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PindahRuangan extends Model
{
    protected $table = 'pindah_ruangan';

    protected $primaryKey = 'pindah_ruangan_id';

    public $timestamps = false;

    protected $fillable = [
        'pasien_id',
        'registrasi_detail_id',
        'bed_asal_id',
        'bagian_asal_id',
        'bed_tujuan_id',
        'bagian_tujuan_id',
        'status',
        'keterangan',
        'disetujui_user_id',
        'disetujui_time',
        'input_time',
        'input_user_id',
        'mod_time',
        'mod_user_id',
        'status_batal',
    ];

    public function pasien()
    {
        return $this->belongsTo(Pasien::class, 'pasien_id', 'pasien_id');
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereNull('status_batal')->orWhere('status_batal', 0);
        });
    }
}
