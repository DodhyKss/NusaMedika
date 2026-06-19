<?php

namespace App\Http\Controllers\RawatInap\Pasien\ListPasien;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ListPasienRanapController extends Controller
{
    public function index()
    {
        return view('moduls.rawat_inap.pasien.list_pasien.list_pasien_ranap');
    }
}
