<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReferensiBagian extends Model
{
    protected $table = 'referensi_bagian';

    protected $primaryKey = 'referensi_bagian_id';

    public $timestamps = false;

    protected $fillable = [
        'nama_referensi_bagian',
        'status_batal',
    ];

    public function bagians()
    {
        return $this->hasMany(Bagian::class, 'referensi_bagian_id', 'referensi_bagian_id');
    }
}
