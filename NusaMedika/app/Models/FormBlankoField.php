<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Field satu template Form Blanko (form 129).
 * `opsi`/`validasi`/`setelan` berupa JSON (cast array).
 */
class FormBlankoField extends Model
{
    protected $table = 'form_blanko_field';

    protected $primaryKey = 'form_blanko_field_id';

    public $timestamps = false;

    protected $fillable = [
        'form_blanko_id', 'kode_field', 'label', 'keterangan',
        'tipe_kontrol', 'urutan', 'grup', 'wajib', 'lebar_kolom',
        'depends_on_field', 'depends_on_value',
        'opsi', 'validasi', 'setelan', 'objek_id',
    ];

    protected $casts = [
        'opsi' => 'array',
        'validasi' => 'array',
        'setelan' => 'array',
        'wajib' => 'boolean',
    ];
}
