<?php

namespace App\Http\Controllers\EMR\SurveilansTirahBaring;

use App\Helpers\AksesEhr;
use App\Helpers\EmrHelper;
use App\Http\Controllers\Controller;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Form Surveilans Pasien Tirah Baring Total (form 119,
 * slug `surveilans_tirah_baring`).
 *
 * HAP bundle checklist + gejala HAP (KONSEP_SURVEILANS_INFEKSI.md §6).
 *
 * Aturan server:
 * - Semua butir `hap_*` (selain `hap_ab_*`) wajib Ya/Tidak/N/A;
 *   `hap_ab_1` wajib teks, `hap_ab_2/3` opsional.
 * - Badge "Gejala HAP ditemukan" = informasional di tampilan JS.
 * - Auto-prefill leukosit dari TTE TERAKHIR (KONSEP opsional) TIDAK
 *   diimplementasikan — deviasi terdokumentasi (butuh keputusan tampilan).
 */
class SurveilansTirahBaringController extends Controller
{
    private const SLUG = 'surveilans_tirah_baring';

    private const JAWABAN = ['Ya', 'Tidak', 'N/A'];

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

        return view('moduls.EMR.SurveilansTirahBaring.index', compact(
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
            $data = $this->validated($request, (int) $form_id);

            EmrHelper::insert((int) $form_id, $data, (int) $registrasi_detail_id);

            return redirect()->back()->with('success', 'Surveilans Tirah Baring berhasil disimpan.');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan Surveilans Tirah Baring: '.$e->getMessage())->withInput();
        }
    }

    public function update(Request $request, $registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'update'), 403);

        try {
            $data = $this->validated($request, (int) $form_id);

            EmrHelper::update((int) $emr_id, (int) $form_id, $data);

            return redirect()->back()->with('success', 'Surveilans Tirah Baring berhasil diperbarui.');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui Surveilans Tirah Baring: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Surveilans Tirah Baring berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus Surveilans Tirah Baring: '.$e->getMessage());
        }
    }

    private function validated(Request $request, int $form_id): array
    {
        $rules = [
            'tanggal_evaluasi' => 'required|date',
            'waktu_evaluasi' => 'required|date_format:H:i',
            'hap_ab_1' => 'required|string|max:200',
            'hap_ab_2' => 'nullable|string|max:200',
            'hap_ab_3' => 'nullable|string|max:200',
            'keterangan' => 'nullable|string|max:2000',
        ];

        foreach ([
            'hap_1', 'hap_2', 'hap_3', 'hap_4',
            'hap_suhu_1', 'hap_suhu_2', 'hap_suhu_3',
            'hap_leukosit_1', 'hap_leukosit_2', 'hap_leukosit_3',
            'hap_sekresi_1', 'hap_sekresi_2',
            'hap_kultur_1', 'hap_kultur_2', 'hap_kultur_3',
        ] as $key) {
            $rules[$key] = 'required|in:'.implode(',', self::JAWABAN);
        }

        $validator = Validator::make($request->all(), $rules);
        $validator->validate();

        return array_merge(
            array_fill_keys(EmrHelper::objekVariabels($form_id), null),
            $validator->validated()
        );
    }

    private function kosong(): array
    {
        $d = [
            'tanggal_evaluasi' => '', 'waktu_evaluasi' => '',
            'hap_ab_1' => '', 'hap_ab_2' => '', 'hap_ab_3' => '',
            'keterangan' => '',
        ];

        foreach ([
            'hap_1', 'hap_2', 'hap_3', 'hap_4',
            'hap_suhu_1', 'hap_suhu_2', 'hap_suhu_3',
            'hap_leukosit_1', 'hap_leukosit_2', 'hap_leukosit_3',
            'hap_sekresi_1', 'hap_sekresi_2',
            'hap_kultur_1', 'hap_kultur_2', 'hap_kultur_3',
        ] as $key) {
            $d[$key] = '';
        }

        return $d;
    }
}
