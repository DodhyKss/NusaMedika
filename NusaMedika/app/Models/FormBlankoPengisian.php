<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Satu kali pengisian satu template Form Blanko (form 129).
 * Nilai seluruh field tersimpan di kolom JSON `data`.
 */
class FormBlankoPengisian extends Model
{
    protected $table = 'form_blanko_pengisian';

    protected $primaryKey = 'form_blanko_pengisian_id';

    public $timestamps = false;

    protected $fillable = [
        'form_blanko_id', 'registrasi_detail_id', 'data', 'ringkasan', 'jumlah_terisi',
    ];

    protected $casts = [
        'data' => 'array',
        'ringkasan' => 'array',
        'jumlah_terisi' => 'integer',
    ];

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereNull('status_batal')->orWhere('status_batal', 0);
        });
    }

    public function blanko()
    {
        return $this->belongsTo(FormBlanko::class, 'form_blanko_id', 'form_blanko_id');
    }
}
