<?php

namespace App\Http\Controllers\Registrasi\Pendaftaran\DaftarIGD;

use App\Http\Controllers\Controller;

class DaftarIGDController extends Controller
{
    public function index()
    {
        return view('moduls.Registrasi.Pendaftaran.DaftarIGD.DaftarIGD');
    }
}
