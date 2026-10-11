<?php

namespace App\Http\Controllers\EMR\RencanaAsuhanKeperawatan;

use App\Helpers\AksesEhr;
use App\Helpers\EmrHelper;
use App\Helpers\SelectOption;
use App\Http\Controllers\Controller;
use App\Models\ICD;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * Form Rencana Asuhan Keperawatan (form 22, slug `rencana_asuhan_keperawatan`).
 *
 * Carrier utama Care Plan SDKI: satu diagnosa keperawatan, tujuan+criteri hasil,
 * intervensi, serta indikasi/objektif pendukung. Field snapshot
 * `nama_diagnosa_keperawatan` SELALU ditulis ulang dari master di server - nilai
 * dari browser dibuang.
 *
 * Catatan master: tabel `diagnosa_keperawatan` legacy (5 tabel) belum ada di
 * NusaMedika (risiko R3 pada docs/KONSEP_ASUHAN_KEPERAWATAN_LANJUTAN.md),
 * sehingga `diagnosa_keperawatan_id` memakai master `icd` yang sudah ada.
 */
class RencanaAsuhanKeperawatanController extends Controller
{
    private const SLUG = 'rencana_asuhan_keperawatan';

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

        $icdList = ICD::aktif()->orderBy('kode_diagnosa')->get(['icd_id', 'kode_diagnosa', 'nama_diagnosa']);

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

        return view('moduls.EMR.RencanaAsuhanKeperawatan.index', compact(
            'registrasi_detail', 'riwayat', 'historyGrouped', 'emr_data',
            'formAction', 'isEdit', 'deleteAction', 'isView', 'emr_id', 'aksesCrud',
            'icdList'
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

            return redirect()->back()->with('success', 'Rencana Asuhan Keperawatan berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan Rencana Asuhan Keperawatan: '.$e->getMessage())->withInput();
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

            return redirect()->back()->with('success', 'Rencana Asuhan Keperawatan berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui Rencana Asuhan Keperawatan: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Rencana Asuhan Keperawatan berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus Rencana Asuhan Keperawatan: '.$e->getMessage());
        }
    }

    /**
     * Nilai kosong untuk form baru.
     */
    private function kosong(): array
    {
        return [
            'diagnosa_keperawatan_id' => '', 'nama_diagnosa_keperawatan' => '',
            'tujuan_keperawatan' => '', 'satuan_waktu_tujuan' => '',
            'kriteria_hasil' => '', 'intervensi_keperawatan' => '',
            'indikasi_keperawatan' => '', 'objektif_keperawatan' => '',
            'catatan_rencana' => '',
        ];
    }

    private function validated(Request $request): array
    {
        $opsional = [
            'nama_diagnosa_keperawatan' => null,
            'kriteria_hasil' => null,
            'intervensi_keperawatan' => null,
            'indikasi_keperawatan' => null,
            'objektif_keperawatan' => null,
            'catatan_rencana' => null,
        ];

        return array_merge($opsional, $request->validate([
            'diagnosa_keperawatan_id' => ['required', 'integer', 'exists:icd,icd_id'],
            'tujuan_keperawatan' => ['required', 'numeric', 'min:1', 'max:365'],
            'satuan_waktu_tujuan' => ['required', Rule::in(array_column(SelectOption::get('satuan_waktu_tujuan'), 'value'))],

            'kriteria_hasil' => 'nullable|string|max:2000',
            'intervensi_keperawatan' => 'nullable|string|max:2000',
            'indikasi_keperawatan' => 'nullable|string|max:2000',
            'objektif_keperawatan' => 'nullable|string|max:2000',
            'catatan_rencana' => 'nullable|string|max:1000',
        ]));
    }

    /**
     * Hanya field terpetakan yang disimpan. Snapshot nama diagnosa ditulis ulang
     * dari master `icd` - nilai dari browser dibuang.
     */
    private function filteredData(array $data, int $formId): array
    {
        $data = array_intersect_key($data, array_flip(EmrHelper::objekVariabels($formId)));

        $icdId = (int) ($data['diagnosa_keperawatan_id'] ?? 0);
        $icd = $icdId > 0 ? ICD::aktif()->where('icd_id', $icdId)->first() : null;
        $data['nama_diagnosa_keperawatan'] = $icd ? $icd->kode_diagnosa.' - '.$icd->nama_diagnosa : null;

        return $data;
    }
}
