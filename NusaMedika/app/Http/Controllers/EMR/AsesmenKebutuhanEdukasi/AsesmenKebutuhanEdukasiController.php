<?php

namespace App\Http\Controllers\EMR\AsesmenKebutuhanEdukasi;

use App\Helpers\AksesEhr;
use App\Helpers\EmrHelper;
use App\Helpers\SelectOption;
use App\Http\Controllers\Controller;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * Form Asesmen Kebutuhan Edukasi (form 31, slug `asesmen_kebutuhan_edukasi`).
 *
 * Menentukan apakah pasien mampu belajar dan menerima edukasi: tingkat
 * pendidikan, kemampuan bicara/baca, bahasa, penerjemah, nilai kebudayaan,
 * hambatan belajar, kebutuhan belajar, serta kesediaan menerima edukasi.
 *
 * Checkbox group disimpan sebagai JSON array pada SATU variabel
 * (`emr_detail.value`), bukan satu baris per centang - satu variabel tanpa
 * sufiks akan tertimpa oleh `pluck('value','variabel')`.
 *
 * `keterangan_kebudayaan` hanya berlaku bila `nilai_kebudayaan` = ada;
 * selain itu field itu dibuang di `filteredData()`.
 */
class AsesmenKebutuhanEdukasiController extends Controller
{
    private const SLUG = 'asesmen_kebutuhan_edukasi';

    /** Checkbox group (disimpan sebagai JSON array). */
    private const GRUP = [
        'hambatan_belajar',
        'kebutuhan_belajar',
        'alasan_asesmen_ulang',
        'respon_emosi_edukasi',
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

        return view('moduls.EMR.AsesmenKebutuhanEdukasi.index', compact(
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

            return redirect()->back()->with('success', 'Asesmen Kebutuhan Edukasi berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan Asesmen Kebutuhan Edukasi: '.$e->getMessage())->withInput();
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

            return redirect()->back()->with('success', 'Asesmen Kebutuhan Edukasi berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui Asesmen Kebutuhan Edukasi: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Asesmen Kebutuhan Edukasi berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus Asesmen Kebutuhan Edukasi: '.$e->getMessage());
        }
    }

    /**
     * Nilai kosong untuk form baru.
     */
    private function kosong(): array
    {
        return [
            'tingkat_pendidikan' => '', 'kemampuan_bicara' => '', 'jenis_bahasa' => '',
            'perlu_penerjemah' => '', 'nilai_kebudayaan' => '', 'keterangan_kebudayaan' => '',
            'kemampuan_baca' => '', 'hambatan_belajar' => '', 'kebutuhan_belajar' => '',
            'sedia_terima_edukasi' => '', 'alasan_asesmen_ulang' => '',
            'materi_edukasi' => '', 'respon_emosi_edukasi' => '',
        ];
    }

    private function validated(Request $request): array
    {
        $opsional = [
            'keterangan_kebudayaan' => null,
            'alasan_asesmen_ulang' => null,
            'materi_edukasi' => null,
            'respon_emosi_edukasi' => null,
        ];

        return array_merge($opsional, $request->validate([
            'tingkat_pendidikan' => ['required', Rule::in(array_column(SelectOption::get('tingkat_pendidikan_edukasi'), 'value'))],
            'kemampuan_bicara' => ['required', Rule::in(array_column(SelectOption::get('kemampuan_bicara'), 'value'))],
            'jenis_bahasa' => 'required|string|max:100',
            'perlu_penerjemah' => ['required', Rule::in(array_column(SelectOption::get('perlu_penerjemah'), 'value'))],
            'nilai_kebudayaan' => ['required', Rule::in(array_column(SelectOption::get('ada_tidak'), 'value'))],
            'keterangan_kebudayaan' => ['nullable', 'required_if:nilai_kebudayaan,ada', 'string', 'max:500'],
            'kemampuan_baca' => ['required', Rule::in(array_column(SelectOption::get('ada_tidak'), 'value'))],

            'hambatan_belajar' => ['required', 'array', 'min:1'],
            'hambatan_belajar.*' => 'string|max:255',

            'kebutuhan_belajar' => ['required', 'array', 'min:1'],
            'kebutuhan_belajar.*' => 'string|max:255',

            'sedia_terima_edukasi' => ['required', Rule::in(array_column(SelectOption::get('sedia_terima_edukasi'), 'value'))],

            'alasan_asesmen_ulang' => ['nullable', 'array'],
            'alasan_asesmen_ulang.*' => 'string|max:255',

            'materi_edukasi' => 'nullable|string|max:2000',

            'respon_emosi_edukasi' => ['nullable', 'array'],
            'respon_emosi_edukasi.*' => 'string|max:255',
        ]));
    }

    /**
     * Hanya field terpetakan yang disimpan. Grup kosong menjadi null (bukan
     * string "[]"), dan keterangan kebudayaan dibuang bila tidak ada nilai
     * kebudayaan yang perlu dihormati.
     */
    private function filteredData(array $data, int $formId): array
    {
        $data = array_intersect_key($data, array_flip(EmrHelper::objekVariabels($formId)));

        if (($data['nilai_kebudayaan'] ?? '') !== 'ada') {
            $data['keterangan_kebudayaan'] = null;
        }

        foreach (self::GRUP as $grup) {
            $nilai = $data[$grup] ?? null;
            $data[$grup] = is_array($nilai) && $nilai !== [] ? $nilai : null;
        }

        return $data;
    }
}
