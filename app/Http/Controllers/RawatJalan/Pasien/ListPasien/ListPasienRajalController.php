<?php

namespace App\Http\Controllers\RawatJalan\Pasien\ListPasien;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ListPasienRajalController extends Controller
{
    public function index()
    {
        return view('moduls.rawat_jalan.pasien.list_pasien.list_pasien_dokter');
    }
}
