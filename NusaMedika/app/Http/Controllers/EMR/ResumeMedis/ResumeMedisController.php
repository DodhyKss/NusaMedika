<?php

namespace App\Http\Controllers\EMR\ResumeMedis;

use App\Helpers\AksesEhr;
use App\Helpers\EmrHelper;
use App\Helpers\EwsHelper;
use App\Helpers\SelectOption;
use App\Http\Controllers\Controller;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * Form Resume Medis (form 16, slug `resume_medis`).
 *
 * Ringkasan satu episode perawatan: kondisi awal, pemeriksaan fisik, diagnosa,
 * dan kondisi saat pulang. Field computed (EWS, GCS) SELALU dihitung ulang di
 * server — nilai dari browser dibuang.
 */
class ResumeMedisController extends Controller
{
    private const SLUG = 'resume_medis';

    /** Pemeriksaan fisik: 12 organ -> satu objek 188, 12 variabel bersuffix. */
    private const PF = [
        'pf_kepala', 'pf_mata', 'pf_tht', 'pf_gigidanmulut', 'pf_leher', 'pf_toraks',
        'pf_jantung', 'pf_paru', 'pf_abdomen', 'pf_kelenjar', 'pf_genitalia',
        'pf_ekstremitas',
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

        return view('moduls.EMR.ResumeMedis.index', compact(
            'registrasi_detail', 'riwayat', 'historyGrouped', 'emr_data',
            'formAction', 'isEdit', 'deleteAction', 'isView', 'emr_id', 'aksesCrud'
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

            return redirect()->back()->with('success', 'Resume Medis berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan Resume Medis: '.$e->getMessage())->withInput();
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

            return redirect()->back()->with('success', 'Resume Medis berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui Resume Medis: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Resume Medis berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus Resume Medis: '.$e->getMessage());
        }
    }

    /**
     * Nilai kosong untuk form baru. `kondisi_saat_pulang` default 'Sembuh'.
     */
    private function kosong(): array
    {
        $d = [
            'tanggal_resume' => '', 'waktu_resume' => now()->format('H:i'),
            'keluhan_utama' => '', 'indikasi_rawat_inap' => '',
            'riwayat_kesehatan_saat_ini' => '', 'riwayat_penyakit_dahulu' => '',
            'td_sistolik' => '', 'td_diastolik' => '', 'nadi' => '',
            'pernapasan' => '', 'suhu' => '', 'skor_nyeri' => '',
            'gcs_e' => '', 'gcs_m' => '', 'gcs_v' => '',
            'diagnosa_primer' => '', 'diagnosa_sekunder' => '', 'prosedur' => '',
            'komplikasi' => '', 'kondisi_saat_pulang' => 'Sembuh',
            'catatan_alasan_pulang' => '', 'dirujuk_ke' => '', 'meninggal' => '',
            'catatan_kematian' => '',
        ];

        foreach (self::PF as $pf) {
            $d[$pf] = '';
        }

        return $d;
    }

    private function validated(Request $request): array
    {
        $opsional = [
            'indikasi_rawat_inap' => null,
            'diagnosa_sekunder' => null,
            'prosedur' => null,
            'komplikasi' => null,
            'dirujuk_ke' => null,
            'meninggal' => null,
            'catatan_kematian' => null,
        ];

        foreach (self::PF as $pf) {
            $opsional[$pf] = null;
        }

        return array_merge($opsional, $request->validate([
            'tanggal_resume' => 'required|date',
            'waktu_resume' => 'required|date_format:H:i',
            'keluhan_utama' => 'required|string|max:1000',
            'riwayat_kesehatan_saat_ini' => 'required|string|max:2000',
            'riwayat_penyakit_dahulu' => 'required|string|max:2000',
            'indikasi_rawat_inap' => 'nullable|string|max:1000',

            'td_sistolik' => 'required|integer|min:40|max:300',
            'td_diastolik' => 'required|integer|min:20|max:200',
            'nadi' => 'required|integer|min:20|max:250',
            'pernapasan' => 'required|integer|min:5|max:60',
            'suhu' => 'required|numeric|min:30|max:45',
            'skor_nyeri' => 'required|integer|min:0|max:10',
            'gcs_e' => 'required|integer|min:1|max:4',
            'gcs_m' => 'required|integer|min:1|max:6',
            'gcs_v' => 'required|integer|min:1|max:5',

            'diagnosa_primer' => 'required|string|max:2000',
            'diagnosa_sekunder' => 'nullable|string|max:2000',
            'prosedur' => 'nullable|string|max:2000',
            'komplikasi' => 'nullable|string|max:2000',

            'kondisi_saat_pulang' => ['required', Rule::in(array_column(SelectOption::get('alasan_pulang'), 'value'))],
            'catatan_alasan_pulang' => 'required|string|max:1000',

            'dirujuk_ke' => 'nullable',
            'meninggal' => 'nullable',
            'catatan_kematian' => 'nullable|string|max:1000',
        ] + array_fill_keys(self::PF, 'nullable|string|max:1000')));
    }

    /**
     * Hanya field terpetakan yang disimpan. Field computed (EWS, GCS) dihitung
     * ulang di sini; field kondisional yang tidak berlaku dibuang supaya tidak
     * ada baris yatim di emr_detail.
     */
    private function filteredData(array $data, int $formId): array
    {
        $data = array_intersect_key($data, array_flip(EmrHelper::objekVariabels($formId)));

        // EWS — hitung ulang dari parameter yang ada di form ini. Resume tidak punya
        // input saturasi/oksigen/kesadaran, jadi ketiganya ditandai "tidak
        // diukur" (bukan diisi 0 — 0 berarti normal, sedangkan tidak diketahui
        // tidak sama dengan normal). Nilai dari browser tidak pernah dipakai.
        $ews = EwsHelper::hitung([
            'pernapasan' => $data['pernapasan'] ?? null,
            'saturasi' => null,
            'oksigen' => null,
            'td_sistolik' => $data['td_sistolik'] ?? null,
            'nadi' => $data['nadi'] ?? null,
            'kesadaran' => null,
            'suhu' => $data['suhu'] ?? null,
        ], ['saturasi', 'oksigen', 'kesadaran']);

        // Skor parsial bisa terlalu rendah dan menyesatkan -> hanya simpan bila
        // semua parameter terisi atau ditandai tidak diukur.
        $data['total_ews'] = $ews['lengkap'] ? $ews['total'] : null;
        $data['kategori_ews'] = $ews['lengkap'] ? $ews['kategori'] : null;

        // GCS total = E + M + V (dihitung ulang, bukan dari browser).
        $e = (int) ($data['gcs_e'] ?? 0);
        $m = (int) ($data['gcs_m'] ?? 0);
        $v = (int) ($data['gcs_v'] ?? 0);
        $data['gcs_jumlah'] = ($e && $m && $v) ? $e + $m + $v : null;

        // Aturan kondisional kondisi pulang: buang yang tidak berlaku.
        if (($data['kondisi_saat_pulang'] ?? '') !== 'Dirujuk') {
            $data['dirujuk_ke'] = null;
        }
        if (($data['kondisi_saat_pulang'] ?? '') !== 'Meninggal') {
            $data['meninggal'] = null;
            $data['catatan_kematian'] = null;
        }

        return $data;
    }
}
