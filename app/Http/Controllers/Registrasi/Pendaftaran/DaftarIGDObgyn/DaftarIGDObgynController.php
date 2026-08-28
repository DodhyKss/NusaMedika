<?php

namespace App\Http\Controllers\Registrasi\Pendaftaran\DaftarIGDObgyn;

use App\Http\Controllers\Controller;

class DaftarIGDObgynController extends Controller
{
    public function index()
    {
        return view('moduls.registrasi.pendaftaran.daftar_igd_obgyn.registrasi_igd_obgyn');
    }
}
