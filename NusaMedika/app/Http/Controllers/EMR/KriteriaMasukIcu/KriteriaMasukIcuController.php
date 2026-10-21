<?php

namespace App\Http\Controllers\EMR\KriteriaMasukIcu;

use App\Helpers\AksesEhr;
use App\Helpers\EmrHelper;
use App\Helpers\IcuHelper;
use App\Http\Controllers\Controller;
use App\Models\Pegawai;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Form Kriteria Masuk ICU (form 52, slug `kriteria_masuk_icu`).
 *
 * Dokumen keputusan transfer ke ICU: 18 kriteria klinis (nilai + status
 * YA/TIDAK), GCS, informed consent, prioritas I/II/III, kontraindikasi.
 * `gcs_jumlah` & `jumlah_kriteria_terpenuhi` dihitung ulang di server;
 * `kesimpulan` diisi manual (legacy tidak punya ambang — KONSEP §3.3).
 */
class KriteriaMasukIcuController extends Controller
{
    private const SLUG = 'kriteria_masuk_icu';

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

        // Dropdown DPJP ICU: dokter (profesi 1) aktif.
        $dpjpIcus = Pegawai::where('profesi_id', 1)
            ->where(function ($q) {
                $q->whereNull('status_batal')->orWhere('status_batal', 0);
            })
            ->orderBy('nama_pegawai')
            ->get(['pegawai_id', 'nama_pegawai']);

        // Default diagnosa primer bila belum ada data EMR.
        if (! $isEdit) {
            $diagnosaDefault = RegistrasiDetail::find($registrasi_detail_id)
                ->registrasi?->pasien;
        }

        return view('moduls.EMR.KriteriaMasukIcu.index', compact(
            'registrasi_detail', 'riwayat', 'historyGrouped', 'emr_data',
            'formAction', 'isEdit', 'deleteAction', 'isView', 'emr_id', 'aksesCrud', 'dpjpIcus'
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

            return redirect()->back()->with('success', 'Kriteria Masuk ICU berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan Kriteria Masuk ICU: '.$e->getMessage())->withInput();
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

            return redirect()->back()->with('success', 'Kriteria Masuk ICU berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui Kriteria Masuk ICU: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Kriteria Masuk ICU berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus Kriteria Masuk ICU: '.$e->getMessage());
        }
    }

    private function validated(Request $request, int $form_id): array
    {
        $rules = [
            'tanggal_kriteria' => 'required|date',
            'waktu_kriteria' => 'required|date_format:H:i',
            'diagnosa' => 'required|string|max:2000',
            'dpjp_icu' => 'required|integer|exists:pegawai,pegawai_id',
            'td_nilai' => 'required|integer|min:30|max:300',
            'td_status' => 'required|in:YA,TIDAK',
            'gcs_e' => 'required|integer|between:1,4',
            'gcs_m' => 'required|integer|between:1,6',
            'gcs_v' => 'required|integer|between:1,5',
            'informed_consent' => 'required|in:YA,TIDAK',
            'prioritas_icu' => 'required|in:I,II,III',
            'kontra_indikasi' => 'required|in:Ada,Tidak',
            'catatan' => 'nullable|string|max:2000',
        ];
        // Grup respirasi: 7 item (frekuensi per usia + SpO2 + deskripsi).
        for ($i = 1; $i <= 5; $i++) {
            $rules['resp_'.$i.'_nilai'] = 'required|integer|min:0|max:120';
            $rules['resp_'.$i.'_status'] = 'required|in:YA,TIDAK';
        }
        $rules['resp_6_nilai'] = 'required|numeric|min:0|max:100';
        $rules['resp_6_status'] = 'required|in:YA,TIDAK';
        $rules['resp_7_nilai'] = 'nullable|string|max:100';
        $rules['resp_7_status'] = 'required|in:YA,TIDAK';
        // Grup nadi: 6 item.
        for ($i = 1; $i <= 6; $i++) {
            $rules['nadi_'.$i.'_nilai'] = 'required|integer|min:0|max:250';
            $rules['nadi_'.$i.'_status'] = 'required|in:YA,TIDAK';
        }
        // Grup EKG: 2 item (deskripsi).
        for ($i = 1; $i <= 2; $i++) {
            $rules['ekg_'.$i.'_nilai'] = 'nullable|string|max:200';
            $rules['ekg_'.$i.'_status'] = 'required|in:YA,TIDAK';
        }
        // Pasca bedah & obat (1 butir masing-masing).
        $rules['pasca_bedah_nilai'] = 'nullable|string|max:200';
        $rules['pasca_bedah_status'] = 'required|in:YA,TIDAK';
        $rules['obat_nilai'] = 'nullable|string|max:200';
        $rules['obat_status'] = 'required|in:YA,TIDAK';

        $validated = $request->validate($rules);

        // Turunan server: nilai browser dibuang.
        $validated['gcs_jumlah'] = IcuHelper::gcsJumlah(
            $validated['gcs_e'] ?? null,
            $validated['gcs_m'] ?? null,
            $validated['gcs_v'] ?? null
        );
        $validated['jumlah_kriteria_terpenuhi'] = IcuHelper::jumlahKriteriaTerpenuhi($validated);
        $validated['kesimpulan'] = IcuHelper::kesimpulanMasuk($validated['jumlah_kriteria_terpenuhi']);

        // Field readonly di form (dpjp dari penanggung rawat) tidak disimpan.
        unset($validated['dpjp']);

        return $validated;
    }

    private function kosong(): array
    {
        return [
            'tanggal_kriteria' => '', 'waktu_kriteria' => '', 'diagnosa' => '',
            'dpjp_icu' => '', 'gcs_e' => '', 'gcs_m' => '', 'gcs_v' => '', 'gcs_jumlah' => '',
            'informed_consent' => '', 'prioritas_icu' => '', 'kontra_indikasi' => '',
            'jumlah_kriteria_terpenuhi' => '', 'kesimpulan' => '', 'catatan' => '',
        ];
    }
}
