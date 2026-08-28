<?php

namespace App\Http\Controllers\Registrasi\Pendaftaran\DaftarRanap;

use App\Http\Controllers\Controller;
use App\Models\KelasRuang;

class DaftarRanapController extends Controller
{
    public function index()
    {
        $kelasList = KelasRuang::aktif()->orderBy('kelas_ruang_id')->get();

        return view('moduls.Registrasi.Pendaftaran.DaftarRanap.DaftarRanap', compact('kelasList'));
    }
}
