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
            return view('dashboard.index', $this->statistik());
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

    /**
     * Statistik dashboard: jumlah RI aktif, RJ/IGD hari ini + data grafik.
     */
    private function statistik(): array
    {
        $rj = env('JENIS_RAWAT_RJ', 'RJ');
        $ri = env('JENIS_RAWAT_RI', 'RI');
        $igd = env('JENIS_RAWAT_IGD', 'IGD');

        $aktif = fn ($q) => $q->whereNull('status_batal')->orWhere('status_batal', 0);

        $jumlahRi = DB::table('registrasi')
            ->where('jenis_rawat', $ri)
            ->whereNull('tgl_keluar')
            ->where($aktif)
            ->count();

        $jumlahRj = DB::table('registrasi')
            ->where('jenis_rawat', $rj)
            ->whereDate('tgl_masuk', today())
            ->where($aktif)
            ->count();

        $jumlahIgd = DB::table('registrasi')
            ->where('jenis_rawat', $igd)
            ->whereDate('tgl_masuk', today())
            ->where($aktif)
            ->count();

        $totalHariIni = $jumlahRj + $jumlahIgd + DB::table('registrasi')
            ->where('jenis_rawat', $ri)
            ->whereDate('tgl_masuk', today())
            ->where($aktif)
            ->count();

        // Kunjungan 7 hari terakhir (termasuk hari ini), per jenis rawat.
        $mulai = today()->subDays(6);
        $harian = DB::table('registrasi')
            ->whereDate('tgl_masuk', '>=', $mulai)
            ->whereDate('tgl_masuk', '<=', today())
            ->where($aktif)
            ->groupBy('jenis_rawat', DB::raw('DATE(tgl_masuk)'))
            ->select(
                'jenis_rawat',
                DB::raw('DATE(tgl_masuk) as tanggal'),
                DB::raw('COUNT(*) as jumlah')
            )
            ->get();

        $labelHari = [];
        $dataRajal = [];
        $dataRanap = [];
        $dataIgd = [];
        for ($i = 6; $i >= 0; $i--) {
            $tanggal = today()->subDays($i);
            $key = $tanggal->toDateString();
            $labelHari[] = $tanggal->format('d/m');
            $dataRajal[] = (int) $harian->where('jenis_rawat', $rj)->where('tanggal', $key)->sum('jumlah');
            $dataRanap[] = (int) $harian->where('jenis_rawat', $ri)->where('tanggal', $key)->sum('jumlah');
            $dataIgd[] = (int) $harian->where('jenis_rawat', $igd)->where('tanggal', $key)->sum('jumlah');
        }

        // Top 5 bagian tujuan hari ini (RJ + IGD + RI baru masuk).
        $topBagian = DB::table('registrasi_detail as rd')
            ->join('registrasi as r', 'r.registrasi_id', '=', 'rd.registrasi_id')
            ->join('bagian as b', 'b.bagian_id', '=', 'rd.bagian_id')
            ->whereDate('r.tgl_masuk', today())
            ->where(function ($q) {
                $q->whereNull('r.status_batal')->orWhere('r.status_batal', 0);
            })
            ->where(function ($q) {
                $q->whereNull('rd.status_batal')->orWhere('rd.status_batal', 0);
            })
            ->where(function ($q) {
                $q->whereNull('b.status_batal')->orWhere('b.status_batal', 0);
            })
            ->groupBy('b.nama_bagian')
            ->select('b.nama_bagian', DB::raw('COUNT(DISTINCT r.registrasi_id) as jumlah'))
            ->orderByDesc('jumlah')
            ->orderBy('b.nama_bagian')
            ->limit(5)
            ->get();

        return [
            'jumlahRi' => $jumlahRi,
            'jumlahRj' => $jumlahRj,
            'jumlahIgd' => $jumlahIgd,
            'totalHariIni' => $totalHariIni,
            'labelHari' => $labelHari,
            'dataRajal' => $dataRajal,
            'dataRanap' => $dataRanap,
            'dataIgd' => $dataIgd,
            'topBagian' => $topBagian,
        ];
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
