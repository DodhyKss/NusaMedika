<?php

namespace App\Http\Controllers\EMR\CatatanAnestesia;

use App\Helpers\AksesEhr;
use App\Helpers\EmrHelper;
use App\Helpers\SelectOption;
use App\Http\Controllers\Controller;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * Form Catatan Anestesia (form 99, slug `catatan_anestesia`).
 *
 * Catatan anestesi lengkap oleh anestesiologist (KONSEP §17):
 * jam selesai >= jam mulai, `lama_anestesia` TURUNAN dihitung server,
 * `caudel` wajib bila jenis Epidural/Spinal, `penanganan_intraop` wajib
 * bila ada kejadian tidak terduga.
 */
class CatatanAnestesiaController extends Controller
{
    private const SLUG = 'catatan_anestesia';

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

        return view('moduls.EMR.CatatanAnestesia.index', compact(
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

            return redirect()->back()->with('success', 'Catatan Anestesia berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan Catatan Anestesia: '.$e->getMessage())->withInput();
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

            return redirect()->back()->with('success', 'Catatan Anestesia berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui Catatan Anestesia: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Catatan Anestesia berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus Catatan Anestesia: '.$e->getMessage());
        }
    }

    private function validated(Request $request, int $form_id): array
    {
        $rules = [
            'tanggal_catatan' => 'required|date',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i',
            'lama_anestesia' => 'nullable|string|max:50',
            'dokter_anestesi' => 'required|integer|exists:pegawai,pegawai_id',
            'perawat_anestesi' => 'required|integer|exists:pegawai,pegawai_id',
            'jenis_anestesi' => 'required|in:Umum,Regional,Epidural,Spinal,Sedasi',
            'airway' => 'required|in:Ada,Tidak_ada',
            'airway_2' => 'required|string|max:2000',
            'ventilasi' => 'required|in:Spontan,Assisted,Controlled',
            'sirkuit' => 'required|string|max:2000',
            'caudel' => 'nullable|string|max:2000',
            'posisi' => 'required|in:Supine,Lateral,Prone,Lithotomy,Semi Fowler,Trendelenburg',
            'monitor' => 'required|string|max:2000',
            'asa' => 'required|in:1,2,3,4,5,6',
            'penunjang_lab' => 'required|string|max:2000',
            'riwayat_penyakit' => 'required|string|max:2000',
            'saraf_perifer' => 'required|string|max:2000',
            'vaskular' => 'required|string|max:2000',
            'saturasi' => 'required|integer|min:0',
            'sistolik' => 'required|integer|min:0',
            'diastolik' => 'required|integer|min:0',
            'nadi' => 'required|integer|min:0',
            'pernapasan' => 'required|integer|min:0',
            'suhu' => 'required|numeric|min:0',
            'kesadaran' => ['required', Rule::in(array_column(SelectOption::get('kesadaran'), 'value'))],
            'obat_cairan_intraop' => 'required|string|max:2000',
            'kejadian_tidak_terduga' => 'required|string|max:2000',
            'penanganan_intraop' => 'nullable|string|max:2000',
            'kondisi_akhir' => 'required|string|max:2000',
            'instruksi_pasca' => 'required|string|max:2000',
            'ruang_tujuan' => 'required|exists:bagian,bagian_id',
        ];

        $validator = Validator::make($request->all(), $rules);
        $validator->after(function ($validator) {
            $mulai = request('jam_mulai');
            $selesai = request('jam_selesai');
            if ($mulai && $selesai && strtotime($selesai) < strtotime($mulai)) {
                $validator->errors()->add('jam_selesai', 'Jam selesai tidak boleh sebelum jam mulai anestesia.');
            }

            if (in_array(request('jenis_anestesi'), ['Epidural', 'Spinal'], true)
                && trim((string) request('caudel', '')) === '') {
                $validator->errors()->add('caudel', 'Ukuran caudel wajib diisi bila jenis anestesi Epidural atau Spinal.');
            }

            if (trim((string) request('kejadian_tidak_terduga', '')) !== ''
                && trim((string) request('penanganan_intraop', '')) === '') {
                $validator->errors()->add('penanganan_intraop', 'Penanganan intraoperatif wajib diisi bila ada kejadian tidak terduga.');
            }
        });
        $validator->validate();

        $data = array_merge(
            array_fill_keys(EmrHelper::objekVariabels($form_id), null),
            $validator->validated()
        );

        // Lama anestesia TURUNAN — dihitung server dari selisih jam (KONSEP §17),
        // input user dibuang (pola form 92 `lama_operasi`).
        $mulai = $data['jam_mulai'] ?? null;
        $selesai = $data['jam_selesai'] ?? null;
        $data['lama_anestesia'] = ($mulai && $selesai && strtotime($selesai) >= strtotime($mulai))
            ? ((int) ((strtotime($selesai) - strtotime($mulai)) / 60)).' menit'
            : null;

        // Caudel hanya relevan utk anestesi neuraxial (Epidural/Spinal).
        if (! in_array($data['jenis_anestesi'] ?? null, ['Epidural', 'Spinal'], true)) {
            $data['caudel'] = null;
        }

        return $data;
    }

    private function kosong(): array
    {
        return [
            'tanggal_catatan' => '', 'jam_mulai' => '', 'jam_selesai' => '', 'lama_anestesia' => '',
            'dokter_anestesi' => '', 'perawat_anestesi' => '', 'jenis_anestesi' => '',
            'airway' => '', 'airway_2' => '', 'ventilasi' => '', 'sirkuit' => '', 'caudel' => '',
            'posisi' => '', 'monitor' => '', 'asa' => '',
            'penunjang_lab' => '', 'riwayat_penyakit' => '', 'saraf_perifer' => '', 'vaskular' => '',
            'saturasi' => '', 'sistolik' => '', 'diastolik' => '', 'nadi' => '', 'pernapasan' => '',
            'suhu' => '', 'kesadaran' => '',
            'obat_cairan_intraop' => '', 'kejadian_tidak_terduga' => '', 'penanganan_intraop' => '',
            'kondisi_akhir' => '', 'instruksi_pasca' => '', 'ruang_tujuan' => '',
        ];
    }
}
