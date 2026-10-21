<?php

namespace App\Http\Controllers\EMR\AsesmenKebidanan;

use App\Helpers\AksesEhr;
use App\Helpers\EmrHelper;
use App\Http\Controllers\Controller;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Form Asesmen Khusus Kebidanan & Penyakit Kandungan (form 59, slug
 * `asesmen_kebidanan`).
 *
 * Menghimpun data obstetris (G/P/A, riwayat persalinan sebelumnya), komplikasi
 * kehamilan, riwayat operasi/penyakit, kontrasepsi, dan riwayat menstruasi.
 * `p + a <= g` divalidasi server. Riwayat obstetrik = 5 baris × 9 kolom dengan
 * variabel bersuffix baris; baris yang seluruh kolomnya kosong dibuang di
 * `filteredData()`.
 */
class AsesmenKebidananController extends Controller
{
    private const SLUG = 'asesmen_kebidanan';

    private const KOLOM_OBST = ['tempat', 'tahun', 'kehamilan', 'jenis_persalinan', 'penyulit', 'nifas', 'sex', 'bb', 'keadaan'];

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

        return view('moduls.EMR.AsesmenKebidanan.index', compact(
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

        try {
            EmrHelper::insert((int) $form_id, $this->filteredData($request, (int) $form_id), (int) $registrasi_detail_id);

            return redirect()->back()->with('success', 'Asesmen Khusus Kebidanan berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan Asesmen Kebidanan: '.$e->getMessage())->withInput();
        }
    }

    public function update(Request $request, $registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'update'), 403);

        try {
            EmrHelper::update((int) $emr_id, (int) $form_id, $this->filteredData($request, (int) $form_id));

            return redirect()->back()->with('success', 'Asesmen Khusus Kebidanan berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui Asesmen Kebidanan: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Asesmen Kebidanan berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus Asesmen Kebidanan: '.$e->getMessage());
        }
    }

    private function kosong(): array
    {
        $data = [
            'tanggal_asesmen' => now()->format('Y-m-d'),
            'jam_asesmen' => now()->format('H:i'),
            'dokter_pemeriksa_id' => '',
            'obstetrik_g' => '', 'obstetrik_p' => '', 'obstetrik_a' => '',
            'komplikasi_hyperemesis' => '0', 'komplikasi_toksemia' => '0',
            'komplikasi_eklampsi' => '0', 'komplikasi_pph' => '0',
            'riwayat_operasi_pernah' => '', 'riwayat_operasi_jenis' => '', 'riwayat_operasi_detail' => '',
            'penyakit_jantung' => '0', 'penyakit_diabetes' => '0', 'penyakit_paru' => '0',
            'penyakit_vemerik' => '0', 'penyakit_lainnya' => '0', 'penyakit_keterangan' => '',
            'program_kb_pernah' => '', 'program_kb_jenis' => '', 'program_kb_sejak' => '',
            'menarche' => '', 'haid_terakhir' => '', 'haid_lama' => '',
            'haid_sebelumnya' => '', 'haid_lama_2' => '',
            'siklus_haid' => '', 'dismenore' => '', 'dismenore_sejak' => '', 'keteraturan_haid' => '',
            'keluhan' => '',
        ];

        foreach (self::KOLOM_OBST as $kolom) {
            for ($baris = 1; $baris <= 5; $baris++) {
                $data['riwayat_obst_'.$kolom.'_'.$baris] = '';
            }
        }

        return $data;
    }

    private function validated(Request $request): array
    {
        $validator = \Validator::make($request->all(), [
            'tanggal_asesmen' => 'required|date',
            'jam_asesmen' => 'required|date_format:H:i',
            'dokter_pemeriksa_id' => 'required|integer|exists:pegawai,pegawai_id',
            'obstetrik_g' => 'required|integer|min:0',
            'obstetrik_p' => 'required|integer|min:0',
            'obstetrik_a' => 'required|integer|min:0',
            'riwayat_operasi_pernah' => 'required|in:Pernah,Tidak Pernah',
            'riwayat_operasi_jenis' => 'required_if:riwayat_operasi_pernah,Pernah|nullable|string|max:100',
            'riwayat_operasi_detail' => 'nullable|string|max:255',
            'penyakit_keterangan' => 'required_if:penyakit_lainnya,1|nullable|string|max:255',
            'program_kb_pernah' => 'required|in:Pernah,Tidak Pernah',
            'program_kb_jenis' => 'required_if:program_kb_pernah,Pernah|nullable|string|max:100',
            'program_kb_sejak' => 'nullable|string|max:50',
            'menarche' => 'required|string|max:20',
            'haid_terakhir' => 'required|date',
            'haid_lama' => 'required|string|max:20',
            'haid_sebelumnya' => 'nullable|date',
            'haid_lama_2' => 'nullable|string|max:20',
            'siklus_haid' => 'required|string|max:20',
            'dismenore' => 'required|in:Ya,Tidak',
            'dismenore_sejak' => 'required_if:dismenore,Ya|nullable|string|max:50',
            'keteraturan_haid' => 'required|in:Teratur,Tidak Teratur',
            'keluhan' => 'nullable|string|max:2000',
        ] + $this->checkboxRules());

        $validator->after(function ($validator) use ($request) {
            $g = (int) $request->input('obstetrik_g', 0);
            $p = (int) $request->input('obstetrik_p', 0);
            $a = (int) $request->input('obstetrik_a', 0);
            if ($g > 0 && ($p + $a) > $g) {
                $validator->errors()->add('obstetrik_p', 'Jumlah P + A tidak boleh melebihi G.');
            }
        });
        $validator->validate();

        return array_merge(array_fill_keys(array_keys($this->kosong()), null), $validator->validated());
    }

    private function checkboxRules(): array
    {
        // Assignment literal per kunci (bukan loop) supaya aturan terbaca
        // oleh payload builder harness E2E (patternAssignment).
        $rules = [
            'komplikasi_hyperemesis' => 'nullable|in:0,1',
            'komplikasi_toksemia' => 'nullable|in:0,1',
            'komplikasi_eklampsi' => 'nullable|in:0,1',
            'komplikasi_pph' => 'nullable|in:0,1',
            'penyakit_jantung' => 'nullable|in:0,1',
            'penyakit_diabetes' => 'nullable|in:0,1',
            'penyakit_paru' => 'nullable|in:0,1',
            'penyakit_vemerik' => 'nullable|in:0,1',
            'penyakit_lainnya' => 'nullable|in:0,1',
        ];

        // Riwayat obstetrik: 5 baris × 9 kolom, semua opsional.
        foreach (self::KOLOM_OBST as $kolom) {
            for ($baris = 1; $baris <= 5; $baris++) {
                $rules['riwayat_obst_'.$kolom.'_'.$baris] = 'nullable|string|max:100';
            }
        }

        return $rules;
    }

    private function filteredData(Request $request, int $formId): array
    {
        $mapped = array_flip(EmrHelper::objekVariabels($formId));
        $data = array_intersect_key($this->validated($request), $mapped);

        // Normalisasi checkbox "0"/"1".
        foreach (['komplikasi_hyperemesis', 'komplikasi_toksemia', 'komplikasi_eklampsi', 'komplikasi_pph',
            'penyakit_jantung', 'penyakit_diabetes', 'penyakit_paru', 'penyakit_vemerik', 'penyakit_lainnya'] as $k) {
            $data[$k] = ($data[$k] ?? null) === '1' ? '1' : '0';
        }

        // Field kondisional: kosongkan bila syarat tidak terpenuhi.
        if (($data['riwayat_operasi_pernah'] ?? '') !== 'Pernah') {
            $data['riwayat_operasi_jenis'] = null;
            $data['riwayat_operasi_detail'] = null;
        }
        if (($data['penyakit_lainnya'] ?? '0') !== '1') {
            $data['penyakit_keterangan'] = null;
        }
        if (($data['program_kb_pernah'] ?? '') !== 'Pernah') {
            $data['program_kb_jenis'] = null;
            $data['program_kb_sejak'] = null;
        }
        if (($data['dismenore'] ?? '') !== 'Ya') {
            $data['dismenore_sejak'] = null;
        }

        // Buang baris riwayat obstetrik yang seluruh kolomnya kosong.
        for ($baris = 1; $baris <= 5; $baris++) {
            $adaIsi = false;
            foreach (self::KOLOM_OBST as $kolom) {
                $nilai = trim((string) ($data['riwayat_obst_'.$kolom.'_'.$baris] ?? ''));
                if ($nilai !== '') {
                    $adaIsi = true;
                    break;
                }
            }
            if (! $adaIsi) {
                foreach (self::KOLOM_OBST as $kolom) {
                    $data['riwayat_obst_'.$kolom.'_'.$baris] = null;
                }
            }
        }

        return $data;
    }
}
