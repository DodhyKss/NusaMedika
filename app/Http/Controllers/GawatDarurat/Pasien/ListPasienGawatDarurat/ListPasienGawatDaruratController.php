<?php

namespace App\Http\Controllers\GawatDarurat\Pasien\ListPasienGawatDarurat;

use App\Http\Controllers\Controller;

class ListPasienGawatDaruratController extends Controller
{
    public function index()
    {
        return view('moduls.GawatDarurat.Pasien.ListPasienGawatDarurat.list_pasien_gawat_darurat');
    }
}
