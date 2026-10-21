<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Percakapan extends Model
{
    protected $table = 'percakapan';

    protected $primaryKey = 'percakapan_id';

    public $timestamps = false;

    protected $fillable = [
        'user_a_id',
        'user_b_id',
        'pesan_terakhir',
        'pesan_terakhir_at',
        'input_time',
        'input_user_id',
        'mod_time',
        'mod_user_id',
        'status_batal',
    ];

    public function userA()
    {
        return $this->belongsTo(User::class, 'user_a_id', 'user_id');
    }

    public function userB()
    {
        return $this->belongsTo(User::class, 'user_b_id', 'user_id');
    }

    public function pesan()
    {
        return $this->hasMany(Pesan::class, 'percakapan_id', 'percakapan_id');
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereNull('status_batal')->orWhere('status_batal', 0);
        });
    }

    /**
     * Percakapan yang miliknya user tersebut (salah satu dari user_a_id / user_b_id).
     */
    public function scopeMilikUser(Builder $query, int $userId): Builder
    {
        return $query->aktif()->where(function ($q) use ($userId) {
            $q->where('user_a_id', $userId)->orWhere('user_b_id', $userId);
        });
    }

    /** User lawan bicara untuk user tertentu. */
    public function lawan(int $userId): ?User
    {
        $lawanId = (int) $this->user_a_id === $userId ? $this->user_b_id : $this->user_a_id;

        return User::where('user_id', $lawanId)->first();
    }

    /** Jumlah pesan yang belum dibaca user tersebut dalam percakapan ini. */
    public function belumDibaca(int $userId): int
    {
        return Pesan::aktif()
            ->where('percakapan_id', $this->percakapan_id)
            ->where('pengirim_id', '!=', $userId)
            ->whereNull('dibaca_at')
            ->count();
    }

    /**
     * Cari percakapan antara dua user, atau null bila belum pernah ada.
     * Pasangan dinormalisasi (a < b) agar sesuai unique index.
     */
    public static function cariAntara(int $userA, int $userB): ?self
    {
        [$a, $b] = $userA < $userB ? [$userA, $userB] : [$userB, $userA];

        return static::aktif()
            ->where('user_a_id', $a)
            ->where('user_b_id', $b)
            ->first();
    }
}
