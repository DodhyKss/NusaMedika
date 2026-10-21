<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class StatusSosial extends Model
{
    protected $table = 'status_sosial';

    protected $primaryKey = 'status_sosial_id';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'isi',
        'tanggal_kadaraluarsa',
        'input_time',
        'input_user_id',
        'mod_time',
        'mod_user_id',
        'status_batal',
    ];

    /** Opsi masa berlaku (jam). NULL = tidak kedaluwarsa. */
    public const MASA_BERLAKU = [
        1 => '1 Jam',
        6 => '6 Jam',
        24 => '24 Jam',
        168 => '7 Hari',
    ];

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

    /**
     * Status yang masih bisa dilihat: belum kadaluarsa.
     * Status tanpa tanggal kadaluarsa selalu tampil.
     */
    public function scopeBelumKedaluwarsa(Builder $query): Builder
    {
        return $query->aktif()->where(function ($q) {
            $q->whereNull('tanggal_kadaraluarsa')
                ->orWhere('tanggal_kadaraluarsa', '>', now());
        });
    }

    public function sudahKedaluwarsa(): bool
    {
        return $this->tanggal_kadaraluarsa !== null
            && Carbon::parse($this->tanggal_kadaraluarsa)->isPast();
    }
}
