<?php

namespace App\Http\Controllers\PenunjangMedis\RehabilitasiMedik\DaftarPasienRehabilitasiMedik;

use App\Helpers\EmrHelper;
use App\Http\Controllers\Controller;
use App\Models\Bagian;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Daftar Pasien Rehabilitasi Medik (Penunjang Medis → Rehabilitasi Medik).
 *
 * Pasien tampil di sini lewat konsultasi jenis Rehabilitasi Medik yang membuat
 * registrasi_detail baru dengan bagian_id = bagian rehab (bukan berbasis order
 * seperti Lab/Rad). Header registrasi tetap sama — pasien masih terdaftar RJ.
 */
class DaftarPasienRehabilitasiMedikController extends Controller
{
    private const SESSION_BAGIAN = 'rehab_bagian_id';

    private const FORM_SLUG = 'konsultasi';

    public function index(Request $request)
    {
        $bagianList = Bagian::aktif()
            ->where('referensi_bagian_id', 6)
            ->orderBy('nama_bagian')
            ->get();

        $bagianTujuanId = (int) session(self::SESSION_BAGIAN, 0);
        if ($bagianTujuanId && ! $bagianList->contains('bagian_id', $bagianTujuanId)) {
            $bagianTujuanId = 0;
        }

        $tglDaftar = $request->input('tgl_daftar');
        $keyword = $request->input('keyword');

        $listPasien = null;

        if ($bagianTujuanId) {
            $query = DB::table('registrasi_detail as rd')
                ->select(
                    'rd.registrasi_detail_id',
                    'rd.tgl_daftar',
                    'rd.bagian_id',
                    'r.registrasi_id',
                    'r.tgl_masuk',
                    'r.jenis_rawat',
                    'p.pasien_id',
                    'p.no_mr',
                    'p.nama_pasien',
                    'p.tgl_lahir',
                    'p.jenis_kelamin',
                    'b.nama_bagian'
                )
                ->join('registrasi as r', 'r.registrasi_id', '=', 'rd.registrasi_id')
                ->join('pasien as p', 'p.pasien_id', '=', 'r.pasien_id')
                ->join('bagian as b', 'b.bagian_id', '=', 'rd.bagian_id')
                ->where('rd.bagian_id', $bagianTujuanId)
                ->where(function ($q) {
                    $q->whereNull('rd.status_batal')->orWhere('rd.status_batal', 0);
                })
                ->where(function ($q) {
                    $q->whereNull('r.status_batal')->orWhere('r.status_batal', 0);
                })
                ->where(function ($q) {
                    $q->whereNull('p.status_batal')->orWhere('p.status_batal', 0);
                })
                ->where(function ($q) {
                    $q->whereNull('b.status_batal')->orWhere('b.status_batal', 0);
                });

            if ($tglDaftar !== null && $tglDaftar !== '') {
                $query->whereDate('rd.tgl_daftar', $tglDaftar);
            }

            if ($keyword !== null && $keyword !== '') {
                $query->where(function ($q) use ($keyword) {
                    $q->where('p.no_mr', 'like', '%'.$keyword.'%')
                        ->orWhere('p.nama_pasien', 'like', '%'.$keyword.'%');
                });
            }

            $listPasien = $query->orderByDesc('rd.registrasi_detail_id')->paginate(10)->withQueryString();

            // Lampirkan indikasi & jenis dari konsultasi terakhir tiap registrasi
            $formKonsultasi = EmrHelper::formIdBySlug(self::FORM_SLUG);
            if ($listPasien->isNotEmpty() && $formKonsultasi) {
                $registrasiIds = $listPasien->pluck('registrasi_id')->map(fn ($v) => (int) $v);

                $konsultasiRows = DB::table('emr')
                    ->whereIn('registrasi_id', $registrasiIds)
                    ->where('form_id', $formKonsultasi)
                    ->where(function ($q) {
                        $q->whereNull('status_batal')->orWhere('status_batal', 0);
                    })
                    ->select('emr_id', 'registrasi_id')
                    ->orderByDesc('tgl_jam')
                    ->get()
                    ->unique('registrasi_id');

                $indikasiByRegistrasi = [];
                $jenisMap = [];
                foreach ($konsultasiRows as $k) {
                    $details = EmrHelper::emrDetailByVariabel((int) $k->emr_id);
                    $indikasiByRegistrasi[$k->registrasi_id] = $details['indikasi_konsultasi'] ?? '-';
                    $jenisMap[$k->registrasi_id] = $details['jenis_konsultasi'] ?? '';
                }

                $listPasien->getCollection()->transform(function ($item) use ($indikasiByRegistrasi, $jenisMap) {
                    $item->indikasi_konsultasi = $indikasiByRegistrasi[$item->registrasi_id] ?? '-';
                    $item->jenis_konsultasi = $jenisMap[$item->registrasi_id] ?? '';

                    return $item;
                });
            }
        }

        return view('moduls.PenunjangMedis.RehabilitasiMedik.DaftarPasienRehabilitasiMedik.daftar_pasien_rehabilitasi_medik', compact(
            'bagianList',
            'bagianTujuanId',
            'listPasien',
            'tglDaftar',
            'keyword'
        ));
    }

    public function pilihBagian(Request $request)
    {
        $request->validate([
            'bagian_tujuan_id' => 'required|integer|exists:bagian,bagian_id',
        ]);

        $bagian = Bagian::aktif()
            ->where('bagian_id', $request->input('bagian_tujuan_id'))
            ->where('referensi_bagian_id', 6)
            ->first();

        if (! $bagian) {
            return back()->with('error', 'Bagian rehabilitasi medik tidak valid.');
        }

        session()->put(self::SESSION_BAGIAN, (int) $bagian->bagian_id);

        return redirect()->route('daftar_pasien_rehabilitasi_medik.index')
            ->with('success', 'Bagian aktif: '.$bagian->nama_bagian.'.');
    }

    public function detail($registrasiDetail)
    {
        $detail = RegistrasiDetail::with([
            'registrasi.pasien',
            'registrasi.penanggungRawat.user',
            'bagian',
        ])->findOrFail((int) $registrasiDetail);

        $formKonsultasi = EmrHelper::formIdBySlug(self::FORM_SLUG);
        $konsultasiList = $formKonsultasi
            ? EmrHelper::emrList((int) $formKonsultasi, (int) $detail->registrasi_id)
            : collect();

        return view('moduls.PenunjangMedis.RehabilitasiMedik.DaftarPasienRehabilitasiMedik.detail', compact(
            'detail',
            'konsultasiList',
            'formKonsultasi'
        ));
    }
}
