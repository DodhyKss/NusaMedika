<?php

namespace App\Http\Controllers\EMR\PengkajianAwalKeperawatan;

use App\Http\Controllers\Controller;
use App\Models\DiagnosaRawat;
use App\Models\Pasien;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PengkajianAwalKeperawatanController extends Controller
{
    public function index($registrasi_detail_id, $emr_id = null)
    {
        $registrasi_detail = RegistrasiDetail::with('registrasi.pasien')->findOrFail($registrasi_detail_id);
        $riwayatPengkajianAwal = [];
        $emr_data = [];
        
        $form_id = env('FORM_ID_PENGKAJIAN_AWAL_KEPERAWATAN');

        // Ambil riwayat untuk sidebar
        $riwayatPengkajianAwal = DB::table('emr')
            ->leftJoin('pegawai', 'emr.pegawai_id', '=', 'pegawai.pegawai_id')
            ->where('emr.registrasi_id', $registrasi_detail->registrasi_id)
            ->where('emr.form_id', $form_id)
            ->whereNull('emr.status_batal')
            ->select('emr.*', 'pegawai.nama_pegawai')
            ->orderBy('emr.tgl_jam', 'desc')
            ->paginate(5)
            ->withQueryString();

        $history_kunjungan = DB::table('registrasi_detail')
            ->join('registrasi', 'registrasi_detail.registrasi_id', '=', 'registrasi.registrasi_id')
            ->leftJoin('bagian', 'registrasi_detail.bagian_id', '=', 'bagian.bagian_id')
            ->where('registrasi.pasien_id', $registrasi_detail->registrasi->pasien_id)
            ->whereNull('registrasi.status_batal')
            ->whereNull('registrasi_detail.status_batal')
            ->select('registrasi.tgl_masuk', 'bagian.nama_bagian', 'registrasi_detail.registrasi_detail_id')
            ->orderBy('registrasi.tgl_masuk', 'desc')
            ->get();

        $historyGrouped = [];
        foreach ($history_kunjungan as $hk) {
            $date = date('Y-m-d', strtotime($hk->tgl_masuk));
            if (!isset($historyGrouped[$date])) {
                $historyGrouped[$date] = [];
            }
            if ($hk->nama_bagian) {
                $historyGrouped[$date][$hk->nama_bagian] = $hk->registrasi_detail_id;
            }
        }

        # Jika emr_id null artinya user input data baru
        if(empty($emr_id)){
            # Data Pasien
            $data_pasien = Pasien::whereNull('status_batal')->where('pasien_id', $registrasi_detail->registrasi->pasien_id)->first();
            $diagnosa_rawat = DiagnosaRawat::with('icd')->whereNull('status_batal')->where('registrasi_id', $registrasi_detail->registrasi->registrasi_id)->first();
            
            $emr_data[env('OBJEK_ID_AGAMA')]['agama'] = $data_pasien->agama;
            $emr_data[env('OBJEK_ID_TINKAT_PENDIDIKAN')]['tingkat_pendidikan'] = $data_pasien->pendidikan;
            $emr_data[env('OBJEK_ID_KEGIATAN_IBADAH_ATAU_BUDAYA')]['kegiatan_ibadah'] = '';
            $emr_data[env('OBJEK_ID_PEKERJAAN')]['pekerjaan'] = $data_pasien->pekerjaan;
            $emr_data[env('OBJEK_ID_SUKU_BANGSA')]['suku_bangsa'] = $data_pasien->suku;
            $emr_data[env('OBJEK_ID_KEBANGSAAN')]['kebangsaan'] = $data_pasien->kebangsaan;
            $emr_data[env('OBJEK_ID_HANDPHONE')]['handphone'] = $data_pasien->no_hp;

            $emr_data[env('OBJEK_ID_NAMA_PASANGAN')]['nama_pasangan'] = '';
            $emr_data[env('OBJEK_ID_USIA_PASANGAN')]['usia_pasangan'] = '';
            $emr_data[env('OBJEK_ID_PENDIDIKAN_PASANGAN')]['pendidikan_pasangan'] = '';
            $emr_data[env('OBJEK_ID_PEKERJAAN_PASANGAN')]['pekerjaan_pasangan'] = '';
            $emr_data[env('OBJEK_ID_SUKU_BANGSA_PASANGAN')]['suku_bangsa_pasangan'] = '';
            $emr_data[env('OBJEK_ID_KEBANGSAAN_PASANGAN')]['kebangsaan_pasangan'] = '';
            $emr_data[env('OBJEK_ID_TINGGAL_BERSAMA')]['tinggal_bersama'] = '';
            $emr_data[env('OBJEK_ID_PENANGGUNG_JAWAB_PASIEN')]['penanggung_jawab_pasien'] = '';
            $emr_data[env('OBJEK_ID_HUBUNGAN_PASIEN')]['hubungan_pasien'] = '';
            
            $emr_data[env('OBJEK_ID_AKTIFITAS_SEBELUM_MAKAN')]['aktifitas_sebelum_makan'] = 'off';
            $emr_data[env('OBJEK_ID_PANTANGAN_PULANG')]['pantangan_pulang'] = 'off';
            $emr_data[env('OBJEK_ID_PANTANGAN_TRANSFUSI_DARAH')]['pantangan_transfusi_darah'] = 'off';
            $emr_data[env('OBJEK_ID_PANTANGAN_MAKAN')]['pantangan_makan'] = 'off';

            $emr_data[env('OBJEK_ID_DIAGNOSA_MEDIS')]['diagnosa_medis'] = $diagnosa_rawat && $diagnosa_rawat->icd ? $diagnosa_rawat->icd->kode_diagnosa . ' - ' . $diagnosa_rawat->icd->nama_diagnosa : '';
            $emr_data[env('OBJEK_ID_KELUHAN')]['keluhan'] = '';
            $emr_data[env('OBJEK_ID_RIWAYAT_PENYAKIT_SEBELUMNYA')]['riwayat_penyakit_sebelumnya'] = '';
            $emr_data[env('OBJEK_ID_RIWAYAT_PENYAKIT_SEKARANG')]['riwayat_penyakit_sekarang'] = '';
            
            $emr_data[env('OBJEK_ID_INFEKSIUS_FLAG')]['infeksius_flag'] = 'Tidak';
            $emr_data[env('OBJEK_ID_MENULAR_MELALUI')]['menular_melalui'] = '';
            $emr_data[env('OBJEK_ID_INFEKSIUS_MEMERLUKAN_ISOLASI')]['infeksius_memerlukan_isolasi'] = 'Tidak';
            $emr_data[env('OBJEK_ID_INFEKSIUS_HASIL_PENUNJANG')]['infeksius_hasil_penunjang'] = '';

            $emr_data[env('OBJEK_ID_IMUNOLOGI_FLAG')]['imunologi_flag'] = 'Tidak';
            $emr_data[env('OBJEK_ID_IMUNOLOGI_MEMERLUKAN_ISOLASI')]['imunologi_memerlukan_isolasi'] = 'Tidak';
            $emr_data[env('OBJEK_ID_IMUNOLOGI_PEMBATASAN_PENGUNJUNG')]['imunologi_pembatasan_pengunjung'] = 'Tidak';
            $emr_data[env('OBJEK_ID_IMUNOLOGI_HASIL_PENUNJANG')]['imunologi_hasil_penunjang'] = '';

            $emr_data[env('OBJEK_ID_VAKSIN_COVID')]['vaksin_covid'] = '3';
            $emr_data[env('OBJEK_ID_VAKSIN_COVID')]['tanggal_covid_1'] = '';
            $emr_data[env('OBJEK_ID_VAKSIN_COVID')]['tanggal_covid_2'] = '';

            $emr_data[env('OBJEK_ID_RIW_OPE_KEMO')]['riw_ope_kemo'] = 'Tidak';
            $emr_data[env('OBJEK_ID_RIWAYAT_OPERASI')]['riwayat_operasi'] = '';
            $emr_data[env('OBJEK_ID_RIWAYAT_KEMOTERAPI')]['riwayat_kemoterapi'] = '';
            $emr_data[env('OBJEK_ID_RIWAYAT_RADIOTERAPI')]['riwayat_radioterapi'] = '';

            $emr_data[env('OBJEK_ID_KESADARAN')]['kesadaran'] = '';
            $emr_data[env('OBJEK_ID_GCS_E')]['gcs_e'] = '';
            $emr_data[env('OBJEK_ID_GCS_M')]['gcs_m'] = '';
            $emr_data[env('OBJEK_ID_GCS_V')]['gcs_v'] = '';
            $emr_data[env('OBJEK_ID_GCS_SCORE')]['gcs_jumlah'] = '';
            $emr_data[env('OBJEK_ID_DPO')]['dpo'] = '';
            $emr_data[env('OBJEK_ID_SISTOLIK')]['td'] = '';
            $emr_data[env('OBJEK_ID_NADI')]['nadi'] = '';
            $emr_data[env('OBJEK_ID_SUHU')]['suhu'] = '';
            $emr_data[env('OBJEK_ID_PERNAPASAN')]['pernapasan'] = '';
            $emr_data[env('OBJEK_ID_BERAT_BADAN')]['berat_badan'] = '';
            $emr_data[env('OBJEK_ID_TINGGI_BADAN')]['tinggi_badan'] = '';
            $emr_data[env('OBJEK_ID_OKSIGEN')]['pemberian_o2'] = '';
            $emr_data[env('OBJEK_ID_CARA_PEMBERIAN')]['cara_pemberian_o2'] = '';
            $emr_data[env('OBJEK_ID_ETT')]['ett'] = '';
            $emr_data[env('OBJEK_ID_SATURASI')]['saturasi'] = '';
            $emr_data[env('OBJEK_ID_EWS')]['ews'] = '';
            $emr_data[env('OBJEK_ID_ALLO_ANAMNESA')]['allo_anamnesa'] = '';
            $emr_data[env('OBJEK_ID_NAMA_ALLO')]['nama_allo'] = '';
            $emr_data[env('OBJEK_ID_HUBUNGAN_ALLO')]['hubungan_allo'] = '';
            $emr_data[env('OBJEK_ID_BMI')]['bmi'] = '';
            
            $emr_data[env('OBJEK_ID_NYERI')]['nyeri'] = 'tidak';
            $emr_data[env('OBJEK_ID_ALERGI')]['alergi'] = 'tidak';
            $emr_data[env('OBJEK_ID_UP_GO_1_A')]['up_go_1_a'] = '';
            $emr_data[env('OBJEK_ID_UP_GO_1_B')]['up_go_1_b'] = '';
            $emr_data[env('OBJEK_ID_UP_GO_2')]['up_go_2'] = '';

            $formAction = route('emr.pengkajian_awal_keperawatan.store', $registrasi_detail_id);
            $isEdit = false;
            $deleteAction = '';
            $isView = false;
        }else{
            # Jika emr_id ada artinya user mengedit data atau melihat data
            $details = DB::table('emr_detail')
                ->where('emr_id', $emr_id)
                ->whereNull('status_batal')
                ->get();
            
            foreach ($details as $d) {
                $emr_data[$d->objek_id][$d->variabel] = $d->value;
            }

            $formAction = route('emr.pengkajian_awal_keperawatan.update', [$registrasi_detail_id, $emr_id]);
            $isEdit = true;
            $deleteAction = route('emr.pengkajian_awal_keperawatan.destroy', [$registrasi_detail_id, $emr_id]);
            $isView = request('action') === 'view';
        }

        $printUrl = ''; // Bisa ditambahkan rute cetak nanti

        return view('moduls.emr.pengkajian_awal_keperawatan.index', compact(
            'registrasi_detail',
            'riwayatPengkajianAwal',
            'emr_data',
            'historyGrouped',
            'formAction',
            'isEdit',
            'deleteAction',
            'isView',
            'printUrl',
            'emr_id'
        ));
    }

    public function store(Request $request, $registrasi_detail_id)
    {
        $registrasi_detail = RegistrasiDetail::findOrFail($registrasi_detail_id);
        $user_id = Auth::user()->user_id;
        $pegawai_id = Auth::user()->pegawai_id;
        $form_id = env('FORM_ID_PENGKAJIAN_AWAL_KEPERAWATAN');
        $now = now();

        if($pegawai_id == null || $user_id == null) {
            return redirect()->back()->with('error', 'Sesi Anda Telah Habis Silahkan Login Kembali!');
        }

        DB::beginTransaction();
        try {
            $emr_id = \App\Helpers\GenerateHelper::getNextId('emr');

            DB::table('emr')->insert([
                'emr_id' => $emr_id,
                'form_id' => $form_id,
                'pegawai_id' => $pegawai_id,
                'tgl_jam' => $now,
                'registrasi_detail_id' => $registrasi_detail_id,
                'pasien_id' => $registrasi_detail->registrasi->pasien_id,
                'registrasi_id' => $registrasi_detail->registrasi_id,
                'input_time' => $now,
                'input_user_id' => $user_id,
            ]);

            // Mapping semua field ke objek_id masing-masing
            $mapping = [
                'agama' => env('OBJEK_ID_AGAMA'),
                'kegiatan_ibadah' => env('OBJEK_ID_KEGIATAN_IBADAH_ATAU_BUDAYA'),
                'tingkat_pendidikan' => env('OBJEK_ID_TINKAT_PENDIDIKAN'),
                'pekerjaan' => env('OBJEK_ID_PEKERJAAN'),
                'suku_bangsa' => env('OBJEK_ID_SUKU_BANGSA'),
                'kebangsaan' => env('OBJEK_ID_KEBANGSAAN'),
                'handphone' => env('OBJEK_ID_HANDPHONE'),
                
                'nama_pasangan' => env('OBJEK_ID_NAMA_PASANGAN'),
                'usia_pasangan' => env('OBJEK_ID_USIA_PASANGAN'),
                'pendidikan_pasangan' => env('OBJEK_ID_PENDIDIKAN_PASANGAN'),
                'pekerjaan_pasangan' => env('OBJEK_ID_PEKERJAAN_PASANGAN'),
                'suku_bangsa_pasangan' => env('OBJEK_ID_SUKU_BANGSA_PASANGAN'),
                'kebangsaan_pasangan' => env('OBJEK_ID_KEBANGSAAN_PASANGAN'),
                'tinggal_bersama' => env('OBJEK_ID_TINGGAL_BERSAMA'),
                
                'penanggung_jawab_pasien' => env('OBJEK_ID_PENANGGUNG_JAWAB_PASIEN'),
                'hubungan_pasien' => env('OBJEK_ID_HUBUNGAN_PASIEN'),
                
                'aktifitas_sebelum_makan' => env('OBJEK_ID_AKTIFITAS_SEBELUM_MAKAN'),
                'pantangan_pulang' => env('OBJEK_ID_PANTANGAN_PULANG'),
                'pantangan_transfusi_darah' => env('OBJEK_ID_PANTANGAN_TRANSFUSI_DARAH'),
                'pantangan_makan' => env('OBJEK_ID_PANTANGAN_MAKAN'),
                
                'diagnosa_medis' => env('OBJEK_ID_DIAGNOSA_MEDIS'),
                'keluhan' => env('OBJEK_ID_KELUHAN'),
                'riwayat_penyakit_sebelumnya' => env('OBJEK_ID_RIWAYAT_PENYAKIT_SEBELUMNYA'),
                'riwayat_penyakit_sekarang' => env('OBJEK_ID_RIWAYAT_PENYAKIT_SEKARANG'),
                
                'infeksius_flag' => env('OBJEK_ID_INFEKSIUS_FLAG'),
                'menular_melalui' => env('OBJEK_ID_MENULAR_MELALUI'),
                'infeksius_memerlukan_isolasi' => env('OBJEK_ID_INFEKSIUS_MEMERLUKAN_ISOLASI'),
                'infeksius_hasil_penunjang' => env('OBJEK_ID_INFEKSIUS_HASIL_PENUNJANG'),
                
                'imunologi_flag' => env('OBJEK_ID_IMUNOLOGI_FLAG'),
                'imunologi_memerlukan_isolasi' => env('OBJEK_ID_IMUNOLOGI_MEMERLUKAN_ISOLASI'),
                'imunologi_pembatasan_pengunjung' => env('OBJEK_ID_IMUNOLOGI_PEMBATASAN_PENGUNJUNG'),
                'imunologi_hasil_penunjang' => env('OBJEK_ID_IMUNOLOGI_HASIL_PENUNJANG'),
                
                'vaksin_covid' => env('OBJEK_ID_VAKSIN_COVID'),
                'tanggal_covid_1' => env('OBJEK_ID_VAKSIN_COVID'),
                'tanggal_covid_2' => env('OBJEK_ID_VAKSIN_COVID'),
                
                'riw_ope_kemo' => env('OBJEK_ID_RIW_OPE_KEMO'),
                'riwayat_operasi' => env('OBJEK_ID_RIWAYAT_OPERASI'),
                'riwayat_kemoterapi' => env('OBJEK_ID_RIWAYAT_KEMOTERAPI'),
                'riwayat_radioterapi' => env('OBJEK_ID_RIWAYAT_RADIOTERAPI'),

                'kesadaran' => env('OBJEK_ID_KESADARAN'),
                'gcs_e' => env('OBJEK_ID_GCS_E'),
                'gcs_m' => env('OBJEK_ID_GCS_M'),
                'gcs_v' => env('OBJEK_ID_GCS_V'),
                'gcs_jumlah' => env('OBJEK_ID_GCS_SCORE'),
                'dpo' => env('OBJEK_ID_DPO'),
                'td' => env('OBJEK_ID_SISTOLIK'), // Kita map td ke sistolik sementara atau jadikan custom jika dipisah
                'nadi' => env('OBJEK_ID_NADI'),
                'suhu' => env('OBJEK_ID_SUHU'),
                'pernapasan' => env('OBJEK_ID_PERNAPASAN'),
                'berat_badan' => env('OBJEK_ID_BERAT_BADAN'),
                'tinggi_badan' => env('OBJEK_ID_TINGGI_BADAN'),
                'pemberian_o2' => env('OBJEK_ID_OKSIGEN'),
                'cara_pemberian_o2' => env('OBJEK_ID_CARA_PEMBERIAN'),
                'ett' => env('OBJEK_ID_ETT'),
                'saturasi' => env('OBJEK_ID_SATURASI'),
                'ews' => env('OBJEK_ID_EWS'),
                'allo_anamnesa' => env('OBJEK_ID_ALLO_ANAMNESA'),
                'nama_allo' => env('OBJEK_ID_NAMA_ALLO'),
                'hubungan_allo' => env('OBJEK_ID_HUBUNGAN_ALLO'),
                'bmi' => env('OBJEK_ID_BMI'),

                'nyeri' => env('OBJEK_ID_NYERI'),
                'alergi' => env('OBJEK_ID_ALERGI'),
                'up_go_1_a' => env('OBJEK_ID_UP_GO_1_A'),
                'up_go_1_b' => env('OBJEK_ID_UP_GO_1_B'),
                'up_go_2' => env('OBJEK_ID_UP_GO_2'),
            ];

            foreach ($mapping as $key => $objek_id) {
                if ($request->has($key)) {
                    $emr_detail_id = \App\Helpers\GenerateHelper::getNextId('emr_detail');
                    DB::table('emr_detail')->insert([
                        'emr_detail_id' => $emr_detail_id,
                        'emr_id' => $emr_id,
                        'objek_id' => $objek_id,
                        'variabel' => $key,
                        'value' => is_array($request->$key) ? json_encode($request->$key) : $request->$key,
                        'input_time' => $now,
                        'input_user_id' => $user_id,
                    ]);
                }
            }

            DB::commit();
            return redirect()->back()->with('success', 'Data Pengkajian Awal Keperawatan berhasil disimpan');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Gagal menyimpan data: ' . $e->getMessage())->withInput();
        }
    }

    public function update(Request $request, $registrasi_detail_id, $emr_id)
    {
        $user_id = Auth::user()->user_id;
        $now = now();

        DB::beginTransaction();
        try {
            DB::table('emr')
                ->where('emr_id', $emr_id)
                ->update([
                    'mod_time' => $now,
                    'mod_user_id' => $user_id,
                ]);

            // Soft delete old details
            DB::table('emr_detail')
                ->where('emr_id', $emr_id)
                ->update(['status_batal' => 1, 'mod_time' => $now, 'mod_user_id' => $user_id]);

            $mapping = [
                'agama' => env('OBJEK_ID_AGAMA'),
                'kegiatan_ibadah' => env('OBJEK_ID_KEGIATAN_IBADAH_ATAU_BUDAYA'),
                'tingkat_pendidikan' => env('OBJEK_ID_TINKAT_PENDIDIKAN'),
                'pekerjaan' => env('OBJEK_ID_PEKERJAAN'),
                'suku_bangsa' => env('OBJEK_ID_SUKU_BANGSA'),
                'kebangsaan' => env('OBJEK_ID_KEBANGSAAN'),
                'handphone' => env('OBJEK_ID_HANDPHONE'),
                
                'nama_pasangan' => env('OBJEK_ID_NAMA_PASANGAN'),
                'usia_pasangan' => env('OBJEK_ID_USIA_PASANGAN'),
                'pendidikan_pasangan' => env('OBJEK_ID_PENDIDIKAN_PASANGAN'),
                'pekerjaan_pasangan' => env('OBJEK_ID_PEKERJAAN_PASANGAN'),
                'suku_bangsa_pasangan' => env('OBJEK_ID_SUKU_BANGSA_PASANGAN'),
                'kebangsaan_pasangan' => env('OBJEK_ID_KEBANGSAAN_PASANGAN'),
                'tinggal_bersama' => env('OBJEK_ID_TINGGAL_BERSAMA'),
                
                'penanggung_jawab_pasien' => env('OBJEK_ID_PENANGGUNG_JAWAB_PASIEN'),
                'hubungan_pasien' => env('OBJEK_ID_HUBUNGAN_PASIEN'),
                
                'aktifitas_sebelum_makan' => env('OBJEK_ID_AKTIFITAS_SEBELUM_MAKAN'),
                'pantangan_pulang' => env('OBJEK_ID_PANTANGAN_PULANG'),
                'pantangan_transfusi_darah' => env('OBJEK_ID_PANTANGAN_TRANSFUSI_DARAH'),
                'pantangan_makan' => env('OBJEK_ID_PANTANGAN_MAKAN'),
                
                'diagnosa_medis' => env('OBJEK_ID_DIAGNOSA_MEDIS'),
                'keluhan' => env('OBJEK_ID_KELUHAN'),
                'riwayat_penyakit_sebelumnya' => env('OBJEK_ID_RIWAYAT_PENYAKIT_SEBELUMNYA'),
                'riwayat_penyakit_sekarang' => env('OBJEK_ID_RIWAYAT_PENYAKIT_SEKARANG'),
                
                'infeksius_flag' => env('OBJEK_ID_INFEKSIUS_FLAG'),
                'menular_melalui' => env('OBJEK_ID_MENULAR_MELALUI'),
                'infeksius_memerlukan_isolasi' => env('OBJEK_ID_INFEKSIUS_MEMERLUKAN_ISOLASI'),
                'infeksius_hasil_penunjang' => env('OBJEK_ID_INFEKSIUS_HASIL_PENUNJANG'),
                
                'imunologi_flag' => env('OBJEK_ID_IMUNOLOGI_FLAG'),
                'imunologi_memerlukan_isolasi' => env('OBJEK_ID_IMUNOLOGI_MEMERLUKAN_ISOLASI'),
                'imunologi_pembatasan_pengunjung' => env('OBJEK_ID_IMUNOLOGI_PEMBATASAN_PENGUNJUNG'),
                'imunologi_hasil_penunjang' => env('OBJEK_ID_IMUNOLOGI_HASIL_PENUNJANG'),
                
                'vaksin_covid' => env('OBJEK_ID_VAKSIN_COVID'),
                'tanggal_covid_1' => env('OBJEK_ID_VAKSIN_COVID'),
                'tanggal_covid_2' => env('OBJEK_ID_VAKSIN_COVID'),
                
                'riw_ope_kemo' => env('OBJEK_ID_RIW_OPE_KEMO'),
                'riwayat_operasi' => env('OBJEK_ID_RIWAYAT_OPERASI'),
                'riwayat_kemoterapi' => env('OBJEK_ID_RIWAYAT_KEMOTERAPI'),
                'riwayat_radioterapi' => env('OBJEK_ID_RIWAYAT_RADIOTERAPI'),

                'kesadaran' => env('OBJEK_ID_KESADARAN'),
                'gcs_e' => env('OBJEK_ID_GCS_E'),
                'gcs_m' => env('OBJEK_ID_GCS_M'),
                'gcs_v' => env('OBJEK_ID_GCS_V'),
                'gcs_jumlah' => env('OBJEK_ID_GCS_SCORE'),
                'dpo' => env('OBJEK_ID_DPO'),
                'td' => env('OBJEK_ID_SISTOLIK'),
                'nadi' => env('OBJEK_ID_NADI'),
                'suhu' => env('OBJEK_ID_SUHU'),
                'pernapasan' => env('OBJEK_ID_PERNAPASAN'),
                'berat_badan' => env('OBJEK_ID_BERAT_BADAN'),
                'tinggi_badan' => env('OBJEK_ID_TINGGI_BADAN'),
                'pemberian_o2' => env('OBJEK_ID_OKSIGEN'),
                'cara_pemberian_o2' => env('OBJEK_ID_CARA_PEMBERIAN'),
                'ett' => env('OBJEK_ID_ETT'),
                'saturasi' => env('OBJEK_ID_SATURASI'),
                'ews' => env('OBJEK_ID_EWS'),
                'allo_anamnesa' => env('OBJEK_ID_ALLO_ANAMNESA'),
                'nama_allo' => env('OBJEK_ID_NAMA_ALLO'),
                'hubungan_allo' => env('OBJEK_ID_HUBUNGAN_ALLO'),
                'bmi' => env('OBJEK_ID_BMI'),

                'nyeri' => env('OBJEK_ID_NYERI'),
                'alergi' => env('OBJEK_ID_ALERGI'),
                'up_go_1_a' => env('OBJEK_ID_UP_GO_1_A'),
                'up_go_1_b' => env('OBJEK_ID_UP_GO_1_B'),
                'up_go_2' => env('OBJEK_ID_UP_GO_2'),
            ];

            foreach ($mapping as $key => $objek_id) {
                if ($request->has($key)) {
                    $emr_detail_id = \App\Helpers\GenerateHelper::getNextId('emr_detail');
                    DB::table('emr_detail')->insert([
                        'emr_detail_id' => $emr_detail_id,
                        'emr_id' => $emr_id,
                        'objek_id' => $objek_id,
                        'variabel' => $key,
                        'value' => is_array($request->$key) ? json_encode($request->$key) : $request->$key,
                        'input_time' => $now,
                        'input_user_id' => $user_id,
                    ]);
                }
            }

            DB::commit();
            return redirect()->back()->with('success', 'Data Pengkajian Awal Keperawatan berhasil diperbarui');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Gagal memperbarui data: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $user_id = Auth::user()->user_id;
        $now = now();

        DB::beginTransaction();
        try {
            DB::table('emr')
                ->where('emr_id', $emr_id)
                ->update([
                    'status_batal' => 1,
                    'mod_time' => $now,
                    'mod_user_id' => $user_id,
                ]);

            DB::table('emr_detail')
                ->where('emr_id', $emr_id)
                ->update([
                    'status_batal' => 1,
                    'mod_time' => $now,
                    'mod_user_id' => $user_id,
                ]);

            DB::commit();
            return redirect()->back()->with('success', 'Data berhasil dihapus');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }
}
