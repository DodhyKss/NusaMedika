<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Pegawai extends Model
{
    protected $table = 'pegawai';

    protected $primaryKey = 'pegawai_id';

    public $timestamps = false;

    protected $fillable = [
        'nama_pegawai',
        'nip',
        'bagian_id',
        'profesi_id',
        'jabatan_id',
        'status_kepegawaian_id',
        'sip',
        'tgl_awal_sip',
        'tgl_akhir_sip',
        'str',
        'tgl_awal_str',
        'tgl_akhir_str',
        'nik',
        'no_rfid',
        'id_satu_sehat',
        'ttd',
        'inacbg_id',
        'sub_id',
        'karu_id',
        'katim_id',
        'foto',
        'status_batal',
    ];

    public function bagian()
    {
        return $this->belongsTo(Bagian::class, 'bagian_id', 'bagian_id');
    }

    public function profesi()
    {
        return $this->belongsTo(Profesi::class, 'profesi_id', 'profesi_id');
    }

    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class, 'jabatan_id', 'jabatan_id');
    }

    public function statusKepegawaian()
    {
        return $this->belongsTo(StatusKepegawaian::class, 'status_kepegawaian_id', 'status_kepegawaian_id');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'pegawai_id', 'pegawai_id');
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereNull('status_batal')->orWhere('status_batal', 0);
        });
    }
}
