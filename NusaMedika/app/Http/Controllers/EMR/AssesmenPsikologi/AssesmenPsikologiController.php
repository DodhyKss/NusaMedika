<?php

namespace App\Http\Controllers\EMR\AssesmenPsikologi;

use App\Helpers\AksesEhr;
use App\Helpers\EmrHelper;
use App\Http\Controllers\Controller;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

/**
 * Form Asesmen Psikologi Pasien & Keluarga (form 112, slug `assesmen_psikologi`).
 *
 * KONSEP_PSIKOSOSIAL_KEROHANIAN.md §3: tiga section — status psikologis
 * pasien & keluarga (orang yang bisa dihubungi / rencana rawat / lingkungan),
 * reaksi pasien atas penyakitnya (8 butir informasi + 2 masalah pribadi),
 * dan reaksi keluarga (10 butir + 3 masalah keperawatan).
 *
 * Aturan server:
 * 1. Field kondisional di-set null bila gerbang radionya tidak aktif, supaya
 *    tidak ada baris `emr_detail` yatim yang menyesatkan saat dibuka lagi.
 * 2. Butir `psi_*` WAJIB bersuffix — 8 item asesmen informasi dipetakan ke SATU
 *    objek (680), tapi `emrDetailByVariabel()` melakukan pluck('value',
 *    'variabel') sehingga tanpa suffix baris saling menimpa.
 */
class AssesmenPsikologiController extends Controller
{
    private const SLUG = 'assesmen_psikologi';

    /** Butir checkbox: 8 informasi + 2 masalah pribadi + 10 keluarga + 3 mk. */
    private const CHECKBOX = [
        'psi_info_1', 'psi_info_2', 'psi_info_3', 'psi_info_4',
        'psi_info_5', 'psi_info_6', 'psi_info_7', 'psi_info_8',
        'psi_masalah_1', 'psi_masalah_2',
        'psi_keluarga_1', 'psi_keluarga_2', 'psi_keluarga_3', 'psi_keluarga_4',
        'psi_keluarga_5', 'psi_keluarga_6', 'psi_keluarga_7', 'psi_keluarga_8',
        'psi_keluarga_9', 'psi_keluarga_10',
        'psi_mk_keluarga_1', 'psi_mk_keluarga_2', 'psi_mk_keluarga_3',
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

        return view('moduls.EMR.AssesmenPsikologi.index', compact(
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
        $data = $this->filteredData($data);

        try {
            EmrHelper::insert((int) $form_id, $data, (int) $registrasi_detail_id);

            return redirect()->back()->with('success', 'Asesmen Psikologi berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan Asesmen Psikologi: '.$e->getMessage())->withInput();
        }
    }

    public function update(Request $request, $registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'update'), 403);

        $data = $this->validated($request, (int) $form_id);
        $data = $this->filteredData($data);

        try {
            EmrHelper::update((int) $emr_id, (int) $form_id, $data);

            return redirect()->back()->with('success', 'Asesmen Psikologi berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui Asesmen Psikologi: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Asesmen Psikologi berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus Asesmen Psikologi: '.$e->getMessage());
        }
    }

    private function validated(Request $request, int $form_id): array
    {
        $rules = [
            'hubungan_keluarga' => 'required|in:Tidak,Ya',
            'nama_keluarga' => 'required_if:hubungan_keluarga,Ya|nullable|string|max:150',
            'status_keluarga' => 'required_if:hubungan_keluarga,Ya|nullable|string|max:100',
            'alamat_keluarga' => 'nullable|string|max:500',
            'hp_keluarga' => 'required_if:hubungan_keluarga,Ya|nullable|string|max:13',
            'rencana_rawat' => 'required|in:Tetap Dirawat di RS,Dirawat di Rumah',
            'lingkungan' => 'required|in:Tidak,Ya',
            'perawat_rumah' => 'required_if:lingkungan,Ya|nullable|string|max:150',
            'fasilitasi_rs' => 'required_if:lingkungan,Tidak|nullable|in:Tidak,Ya',
            'tanggal_asesmen' => 'required|date',
            'waktu_asesmen' => 'required|date_format:H:i',
            'psi_info_1' => 'nullable|in:0,1',
            'psi_info_2' => 'nullable|in:0,1',
            'psi_info_3' => 'nullable|in:0,1',
            'psi_info_4' => 'nullable|in:0,1',
            'psi_info_5' => 'nullable|in:0,1',
            'psi_info_6' => 'nullable|in:0,1',
            'psi_info_7' => 'nullable|in:0,1',
            'psi_info_8' => 'nullable|in:0,1',
            'psi_masalah_1' => 'nullable|in:0,1',
            'psi_masalah_2' => 'nullable|in:0,1',
            'psi_keluarga_1' => 'nullable|in:0,1',
            'psi_keluarga_2' => 'nullable|in:0,1',
            'psi_keluarga_3' => 'nullable|in:0,1',
            'psi_keluarga_4' => 'nullable|in:0,1',
            'psi_keluarga_5' => 'nullable|in:0,1',
            'psi_keluarga_6' => 'nullable|in:0,1',
            'psi_keluarga_7' => 'nullable|in:0,1',
            'psi_keluarga_8' => 'nullable|in:0,1',
            'psi_keluarga_9' => 'nullable|in:0,1',
            'psi_keluarga_10' => 'nullable|in:0,1',
            'psi_mk_keluarga_1' => 'nullable|in:0,1',
            'psi_mk_keluarga_2' => 'nullable|in:0,1',
            'psi_mk_keluarga_3' => 'nullable|in:0,1',
        ];

        $validator = Validator::make($request->all(), $rules);
        $validator->validate();

        return array_merge(
            array_fill_keys(EmrHelper::objekVariabels($form_id), null),
            $validator->validated()
        );
    }

    /**
     * Field kondisional di-set null (bukan di-unset) supaya baris
     * `emr_detail`-nya tetap ada dan `simpan` E2E penuh.
     */
    private function filteredData(array $data): array
    {
        if (($data['hubungan_keluarga'] ?? null) !== 'Ya') {
            $data['nama_keluarga'] = null;
            $data['status_keluarga'] = null;
            $data['alamat_keluarga'] = null;
            $data['hp_keluarga'] = null;
        }

        if (($data['lingkungan'] ?? null) !== 'Ya') {
            $data['perawat_rumah'] = null;
        }

        if (($data['lingkungan'] ?? null) !== 'Tidak') {
            $data['fasilitasi_rs'] = null;
        }

        return $data;
    }

    private function kosong(): array
    {
        $d = [
            'hubungan_keluarga' => '', 'nama_keluarga' => '', 'status_keluarga' => '',
            'alamat_keluarga' => '', 'hp_keluarga' => '', 'rencana_rawat' => '',
            'lingkungan' => '', 'perawat_rumah' => '', 'fasilitasi_rs' => '',
            'tanggal_asesmen' => '', 'waktu_asesmen' => '',
        ];

        foreach (self::CHECKBOX as $butir) {
            $d[$butir] = '0';
        }

        return $d;
    }
}
