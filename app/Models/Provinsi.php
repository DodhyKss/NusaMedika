<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Provinsi extends Model
{
    protected $table = 'provinsi';

    protected $primaryKey = 'provinsi_id';

    public $timestamps = false;

    protected $fillable = [
        'nama_provinsi',
        'kode_wilayah_provinsi',
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
        return $this->hasMany(Kabupaten::class, 'provinsi_id', 'provinsi_id');
    }
}
