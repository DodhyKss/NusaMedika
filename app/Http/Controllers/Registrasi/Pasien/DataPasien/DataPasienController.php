<?php

namespace App\Http\Controllers\Registrasi\Pasien\DataPasien;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DataPasienController extends Controller
{
    public function index() 
    {
        return view('moduls.registrasi.pasien.data_pasien.daftar_pasien');
    }
}
