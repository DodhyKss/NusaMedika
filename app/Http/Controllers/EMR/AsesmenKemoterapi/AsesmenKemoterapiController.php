<?php

namespace App\Http\Controllers\EMR\AsesmenKemoterapi;

use App\Helpers\AksesEhr;
use App\Helpers\EmrHelper;
use App\Http\Controllers\Controller;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

/**
 * Form Asesmen Kemoterapi (form 106, slug `asesmen_kemoterapi`).
 *
 * Asesmen awal sebelum siklus kemoterapi (KONSEP_ONKOLOGI_TRANSFUSI §3):
 * riwayat operasi/kemoterapi/radioterapi, siklus & protokol, penunjang
 * terjadwal, alat akses, reaksi, serta lima masalah keperawatan kemoterapi
 * (nyeri, risiko, cemas, nutrisi, infeksi) — masing-masing berpasangan
 * status (`Ya`/`Tidak`) + isi intervensi.
 *
 * Aturan yang ditegakkan SERVER:
 * 1. `isi_*` hanya relevan bila statusnya `Ya` — selain itu di-set null.
 * 2. `ket_reaksi` hanya relevan bila `reaksi_kemoterapi = Ada`.
 */
class AsesmenKemoterapiController extends Controller
{
    private const SLUG = 'asesmen_kemoterapi';

    /** Lima aspek masalah keperawatan kemoterapi (status + isi). */
    private const ASPEK = ['nyeri', 'resiko', 'cemas', 'nutrisi', 'infeksi'];

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

        return view('moduls.EMR.AsesmenKemoterapi.index', compact(
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

            return redirect()->back()->with('success', 'Asesmen Kemoterapi berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan Asesmen Kemoterapi: '.$e->getMessage())->withInput();
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

            return redirect()->back()->with('success', 'Asesmen Kemoterapi berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui Asesmen Kemoterapi: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Asesmen Kemoterapi berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus Asesmen Kemoterapi: '.$e->getMessage());
        }
    }

    private function validated(Request $request, int $form_id): array
    {
        $rules = [
            'tanggal_asesmen' => 'required|date',
            'waktu_asesmen' => 'required|date_format:H:i',
            'riwayat_operasi' => 'required|in:0,1',
            'riwayat_kemoterapi' => 'required|in:0,1',
            'riwayat_radioterapi' => 'required|in:0,1',
            'siklus' => 'required|string|max:50',
            'protokol' => 'required|string|max:100',
            'dpjp_utama' => 'required|integer|exists:pegawai,pegawai_id',
            'diagnosis' => 'required|string|max:2000',
            'penunjang_1' => 'required|in:0,1',
            'penunjang_2' => 'required|in:0,1',
            'penunjang_3' => 'required|in:0,1',
            'penunjang_4' => 'required|in:0,1',
            'tanggal_pemberian_obat' => 'required|date',
            'pasang_alat' => 'required|in:Infus,Catheter,Lainnya',
            'jenis_obat' => 'required|string|max:1000',
            'reaksi_kemoterapi' => 'required|in:Tidak ada,Ada',
            'ket_reaksi' => 'required_if:reaksi_kemoterapi,Ada|nullable|string|max:500',
            'kondisi_rahim' => 'nullable|string|max:1000',
            'perawat_kemoterapi' => 'required|string|max:150',
            'keluarga_kemoterapi' => 'required|string|max:150',
            'nyeri_kemoterapi' => 'required|in:Ya,Tidak',
            'isi_nyeri_kemoterapi' => 'required_if:nyeri_kemoterapi,Ya|nullable|string|max:2000',
            'resiko_kemoterapi' => 'required|in:Ya,Tidak',
            'isi_resiko_kemoterapi' => 'required_if:resiko_kemoterapi,Ya|nullable|string|max:2000',
            'cemas_kemoterapi' => 'required|in:Ya,Tidak',
            'isi_cemas_kemoterapi' => 'required_if:cemas_kemoterapi,Ya|nullable|string|max:2000',
            'nutrisi_kemoterapi' => 'required|in:Ya,Tidak',
            'isi_nutrisi_kemoterapi' => 'required_if:nutrisi_kemoterapi,Ya|nullable|string|max:2000',
            'infeksi_kemoterapi' => 'required|in:Ya,Tidak',
            'isi_infeksi_kemoterapi' => 'required_if:infeksi_kemoterapi,Ya|nullable|string|max:2000',
            'skala_nyeri' => 'nullable|integer|min:0|max:10',
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
        foreach (self::ASPEK as $aspek) {
            if (($data[$aspek.'_kemoterapi'] ?? null) !== 'Ya') {
                $data['isi_'.$aspek.'_kemoterapi'] = null;
            }
        }

        if (($data['reaksi_kemoterapi'] ?? null) !== 'Ada') {
            $data['ket_reaksi'] = null;
        }

        return $data;
    }

    private function kosong(): array
    {
        $d = [
            'tanggal_asesmen' => '', 'waktu_asesmen' => '',
            'riwayat_operasi' => '0', 'riwayat_kemoterapi' => '0', 'riwayat_radioterapi' => '0',
            'siklus' => '', 'protokol' => '', 'dpjp_utama' => '', 'diagnosis' => '',
            'penunjang_1' => '0', 'penunjang_2' => '0', 'penunjang_3' => '0', 'penunjang_4' => '0',
            'tanggal_pemberian_obat' => '', 'pasang_alat' => '', 'jenis_obat' => '',
            'reaksi_kemoterapi' => '', 'ket_reaksi' => null, 'kondisi_rahim' => null,
            'perawat_kemoterapi' => '', 'keluarga_kemoterapi' => '',
            'skala_nyeri' => null,
        ];

        foreach (self::ASPEK as $aspek) {
            $d[$aspek.'_kemoterapi'] = '';
            $d['isi_'.$aspek.'_kemoterapi'] = null;
        }

        return $d;
    }
}
