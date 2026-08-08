<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jabatan extends Model
{
    protected $table = 'jabatan';

    protected $primaryKey = 'jabatan_id';

    public $timestamps = false;

    protected $fillable = [
        'nama_jabatan',
        'status_batal',
    ];

    public function pegawais()
    {
        return $this->hasMany(Pegawai::class, 'jabatan_id', 'jabatan_id');
    }
}
