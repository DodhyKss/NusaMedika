<?php

namespace App\Http\Controllers\EMR\MonitoringAnestesiSedasi;

use App\Helpers\AksesEhr;
use App\Helpers\EmrHelper;
use App\Http\Controllers\Controller;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

/**
 * Form Monitoring Anestesi & Sedasi (form 88, slug `monitoring_anestesi_sedasi`).
 *
 * Monitoring intra-operasi/intra-sedasi (KONSEP §6): tabel monitoring
 * berkala 20 baris (hemodinamik, suhu, oksigenasi, kesadaran AVPU, nyeri
 * VAS) + catatan kejadian kritis, lampiran scan, dan status monitoring.
 *
 * Baris menit 0/5/15/30 (baris 1..4) wajib; baris 5..20 opsional.
 * Validasi literal ditulis penuh (bukan loop) karena harness E2E
 * `tools/lib_pay.php` hanya membaca `'key' => 'rule'` dari sumber.
 */
class MonitoringAnestesiSedasiController extends Controller
{
    private const SLUG = 'monitoring_anestesi_sedasi';

    private const JUMLAH_BARIS = 20;

    /** Opsi kesadaran AVPU (KONSEP §6 — tidak ada di SelectOption). */
    private const AVPU = ['Alert', 'Voice', 'Pain', 'Unresponsive'];

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

        return view('moduls.EMR.MonitoringAnestesiSedasi.index', compact(
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

            return redirect()->back()->with('success', 'Monitoring Anestesi & Sedasi berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan Monitoring Anestesi: '.$e->getMessage())->withInput();
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

            return redirect()->back()->with('success', 'Monitoring Anestesi & Sedasi berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui Monitoring Anestesi: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Monitoring Anestesi & Sedasi berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus Monitoring Anestesi: '.$e->getMessage());
        }
    }

    private function validated(Request $request, int $form_id): array
    {
        // Baris 1..4 = menit 0, 5, 15, 30 (wajib); baris 5..20 opsional.
        $rules = [
            'tanggal_monitoring' => 'required|date',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i',
            'kejadian_kritis' => 'required_if:status_monitoring,Ongoing|string|max:2000',
            'lampiran_monitoring' => 'nullable|string|max:60000',
            'status_monitoring' => 'required|in:Ongoing,Completed',
            'hemodinamik_1' => 'required|string|max:255', 'suhu_1' => 'required|numeric', 'oksigenasi_1' => 'required|string|max:255', 'kesadaran_1' => 'required|in:Alert,Voice,Pain,Unresponsive', 'nyeri_1' => 'required|integer|between:0,10',
            'hemodinamik_2' => 'required|string|max:255', 'suhu_2' => 'required|numeric', 'oksigenasi_2' => 'required|string|max:255', 'kesadaran_2' => 'required|in:Alert,Voice,Pain,Unresponsive', 'nyeri_2' => 'required|integer|between:0,10',
            'hemodinamik_3' => 'required|string|max:255', 'suhu_3' => 'required|numeric', 'oksigenasi_3' => 'required|string|max:255', 'kesadaran_3' => 'required|in:Alert,Voice,Pain,Unresponsive', 'nyeri_3' => 'required|integer|between:0,10',
            'hemodinamik_4' => 'required|string|max:255', 'suhu_4' => 'required|numeric', 'oksigenasi_4' => 'required|string|max:255', 'kesadaran_4' => 'required|in:Alert,Voice,Pain,Unresponsive', 'nyeri_4' => 'required|integer|between:0,10',
            'hemodinamik_5' => 'nullable|string|max:255', 'suhu_5' => 'nullable|numeric', 'oksigenasi_5' => 'nullable|string|max:255', 'kesadaran_5' => 'nullable|in:Alert,Voice,Pain,Unresponsive', 'nyeri_5' => 'nullable|integer|between:0,10',
            'hemodinamik_6' => 'nullable|string|max:255', 'suhu_6' => 'nullable|numeric', 'oksigenasi_6' => 'nullable|string|max:255', 'kesadaran_6' => 'nullable|in:Alert,Voice,Pain,Unresponsive', 'nyeri_6' => 'nullable|integer|between:0,10',
            'hemodinamik_7' => 'nullable|string|max:255', 'suhu_7' => 'nullable|numeric', 'oksigenasi_7' => 'nullable|string|max:255', 'kesadaran_7' => 'nullable|in:Alert,Voice,Pain,Unresponsive', 'nyeri_7' => 'nullable|integer|between:0,10',
            'hemodinamik_8' => 'nullable|string|max:255', 'suhu_8' => 'nullable|numeric', 'oksigenasi_8' => 'nullable|string|max:255', 'kesadaran_8' => 'nullable|in:Alert,Voice,Pain,Unresponsive', 'nyeri_8' => 'nullable|integer|between:0,10',
            'hemodinamik_9' => 'nullable|string|max:255', 'suhu_9' => 'nullable|numeric', 'oksigenasi_9' => 'nullable|string|max:255', 'kesadaran_9' => 'nullable|in:Alert,Voice,Pain,Unresponsive', 'nyeri_9' => 'nullable|integer|between:0,10',
            'hemodinamik_10' => 'nullable|string|max:255', 'suhu_10' => 'nullable|numeric', 'oksigenasi_10' => 'nullable|string|max:255', 'kesadaran_10' => 'nullable|in:Alert,Voice,Pain,Unresponsive', 'nyeri_10' => 'nullable|integer|between:0,10',
            'hemodinamik_11' => 'nullable|string|max:255', 'suhu_11' => 'nullable|numeric', 'oksigenasi_11' => 'nullable|string|max:255', 'kesadaran_11' => 'nullable|in:Alert,Voice,Pain,Unresponsive', 'nyeri_11' => 'nullable|integer|between:0,10',
            'hemodinamik_12' => 'nullable|string|max:255', 'suhu_12' => 'nullable|numeric', 'oksigenasi_12' => 'nullable|string|max:255', 'kesadaran_12' => 'nullable|in:Alert,Voice,Pain,Unresponsive', 'nyeri_12' => 'nullable|integer|between:0,10',
            'hemodinamik_13' => 'nullable|string|max:255', 'suhu_13' => 'nullable|numeric', 'oksigenasi_13' => 'nullable|string|max:255', 'kesadaran_13' => 'nullable|in:Alert,Voice,Pain,Unresponsive', 'nyeri_13' => 'nullable|integer|between:0,10',
            'hemodinamik_14' => 'nullable|string|max:255', 'suhu_14' => 'nullable|numeric', 'oksigenasi_14' => 'nullable|string|max:255', 'kesadaran_14' => 'nullable|in:Alert,Voice,Pain,Unresponsive', 'nyeri_14' => 'nullable|integer|between:0,10',
            'hemodinamik_15' => 'nullable|string|max:255', 'suhu_15' => 'nullable|numeric', 'oksigenasi_15' => 'nullable|string|max:255', 'kesadaran_15' => 'nullable|in:Alert,Voice,Pain,Unresponsive', 'nyeri_15' => 'nullable|integer|between:0,10',
            'hemodinamik_16' => 'nullable|string|max:255', 'suhu_16' => 'nullable|numeric', 'oksigenasi_16' => 'nullable|string|max:255', 'kesadaran_16' => 'nullable|in:Alert,Voice,Pain,Unresponsive', 'nyeri_16' => 'nullable|integer|between:0,10',
            'hemodinamik_17' => 'nullable|string|max:255', 'suhu_17' => 'nullable|numeric', 'oksigenasi_17' => 'nullable|string|max:255', 'kesadaran_17' => 'nullable|in:Alert,Voice,Pain,Unresponsive', 'nyeri_17' => 'nullable|integer|between:0,10',
            'hemodinamik_18' => 'nullable|string|max:255', 'suhu_18' => 'nullable|numeric', 'oksigenasi_18' => 'nullable|string|max:255', 'kesadaran_18' => 'nullable|in:Alert,Voice,Pain,Unresponsive', 'nyeri_18' => 'nullable|integer|between:0,10',
            'hemodinamik_19' => 'nullable|string|max:255', 'suhu_19' => 'nullable|numeric', 'oksigenasi_19' => 'nullable|string|max:255', 'kesadaran_19' => 'nullable|in:Alert,Voice,Pain,Unresponsive', 'nyeri_19' => 'nullable|integer|between:0,10',
            'hemodinamik_20' => 'nullable|string|max:255', 'suhu_20' => 'nullable|numeric', 'oksigenasi_20' => 'nullable|string|max:255', 'kesadaran_20' => 'nullable|in:Alert,Voice,Pain,Unresponsive', 'nyeri_20' => 'nullable|integer|between:0,10',
        ];

        $validator = Validator::make($request->all(), $rules);

        // jam_selesai >= jam_mulai (KONSEP §6: withValidator).
        $validator->after(function ($validator) {
            $mulai = $this->input('jam_mulai');
            $selesai = $this->input('jam_selesai');
            if ($mulai !== '' && $selesai !== '' && strcmp($selesai, $mulai) < 0) {
                $validator->errors()->add('jam_selesai', 'Jam selesai tidak boleh sebelum jam mulai monitoring.');
            }
        });

        $validator->validate();

        $data = array_merge(
            array_fill_keys(EmrHelper::objekVariabels($form_id), null),
            $validator->validated()
        );

        // Lampiran: hanya data URL gambar yang disimpan (pola form 37).
        $lampiran = $data['lampiran_monitoring'] ?? null;
        if ($lampiran !== null && $lampiran !== '' && ! preg_match('#^data:image/[a-z.+-]+;base64,#i', $lampiran)) {
            $data['lampiran_monitoring'] = null;
        }

        return $data;
    }

    private function input(string $key): string
    {
        return (string) request()->input($key, '');
    }

    private function kosong(): array
    {
        $d = [
            'tanggal_monitoring' => '', 'jam_mulai' => '', 'jam_selesai' => '',
            'kejadian_kritis' => '', 'lampiran_monitoring' => '',
            'status_monitoring' => '',
        ];
        for ($i = 1; $i <= self::JUMLAH_BARIS; $i++) {
            $d['hemodinamik_'.$i] = '';
            $d['suhu_'.$i] = '';
            $d['oksigenasi_'.$i] = '';
            $d['kesadaran_'.$i] = '';
            $d['nyeri_'.$i] = '';
        }

        return $d;
    }
}
