<?php

namespace App\Http\Controllers\EMR\PengkajianAwalKeperawatan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PengkajianAwalKeperawatanController extends Controller
{
    public function index($registrasi_detail_id, $emr_id = null)
    {
        $tes = "Halo";

        return view('moduls.emr.pengkajian_awal_keperawatan.index', compact('tes'));
    }
}
