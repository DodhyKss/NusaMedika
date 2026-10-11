<?php

namespace App\Http\Controllers\EMR\PenilaianNyeri;

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
 * Form Penilaian Nyeri (form 27, slug `penilaian_nyeri`).
 *
 * Pengkajian nyeri utama: flag nyeri, metode skala (VAS atau FLACC), frekuensi,
 * karakteristik, lokasi (teks + body map), pemicu/meringankan/intervensi.
 * Field computed `skor_flacc` SELALU dihitung ulang di server — nilai dari
 * browser dibuang.
 */
class PenilaianNyeriController extends Controller
{
    private const SLUG = 'penilaian_nyeri';

    /**
     * 5 butir FLACC. Sengaja TIDAK ada di `objek_form_control` (objek NULL)
     * karena nilainya sudah terwakili `skor_flacc` (objek 318). Jadi field ini
     * WAJIB dibaca di `filteredData()` SEBELUM `array_intersect_key()`, pola yang
     * sama dengan `ews_na` pada `EwsHelper`.
     */
    private const FLACC = ['flacc_wajah', 'flacc_kaki', 'flacc_aktivitas', 'flacc_menangis', 'flacc_konsolabilitas'];

    /** Field yang tidak berlaku bila pasien tidak nyeri. */
    private const BUKAN_NYERI = [
        'metode_skala_nyeri', 'skor_nyeri', 'skor_flacc', 'frekuensi_nyeri',
        'karakteristik_nyeri', 'pemicu_nyeri', 'meringankan_nyeri', 'lokasi_nyeri',
        'gambar_lokasi_nyeri', 'intervensi_nyeri',
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

        return view('moduls.EMR.PenilaianNyeri.index', compact(
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
            EmrHelper::insert((int) $form_id, $this->filteredData($request, $data, (int) $form_id), (int) $registrasi_detail_id);

            return redirect()->back()->with('success', 'Penilaian Nyeri berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan Penilaian Nyeri: '.$e->getMessage())->withInput();
        }
    }

    public function update(Request $request, $registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'update'), 403);

        $data = $this->validated($request);

        try {
            EmrHelper::update((int) $emr_id, (int) $form_id, $this->filteredData($request, $data, (int) $form_id));

            return redirect()->back()->with('success', 'Penilaian Nyeri berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui Penilaian Nyeri: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Penilaian Nyeri berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus Penilaian Nyeri: '.$e->getMessage());
        }
    }

    /**
     * Nilai kosong untuk form baru.
     */
    private function kosong(): array
    {
        return [
            'tanggal_nyeri' => now()->format('Y-m-d'),
            'waktu_nyeri' => now()->format('H:i'),
            'nyeri' => 'Ya',
            'metode_skala_nyeri' => 'VAS',
            'skor_nyeri' => '',
            'skor_flacc' => '',
            'frekuensi_nyeri' => '',
            'karakteristik_nyeri' => [],
            'pemicu_nyeri' => '',
            'meringankan_nyeri' => '',
            'lokasi_nyeri' => '',
            'gambar_lokasi_nyeri' => '',
            'intervensi_nyeri' => '',
            'catatan' => '',
        ];
    }

    private function validated(Request $request): array
    {
        $opsional = [
            'metode_skala_nyeri' => null,
            'skor_nyeri' => null,
            'skor_flacc' => null,
            'frekuensi_nyeri' => null,
            'karakteristik_nyeri' => [],
            'pemicu_nyeri' => null,
            'meringankan_nyeri' => null,
            'lokasi_nyeri' => null,
            'gambar_lokasi_nyeri' => null,
            'intervensi_nyeri' => null,
            'catatan' => null,
        ];

        return array_merge($opsional, $request->validate([
            'tanggal_nyeri' => 'required|date',
            'waktu_nyeri' => 'required|date_format:H:i',
            'nyeri' => ['required', Rule::in(array_column(SelectOption::get('ya_tidak_kapital'), 'value'))],

            'metode_skala_nyeri' => ['nullable', 'required_if:nyeri,Ya', Rule::in(array_column(SelectOption::get('metode_skala_nyeri'), 'value'))],
            'skor_nyeri' => 'nullable|required_if:metode_skala_nyeri,VAS|integer|between:0,10',

            'frekuensi_nyeri' => ['nullable', 'required_if:nyeri,Ya', Rule::in(array_column(SelectOption::get('frekuensi_nyeri_penilaian'), 'value'))],
            'karakteristik_nyeri' => ['nullable', 'required_if:nyeri,Ya', 'array', Rule::when($request->input('nyeri') === 'Ya', 'min:1')],
            'karakteristik_nyeri.*' => 'string|max:100',

            'pemicu_nyeri' => 'nullable|required_if:nyeri,Ya|string|max:1000',
            'meringankan_nyeri' => 'nullable|required_if:nyeri,Ya|string|max:1000',
            'lokasi_nyeri' => 'nullable|required_if:nyeri,Ya|string|max:200',
            'intervensi_nyeri' => 'nullable|required_if:nyeri,Ya|string|max:2000',

            'gambar_lokasi_nyeri' => 'nullable|string|max:60000',
            'catatan' => 'nullable|string|max:1000',
        ]));
    }

    /**
     * Hanya field terpetakan yang disimpan.
     *
     * - Butir FLACC dibaca dari request SEBELUM `array_intersect_key()` karena
     *   sengaja tidak punya objek di `objek_form_control`.
     * - `skor_flacc` dihitung ulang di server; nilai browser dibuang.
     * - Field yang tidak berlaku (pasien tidak nyeri / metode bukan FLACC)
     *   dikosongkan supaya tidak ada baris berisi nilai yang menyesatkan.
     */
    private function filteredData(Request $request, array $data, int $formId): array
    {
        $flacc = [];
        foreach (self::FLACC as $butir) {
            $flacc[$butir] = $request->input($butir);
        }

        $data = array_intersect_key($data, array_flip(EmrHelper::objekVariabels($formId)));

        $adaNyeri = ($data['nyeri'] ?? '') === 'Ya';
        $metode = $data['metode_skala_nyeri'] ?? '';

        // Skor FLACC hanya bila metode FLACC DAN 5/5 butir terisi.
        if ($adaNyeri && $metode === 'FLACC') {
            $hasil = NyeriHelper::flacc($flacc);
            $data['skor_flacc'] = $hasil['total'];
        } else {
            $data['skor_flacc'] = null;
        }

        // Tanpa nyeri, seluruh blok pain score dibuang (dikosongkan, bukan
        // "0" palsu).
        if (! $adaNyeri) {
            foreach (self::BUKAN_NYERI as $key) {
                $data[$key] = null;
            }
        }

        // Metode VAS: butir FLACC tidak berlaku.
        if ($metode !== 'FLACC') {
            $data['skor_flacc'] = null;
        }

        // Buang entri "Lainnya" yang teksnya kosong.
        if (is_array($data['karakteristik_nyeri'] ?? null)) {
            $bersih = array_values(array_filter(
                $data['karakteristik_nyeri'],
                fn ($item) => trim((string) $item) !== '' && trim((string) $item) !== 'Lainnya'
            ));
            $data['karakteristik_nyeri'] = $bersih === [] ? null : $bersih;
        }

        // Body map hanya boleh berisi data URL PNG.
        $data['gambar_lokasi_nyeri'] = NyeriHelper::normalisasiGambar($data['gambar_lokasi_nyeri'] ?? null);

        return $data;
    }
}
