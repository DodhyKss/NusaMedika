<?php

namespace App\Http\Controllers\EMR\AsesmenGizi;

use App\Helpers\AksesEhr;
use App\Helpers\EmrHelper;
use App\Helpers\GiziHelper;
use App\Http\Controllers\Controller;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Form Asesmen Gizi (form 39, slug `asesmen_gizi`).
 *
 * Pengkajian awal oleh tenaga gizi dalam 24 jam pertama pasien masuk. Output-nya
 * menentukan status gizi, risiko malnutrisi, kebutuhan energi, dan bentuk
 * intervensi gizi.
 *
 * Field TURUNAN yang selalu dihitung ulang di server (`filteredData()`), nilai
 * browser dibuang sepenuhnya:
 * - `bmi`               → `GiziHelper::imt(berat_badan, tinggi_badan)`
 * - `status_gizi`        → TIDAK disimpan (diturunkan dari `bmi` + usia di view)
 * - `penurunan_bb_persen`→ TIDAK disimpan (diturunkan dari `bb_biasanya` di view)
 *
 * `umur` & `jenis_kelamin` juga tidak disimpan — keduanya diturunkan dari master
 * pasien, bukan input petugas.
 *
 * Enam gejala klinis (fkg_*) sengaja dipetakan ke SATU objek 376 — lihat
 * docs/KONSEP_ASESMEN_GIZI.md §3.2. Presedennya sudah ada (form 3 memetakan
 * `vaksin_covid`/`tanggal_covid_1`/`tanggal_covid_2` ke objek 62).
 */
class AsesmenGiziController extends Controller
{
    private const SLUG = 'asesmen_gizi';

    /** Enam gejala klinis -&gt; satu objek (376). */
    public const FKG = ['fkg_diare', 'fkg_edema_ascites', 'fkg_muntah', 'fkg_konstipasi', 'fkg_mual', 'fkg_hilang_lemak'];

    /** Label gejala klinis untuk ditampilkan. */
    public const FKG_LABEL = [
        'fkg_diare' => 'Diare',
        'fkg_edema_ascites' => 'Edema / Asites',
        'fkg_muntah' => 'Muntah',
        'fkg_konstipasi' => 'Konstipasi',
        'fkg_mual' => 'Mual',
        'fkg_hilang_lemak' => 'Hilang Lemak (Subkutan)',
    ];

    /** Opsi gangguan menelan/menghisap/mengunyah (objek 375, checkbox). */
    public const OPSI_MENELAN = [
        'gangguan_menelan' => 'Menelan',
        'gangguan_menghisap' => 'Menghisap',
        'gangguan_mengunyah' => 'Mengunyah',
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

        $pasien = optional($registrasi_detail->registrasi)->pasien;
        $usia = GiziHelper::usia(
            optional($pasien)->tgl_lahir ? (string) $pasien->tgl_lahir : null,
            $registrasi_detail->tgl_daftar ? (string) $registrasi_detail->tgl_daftar : null
        );

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

        return view('moduls.EMR.AsesmenGizi.index', compact(
            'registrasi_detail', 'riwayat', 'historyGrouped', 'emr_data',
            'formAction', 'isEdit', 'deleteAction', 'isView', 'emr_id', 'aksesCrud',
            'usia'
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

            return redirect()->back()->with('success', 'Asesmen Gizi berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan Asesmen Gizi: '.$e->getMessage())->withInput();
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

            return redirect()->back()->with('success', 'Asesmen Gizi berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui Asesmen Gizi: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Asesmen Gizi berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus Asesmen Gizi: '.$e->getMessage());
        }
    }

    /**
     * Nilai kosong untuk form baru.
     */
    private function kosong(): array
    {
        return [
            'tanggal_asesmen' => '', 'waktu_asesmen' => '', 'jenis_asesmen' => '',
            'riwayat_penyakit' => '', 'riwayat_penyakit_keluarga' => '', 'diagnosa_medis' => '',
            'alergi' => '', 'ketidaksukaan_makanan' => '', 'pantangan_makan' => '',
            'pengalaman_diit' => '', 'asupan_smrs' => '',
            'berat_badan' => '', 'tinggi_badan' => '', 'z_score' => '', 'lingkar_lengan' => '',
            'bb_biasanya' => '', 'biokimia' => '',
            'nafsu_makan' => '', 'gigi_geligi' => '',
            'gangguan_menelan' => null, 'gangguan_menghisap' => null, 'gangguan_mengunyah' => null,
            'diagnosa_gizi' => '', 'kebutuhan_energi' => '', 'intervensi_gizi' => '', 'monev' => '',
            'rekomendasi' => '',
        ];
    }

    /**
     * Aturan validasi (docs/KONSEP_ASESMEN_GIZI.md §3.8).
     *
     * `bmi` SENGAJA tidak ada di sini — field turunan tidak pernah divalidasi dari
     * browser, nilainya selalu dihitung ulang di `filteredData()`.
     */
    private function validated(Request $request): array
    {
        $rules = [
            'tanggal_asesmen' => 'required|date',
            'waktu_asesmen' => 'required|date_format:H:i',
            'jenis_asesmen' => 'required|in:Perlu Asuhan Gizi,Re-Asesmen',
            'riwayat_penyakit' => 'required|string|max:1000',
            'riwayat_penyakit_keluarga' => 'nullable|string|max:1000',
            'diagnosa_medis' => 'required|string|max:1000',
            'alergi' => 'nullable|string|max:1000',
            'ketidaksukaan_makanan' => 'nullable|string|max:255',
            'pantangan_makan' => 'nullable|string|max:255',
            'pengalaman_diit' => 'required|in:Tidak,Ada',
            'asupan_smrs' => 'nullable|string|max:500',
            'berat_badan' => 'required|numeric|min:0.5|max:500',
            'tinggi_badan' => 'required|numeric|min:20|max:260',
            'z_score' => 'nullable|numeric|between:-6,6',
            'lingkar_lengan' => 'nullable|numeric|min:5|max:90',
            'bb_biasanya' => 'nullable|string|max:255',
            'biokimia' => 'nullable|string|max:1000',
            'nafsu_makan' => 'required|in:Baik,Kurang,Tidak Ada',
            'gigi_geligi' => 'required|in:Lengkap,Tidak Lengkap',
            'diagnosa_gizi' => 'required|string|max:2000',
            'kebutuhan_energi' => 'required|string|max:255',
            'intervensi_gizi' => 'required|string|max:2000',
            'monev' => 'required|string|max:2000',
            'rekomendasi' => 'required|in:DPJP,Ahli Gizi',
        ];

        // Checkbox gangguan menelan. `in:0,1` bukan `boolean`: HTML checkbox mengirim
        // string "1", dan bentuk in: lebih mudah dibaca baik di form biasa
        // maupun di harness E2E (tools/lib_pay.php).
        $rules['gangguan_menelan'] = 'nullable|in:0,1';
        $rules['gangguan_menghisap'] = 'nullable|in:0,1';
        $rules['gangguan_mengunyah'] = 'nullable|in:0,1';
        // Enam gejala klinis: aturan ditulis eksplisit (bukan loop) supaya
        // mudah dibaca dan terbaca harness E2E.
        $rules['fkg_diare'] = 'required|in:Ada,Tidak';
        $rules['fkg_edema_ascites'] = 'required|in:Ada,Tidak';
        $rules['fkg_muntah'] = 'required|in:Ada,Tidak';
        $rules['fkg_konstipasi'] = 'required|in:Ada,Tidak';
        $rules['fkg_mual'] = 'required|in:Ada,Tidak';
        $rules['fkg_hilang_lemak'] = 'required|in:Ada,Tidak';
        // Tanda vital. Aturannya WAJIB ada walau nullable: `validated()` hanya
        // mengembalikan key yang ada di rules, jadi tanpa baris ini keenam field
        // ini tidak pernah ikut ke payload dan tidak tersimpan sama sekali.
        $rules['td_sistolik'] = 'nullable|numeric|min:40|max:300';
        $rules['td_diastolik'] = 'nullable|numeric|min:20|max:200';
        $rules['nadi'] = 'nullable|numeric|min:10|max:250';
        $rules['suhu'] = 'nullable|numeric|min:30|max:45';
        $rules['pernapasan'] = 'nullable|numeric|min:5|max:60';
        $rules['saturasi'] = 'nullable|numeric|min:40|max:100';

        // Default null untuk seluruh field opsional supaya `validated()` tidak
        // melempar "Undefined array key" saat key tidak dikirim browser.
        $opsional = [
            'riwayat_penyakit_keluarga' => null, 'alergi' => null,
            'ketidaksukaan_makanan' => null, 'pantangan_makan' => null, 'asupan_smrs' => null,
            'z_score' => null, 'lingkar_lengan' => null, 'bb_biasanya' => null, 'biokimia' => null,
        ];
        foreach (array_keys(self::OPSI_MENELAN) as $variabel) {
            $opsional[$variabel] = null;
        }

        return array_merge($opsional, $request->validate($rules));
    }

    /**
     * Hanya field terpetakan yang disimpan, dengan field turunan dihitung ulang.
     */
    private function filteredData(array $data, int $formId): array
    {
        $data = array_intersect_key($data, array_flip(EmrHelper::objekVariabels($formId)));

        // IMT dihitung ulang di server — nilai browser dibuang total.
        $data['bmi'] = GiziHelper::imt($data['berat_badan'] ?? null, $data['tinggi_badan'] ?? null);

        // Checkbox gangguan menelan: yang tidak dicentang disimpan null (bukan
        // `unset()`) supaya baris emr_detail tetap ada dan kelengkapan mapping
        // tetap terverifikasi.
        foreach (array_keys(self::OPSI_MENELAN) as $variabel) {
            $data[$variabel] = ! empty($data[$variabel]) ? 1 : null;
        }

        // String kosong -> null supaya tidak tersimpan sebagai baris kosong.
        foreach ($data as $key => $value) {
            if (is_string($value) && trim($value) === '') {
                $data[$key] = null;
            }
        }

        return $data;
    }
}
