<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kabupaten extends Model
{
    protected $table = 'kabupaten';

    protected $primaryKey = 'kabupaten_id';

    public $timestamps = false;

    protected $fillable = [
        'provinsi_id',
        'nama_kabupaten',
        'kode_wilayah_kabupaten',
        'status_batal',
    ];

    public function scopeAktif($query)
    {
        return $query->where(function ($q) {
            $q->where('status_batal', '!=', 1)->orWhereNull('status_batal');
        });
    }

    public function provinsi()
    {
        return $this->belongsTo(Provinsi::class, 'provinsi_id', 'provinsi_id');
    }

    public function kecamatan()
    {
        return $this->hasMany(Kecamatan::class, 'kabupaten_id', 'kabupaten_id');
    }
}
