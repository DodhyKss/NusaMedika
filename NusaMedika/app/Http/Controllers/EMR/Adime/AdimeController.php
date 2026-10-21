<?php

namespace App\Http\Controllers\EMR\Adime;

use App\Helpers\AksesEhr;
use App\Helpers\EmrHelper;
use App\Http\Controllers\Controller;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Form ADIME (form 44, slug `adime`).
 *
 * ADIME = **Assesment - Diagnosa - Intervensi - Monitoring - Evaluasi**,
 * kerangka dokumentasi asuhan yang dipakai legacy sebagai form terpisah dari
 * asesmen gizi. Perawat mengisi; ahli gizi yang memberikan hasil akhir.
 *
 * Hampir seluruh field-nya ME-REUSE objek yang sudah dideklarasikan form 39:
 * Diagnosa Gizi (377), Intervensi Gizi (379), Monitoring & Evaluasi (380),
 * Assessment (A) (3), Instruksi (I) (5), Tanggal/Waktu Asesmen (362/363).
 * Form ini tidak punya objek baru sama sekali.
 *
 * **Tiga `monitor_*` WAJIB punya mapping** ke objek 380 (tidak boleh
 * `objek_id = NULL` seperti `ews_na[]` di form 11) supaya tetap tercatat di
 * laporan. Nilainya `on`/`off`.
 *
 * Perbedaan `Str::studly`: `Str::studly('adime')` = `Adime` → folder
 * controller/view `Adime`, bukan `ADIME`.
 */
class AdimeController extends Controller
{
    private const SLUG = 'adime';

    /** Opsi monitoring (3 checkbox berbagi objek 380). */
    public const OPSI_MONITOR = [
        'monitor_asupan_makanan' => 'Asupan Makanan',
        'monitor_hasil_lab' => 'Hasil Laboratorium',
        'monitor_berat_badan' => 'Berat Badan',
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

        return view('moduls.EMR.Adime.index', compact(
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

            return redirect()->back()->with('success', 'ADIME berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan ADIME: '.$e->getMessage())->withInput();
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

            return redirect()->back()->with('success', 'ADIME berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui ADIME: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'ADIME berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus ADIME: '.$e->getMessage());
        }
    }

    private function kosong(): array
    {
        $d = [
            'tanggal_asesmen' => '', 'waktu_asesmen' => '',
            'assessment' => '', 'diagnosa_gizi' => '', 'intervensi' => '',
            'evaluasi' => '', 'instruksi' => '',
        ];
        foreach (array_keys(self::OPSI_MONITOR) as $variabel) {
            $d[$variabel] = null;
        }

        return $d;
    }

    private function validated(Request $request): array
    {
        $rules = [
            'tanggal_asesmen' => 'required|date',
            'waktu_asesmen' => 'required|date_format:H:i',
            'assessment' => 'required|string|max:2000',
            'diagnosa_gizi' => 'required|string|max:2000',
            'intervensi' => 'required|string|max:2000',
            'evaluasi' => 'required|string|max:2000',
            'instruksi' => 'required|string|max:2000',
        ];

        // Tiga checkbox monitoring: `in:on,off` (bukan boolean) supaya nilai
        // yang tersimpan eksplisit on/off dan mudah dibaca laporan.
        $rules['monitor_asupan_makanan'] = 'nullable|in:on,off';
        $rules['monitor_hasil_lab'] = 'nullable|in:on,off';
        $rules['monitor_berat_badan'] = 'nullable|in:on,off';

        return array_merge([
            'monitor_asupan_makanan' => null,
            'monitor_hasil_lab' => null,
            'monitor_berat_badan' => null,
        ], $request->validate($rules));
    }

    private function filteredData(array $data, int $formId): array
    {
        $data = array_intersect_key($data, array_flip(EmrHelper::objekVariabels($formId)));

        // Checkbox tidak dicentang → 'off' (bukan unset), supaya baris
        // emr_detail tetap ada untuk ketiga variabel monitoring.
        foreach (array_keys(self::OPSI_MONITOR) as $variabel) {
            $data[$variabel] = ! empty($data[$variabel]) ? 'on' : 'off';
        }

        foreach ($data as $key => $value) {
            if (is_string($value) && trim($value) === '') {
                $data[$key] = null;
            }
        }

        return $data;
    }
}
