<?php

namespace App\Http\Controllers\EMR\SurveilansIvCatheter;

use App\Helpers\AksesEhr;
use App\Helpers\EmrHelper;
use App\Helpers\SelectOption;
use App\Http\Controllers\Controller;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Form Surveilans Pasien Terpasang IV Catheter Perifer (form 117,
 * slug `surveilans_iv_catheter`).
 *
 * 7 item pencegahan phlebitis + skala PIVAS 0-4 (KONSEP_SURVEILANS_INFEKSI.md §4).
 *
 * Aturan server:
 * 1. Konsistensi skor <-> gejala: `pivas_skor = 0` wajib `pivas_gejala_1`;
 *    `pivas_skor >= 3` wajib `pivas_gejala_4`.
 * 2. Deviasi terdokumentasi: `lokasi_iv` dibatasi 5 nilai vena perifer
 *    (literal `in:`) — UI menyaring `SelectOption::lokasi_alat_invasif`
 *    (14 nilai) ke 5 nilai itu, sesuai validasi KONSEP §4.
 */
class SurveilansIvCatheterController extends Controller
{
    private const SLUG = 'surveilans_iv_catheter';

    private const JAWABAN = ['Ya', 'Tidak', 'N/A'];

    /** Hanya vena perifer (KONSEP §4) — subset dari SelectOption::lokasi_alat_invasif. */
    private const LOKASI_PERIFER = [
        'Vena Mediana Cubiti', 'Vena Cephalica', 'Vena Basilica', 'Tangan', 'Kaki',
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

        $opsiLokasi = array_values(array_filter(
            SelectOption::get('lokasi_alat_invasif'),
            fn ($o) => in_array($o['value'], self::LOKASI_PERIFER, true)
        ));

        return view('moduls.EMR.SurveilansIvCatheter.index', compact(
            'registrasi_detail', 'riwayat', 'historyGrouped', 'emr_data',
            'formAction', 'isEdit', 'deleteAction', 'isView', 'emr_id', 'aksesCrud',
            'opsiLokasi'
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

            return redirect()->back()->with('success', 'Surveilans IV Catheter berhasil disimpan.');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan Surveilans IV Catheter: '.$e->getMessage())->withInput();
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

            return redirect()->back()->with('success', 'Surveilans IV Catheter berhasil diperbarui.');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui Surveilans IV Catheter: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Surveilans IV Catheter berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus Surveilans IV Catheter: '.$e->getMessage());
        }
    }

    private function validated(Request $request, int $form_id): array
    {
        $rules = [
            'tanggal_evaluasi' => 'required|date',
            'waktu_evaluasi' => 'required|date_format:H:i',
            'lokasi_iv' => 'required|in:'.implode(',', self::LOKASI_PERIFER),
            'nomor_iv' => 'required|string|max:50',
            'jenis_cairan' => 'required|string|max:200',
            'pivas_skor' => 'required|in:0,1,2,3,4',
            'pivas_gejala_1' => 'required|in:0,1',
            'pivas_gejala_2' => 'required|in:0,1',
            'pivas_gejala_3' => 'required|in:0,1',
            'pivas_gejala_4' => 'required|in:0,1',
            'tanggal_pasang' => 'nullable|date',
            'tanggal_lepas' => 'nullable|date|after_or_equal:tanggal_pasang',
        ];

        for ($i = 1; $i <= 7; $i++) {
            $rules['iv_'.$i] = 'required|in:'.implode(',', self::JAWABAN);
        }

        $validator = Validator::make($request->all(), $rules);
        $validator->after(function ($validator) use ($request) {
            $skor = (int) $request->input('pivas_skor', -1);

            if ($skor === 0 && $request->input('pivas_gejala_1') !== '1') {
                $validator->errors()->add('pivas_gejala_1', 'PIVAS skor 0 wajib menandai "tidak ada rasa sakit / tanda phlebitis".');
            }

            if ($skor >= 3 && $request->input('pivas_gejala_4') !== '1') {
                $validator->errors()->add('pivas_gejala_4', 'PIVAS skor >= 3 wajib menandai gejala berat.');
            }
        });
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
            'lokasi_iv' => '', 'nomor_iv' => '', 'jenis_cairan' => '',
            'pivas_skor' => '', 'tanggal_pasang' => '', 'tanggal_lepas' => '',
        ];

        for ($i = 1; $i <= 7; $i++) {
            $d['iv_'.$i] = '';
        }
        for ($i = 1; $i <= 4; $i++) {
            $d['pivas_gejala_'.$i] = '0';
        }

        return $d;
    }
}
