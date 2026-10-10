<?php

namespace App\Http\Controllers\EMR\PemulanganPasien;

use App\Helpers\AksesEhr;
use App\Helpers\EmrHelper;
use App\Helpers\EwsHelper;
use App\Helpers\SelectOption;
use App\Http\Controllers\Controller;
use App\Models\Implementasi;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * Form Pemulangan Pasien (form 18, slug `pemulangan_pasien`).
 *
 * Dokumen yang dibawa pulang: kondisi klinis saat pulang, hasil pemeriksaan,
 * anjuran pulang, barang & hasil yang diserahkan, masalah keperawatan selama
 * dirawat, dan rencana kontrol.
 *
 * Field TURUNAN - `total_ews`, `kategori_ews`, `gcs_jumlah` - SELALU dihitung
 * ulang di server dari input yang sama (nilai dari browser dibuang), sehingga
 * angkanya tidak bisa dimanipulasi dari sisi klien. `tanggal_pulang` dan
 * `waktu_pulang` juga diset server dari waktu saat dokumen disimpan.
 */
class PemulanganPasienController extends Controller
{
    private const SLUG = 'pemulangan_pasien';

    /** Slug form 17 (Discharge Planning) - sumber penentu blok Manajemen Nyeri. */
    private const SLUG_DISCHARGE = 'discharge_planning';

    /** Checkbox yang dinormalisasi menjadi "Ya"/"Tidak" di filteredData(). */
    private const CHECKBOX = [
        'serah_surat_asuransi', 'serah_resume', 'serah_buku_bayi',
        'serah_gol_darah', 'serah_skl_bayi', 'edukasi_1', 'edukasi_2',
        'edukasi_3', 'edukasi_4', 'edukasi_5', 'edukasi_6', 'edukasi_7',
    ];

    /** Opsi select yang belum punya key di SelectOption (lihat SelectOption.php). */
    private const OPSI = [
        'diet_jenis' => [
            ['value' => 'Oral', 'label' => 'Oral'],
            ['value' => 'NGT', 'label' => 'Nasogastric Tube (NGT)'],
            ['value' => 'Diet Khusus', 'label' => 'Diet Khusus'],
        ],
        'eliminasi_bab' => [
            ['value' => 'Normal', 'label' => 'Normal'],
            ['value' => 'Ileostomy / Colostomy', 'label' => 'Ileostomy / Colostomy'],
        ],
        'eliminasi_bak' => [
            ['value' => 'Normal', 'label' => 'Normal'],
            ['value' => 'Inkontinensia', 'label' => 'Inkontinensia'],
        ],
        'kondisi_luka' => [
            ['value' => 'Bersih', 'label' => 'Bersih'],
            ['value' => 'Kering', 'label' => 'Kering'],
        ],
        'tingkat_kemandirian' => [
            ['value' => 'Mandiri', 'label' => 'Mandiri'],
            ['value' => 'Dengan Pengawasan', 'label' => 'Dengan Pengawasan'],
            ['value' => 'Dibantu Sebagian', 'label' => 'Dibantu Sebagian'],
            ['value' => 'Dibantu Penuh', 'label' => 'Dibantu Penuh'],
        ],
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
            $kosong = $this->kosong();
            $kosong['tanggal_pulang'] = now()->format('Y-m-d');
            $kosong['waktu_pulang'] = now()->format('H:i');

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

        // Blok Manajemen Nyeri tampil bila discharge planning terakhir menandai
        // nyeri kronis / rencana manajemen nyeri.
        $perluNyeri = $this->perluManajemenNyeri((int) $registrasi_detail_id);

        // Daftar masalah keperawatan dari master Implementasi.
        $masalahKeperawatan = Implementasi::aktif()->orderBy('nama_implementasi')
            ->pluck('nama_implementasi')->all();

        $opsi = [
            'alasan_pulang' => SelectOption::get('alasan_pulang'),
            'tujuan_rujukan' => SelectOption::get('tujuan_rujukan'),
            'penyebab_kematian' => SelectOption::get('penyebab_kematian'),
        ] + self::OPSI;

        return view('moduls.EMR.PemulanganPasien.index', compact(
            'registrasi_detail', 'riwayat', 'historyGrouped', 'emr_data',
            'formAction', 'isEdit', 'deleteAction', 'isView', 'emr_id', 'aksesCrud',
            'opsi', 'masalahKeperawatan', 'perluNyeri'
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

            return redirect()->back()->with('success', 'Pemulangan Pasien berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan Pemulangan Pasien: '.$e->getMessage())->withInput();
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

            return redirect()->back()->with('success', 'Pemulangan Pasien berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui Pemulangan Pasien: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Pemulangan Pasien berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus Pemulangan Pasien: '.$e->getMessage());
        }
    }

    /**
     * Blok Manajemen Nyeri hanya relevan bila discharge planning terakhir
     * menandai nyeri kronis atau merencanakan manajemen nyeri. Bila belum
     * ada discharge planning sama sekali, blok tetap ditampilkan agar
     * petugas tetap bisa mencatatnya.
     */
    private function perluManajemenNyeri(int $registrasiDetailId): bool
    {
        $formId = EmrHelper::formIdBySlug(self::SLUG_DISCHARGE);

        if (! $formId) {
            return true;
        }

        $discharge = EmrHelper::latestValuesByVariabel((int) $formId, $registrasiDetailId, [
            'nyeri_kronis', 'edukasi_manajemen_nyeri',
        ]);

        if (! $discharge) {
            return true;
        }

        return ($discharge['nyeri_kronis'] ?? '') === 'Ya'
            || ($discharge['edukasi_manajemen_nyeri'] ?? '') === 'Ya';
    }

    /**
     * Nilai kosong untuk form baru.
     */
    private function kosong(): array
    {
        $d = [
            'tanggal_pulang' => '', 'waktu_pulang' => '',
            'kondisi_pulang' => 'Sembuh', 'catatan_alasan_pulang' => '',
            'dirujuk_ke' => '', 'rujuk_di_area_sama' => '',
            'meninggal' => '', 'catatan_meninggal' => '',
            'td_sistolik' => '', 'td_diastolik' => '', 'nadi' => '',
            'pernapasan' => '', 'suhu' => '', 'skor_nyeri' => '',
            'total_ews' => '', 'kategori_ews' => '',
            'gcs_e' => '', 'gcs_m' => '', 'gcs_v' => '', 'gcs_jumlah' => '',
            'diet_jenis' => '', 'diet_keterangan' => '',
            'bab' => '', 'bak' => '', 'bak_keterangan' => '',
            'luka' => '', 'luka_keterangan' => '', 'transfer' => '',
            'edukasi_keterangan' => '',
            'nyeri_terapi' => '', 'nyeri_efek_samping' => '', 'nyeri_kapan_ke_rs' => '',
            'masalah_keperawatan' => '', 'anjuran_pulang' => '', 'keterangan' => '',
            'serah_lab' => '', 'serah_rontgen' => '', 'serah_ct_scan' => '',
            'serah_mri' => '', 'serah_usg' => '', 'serah_surat_sakit' => '',
            'serah_surat_asuransi' => '', 'serah_resume' => '', 'serah_buku_bayi' => '',
            'serah_gol_darah' => '', 'serah_skl_bayi' => '',
            'serah_penyerah_bayi' => '', 'serah_lainnya' => '',
        ];

        foreach (self::CHECKBOX as $flag) {
            $d[$flag] = '';
        }

        return $d;
    }

    private function validated(Request $request): array
    {
        $opsional = [
            'rujuk_di_area_sama' => null,
            'meninggal' => null,
            'catatan_meninggal' => null,
            'diet_keterangan' => null,
            'bak_keterangan' => null,
            'luka_keterangan' => null,
            'edukasi_keterangan' => null,
            'nyeri_terapi' => null,
            'nyeri_efek_samping' => null,
            'nyeri_kapan_ke_rs' => null,
            'serah_penyerah_bayi' => null,
            'serah_lainnya' => null,
            'keterangan' => null,
            'dirujuk_ke' => null,
            'serah_lab' => null,
            'serah_rontgen' => null,
            'serah_ct_scan' => null,
            'serah_mri' => null,
            'serah_usg' => null,
            'serah_surat_sakit' => null,
        ];

        $opsional += array_fill_keys(self::CHECKBOX, null);

        $validate = [
            'kondisi_pulang' => ['required', Rule::in(array_column(SelectOption::get('alasan_pulang'), 'value'))],
            'catatan_alasan_pulang' => 'required|string|max:1000',

            'td_sistolik' => 'required|integer|min:40|max:300',
            'td_diastolik' => 'required|integer|min:20|max:200',
            'nadi' => 'required|integer|min:20|max:250',
            'pernapasan' => 'required|integer|min:5|max:60',
            'suhu' => 'required|numeric|min:30|max:45',
            'skor_nyeri' => 'required|integer|min:0|max:10',
            'gcs_e' => 'required|integer|min:1|max:4',
            'gcs_m' => 'required|integer|min:1|max:6',
            'gcs_v' => 'required|integer|min:1|max:5',

            'dirujuk_ke' => 'nullable|string|max:100',
            'rujuk_di_area_sama' => ['nullable', Rule::in(['Ya', 'Tidak'])],
            'meninggal' => ['nullable', Rule::in(array_column(SelectOption::get('penyebab_kematian'), 'value'))],
            'catatan_meninggal' => 'nullable|string|max:1000',

            'diet_jenis' => ['required', Rule::in(['Oral', 'NGT', 'Diet Khusus'])],
            'diet_keterangan' => 'nullable|string|max:200',
            'bab' => ['required', Rule::in(['Normal', 'Ileostomy / Colostomy'])],
            'bak' => ['required', Rule::in(['Normal', 'Inkontinensia'])],
            'bak_keterangan' => 'nullable|string|max:200',
            'luka' => ['required', Rule::in(['Bersih', 'Kering'])],
            'luka_keterangan' => 'nullable|string|max:500',
            'transfer' => ['required', Rule::in(['Mandiri', 'Dengan Pengawasan', 'Dibantu Sebagian', 'Dibantu Penuh'])],

            'edukasi_keterangan' => 'nullable|string|max:1000',
            'nyeri_terapi' => 'nullable|string|max:1000',
            'nyeri_efek_samping' => 'nullable|string|max:1000',
            'nyeri_kapan_ke_rs' => 'nullable|string|max:1000',

            'masalah_keperawatan' => 'required|array|min:1',
            'masalah_keperawatan.*' => 'required|string|max:200',
            'anjuran_pulang' => 'required|string|max:3000',
            'keterangan' => 'nullable|string|max:2000',

            'serah_lab' => 'nullable|integer|min:0|max:99',
            'serah_rontgen' => 'nullable|integer|min:0|max:99',
            'serah_ct_scan' => 'nullable|integer|min:0|max:99',
            'serah_mri' => 'nullable|integer|min:0|max:99',
            'serah_usg' => 'nullable|integer|min:0|max:99',
            'serah_surat_sakit' => 'nullable|integer|min:0|max:99',
            'serah_penyerah_bayi' => 'nullable|string|max:200',
            'serah_lainnya' => 'nullable|string|max:500',
        ];

        return array_merge($opsional, $request->validate($validate));
    }

    /**
     * Hanya field terpetakan yang disimpan. Field computed (EWS, GCS) dihitung
     * ulang di server; tanggal/jam pulang ditulis dari waktu server; field
     * kondisional yang tidak berlaku dibuang supaya tidak ada baris yatim di
     * `emr_detail`.
     */
    private function filteredData(array $data, Request $request, int $formId, int $registrasiDetailId): array
    {
        $data = array_intersect_key($data, array_flip(EmrHelper::objekVariabels($formId)));

        // Checkbox tidak ikut divalidasi satu per satu - dibaca dari presence
        // di request. Kolom yang kosong TIDAK disimpan sebagai string kosong.
        foreach (self::CHECKBOX as $flag) {
            $data[$flag] = $request->boolean($flag) ? 'Ya' : 'Tidak';
        }

        // Tanggal & jam pulang dicatat server, nilai browser tidak dipakai.
        $data['tanggal_pulang'] = Carbon::now()->format('Y-m-d');
        $data['waktu_pulang'] = Carbon::now()->format('H:i');

        // EWS - hitung ulang dari parameter yang ada di form ini. Form pulang
        // tidak punya input saturasi/oksigen/kesadaran, jadi ketiganya ditandai
        // "tidak diukur" (bukan diisi 0 - 0 berarti normal, sedangkan tidak
        // diketahui tidak sama dengan normal).
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
        if (($data['kondisi_pulang'] ?? '') !== 'Dirujuk') {
            $data['dirujuk_ke'] = null;
            $data['rujuk_di_area_sama'] = null;
        }
        if (($data['kondisi_pulang'] ?? '') !== 'Meninggal') {
            $data['meninggal'] = null;
            $data['catatan_meninggal'] = null;
        }

        if (($data['diet_jenis'] ?? '') !== 'Diet Khusus') {
            $data['diet_keterangan'] = null;
        }
        if (($data['bak'] ?? '') === 'Normal') {
            $data['bak_keterangan'] = null;
        }
        if (($data['luka'] ?? '') === 'Bersih') {
            $data['luka_keterangan'] = null;
        }

        // Blok manajemen nyeri tidak berlaku bila discharge planning terakhir
        // menyatakan pasien tidak nyeri kronis.
        if (! $this->perluManajemenNyeri($registrasiDetailId)) {
            $data['nyeri_terapi'] = null;
            $data['nyeri_efek_samping'] = null;
            $data['nyeri_kapan_ke_rs'] = null;
        }

        return $data;
    }
}
