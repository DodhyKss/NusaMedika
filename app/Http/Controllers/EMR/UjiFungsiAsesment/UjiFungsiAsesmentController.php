<?php

namespace App\Http\Controllers\EMR\UjiFungsiAsesment;

use App\Helpers\AksesEhr;
use App\Helpers\EmrHelper;
use App\Helpers\SelectOption;
use App\Http\Controllers\Controller;
use App\Models\ICD;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * Form Uji Fungsi & Asesment (form 124, slug `uji_fungsi_asesment`).
 *
 * Lembar asesmen rehabilitasi medik / fisioterapi dengan bentuk SOAP dasar.
 * SELURUH field reuse objek yang sudah ada - dokumen ini menambah nol objek baru.
 *
 * Saat membuat baris baru, kolom S dan O di-prefill dari Pengkajian Awal
 * Keperawatan (form 3) pada kunjungan yang sama. Prefill HANYA teks - petugas
 * tetap bebas mengubahnya sebelum disimpan.
 *
 * CATATAN (risiko R5 pada docs/KONSEP_ASUHAN_KEPERAWATAN_LANJUTAN.md):
 * `evaluasi`, `edukasi_anjuran`, dan `frekuensi_kunjungan` berbagi objek 77.
 * Laporan form ini WAJIB memakai `EmrHelper::emrDetailByVariabel()`, bukan
 * `emrDetailByObjek()` - yang terakhir melakukan `pluck('value','objek_id')`
 * sehingga satu dari tiga field tersebut akan tertimpa.
 */
class UjiFungsiAsesmentController extends Controller
{
    private const SLUG = 'uji_fungsi_asesment';

    /** Slug form 3 - sumber prefill kolom Subjective, Objective, dan diagnosa. */
    private const SLUG_PENGKAJIAN_AWAL = 'pengkajian_awal_keperawatan';

    /** Parameter tanda vital yang dipakai untuk menyusun prefill kolom Objective. */
    private const VITAL_PRE = [
        'td' => 'Tekanan Darah',
        'nadi' => 'Nadi',
        'suhu' => 'Suhu',
        'pernapasan' => 'Pernapasan',
        'saturasi' => 'Saturasi',
        'berat_badan' => 'Berat Badan',
        'tinggi_badan' => 'Tinggi Badan',
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

        // Tidak boleh membuat data baru -> tampilkan riwayat terakhir.
        if (empty($emr_id) && ! ($aksesCrud['create'] ?? false) && $riwayat->isNotEmpty()) {
            return redirect()->route('emr.dynamic.index', [
                'form_name' => self::SLUG,
                'registrasi_detail_id' => $registrasi_detail_id,
                'emr_id' => $riwayat->first()->emr_id,
                'action' => 'view',
            ]);
        }

        $icdList = ICD::aktif()->orderBy('kode_diagnosa')->get(['icd_id', 'kode_diagnosa', 'nama_diagnosa']);

        if (empty($emr_id)) {
            $prefill = $this->prefillPengkajianAwal((int) $registrasi_detail_id);

            $kosong = $this->kosong();
            $kosong['subjective'] = $prefill['subjective'];
            $kosong['objective'] = $prefill['objective'];
            $kosong['diagnosis_medis'] = $prefill['diagnosis_medis'];

            $emr_data = EmrHelper::wrapData($kosong);
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

        return view('moduls.EMR.UjiFungsiAsesment.index', compact(
            'registrasi_detail', 'riwayat', 'historyGrouped', 'emr_data',
            'formAction', 'isEdit', 'deleteAction', 'isView', 'emr_id', 'aksesCrud',
            'icdList'
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

            return redirect()->back()->with('success', 'Uji Fungsi Asesment berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan Uji Fungsi Asesment: '.$e->getMessage())->withInput();
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

            return redirect()->back()->with('success', 'Uji Fungsi Asesment berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui Uji Fungsi Asesment: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Uji Fungsi Asesment berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus Uji Fungsi Asesment: '.$e->getMessage());
        }
    }

    /**
     * Nilai kosong untuk form baru.
     */
    private function kosong(): array
    {
        return [
            'tanggal_pelayanan' => '', 'waktu_pelayanan' => '',
            'subjective' => '', 'objective' => '', 'diagnosis_fungsi' => '', 'goal' => '',
            'diagnosis_medis' => '', 'instruksi' => '',
            'evaluasi' => '', 'edukasi_anjuran' => '', 'frekuensi_kunjungan' => '',
        ];
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'tanggal_pelayanan' => 'required|date',
            'waktu_pelayanan' => 'required|date_format:H:i',
            'subjective' => 'required|string|max:2000',
            'objective' => 'required|string|max:2000',
            'diagnosis_fungsi' => 'required|string|max:1000',
            'goal' => 'required|string|max:1000',
            'diagnosis_medis' => ['required', 'integer', 'exists:icd,icd_id'],
            'instruksi' => 'required|string|max:2000',
            'evaluasi' => 'required|string|max:2000',
            'edukasi_anjuran' => 'required|string|max:1000',
            'frekuensi_kunjungan' => [
                'required',
                Rule::in(array_column(SelectOption::get('frekuensi_kunjungan_rehab'), 'value')),
            ],
        ]);
    }

    /**
     * Hanya field terpetakan yang disimpan. Tidak ada field turunan pada form ini.
     */
    private function filteredData(array $data, int $formId): array
    {
        return array_intersect_key($data, array_flip(EmrHelper::objekVariabels($formId)));
    }

    /**
     * Prefill kolom Subjective, Objective, dan diagnosa medis dari Pengkajian
     * Awal Keperawatan (form 3) pada kunjungan yang sama.
     *
     * @return array{subjective: string, objective: string, diagnosis_medis: string}
     */
    private function prefillPengkajianAwal(int $registrasiDetailId): array
    {
        $kosong = ['subjective' => '', 'objective' => '', 'diagnosis_medis' => ''];

        $formId = EmrHelper::formIdBySlug(self::SLUG_PENGKAJIAN_AWAL);
        if (! $formId) {
            return $kosong;
        }

        if (! EmrHelper::latestEmr((int) $formId, $registrasiDetailId)) {
            return $kosong;
        }

        $src = EmrHelper::latestValuesByVariabel((int) $formId, $registrasiDetailId, array_merge(
            ['keluhan', 'riwayat_penyakit_sebelumnya', 'riwayat_penyakit_sekarang', 'diagnosa_medis'],
            array_keys(self::VITAL_PRE)
        ));

        if (! $src) {
            return $kosong;
        }

        $s = [];
        foreach (['keluhan', 'riwayat_penyakit_sebelumnya', 'riwayat_penyakit_sekarang'] as $variabel) {
            $teks = trim((string) ($src[$variabel] ?? ''));
            if ($teks !== '') {
                $s[] = $teks;
            }
        }

        $o = [];
        foreach (self::VITAL_PRE as $variabel => $label) {
            $nilai = trim((string) ($src[$variabel] ?? ''));
            if ($nilai !== '') {
                $o[] = $label.': '.$nilai;
            }
        }

        // Diagnosa medis pada form 3 berupa teks bebas; dicocokkan ke master ICD.
        $nama = trim((string) ($src['diagnosa_medis'] ?? ''));
        $icdId = '';
        if ($nama !== '') {
            $icd = ICD::aktif()->where('nama_diagnosa', $nama)->first();
            $icdId = $icd ? (string) $icd->icd_id : '';
        }

        return [
            'subjective' => implode("\n", $s),
            'objective' => implode("\n", $o),
            'diagnosis_medis' => $icdId,
        ];
    }
}
