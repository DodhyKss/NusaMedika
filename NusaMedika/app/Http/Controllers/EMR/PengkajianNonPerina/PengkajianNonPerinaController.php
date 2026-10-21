<?php

namespace App\Http\Controllers\EMR\PengkajianNonPerina;

use App\Helpers\AksesEhr;
use App\Helpers\EmrHelper;
use App\Helpers\RisikoJatuhHelper;
use App\Http\Controllers\Controller;
use App\Models\DiagnosaRawat;
use App\Models\Pasien;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Form Pengkajian Awal Keperawatan Non-Perina (form 61, slug
 * `pengkajian_non_perina`).
 *
 * Struktur = form 3 (Pengkajian Awal Keperawatan) + satu variabel penanda
 * `jenis_pengkajian` (objek 562) yang diisi server `Non-Perina` — nilai
 * browser dibuang. Seluruh partial & logika risiko jatuh dipinjam dari form 3.
 */
class PengkajianNonPerinaController extends Controller
{
    public function index($registrasi_detail_id, $emr_id = null)
    {
        $registrasi_detail = RegistrasiDetail::with('registrasi.pasien')->findOrFail($registrasi_detail_id);

        $form_id = EmrHelper::formIdBySlug('pengkajian_non_perina');
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'read'), 403);

        $aksesCrud = AksesEhr::flags((int) $form_id);
        $riwayatPengkajianAwal = EmrHelper::emrList((int) $form_id, (int) $registrasi_detail->registrasi_id);

        if (empty($emr_id) && ! ($aksesCrud['create'] ?? false) && $riwayatPengkajianAwal->isNotEmpty()) {
            return redirect()->route('emr.dynamic.index', [
                'form_name' => 'pengkajian_non_perina',
                'registrasi_detail_id' => $registrasi_detail_id,
                'emr_id' => $riwayatPengkajianAwal->first()->emr_id,
                'action' => 'view',
            ]);
        }

        $historyGrouped = EmrHelper::historyKunjunganGrouped($registrasi_detail);
        $usia = RisikoJatuhHelper::hitungUsia($registrasi_detail->registrasi->pasien->tgl_lahir);

        if (empty($emr_id)) {
            $data_pasien = Pasien::where(function ($q) {
                $q->whereNull('status_batal')->orWhere('status_batal', 0);
            })->where('pasien_id', $registrasi_detail->registrasi->pasien_id)->first();
            $diagnosa_rawat = DiagnosaRawat::with(['icd' => function ($q) {
                $q->aktif();
            }])->where(function ($q) {
                $q->whereNull('status_batal')->orWhere('status_batal', 0);
            })->where('registrasi_id', $registrasi_detail->registrasi->registrasi_id)->first();

            $emr_data = EmrHelper::wrapData([
                'jenis_pengkajian' => 'Non-Perina',
                'agama' => $data_pasien->agama,
                'kegiatan_ibadah' => '',
                'tingkat_pendidikan' => $data_pasien->pendidikan,
                'pekerjaan' => $data_pasien->pekerjaan,
                'suku_bangsa' => $data_pasien->suku,
                'kebangsaan' => $data_pasien->kebangsaan,
                'handphone' => $data_pasien->no_hp,
                'nama_pasangan' => '', 'usia_pasangan' => '', 'pendidikan_pasangan' => '',
                'pekerjaan_pasangan' => '', 'suku_bangsa_pasangan' => '', 'kebangsaan_pasangan' => '',
                'tinggal_bersama' => '', 'penanggung_jawab_pasien' => '', 'hubungan_pasien' => '',
                'aktifitas_sebelum_makan' => 'off',
                'pantangan_pulang' => 'off',
                'pantangan_transfusi_darah' => 'off',
                'pantangan_makan' => 'off',
                'diagnosa_medis' => $diagnosa_rawat && $diagnosa_rawat->icd ? $diagnosa_rawat->icd->kode_diagnosa.' - '.$diagnosa_rawat->icd->nama_diagnosa : '',
                'keluhan' => '',
                'riwayat_penyakit_sebelumnya' => '',
                'riwayat_penyakit_sekarang' => '',
                'infeksius_flag' => 'Tidak', 'menular_melalui' => '',
                'infeksius_memerlukan_isolasi' => 'Tidak', 'infeksius_hasil_penunjang' => '',
                'imunologi_flag' => 'Tidak', 'imunologi_memerlukan_isolasi' => 'Tidak',
                'imunologi_pembatasan_pengunjung' => 'Tidak', 'imunologi_hasil_penunjang' => '',
                'vaksin_covid' => '3', 'tanggal_covid_1' => '', 'tanggal_covid_2' => '',
                'riw_ope_kemo' => 'Tidak', 'riwayat_operasi' => '',
                'riwayat_kemoterapi' => '', 'riwayat_radioterapi' => '',
                'kesadaran' => '', 'gcs_e' => '', 'gcs_m' => '', 'gcs_v' => '', 'gcs_jumlah' => '', 'dpo' => '',
                'td' => '', 'nadi' => '', 'suhu' => '', 'pernapasan' => '', 'berat_badan' => '', 'tinggi_badan' => '',
                'pemberian_o2' => '', 'cara_pemberian_o2' => '', 'ett' => '', 'saturasi' => '', 'ews' => '',
                'allo_anamnesa' => '', 'nama_allo' => '', 'hubungan_allo' => '', 'bmi' => '',
                'nyeri' => 'tidak', 'alergi' => 'tidak',
                'risiko_jatuh_instrumen' => RisikoJatuhHelper::instrumenUntukUsia($usia),
                'risiko_jatuh_skor' => '', 'risiko_jatuh_label' => '',
                'risiko_jatuh_intervensi' => '', 'risiko_jatuh_ringkasan' => '',
            ]);

            $formAction = route('emr.form.store', ['form_name' => 'pengkajian_non_perina', 'registrasi_detail_id' => $registrasi_detail_id]);
            $isEdit = false;
            $deleteAction = '';
            $isView = false;
        } else {
            $emr_data = EmrHelper::emrDetailByVariabel((int) $emr_id);
            $formAction = route('emr.form.update', ['form_name' => 'pengkajian_non_perina', 'registrasi_detail_id' => $registrasi_detail_id, 'emr_id' => $emr_id]);
            $isEdit = true;
            $deleteAction = route('emr.form.destroy', ['form_name' => 'pengkajian_non_perina', 'registrasi_detail_id' => $registrasi_detail_id, 'emr_id' => $emr_id]);
            $isView = request('action') === 'view';
        }

        $printUrl = '';

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
            'aksesCrud',
            'usia'
        ));
    }

    public function store(Request $request, $registrasi_detail_id)
    {
        $form_id = EmrHelper::formIdBySlug('pengkajian_non_perina');
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'create'), 403);

        $registrasi_detail = RegistrasiDetail::with('registrasi.pasien')->findOrFail($registrasi_detail_id);
        $usia = RisikoJatuhHelper::hitungUsia($registrasi_detail->registrasi->pasien->tgl_lahir ?? null);

        $user = Auth::user();
        if ($user == null || $user->pegawai_id == null || $user->user_id == null) {
            return redirect()->back()->with('error', 'Sesi Anda Telah Habis Silahkan Login Kembali!');
        }

        try {
            EmrHelper::insert((int) $form_id, $this->filteredData($request, (int) $form_id, $usia), (int) $registrasi_detail_id);

            return redirect()->back()->with('success', 'Data Pengkajian Awal Keperawatan Non-Perina berhasil disimpan');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan data: '.$e->getMessage())->withInput();
        }
    }

    public function update(Request $request, $registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug('pengkajian_non_perina');
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'update'), 403);

        try {
            $registrasi_detail = RegistrasiDetail::with('registrasi.pasien')->findOrFail($registrasi_detail_id);
            $usia = RisikoJatuhHelper::hitungUsia($registrasi_detail->registrasi->pasien->tgl_lahir ?? null);

            EmrHelper::update((int) $emr_id, (int) $form_id, $this->filteredData($request, (int) $form_id, $usia));

            return redirect()->back()->with('success', 'Data Pengkajian Awal Keperawatan Non-Perina berhasil diperbarui');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui data: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug('pengkajian_non_perina');
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Data berhasil dihapus');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus data: '.$e->getMessage());
        }
    }

    private function filteredData(Request $request, int $formId, ?int $usia = null): array
    {
        $mapped = array_flip(EmrHelper::objekVariabels($formId));
        $data = array_intersect_key($request->all(), $mapped);

        // Penanda jenis pengkajian selalu diisi server (bukan browser).
        $data['jenis_pengkajian'] = 'Non-Perina';

        $kode = RisikoJatuhHelper::normalisasiKode($request->input('risiko_jatuh_instrumen'))
            ?: RisikoJatuhHelper::deteksiInstrumen($data);

        $hasil = RisikoJatuhHelper::hitung($kode, $data, $usia);

        $data['risiko_jatuh_instrumen'] = $kode;
        $data['risiko_jatuh_skor'] = $hasil['skor'];
        $data['risiko_jatuh_label'] = $hasil['lengkap'] ? $hasil['label'] : '';
        $data['risiko_jatuh_intervensi'] = $hasil['intervensi'];
        $data['risiko_jatuh_ringkasan'] = RisikoJatuhHelper::ringkasan($hasil);

        return $data;
    }
}
