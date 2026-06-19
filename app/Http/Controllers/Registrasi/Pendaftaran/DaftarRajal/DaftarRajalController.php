<?php

namespace App\Http\Controllers\Registrasi\Pendaftaran\DaftarRajal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DaftarRajalController extends Controller
{
    public function index()
    {
        return view('moduls.registrasi.pendaftaran.daftar_rj.daftar_rajal');
    }
}
