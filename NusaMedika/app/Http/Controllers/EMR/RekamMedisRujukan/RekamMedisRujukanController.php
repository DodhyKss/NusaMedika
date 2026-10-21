<?php

namespace App\Http\Controllers\EMR\RekamMedisRujukan;

use App\Helpers\AksesEhr;
use App\Helpers\DiagnosisHelper;
use App\Helpers\EmrHelper;
use App\Helpers\SelectOption;
use App\Http\Controllers\Controller;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Form Rekam Medis Rujukan (form 58, slug `rekam_medis_rujukan`).
 *
 * Surat pengantar rujukan keluar RS (KONSEP_FORMULIR_RUJUKAN.md §3).
 *
 * Aturan server & deviasi terdokumentasi:
 * - Prefill saat BARU saja: `diagnosis` dari objek 40 terakhir
 *   (DiagnosisHelper — legacy `diagnosisDokter()` tidak ada) dan
 *   `handphone` dari `pasien.no_hp`; nilai browser tetap yang disimpan
 *   dan boleh diedit.
 * - `kriteria_covid` di-null-kan bila `screening_covid != 'Ya'`.
 * - `dirujuk_ke` menempel objek reuse 186 (milik form 16 — catatan
 *   TASK_FORM EMR-058).
 * - Cetak `print.blade.php` TIDAK diimplementasikan (`printUrl=''`,
 *   konsisten seluruh form P5/P6).
 */
class RekamMedisRujukanController extends Controller
{
    private const SLUG = 'rekam_medis_rujukan';

    /** Opsi EWS legacy — enum lokal 6 nilai, bukan key SelectOption. */
    private const EWS = ['0', '1', '2', '3', '4-6', '>=7'];

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
            $emr_data = EmrHelper::wrapData($this->kosong($registrasi_detail));
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

        // Opsi panjang dari SelectOption (bukan <option> hardcode di blade).
        $opsiUrgensi = SelectOption::get('tingkat_urgensi_rujukan');
        $opsiIndikasi = SelectOption::get('indikasi_rawat_inap');
        $opsiPelayanan = SelectOption::get('kebutuhan_pelayanan');
        $opsiKamar = SelectOption::get('kebutuhan_kamar');
        $opsiEws = self::EWS;

        return view('moduls.EMR.RekamMedisRujukan.index', compact(
            'registrasi_detail', 'riwayat', 'historyGrouped', 'emr_data',
            'formAction', 'isEdit', 'deleteAction', 'isView', 'emr_id', 'aksesCrud',
            'opsiUrgensi', 'opsiIndikasi', 'opsiPelayanan', 'opsiKamar', 'opsiEws'
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

        try {
            $data = $this->validated($request, (int) $form_id);
            $data = $this->filteredData($data);

            EmrHelper::insert((int) $form_id, $data, (int) $registrasi_detail_id);

            return redirect()->back()->with('success', 'Rekam Medis Rujukan berhasil disimpan.');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan Rekam Medis Rujukan: '.$e->getMessage())->withInput();
        }
    }

    public function update(Request $request, $registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'update'), 403);

        try {
            $data = $this->validated($request, (int) $form_id);
            $data = $this->filteredData($data);

            EmrHelper::update((int) $emr_id, (int) $form_id, $data);

            return redirect()->back()->with('success', 'Rekam Medis Rujukan berhasil diperbarui.');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui Rekam Medis Rujukan: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Rekam Medis Rujukan berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus Rekam Medis Rujukan: '.$e->getMessage());
        }
    }

    private function validated(Request $request, int $form_id): array
    {
        $rules = [
            'tanggal_pengantar' => 'required|date',
            'tgl_rencana_ranap' => 'nullable|date',
            'tingkat_urgensi' => 'required|in:Rawat Tindakan Elektif,Rawat IGD / Tindakan Cito IGD',
            'indikasi_rawat_inap' => 'required|in:'.implode(',', array_column(SelectOption::get('indikasi_rawat_inap'), 'value')),
            'rencana_pemeriksaan_penunjang' => 'required|string|max:2000',
            'terapi_keperawatan' => 'required|string|max:2000',
            'dokter_rawat' => 'required|integer|exists:pegawai,pegawai_id',
            'kebutuhan_pelayanan' => 'required|in:'.implode(',', array_column(SelectOption::get('kebutuhan_pelayanan'), 'value')),
            'kebutuhan_kamar' => 'required|in:'.implode(',', array_column(SelectOption::get('kebutuhan_kamar'), 'value')),
            'screening_covid' => 'required|in:Ya,Tidak',
            'kriteria_covid' => 'required_if:screening_covid,Ya|nullable|string|max:1000',
            'ews' => 'required|in:0,1,2,3,4-6,>=7',
            'oksigen' => 'required|in:Ya,Tidak',
            'ventilator' => 'required|in:Ya,Tidak',
            'monitor' => 'required|in:Ya,Tidak',
            'syringe_pump' => 'required|in:Ya,Tidak',
            'diagnosis' => 'required|string|max:2000',
            'handphone' => 'required|string|max:13',
            'alasan' => 'nullable|string|max:2000',
            'dirujuk_ke' => 'nullable|string|max:100',
        ];

        $validator = Validator::make($request->all(), $rules);
        $validator->validate();

        return array_merge(
            array_fill_keys(EmrHelper::objekVariabels($form_id), null),
            $validator->validated()
        );
    }

    /**
     * Aturan kondisional dibuang di sini, bukan sekadar tidak divalidasi
     * (baris emr_detail tetap ada — simpan E2E penuh).
     */
    private function filteredData(array $data): array
    {
        if (($data['screening_covid'] ?? null) !== 'Ya') {
            $data['kriteria_covid'] = null;
        }

        return $data;
    }

    /**
     * Nilai awal form BARU: prefill diagnosis (objek 40 terakhir) +
     * handphone pasien. Saat edit nilai tersimpan yang dipakai.
     */
    private function kosong($registrasi_detail): array
    {
        $pasien = $registrasi_detail->registrasi->pasien ?? null;

        return [
            'tanggal_pengantar' => date('Y-m-d'),
            'tgl_rencana_ranap' => '',
            'tingkat_urgensi' => '',
            'indikasi_rawat_inap' => '',
            'rencana_pemeriksaan_penunjang' => '',
            'terapi_keperawatan' => '',
            'dokter_rawat' => '',
            'kebutuhan_pelayanan' => '',
            'kebutuhan_kamar' => '',
            'screening_covid' => '',
            'kriteria_covid' => '',
            'ews' => '',
            'oksigen' => '',
            'ventilator' => '',
            'monitor' => '',
            'syringe_pump' => '',
            'diagnosis' => DiagnosisHelper::terakhir((int) $registrasi_detail->registrasi_detail_id),
            'handphone' => $pasien->no_hp ?? '',
            'alasan' => '',
            'dirujuk_ke' => '',
        ];
    }
}
