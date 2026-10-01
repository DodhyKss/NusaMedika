<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class GroupTindakan extends Model
{
    protected $table = 'group_tindakan';

    protected $primaryKey = 'group_tindakan_id';

    public $timestamps = false;

    protected $fillable = [
        'nama_group_tindakan',
        'bagian_id',
    ];

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereNull('status_batal')->orWhere('status_batal', 0);
        });
    }

    public function bagian(): BelongsTo
    {
        return $this->belongsTo(Bagian::class, 'bagian_id', 'bagian_id');
    }

    public function tindakan(): BelongsToMany
    {
        return $this->belongsToMany(Tindakan::class, 'group_tindakan_tindakan', 'group_tindakan_id', 'tindakan_id')
            ->wherePivot('status_batal', '!=', 1)
            ->where(
                fn ($q) => $q->where('tindakan.status_batal', '!=', 1)->orWhereNull('tindakan.status_batal')
            );
    }
}
