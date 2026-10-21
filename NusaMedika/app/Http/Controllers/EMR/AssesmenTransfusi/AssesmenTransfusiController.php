<?php

namespace App\Http\Controllers\EMR\AssesmenTransfusi;

use App\Helpers\AksesEhr;
use App\Helpers\EmrHelper;
use App\Http\Controllers\Controller;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

/**
 * Form Assesmen Transfusi (form 109, slug `assesmen_transfusi`).
 *
 * Persiapan sebelum komponen darah masuk (KONSEP_ONKOLOGI_TRANSFUSI §6):
 * identitas labu darah, keadaan umum, TTV, dan **verifikasi ganda 4 butir ×
 * dua perawat** (SESUAI / TIDAK SESUAI).
 *
 * Aturan yang ditegakkan SERVER:
 * 1. `perawat_2_id` wajib berbeda dari `perawat_1_id` (`different`) — inti
 *    verifikasi ganda.
 * 2. Badge "verifikasi tidak sesuai" & peringatan jumlah labu vs form 108
 *    = tampilan non-blocking di view.
 *
 * Nama folder memakai `Assesmen` (dua s) mengikuti slug `assesmen_transfusi`.
 */
class AssesmenTransfusiController extends Controller
{
    private const SLUG = 'assesmen_transfusi';

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

        // Pembanding jumlah labu dengan asesmen form 108 (peringatan view
        // non-blocking — menyimpan tetap boleh walau beda).
        $bandingDarah = EmrHelper::latestValuesByVariabel(108, (int) $registrasi_detail_id, ['jumlah_darah', 'jenis_darah']);

        return view('moduls.EMR.AssesmenTransfusi.index', compact(
            'registrasi_detail', 'riwayat', 'historyGrouped', 'emr_data',
            'formAction', 'isEdit', 'deleteAction', 'isView', 'emr_id', 'aksesCrud',
            'bandingDarah'
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

            return redirect()->back()->with('success', 'Assesmen Transfusi berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan Assesmen Transfusi: '.$e->getMessage())->withInput();
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

            return redirect()->back()->with('success', 'Assesmen Transfusi berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui Assesmen Transfusi: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Assesmen Transfusi berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus Assesmen Transfusi: '.$e->getMessage());
        }
    }

    private function validated(Request $request, int $form_id): array
    {
        // 8 butir verifikasi ditulis literal penuh — ruleMap() harness hanya
        // mem-parse string literal per key (pelajaran form 88).
        $rules = [
            'tanggal_persiapan' => 'required|date',
            'waktu_persiapan' => 'required|date_format:H:i',
            'no_labu_darah' => 'required|string|max:50',
            'jumlah_labu_darah' => 'required|integer|min:1|max:10000',
            'jenis_labu_darah' => 'required|in:Whole Blood,Packed Red Cell,Plasma,Platelet,Cryoprecipitate',
            'keadaan_umum' => 'required|in:Baik,Cukup,Buruk',
            'keluhan' => 'required|string|max:500',
            'dc1_identitas' => 'required|in:SESUAI,TIDAK SESUAI',
            'dc1_jumlah' => 'required|in:SESUAI,TIDAK SESUAI',
            'dc1_jenis' => 'required|in:SESUAI,TIDAK SESUAI',
            'dc1_golongan' => 'required|in:SESUAI,TIDAK SESUAI',
            'dc2_identitas' => 'required|in:SESUAI,TIDAK SESUAI',
            'dc2_jumlah' => 'required|in:SESUAI,TIDAK SESUAI',
            'dc2_jenis' => 'required|in:SESUAI,TIDAK SESUAI',
            'dc2_golongan' => 'required|in:SESUAI,TIDAK SESUAI',
            'perawat_1_id' => 'required|integer|exists:pegawai,pegawai_id',
            'perawat_2_id' => 'required|integer|exists:pegawai,pegawai_id|different:perawat_1_id',
            'sistolik' => 'required|integer|min:50|max:300',
            'diastolik' => 'required|integer|min:20|max:200',
            'nadi' => 'required|integer|min:20|max:250',
            'pernapasan' => 'required|integer|min:5|max:80',
            'suhu' => 'required|numeric|min:30|max:45',
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
            'tanggal_persiapan' => '', 'waktu_persiapan' => '',
            'no_labu_darah' => '', 'jumlah_labu_darah' => '', 'jenis_labu_darah' => '',
            'keadaan_umum' => '', 'keluhan' => '',
            'dc1_identitas' => '', 'dc1_jumlah' => '', 'dc1_jenis' => '', 'dc1_golongan' => '',
            'dc2_identitas' => '', 'dc2_jumlah' => '', 'dc2_jenis' => '', 'dc2_golongan' => '',
            'perawat_1_id' => '', 'perawat_2_id' => '',
            'sistolik' => '', 'diastolik' => '', 'nadi' => '', 'pernapasan' => '', 'suhu' => '',
        ];
    }
}
