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
        'ktp',
        'tempat_lahir',
        'tgl_lahir',
        'jenis_kelamin',
        'agama',
        'no_hp',
        'nama_ibu_kandung',
        'gol_darah',
        'status_perkawinan',
        'alamat',
        'kelurahan_id',
        'status_batal',
    ];
}
