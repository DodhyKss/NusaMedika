<?php

namespace App\Http\Controllers\EMR\PengkajianAwalKeperawatan;

use App\Helpers\AksesEhr;
use App\Helpers\EmrHelper;
use App\Http\Controllers\Controller;
use App\Models\DiagnosaRawat;
use App\Models\Pasien;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PengkajianAwalKeperawatanController extends Controller
{
    public function index($registrasi_detail_id, $emr_id = null)
    {
        $registrasi_detail = RegistrasiDetail::with('registrasi.pasien')->findOrFail($registrasi_detail_id);

        $form_id = EmrHelper::formIdBySlug('pengkajian_awal_keperawatan');
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'read'), 403);

        $aksesCrud = AksesEhr::flags((int) $form_id);

        // Ambil riwayat untuk sidebar
        $riwayatPengkajianAwal = EmrHelper::emrList((int) $form_id, (int) $registrasi_detail->registrasi_id);

        $history_kunjungan = DB::table('registrasi_detail')
            ->join('registrasi', 'registrasi_detail.registrasi_id', '=', 'registrasi.registrasi_id')
            ->leftJoin('bagian', function ($join) {
                $join->on('registrasi_detail.bagian_id', '=', 'bagian.bagian_id')
                    ->where(function ($q) {
                        $q->whereNull('bagian.status_batal')->orWhere('bagian.status_batal', 0);
                    });
            })
            ->where('registrasi.pasien_id', $registrasi_detail->registrasi->pasien_id)
            ->where(function ($q) {
                $q->whereNull('registrasi.status_batal')->orWhere('registrasi.status_batal', 0);
            })
            ->where(function ($q) {
                $q->whereNull('registrasi_detail.status_batal')->orWhere('registrasi_detail.status_batal', 0);
            })
            ->select('registrasi.tgl_masuk', 'bagian.nama_bagian', 'registrasi_detail.registrasi_detail_id')
            ->orderBy('registrasi.tgl_masuk', 'desc')
            ->get();

        $historyGrouped = [];
        foreach ($history_kunjungan as $hk) {
            $date = date('Y-m-d', strtotime($hk->tgl_masuk));
            if (! isset($historyGrouped[$date])) {
                $historyGrouped[$date] = [];
            }
            if ($hk->nama_bagian) {
                $historyGrouped[$date][$hk->nama_bagian] = $hk->registrasi_detail_id;
            }
        }

        $emr_data = [];

        // Jika emr_id null artinya user input data baru
        if (empty($emr_id)) {
            // Data Pasien
            $data_pasien = Pasien::where(function ($q) {
                $q->whereNull('status_batal')->orWhere('status_batal', 0);
            })->where('pasien_id', $registrasi_detail->registrasi->pasien_id)->first();
            $diagnosa_rawat = DiagnosaRawat::with(['icd' => function ($q) {
                $q->aktif();
            }])->where(function ($q) {
                $q->whereNull('status_batal')->orWhere('status_batal', 0);
            })->where('registrasi_id', $registrasi_detail->registrasi->registrasi_id)->first();

            $emr_data = [
                // Informasi Dasar Pasien
                'agama' => $data_pasien->agama,
                'kegiatan_ibadah' => '',
                'tingkat_pendidikan' => $data_pasien->pendidikan,
                'pekerjaan' => $data_pasien->pekerjaan,
                'suku_bangsa' => $data_pasien->suku,
                'kebangsaan' => $data_pasien->kebangsaan,
                'handphone' => $data_pasien->no_hp,

                // Informasi Keluarga
                'nama_pasangan' => '', 'usia_pasangan' => '', 'pendidikan_pasangan' => '',
                'pekerjaan_pasangan' => '', 'suku_bangsa_pasangan' => '', 'kebangsaan_pasangan' => '',
                'tinggal_bersama' => '', 'penanggung_jawab_pasien' => '', 'hubungan_pasien' => '',

                // Nilai Nilai Kebudayaan
                'aktifitas_sebelum_makan' => 'off',
                'pantangan_pulang' => 'off',
                'pantangan_transfusi_darah' => 'off',
                'pantangan_makan' => 'off',

                // Riwayat Penyakit
                'diagnosa_medis' => $diagnosa_rawat && $diagnosa_rawat->icd ? $diagnosa_rawat->icd->kode_diagnosa.' - '.$diagnosa_rawat->icd->nama_diagnosa : '',
                'keluhan' => '',
                'riwayat_penyakit_sebelumnya' => '',
                'riwayat_penyakit_sekarang' => '',

                // Infeksius
                'infeksius_flag' => 'Tidak', 'menular_melalui' => '',
                'infeksius_memerlukan_isolasi' => 'Tidak', 'infeksius_hasil_penunjang' => '',

                // Imunologi
                'imunologi_flag' => 'Tidak', 'imunologi_memerlukan_isolasi' => 'Tidak',
                'imunologi_pembatasan_pengunjung' => 'Tidak', 'imunologi_hasil_penunjang' => '',

                // Vaksin COVID
                'vaksin_covid' => '3', 'tanggal_covid_1' => '', 'tanggal_covid_2' => '',

                // Riwayat Operasi / Kemo
                'riw_ope_kemo' => 'Tidak', 'riwayat_operasi' => '',
                'riwayat_kemoterapi' => '', 'riwayat_radioterapi' => '',

                // Pemeriksaan Fisik
                'kesadaran' => '', 'gcs_e' => '', 'gcs_m' => '', 'gcs_v' => '', 'gcs_jumlah' => '', 'dpo' => '',
                'td' => '', 'nadi' => '', 'suhu' => '', 'pernapasan' => '', 'berat_badan' => '', 'tinggi_badan' => '',
                'pemberian_o2' => '', 'cara_pemberian_o2' => '', 'ett' => '', 'saturasi' => '', 'ews' => '',
                'allo_anamnesa' => '', 'nama_allo' => '', 'hubungan_allo' => '', 'bmi' => '',

                // Pengkajian Nyeri / Alergi / UP GO
                'nyeri' => 'tidak', 'alergi' => 'tidak',
                'up_go_1_a' => '', 'up_go_1_b' => '', 'up_go_2' => '',
            ];

            $formAction = route('emr.form.store', ['form_name' => 'pengkajian_awal_keperawatan', 'registrasi_detail_id' => $registrasi_detail_id]);
            $isEdit = false;
            $deleteAction = '';
            $isView = false;
        } else {
            // Jika emr_id ada artinya user mengedit data atau melihat data
            $emr_data = EmrHelper::emrDetailByVariabel((int) $emr_id);

            $formAction = route('emr.form.update', ['form_name' => 'pengkajian_awal_keperawatan', 'registrasi_detail_id' => $registrasi_detail_id, 'emr_id' => $emr_id]);
            $isEdit = true;
            $deleteAction = route('emr.form.destroy', ['form_name' => 'pengkajian_awal_keperawatan', 'registrasi_detail_id' => $registrasi_detail_id, 'emr_id' => $emr_id]);
            $isView = request('action') === 'view';
        }

        $printUrl = ''; // Bisa ditambahkan rute cetak nanti

        return view('moduls.EMR.PengkajianAwalKeperawatan.index', compact(
            'registrasi_detail',
            'riwayatPengkajianAwal',
            'emr_data',
            'historyGrouped',
            'formAction',
            'isEdit',
            'deleteAction',
            'isView',
            'printUrl',
            'emr_id',
            'aksesCrud'
        ));
    }

    public function store(Request $request, $registrasi_detail_id)
    {
        $form_id = EmrHelper::formIdBySlug('pengkajian_awal_keperawatan');
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'create'), 403);

        $registrasi_detail = RegistrasiDetail::findOrFail($registrasi_detail_id);

        $user = Auth::user();
        if ($user == null || $user->pegawai_id == null || $user->user_id == null) {
            return redirect()->back()->with('error', 'Sesi Anda Telah Habis Silahkan Login Kembali!');
        }

        try {
            EmrHelper::insert((int) $form_id, $this->filteredData($request, (int) $form_id), (int) $registrasi_detail_id);

            return redirect()->back()->with('success', 'Data Pengkajian Awal Keperawatan berhasil disimpan');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan data: '.$e->getMessage())->withInput();
        }
    }

    public function update(Request $request, $registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug('pengkajian_awal_keperawatan');
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'update'), 403);

        try {
            EmrHelper::update((int) $emr_id, (int) $form_id, $this->filteredData($request, (int) $form_id));

            return redirect()->back()->with('success', 'Data Pengkajian Awal Keperawatan berhasil diperbarui');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui data: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug('pengkajian_awal_keperawatan');
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Data berhasil dihapus');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus data: '.$e->getMessage());
        }
    }

    /**
     * Ambil hanya field yang terdaftar di mapping form (objekVariabels),
     * sisanya (radio bantu, _token, dll.) tidak ikut disimpan.
     */
    private function filteredData(Request $request, int $formId): array
    {
        $mapped = array_flip(EmrHelper::objekVariabels($formId));

        return array_intersect_key($request->all(), $mapped);
    }
}
