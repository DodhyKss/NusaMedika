<?php

namespace App\Http\Controllers\EMR\BayiBaruLahir;

use App\Helpers\AksesEhr;
use App\Helpers\EmrHelper;
use App\Http\Controllers\Controller;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Form Pelaksanaan Bayi Baru Lahir (form 64, slug `bayi_baru_lahir`).
 *
 * Immediate newborn care: penilaian awal (baik/ada penyulit), waktu
 * skin-to-skin, dan daftar tindakan. Checkbox memakai nilai "0"/"1" supaya
 * bisa dijumlahkan server. `Ada penyulit` ⇒ minimal satu tindakan abnormal.
 */
class BayiBaruLahirController extends Controller
{
    private const SLUG = 'bayi_baru_lahir';

    /** Tindakan bayi normal (1–4). */
    private const TINDAKAN_NORMAL = [
        'Mengeringkan', 'Menghangatkan', 'Membungkus bayi', 'Menempatkan di sisi Ibu',
    ];

    /** Tindakan bayi tidak normal (1–9). */
    private const TINDAKAN_ABNORMAL = [
        'Mengeringkan', 'Bebaskan jalan napas', 'Menghangatkan', 'Rangsang taktil',
        'Bungkus bayi', 'Pemberian O2', 'Penempatan posisi kepala bayi', 'VTP', 'Pemberian obat',
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

        return view('moduls.EMR.BayiBaruLahir.index', compact(
            'registrasi_detail', 'riwayat', 'historyGrouped', 'emr_data',
            'formAction', 'isEdit', 'deleteAction', 'isView', 'emr_id', 'aksesCrud'
        ) + ['tindakanNormal' => self::TINDAKAN_NORMAL, 'tindakanAbnormal' => self::TINDAKAN_ABNORMAL]);
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
            EmrHelper::insert((int) $form_id, $this->filteredData($data), (int) $registrasi_detail_id);

            return redirect()->back()->with('success', 'Pelaksanaan Bayi Baru Lahir berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan Pelaksanaan Bayi Baru Lahir: '.$e->getMessage())->withInput();
        }
    }

    public function update(Request $request, $registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'update'), 403);

        $data = $this->validated($request);

        try {
            EmrHelper::update((int) $emr_id, (int) $form_id, $this->filteredData($data));

            return redirect()->back()->with('success', 'Pelaksanaan Bayi Baru Lahir berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui Pelaksanaan Bayi Baru Lahir: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Pelaksanaan Bayi Baru Lahir berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus Pelaksanaan Bayi Baru Lahir: '.$e->getMessage());
        }
    }

    private function kosong(): array
    {
        $d = [
            'penilaian_bayi_baru_lahir' => '',
            'penilaian_bayi_baru_lahir_ya' => '',
            'waktu_penilaian_bayi' => '',
            'alasan_tidak_penilaian' => '',
        ];
        for ($i = 1; $i <= 4; $i++) {
            $d['tindakan_bayi_normal_'.$i] = '0';
        }
        for ($i = 1; $i <= 9; $i++) {
            $d['tindakan_bayi_abnormal_'.$i] = '0';
        }

        return $d;
    }

    private function validated(Request $request): array
    {
        $validator = \Validator::make($request->all(), [
            'penilaian_bayi_baru_lahir' => 'required|in:Baik,Ada penyulit',
            'penilaian_bayi_baru_lahir_ya' => 'required|in:Ya,Tidak diberikan',
            'waktu_penilaian_bayi' => 'required_if:penilaian_bayi_baru_lahir_ya,Ya|nullable|string|max:100',
            'alasan_tidak_penilaian' => 'required_if:penilaian_bayi_baru_lahir_ya,Tidak diberikan|nullable|string|max:255',
        ] + $this->checkboxRules());

        $validator->after(function ($validator) use ($request) {
            // Checkbox = hidden "0" + checkbox "1". Validasi hanya bila user
            // benar-benar mengirim nilai selain "0" (harness mengirim "0" default).
            $normalDipilih = false;
            $adaCheckboxNormal = false;
            for ($i = 1; $i <= 4; $i++) {
                $val = $request->input('tindakan_bayi_normal_'.$i);
                if ($val === '1') {
                    $normalDipilih = true;
                }
                if ($val !== null && $val !== '' && $val !== '0') {
                    $adaCheckboxNormal = true;
                }
            }
            if ($adaCheckboxNormal && ! $normalDipilih) {
                $validator->errors()->add('tindakan_bayi_normal_1', 'Minimal satu tindakan bayi normal wajib dipilih.');
            }

            if ($request->input('penilaian_bayi_baru_lahir') === 'Ada penyulit') {
                $abnormalDipilih = false;
                $adaCheckboxAbnormal = false;
                for ($i = 1; $i <= 9; $i++) {
                    $val = $request->input('tindakan_bayi_abnormal_'.$i);
                    if ($val === '1') {
                        $abnormalDipilih = true;
                    }
                    if ($val !== null && $val !== '' && $val !== '0') {
                        $adaCheckboxAbnormal = true;
                    }
                }
                if ($adaCheckboxAbnormal && ! $abnormalDipilih) {
                    $validator->errors()->add('tindakan_bayi_abnormal_1', 'Bila ada penyulit, minimal satu tindakan tidak normal wajib dipilih.');
                }
            }
        });
        $validator->validate();

        return array_merge(array_fill_keys(array_keys($this->kosong()), null), $validator->validated());
    }

    private function checkboxRules(): array
    {
        $rules = [];
        for ($i = 1; $i <= 4; $i++) {
            $rules['tindakan_bayi_normal_'.$i] = 'nullable|in:0,1';
        }
        for ($i = 1; $i <= 9; $i++) {
            $rules['tindakan_bayi_abnormal_'.$i] = 'nullable|in:0,1';
        }

        return $rules;
    }

    private function filteredData(array $data): array
    {
        $data = array_intersect_key($data, array_flip(EmrHelper::objekVariabels(
            (int) EmrHelper::formIdBySlug(self::SLUG)
        )));

        for ($i = 1; $i <= 4; $i++) {
            $data['tindakan_bayi_normal_'.$i] = $data['tindakan_bayi_normal_'.$i] === '1' ? '1' : '0';
        }
        for ($i = 1; $i <= 9; $i++) {
            $data['tindakan_bayi_abnormal_'.$i] = $data['tindakan_bayi_abnormal_'.$i] === '1' ? '1' : '0';
        }

        return $data;
    }
}
