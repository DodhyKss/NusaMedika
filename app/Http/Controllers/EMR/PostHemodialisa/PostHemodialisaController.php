<?php

namespace App\Http\Controllers\EMR\PostHemodialisa;

use App\Helpers\AksesEhr;
use App\Helpers\EmrHelper;
use App\Helpers\EwsHelper;
use App\Helpers\HemodialisaHelper;
use App\Http\Controllers\Controller;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * Form Post Hemodialisa (form 104, slug `post_hemodialisa`).
 *
 * Evaluasi setelah sesi (KONSEP §7): struktur form 103 + parameter khas
 * post-sesi (TBV, UFG, Kt/V, Convective Volume) + terapi tambahan.
 * Turunan server: `ews` (EwsHelper). Kt/V < 1,2 memicu peringatan UI
 * (bukan error — hasil lab post-HD bisa diisi sementara).
 */
class PostHemodialisaController extends Controller
{
    private const SLUG = 'post_hemodialisa';

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

        $ktvInfo = HemodialisaHelper::ktv($emr_data['kt_v'] ?? null);

        return view('moduls.EMR.PostHemodialisa.index', compact(
            'registrasi_detail', 'riwayat', 'historyGrouped', 'emr_data',
            'formAction', 'isEdit', 'deleteAction', 'isView', 'emr_id', 'aksesCrud', 'ktvInfo'
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

            return redirect()->back()->with('success', 'Post Hemodialisa berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan Post Hemodialisa: '.$e->getMessage())->withInput();
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

            return redirect()->back()->with('success', 'Post Hemodialisa berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui Post Hemodialisa: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Post Hemodialisa berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus Post Hemodialisa: '.$e->getMessage());
        }
    }

    private function validated(Request $request): array
    {
        $rules = [
            'tanggal_pertemuan' => 'required|date',
            'waktu_pertemuan' => 'required|date_format:H:i',
            'data_focus' => 'required|string|max:2000',
            'ku_pasien' => 'required|string|max:2000',
            'td_sistolik' => 'required|integer|min:50|max:300',
            'td_diastolik' => 'required|integer|min:30|max:200',
            'nadi' => 'required|integer|min:0|max:250',
            'pernapasan' => 'required|integer|min:0|max:120',
            'suhu' => 'required|numeric|between:30,45',
            'saturasi' => 'required|numeric|min:50|max:100',
            'berat_badan' => 'required|numeric|min:20|max:300',
            'bb_sebelumnya' => 'required|numeric|min:20|max:300',
            'bb_kering' => 'required|numeric|min:20|max:300',
            'hb' => 'required|numeric|min:0',
            'ureum' => 'required|numeric|min:0',
            'creatinine' => 'required|numeric|min:0',
            'hbsag' => 'required|in:Negatif,Positif',
            'kalium' => 'required|numeric|min:0',
            'sgot' => 'required|numeric|min:0',
            'sgpt' => 'required|numeric|min:0',
            'hasil_pemeriksaan_lain' => 'nullable|string|max:2000',
            'masalah_keperawatan' => 'required|string|max:2000',
            'tindakan_keperawatan' => 'required|string|max:2000',
            'terapi_obat' => 'required|string|max:2000',
            'evaluasi' => 'required|string|max:2000',
            'keterangan' => 'nullable|string|max:2000',
            'tbv' => 'required|numeric|min:1000|max:8000',
            'ufg' => 'required|numeric|min:0|max:10',
            'kt_v' => 'required|numeric|min:0|max:5',
            'convective_volume' => 'required|numeric|min:0|max:50',
            'transfusi' => 'nullable|numeric|min:0',
            'd40' => 'nullable|numeric|min:0',
            'ca_glukonas' => 'nullable|numeric|min:0',
            'kcl' => 'nullable|numeric|min:0',
            'renxamin' => 'nullable|numeric|min:0',
            'erythropoietin' => 'nullable|numeric|min:0',
        ];

        $validated = $request->validate($rules);

        if ((float) $validated['berat_badan'] < (float) $validated['bb_kering']) {
            throw ValidationException::withMessages([
                'berat_badan' => 'Berat badan saat ini tidak boleh kurang dari berat badan kering.',
            ]);
        }

        return $validated;
    }

    /** Turunan server: EWS dihitung ulang; nilai browser dibuang. */
    private function filteredData(array $data, int $formId): array
    {
        $data = array_intersect_key($data, array_flip(EmrHelper::objekVariabels($formId)));

        $ews = EwsHelper::hitung($data + ['oksigen' => 'Air']);
        $data['ews'] = $ews['lengkap'] ? $ews['total'] : null;

        return $data;
    }

    private function kosong(): array
    {
        return [
            'tanggal_pertemuan' => '', 'waktu_pertemuan' => '', 'data_focus' => '', 'ku_pasien' => '',
            'td_sistolik' => '', 'td_diastolik' => '', 'nadi' => '', 'pernapasan' => '', 'suhu' => '',
            'saturasi' => '', 'berat_badan' => '', 'bb_sebelumnya' => '', 'bb_kering' => '', 'ews' => '',
            'hb' => '', 'ureum' => '', 'creatinine' => '', 'hbsag' => '', 'kalium' => '', 'sgot' => '', 'sgpt' => '',
            'hasil_pemeriksaan_lain' => '', 'masalah_keperawatan' => '', 'tindakan_keperawatan' => '',
            'terapi_obat' => '', 'evaluasi' => '', 'keterangan' => '',
            'tbv' => '', 'ufg' => '', 'kt_v' => '', 'convective_volume' => '',
            'transfusi' => '', 'd40' => '', 'ca_glukonas' => '', 'kcl' => '', 'renxamin' => '', 'erythropoietin' => '',
        ];
    }
}
