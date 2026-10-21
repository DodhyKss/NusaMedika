<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Bed extends Model
{
    protected $table = 'bed';

    protected $primaryKey = 'bed_id';

    public $timestamps = false;

    protected $fillable = [
        'bagian_id',
        'no_kamar',
        'nama_bed',
        'status_bed',
        'kelas_id',
        'tgl_masuk',
        'pasien_id_1',
        'pasien_id_2',
        'flag_isolasi',
        'keterangan',
        'flag_isolasi_pressure',
        'flag_ventilator',
        'lokasi',
        'flag_neonatus',
        'siap_kirim',
        'kodekelas',
        'namakelas',
        'flag_extra',
        'bor',
        'flag_persiapan_pulang',
        'tgl_pulang',
        'status_batal',
    ];

    public function bagian()
    {
        return $this->belongsTo(Bagian::class, 'bagian_id', 'bagian_id');
    }

    public function pasien()
    {
        return $this->belongsTo(Pasien::class, 'pasien_id_1', 'pasien_id');
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereNull('status_batal')->orWhere('status_batal', 0);
        });
    }

    public function scopeTerisi(Builder $query): Builder
    {
        return $query->aktif()->whereNotNull('pasien_id_1');
    }

    public function scopeKosong(Builder $query): Builder
    {
        return $query->aktif()->whereNull('pasien_id_1');
    }

    public function scopePersiapanPulang(Builder $query): Builder
    {
        return $query->aktif()->whereNotNull('pasien_id_1')->where('flag_persiapan_pulang', 1);
    }
}
