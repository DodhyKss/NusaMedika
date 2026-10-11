<?php

namespace App\Http\Controllers\EMR\AsesmenAkhirKehidupan;

use App\Helpers\AksesEhr;
use App\Helpers\EmrHelper;
use App\Helpers\SelectOption;
use App\Http\Controllers\Controller;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * Form Asesmen Akhir Kehidupan (form 123, slug `asesmen_akhir_kehidupan`).
 *
 * Fokus form ini adalah kerangka waktu asesmen, gejala fisik terminal, dan
 * keputusan paliatif. Panel gejala, spiritual, psikologis, dan kebutuhan
 * ditangani oleh dokumen-domain lain (KONSEP_ASESMEN_GIZI,
 * KONSEP_PSIKOSOSIAL_KEROHANIAN, KONSEP_DISCHARGE_PLANNING) dan akan
 * ditambahkan sebagai partial inklusif - lihat risiko R11 pada
 * docs/KONSEP_ASUHAN_KEPERAWATAN_LANJUTAN.md.
 *
 * `skor_nyeri` hanya berlaku bila `nyeri_terakhir` = Ya; selain itu field itu
 * dibuang di `filteredData()`.
 */
class AsesmenAkhirKehidupanController extends Controller
{
    private const SLUG = 'asesmen_akhir_kehidupan';

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

        return view('moduls.EMR.AsesmenAkhirKehidupan.index', compact(
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

            return redirect()->back()->with('success', 'Asesmen Akhir Kehidupan berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan Asesmen Akhir Kehidupan: '.$e->getMessage())->withInput();
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

            return redirect()->back()->with('success', 'Asesmen Akhir Kehidupan berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui Asesmen Akhir Kehidupan: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Asesmen Akhir Kehidupan berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus Asesmen Akhir Kehidupan: '.$e->getMessage());
        }
    }

    /**
     * Nilai kosong untuk form baru.
     */
    private function kosong(): array
    {
        return [
            'tanggal_asesmen_akhir' => '', 'waktu_asesmen_akhir' => '',
            'waktu_asesmen_awal' => '', 'waktu_asesmen_akhir_hari' => '',
            'kesadaran_terminal' => '', 'nyeri_terakhir' => '', 'skor_nyeri' => '',
            'gejala_terminal' => '', 'tindakan_paliatif' => '', 'catatan_keluarga' => '',
        ];
    }

    private function validated(Request $request): array
    {
        $opsional = [
            'skor_nyeri' => null,
            'catatan_keluarga' => null,
        ];

        return array_merge($opsional, $request->validate([
            'tanggal_asesmen_akhir' => 'required|date',
            'waktu_asesmen_akhir' => 'required|date_format:H:i',
            'waktu_asesmen_awal' => 'required|date',
            'waktu_asesmen_akhir_hari' => 'required|date|after_or_equal:waktu_asesmen_awal',

            'kesadaran_terminal' => ['required', Rule::in(array_column(SelectOption::get('kesadaran_kritis'), 'value'))],
            'nyeri_terakhir' => ['required', Rule::in(array_column(SelectOption::get('ya_tidak_kapital'), 'value'))],
            'skor_nyeri' => 'nullable|required_if:nyeri_terakhir,Ya|integer|between:0,10',

            'gejala_terminal' => 'required|string|max:2000',
            'tindakan_paliatif' => 'required|string|max:2000',
            'catatan_keluarga' => 'nullable|string|max:1000',
        ]));
    }

    /**
     * Hanya field terpetakan yang disimpan. `skor_nyeri` dibuang bila pasien
     * tidak reportó nyeri.
     */
    private function filteredData(array $data, int $formId): array
    {
        $data = array_intersect_key($data, array_flip(EmrHelper::objekVariabels($formId)));

        if (($data['nyeri_terakhir'] ?? '') !== 'Ya') {
            $data['skor_nyeri'] = null;
        }

        return $data;
    }
}
