<?php

namespace App\Http\Controllers\RawatJalan\Pasien\ListPasien;

use App\Http\Controllers\Controller;
use DB;
use Illuminate\Http\Request;

class ListPasienRajalController extends Controller
{
    public function index(Request $request)
    {
        $tanggalKunjungan = $request->input('tanggal_kunjungan', date('Y-m-d'));
        $poliklinikId = $request->input('poliklinik', '');

        $listPasien = collect([]);

        if (!empty($tanggalKunjungan) && !empty($poliklinikId)) {
            $listPasien = DB::table('registrasi as r')
                ->select(
                    'r.prioritas',
                    'rd.registrasi_detail_id',
                    'p.no_mr',
                    'p.tgl_lahir',
                    'p.nama_pasien',
                    'b.nama_bagian',
                    'n.nama_nasabah',
                    'bt.status_selesai',
                    'ru.urutan'
                )
                ->join('registrasi_detail as rd', 'rd.registrasi_id', '=', 'r.registrasi_id')
                ->join('pasien as p', 'p.pasien_id', '=', 'r.pasien_id')
                ->join('bagian as b', 'b.bagian_id', '=', 'rd.bagian_id')
                ->join('pasien_nasabah as pn', 'pn.pasien_nasabah_id', '=', 'r.pasien_nasabah_id')
                ->join('nasabah as n', 'n.nasabah_id', '=', 'pn.nasabah_id')
                ->join('bill_temp as bt', 'bt.registrasi_detail_id', '=', 'rd.registrasi_detail_id')
                ->join('registrasi_urut as ru', 'ru.registrasi_detail_id', '=', 'rd.registrasi_detail_id')
                ->join('penanggung_rawat as pr', 'pr.registrasi_id', '=', 'r.registrasi_id')
                ->whereNull('rd.status_batal')
                ->whereNull('r.status_batal')
                ->whereNull('pn.status_batal')
                ->whereNull('bt.status_batal')
                ->whereNull('ru.status_batal')
                ->whereNull('pr.status_batal')
                ->where('pr.rawat_user_id', auth()->user()->user_id)
                ->whereDate('r.tgl_masuk', $tanggalKunjungan)
                ->where('rd.bagian_id', $poliklinikId)
                ->where('r.jenis_rawat', env('JENIS_RAWAT_RJ'))
                ->orderBy('ru.urutan', 'asc')
                ->paginate(10)->withQueryString();

            if ($listPasien->isNotEmpty()) {
                $detailIds = $listPasien->pluck('registrasi_detail_id');

                $emrForms = DB::table('emr')
                    ->whereIn('registrasi_detail_id', $detailIds)
                    ->whereNull('status_batal')
                    ->whereIn('form_id', [env('FORM_ID_PENGKAJIAN_AWAL_KEPERAWATAN'), env('FORM_ID_PENGKAJIAN_HARIAN_KEPERAWATAN'), env('FORM_ID_SOAP')])
                    ->select('registrasi_detail_id', 'form_id')
                    ->get()
                    ->groupBy('registrasi_detail_id');

                $listPasien->getCollection()->transform(function ($item) use ($emrForms) {
                    $item->emr_forms = $emrForms->get($item->registrasi_detail_id, collect([]))->pluck('form_id')->toArray();
                    return $item;
                });
            }
        }

        return view('moduls.rawat_jalan.pasien.list_pasien.list_pasien_dokter', compact(
            'listPasien',
            'tanggalKunjungan',
            'poliklinikId'
        ));
    }
}
