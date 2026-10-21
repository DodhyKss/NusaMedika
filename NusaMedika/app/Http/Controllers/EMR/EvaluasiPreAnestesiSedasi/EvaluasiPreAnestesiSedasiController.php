<?php

namespace App\Http\Controllers\EMR\EvaluasiPreAnestesiSedasi;

use App\Helpers\AksesEhr;
use App\Helpers\EmrHelper;
use App\Helpers\SelectOption;
use App\Http\Controllers\Controller;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * Form Evaluasi Pre-Anestesi & Sedasi (form 86, slug `evaluasi_pre_anestesi_sedasi`).
 *
 * Evaluasi pra-anestesi sebelum operasi/sedasi (KONSEP §4): anamnesis per
 * sistem organ (19 objek grup), alergi, skor ASA 1–6, rencana anestesi &
 * sedasi, persetujuan, dan catatan. Dua aturan keras (safety gate):
 *
 *  - `persetujuan_anestesi` belum "Sudah" ⇒ simpan DITOLAK.
 *  - Perempuan usia < 60 tahun ⇒ `hamil_bulan` wajib.
 */
class EvaluasiPreAnestesiSedasiController extends Controller
{
    private const SLUG = 'evaluasi_pre_anestesi_sedasi';

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
            $emr_data = EmrHelper::wrapData($this->kosong($registrasi_detail));
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

        return view('moduls.EMR.EvaluasiPreAnestesiSedasi.index', compact(
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

            return redirect()->back()->with('success', 'Evaluasi Pre-Anestesi & Sedasi berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan Evaluasi Pre-Anestesi: '.$e->getMessage())->withInput();
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

            return redirect()->back()->with('success', 'Evaluasi Pre-Anestesi & Sedasi berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui Evaluasi Pre-Anestesi: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Evaluasi Pre-Anestesi & Sedasi berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus Evaluasi Pre-Anestesi: '.$e->getMessage());
        }
    }

    private function validated(Request $request, int $form_id): array
    {
        $rules = [
            'tanggal_evaluasi' => 'required|date',
            'jam_evaluasi' => 'required|date_format:H:i',
            'dokter_anestesi_id' => 'required|integer|exists:pegawai,pegawai_id',
            'nama_pasien' => 'required|string|max:200',
            'tanggal_lahir' => 'required|date',
            'jenis_kelamin' => 'required|in:L,P',
            'pekerjaan' => ['required', Rule::in(array_column(SelectOption::get('pekerjaan'), 'value'))],
            'riwayat_penyakit_keluarga' => 'required|string|max:2000',
            'riwayat_pengobatan' => 'required|string|max:2000',
            'jalan_nafas' => 'required|in:Ya,Tidak',
            'gangguan_pernafasan' => 'required_if:jalan_nafas,Ya|string|max:2000',
            'snoring' => 'required|in:Ya,Tidak',
            'pernapasan' => 'required|in:Ya,Tidak',
            'penyakit_paru' => 'nullable|string|max:2000',
            'suhu' => 'required|numeric',
            'td' => 'required|string|max:20',
            'nadi' => 'required|numeric',
            'jantung' => 'required|in:Ya,Tidak',
            'op_jantung_koroner' => 'required|in:Ya,Tidak',
            'serangan_jantung' => 'required|in:Ya,Tidak',
            'hipertensi' => 'required|in:Ya,Tidak',
            'riwayat_transfusi' => 'nullable|string|max:2000',
            'saraf_perifer' => 'required|in:Ya,Tidak',
            'pingsan' => 'required|in:Ya,Tidak',
            'kejang' => 'required|in:Ya,Tidak',
            'stroke' => 'required|in:Ya,Tidak',
            'saraf' => 'nullable|string|max:2000',
            'hamil_bulan' => 'nullable|integer|min:1|max:9',
            'diabetes' => 'required|in:Ya,Tidak',
            'gangguan_tiroid' => 'required|in:Ya,Tidak',
            'obesitas' => 'required|in:Ya,Tidak',
            'gula_darah' => 'required|numeric',
            'ginjal_gagal' => 'required|in:Ya,Tidak',
            'susah_kencing' => 'required|in:Ya,Tidak',
            'kreatinin' => 'required|numeric',
            'ureum' => 'required|numeric',
            'abdomen' => 'required|in:Ya,Tidak',
            'maag' => 'required|in:Ya,Tidak',
            'muntah' => 'required|in:Ya,Tidak',
            'faring' => 'required|in:Ya,Tidak',
            'gigi_geligi' => 'required|in:Ya,Tidak',
            'mobilitas' => 'required|in:Ya,Tidak',
            'anemia' => 'required|in:Ya,Tidak',
            'leukosit' => 'required|numeric',
            'trombosit' => 'required|numeric',
            'kadar_gula_darah' => 'required|numeric',
            'prothrombin_time' => 'required|numeric',
            'alergi_latraks' => 'required|in:Ada,Tidak',
            'alergi_makanan' => 'required|in:Ada,Tidak',
            'alergi_plaster' => 'required|in:Ada,Tidak',
            'alergi_obat_timbul' => 'required_if:alergi_latraks,Ada|string|max:2000',
            'kajar_aspirasi' => 'required|in:Ya,Tidak',
            'ceklis_equip_emergi' => 'required|in:Ya,Tidak',
            'perlu_alat_bantu' => 'required_if:ceklis_equip_emergi,Ya|string|max:2000',
            'asa' => 'required|in:1,2,3,4,5,6',
            'nyeri' => 'required|in:Ya,Tidak',
            'tipe_anestesia' => 'required|in:Umum,Regional,Lokal,Sedasi',
            'anes_umum' => 'required|in:Ya,Tidak',
            'anes_regional' => 'required|in:Ya,Tidak',
            'sedasi' => 'required|in:Ya,Tidak',
            'persetujuan_anestesi' => 'required|in:Sudah,Belum',
            'catatan_evaluasi' => 'required|string|max:2000',
        ];

        $validator = Validator::make($request->all(), $rules);

        $validator->after(function ($validator) use ($request) {
            // SAFETY GATE (KONSEP §4): persetujuan anestesi belum "Sudah" —
            // store/update DITOLAK, bukan sekadar pesan peringatan.
            if ($request->filled('persetujuan_anestesi') && $request->input('persetujuan_anestesi') !== 'Sudah') {
                $validator->errors()->add(
                    'persetujuan_anestesi',
                    'Persetujuan anestesi belum diberikan — evaluasi pre-anestesi tidak boleh disimpan.'
                );
            }

            // Perempuan usia < 60 tahun wajib mengisi hamil_bulan
            // (dicek null / 0 / kosong). Usia dihitung dari tanggal_lahir
            // yang dikirim (prefilled dari data pasien, readonly di form).
            if ($request->input('jenis_kelamin') === 'P' && $request->filled('tanggal_lahir')) {
                $usia = (int) Carbon::parse($request->input('tanggal_lahir'))->age;
                $hamil = $request->input('hamil_bulan');
                if ($usia < 60 && ($hamil === null || $hamil === '' || (int) $hamil === 0)) {
                    $validator->errors()->add('hamil_bulan', 'Umur kehamilan wajib diisi untuk perempuan usia kurang dari 60 tahun.');
                }
            }
        });
        $validator->validate();

        // Semua variabel mapping selalu ikut (nullable yang tidak dikirim → null)
        // supaya setiap baris emr_detail tercipta dan tampil lagi saat edit.
        $data = array_merge(
            array_fill_keys(EmrHelper::objekVariabels($form_id), null),
            $validator->validated()
        );

        // Field kehamilan tidak relevan selain perempuan → dikosongkan,
        // bukan di-unset, supaya baris emr_detail tetap ada (KONSEP §4).
        if (($data['jenis_kelamin'] ?? null) !== 'P') {
            $data['hamil_bulan'] = null;
        }

        return $data;
    }

    /**
     * Default form baru: identitas anamnesis (nama, tanggal lahir, jenis
     * kelamin) di-prefill dari data pasien kunjungan ini.
     */
    private function kosong($registrasi_detail): array
    {
        $pasien = $registrasi_detail->registrasi->pasien ?? null;

        return [
            'tanggal_evaluasi' => '', 'jam_evaluasi' => '', 'dokter_anestesi_id' => '',
            'nama_pasien' => $pasien->nama_pasien ?? '',
            'tanggal_lahir' => $pasien->tgl_lahir ?? '',
            'jenis_kelamin' => $pasien->jenis_kelamin ?? '',
            'pekerjaan' => '', 'riwayat_penyakit_keluarga' => '', 'riwayat_pengobatan' => '',
            'jalan_nafas' => '', 'gangguan_pernafasan' => '', 'snoring' => '',
            'pernapasan' => '', 'penyakit_paru' => '', 'suhu' => '', 'td' => '', 'nadi' => '',
            'jantung' => '', 'op_jantung_koroner' => '', 'serangan_jantung' => '', 'hipertensi' => '',
            'riwayat_transfusi' => '',
            'saraf_perifer' => '', 'pingsan' => '', 'kejang' => '', 'stroke' => '', 'saraf' => '',
            'hamil_bulan' => '', 'diabetes' => '', 'gangguan_tiroid' => '', 'obesitas' => '', 'gula_darah' => '',
            'ginjal_gagal' => '', 'susah_kencing' => '', 'kreatinin' => '', 'ureum' => '',
            'abdomen' => '', 'maag' => '', 'muntah' => '', 'faring' => '', 'gigi_geligi' => '', 'mobilitas' => '',
            'anemia' => '', 'leukosit' => '', 'trombosit' => '', 'kadar_gula_darah' => '', 'prothrombin_time' => '',
            'alergi_latraks' => '', 'alergi_makanan' => '', 'alergi_plaster' => '', 'alergi_obat_timbul' => '',
            'kajar_aspirasi' => '', 'ceklis_equip_emergi' => '', 'perlu_alat_bantu' => '',
            'asa' => '', 'nyeri' => '',
            'tipe_anestesia' => '', 'anes_umum' => '', 'anes_regional' => '', 'sedasi' => '',
            'persetujuan_anestesi' => '', 'catatan_evaluasi' => '',
        ];
    }
}
