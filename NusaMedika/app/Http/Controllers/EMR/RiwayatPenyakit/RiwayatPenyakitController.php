<?php

namespace App\Http\Controllers\EMR\RiwayatPenyakit;

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
 * Form Riwayat Penyakit (form 55, slug `riwayat_penyakit`).
 *
 * Anamnesis riwayat penyakit: diagnosa medis, keluhan, riwayat penyakit
 * sebelumnya & sekarang, blok infeksius, blok imunologi, riwayat kemoterapi /
 * radioterapi / operasi, vaksin, alergi, dan riwayat perawatan berulang.
 *
 * Checkbox group disimpan sebagai JSON array pada SATU variabel, bukan satu baris
 * per centang. Seluruh blok infeksius & imunologi DIBUANG di `filteredData()`
 * bila flag-nya "tidak", supaya tidak ada `emr_detail` setengah terisi.
 */
class RiwayatPenyakitController extends Controller
{
    private const SLUG = 'riwayat_penyakit';

    /** Blok yang dibuang bila `infeksius_flag` bukan "ya". */
    private const BLOK_INFEKSIUS = [
        'menular_melalui',
        'infeksius_memerlukan_isolasi',
        'infeksius_hasil_penunjang',
    ];

    /** Blok yang dibuang bila `imunologi_flag` bukan "ya". */
    private const BLOK_IMUNOLOGI = [
        'imunologi_memerlukan_isolasi',
        'imunologi_pembatasan_pengunjung',
        'imunologi_hasil_penunjang',
    ];

    /** Checkbox group (disimpan sebagai JSON array). */
    private const GRUP = [
        'diagnosa_medis',
        'keluhan',
        'riwayat_penyakit_sebelumnya',
        'menular_melalui',
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

        return view('moduls.EMR.RiwayatPenyakit.index', compact(
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

            return redirect()->back()->with('success', 'Riwayat Penyakit berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan Riwayat Penyakit: '.$e->getMessage())->withInput();
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

            return redirect()->back()->with('success', 'Riwayat Penyakit berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui Riwayat Penyakit: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Riwayat Penyakit berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus Riwayat Penyakit: '.$e->getMessage());
        }
    }

    /**
     * Nilai kosong untuk form baru.
     */
    private function kosong(): array
    {
        return [
            'diagnosa_medis' => '', 'keluhan' => '',
            'riwayat_penyakit_sebelumnya' => '', 'riwayat_penyakit_sekarang' => '',
            'infeksius_flag' => '', 'menular_melalui' => '',
            'infeksius_memerlukan_isolasi' => '', 'infeksius_hasil_penunjang' => '',
            'imunologi_flag' => '', 'imunologi_memerlukan_isolasi' => '',
            'imunologi_pembatasan_pengunjung' => '', 'imunologi_hasil_penunjang' => '',
            'riwayat_kemoterapi' => '', 'riwayat_radioterapi' => '', 'riwayat_operasi' => '',
            'vaksin_covid' => '', 'alergi' => '', 'riwayat_perawatan_berulang' => '',
            'catatan_riwayat' => '',
        ];
    }

    private function validated(Request $request): array
    {
        $flag = array_column(SelectOption::get('flag_ya_tidak'), 'value');

        $opsional = [
            'menular_melalui' => null,
            'infeksius_memerlukan_isolasi' => null,
            'infeksius_hasil_penunjang' => null,
            'imunologi_memerlukan_isolasi' => null,
            'imunologi_pembatasan_pengunjung' => null,
            'imunologi_hasil_penunjang' => null,
            'vaksin_covid' => null,
            'catatan_riwayat' => null,
        ];

        return array_merge($opsional, $request->validate([
            'diagnosa_medis' => ['required', 'array', 'min:1'],
            'diagnosa_medis.*' => 'integer|exists:icd,icd_id',

            'keluhan' => ['required', 'array', 'min:1'],
            'keluhan.*' => 'string|max:255',

            'riwayat_penyakit_sebelumnya' => ['required', 'array', 'min:1'],
            'riwayat_penyakit_sebelumnya.*' => 'string|max:255',
            'riwayat_penyakit_sekarang' => 'required|string|max:2000',

            'infeksius_flag' => ['required', Rule::in($flag)],
            'menular_melalui' => ['nullable', 'required_if:infeksius_flag,ya', 'array', 'min:1'],
            'menular_melalui.*' => 'string|max:255',
            'infeksius_memerlukan_isolasi' => ['nullable', 'required_if:infeksius_flag,ya', Rule::in($flag)],
            'infeksius_hasil_penunjang' => ['nullable', 'required_if:infeksius_flag,ya', 'string', 'max:1000'],

            'imunologi_flag' => ['required', Rule::in($flag)],
            'imunologi_memerlukan_isolasi' => ['nullable', 'required_if:imunologi_flag,ya', Rule::in($flag)],
            'imunologi_pembatasan_pengunjung' => ['nullable', 'required_if:imunologi_flag,ya', Rule::in($flag)],
            'imunologi_hasil_penunjang' => ['nullable', 'required_if:imunologi_flag,ya', 'string', 'max:1000'],

            'riwayat_kemoterapi' => ['required', Rule::in($flag)],
            'riwayat_radioterapi' => ['required', Rule::in($flag)],
            'riwayat_operasi' => ['required', Rule::in($flag)],
            'vaksin_covid' => 'nullable|date',

            'alergi' => 'required|string|max:1000',
            'riwayat_perawatan_berulang' => ['required', Rule::in($flag)],
            'catatan_riwayat' => 'nullable|string|max:1000',
        ]));
    }

    /**
     * Hanya field terpetakan yang disimpan. Grup kosong menjadi null (bukan
     * string "[]"), dan seluruh blok yang tidak berlaku dibuang.
     */
    private function filteredData(array $data, int $formId): array
    {
        $data = array_intersect_key($data, array_flip(EmrHelper::objekVariabels($formId)));

        foreach (self::GRUP as $grup) {
            $nilai = $data[$grup] ?? null;
            $data[$grup] = is_array($nilai) && $nilai !== [] ? $nilai : null;
        }

        if (($data['infeksius_flag'] ?? '') !== 'ya') {
            foreach (self::BLOK_INFEKSIUS as $variabel) {
                $data[$variabel] = null;
            }
        }

        if (($data['imunologi_flag'] ?? '') !== 'ya') {
            foreach (self::BLOK_IMUNOLOGI as $variabel) {
                $data[$variabel] = null;
            }
        }

        return $data;
    }
}
