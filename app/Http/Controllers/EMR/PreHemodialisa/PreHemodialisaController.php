<?php

namespace App\Http\Controllers\EMR\PreHemodialisa;

use App\Helpers\AksesEhr;
use App\Helpers\EmrHelper;
use App\Helpers\EwsHelper;
use App\Helpers\HemodialisaHelper;
use App\Helpers\IcuHelper;
use App\Http\Controllers\Controller;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * Form Pre Hemodialisa (form 102, slug `pre_hemodialisa`).
 *
 * Pengkajian pra-sesi HD (KONSEP §5) — accordion A–E:
 * data fokus + TTV/EWS/GCS, heparinisasi, waktu & ultrafiltrasi, laboratorium,
 * terapi tambahan, psikososial, asuhan keperawatan, screening akses.
 * Turunan server: `ews`, `gcs_jumlah`, `lama_hd` — nilai browser dibuang.
 */
class PreHemodialisaController extends Controller
{
    private const SLUG = 'pre_hemodialisa';

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

        return view('moduls.EMR.PreHemodialisa.index', compact(
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
            EmrHelper::insert((int) $form_id, $this->filteredData($data, (int) $form_id), (int) $registrasi_detail_id);

            return redirect()->back()->with('success', 'Pre Hemodialisa berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan Pre Hemodialisa: '.$e->getMessage())->withInput();
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

            return redirect()->back()->with('success', 'Pre Hemodialisa berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui Pre Hemodialisa: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Pre Hemodialisa berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus Pre Hemodialisa: '.$e->getMessage());
        }
    }

    private function validated(Request $request): array
    {
        $rules = [
            'tanggal_pertemuan' => 'required|date',
            'waktu_pertemuan' => 'required|date_format:H:i',
            'subjective' => 'required|string|max:2000',
            'objective' => 'required|string|max:2000',
            'bb_awal' => 'required|numeric|min:20|max:300',
            'berat_badan' => 'required|numeric|min:20|max:300',
            'bb_kering' => 'required|numeric|min:20|max:300',
            'type_hd' => 'required|string|max:50',
            'riwayat_alergi' => 'required|string|max:2000',
            'dialiser' => 'required|string|max:50',
            'td_sistolik' => 'required|integer|min:50|max:300',
            'td_diastolik' => 'required|integer|min:30|max:200',
            'nadi' => 'required|integer|min:0|max:250',
            'pernapasan' => 'required|integer|min:0|max:120',
            'suhu' => 'required|numeric|between:30,45',
            'saturasi' => 'required|numeric|min:50|max:100',
            'kesadaran' => 'required|string',
            'gcs_e' => 'required|integer|between:1,4',
            'gcs_m' => 'required|integer|between:1,6',
            'gcs_v' => 'required|integer|between:1,5',
            'dializer' => 'required|string|max:50',
            'dialisat' => 'nullable|string|max:100',
            'akses_fistula' => 'required|in:off,on',
            'akses_graft' => 'required|in:off,on',
            'akses_sndl' => 'required|in:off,on',
            'akses_cdl' => 'required|in:off,on',
            'akses_cdl_thermo' => 'required|in:off,on',
            'akses_femoral' => 'required|in:off,on',
            'dosis_sirkulasi' => 'required|string|max:50',
            'dosis_bolus' => 'required|string|max:50',
            'dosis_maintenance' => 'required|numeric|min:0|max:20000',
            'tanpa_heparinisasi' => 'nullable|string|max:500',
            'prog_bilas' => 'required|string|max:50',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i',
            'uf' => 'required|numeric|min:0',
            'qb' => 'required|numeric|min:0|max:2000',
            'tbv' => 'required|numeric|min:0|max:10000',
            'tgl_laborat' => 'required|date',
            'jam_laborat' => 'required|date_format:H:i',
            'hb' => 'required|numeric|min:0',
            'ureum' => 'required|numeric|min:0',
            'creatinine' => 'required|numeric|min:0',
            'hbsag' => 'required|in:Negatif,Positif',
            'kalium' => 'required|numeric|min:0',
            'sgot' => 'required|numeric|min:0',
            'sgpt' => 'required|numeric|min:0',
            'hasil_pemeriksaan_lain' => 'nullable|string|max:2000',
            'transfusi' => 'nullable|numeric|min:0',
            'd40' => 'nullable|numeric|min:0',
            'ca_glukonas' => 'nullable|numeric|min:0',
            'kcl' => 'nullable|numeric|min:0',
            'renxamin' => 'nullable|numeric|min:0',
            'erythropoietin' => 'nullable|numeric|min:0',
            'riwayat_kesehatan_saat_ini' => 'required|string|max:2000',
            'komunikasi_ada_kendala' => 'required|in:off,on',
            'komunikasi_tidak_ada' => 'required|in:off,on',
            'komunikasi_lain' => 'required|in:off,on',
            'komunikasi_keterangan' => 'nullable|string|max:500',
            'perawat_rumah_ada' => 'required|in:off,on',
            'perawat_rumah_tidak' => 'required|in:off,on',
            'perawat_rumah_lain' => 'required|in:off,on',
            'perawat_rumah_keterangan' => 'nullable|string|max:500',
            'gaduh_kondisi_saat_ini' => 'required|in:off,on',
            'gaduh_tenang' => 'required|in:off,on',
            'gaduh_gelisah' => 'required|in:off,on',
            'gaduh_takut_tindakan' => 'required|in:off,on',
            'gaduh_marah' => 'required|in:off,on',
            'gaduh_mudah_tersinggung' => 'required|in:off,on',
            'diagnosa_keperawatan' => 'required|string|max:2000',
            'planning' => 'required|string|max:2000',
            'evaluasi' => 'required|string|max:2000',
            'tanggal_hd_berikutnya' => 'required|date|after:tanggal_pertemuan',
            'lama_hd_rencana' => 'required|numeric|between:1,12',
            'screening_akses_tanggal' => 'required|date',
            'screening_akses_catatan' => 'nullable|string|max:2000',
            'keterangan' => 'nullable|string|max:2000',
        ];

        $validated = $request->validate($rules);

        // Validasi silang (KONSEP §5.7).
        if ((float) $validated['bb_kering'] > (float) $validated['berat_badan']) {
            throw ValidationException::withMessages([
                'bb_kering' => 'Berat badan kering tidak boleh melebihi berat badan saat ini.',
            ]);
        }
        if (strtotime('1970-01-01 '.$validated['jam_selesai']) <= strtotime('1970-01-01 '.$validated['jam_mulai'])) {
            throw ValidationException::withMessages([
                'jam_selesai' => 'Jam selesai HD harus setelah jam mulai.',
            ]);
        }
        if ((float) $validated['dosis_maintenance'] === 0.0 && trim((string) ($validated['tanpa_heparinisasi'] ?? '')) === '') {
            throw ValidationException::withMessages([
                'tanpa_heparinisasi' => 'Penyebab tanpa heparinisasi wajib diisi bila dosis maintenance = 0.',
            ]);
        }
        $aksesKeys = array_keys(HemodialisaHelper::AKSES_VASKULER);
        $adaAkses = false;
        foreach ($aksesKeys as $k) {
            if (($validated[$k] ?? 'off') === 'on') {
                $adaAkses = true;
                break;
            }
        }
        if (! $adaAkses) {
            throw ValidationException::withMessages([
                'akses_fistula' => 'Minimal satu jenis akses vaskuler harus dipilih.',
            ]);
        }
        if (($validated['komunikasi_lain'] ?? 'off') === 'on' && trim((string) ($validated['komunikasi_keterangan'] ?? '')) === '') {
            throw ValidationException::withMessages([
                'komunikasi_keterangan' => 'Keterangan kendala komunikasi wajib diisi bila "Lainnya" dipilih.',
            ]);
        }
        if (($validated['perawat_rumah_lain'] ?? 'off') === 'on' && trim((string) ($validated['perawat_rumah_keterangan'] ?? '')) === '') {
            throw ValidationException::withMessages([
                'perawat_rumah_keterangan' => 'Keterangan perawat rumah wajib diisi bila "Lainnya" dipilih.',
            ]);
        }

        return $validated;
    }

    /** Turunan server: EWS, GCS jumlah, lama dialisis (menit). */
    private function filteredData(array $data, int $formId): array
    {
        $data = array_intersect_key($data, array_flip(EmrHelper::objekVariabels($formId)));

        // Form ini tidak punya input `oksigen` — default Air untuk EWS.
        $ews = EwsHelper::hitung($data + ['oksigen' => 'Air']);
        $data['ews'] = $ews['lengkap'] ? $ews['total'] : null;

        $data['gcs_jumlah'] = IcuHelper::gcsJumlah(
            $data['gcs_e'] ?? null,
            $data['gcs_m'] ?? null,
            $data['gcs_v'] ?? null,
        );

        $data['lama_hd'] = HemodialisaHelper::lamaDialisis(
            $data['jam_mulai'] ?? null,
            $data['jam_selesai'] ?? null,
        );

        return $data;
    }

    private function kosong(): array
    {
        $kosong = [
            'tanggal_pertemuan' => '', 'waktu_pertemuan' => '', 'subjective' => '', 'objective' => '',
            'bb_awal' => '', 'berat_badan' => '', 'bb_kering' => '', 'type_hd' => '', 'riwayat_alergi' => '',
            'dialiser' => '', 'td_sistolik' => '', 'td_diastolik' => '', 'nadi' => '', 'pernapasan' => '',
            'suhu' => '', 'saturasi' => '', 'ews' => '', 'kesadaran' => '',
            'gcs_e' => '', 'gcs_m' => '', 'gcs_v' => '', 'gcs_jumlah' => '',
            'dializer' => '', 'dialisat' => '',
            'dosis_sirkulasi' => '', 'dosis_bolus' => '', 'dosis_maintenance' => '',
            'tanpa_heparinisasi' => '', 'prog_bilas' => '',
            'jam_mulai' => '', 'jam_selesai' => '', 'lama_hd' => '',
            'uf' => '', 'qb' => '', 'tbv' => '',
            'tgl_laborat' => '', 'jam_laborat' => '', 'hb' => '', 'ureum' => '', 'creatinine' => '',
            'hbsag' => '', 'kalium' => '', 'sgot' => '', 'sgpt' => '', 'hasil_pemeriksaan_lain' => '',
            'transfusi' => '', 'd40' => '', 'ca_glukonas' => '', 'kcl' => '', 'renxamin' => '', 'erythropoietin' => '',
            'riwayat_kesehatan_saat_ini' => '', 'komunikasi_keterangan' => '',
            'perawat_rumah_keterangan' => '',
            'diagnosa_keperawatan' => '', 'planning' => '', 'evaluasi' => '',
            'tanggal_hd_berikutnya' => '', 'lama_hd_rencana' => '',
            'screening_akses_tanggal' => '', 'screening_akses_catatan' => '', 'keterangan' => '',
        ];
        foreach (array_keys(HemodialisaHelper::AKSES_VASKULER) as $k) {
            $kosong[$k] = 'off';
        }
        foreach ([
            'komunikasi_ada_kendala', 'komunikasi_tidak_ada', 'komunikasi_lain',
            'perawat_rumah_ada', 'perawat_rumah_tidak', 'perawat_rumah_lain',
            'gaduh_kondisi_saat_ini', 'gaduh_tenang', 'gaduh_gelisah',
            'gaduh_takut_tindakan', 'gaduh_marah', 'gaduh_mudah_tersinggung',
        ] as $k) {
            $kosong[$k] = 'off';
        }

        return $kosong;
    }
}
