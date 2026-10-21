<?php

namespace App\Http\Controllers\EMR\AsesmenPemberianDarah;

use App\Helpers\AksesEhr;
use App\Helpers\EmrHelper;
use App\Helpers\SelectOption;
use App\Http\Controllers\Controller;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * Form Asesmen Pasien dengan Darah (form 108, slug `asesmen_pemberian_darah`).
 *
 * Asesmen pra-transfusi (KONSEP_ONKOLOGI_TRANSFUSI §5): golongan darah & Rh,
 * dasar penentuan, jenis & jumlah darah, alasan transfusi, riwayat transfusi
 * & reaksi alergi, tanggal permintaan, dokter pemohon — dasar permintaan
 * darah ke Bank Darah.
 *
 * Aturan yang ditegakkan SERVER:
 * 1. `pernah_transfusi = Tidak` → `reaksi_alergi` & `pantangan_transfusi`
 *    di-set null.
 * 2. `nomor_hasil_lab` nullable tanpa linkage LIS (tidak ada guard).
 */
class AsesmenPemberianDarahController extends Controller
{
    private const SLUG = 'asesmen_pemberian_darah';

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

        return view('moduls.EMR.AsesmenPemberianDarah.index', compact(
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

            return redirect()->back()->with('success', 'Asesmen Pasien dengan Darah berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan Asesmen Pasien dengan Darah: '.$e->getMessage())->withInput();
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

            return redirect()->back()->with('success', 'Asesmen Pasien dengan Darah berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui Asesmen Pasien dengan Darah: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Asesmen Pasien dengan Darah berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus Asesmen Pasien dengan Darah: '.$e->getMessage());
        }
    }

    private function validated(Request $request, int $form_id): array
    {
        $rules = [
            'golongan_darah' => ['required', Rule::in(array_column(SelectOption::get('golongan_darah'), 'value'))],
            'rhesus' => 'required|in:Positif,Negatif',
            'dasar_penentuan' => 'required|in:Sesuai anamnesis pasien,Sesuai hasil lab',
            'nomor_hasil_lab' => 'nullable|string|max:100',
            'jenis_darah' => 'required|in:Whole Blood,Packed Red Cell,Plasma,Platelet,Cryoprecipitate,Lainnya',
            'jumlah_darah' => 'required|integer|min:1|max:10000',
            'alasan_transfusi' => 'required|string|max:1000',
            'pernah_transfusi' => 'required|in:Ya,Tidak',
            'reaksi_alergi' => 'required_if:pernah_transfusi,Ya|nullable|in:Ya,Tidak',
            'pantangan_transfusi' => 'required_if:reaksi_alergi,Ya|nullable|string|max:500',
            'tanggal_permintaan' => 'required|date',
            'dokter_pemohon_id' => 'required|integer|exists:pegawai,pegawai_id',
        ];

        $validator = Validator::make($request->all(), $rules);
        $validator->validate();

        return array_merge(
            array_fill_keys(EmrHelper::objekVariabels($form_id), null),
            $validator->validated()
        );
    }

    /**
     * Riwayat transfusi negatif → reaksi alergi & pantangan tidak berlaku
     * (di-set null supaya baris `emr_detail` tetap ada).
     */
    private function filteredData(array $data): array
    {
        if (($data['pernah_transfusi'] ?? null) === 'Tidak') {
            $data['reaksi_alergi'] = null;
            $data['pantangan_transfusi'] = null;
        }

        return $data;
    }

    private function kosong(): array
    {
        return [
            'golongan_darah' => '', 'rhesus' => '', 'dasar_penentuan' => '',
            'nomor_hasil_lab' => null, 'jenis_darah' => '', 'jumlah_darah' => '',
            'alasan_transfusi' => '', 'pernah_transfusi' => '', 'reaksi_alergi' => null,
            'pantangan_transfusi' => null, 'tanggal_permintaan' => '', 'dokter_pemohon_id' => '',
        ];
    }
}
