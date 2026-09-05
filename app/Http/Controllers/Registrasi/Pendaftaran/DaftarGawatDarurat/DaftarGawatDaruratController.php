<?php

namespace App\Http\Controllers\Registrasi\Pendaftaran\DaftarGawatDarurat;

use App\Http\Controllers\Controller;

class DaftarGawatDaruratController extends Controller
{
    public function index()
    {
        return view('moduls.Registrasi.Pendaftaran.DaftarGawatDarurat.daftar_gawat_darurat');
    }
}
