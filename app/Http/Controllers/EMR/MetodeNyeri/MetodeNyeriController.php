<?php

namespace App\Http\Controllers\EMR\MetodeNyeri;

use App\Helpers\AksesEhr;
use App\Helpers\EmrHelper;
use App\Helpers\NyeriHelper;
use App\Helpers\SelectOption;
use App\Http\Controllers\Controller;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * Form Metode Nyeri (form 36, slug `metode_nyeri`).
 *
 * Pemilih metode penskalaan VAS/NRS atau FLACC, berdiri sendiri (tidak
 * bergantung form 27) sehingga tetap bisa diisi saat pasien berpindah unit.
 * `skor_flacc` dan `interpretasi_nyeri` SELALU dihitung ulang di server — nilai
 * dari browser dibuang.
 */
class MetodeNyeriController extends Controller
{
    private const SLUG = 'metode_nyeri';

    /** 5 butir FLACC; variabelnya sudah punya objek (320-324). */
    private const FLACC = ['flacc_wajah', 'flacc_kaki', 'flacc_aktivitas', 'flacc_menangis', 'flacc_konsolabilitas'];

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

        return view('moduls.EMR.MetodeNyeri.index', compact(
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

            return redirect()->back()->with('success', 'Metode Nyeri berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan Metode Nyeri: '.$e->getMessage())->withInput();
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

            return redirect()->back()->with('success', 'Metode Nyeri berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui Metode Nyeri: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Metode Nyeri berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus Metode Nyeri: '.$e->getMessage());
        }
    }

    /**
     * Nilai kosong untuk form baru.
     */
    private function kosong(): array
    {
        $d = [
            'metode_skala_nyeri' => 'VAS',
            'skor_nyeri' => '',
            'skor_flacc' => '',
            'interpretasi_nyeri' => '',
            'keterangan' => '',
        ];

        foreach (self::FLACC as $butir) {
            $d[$butir] = '';
        }

        return $d;
    }

    private function validated(Request $request): array
    {
        $opsional = [
            'skor_nyeri' => null,
            'flacc_wajah' => null,
            'flacc_kaki' => null,
            'flacc_aktivitas' => null,
            'flacc_menangis' => null,
            'flacc_konsolabilitas' => null,
            'skor_flacc' => null,
            'interpretasi_nyeri' => null,
            'keterangan' => null,
        ];

        return array_merge($opsional, $request->validate([
            'metode_skala_nyeri' => ['required', Rule::in(array_column(SelectOption::get('metode_skala_nyeri'), 'value'))],
            'skor_nyeri' => 'nullable|required_if:metode_skala_nyeri,VAS|integer|between:0,10',

            'flacc_wajah' => 'nullable|required_if:metode_skala_nyeri,FLACC|integer|between:0,2',
            'flacc_kaki' => 'nullable|required_if:metode_skala_nyeri,FLACC|integer|between:0,2',
            'flacc_aktivitas' => 'nullable|required_if:metode_skala_nyeri,FLACC|integer|between:0,2',
            'flacc_menangis' => 'nullable|required_if:metode_skala_nyeri,FLACC|integer|between:0,2',
            'flacc_konsolabilitas' => 'nullable|required_if:metode_skala_nyeri,FLACC|integer|between:0,2',

            'keterangan' => 'nullable|string|max:1000',
        ]));
    }

    /**
     * Hanya field terpetakan yang disimpan. `skor_flacc` dan
     * `interpretasi_nyeri` dihitung ulang di server; nilai browser dibuang.
     */
    private function filteredData(array $data, int $formId): array
    {
        $data = array_intersect_key($data, array_flip(EmrHelper::objekVariabels($formId)));

        $metode = $data['metode_skala_nyeri'] ?? '';

        $flacc = [];
        foreach (self::FLACC as $butir) {
            $flacc[$butir] = $data[$butir] ?? null;
        }

        if ($metode === 'FLACC') {
            $hasil = NyeriHelper::flacc($flacc);
            $data['skor_flacc'] = $hasil['total'];
            $data['interpretasi_nyeri'] = $hasil['kategori'];
        } else {
            // Metode VAS: butir FLACC tidak berlaku, interpretasi ikut dari skor VAS.
            foreach (self::FLACC as $butir) {
                $data[$butir] = null;
            }
            $data['skor_flacc'] = null;

            $skorVas = $data['skor_nyeri'] ?? null;
            $data['interpretasi_nyeri'] = ($skorVas === null || $skorVas === '')
                ? null
                : NyeriHelper::kategoriVas((int) $skorVas);
        }

        return $data;
    }
}
