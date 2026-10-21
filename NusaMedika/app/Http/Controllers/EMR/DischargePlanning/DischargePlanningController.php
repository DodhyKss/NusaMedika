<?php

namespace App\Http\Controllers\EMR\DischargePlanning;

use App\Helpers\AksesEhr;
use App\Helpers\EmrHelper;
use App\Http\Controllers\Controller;
use App\Models\ICD;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * Form Discharge Planning (form 17, slug `discharge_planning`).
 *
 * Penilaian kesiapan pulang: pengaruh perubahan kondisi, kebutuhan bantuan
 * sehari-hari (ADL), kebutuhan edukasi, keterampilan khusus, dan rencana
 * tanggal pulang. Form ini diisi berulang selama episode perawatan, bukan
 * hanya saat pasien pulang.
 *
 * Tanggal & jam masuk RS SELALU diset dari master `registrasi` di server -
 * nilai dari browser tidak dipercaya. Checkbox ADL & edukasi dinormalisasi
 * menjadi "Ya"/"Tidak" supaya tidak ada baris kosong di `emr_detail`.
 */
class DischargePlanningController extends Controller
{
    private const SLUG = 'discharge_planning';

    /** Slug form 16 (Resume Medis) - sumber prefill diagnosa medis. */
    private const SLUG_RESUME = 'resume_medis';

    /** Checkbox yang dinormalisasi menjadi "Ya"/"Tidak" di filteredData(). */
    private const CHECKBOX = [
        'adl_menyiapkan_makanan', 'adl_makan', 'adl_diet', 'adl_menyiapkan_obat',
        'adl_minum_obat', 'adl_mandi', 'adl_berpakaian', 'adl_transportasi',
        'adl_edukasi_kesehatan', 'edukasi_obat_obat', 'edukasi_nutrisi',
        'edukasi_perawatan_luka', 'edukasi_mobilisasi', 'edukasi_manajemen_nyeri',
        'edukasi_insulin_sc',
    ];

    /** Blok E - seluruhnya dibuang bila `perlu_edukasi` = "Tidak". */
    private const BLOK_EDUKASI = [
        'edukasi_obat_obat', 'edukasi_nutrisi', 'edukasi_perawatan_luka',
        'edukasi_mobilisasi', 'edukasi_manajemen_nyeri', 'edukasi_insulin_sc',
        'edukasi_lain_1', 'edukasi_lain_2',
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

        if (empty($emr_id)) {
            $kosong = $this->kosong();

            // Tanggal/jam admisi diambil dari master registrasi, diagnosa medis
            // dicoba isi dari diagnosa primer resume medis terakhir.
            $admisi = $this->dataAdmisi($registrasi_detail);
            $kosong['tanggal_masuk_rs'] = $admisi['tanggal_masuk_rs'];
            $kosong['waktu_masuk_rs'] = $admisi['waktu_masuk_rs'];
            $kosong['diagnosis_medis'] = $this->diagnosisPrefill((int) $registrasi_detail_id) ?? '';

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

        return view('moduls.EMR.DischargePlanning.index', compact(
            'registrasi_detail', 'riwayat', 'historyGrouped', 'emr_data',
            'formAction', 'isEdit', 'deleteAction', 'isView', 'emr_id', 'aksesCrud',
            'icdList'
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
            EmrHelper::insert(
                (int) $form_id,
                $this->filteredData($data, $request, (int) $form_id, (int) $registrasi_detail_id),
                (int) $registrasi_detail_id
            );

            return redirect()->back()->with('success', 'Discharge Planning berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan Discharge Planning: '.$e->getMessage())->withInput();
        }
    }

    public function update(Request $request, $registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'update'), 403);

        $data = $this->validated($request);

        try {
            EmrHelper::update(
                (int) $emr_id,
                (int) $form_id,
                $this->filteredData($data, $request, (int) $form_id, (int) $registrasi_detail_id)
            );

            return redirect()->back()->with('success', 'Discharge Planning berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui Discharge Planning: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Discharge Planning berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus Discharge Planning: '.$e->getMessage());
        }
    }

    /**
     * Tanggal & jam admisi dari master `registrasi` (bukan dari browser).
     *
     * @return array{tanggal_masuk_rs: string, waktu_masuk_rs: string}
     */
    private function dataAdmisi($registrasiDetail): array
    {
        $tglMasuk = $registrasiDetail->registrasi->tgl_masuk ?? null;

        if (! $tglMasuk) {
            return ['tanggal_masuk_rs' => '', 'waktu_masuk_rs' => ''];
        }

        $masuk = Carbon::parse($tglMasuk);

        return [
            'tanggal_masuk_rs' => $masuk->format('Y-m-d'),
            'waktu_masuk_rs' => $masuk->format('H:i'),
        ];
    }

    /**
     * Prefill `icd_id` dari diagnosa primer resume medis (form 16) terakhir.
     * Dicocokkan ke master ICD; bila tidak ketemu dikosongkan saja - petugas
     * tetap wajib memilih sendiri.
     */
    private function diagnosisPrefill(int $registrasiDetailId): ?int
    {
        $formId = EmrHelper::formIdBySlug(self::SLUG_RESUME);

        if (! $formId) {
            return null;
        }

        $terakhir = EmrHelper::latestValuesByVariabel((int) $formId, $registrasiDetailId, ['diagnosa_primer']);
        $nama = trim((string) ($terakhir['diagnosa_primer'] ?? ''));

        if ($nama === '') {
            return null;
        }

        $icd = ICD::aktif()->where('nama_diagnosa', $nama)->first();

        return $icd ? (int) $icd->icd_id : null;
    }

    /**
     * Nilai kosong untuk form baru.
     */
    private function kosong(): array
    {
        return [
            'tanggal_masuk_rs' => '', 'waktu_masuk_rs' => '', 'diagnosis_medis' => '',
            'estimasi_hari_rawat' => '', 'tanggal_ren_pulang' => '', 'waktu_ren_pulang' => '',
            'pengaruh_pasien_kel' => '', 'pengaruh_kerja' => '', 'pengaruh_keuangan' => '',
            'antisipati_masalah' => '',
            'tinggal_sendiri' => '', 'membantu_pasien' => '', 'gunakan_alat_medis' => '',
            'perlu_alat_bantu' => '', 'perlu_perawatan_khusus' => '',
            'masalah_kebutuhan_pribadi' => '', 'nyeri_kronis' => '',
            'perlu_edukasi' => '', 'keterampilan_khusus' => '',
            'adl_menyiapkan_makanan' => '', 'adl_makan' => '', 'adl_diet' => '',
            'adl_menyiapkan_obat' => '', 'adl_minum_obat' => '', 'adl_mandi' => '',
            'adl_berpakaian' => '', 'adl_transportasi' => '', 'adl_edukasi_kesehatan' => '',
            'adl_edukasi_lain' => '',
            'edukasi_obat_obat' => '', 'edukasi_nutrisi' => '', 'edukasi_perawatan_luka' => '',
            'edukasi_mobilisasi' => '', 'edukasi_manajemen_nyeri' => '', 'edukasi_insulin_sc' => '',
            'edukasi_lain_1' => '', 'edukasi_lain_2' => '',
            'catatan' => '',
        ];
    }

    private function validated(Request $request): array
    {
        $opsional = [
            'waktu_ren_pulang' => null,
            'adl_edukasi_lain' => null,
            'edukasi_lain_1' => null,
            'edukasi_lain_2' => null,
            'catatan' => null,
        ];

        return array_merge($opsional, $request->validate([
            'tanggal_masuk_rs' => 'required|date',
            'diagnosis_medis' => ['required', 'integer', 'exists:icd,icd_id'],
            'estimasi_hari_rawat' => 'required|integer|min:0|max:365',
            'tanggal_ren_pulang' => 'required|date|after_or_equal:tanggal_masuk_rs',
            'waktu_ren_pulang' => 'nullable|date_format:H:i',

            'pengaruh_pasien_kel' => ['required', Rule::in(['Ya', 'Tidak'])],
            'pengaruh_kerja' => ['required', Rule::in(['Ya', 'Tidak'])],
            'pengaruh_keuangan' => ['required', Rule::in(['Ya', 'Tidak'])],

            'antisipati_masalah' => 'required|string|max:2000',
            'tinggal_sendiri' => ['required', Rule::in(['Ya', 'Tidak'])],
            'membantu_pasien' => ['required', Rule::in(['Ya', 'Tidak'])],
            'gunakan_alat_medis' => ['required', Rule::in(['Ya', 'Tidak'])],
            'perlu_alat_bantu' => ['required', Rule::in(['Ya', 'Tidak'])],
            'perlu_perawatan_khusus' => ['required', Rule::in(['Ya', 'Tidak'])],
            'masalah_kebutuhan_pribadi' => ['required', Rule::in(['Ya', 'Tidak'])],
            'nyeri_kronis' => ['required', Rule::in(['Ya', 'Tidak'])],
            'perlu_edukasi' => ['required', Rule::in(['Ya', 'Tidak'])],
            'keterampilan_khusus' => ['required', Rule::in(['Ya', 'Tidak'])],

            'catatan' => 'nullable|string|max:2000',
        ]));
    }

    /**
     * Hanya field terpetakan yang disimpan. Checkbox dinormalisasi "Ya"/"Tidak",
     * tanggal/jam admisi ditulis ulang dari master, dan blok yang tidak berlaku
     * dibuang supaya tidak ada baris yatim di `emr_detail`.
     */
    private function filteredData(array $data, Request $request, int $formId, int $registrasiDetailId): array
    {
        $data = array_intersect_key($data, array_flip(EmrHelper::objekVariabels($formId)));

        // Checkbox tidak ikut divalidasi satu per satu - dibaca dari presence
        // di request. Kolom yang kosong TIDAK disimpan sebagai string kosong.
        foreach (self::CHECKBOX as $flag) {
            $data[$flag] = $request->boolean($flag) ? 'Ya' : 'Tidak';
        }

        // Tanggal & jam masuk RS berasal dari master registrasi.
        $registrasiDetail = RegistrasiDetail::with('registrasi')->find($registrasiDetailId);
        if ($registrasiDetail) {
            $data = array_merge($data, $this->dataAdmisi($registrasiDetail));
        }

        // Perlu edukasi = "Tidak" -> seluruh blok edukasi dibuang.
        if (($data['perlu_edukasi'] ?? '') !== 'Ya') {
            foreach (self::BLOK_EDUKASI as $variabel) {
                $data[$variabel] = null;
            }
        }

        return $data;
    }
}
