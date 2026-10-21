<?php

namespace App\Http\Controllers\EMR\PengkajianKeperawatanKritis;

use App\Helpers\AksesEhr;
use App\Helpers\AsesmenKeperawatanHelper;
use App\Helpers\EmrHelper;
use App\Helpers\SelectOption;
use App\Http\Controllers\Controller;
use App\Models\ICD;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * Form Pengkajian Keperawatan Kritis (form 35, slug `pengkajian_keperawatan_kritis`).
 *
 * Pengkajian thorough dengan pendekatan ABCDE. Fokusnya RINGKASAN ABCDE yang
 * belum tertangkap Tanda Vital (form 13) dan Tanda Vital-kritis.
 *
 * Dua field turunan, `gcs_jumlah` dan `skor_nyeri_kritis`, SELALU dihitung ulang
 * di server. Skor nyeri memakai VAS atau FLACC sesuai `metode_skala_nyeri_kritis`
 * - kelima butir FLACC tidak punya objek sendiri (risiko R6 pada
 * docs/KONSEP_ASUHAN_KEPERAWATAN_LANJUTAN.md) karena nilainya sudah terwakili
 * oleh objek 282, jadi HARUS dibaca sebelum `array_intersect_key()` membuangnya.
 *
 * `asal_masuk_detail` hanya berlaku bila asal masuk = Lainnya; selain itu
 * field itu dibuang di `filteredData()`.
 */
class PengkajianKeperawatanKritisController extends Controller
{
    private const SLUG = 'pengkajian_keperawatan_kritis';

    /**
     * Field bantu skala nyeri: TIDAK punya objek, hanya dipakai untuk menghitung
     * `skor_nyeri_kritis` (objek 282).
     */
    private const BANTU_NYERI = [
        'metode_skala_nyeri_kritis', 'skor_vas_kritis',
        'flacc_wajah', 'flacc_kaki', 'flacc_aktivitas', 'flacc_menangis', 'flacc_konsolabilitas',
    ];

    public function index($registrasi_detail_id, $emr_id = null)
    {
        $registrasi_detail = RegistrasiDetail::with('registrasi.pasien')->findOrFail($registrasi_detail_id);

        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'read'), 403);

        $aksesCrud = AksesEhr::flags((int) $form_id);
        $riwayat = EmrHelper::emrList((int) $form_id, (int) $registrasi_detail->registrasi_id);
        $historyGrouped = EmrHelper::historyKunjunganGrouped($registrasi_detail);

        // Tidak boleh membuat data baru -> tampilkan riwayat terakhir.
        if (empty($emr_id) && ! ($aksesCrud['create'] ?? false) && $riwayat->isNotEmpty()) {
            return redirect()->route('emr.dynamic.index', [
                'form_name' => self::SLUG,
                'registrasi_detail_id' => $registrasi_detail_id,
                'emr_id' => $riwayat->first()->emr_id,
                'action' => 'view',
            ]);
        }

        $icdList = ICD::aktif()->orderBy('kode_diagnosa')->get(['icd_id', 'kode_diagnosa', 'nama_diagnosa']);
        $itemFlacc = AsesmenKeperawatanHelper::flaccItems();

        if (empty($emr_id)) {
            $emr_data = EmrHelper::wrapData($this->kosong());
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

        return view('moduls.EMR.PengkajianKeperawatanKritis.index', compact(
            'registrasi_detail', 'riwayat', 'historyGrouped', 'emr_data',
            'formAction', 'isEdit', 'deleteAction', 'isView', 'emr_id', 'aksesCrud',
            'icdList', 'itemFlacc'
        ));
    }

    public function store(Request $request, $registrasi_detail_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'create'), 403);

        $data = $this->validated($request);

        $user = Auth::user();
        if (! $user || ! $user->pegawai_id || ! $user->user_id) {
            return redirect()->back()->with('error', 'Sesi Anda Telah Habis Silahkan Login Kembali!');
        }

        try {
            EmrHelper::insert((int) $form_id, $this->filteredData($data, (int) $form_id), (int) $registrasi_detail_id);

            return redirect()->back()->with('success', 'Pengkajian Keperawatan Kritis berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan Pengkajian Keperawatan Kritis: '.$e->getMessage())->withInput();
        }
    }

    public function update(Request $request, $registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'update'), 403);

        $data = $this->validated($request);

        try {
            EmrHelper::update((int) $emr_id, (int) $form_id, $this->filteredData($data, (int) $form_id));

            return redirect()->back()->with('success', 'Pengkajian Keperawatan Kritis berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui Pengkajian Keperawatan Kritis: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Pengkajian Keperawatan Kritis berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus Pengkajian Keperawatan Kritis: '.$e->getMessage());
        }
    }

    /**
     * Nilai kosong untuk form baru (termasuk field bantu nyeri yang tidak punya
     * objek, supaya blade tidak pernah "Undefined array key").
     */
    private function kosong(): array
    {
        $d = [
            'asal_masuk' => '', 'asal_masuk_detail' => '', 'diagnosa_medis_kritis' => '',
            'jalan_nafas' => '', 'deviasi_trakea' => '', 'pernapasan_kritis' => '',
            'suara_nafas_terdengar' => '', 'sirkulasi_perfusi' => '', 'kesadaran_kritis' => '',
            'gcs_e' => '', 'gcs_m' => '', 'gcs_v' => '', 'gcs_jumlah' => '',
            'skor_nyeri_kritis' => '', 'nilai_kritis' => '',
            'eksposisi_kritis' => '', 'catatan_kritis' => '',
            'masalah_keperawatan_kritis' => '', 'dpo' => '', 'alergi' => '',
        ];

        foreach (self::BANTU_NYERI as $variabel) {
            $d[$variabel] = '';
        }

        $d['flacc_wajah'] = '0';
        $d['flacc_kaki'] = '0';
        $d['flacc_aktivitas'] = '0';
        $d['flacc_menangis'] = '0';
        $d['flacc_konsolabilitas'] = '0';

        return $d;
    }

    private function validated(Request $request): array
    {
        $opsional = [
            'asal_masuk_detail' => null,
            'catatan_kritis' => null,
            'alergi' => null,
            'dpo' => null,
            'gcs_jumlah' => null,
            'skor_nyeri_kritis' => null,
            'metode_skala_nyeri_kritis' => null,
            'skor_vas_kritis' => null,
            'flacc_wajah' => null,
            'flacc_kaki' => null,
            'flacc_aktivitas' => null,
            'flacc_menangis' => null,
            'flacc_konsolabilitas' => null,
        ];

        $flaccRules = [];
        foreach (array_keys(AsesmenKeperawatanHelper::flaccItems()) as $variabel) {
            $flaccRules[$variabel] = 'nullable|integer|between:0,2';
        }

        return array_merge($opsional, $request->validate([
            'asal_masuk' => ['required', Rule::in(array_column(SelectOption::get('asal_masuk_kritis'), 'value'))],
            'asal_masuk_detail' => ['nullable', 'required_if:asal_masuk,Lainnya', 'string', 'max:200'],
            'diagnosa_medis_kritis' => ['required', 'integer', 'exists:icd,icd_id'],

            'jalan_nafas' => ['required', Rule::in(array_column(SelectOption::get('jalan_nafas_kritis'), 'value'))],
            'deviasi_trakea' => ['required', Rule::in(array_column(SelectOption::get('ya_tidak_kapital'), 'value'))],
            'pernapasan_kritis' => ['required', Rule::in(array_column(SelectOption::get('pernapasan_kritis'), 'value'))],
            'suara_nafas_terdengar' => ['required', 'array', 'min:1'],
            'suara_nafas_terdengar.*' => 'string|max:255',
            'sirkulasi_perfusi' => ['required', Rule::in(array_column(SelectOption::get('sirkulasi_perfusi'), 'value'))],

            'kesadaran_kritis' => ['required', Rule::in(array_column(SelectOption::get('kesadaran_kritis'), 'value'))],
            'gcs_e' => 'required|integer|between:1,4',
            'gcs_m' => 'required|integer|between:1,6',
            'gcs_v' => 'required|integer|between:1,5',

            'metode_skala_nyeri_kritis' => ['nullable', Rule::in(array_column(SelectOption::get('metode_skala_nyeri'), 'value'))],
            'skor_vas_kritis' => 'nullable|integer|between:0,10',

            'nilai_kritis' => ['required', Rule::in(array_column(SelectOption::get('nilai_kritis'), 'value'))],
            'eksposisi_kritis' => 'required|string|max:2000',
            'catatan_kritis' => 'nullable|string|max:2000',
            'masalah_keperawatan_kritis' => 'required|string|max:255',
            'dpo' => 'nullable',
            'alergi' => 'nullable|string|max:500',
        ] + $flaccRules));
    }

    /**
     * Hanya field terpetakan yang disimpan. Field bantu nyeri dibaca SEBELUM
     * `array_intersect_key()` membuangnya, lalu `gcs_jumlah` dan
     * `skor_nyeri_kritis` dihitung ulang dari jawaban mentah.
     */
    private function filteredData(array $data, int $formId): array
    {
        // 1. Bantu nyeri (tidak terpetakan) harus dibaca lebih dulu.
        $metode = (string) ($data['metode_skala_nyeri_kritis'] ?? 'VAS');
        $skorNyeri = $metode === 'FLACC'
            ? AsesmenKeperawatanHelper::hitungFlacc($data)
            : ($data['skor_vas_kritis'] ?? null);

        // 2. Sisa field dibatasi ke yang benar-benar terpetakan.
        $data = array_intersect_key($data, array_flip(EmrHelper::objekVariabels($formId)));

        // 3. Field turunan ditulis ulang, nilai browser dibuang.
        $e = (int) ($data['gcs_e'] ?? 0);
        $m = (int) ($data['gcs_m'] ?? 0);
        $v = (int) ($data['gcs_v'] ?? 0);
        $data['gcs_jumlah'] = ($e && $m && $v) ? $e + $m + $v : null;

        $data['skor_nyeri_kritis'] = ($skorNyeri === null || $skorNyeri === '')
            ? null
            : (int) $skorNyeri;

        // 4. Aturan kondisional asal masuk.
        if (($data['asal_masuk'] ?? '') !== 'Lainnya') {
            $data['asal_masuk_detail'] = null;
        }

        // 5. Checkbox DPO dinormalisasi supaya variabelnya selalu ada.
        $data['dpo'] = ($data['dpo'] ?? null) ? 'Ya' : 'Tidak';

        return $data;
    }
}
