<?php

namespace App\Http\Controllers\Pendaftaran;

use App\Http\Controllers\Controller;
use App\Models\Registrasi;
use Illuminate\Http\Request;

class ListPelayananController extends Controller
{
    public function index(Request $request)
    {
        // Default filter tanggal adalah hari ini agar load data tidak berat
        $tanggalAwal = $request->input('tanggal_awal', date('Y-m-d'));
        $tanggalAkhir = $request->input('tanggal_akhir', date('Y-m-d'));
        $jenisLayanan = $request->input('jenis_layanan');

        $query = Registrasi::where(function($q) {
            $q->whereNull('status_batal')
              ->orWhere('status_batal', '!=', 1);
        });

        // Filter Tanggal
        if ($tanggalAwal) {
            $query->whereDate('tgl_masuk', '>=', $tanggalAwal);
        }
        if ($tanggalAkhir) {
            $query->whereDate('tgl_masuk', '<=', $tanggalAkhir);
        }

        // Filter Jenis Layanan (IGD, RI, RJ)
        if ($jenisLayanan) {
            $query->where('jenis_rawat', $jenisLayanan);
        }

        $kunjungan = $query->orderBy('tgl_masuk', 'desc')->paginate(10)->withQueryString();

        return view('moduls.pendaftaran.list_pelayanan_pasien', compact(
            'kunjungan', 'tanggalAwal', 'tanggalAkhir', 'jenisLayanan'
        ));
    }
}
