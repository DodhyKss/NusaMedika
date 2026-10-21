<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kecamatan extends Model
{
    protected $table = 'kecamatan';

    protected $primaryKey = 'kecamatan_id';

    public $timestamps = false;

    protected $fillable = [
        'kabupaten_id',
        'nama_kecamatan',
        'kode_wilayah_kecamatan',
        'status_batal',
    ];

    public function scopeAktif($query)
    {
        return $query->where(function ($q) {
            $q->where('status_batal', '!=', 1)->orWhereNull('status_batal');
        });
    }

    public function kabupaten()
    {
        return $this->belongsTo(Kabupaten::class, 'kabupaten_id', 'kabupaten_id');
    }

    public function kelurahan()
    {
        return $this->hasMany(Kelurahan::class, 'kecamatan_id', 'kecamatan_id');
    }
}
