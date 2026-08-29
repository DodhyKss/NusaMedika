<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiagnosaRawat extends Model
{
    protected $table = 'diagnosa_rawat';

    protected $primaryKey = 'diagnosa_rawat_id';

    public $timestamps = false;

    protected $fillable = [
        'registrasi_id',
        'input_user_id',
        'input_time',
        'mod_user_id',
        'mod_time',
        'status_batal',
        'icd_id',
    ];

    public function icd()
    {
        // Pastikan model Icd sudah dibuat
        return $this->belongsTo(ICD::class, 'icd_id', 'icd_id');
    }
}
