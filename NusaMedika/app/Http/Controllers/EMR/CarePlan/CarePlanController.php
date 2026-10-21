<?php

namespace App\Http\Controllers\EMR\CarePlan;

use App\Helpers\AksesEhr;
use App\Helpers\EmrHelper;
use App\Helpers\SelectOption;
use App\Http\Controllers\Controller;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * Form Care Plan (form 20, slug `care_plan`).
 *
 * Rencana asuhan tertulis: tempat pertemuan, intervensi non farmakologis &
 * farmakologis, target terukur (perkiraan lama rawat, target perawatan, kriteria
 * pemulangan). Tidak ada field turunan pada form ini, jadi semua nilai yang
 * disimpan berasal dari input petugas.
 */
class CarePlanController extends Controller
{
    private const SLUG = 'care_plan';

    public function index($registrasi_detail_id, $emr_id = null)
    {
        $registrasi_detail = RegistrasiDetail::with('registrasi.pasien')->findOrFail($registrasi_detail_id);

        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'read'), 403);

        $aksesCrud = AksesEhr::flags((int) $form_id);
        $riwayat = EmrHelper::emrList((int) $form_id, (int) $registrasi_detail->registrasi_id);
        $historyGrouped = EmrHelper::historyKunjunganGrouped($registrasi_detail);

        // Tidak boleh membuat data baru -> tampilkan riwayat terakhir.
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

        return view('moduls.EMR.CarePlan.index', compact(
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

            return redirect()->back()->with('success', 'Care Plan berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan Care Plan: '.$e->getMessage())->withInput();
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

            return redirect()->back()->with('success', 'Care Plan berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui Care Plan: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Care Plan berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus Care Plan: '.$e->getMessage());
        }
    }

    /**
     * Nilai kosong untuk form baru. Tanggal default hari ini, sisanya kosong
     * (petugas wajib mengisinya sendiri).
     */
    private function kosong(): array
    {
        return [
            'tanggal_care_plan' => now()->toDateString(),
            'perkiraan_lama_rawat' => '',
            'tempat_pertemuan' => '',
            'intervensi_non_farmakologis' => '',
            'intervensi_farmakologis' => '',
            'target_perawatan' => '',
            'kriteria_pemulangan' => '',
            'catatan' => '',
        ];
    }

    private function validated(Request $request): array
    {
        return array_merge([
            'catatan' => null,
        ], $request->validate([
            'tanggal_care_plan' => 'required|date',
            'perkiraan_lama_rawat' => 'required|integer|min:0|max:100',
            'tempat_pertemuan' => ['required', Rule::in(array_column(SelectOption::get('tempat_pertemuan'), 'value'))],
            'intervensi_non_farmakologis' => 'required|string|max:3000',
            'intervensi_farmakologis' => 'required|string|max:3000',
            'target_perawatan' => 'required|string|max:1000',
            'kriteria_pemulangan' => 'required|string|max:1000',
            'catatan' => 'nullable|string|max:1000',
        ]));
    }

    /**
     * Hanya variabel yang ada di mapping form 20 yang boleh disimpan.
     */
    private function filteredData(array $data, int $formId): array
    {
        return array_intersect_key($data, array_flip(EmrHelper::objekVariabels($formId)));
    }
}
