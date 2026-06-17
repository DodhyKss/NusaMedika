<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PenanggungRawat extends Model
{
    protected $table = 'penanggung_rawat';
    protected $primaryKey = 'penanggung_rawat_id';
    public $timestamps = false;

    protected $fillable = [
        'registrasi_id',
        'rawat_user_id',
        'status_batal'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'rawat_user_id', 'user_id');
    }
}
