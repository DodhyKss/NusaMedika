<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegistrasiUrut extends Model
{
    protected $table = 'registrasi_urut';
    protected $primaryKey = 'registrasi_urut_id';
    public $timestamps = false;

    protected $fillable = [
        'registrasi_urut_id',
        'registrasi_detail_id',
        'pegawai_id',
        'bagian_id',
        'urutan',
        'tgl_urut',
        'status_check_in',
        'status_panggil',
        'tgl_panggil',
        'urutan_ttv',
        'urutan_check_in',
        'tgl_check_in',
        'status_check_in_rs',
        'tgl_check_in_rs',
        'tgl_jam_wa_konfirmasi',
        'flag_konfirmasi',
        'tgl_jam_konfirmasi',
        'tgl_jam_checkin',
        'status_antrian',
        'input_time',
        'input_user_id',
        'mod_time',
        'mod_user_id',
        'status_batal'
    ];
}
