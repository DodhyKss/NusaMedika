<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Modul extends Model
{
    protected $table = 'modul';

    protected $primaryKey = 'modul_id';

    public $timestamps = false;

    protected $fillable = [
        'nama_modul',
        'icon_modul',
        'urutan_modul',
        'status_batal',
    ];

    public function menus()
    {
        return $this->hasMany(Menu::class, 'modul_id', 'modul_id')->orderBy('urutan_menu');
    }
}
