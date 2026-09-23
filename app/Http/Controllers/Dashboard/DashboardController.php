<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $isDokter = $user && $user->pegawai
            && (int) $user->pegawai->profesi_id === (int) env('PROFESI_ID_DOKTER', 1);

        if (! $isDokter) {
            return view('dashboard.index');
        }

        return view('dashboard.dokter');
    }

    public function pasien(string $jenis)
    {
        abort_unless(
            Auth::user()->pegawai
            && (int) Auth::user()->pegawai->profesi_id === (int) env('PROFESI_ID_DOKTER', 1),
            403
        );

        $config = [
            'rajal' => ['jenis' => env('JENIS_RAWAT_RJ', 'RJ'), 'mode' => 'rajal'],
            'ranap' => ['jenis' => env('JENIS_RAWAT_RI', 'RI'), 'mode' => 'ranap'],
            'igd' => ['jenis' => env('JENIS_RAWAT_IGD', 'IGD'), 'mode' => 'igd'],
        ];

        if (! isset($config[$jenis])) {
            abort(404);
        }

        $rows = $this->pasienAktif($config[$jenis]['jenis'], $config[$jenis]['mode']);

        return view('dashboard._pasien_rows', ['rows' => $rows]);
    }

    private function pasienAktif(string $jenis, string $mode)
    {
        $query = DB::table('registrasi as r')
            ->join('registrasi_detail as rd', 'rd.registrasi_id', '=', 'r.registrasi_id')
            ->join('pasien as p', 'p.pasien_id', '=', 'r.pasien_id')
            ->join('bagian as b', 'b.bagian_id', '=', 'rd.bagian_id')
            ->where('r.jenis_rawat', $jenis)
            ->whereNull('rd.check_out')
            ->where(function ($q) {
                $q->whereNull('r.status_batal')->orWhere('r.status_batal', 0);
            })
            ->where(function ($q) {
                $q->whereNull('rd.status_batal')->orWhere('rd.status_batal', 0);
            })
            ->where(function ($q) {
                $q->whereNull('p.status_batal')->orWhere('p.status_batal', 0);
            })
            ->where(function ($q) {
                $q->whereNull('b.status_batal')->orWhere('b.status_batal', 0);
            })
            ->select(
                'p.pasien_id',
                'p.no_mr',
                'p.nama_pasien',
                'p.tgl_lahir',
                'p.jenis_kelamin',
                'rd.registrasi_detail_id',
                'b.nama_bagian',
                'r.tgl_masuk'
            );

        if ($mode === 'rajal') {
            $query->whereDate('r.tgl_masuk', today());
        } else {
            $query->whereNull('r.tgl_keluar');
        }

        return $query->orderByDesc('r.tgl_masuk')->get();
    }
}
