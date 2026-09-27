<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ForumBalasan extends Model
{
    protected $table = 'forum_balasan';

    protected $primaryKey = 'balasan_id';

    public $timestamps = false;

    protected $fillable = [
        'topik_id',
        'user_id',
        'isi',
        'input_time',
        'input_user_id',
        'mod_time',
        'mod_user_id',
        'status_batal',
    ];

    public function topik()
    {
        return $this->belongsTo(ForumTopik::class, 'topik_id', 'topik_id');
    }

    public function penulis()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereNull('status_batal')->orWhere('status_batal', 0);
        });
    }
}
