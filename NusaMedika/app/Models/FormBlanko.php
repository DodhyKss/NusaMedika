<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Template Form Blanko (form generator, form 129) — tabel sendiri,
 * BUKAN model `form`/`objek`/`emr_detail`.
 */
class FormBlanko extends Model
{
    protected $table = 'form_blanko';

    protected $primaryKey = 'form_blanko_id';

    public $timestamps = false;

    protected $fillable = [
        'kode_blanko', 'nama_blanko', 'slug', 'deskripsi',
        'menu_induk', 'sub_induk', 'extra_induk',
        'kategori', 'departemen', 'is_aktif',
        'ukuran_halaman', 'orientasi_halaman',
        'tampilkan_identitas_pasien', 'tampilkan_logo', 'kop_surat',
        'filter_adaptif', 'versi', 'publish_time',
    ];

    protected $casts = [
        'filter_adaptif' => 'array',
        'is_aktif' => 'boolean',
        'versi' => 'integer',
        'publish_time' => 'datetime',
    ];

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereNull('status_batal')->orWhere('status_batal', 0);
        })->where('is_aktif', 1);
    }

    public function fields()
    {
        return $this->hasMany(FormBlankoField::class, 'form_blanko_id', 'form_blanko_id')
            ->where(function ($q) {
                $q->whereNull('status_batal')->orWhere('status_batal', 0);
            })
            ->orderBy('urutan');
    }

    public function pengisian()
    {
        return $this->hasMany(FormBlankoPengisian::class, 'form_blanko_id', 'form_blanko_id');
    }
}
