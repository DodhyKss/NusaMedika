<?php

namespace App\Http\Controllers\EMR\KriteriaKeluarIcu;

use App\Helpers\AksesEhr;
use App\Helpers\EmrHelper;
use App\Helpers\IcuHelper;
use App\Http\Controllers\Controller;
use App\Models\Pegawai;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * Form Kriteria Keluar ICU (form 53, slug `kriteria_keluar_icu`).
 *
 * Empat kriteria keluar (KONSEP §4): I kelayakan life support (8 butir
 * YA/TIDAK — `kr1_status` turunan: YA bila semua butir TIDAK), II terapi
 * gagal/terminal, III stabil normal (parameter Tensi/Nadi/RR/Suhu/GCS/CVP),
 * IV prognosis jelek (kolom lain-lain). `kesimpulan` turunan server:
 * LAYAK / TIDAK LAYAK / null bila belum lengkap.
 */
class KriteriaKeluarIcuController extends Controller
{
    private const SLUG = 'kriteria_keluar_icu';

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

        // Dropdown konsultan ICU: dokter (profesi 1) aktif.
        $dokterKonsultans = Pegawai::where('profesi_id', 1)
            ->where(function ($q) {
                $q->whereNull('status_batal')->orWhere('status_batal', 0);
            })
            ->orderBy('nama_pegawai')
            ->get(['pegawai_id', 'nama_pegawai']);

        return view('moduls.EMR.KriteriaKeluarIcu.index', compact(
            'registrasi_detail', 'riwayat', 'historyGrouped', 'emr_data',
            'formAction', 'isEdit', 'deleteAction', 'isView', 'emr_id', 'aksesCrud', 'dokterKonsultans'
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

            return redirect()->back()->with('success', 'Kriteria Keluar ICU berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan Kriteria Keluar ICU: '.$e->getMessage())->withInput();
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

            return redirect()->back()->with('success', 'Kriteria Keluar ICU berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui Kriteria Keluar ICU: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Kriteria Keluar ICU berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus Kriteria Keluar ICU: '.$e->getMessage());
        }
    }

    private function validated(Request $request, int $form_id): array
    {
        $rules = [
            'tanggal_keluar_icu' => 'required|date',
            'waktu_keluar_icu' => 'required|date_format:H:i',
            'diagnosa_keluar_icu' => 'required|string|max:2000',
            'dokter_konsultan_icu' => 'required|integer|exists:pegawai,pegawai_id',
            'kr1_masker_nrm' => 'required|in:YA,TIDAK',
            'kr1_masker_rm' => 'required|in:YA,TIDAK',
            'kr1_jackson_rees' => 'required|in:YA,TIDAK',
            'kr1_ventilator' => 'required|in:YA,TIDAK',
            'kr1_dopamin' => 'required|in:YA,TIDAK',
            'kr1_dobutamin' => 'required|in:YA,TIDAK',
            'kr1_vascon' => 'required|in:YA,TIDAK',
            'kr1_adrenalin' => 'required|in:YA,TIDAK',
            'kr2_status' => 'required|in:YA,TIDAK',
            'kr3_tensi' => 'required|integer|min:30|max:300',
            'kr3_nadi' => 'required|integer|min:0|max:250',
            'kr3_rr' => 'required|integer|min:0|max:120',
            'kr3_suhu' => 'required|numeric|between:25,45',
            'kr3_gcs' => 'required|integer|between:3,15',
            'kr3_cvp' => 'required|integer|min:0|max:50',
            'kr3_status' => 'required|in:YA,TIDAK',
            'kr4_status' => 'required|in:YA,TIDAK',
            'kr4_lain' => 'nullable|string|max:2000',
            'catatan' => 'nullable|string|max:2000',
        ];

        $validated = $request->validate($rules);

        // Turunan server.
        $validated['kr1_status'] = IcuHelper::kr1Status($validated);
        $validated['kesimpulan'] = IcuHelper::kesimpulanKeluar($validated);

        // Validasi silang (KONSEP §4.7): kr2/kr4 YA → diagnosa + catatan wajib.
        if (in_array(strtoupper($validated['kr2_status']), ['YA'], true)
            || in_array(strtoupper($validated['kr4_status']), ['YA'], true)) {
            $catatan = trim((string) ($validated['catatan'] ?? ''));
            if (mb_strlen($catatan) < 5) {
                throw ValidationException::withMessages([
                    'catatan' => 'Catatan wajib diisi (min. 5 karakter) bila kriteria II atau IV dinyatakan YA.',
                ]);
            }
        }

        return $validated;
    }

    private function kosong(): array
    {
        return [
            'tanggal_keluar_icu' => '', 'waktu_keluar_icu' => '', 'diagnosa_keluar_icu' => '',
            'dokter_konsultan_icu' => '', 'kr1_status' => '', 'kr2_status' => '',
            'kr3_tensi' => '', 'kr3_nadi' => '', 'kr3_rr' => '', 'kr3_suhu' => '',
            'kr3_gcs' => '', 'kr3_cvp' => '', 'kr3_status' => '', 'kr4_status' => '',
            'kr4_lain' => '', 'kesimpulan' => '', 'catatan' => '',
        ];
    }
}
