<?php

namespace App\Http\Controllers\EMR\PengkajianPerina;

use App\Helpers\AksesEhr;
use App\Helpers\EmrHelper;
use App\Helpers\SelectOption;
use App\Http\Controllers\Controller;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * Form Pengkajian Awal Keperawatan Perina (form 60, slug `pengkajian_perina`).
 *
 * Profil ibu + kehamilan + SAP neonatal head-to-toe. Blok bayi lain (APGAR,
 * resusitasi, dst.) dipisah ke form 63-67. Checkbox penyakit hamil / komplikasi
 * memakai pola hidden "0" + checkbox "1" (in:0,1). Baris `usg_ibu_N` yang kosong
 * dibuang di `filteredData()` (dinode-kan null, bukan unset, supaya mapping
 * lengkap).
 */
class PengkajianPerinaController extends Controller
{
    private const SLUG = 'pengkajian_perina';

    public function index($registrasi_detail_id, $emr_id = null)
    {
        $registrasi_detail = RegistrasiDetail::with('registrasi.pasien')->findOrFail($registrasi_detail_id);

        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'read'), 403);

        $aksesCrud = AksesEhr::flags((int) $form_id);
        $riwayat = EmrHelper::emrList((int) $form_id, (int) $registrasi_detail->registrasi_id);
        $historyGrouped = EmrHelper::historyKunjunganGrouped($registrasi_detail);

        if (empty($emr_id) && ! ($aksesCrud['create'] ?? false) && $riwayat->isNotEmpty()) {
            return redirect()->route('emr.dynamic.index', [
                'form_name' => self::SLUG,
                'registrasi_detail_id' => $registrasi_detail_id,
                'emr_id' => $riwayat->first()->emr_id,
                'action' => 'view',
            ]);
        }

        if (empty($emr_id)) {
            $kosong = $this->kosong();
            // Prefill identitas dari pasien bila belum ada data EMR.
            $pasien = $registrasi_detail->registrasi->pasien ?? null;
            if ($pasien) {
                $kosong['nama_bayi'] = $pasien->nama_pasien ?? '';
            }
            $emr_data = EmrHelper::wrapData($kosong);
            $formAction = route('emr.form.store', ['form_name' => self::SLUG, 'registrasi_detail_id' => $registrasi_detail_id]);
            $isEdit = false;
            $deleteAction = '';
            $isView = false;
        } else {
            $emr_data = EmrHelper::emrDetailByVariabel((int) $emr_id);
            $formAction = route('emr.form.update', ['form_name' => self::SLUG, 'registrasi_detail_id' => $registrasi_detail_id, 'emr_id' => $emr_id]);
            $isEdit = true;
            $deleteAction = route('emr.form.destroy', ['form_name' => self::SLUG, 'registrasi_detail_id' => $registrasi_detail_id, 'emr_id' => $emr_id]);
            $isView = request('action') === 'view';
        }

        return view('moduls.EMR.PengkajianPerina.index', compact(
            'registrasi_detail', 'riwayat', 'historyGrouped', 'emr_data',
            'formAction', 'isEdit', 'deleteAction', 'isView', 'emr_id', 'aksesCrud'
        ));
    }

    public function store(Request $request, $registrasi_detail_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'create'), 403);

        $user = Auth::user();
        if (! $user || ! $user->pegawai_id || ! $user->user_id) {
            return redirect()->back()->with('error', 'Sesi Anda Telah Habis Silahkan Login Kembali!');
        }

        $data = $this->validated($request);

        try {
            EmrHelper::insert((int) $form_id, $data, (int) $registrasi_detail_id);

            return redirect()->back()->with('success', 'Pengkajian Awal Keperawatan Perina berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan Pengkajian Perina: '.$e->getMessage())->withInput();
        }
    }

    public function update(Request $request, $registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'update'), 403);

        $data = $this->validated($request);

        try {
            EmrHelper::update((int) $emr_id, (int) $form_id, $data);

            return redirect()->back()->with('success', 'Pengkajian Awal Keperawatan Perina berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui Pengkajian Perina: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Pengkajian Awal Keperawatan Perina berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus Pengkajian Perina: '.$e->getMessage());
        }
    }

    private function kosong(): array
    {
        $data = [
            'tanggal_pengkajian' => now()->format('Y-m-d'), 'jam_pengkajian' => now()->format('H:i'),
            'nama_bayi' => '', 'tanggal_lahir_bayi' => '', 'jam_lahir_bayi' => '', 'jenis_kelamin_bayi' => '',
            'lingkar_kepala_bayi' => '', 'suhu_rectal_bayi' => '', 'tanda_pengenal_bayi' => '', 'tempat_lahir_bayi' => '',
            'nama_ibu' => '', 'umur_ibu' => '', 'alamat_ibu' => '',
            'nama_ayah' => '', 'umur_ayah' => '',
            'tinggi_ibu' => '', 'berat_ibu' => '', 'presentasi_bayi' => '',
            'antenatal_care' => '', 'lokasi_antenatal_care' => '',
            'hpht' => '', 'taksiran_partus' => '', 'imunisasi_hamil' => '',
            'penyakit_hamil_lainnya' => '', 'komplikasi_lainnya' => '',
            'golongan_darah_ibu' => '', 'hb_ibu' => '', 'leukosit_ibu' => '', 'gula_darah_ibu' => '', 'urine_lengkap_ibu' => '',
            'berat_badan_lahir' => '', 'tinggi_badan_lahir' => '', 'denyut_jantung_bayi' => '', 'pernapasan_bayi' => '',
            'agama_ibu' => '', 'pendidikan_ibu' => '', 'pekerjaan_ibu' => '',
            'agama_ayah' => '', 'pendidikan_ayah' => '', 'pekerjaan_ayah' => '',
        ];

        foreach ($this->checkboxKehamilan() as $k) {
            $data[$k] = '0';
        }
        foreach (range(1, 5) as $i) {
            $data['usg_ibu_'.$i] = '';
        }

        return $data;
    }

    private function checkboxKehamilan(): array
    {
        return [
            'penyakit_hamil_anemia', 'penyakit_hamil_jantung', 'penyakit_hamil_hipertensi',
            'penyakit_hamil_tb', 'penyakit_hamil_diabetes', 'penyakit_hamil_sifilis', 'penyakit_hamil_lain',
            'komplikasi_pendarahan', 'komplikasi_peb', 'komplikasi_infeksi', 'komplikasi_disproporsi', 'komplikasi_lain',
        ];
    }

    private function validated(Request $request): array
    {
        $rules = [
            'tanggal_pengkajian' => 'required|date|before_or_equal:today',
            'jam_pengkajian' => 'required|date_format:H:i',
            'nama_bayi' => 'required|string|max:100',
            'tanggal_lahir_bayi' => 'required|date|before_or_equal:today',
            'jam_lahir_bayi' => 'required|date_format:H:i',
            'jenis_kelamin_bayi' => 'required|in:L,P',
            'berat_badan_lahir' => 'required|numeric|min:0.3|max:6',
            'tinggi_badan_lahir' => 'required|numeric|min:30|max:70',
            'lingkar_kepala_bayi' => 'required|numeric|min:20|max:50',
            'denyut_jantung_bayi' => 'required|integer|min:0|max:300',
            'pernapasan_bayi' => 'required|integer|min:0|max:150',
            'suhu_rectal_bayi' => 'required|numeric|min:30|max:45',
            'tanda_pengenal_bayi' => 'required|string|max:255',
            'tempat_lahir_bayi' => 'required|string|max:255',
            'nama_ibu' => 'required|string|max:100',
            'umur_ibu' => 'required|string|max:20',
            'agama_ibu' => ['required', Rule::in(array_column(SelectOption::get('agama'), 'value'))],
            'pendidikan_ibu' => ['required', Rule::in(array_column(SelectOption::get('pendidikan'), 'value'))],
            'pekerjaan_ibu' => ['required', Rule::in(array_column(SelectOption::get('pekerjaan'), 'value'))],
            'alamat_ibu' => 'required|string|max:2000',
            'nama_ayah' => 'required|string|max:100',
            'umur_ayah' => 'required|string|max:20',
            'agama_ayah' => ['nullable', Rule::in(array_column(SelectOption::get('agama'), 'value'))],
            'pendidikan_ayah' => ['nullable', Rule::in(array_column(SelectOption::get('pendidikan'), 'value'))],
            'pekerjaan_ayah' => ['nullable', Rule::in(array_column(SelectOption::get('pekerjaan'), 'value'))],
            'tinggi_ibu' => 'required|numeric|min:100|max:220',
            'berat_ibu' => 'required|numeric|min:20|max:250',
            'presentasi_bayi' => 'required|string|max:100',
            'antenatal_care' => 'required|in:1,2',
            'lokasi_antenatal_care' => 'required_if:antenatal_care,1|nullable|string|max:255',
            'hpht' => 'required|date|before_or_equal:today',
            'taksiran_partus' => 'required|date',
            'imunisasi_hamil' => 'required|in:Lengkap,Tidak Lengkap',
            'penyakit_hamil_lainnya' => 'required_if:penyakit_hamil_lain,1|nullable|string|max:255',
            'komplikasi_lainnya' => 'required_if:komplikasi_lain,1|nullable|string|max:255',
            'golongan_darah_ibu' => ['required', Rule::in(array_column(SelectOption::get('golongan_darah'), 'value'))],
            'hb_ibu' => 'required|numeric|min:0|max:30',
            'leukosit_ibu' => 'required|numeric|min:0|max:100',
            'gula_darah_ibu' => 'required|numeric|min:0|max:500',
            'urine_lengkap_ibu' => 'required|string|max:255',
            'tgl_bab_pertama' => 'nullable|date',
            'jam_bab_pertama' => 'nullable|date_format:H:i',
            'tgl_bak_pertama' => 'nullable|date',
            'jam_bak_pertama' => 'nullable|date_format:H:i',
            'penyakit_hamil_anemia' => 'nullable|in:0,1',
            'penyakit_hamil_jantung' => 'nullable|in:0,1',
            'penyakit_hamil_hipertensi' => 'nullable|in:0,1',
            'penyakit_hamil_tb' => 'nullable|in:0,1',
            'penyakit_hamil_diabetes' => 'nullable|in:0,1',
            'penyakit_hamil_sifilis' => 'nullable|in:0,1',
            'penyakit_hamil_lain' => 'nullable|in:0,1',
            'komplikasi_pendarahan' => 'nullable|in:0,1',
            'komplikasi_peb' => 'nullable|in:0,1',
            'komplikasi_infeksi' => 'nullable|in:0,1',
            'komplikasi_disproporsi' => 'nullable|in:0,1',
            'komplikasi_lain' => 'nullable|in:0,1',
            // Butir SAP & pendukung (opsional).
            'kepala_lain' => 'nullable|string|max:255',
            'telinga_lain' => 'nullable|string|max:255',
            'mulut_lain' => 'nullable|string|max:255',
            'punggung_lain' => 'nullable|string|max:255',
            'anus_lain' => 'nullable|string|max:255',
            'genitalia_pria_lain' => 'nullable|string|max:255',
            'warna_kulit' => 'nullable|in:Merah Muda,Pucat,Sianosis,Ikterus,Vernik,Rambut Lanugo,Rashes,Hidrasi',
            'pergerakan_bayi' => 'nullable|in:Aktif,Lemah',
            'meconium' => 'nullable|in:Ada,Tidak Ada',
            'dada_bayi' => 'nullable|in:Simetris,Tidak Simetris',
            'perut_bayi' => 'nullable|in:Lembek,Kembung,Teraba Hernia',
            'umbilikal' => 'nullable|in:Normal,Omphalocel',
            'bising_usus' => 'nullable|in:Ada,Tidak Ada',
            'jari_tangan' => 'nullable|in:Normal,Polidactili,Syndactili',
            'pergerakan_tangan' => 'nullable|in:Aktif,Lemah',
            'garis_tangan' => 'nullable|in:Jelas,Tidak Jelas',
            'tangan_keriput' => 'nullable|in:Keriput,Tidak Keriput',
            'tangan_terkelupas' => 'nullable|in:Terkelupas,Tidak Terkelupas',
            'jari_kaki' => 'nullable|in:Normal,Polidactili,Syndactili',
            'pergerakan_kaki' => 'nullable|in:Aktif,Lemah',
            'garis_kaki' => 'nullable|in:Jelas,Tidak Jelas',
            'kaki_keriput' => 'nullable|in:Keriput,Tidak Keriput',
            'kaki_terkelupas' => 'nullable|in:Terkelupas,Tidak Terkelupas',
            'kuku' => 'nullable|in:Panjang,Pendek',
            'labio_mayora' => 'nullable|in:Normal,Tidak Normal',
            'labio_minora' => 'nullable|in:Normal,Tidak Normal',
        ];

        for ($i = 1; $i <= 5; $i++) {
            $rules['usg_ibu_'.$i] = 'nullable|string|max:255';
        }

        // SAP neonatal head-to-toe (opsional, opsi per grup).
        for ($i = 1; $i <= 5; $i++) {
            $rules['kepala_'.$i] = 'nullable|in:Normal,Tidak Normal,Caput Succedaneum,Cephal Hematom,Lainnya';
            $rules['mata_'.$i] = 'nullable|in:Normal,Simetris,Tidak Simetris,Sclera Kuning,Perdarahan';
        }
        for ($i = 1; $i <= 3; $i++) {
            $rules['ubun_ubun_'.$i] = 'nullable|in:Besar,Cekung,Cembung';
            $rules['telinga_'.$i] = 'nullable|in:Simetris,Tidak Simetris,Lainnya';
            $rules['leher_'.$i] = 'nullable|in:Pergerakan Aktif,Kaku,Tidak Bereaksi';
            $rules['menangis_'.$i] = 'nullable|in:Kuat,Lemah,Merintih';
            $rules['anus_'.$i] = 'nullable|in:Normal,Atresia Ani,Lainnya';
            $rules['keluaran_genitalia_'.$i] = 'nullable|in:Tidak Ada Pengeluaran,Lendir,Darah';
        }
        for ($i = 1; $i <= 5; $i++) {
            $rules['mulut_'.$i] = 'nullable|in:Simetris,Tidak Simetris,Labio Schizis,Palato Schizis,Lainnya';
            $rules['genitalia_pria_'.$i] = 'nullable|in:Normal,Hipospadia,Phimosis,Episdalia,Lainnya';
        }
        for ($i = 1; $i <= 4; $i++) {
            $rules['hidung_'.$i] = 'nullable|in:Lubang Ada,Tidak Ada,Kotor,Tidak Kotor';
        }
        for ($i = 1; $i <= 6; $i++) {
            $rules['punggung_'.$i] = 'nullable|in:Normal,Kifosis,Lordosis,Skoliosis,Spina Bifida,Lainnya';
        }

        $validator = \Validator::make($request->all(), $rules);
        $validator->validate();

        $data = array_merge(array_fill_keys(array_keys($this->kosong()), null), $validator->validated());

        // Normalisasi checkbox "0"/"1".
        foreach ($this->checkboxKehamilan() as $k) {
            $data[$k] = ($data[$k] ?? null) === '1' ? '1' : '0';
        }

        // Field kondisional.
        if (($data['antenatal_care'] ?? '') !== '1') {
            $data['lokasi_antenatal_care'] = null;
        }
        if (($data['penyakit_hamil_lain'] ?? '0') !== '1') {
            $data['penyakit_hamil_lainnya'] = null;
        }
        if (($data['komplikasi_lain'] ?? '0') !== '1') {
            $data['komplikasi_lainnya'] = null;
        }

        // Baris USG kosong dinode-kan null (tanpa unset — mapping tetap lengkap).
        foreach (range(1, 5) as $i) {
            if (trim((string) ($data['usg_ibu_'.$i] ?? '')) === '') {
                $data['usg_ibu_'.$i] = null;
            }
        }

        return $data;
    }
}
