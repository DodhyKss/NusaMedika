<?php

namespace App\Http\Controllers\EMR\PengkajianOperasi;

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
 * Form Pengkajian Pre/Intra/Post Operasi (form 91, slug `pengkajian_operasi`).
 *
 * Pengkajian keperawatan perioperatif (KONSEP §9): satu form dengan
 * `fase` radio Pre/Intra/Post. Field di luar fase terpilih di-set NULL
 * (bukan di-unset — baris emr_detail tetap ada, KONSEP: "dibuang di
 * filteredData").
 */
class PengkajianOperasiController extends Controller
{
    private const SLUG = 'pengkajian_operasi';

    /** Variabel yang hanya berlaku untuk tiap fase. */
    private const FASE_FIELDS = [
        'Pre' => [
            'keluhan_utama', 'riwayat_penyakit', 'riwayat_operasi', 'riwayat_alergi',
            'jenis_operasi', 'status_emosi', 'skala_cemas', 'data_penunjang', 'persiapan_darah',
        ],
        'Intra' => [
            'pemeriksaan_fisik_intra', 'posisi_operasi_intra', 'keadaan_umum_intra',
            'asa_intra', 'ruangan_pulih',
        ],
        'Post' => [
            'nyeri_post', 'skor_nyeri_post', 'mobilisasi_post', 'luka_operasi',
        ],
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

        return view('moduls.EMR.PengkajianOperasi.index', compact(
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

            return redirect()->back()->with('success', 'Pengkajian Pre/Intra/Post Operasi berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan Pengkajian Operasi: '.$e->getMessage())->withInput();
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

            return redirect()->back()->with('success', 'Pengkajian Pre/Intra/Post Operasi berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui Pengkajian Operasi: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Pengkajian Pre/Intra/Post Operasi berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus Pengkajian Operasi: '.$e->getMessage());
        }
    }

    private function validated(Request $request, int $form_id): array
    {
        $rules = [
            'tanggal_pengkajian' => 'required|date',
            'jam_pengkajian' => 'required|date_format:H:i',
            'fase' => 'required|in:Pre,Intra,Post',
            // ---- Fase Pre ----
            'keluhan_utama' => 'required_if:fase,Pre|string|max:2000',
            'riwayat_penyakit' => 'required_if:fase,Pre|string|max:2000',
            'riwayat_operasi' => 'required_if:fase,Pre|string|max:2000',
            'riwayat_alergi' => 'required_if:fase,Pre|string|max:2000',
            'jenis_operasi' => ['required_if:fase,Pre', Rule::in(array_column(SelectOption::get('jenis_operasi'), 'value'))],
            'status_emosi' => ['required_if:fase,Pre', Rule::in(array_column(SelectOption::get('status_emosi'), 'value'))],
            'skala_cemas' => 'required_if:fase,Pre|integer|between:0,4',
            'data_penunjang' => 'required_if:fase,Pre|string|max:2000',
            'persiapan_darah' => 'required_if:fase,Pre|in:Ya,Tidak',
            // ---- Fase Intra ----
            'pemeriksaan_fisik_intra' => 'required_if:fase,Intra|string|max:2000',
            'posisi_operasi_intra' => ['required_if:fase,Intra', Rule::in(array_column(SelectOption::get('posisi_operasi'), 'value'))],
            'keadaan_umum_intra' => ['required_if:fase,Intra', Rule::in(array_column(SelectOption::get('keadaan_umum_pulih'), 'value'))],
            'asa_intra' => ['required_if:fase,Intra', Rule::in(array_column(SelectOption::get('asa_kelas'), 'value'))],
            'ruangan_pulih' => 'required_if:fase,Intra|integer|exists:bagian,bagian_id',
            // ---- Fase Post ----
            'nyeri_post' => 'required_if:fase,Post|in:Ya,Tidak',
            'skor_nyeri_post' => 'required_if:fase,Post|integer|between:0,10',
            'mobilisasi_post' => ['required_if:fase,Post', Rule::in(array_column(SelectOption::get('mobilisasi_operasi'), 'value'))],
            'luka_operasi' => 'required_if:fase,Post|string|max:2000',
            // ---- Selalu wajib ----
            'rencana_keperawatan' => 'required|string|max:2000',
            'kebutuhan_nutrisi' => 'required|string|max:2000',
            'edukasi_pasien' => 'required|string|max:2000',
            'catatan_pengkajian' => 'required|string|max:2000',
        ];

        $validator = Validator::make($request->all(), $rules);
        $validator->validate();

        $data = array_merge(
            array_fill_keys(EmrHelper::objekVariabels($form_id), null),
            $validator->validated()
        );

        // Field di luar fase terpilih Dibuang (di-set null — baris tetap ada).
        $fase = $data['fase'] ?? null;
        foreach (self::FASE_FIELDS as $f => $keys) {
            if ($f === $fase) {
                continue;
            }
            foreach ($keys as $k) {
                $data[$k] = null;
            }
        }

        return $data;
    }

    private function kosong(): array
    {
        $d = [
            'tanggal_pengkajian' => '', 'jam_pengkajian' => '', 'fase' => 'Pre',
            'rencana_keperawatan' => '', 'kebutuhan_nutrisi' => '',
            'edukasi_pasien' => '', 'catatan_pengkajian' => '',
        ];
        foreach (self::FASE_FIELDS as $keys) {
            foreach ($keys as $k) {
                $d[$k] = '';
            }
        }

        return $d;
    }
}
