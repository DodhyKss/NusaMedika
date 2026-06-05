<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $table = 'menu';
    protected $primaryKey = 'menu_id';
    public $timestamps = false;

    protected $fillable = [
        'modul_id',
        'nama_menu',
        'urutan_menu',
        'status_batal'
    ];

    public function modul()
    {
        return $this->belongsTo(Modul::class, 'modul_id', 'modul_id');
    }

    public function subMenus()
    {
        return $this->hasMany(SubMenu::class, 'menu_id', 'menu_id')->orderBy('urutan_sub_menu');
    }
}
