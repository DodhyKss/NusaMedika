<?php

namespace App\Http\Controllers\GawatDarurat\Pasien\ListPasienIGD;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ListPasienIGDController extends Controller
{
    public function index()
    {
        return view('moduls.GawatDarurat.Pasien.ListPasienIGD.ListPasienIGD');
    }
}
