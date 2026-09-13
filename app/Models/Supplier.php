<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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

    public function barangs(): BelongsToMany
    {
        return $this->belongsToMany(Barang::class, 'barang_supplier', 'supplier_id', 'barang_id')
            ->wherePivot('status_batal', '!=', 1)
            ->withPivot('barang_supplier_id')
            ->where(
                fn ($q) => $q->where('barang.status_batal', '!=', 1)->orWhereNull('barang.status_batal')
            );
    }

    public function distributors(): BelongsToMany
    {
        return $this->belongsToMany(Supplier::class, 'supplier_distributor', 'supplier_id', 'distributor_id')
            ->wherePivot('status_batal', '!=', 1)
            ->where('jenis_supplier', 'DISTRIBUTOR')
            ->where(
                fn ($q) => $q->where('supplier.status_batal', '!=', 1)->orWhereNull('supplier.status_batal')
            );
    }

    public function parentSupplier(): BelongsToMany
    {
        return $this->belongsToMany(Supplier::class, 'supplier_distributor', 'distributor_id', 'supplier_id')
            ->wherePivot('status_batal', '!=', 1)
            ->where('jenis_supplier', 'SUPPLIER')
            ->where(
                fn ($q) => $q->where('supplier.status_batal', '!=', 1)->orWhereNull('supplier.status_batal')
            );
    }
}
