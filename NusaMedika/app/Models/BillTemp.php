<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillTemp extends Model
{
    protected $table = 'bill_temp';

    protected $primaryKey = 'bill_temp_id';

    public $timestamps = false;

    protected $fillable = [
        'registrasi_detail_id',
        'status_selesai',
    ];
}
