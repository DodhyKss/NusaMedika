<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bagian extends Model
{
    protected $table = 'bagian';

    protected $primaryKey = 'bagian_id';

    public $timestamps = false;

    protected $fillable = [
        'nama_bagian',
        'referensi_bagian_id',
        'group_bagian',
        'seri_bagian',
        'id_satu_sehat',
        'flag_eksekutif',
        'id_location',
        'status_batal',
    ];

    public function referensi()
    {
        return $this->belongsTo(ReferensiBagian::class, 'referensi_bagian_id', 'referensi_bagian_id_id');
    }

    public function pegawais()
    {
        return $this->hasMany(Pegawai::class, 'bagian_id', 'bagian_id');
    }
}
