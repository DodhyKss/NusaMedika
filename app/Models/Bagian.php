<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bagian extends Model
{
    protected $table = 'bagian';
    protected $primaryKey = 'bagian_id';
    public $timestamps = false;

    protected $fillable = [
        'nama_bagian',
        'referensi_bagian'
    ];
}
