<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $table = 'supplier';

    protected $primaryKey = 'supplier_id';

    public $timestamps = false;

    protected $fillable = [
        'nama_supplier',
        'jenis_supplier',
        'alamat',
        'telepon',
        'email',
        'npwp',
    ];

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereNull('status_batal')->orWhere('status_batal', 0);
        });
    }

    public function getJenisLabelAttribute(): string
    {
        return $this->jenis_supplier === 'DISTRIBUTOR' ? 'Distributor' : 'Supplier';
    }
}
