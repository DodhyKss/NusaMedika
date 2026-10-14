<?php

namespace App\Http\Controllers\EMR\PenilaianBayiDenganApgarScore;

use App\Helpers\AksesEhr;
use App\Helpers\ApgarHelper;
use App\Helpers\EmrHelper;
use App\Http\Controllers\Controller;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Form Penilaian Bayi dengan APGAR Score (form 66, slug `penilaian_bayi_dengan_apgar_score`).
 *
 * Lima tanda APGAR × 5 titik waktu (menit 1, 5, 10, 15, 20). Menit 1 & 5
 * wajib; menit 10/15/20 opsional (null = belum dinilai). `apgar_total_*`
 * selalu dihitung ulang di server via `ApgarHelper::hitung()`.
 */
class PenilaianBayiDenganApgarScoreController extends Controller
{
    private const SLUG = 'penilaian_bayi_dengan_apgar_score';

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

        // Total per menit untuk tampilan (recompute dari nilai tersimpan).
        $totalTersimpan = [];
        foreach (ApgarHelper::MENIT as $menit) {
            $totalTersimpan[$menit] = $emr_data[ApgarHelper::variabelTotal($menit)] ?? null;
        }

        return view('moduls.EMR.PenilaianBayiDenganApgarScore.index', compact(
            'registrasi_detail', 'riwayat', 'historyGrouped', 'emr_data',
            'formAction', 'isEdit', 'deleteAction', 'isView', 'emr_id', 'aksesCrud',
            'totalTersimpan'
        ) + ['menitApgar' => ApgarHelper::MENIT, 'tandaApgar' => ApgarHelper::TANDA, 'warnaApgar' => ApgarHelper::warna()]);
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
            EmrHelper::insert((int) $form_id, $this->filteredData($data), (int) $registrasi_detail_id);

            return redirect()->back()->with('success', 'Penilaian APGAR berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan Penilaian APGAR: '.$e->getMessage())->withInput();
        }
    }

    public function update(Request $request, $registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'update'), 403);

        $data = $this->validated($request);

        try {
            EmrHelper::update((int) $emr_id, (int) $form_id, $this->filteredData($data));

            return redirect()->back()->with('success', 'Penilaian APGAR berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui Penilaian APGAR: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Penilaian APGAR berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus Penilaian APGAR: '.$e->getMessage());
        }
    }

    private function kosong(): array
    {
        $d = [];
        foreach (ApgarHelper::MENIT as $menit) {
            foreach (array_keys(ApgarHelper::TANDA) as $tanda) {
                $d[ApgarHelper::variabel($tanda, $menit)] = '';
            }
            $d[ApgarHelper::variabelTotal($menit)] = '';
        }

        return $d;
    }

    private function validated(Request $request): array
    {
        // Bentuk string literal (bukan loop helper) supaya aturan terbaca oleh
        // harness `tools/lib_pay.php` — konsisten dengan konvensi Fase P2–P4.
        return $request->validate([
            'apgar_frekuensi_jantung_1' => 'required|in:0,1,2',
            'apgar_usaha_napas_1' => 'required|in:0,1,2',
            'apgar_tonus_otot_1' => 'required|in:0,1,2',
            'apgar_refleks_1' => 'required|in:0,1,2',
            'apgar_warna_1' => 'required|in:0,1,2',
            'apgar_frekuensi_jantung_5' => 'required|in:0,1,2',
            'apgar_usaha_napas_5' => 'required|in:0,1,2',
            'apgar_tonus_otot_5' => 'required|in:0,1,2',
            'apgar_refleks_5' => 'required|in:0,1,2',
            'apgar_warna_5' => 'required|in:0,1,2',
            'apgar_frekuensi_jantung_10' => 'nullable|in:0,1,2',
            'apgar_usaha_napas_10' => 'nullable|in:0,1,2',
            'apgar_tonus_otot_10' => 'nullable|in:0,1,2',
            'apgar_refleks_10' => 'nullable|in:0,1,2',
            'apgar_warna_10' => 'nullable|in:0,1,2',
            'apgar_frekuensi_jantung_15' => 'nullable|in:0,1,2',
            'apgar_usaha_napas_15' => 'nullable|in:0,1,2',
            'apgar_tonus_otot_15' => 'nullable|in:0,1,2',
            'apgar_refleks_15' => 'nullable|in:0,1,2',
            'apgar_warna_15' => 'nullable|in:0,1,2',
            'apgar_frekuensi_jantung_20' => 'nullable|in:0,1,2',
            'apgar_usaha_napas_20' => 'nullable|in:0,1,2',
            'apgar_tonus_otot_20' => 'nullable|in:0,1,2',
            'apgar_refleks_20' => 'nullable|in:0,1,2',
            'apgar_warna_20' => 'nullable|in:0,1,2',
        ]);
    }

    private function filteredData(array $data): array
    {
        $data = array_intersect_key($data, array_flip(EmrHelper::objekVariabels(
            (int) EmrHelper::formIdBySlug(self::SLUG)
        )));

        foreach (ApgarHelper::MENIT as $menit) {
            foreach (array_keys(ApgarHelper::TANDA) as $tanda) {
                $key = ApgarHelper::variabel($tanda, $menit);
                $data[$key] = ($data[$key] === null || $data[$key] === '') ? null : (int) $data[$key];
            }
            // Total selalu dihitung ulang di server (bukan nilai browser).
            $skor = ApgarHelper::hitung($data, $menit);
            $data[ApgarHelper::variabelTotal($menit)] = $skor;
        }

        return $data;
    }
}
