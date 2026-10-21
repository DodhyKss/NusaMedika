<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAkses extends Model
{
    protected $table = 'user_akses';

    protected $primaryKey = 'user_akses_id';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'sub_menu_id',
        'status_batal',
    ];

    public function subMenu()
    {
        return $this->belongsTo(SubMenu::class, 'sub_menu_id', 'sub_menu_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
