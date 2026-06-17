<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasienNasabah extends Model
{
    protected $table = 'pasien_nasabah';
    protected $primaryKey = 'pasien_nasabah_id';
    public $timestamps = false;

    protected $fillable = [
        'pasien_id',
        'nasabah_id',
        'no_peserta',
        'status_batal'
    ];

    public function nasabah()
    {
        return $this->belongsTo(Nasabah::class, 'nasabah_id', 'nasabah_id');
    }
}
