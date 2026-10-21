<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Pesan extends Model
{
    protected $table = 'pesan';

    protected $primaryKey = 'pesan_id';

    public $timestamps = false;

    protected $fillable = [
        'percakapan_id',
        'pengirim_id',
        'isi',
        'dibaca_at',
        'input_time',
        'input_user_id',
        'mod_time',
        'mod_user_id',
        'status_batal',
    ];

    public function percakapan()
    {
        return $this->belongsTo(Percakapan::class, 'percakapan_id', 'percakapan_id');
    }

    public function pengirim()
    {
        return $this->belongsTo(User::class, 'pengirim_id', 'user_id');
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereNull('status_batal')->orWhere('status_batal', 0);
        });
    }

    public function scopeBelumDibaca(Builder $query): Builder
    {
        return $query->aktif()->whereNull('dibaca_at');
    }
}
