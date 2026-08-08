<?php

namespace App\Http\Controllers\Registrasi\Pasien\NasabahPasien;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NasabahPasienController extends Controller
{
    public function index() 
    {
        return view('moduls.registrasi.pasien.data_nasabah_pasien.nasabah_pasien');
    }
}
