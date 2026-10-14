<?php

namespace App\Http\Controllers\EMR\SurveilansLukaInsisi;

use App\Helpers\AksesEhr;
use App\Helpers\EmrHelper;
use App\Http\Controllers\Controller;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * Form Surveilans Luka Insisi (form 98, slug `surveilans_luka_insisi`).
 *
 * Surveilans infeksi luka insisi (SSI) WHO/INCDC — Pre OP & Post OP
 * (KONSEP §16). Safety gate: tanggal operasi >= tanggal MRS, keterangan
 * trauma bila trauma aktif, minimal satu tanda infeksi bila klasifikasi
 * luka >= II.
 */
class SurveilansLukaInsisiController extends Controller
{
    private const SLUG = 'surveilans_luka_insisi';

    public function index($registrasi_detail_id, $emr_id = null)
    {
        $registrasi_detail = RegistrasiDetail::with('registrasi.pasien')->findOrFail($registrasi_detail_id);

        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'read'), 403);

        $aksesCrud = AksesEhr::flags((int) $form_id);
        $riwayat = EmrHelper::emrList((int) $form_id, (int) $registrasi_detail->registrasi_id);
        $historyGrouped = EmrHelper::historyKunjunganGrouped($registrasi_detail);
        $kamars = $this->kamarOperasi();

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

        return view('moduls.EMR.SurveilansLukaInsisi.index', compact(
            'registrasi_detail', 'riwayat', 'historyGrouped', 'emr_data',
            'formAction', 'isEdit', 'deleteAction', 'isView', 'emr_id', 'aksesCrud', 'kamars'
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

            return redirect()->back()->with('success', 'Surveilans Luka Insisi berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan Surveilans Luka Insisi: '.$e->getMessage())->withInput();
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

            return redirect()->back()->with('success', 'Surveilans Luka Insisi berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui Surveilans Luka Insisi: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Surveilans Luka Insisi berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus Surveilans Luka Insisi: '.$e->getMessage());
        }
    }

    private function validated(Request $request, int $form_id): array
    {
        $rules = [
            'tgl_mrs' => 'required|date',
            'tgl_operasi' => 'required|date',
            'jam_operasi' => 'required|date_format:H:i',
            'lama_operasi' => 'required|string|max:2000',
            'jenis_operasi' => 'required|in:Efektif,Darurat,Semi Darurat',
            'kamar_operasi' => 'required|exists:bed,nama_bed',
            'dokter_bedah' => 'required|in:Spesialis,Subspesialis,Dokter Umum',
            'trauma_pada' => 'required|in:Trauma Tertutup,Trauma Terbuka,Elektif',
            'keterangan_trauma' => 'nullable|string|max:2000',
            'klasifikasi_luka' => 'required|in:I,II,III,IV',
            'profilaksis_1' => 'required|in:0,1',
            'profilaksis_2' => 'required|in:0,1',
            'profilaksis_3' => 'required|in:0,1',
            'profilaksis_4' => 'required|in:0,1',
            'profilaksis_5' => 'required|in:0,1',
            'tanda_infeksi_1' => 'required|in:0,1',
            'tanda_infeksi_2' => 'required|in:0,1',
            'tanda_infeksi_3' => 'required|in:0,1',
            'tanda_infeksi_4' => 'required|in:0,1',
            'tanda_infeksi_5' => 'required|in:0,1',
            'kondisi_luka_post' => 'required|in:Kering,Basah,Eksudat Serosa,Eksudat Purulen,Nekrosis',
            'drainase_luka' => 'required|in:Ada,Tidak Ada',
            'keterangan_surveilans' => 'nullable|string|max:2000',
        ];

        $validator = Validator::make($request->all(), $rules);
        $validator->after(function ($validator) {
            $tglMrs = request('tgl_mrs');
            $tglOperasi = request('tgl_operasi');
            if ($tglMrs && $tglOperasi && strtotime($tglOperasi) < strtotime($tglMrs)) {
                $validator->errors()->add('tgl_operasi', 'Tanggal operasi tidak boleh sebelum tanggal masuk rumah sakit.');
            }

            if (in_array(request('trauma_pada'), ['Trauma Tertutup', 'Trauma Terbuka'], true)
                && trim((string) request('keterangan_trauma', '')) === '') {
                $validator->errors()->add('keterangan_trauma', 'Keterangan trauma wajib diisi bila pasien datang dengan trauma.');
            }

            $derajat = ['I' => 1, 'II' => 2, 'III' => 3, 'IV' => 4];
            if (($derajat[(string) request('klasifikasi_luka')] ?? 1) >= 2) {
                $terpilih = 0;
                for ($i = 1; $i <= 5; $i++) {
                    if (request('tanda_infeksi_'.$i) === '1') {
                        $terpilih++;
                    }
                }
                if ($terpilih < 1) {
                    $validator->errors()->add('tanda_infeksi_1', 'Minimal satu tanda infeksi wajib dicentang bila klasifikasi luka II atau lebih.');
                }
            }
        });
        $validator->validate();

        $data = array_merge(
            array_fill_keys(EmrHelper::objekVariabels($form_id), null),
            $validator->validated()
        );

        // Keterangan trauma hanya relevan bila pasien datang dengan trauma.
        if (in_array($data['trauma_pada'] ?? null, ['Trauma Tertutup', 'Trauma Terbuka'], true)) {
            $data['keterangan_trauma'] = trim((string) ($data['keterangan_trauma'] ?? '')) ?: null;
        } else {
            $data['keterangan_trauma'] = null;
        }

        return $data;
    }

    /**
     * Daftar kamar operasi = nama bed pada unit bedah (legacy pre_op_insisi
     * memakai bed dari bagian bedah/endoscopy/cathlab — dua unit terakhir
     * belum ada di DB lokal, jadi hanya INSTALASI BEDAH SENTRAL).
     * Nilai yang disimpan = nama_bed (bukan id), pola legacy.
     */
    private function kamarOperasi(): array
    {
        $bagianIbs = DB::table('bagian')
            ->where('nama_bagian', 'INSTALASI BEDAH SENTRAL')
            ->where(function ($q) {
                $q->whereNull('status_batal')->orWhere('status_batal', 0);
            })
            ->value('bagian_id');

        if (! $bagianIbs) {
            return [];
        }

        return DB::table('bed')
            ->where('bagian_id', $bagianIbs)
            ->where(function ($q) {
                $q->whereNull('status_batal')->orWhere('status_batal', 0);
            })
            ->orderBy('nama_bed')
            ->pluck('nama_bed')
            ->all();
    }

    private function kosong(): array
    {
        return [
            'tgl_mrs' => '', 'tgl_operasi' => '', 'jam_operasi' => '', 'lama_operasi' => '',
            'jenis_operasi' => '', 'kamar_operasi' => '', 'dokter_bedah' => '', 'trauma_pada' => '',
            'keterangan_trauma' => '',
            'klasifikasi_luka' => '',
            'profilaksis_1' => '0', 'profilaksis_2' => '0', 'profilaksis_3' => '0',
            'profilaksis_4' => '0', 'profilaksis_5' => '0',
            'tanda_infeksi_1' => '0', 'tanda_infeksi_2' => '0', 'tanda_infeksi_3' => '0',
            'tanda_infeksi_4' => '0', 'tanda_infeksi_5' => '0',
            'kondisi_luka_post' => '', 'drainase_luka' => '', 'keterangan_surveilans' => '',
        ];
    }
}
