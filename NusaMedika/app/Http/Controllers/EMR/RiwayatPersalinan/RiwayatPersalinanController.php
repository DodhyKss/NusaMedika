<?php

namespace App\Http\Controllers\EMR\RiwayatPersalinan;

use App\Helpers\AksesEhr;
use App\Helpers\EmrHelper;
use App\Http\Controllers\Controller;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Form Riwayat Persalinan Sekarang (form 63, slug `riwayat_persalinan`).
 *
 * Rekap episode persalinan berjalan (diisi bidan setelah kala II–III).
 * `durasi_kala_*` harus `HH:MM` valid. `warna_ketuban = Hijau` mewajibkan
 * `ketuban_lain` (meconium — alarm IUGR/aspiksia). Kehilangan darah > 500 cc
 * memunculkan peringatan (bukan blocking).
 */
class RiwayatPersalinanController extends Controller
{
    private const SLUG = 'riwayat_persalinan';

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

        return view('moduls.EMR.RiwayatPersalinan.index', compact(
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
            EmrHelper::insert((int) $form_id, $data, (int) $registrasi_detail_id);

            return redirect()->back()->with('success', 'Riwayat Persalinan Sekarang berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan Riwayat Persalinan: '.$e->getMessage())->withInput();
        }
    }

    public function update(Request $request, $registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'update'), 403);

        $data = $this->validated($request);

        try {
            EmrHelper::update((int) $emr_id, (int) $form_id, $data);

            return redirect()->back()->with('success', 'Riwayat Persalinan Sekarang berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui Riwayat Persalinan: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Riwayat Persalinan berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus Riwayat Persalinan: '.$e->getMessage());
        }
    }

    private function kosong(): array
    {
        return [
            'penolong_persalinan' => '', 'penolong_persalinan_lain' => '',
            'durasi_kala_1' => '', 'durasi_kala_2' => '', 'durasi_kala_3' => '',
            'ketuban' => '', 'warna_ketuban' => '', 'ketuban_lain' => '',
            'jam_persalinan_kala_2' => '', 'tanggal_lahir_bayi' => '', 'jenis_kelamin_kala_2' => '',
            'as_bayi' => '', 'berat_badan_kala_2' => '', 'tinggi_badan_kala_2' => '',
            'lingkar_kepala_kala_2' => '', 'kelainan_bayi' => '',
            'plasenta_lahir' => '', 'infark_plasenta' => '',
            'keadaan_umum_ibu' => '', 'kehilangan_darah' => '',
        ];
    }

    private function validated(Request $request): array
    {
        $validator = \Validator::make($request->all(), [
            'penolong_persalinan' => 'required|in:Dokter Obgyn,Bidan,Lainnya',
            'penolong_persalinan_lain' => 'required_if:penolong_persalinan,Lainnya|nullable|string|max:100',
            'durasi_kala_1' => 'required|regex:/^\d{1,2}:\d{2}$/',
            'durasi_kala_2' => 'required|regex:/^\d{1,2}:\d{2}$/',
            'durasi_kala_3' => 'required|regex:/^\d{1,2}:\d{2}$/',
            'ketuban' => 'required|in:Pecah,Dipecahkan',
            'warna_ketuban' => 'required|in:Jernih,Keruh,Hijau',
            'ketuban_lain' => 'required_if:warna_ketuban,Hijau|nullable|string|max:255',
            'jam_persalinan_kala_2' => 'required|date_format:H:i',
            'tanggal_lahir_bayi' => 'required|date|before_or_equal:today',
            'jenis_kelamin_kala_2' => 'required|in:L,P',
            'as_bayi' => 'required|string|max:1',
            'berat_badan_kala_2' => 'required|numeric|min:0|max:10',
            'tinggi_badan_kala_2' => 'required|numeric|min:0|max:70',
            'lingkar_kepala_kala_2' => 'required|numeric|min:0|max:60',
            'kelainan_bayi' => 'required|string|max:255',
            'plasenta_lahir' => 'required|string|max:255',
            'infark_plasenta' => 'required|string|max:255',
            'keadaan_umum_ibu' => 'required|string|max:255',
            'kehilangan_darah' => 'required|numeric|min:0',
        ]);

        // Menit durasi < 60.
        $validator->after(function ($validator) use ($request) {
            foreach (['durasi_kala_1', 'durasi_kala_2', 'durasi_kala_3'] as $k) {
                $v = $request->input($k);
                if (is_string($v) && preg_match('/^\d{1,2}:(\d{2})$/', $v, $m) && (int) $m[1] > 59) {
                    $validator->errors()->add($k, 'Menit durasi tidak boleh lebih dari 59.');
                }
            }
        });
        $validator->validate();

        return array_merge(array_fill_keys(array_keys($this->kosong()), null), $validator->validated());
    }
}
