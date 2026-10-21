<?php

namespace App\Http\Controllers\EMR\CatatanBedah;

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
 * Form Catatan Bedah (form 93, slug `catatan_bedah`).
 *
 * Catatan bedah yang didokumentasikan di ruang operasi (KONSEP §11):
 * pemeriksaan fisik, posisi, alat, pembedahan, pembiusan, drainase,
 * torniquet, diathermi, cairan/darah, sirkuler & instruksi pasca operasi.
 */
class CatatanBedahController extends Controller
{
    private const SLUG = 'catatan_bedah';

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

        return view('moduls.EMR.CatatanBedah.index', compact(
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

            return redirect()->back()->with('success', 'Catatan Bedah berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan Catatan Bedah: '.$e->getMessage())->withInput();
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

            return redirect()->back()->with('success', 'Catatan Bedah berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui Catatan Bedah: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Catatan Bedah berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus Catatan Bedah: '.$e->getMessage());
        }
    }

    private function validated(Request $request, int $form_id): array
    {
        $rules = [
            'tanggal_catatan' => 'required|date',
            'jam_catatan' => 'required|date_format:H:i',
            'dokter_bedah' => 'required|integer|exists:pegawai,pegawai_id',
            'petugas_pendamping' => 'required|integer|exists:pegawai,pegawai_id',
            'prabedah' => 'required|string|max:2000',
            'pre_operasi' => 'required|string|max:2000',
            'pemeriksaan_fisik' => 'required|string|max:2000',
            'kulit' => 'required|string|max:2000',
            'warna_kulit' => 'required|string|max:2000',
            'keadaan_umum' => ['required', Rule::in(array_column(SelectOption::get('keadaan_umum_pulih'), 'value'))],
            'posisi_operasi' => ['required', Rule::in(array_column(SelectOption::get('posisi_operasi'), 'value'))],
            'lokasi_operasi' => 'required|string|max:2000',
            'peralatan' => 'required|string|max:2000',
            'pasang_alat' => 'required|string|max:2000',
            'kebutuhan_kamar' => 'required|string|max:2000',
            'klarifikasi' => 'required|string|max:2000',
            'pembedahan' => 'required|string|max:2000',
            'pembiusan' => 'required|string|max:2000',
            'pemakaian_drain' => 'required|string|max:2000',
            'torniquet' => 'required|string|max:2000',
            'diathermi' => 'required|string|max:2000',
            'jumlah_cairan' => 'required|numeric|min:0',
            'perawatan_darah' => 'required|string|max:2000',
            'sirkuler_a' => 'required|string|max:2000',
            'asisten' => 'required|string|max:2000',
            'instruksi' => 'required|string|max:2000',
            'diagnosa_medis' => 'required|string|max:2000',
            'jenis_operasi' => 'required|in:Efektif,Darurat',
        ];

        $validator = Validator::make($request->all(), $rules);
        $validator->validate();

        return array_merge(
            array_fill_keys(EmrHelper::objekVariabels($form_id), null),
            $validator->validated()
        );
    }

    private function kosong(): array
    {
        return [
            'tanggal_catatan' => '', 'jam_catatan' => '',
            'dokter_bedah' => '', 'petugas_pendamping' => '',
            'prabedah' => '', 'pre_operasi' => '',
            'pemeriksaan_fisik' => '', 'kulit' => '', 'warna_kulit' => '', 'keadaan_umum' => '',
            'posisi_operasi' => '', 'lokasi_operasi' => '',
            'peralatan' => '', 'pasang_alat' => '',
            'kebutuhan_kamar' => '', 'klarifikasi' => '',
            'pembedahan' => '', 'pembiusan' => '', 'pemakaian_drain' => '',
            'torniquet' => '', 'diathermi' => '',
            'jumlah_cairan' => '', 'perawatan_darah' => '',
            'sirkuler_a' => '', 'asisten' => '',
            'instruksi' => '', 'diagnosa_medis' => '',
            'jenis_operasi' => '',
        ];
    }
}
