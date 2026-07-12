<?php

namespace App\Http\Controllers\RawatInap\Pasien\ListPasien;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ListPasienRanapController extends Controller
{
    public function index(Request $request)
    {
        $ruanganId = $request->input('ruangan', '');
        $filter = $request->input('filter', false);

        $listPasien = collect([]);

        if ($filter) {
            $subQueryDpjp = DB::table('penanggung_rawat as pr')
                ->select(
                    'r.registrasi_id',
                    'r.pasien_id',
                    'r.tgl_masuk',
                    DB::raw("CONCAT(bd.no_kamar, ' - ', bd.nama_bed) as no_bed"),
                    'bd.no_kamar',
                    'bd.nama_bed',
                    'bd.namakelas',
                    'b.nama_bagian',
                    'bd.bagian_id',
                    'p.no_mr',
                    'p.nama_pasien',
                    'p.jenis_kelamin',
                    'p.tgl_lahir',
                    'n.nama_nasabah',
                    DB::raw("date_trunc('second', age(current_timestamp, r.tgl_masuk)) as los"),
                    DB::raw("case when age(current_timestamp, r.tgl_masuk) <= interval '3 days' then 'LOS <=3' when age(current_timestamp, r.tgl_masuk) <= interval '5 days' then 'LOS >3 & <=5' when age(current_timestamp, r.tgl_masuk) <= interval '7 days' then 'LOS >5 & <=7' when age(current_timestamp, r.tgl_masuk) <= interval '10 days' then 'LOS >7 & <=10' else 'LOS >10' end as kapasitas"),
                    DB::raw("'DPJP' as status_perawatan")
                )
                ->join('registrasi as r', 'pr.registrasi_id', '=', 'r.registrasi_id')
                ->join('bed as bd', 'r.pasien_id', '=', 'bd.pasien_id_1')
                ->join('bagian as b', 'bd.bagian_id', '=', 'b.bagian_id')
                ->join('pasien as p', 'r.pasien_id', '=', 'p.pasien_id')
                ->join('pasien_nasabah as pn', 'r.pasien_nasabah_id', '=', 'pn.pasien_nasabah_id')
                ->join('nasabah as n', 'pn.nasabah_id', '=', 'n.nasabah_id')
                ->where('pr.rawat_user_id', auth()->user()->user_id)
                ->whereNull('pr.status_batal')
                ->where('r.jenis_rawat', env('JENIS_RAWAT_RI', 'RI'))
                ->where('b.referensi_bagian', env('REF_BAGIAN_RANAP', 31))
                ->whereNull('r.tgl_keluar')
                ->whereNull('r.status_batal')
                ->when(!empty($ruanganId), function ($query) use ($ruanganId) {
                    $query->where('bd.bagian_id', $ruanganId);
                })
                ->groupBy(
                    'r.registrasi_id',
                    'r.pasien_id',
                    'r.tgl_masuk',
                    'bd.no_kamar',
                    'bd.nama_bed',
                    'bd.namakelas',
                    'bd.bagian_id',
                    'b.nama_bagian',
                    'p.no_mr',
                    'p.nama_pasien',
                    'p.jenis_kelamin',
                    'p.tgl_lahir',
                    'n.nama_nasabah'
                );

            $subQueryKonsul = DB::table('emr as e')
                ->select(
                    'r.registrasi_id',
                    'r.pasien_id',
                    'r.tgl_masuk',
                    DB::raw("CONCAT(bd.no_kamar, ' - ', bd.nama_bed) as no_bed"),
                    'bd.no_kamar',
                    'bd.nama_bed',
                    'bd.namakelas',
                    'b.nama_bagian',
                    'bd.bagian_id',
                    'p.no_mr',
                    'p.nama_pasien',
                    'p.jenis_kelamin',
                    'p.tgl_lahir',
                    'n.nama_nasabah',
                    DB::raw("date_trunc('second', age(current_timestamp, r.tgl_masuk)) as los"),
                    DB::raw("case when age(current_timestamp, r.tgl_masuk) <= interval '3 days' then 'LOS <=3' when age(current_timestamp, r.tgl_masuk) <= interval '5 days' then 'LOS >3 & <=5' when age(current_timestamp, r.tgl_masuk) <= interval '7 days' then 'LOS >5 & <=7' when age(current_timestamp, r.tgl_masuk) <= interval '10 days' then 'LOS >7 & <=10' else 'LOS >10' end as kapasitas"),
                    DB::raw("COALESCE(ed_jk.value, 'Konsul') as status_perawatan")
                )
                ->join('registrasi as r', 'e.registrasi_id', '=', 'r.registrasi_id')
                ->join('bed as bd', 'r.pasien_id', '=', 'bd.pasien_id_1')
                ->join('bagian as b', 'bd.bagian_id', '=', 'b.bagian_id')
                ->join('pasien as p', 'r.pasien_id', '=', 'p.pasien_id')
                ->join('emr_detail as ed', 'e.emr_id', '=', 'ed.emr_id')
                ->leftJoin('emr_detail as ed_jk', function ($join) {
                    $join->on('e.emr_id', '=', 'ed_jk.emr_id')
                         ->where('ed_jk.objek_id', env('OBJEK_ID_JENIS_KONSULTASI', 151))
                         ->whereNull('ed_jk.status_batal');
                })
                ->join('pasien_nasabah as pn', 'r.pasien_nasabah_id', '=', 'pn.pasien_nasabah_id')
                ->join('nasabah as n', 'pn.nasabah_id', '=', 'n.nasabah_id')
                ->where('e.form_id', env('FORM_ID_KONSULTASI', 26))
                ->whereNull('e.status_batal')
                ->where('r.jenis_rawat', env('JENIS_RAWAT_RI', 'RI'))
                ->where('b.referensi_bagian', env('REF_BAGIAN_RANAP', 31))
                ->whereNull('r.tgl_keluar')
                ->whereNull('r.status_batal')
                ->whereNull('ed.status_batal')
                ->where('ed.value', (string) auth()->user()->user_id)
                ->where('ed.objek_id', env('OBJEK_ID_DOKTER_PENERIMA_KONSUL', 303))
                ->whereNotIn('r.pasien_id', function ($query) {
                    $query->select('r2.pasien_id')
                          ->from('penanggung_rawat as pr2')
                          ->join('registrasi as r2', 'pr2.registrasi_id', '=', 'r2.registrasi_id')
                          ->where('pr2.rawat_user_id', auth()->user()->user_id)
                          ->whereNull('pr2.status_batal')
                          ->whereNull('r2.tgl_keluar')
                          ->whereNull('r2.status_batal');
                })
                ->when(!empty($ruanganId), function ($query) use ($ruanganId) {
                    $query->where('bd.bagian_id', $ruanganId);
                })
                ->groupBy(
                    'r.registrasi_id',
                    'r.pasien_id',
                    'r.tgl_masuk',
                    'bd.no_kamar',
                    'bd.nama_bed',
                    'bd.namakelas',
                    'bd.bagian_id',
                    'b.nama_bagian',
                    'p.no_mr',
                    'p.nama_pasien',
                    'p.jenis_kelamin',
                    'p.tgl_lahir',
                    'n.nama_nasabah',
                    'ed_jk.value'
                );

            $subQuery = $subQueryDpjp->unionAll($subQueryKonsul);

            $listPasien = DB::query()
                ->fromSub($subQuery, 'x')
                ->select(
                    'x.*',
                    DB::raw("(SELECT MAX(rd.registrasi_detail_id) FROM registrasi_detail rd WHERE x.registrasi_id = rd.registrasi_id AND x.bagian_id = rd.bagian_id AND rd.status_batal IS NULL) as registrasi_detail_id")
                )
                ->orderBy('x.nama_bagian')
                ->orderBy('x.nama_pasien')
                ->paginate(10)
                ->withQueryString();
        }
        return view('moduls.rawat_inap.pasien.list_pasien.list_pasien_ranap', compact('ruanganId', 'listPasien'));
    }
}
