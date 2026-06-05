<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Registrasi extends Model
{
    protected $table = 'registrasi';

    protected $primaryKey = 'registrasi_id';
    public $timestamps = false;

    protected $fillable = [
        'registrasi_id'
    ];
}
