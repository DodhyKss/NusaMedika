<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegistrasiDetail extends Model
{
    protected $table = 'registrasi_detail';
    protected $primaryKey = 'registrasi_detail_id';
    public $timestamps = false;

    protected $fillable = [
        'registrasi_id',
        'bagian_id',
        'status_batal'
    ];

    public function bagian()
    {
        return $this->belongsTo(Bagian::class, 'bagian_id', 'bagian_id');
    }

    public function billTemp()
    {
        return $this->hasOne(BillTemp::class, 'registrasi_detail_id', 'registrasi_detail_id');
    }
}
