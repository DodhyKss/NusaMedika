<?php

namespace App\Http\Controllers\EMR\AsesmenPostAnestesiSedasi;

use App\Helpers\AksesEhr;
use App\Helpers\EmrHelper;
use App\Helpers\SelectOption;
use App\Helpers\SkorPemulihanHelper;
use App\Http\Controllers\Controller;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * Form Asesmen Post-Anestesi & Sedasi (form 87, slug `asesmen_post_anestesi_sedasi`).
 *
 * Asesmen pasca-anestesi sebelum pasien kembali ke ruang perawatan
 * (KONSEP §5): skor pemulihan Aldrete/Bromage/Steward/PADSS, tanda vital,
 * komplikasi, intake oral, mobilisasi, keselamatan pasien, ruang tujuan,
 * dan rekomendasi.
 *
 * Semua TOTAL skor dan `kriteria_pemulihan` dihitung ulang di server oleh
 * `SkorPemulihanHelper` — nilai browser dibuang.
 */
class AsesmenPostAnestesiSedasiController extends Controller
{
    private const SLUG = 'asesmen_post_anestesi_sedasi';

    /** Variabel checkbox grup komplikasi post-anestesi (objek 802). */
    private const KOMPLIKASI = [
        'komplikasi_1', 'komplikasi_2', 'komplikasi_3',
        'komplikasi_4', 'komplikasi_5', 'komplikasi_6',
    ];

    /** Variabel checklist keselamatan pasien (objek 806). */
    private const KESELAMATAN = ['keselamatan_id', 'keselamatan_lokasi', 'keselamatan_site'];

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

        return view('moduls.EMR.AsesmenPostAnestesiSedasi.index', compact(
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

        $data = $this->validated($request, (int) $form_id);

        try {
            EmrHelper::insert((int) $form_id, $data, (int) $registrasi_detail_id);

            return redirect()->back()->with('success', 'Asesmen Post-Anestesi & Sedasi berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan Asesmen Post-Anestesi: '.$e->getMessage())->withInput();
        }
    }

    public function update(Request $request, $registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'update'), 403);

        $data = $this->validated($request, (int) $form_id);

        try {
            EmrHelper::update((int) $emr_id, (int) $form_id, $data);

            return redirect()->back()->with('success', 'Asesmen Post-Anestesi & Sedasi berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui Asesmen Post-Anestesi: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Asesmen Post-Anestesi & Sedasi berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus Asesmen Post-Anestesi: '.$e->getMessage());
        }
    }

    private function validated(Request $request, int $form_id): array
    {
        $rules = [
            'tanggal_asesmen' => 'required|date',
            'jam_asesmen' => 'required|date_format:H:i',
            'dokter_anestesi_id' => 'required|integer|exists:pegawai,pegawai_id',
            'skor_alderete_kesadaran' => 'required|integer|between:0,2',
            'skor_alderete_aktivitas' => 'required|integer|between:0,2',
            'skor_alderete_ventilasi' => 'required|integer|between:0,2',
            'skor_alderete_warna_kulit' => 'required|integer|between:0,2',
            'skor_alderete_total' => 'nullable|integer',
            'skor_bromage_tangan' => 'required|integer|between:0,3',
            'skor_bromage_lengan' => 'required|integer|between:0,3',
            'skor_bromage_kaki' => 'required|integer|between:0,3',
            'skor_bromage_total' => 'nullable|integer',
            'skor_steward_pergerakan' => 'required|integer|between:0,3',
            'skor_steward_pernapasan' => 'required|integer|between:0,3',
            'skor_steward_kesadaran' => 'required|integer|between:0,3',
            'skor_steward_total' => 'nullable|integer',
            'skor_padss_vas' => 'required|integer|between:0,3',
            'skor_padss_aktivitas' => 'required|integer|between:0,3',
            'skor_padss_total' => 'nullable|integer',
            'kriteria_pemulihan' => 'required|in:Terpenuhi,Belum',
            'kesadaran' => ['required', Rule::in(array_column(SelectOption::get('kesadaran'), 'value'))],
            'sistolik' => 'required|numeric',
            'diastolik' => 'required|numeric',
            'nadi' => 'required|numeric',
            'pernapasan' => 'required|numeric',
            'suhu' => 'required|numeric',
            'saturasi' => 'required|numeric',
            'nyeri' => 'required|in:Ya,Tidak',
            'komplikasi_1' => 'nullable|in:0,1',
            'komplikasi_2' => 'nullable|in:0,1',
            'komplikasi_3' => 'nullable|in:0,1',
            'komplikasi_4' => 'nullable|in:0,1',
            'komplikasi_5' => 'nullable|in:0,1',
            'komplikasi_6' => 'nullable|in:0,1',
            'skala_nyeri' => 'required_if:nyeri,Ya|integer|between:0,10',
            'intake_oral_ada' => 'required|in:Ya,Tidak',
            'intake_oral_catatan' => 'required_if:intake_oral_ada,Ya|string|max:2000',
            'mobilisasi' => 'required|in:Ya,Tidak',
            'mobilisasi_catatan' => 'required_if:mobilisasi,Tidak|string|max:2000',
            'keadaan_umum' => ['required', Rule::in(array_column(SelectOption::get('keadaan_umum_pulih'), 'value'))],
            'keselamatan_id' => 'required|in:0,1',
            'keselamatan_lokasi' => 'required|in:0,1',
            'keselamatan_site' => 'required|in:0,1',
            'ruangan_tujuan' => 'required|exists:bagian,bagian_id',
            'formulir_tertunda' => 'nullable|string|max:2000',
            'rekomendasi' => 'required|string|max:2000',
        ];

        $validator = Validator::make($request->all(), $rules);
        $validator->validate();

        // Semua variabel mapping selalu ikut (nullable yang tidak dikirim → null)
        // supaya setiap baris emr_detail tercipta dan tampil lagi saat edit.
        $data = array_merge(
            array_fill_keys(EmrHelper::objekVariabels($form_id), null),
            $validator->validated()
        );

        // ===== Komputasi server (nilai browser DIBUANG) =====
        $skor = SkorPemulihanHelper::hitung($data);
        $data['skor_alderete_total'] = $skor['alderete'];
        $data['skor_bromage_total'] = $skor['bromage'];
        $data['skor_steward_total'] = $skor['steward'];
        $data['skor_padss_total'] = $skor['padss'];
        // Kriteria pemulihan diturunkan dari ambang Aldrete (> 8 layak ke
        // ruang) — user tidak bebas memilih (KONSEP §5).
        $data['kriteria_pemulihan'] = ($skor['alderete'] !== null && $skor['alderete'] > 8) ? 'Terpenuhi' : 'Belum';

        // Normalisasi checkbox: "1" bila terkirim & terpilih, selain itu "0".
        foreach (self::KOMPLIKASI as $k) {
            $data[$k] = $data[$k] === '1' ? '1' : '0';
        }
        foreach (self::KESELAMATAN as $k) {
            $data[$k] = $data[$k] === '1' ? '1' : '0';
        }

        // Field kondisional dikosongkan (null), bukan di-unset, supaya baris
        // emr_detail tetap ada tapi tidak menyesatkan.
        if (($data['nyeri'] ?? null) !== 'Ya') {
            $data['skala_nyeri'] = null;
        }
        if (($data['intake_oral_ada'] ?? null) !== 'Ya') {
            $data['intake_oral_catatan'] = null;
        }
        if (($data['mobilisasi'] ?? null) !== 'Tidak') {
            $data['mobilisasi_catatan'] = null;
        }

        return $data;
    }

    private function kosong(): array
    {
        $d = [
            'tanggal_asesmen' => '', 'jam_asesmen' => '', 'dokter_anestesi_id' => '',
            'kriteria_pemulihan' => 'Belum', 'kesadaran' => '',
            'sistolik' => '', 'diastolik' => '', 'nadi' => '', 'pernapasan' => '',
            'suhu' => '', 'saturasi' => '',
            'nyeri' => 'Tidak', 'skala_nyeri' => '',
            'intake_oral_ada' => '', 'intake_oral_catatan' => '',
            'mobilisasi' => '', 'mobilisasi_catatan' => '',
            'keadaan_umum' => '', 'ruangan_tujuan' => '',
            'formulir_tertunda' => '', 'rekomendasi' => '',
        ];
        foreach (['skor_alderete_total', 'skor_bromage_total', 'skor_steward_total', 'skor_padss_total'] as $k) {
            $d[$k] = '';
        }
        foreach (self::KOMPLIKASI as $k) {
            $d[$k] = '0';
        }
        foreach (self::KESELAMATAN as $k) {
            $d[$k] = '0';
        }

        return $d;
    }
}
