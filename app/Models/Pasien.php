<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pasien extends Model
{
    protected $table = 'pasien';
    protected $primaryKey = 'pasien_id';
    public $timestamps = false;

    protected $fillable = [
        'nama_pasien',
        'no_mr',
        'tgl_lahir'
    ];
}
