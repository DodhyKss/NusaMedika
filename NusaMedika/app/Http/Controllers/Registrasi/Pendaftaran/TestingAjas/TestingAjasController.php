<?php

namespace App\Http\Controllers\Registrasi\Pendaftaran\TestingAjas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TestingAjasController extends Controller
{
    public function index(Request $request)
    {
        return view('moduls.Registrasi.Pendaftaran.TestingAjas.index');
    }
}
