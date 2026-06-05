<?php

namespace App\Http\Controllers\Pendaftaran;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ListPelayananController extends Controller
{
    public function index()
    {
        return view('moduls.pendaftaran.list_pelayanan_pasien');
    }
}
