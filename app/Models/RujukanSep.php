<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RujukanSep extends Model
{
    protected $table = 'rujukan_sep';
    protected $primaryKey = 'rujukan_sep_id';
    public $timestamps = false;

    protected $fillable = [
        'registrasi_id',
        'sep',
        'no_rujukan',
        'status_batal'
    ];
}
