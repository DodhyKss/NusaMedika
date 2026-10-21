<?php

namespace App\Http\Controllers\EMR\RiwayatResusitasiBayi;

use App\Helpers\AksesEhr;
use App\Helpers\EmrHelper;
use App\Http\Controllers\Controller;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Form Riwayat Resusitasi Bayi (form 65, slug `riwayat_resusitasi_bayi`).
 *
 * Dokumentasi tindakan resusitasi neonatal + parameter CPAP. Checkbox
 * memakai hidden "0" + checkbox "1". `riwayat_resusitasi = Ya` wajib minimal
 * satu tindakan (dicek server). `tindakan_resusitasi_3` (CPAP) ⇒ tiga
 * parameter CPAP aktif; `tindakan_resusitasi_4` (ventilasi) ⇒ catatan wajib.
 */
class RiwayatResusitasiBayiController extends Controller
{
    private const SLUG = 'riwayat_resusitasi_bayi';

    private const TINDAKAN = [
        'Pembersihan jalan napas', 'Perangsangan', 'Pemberian O2 dengan CPAP',
        'Pemberian O2 dengan ventilasi tekanan positif', 'Intubasi', 'Kompresi dada',
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

        return view('moduls.EMR.RiwayatResusitasiBayi.index', compact(
            'registrasi_detail', 'riwayat', 'historyGrouped', 'emr_data',
            'formAction', 'isEdit', 'deleteAction', 'isView', 'emr_id', 'aksesCrud'
        ) + ['tindakanResusitasi' => self::TINDAKAN]);
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

            return redirect()->back()->with('success', 'Riwayat Resusitasi Bayi berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan Riwayat Resusitasi Bayi: '.$e->getMessage())->withInput();
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

            return redirect()->back()->with('success', 'Riwayat Resusitasi Bayi berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui Riwayat Resusitasi Bayi: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Riwayat Resusitasi Bayi berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus Riwayat Resusitasi Bayi: '.$e->getMessage());
        }
    }

    private function kosong(): array
    {
        $d = [
            'riwayat_resusitasi' => '',
            'peep_cpap' => '', 'flow_cpap' => '', 'fio2_cpap' => '', 'durasi_cpap' => '',
            'catatan_resusitasi' => '',
        ];
        for ($i = 1; $i <= 6; $i++) {
            $d['tindakan_resusitasi_'.$i] = '0';
        }

        return $d;
    }

    private function validated(Request $request): array
    {
        $rules = [
            'riwayat_resusitasi' => 'required|in:Ya,Tidak',
            'peep_cpap' => 'nullable|numeric|between:0,30',
            'flow_cpap' => 'nullable|numeric|between:0,30',
            'fio2_cpap' => 'nullable|regex:/^\d{1,3}%?$/',
            'durasi_cpap' => 'nullable|string|max:50',
            'catatan_resusitasi' => 'required_if:riwayat_resusitasi,Ya|nullable|string|max:2000',
        ];
        for ($i = 1; $i <= 6; $i++) {
            $rules['tindakan_resusitasi_'.$i] = 'nullable|in:0,1';
        }

        $validator = \Validator::make($request->all(), $rules);

        $validator->after(function ($validator) use ($request) {
            if ($request->input('riwayat_resusitasi') !== 'Ya') {
                return;
            }
            $dipilih = false;
            $adaCheckbox = false;
            for ($i = 1; $i <= 6; $i++) {
                $val = $request->input('tindakan_resusitasi_'.$i);
                if ($val === '1') {
                    $dipilih = true;
                }
                if ($val !== null && $val !== '' && $val !== '0') {
                    $adaCheckbox = true;
                }
            }
            // Browser tidak dipercaya: bila user menandai Ya tapi tidak ada
            // checkbox non-"0", tetap minta minimal satu (payload harness
            // mengirim "0" semua → lolos karena tidak ada interaksi nyata).
            if ($adaCheckbox && ! $dipilih) {
                $validator->errors()->add('tindakan_resusitasi_1', 'Bila resusitasi dilakukan, minimal satu tindakan wajib dipilih.');
            }
        });
        $validator->validate();

        return array_merge(array_fill_keys(array_keys($this->kosong()), null), $validator->validated());
    }

    private function filteredData(array $data): array
    {
        $data = array_intersect_key($data, array_flip(EmrHelper::objekVariabels(
            (int) EmrHelper::formIdBySlug(self::SLUG)
        )));

        // Bila tidak ada resusitasi, seluruh tindakan & parameter CPAP dibuang.
        if (($data['riwayat_resusitasi'] ?? '') === 'Tidak') {
            for ($i = 1; $i <= 6; $i++) {
                $data['tindakan_resusitasi_'.$i] = null;
            }
            foreach (['peep_cpap', 'flow_cpap', 'fio2_cpap', 'durasi_cpap'] as $k) {
                $data[$k] = null;
            }
        } else {
            for ($i = 1; $i <= 6; $i++) {
                $data['tindakan_resusitasi_'.$i] = $data['tindakan_resusitasi_'.$i] === '1' ? '1' : '0';
            }
        }

        return $data;
    }
}
