<?php

namespace App\Http\Controllers\EMR\AssesmenSpiritual;

use App\Helpers\AksesEhr;
use App\Helpers\EmrHelper;
use App\Http\Controllers\Controller;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

/**
 * Form Asesmen Spiritual (form 113, slug `assesmen_spiritual`).
 *
 * KONSEP_PSIKOSOSIAL_KEROHANIAN.md §4: manajemen gejala saat ini (8 masalah
 * keperawatan), orientasi spiritual (perlu pelayanan Ya/Tidak + petugas),
 * serta urusan & kebutuhan spiritual (doa / bimbingan / pendamping — masing-
 * masing Ya/Tidak + petugas). Hasilnya menjadi dasar form 114.
 *
 * Aturan server: tiap `petugas_*` di-set null bila radio gerbangnya ≠ 'Ya'.
 * Ejaan legacy `Konstipasi` (masalah_5) dipertahankan persis — lihat KONSEP §4.
 */
class AssesmenSpiritualController extends Controller
{
    private const SLUG = 'assesmen_spiritual';

    /** 8 masalah keperawatan manajemen gejala (checkbox hidden 0 + checkbox 1). */
    private const MASALAH = [
        'masalah_1', 'masalah_2', 'masalah_3', 'masalah_4',
        'masalah_5', 'masalah_6', 'masalah_7', 'masalah_8',
    ];

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

        return view('moduls.EMR.AssesmenSpiritual.index', compact(
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

            return redirect()->back()->with('success', 'Asesmen Spiritual berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan Asesmen Spiritual: '.$e->getMessage())->withInput();
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

            return redirect()->back()->with('success', 'Asesmen Spiritual berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui Asesmen Spiritual: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Asesmen Spiritual berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus Asesmen Spiritual: '.$e->getMessage());
        }
    }

    private function validated(Request $request, int $form_id): array
    {
        $rules = [
            'masalah_1' => 'nullable|in:0,1',
            'masalah_2' => 'nullable|in:0,1',
            'masalah_3' => 'nullable|in:0,1',
            'masalah_4' => 'nullable|in:0,1',
            'masalah_5' => 'nullable|in:0,1',
            'masalah_6' => 'nullable|in:0,1',
            'masalah_7' => 'nullable|in:0,1',
            'masalah_8' => 'nullable|in:0,1',
            'spiritual' => 'required|in:Tidak,Ya',
            'rohani_1' => 'required|in:Tidak,Ya',
            'rohani_2' => 'required|in:Tidak,Ya',
            'rohani_3' => 'required|in:Tidak,Ya',
            'petugas_spiritual' => 'required_if:spiritual,Ya|nullable|string|max:150',
            'petugas_rohani_1' => 'required_if:rohani_1,Ya|nullable|string|max:150',
            'petugas_rohani_2' => 'required_if:rohani_2,Ya|nullable|string|max:150',
            'petugas_rohani_3' => 'required_if:rohani_3,Ya|nullable|string|max:150',
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
        if (($data['spiritual'] ?? null) !== 'Ya') {
            $data['petugas_spiritual'] = null;
        }

        foreach ([1, 2, 3] as $i) {
            if (($data['rohani_'.$i] ?? null) !== 'Ya') {
                $data['petugas_rohani_'.$i] = null;
            }
        }

        return $data;
    }

    private function kosong(): array
    {
        $d = ['spiritual' => '', 'rohani_1' => '', 'rohani_2' => '', 'rohani_3' => ''];
        $petugas = ['petugas_spiritual', 'petugas_rohani_1', 'petugas_rohani_2', 'petugas_rohani_3'];

        foreach (self::MASALAH as $butir) {
            $d[$butir] = '0';
        }
        foreach ($petugas as $key) {
            $d[$key] = '';
        }

        return $d;
    }
}
