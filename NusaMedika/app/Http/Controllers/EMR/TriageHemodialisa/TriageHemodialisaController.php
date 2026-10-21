<?php

namespace App\Http\Controllers\EMR\TriageHemodialisa;

use App\Helpers\AksesEhr;
use App\Helpers\EmrHelper;
use App\Helpers\HemodialisaHelper;
use App\Http\Controllers\Controller;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Form Triage Hemodialisa (form 105, slug `triage_hemodialisa`).
 *
 * Triase awal sebelum sesi HD (KONSEP §8): keluhan, BB kering/saat ini,
 * kesesuaian care plan, informed consent & administrasi. Peringatan
 * gain/susut BB dihitung server via HemodialisaHelper::selisihBb().
 */
class TriageHemodialisaController extends Controller
{
    private const SLUG = 'triage_hemodialisa';

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

        $selisih = HemodialisaHelper::selisihBb(
            is_numeric($emr_data['berat_badan'] ?? null) ? (float) $emr_data['berat_badan'] : null,
            is_numeric($emr_data['bb_kering'] ?? null) ? (float) $emr_data['bb_kering'] : null,
        );

        return view('moduls.EMR.TriageHemodialisa.index', compact(
            'registrasi_detail', 'riwayat', 'historyGrouped', 'emr_data',
            'formAction', 'isEdit', 'deleteAction', 'isView', 'emr_id', 'aksesCrud', 'selisih'
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
            EmrHelper::insert((int) $form_id, $data, (int) $registrasi_detail_id);

            return redirect()->back()->with('success', 'Triage Hemodialisa berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan Triage Hemodialisa: '.$e->getMessage())->withInput();
        }
    }

    public function update(Request $request, $registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'update'), 403);

        $data = $this->validated($request);

        try {
            EmrHelper::update((int) $emr_id, (int) $form_id, $data);

            return redirect()->back()->with('success', 'Triage Hemodialisa berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui Triage Hemodialisa: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Triage Hemodialisa berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus Triage Hemodialisa: '.$e->getMessage());
        }
    }

    private function validated(Request $request): array
    {
        $rules = [
            'keluhan' => 'required|string|max:2000',
            'bb_kering' => 'required|numeric|min:20|max:300',
            'berat_badan' => 'required|numeric|min:20|max:300',
            'care_plan' => 'required|in:sesuai,tidak_sesuai',
            'informed_consent' => 'nullable|in:lengkap',
            'administrasi' => 'nullable|in:lengkap',
        ];

        $validated = $request->validate($rules);

        // Peringatan gain/susut BB (KONSEP §8.6) TIDAK disimpan — hanya
        // ditampilkan di UI via HemodialisaHelper::selisihBb().

        return $validated;
    }

    private function kosong(): array
    {
        return [
            'keluhan' => '', 'bb_kering' => '', 'berat_badan' => '', 'care_plan' => '',
            'informed_consent' => '', 'administrasi' => '',
        ];
    }
}
