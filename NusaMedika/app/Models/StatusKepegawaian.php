<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatusKepegawaian extends Model
{
    protected $table = 'status_kepegawaian';

    protected $primaryKey = 'status_kepegawaian_id';

    public $timestamps = false;

    protected $fillable = [
        'nama_status_kepegawaian',
        'status_batal',
    ];

    public function pegawais()
    {
        return $this->hasMany(Pegawai::class, 'status_kepegawaian_id', 'status_kepegawaian_id');
    }
}
