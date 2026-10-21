<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kelurahan extends Model
{
    protected $table = 'kelurahan';

    protected $primaryKey = 'kelurahan_id';

    public $timestamps = false;

    protected $fillable = [
        'kecamatan_id',
        'nama_kelurahan',
        'kode_wilayah_kelurahan',
        'status_batal',
    ];

    public function scopeAktif($query)
    {
        return $query->where(function ($q) {
            $q->where('status_batal', '!=', 1)->orWhereNull('status_batal');
        });
    }

    public function kecamatan()
    {
        return $this->belongsTo(Kecamatan::class, 'kecamatan_id', 'kecamatan_id');
    }
}
