<?php

namespace App\Http\Controllers\Registrasi\Pendaftaran\DaftarRanap;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DaftarRanapController extends Controller
{
    public function index()
    {
        return view('moduls.registrasi.pendaftaran.daftar_ri.daftar_ranap');
    }
}
