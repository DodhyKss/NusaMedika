<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profesi extends Model
{
    protected $table = 'profesi';

    protected $primaryKey = 'profesi_id';

    public $timestamps = false;

    protected $fillable = [
        'nama_profesi',
        'ehr',
        'status_batal',
    ];

    public function pegawais()
    {
        return $this->hasMany(Pegawai::class, 'profesi_id', 'profesi_id');
    }
}
