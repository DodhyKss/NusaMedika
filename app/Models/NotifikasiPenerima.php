<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class NotifikasiPenerima extends Model
{
    protected $table = 'notifikasi_penerima';

    protected $primaryKey = 'notifikasi_penerima_id';

    public $timestamps = false;

    protected $fillable = [
        'notifikasi_id',
        'user_id',
        'is_read',
        'dibaca_time',
        'input_time',
        'input_user_id',
        'mod_time',
        'mod_user_id',
        'status_batal',
    ];

    public function notifikasi()
    {
        return $this->belongsTo(Notifikasi::class, 'notifikasi_id', 'notifikasi_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereNull('status_batal')->orWhere('status_batal', 0);
        });
    }

    /** Notifikasi yang masih harus ditutup oleh user ini. */
    public function scopeBelumDibaca(Builder $query): Builder
    {
        return $query->aktif()->where('is_read', 0);
    }
}
