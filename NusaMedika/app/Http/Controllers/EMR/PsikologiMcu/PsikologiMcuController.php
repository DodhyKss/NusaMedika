<?php

namespace App\Http\Controllers\EMR\PsikologiMcu;

use App\Helpers\AksesEhr;
use App\Helpers\EmrHelper;
use App\Http\Controllers\Controller;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Form Psikologi MCU (form 84, slug `psikologi_mcu`).
 *
 * Asesmen psikologis oleh psikolog klinis. `interpretasi` adalah field
 * TURUNAN — dihitung ulang di `filteredData()` dari `skala_cemas` +
 * `skala_depresi` (GAD-2 & PHQ-2 ringkas); nilai browser dibuang.
 *
 * Instrumen GAD-2/PHQ-2 (objek 668–669) adalah penambahan dari legacy yang
 * hanya punya `gangguan_kehidupan` & `saran` — lihat KONSEP_FORM_MCU §18.
 */
class PsikologiMcuController extends Controller
{
    private const SLUG = 'psikologi_mcu';

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

        return view('moduls.EMR.PsikologiMcu.index', compact(
            'registrasi_detail', 'riwayat', 'historyGrouped', 'emr_data',
            'formAction', 'isEdit', 'deleteAction', 'isView', 'emr_id', 'aksesCrud'
        ));
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
            EmrHelper::insert((int) $form_id, $this->filteredData($data, (int) $form_id), (int) $registrasi_detail_id);

            return redirect()->back()->with('success', 'Psikologi MCU berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan Psikologi MCU: '.$e->getMessage())->withInput();
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

            return redirect()->back()->with('success', 'Psikologi MCU berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui Psikologi MCU: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Psikologi MCU berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus Psikologi MCU: '.$e->getMessage());
        }
    }

    private function kosong(): array
    {
        return [
            'gangguan_kehidupan_ada' => '0', 'gangguan_kehidupan' => '',
            'skala_cemas' => '', 'skala_depresi' => '',
            'interpretasi' => '', 'saran' => '',
        ];
    }

    private function validated(Request $request): array
    {
        $rules = [
            'gangguan_kehidupan_ada' => 'required|in:0,1',
            'gangguan_kehidupan' => 'required_if:gangguan_kehidupan_ada,1|nullable|string|max:2000',
            'skala_cemas' => 'required|integer|between:0,4',
            'skala_depresi' => 'required|integer|between:0,4',
            'saran' => 'required|string|max:2000',
        ];

        return array_merge(
            ['interpretasi' => null],
            $request->validate($rules)
        );
    }

    private function filteredData(array $data, int $formId): array
    {
        $data = array_intersect_key($data, array_flip(EmrHelper::objekVariabels($formId)));

        // Interpretasi dihitung ulang dari total GAD-2 + PHQ-2; nilai browser dibuang.
        $total = (int) ($data['skala_cemas'] ?? 0) + (int) ($data['skala_depresi'] ?? 0);
        $data['interpretasi'] = match (true) {
            $total >= 5 => 'Rujukan ke psikolog/klinisi',
            $total >= 3 => 'Keluhan ringan',
            default => 'Normal',
        };

        // Bila tidak ada gangguan kehidupan, uraian di-null-kan (bukan unset).
        if (($data['gangguan_kehidupan_ada'] ?? '0') === '0') {
            $data['gangguan_kehidupan'] = null;
        }

        foreach ($data as $key => $value) {
            if (is_string($value) && trim($value) === '') {
                $data[$key] = null;
            }
        }

        return $data;
    }
}
