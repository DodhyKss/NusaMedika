<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $table = 'users';

    protected $primaryKey = 'user_id';

    public $timestamps = false; // We have custom timestamps

    protected $fillable = [
        'user_name',
        'user_password',
        'nama_pegawai',
        'pegawai_id',
        'status_batal',
    ];

    protected $hidden = [
        'user_password',
    ];

    public function getAuthPassword()
    {
        return $this->user_password;
    }

    public function akses()
    {
        return $this->hasMany(UserAkses::class, 'user_id', 'user_id')
                    ->where(function ($q) {
                        $q->whereNull('status_batal')->orWhere('status_batal', 0);
                    });
    }

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'pegawai_id', 'pegawai_id');
    }
}
