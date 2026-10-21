<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KategoriTindakan extends Model
{
    protected $table = 'kategori_tindakan';

    protected $primaryKey = 'kategori_tindakan_id';

    public $timestamps = false;

    protected $fillable = [
        'nama_kategori_tindakan',
    ];

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereNull('status_batal')->orWhere('status_batal', 0);
        });
    }

    public function tindakan(): HasMany
    {
        return $this->hasMany(Tindakan::class, 'kategori_tindakan_id', 'kategori_tindakan_id');
    }
}
