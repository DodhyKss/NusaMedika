<?php

namespace App\Http\Controllers\EMR\PengkajianAwalKeperawatan;

use App\Http\Controllers\Controller;
use App\Models\Pasien;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;

class PengkajianAwalKeperawatanController extends Controller
{
    public function index($registrasi_detail_id, $emr_id = null)
    {
        $registrasi_detail = RegistrasiDetail::with('registrasi.pasien')->findOrFail($registrasi_detail_id);
        $riwayatPengkajianAwal = [];
        $emr_data = [];

        # Jika emr_id null artinya user input data baru
        if(empty($emr_id)){
            # Data Pasien
            $data_pasien = Pasien::whereNull('status_batal')->where('pasien_id', $registrasi_detail->registrasi->pasien_id)->first();
            
            $emr_data[env('OBJEK_ID_AGAMA')]['agama'] = $data_pasien->agama;
            $emr_data[env('OBJEK_ID_TINKAT_PENDIDIKAN')]['tingkat_pendidikan'] = $data_pasien->pendidikan;
        }else{
            # Jika emr_id ada artinya user mengedit data atau melihat data
        }

        return view('moduls.emr.pengkajian_awal_keperawatan.index', compact(
            'registrasi_detail',
            'riwayatPengkajianAwal',
            'emr_data'
        ));
    }
}
