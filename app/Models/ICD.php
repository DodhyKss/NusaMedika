<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ICD extends Model
{
    protected $table = 'icd';
    protected $primaryKey = 'icd_id';
    public $timestamps = false;

    protected $fillable = [
        'kode_diagnosa',
        'nama_diagnosa',
        'kategori',
        'status_batal'
    ];
}