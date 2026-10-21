<?php

namespace App\Http\Controllers\EMR\LaporanOperasi;

use App\Helpers\AksesEhr;
use App\Helpers\EmrHelper;
use App\Http\Controllers\Controller;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

/**
 * Form Laporan Operasi (form 92, slug `laporan_operasi`).
 *
 * Dokumen resmi hasil operasi (KONSEP §10): jam selesai >= jam mulai,
 * `lama_operasi` TURUNAN dihitung server (selisih menit), guard
 * transfusi/implan/specimen bila berstatus "Ya".
 */
class LaporanOperasiController extends Controller
{
    private const SLUG = 'laporan_operasi';

    private const KOMPLIKASI = [
        'komplikasi_operasi_1', 'komplikasi_operasi_2', 'komplikasi_operasi_3',
        'komplikasi_operasi_4', 'komplikasi_operasi_5', 'komplikasi_operasi_6',
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

        return view('moduls.EMR.LaporanOperasi.index', compact(
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

            return redirect()->back()->with('success', 'Laporan Operasi berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan Laporan Operasi: '.$e->getMessage())->withInput();
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

            return redirect()->back()->with('success', 'Laporan Operasi berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui Laporan Operasi: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Laporan Operasi berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus Laporan Operasi: '.$e->getMessage());
        }
    }

    private function validated(Request $request, int $form_id): array
    {
        $rules = [
            'tanggal_laporan' => 'required|date',
            'jam_mulai_operasi' => 'required|date_format:H:i',
            'jam_selesai_operasi' => 'required|date_format:H:i',
            'lama_operasi' => 'nullable|string|max:50',
            'nama_tindakan_operasi' => 'required|string|max:2000',
            'jenis_operasi' => 'required|in:Efektif,Darurat,Semi Darurat',
            'jenis_anas_umum' => 'required|in:Ya,Tidak',
            'jenis_anas_lokal' => 'required|in:Ya,Tidak',
            'dokter_bedah_operator' => 'required|integer|exists:pegawai,pegawai_id',
            'dokter_anestesi' => 'required|integer|exists:pegawai,pegawai_id',
            'diagnosa_pra_operasi' => 'required|string|max:2000',
            'diagnosa_pasca_operasi' => 'required|string|max:2000',
            'temuan_operasi' => 'required|string|max:2000',
            'prosedur_operasi' => 'required|string|max:2000',
            'komplikasi_operasi_1' => 'nullable|in:0,1',
            'komplikasi_operasi_2' => 'nullable|in:0,1',
            'komplikasi_operasi_3' => 'nullable|in:0,1',
            'komplikasi_operasi_4' => 'nullable|in:0,1',
            'komplikasi_operasi_5' => 'nullable|in:0,1',
            'komplikasi_operasi_6' => 'nullable|in:0,1',
            'transfusi_darah' => 'required|in:Ya,Tidak',
            'jumlah_transfusi' => 'required_if:transfusi_darah,Ya|integer|min:1',
            'implan_pasang' => 'required|in:Ya,Tidak',
            'jenis_implan' => 'required_if:implan_pasang,Ya|string|max:2000',
            'instrumen_kasa' => 'required|string|max:2000',
            'specimen_diterima' => 'required|in:Ya,Tidak',
            'nama_specimen' => 'required_if:specimen_diterima,Ya|string|max:2000',
            'kehilangan_darah' => 'required|integer|between:0,99999',
            'cairan_masuk' => 'required|integer|between:0,99999',
            'ruangan_pasca_operasi' => 'required|integer|exists:bagian,bagian_id',
            'instruksi_pasca_operasi' => 'required|string|max:2000',
        ];

        $validator = Validator::make($request->all(), $rules);
        $validator->after(function ($validator) {
            $mulai = request('jam_mulai_operasi');
            $selesai = request('jam_selesai_operasi');
            if ($mulai && $selesai && strtotime($selesai) < strtotime($mulai)) {
                $validator->errors()->add('jam_selesai_operasi', 'Jam selesai operasi tidak boleh lebih awal dari jam mulai.');
            }
        });
        $validator->validate();

        $data = array_merge(
            array_fill_keys(EmrHelper::objekVariabels($form_id), null),
            $validator->validated()
        );

        // Lama operasi TURUNAN — dihitung server dari selisih jam (KONSEP §10).
        $mulai = $data['jam_mulai_operasi'] ?? null;
        $selesai = $data['jam_selesai_operasi'] ?? null;
        $data['lama_operasi'] = ($mulai && $selesai && strtotime($selesai) >= strtotime($mulai))
            ? ((int) ((strtotime($selesai) - strtotime($mulai)) / 60)).' menit'
            : null;

        // Normalisasi checkbox komplikasi: "1" bila terkirim & terpilih, selain itu "0".
        foreach (self::KOMPLIKASI as $k) {
            $data[$k] = ($data[$k] ?? null) === '1' ? '1' : '0';
        }

        // Field kondisional dikosongkan (null), bukan di-unset.
        if (($data['transfusi_darah'] ?? null) !== 'Ya') {
            $data['jumlah_transfusi'] = null;
        }
        if (($data['implan_pasang'] ?? null) !== 'Ya') {
            $data['jenis_implan'] = null;
        }
        if (($data['specimen_diterima'] ?? null) !== 'Ya') {
            $data['nama_specimen'] = null;
        }

        return $data;
    }

    private function kosong(): array
    {
        $d = [
            'tanggal_laporan' => '', 'jam_mulai_operasi' => '', 'jam_selesai_operasi' => '',
            'lama_operasi' => '', 'nama_tindakan_operasi' => '', 'jenis_operasi' => '',
            'jenis_anas_umum' => 'Tidak', 'jenis_anas_lokal' => 'Tidak',
            'dokter_bedah_operator' => '', 'dokter_anestesi' => '',
            'diagnosa_pra_operasi' => '', 'diagnosa_pasca_operasi' => '',
            'temuan_operasi' => '', 'prosedur_operasi' => '',
            'transfusi_darah' => 'Tidak', 'jumlah_transfusi' => '',
            'implan_pasang' => 'Tidak', 'jenis_implan' => '',
            'instrumen_kasa' => '', 'specimen_diterima' => 'Tidak', 'nama_specimen' => '',
            'kehilangan_darah' => '', 'cairan_masuk' => '', 'ruangan_pasca_operasi' => '',
            'instruksi_pasca_operasi' => '',
        ];
        foreach (self::KOMPLIKASI as $k) {
            $d[$k] = '0';
        }

        return $d;
    }
}
