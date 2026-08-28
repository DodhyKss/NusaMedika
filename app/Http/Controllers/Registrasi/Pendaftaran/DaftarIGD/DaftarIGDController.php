<?php

namespace App\Http\Controllers\Registrasi\Pendaftaran\DaftarIGD;

use App\Http\Controllers\Controller;

class DaftarIGDController extends Controller
{
    public function index()
    {
        return view('moduls.registrasi.pendaftaran.daftar_igd.registrasi_igd');
    }
}
