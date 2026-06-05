<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubMenu extends Model
{
    protected $table = 'sub_menu';
    protected $primaryKey = 'sub_menu_id';
    public $timestamps = false;

    protected $fillable = [
        'menu_id',
        'nama_sub_menu',
        'file_sub_menu',
        'urutan_sub_menu',
        'status_batal'
    ];

    public function menu()
    {
        return $this->belongsTo(Menu::class, 'menu_id', 'menu_id');
    }

    public function akses()
    {
        return $this->hasMany(UserAkses::class, 'sub_menu_id', 'sub_menu_id');
    }
}
