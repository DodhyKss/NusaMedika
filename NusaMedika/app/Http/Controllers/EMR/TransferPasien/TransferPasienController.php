<?php

namespace App\Http\Controllers\EMR\TransferPasien;

use App\Helpers\AksesEhr;
use App\Helpers\EmrHelper;
use App\Helpers\SelectOption;
use App\Http\Controllers\Controller;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * Form Transfer Pasien Antar Ruangan (form 94, slug `transfer_pasien`).
 *
 * Serah terima pasien antar ruang (IGD → ruang, ruang → ruang,
 * pre-operasi → kamar operasi). KONSEP §12: ruangan tujuan harus
 * berbeda dari ruangan asal; dokumen pendukung minimal satu tercentang.
 */
class TransferPasienController extends Controller
{
    private const SLUG = 'transfer_pasien';

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

        return view('moduls.EMR.TransferPasien.index', compact(
            'registrasi_detail', 'riwayat', 'historyGrouped', 'emr_data',
            'formAction', 'isEdit', 'deleteAction', 'isView', 'emr_id', 'aksesCrud'
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

            return redirect()->back()->with('success', 'Transfer Pasien berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan Transfer Pasien: '.$e->getMessage())->withInput();
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

            return redirect()->back()->with('success', 'Transfer Pasien berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui Transfer Pasien: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Transfer Pasien berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus Transfer Pasien: '.$e->getMessage());
        }
    }

    private function validated(Request $request, int $form_id): array
    {
        $rules = [
            'tanggal_transfer' => 'required|date',
            'jam_transfer' => 'required|date_format:H:i',
            'ruangan_asal' => 'required|integer|exists:bagian,bagian_id',
            'ruangan_tujuan' => 'required|integer|exists:bagian,bagian_id',
            'kategori_transfer' => 'required|in:Rawat Inap,Rawat Jalan,IGD',
            'identitas_pasien' => 'required|string|max:2000',
            'dpjp_utama' => 'required|string|max:2000',
            'dokter_pengantar' => 'required|integer|exists:pegawai,pegawai_id',
            'dokter_rawat_1' => 'required|integer|exists:pegawai,pegawai_id',
            'dokter_rawat_2' => 'required|integer|exists:pegawai,pegawai_id',
            'dokter_rawat_3' => 'required|integer|exists:pegawai,pegawai_id',
            'dokter_rawat_4' => 'required|integer|exists:pegawai,pegawai_id',
            'dokter_rawat_5' => 'required|integer|exists:pegawai,pegawai_id',
            'perawat_ruangan' => 'required|string|max:2000',
            'diagnosa_medis' => 'required|string|max:2000',
            'diagnosa_keperawatan' => 'required|string|max:2000',
            'masalah_keperawatan' => 'required|string|max:2000',
            'kebutuhan_khusus' => 'required|string|max:2000',
            'kewaspadaan' => 'required|string|max:2000',
            'nama_terapi_obat' => 'required|string|max:2000',
            'terapi_infus' => 'required|string|max:2000',
            'oksigen' => 'required|string|max:2000',
            'gangguan_indra' => 'required|string|max:2000',
            'prosedur_transfer' => 'required|string|max:2000',
            'peralatan' => 'required|string|max:2000',
            'perlu_alat_bantu' => 'required|string|max:2000',
            'riwayat_alergi' => 'required|string|max:2000',
            'sistolik' => 'required|integer|min:0',
            'diastolik' => 'required|integer|min:0',
            'nadi' => 'required|integer|min:0',
            'pernapasan' => 'required|integer|min:0',
            'suhu' => 'required|numeric|min:0',
            'kesadaran' => ['required', Rule::in(array_column(SelectOption::get('kesadaran'), 'value'))],
            'gcs_eye' => 'required|integer|between:1,4',
            'gcs_motorik' => 'required|integer|between:1,6',
            'gcs_verbal' => 'required|integer|between:1,5',
            'gcs_score' => 'required|integer|between:3,15',
            'dokumen_pendukung' => 'required|array|min:1',
            'hasil_lab' => 'required|string|max:2000',
            'hasil_usg' => 'required|string|max:2000',
            'hasil_radiologi' => 'required|string|max:2000',
            'konsultasi' => 'required|string|max:2000',
            'catatan_transfer' => 'required|string|max:2000',
            'nama_penerima' => 'required|integer|exists:pegawai,pegawai_id',
            'tanggal_penerimaan' => 'required|date',
            'jam_penerimaan' => 'required|date_format:H:i',
        ];

        $validator = Validator::make($request->all(), $rules);
        $validator->after(function ($validator) {
            $asal = request('ruangan_asal');
            $tujuan = request('ruangan_tujuan');
            if ($asal && $tujuan && $tujuan === $asal) {
                $validator->errors()->add('ruangan_tujuan', 'Ruangan tujuan tidak boleh sama dengan ruangan asal.');
            }
        });
        $validator->validate();

        $data = array_merge(
            array_fill_keys(EmrHelper::objekVariabels($form_id), null),
            $validator->validated()
        );

        // Dokumen pendukung multi-pilih disimpan comma-separated dalam SATU baris.
        if (is_array($data['dokumen_pendukung'] ?? null)) {
            $data['dokumen_pendukung'] = implode(', ', array_values(array_filter(
                $data['dokumen_pendukung'],
                fn ($t) => $t !== null && $t !== ''
            )));
        }

        return $data;
    }

    private function kosong(): array
    {
        return [
            'tanggal_transfer' => '', 'jam_transfer' => '',
            'ruangan_asal' => '', 'ruangan_tujuan' => '', 'kategori_transfer' => '',
            'identitas_pasien' => '', 'dpjp_utama' => '',
            'dokter_pengantar' => '', 'perawat_ruangan' => '',
            'dokter_rawat_1' => '', 'dokter_rawat_2' => '', 'dokter_rawat_3' => '',
            'dokter_rawat_4' => '', 'dokter_rawat_5' => '',
            'diagnosa_medis' => '', 'diagnosa_keperawatan' => '', 'masalah_keperawatan' => '',
            'kebutuhan_khusus' => '', 'kewaspadaan' => '',
            'nama_terapi_obat' => '', 'terapi_infus' => '', 'oksigen' => '',
            'gangguan_indra' => '', 'prosedur_transfer' => '', 'peralatan' => '', 'perlu_alat_bantu' => '',
            'riwayat_alergi' => '',
            'sistolik' => '', 'diastolik' => '', 'nadi' => '', 'pernapasan' => '', 'suhu' => '',
            'kesadaran' => '',
            'gcs_eye' => '', 'gcs_motorik' => '', 'gcs_verbal' => '', 'gcs_score' => '',
            'dokumen_pendukung' => [], 'hasil_lab' => '', 'hasil_usg' => '', 'hasil_radiologi' => '',
            'konsultasi' => '', 'catatan_transfer' => '',
            'nama_penerima' => '', 'tanggal_penerimaan' => '', 'jam_penerimaan' => '',
        ];
    }
}
