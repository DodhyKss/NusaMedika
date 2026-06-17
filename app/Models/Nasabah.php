<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Nasabah extends Model
{
    protected $table = 'nasabah';
    protected $primaryKey = 'nasabah_id';
    public $timestamps = false;

    protected $fillable = [
        'nama_nasabah'
    ];
}
