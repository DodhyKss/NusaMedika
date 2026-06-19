<?php

namespace App\Http\Controllers\GawatDarurat\Pasien\ListPasienIGD;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ListPasienIGDController extends Controller
{
    public function index()
    {
        return view('moduls.gawat_darurat.pasien.list_pasien_igd.list_pasien_igd');
    }
}
