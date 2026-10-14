<?php

namespace App\Http\Controllers\EMR\KesimpulanMcu;

use App\Helpers\AksesEhr;
use App\Helpers\EmrHelper;
use App\Helpers\McuHelper;
use App\Http\Controllers\Controller;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Form Kesimpulan MCU (form 70, slug `kesimpulan_mcu`).
 *
 * Form PENUTUP episode Medical Check Up. Menggabungkan temuan 14 form organ
 * (71–84) menjadi interpretasi, kategori kesimpulan, catatan, dan rekomendasi.
 *
 * - Panel ringkasan (read-only) memakai `McuHelper::ringkasan()`.
 * - `interpretasi` di-prefill teks dari `McuHelper::interpretasiOtomatis()`
 *   (pola `SbarController`: teks prefill, EMR tersimpan menang, `old()` menang
 *   atas keduanya).
 * - Soft-gate: server menolak `store` bila BELUM ADA satupun form organ yang
 *   terisi pada kunjungan yang sama (konsep §19.4).
 * - Peringatan bila ≥ 3 temuan tidak normal — ditampilkan, bukan memblokir.
 */
class KesimpulanMcuController extends Controller
{
    private const SLUG = 'kesimpulan_mcu';

    public function index($registrasi_detail_id, $emr_id = null)
    {
        $registrasi_detail = RegistrasiDetail::with('registrasi.pasien')->findOrFail($registrasi_detail_id);

        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'read'), 403);

        $aksesCrud = AksesEhr::flags((int) $form_id);
        $riwayat = EmrHelper::emrList((int) $form_id, (int) $registrasi_detail->registrasi_id);
        $historyGrouped = EmrHelper::historyKunjunganGrouped($registrasi_detail);

        $ringkasan = McuHelper::ringkasan($registrasi_detail);
        $organBelum = McuHelper::organBelumDiperiksa($ringkasan);
        $jumlahTidakNormal = McuHelper::jumlahTemuanTidakNormal($ringkasan);
        $kategoriKesimpulan = McuHelper::kategoriKesimpulan();
        $prefillInterpretasi = McuHelper::interpretasiOtomatis($ringkasan);

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

        return view('moduls.EMR.KesimpulanMcu.index', compact(
            'registrasi_detail', 'riwayat', 'historyGrouped', 'emr_data',
            'formAction', 'isEdit', 'deleteAction', 'isView', 'emr_id', 'aksesCrud',
            'ringkasan', 'organBelum', 'jumlahTidakNormal', 'kategoriKesimpulan',
            'prefillInterpretasi'
        ));
    }

    public function store(Request $request, $registrasi_detail_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'create'), 403);

        // Soft-gate: minimal satu form organ sudah terisi.
        $registrasi_detail = RegistrasiDetail::findOrFail($registrasi_detail_id);
        $ringkasan = McuHelper::ringkasan($registrasi_detail);
        if (count(McuHelper::organBelumDiperiksa($ringkasan)) === count($ringkasan)) {
            return back()->with('error', 'Isi minimal satu pemeriksaan spesialis sebelum menyimpan Kesimpulan MCU.')->withInput();
        }

        $data = $this->validated($request);

        $user = Auth::user();
        if (! $user || ! $user->pegawai_id || ! $user->user_id) {
            return redirect()->back()->with('error', 'Sesi Anda Telah Habis Silahkan Login Kembali!');
        }

        // Snapshot petugas pelaksana bila kolomnya tidak diisi manual.
        if (empty($data['dokter_pemeriksa_id'])) {
            $data['dokter_pemeriksa_id'] = $user->pegawai_id;
        }

        try {
            EmrHelper::insert((int) $form_id, $this->filteredData($data, (int) $form_id), (int) $registrasi_detail_id);

            return redirect()->back()->with('success', 'Kesimpulan MCU berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan Kesimpulan MCU: '.$e->getMessage())->withInput();
        }
    }

    public function update(Request $request, $registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'update'), 403);

        $data = $this->validated($request);
        if (empty($data['dokter_pemeriksa_id'])) {
            $data['dokter_pemeriksa_id'] = Auth::user()->pegawai_id ?? null;
        }

        try {
            EmrHelper::update((int) $emr_id, (int) $form_id, $this->filteredData($data, (int) $form_id));

            return redirect()->back()->with('success', 'Kesimpulan MCU berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui Kesimpulan MCU: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Kesimpulan MCU berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus Kesimpulan MCU: '.$e->getMessage());
        }
    }

    private function kosong(): array
    {
        return [
            'tanggal_kesimpulan' => '', 'jam_kesimpulan' => '',
            'interpretasi' => '', 'kesimpulan_kategori' => '',
            'kesimpulan_catatan' => '', 'rekomendasi' => '',
            'dokter_pemeriksa_id' => '',
        ];
    }

    private function validated(Request $request): array
    {
        $rules = [
            'tanggal_kesimpulan' => 'required|date',
            'jam_kesimpulan' => 'required|date_format:H:i',
            'interpretasi' => 'required|string|max:5000',
            'kesimpulan_kategori' => 'required|in:1,2,3,4',
            'kesimpulan_catatan' => 'required_if:kesimpulan_kategori,2,3,4|nullable|string|max:2000',
            'rekomendasi' => 'required|string|max:5000',
            'dokter_pemeriksa_id' => 'nullable|integer|exists:pegawai,pegawai_id',
        ];

        return $request->validate($rules);
    }

    private function filteredData(array $data, int $formId): array
    {
        $data = array_intersect_key($data, array_flip(EmrHelper::objekVariabels($formId)));

        foreach ($data as $key => $value) {
            if (is_string($value) && trim($value) === '') {
                $data[$key] = null;
            }
        }

        return $data;
    }
}
