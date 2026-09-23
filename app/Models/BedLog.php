<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class BedLog extends Model
{
    protected $table = 'bed_log';

    protected $primaryKey = 'bed_log_id';

    public $timestamps = false;

    protected $fillable = [
        'pasien_id',
        'registrasi_detail_id',
        'bed_asal_id',
        'bagian_asal_id',
        'bed_id',
        'bagian_id',
        'aksi',
        'tgl_pindah',
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

    public function bed()
    {
        return $this->belongsTo(Bed::class, 'bed_id', 'bed_id');
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereNull('status_batal')->orWhere('status_batal', 0);
        });
    }
}
