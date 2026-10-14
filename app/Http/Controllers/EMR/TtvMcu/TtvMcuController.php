<?php

namespace App\Http\Controllers\EMR\TtvMcu;

use App\Helpers\AksesEhr;
use App\Helpers\EmrHelper;
use App\Helpers\GiziHelper;
use App\Http\Controllers\Controller;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Form Catatan Keperawatan / TTV MCU (form 71, slug `ttv_mcu`).
 *
 * Form PEMBUKA episode MCU — diisi perawat sebelum pasien diperiksa dokter
 * spesialis. Semua 14 form spesialis memakai header identifikasi dari form ini.
 *
 * `bmi` dan `kategori_bmi` adalah field TURUNAN — dihitung ulang di
 * `filteredData()` dari `berat_badan` / `tinggi_badan`; nilai browser dibuang.
 * Ketiga `*_detail` gaya hidup wajib diisi bila radio-nya `Ya`
 * (`required_if`).
 */
class TtvMcuController extends Controller
{
    private const SLUG = 'ttv_mcu';

    public function index($registrasi_detail_id, $emr_id = null)
    {
        $registrasi_detail = RegistrasiDetail::with('registrasi.pasien')->findOrFail($registrasi_detail_id);

        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'read'), 403);

        $aksesCrud = AksesEhr::flags((int) $form_id);
        $riwayat = EmrHelper::emrList((int) $form_id, (int) $registrasi_detail->registrasi_id);
        $historyGrouped = EmrHelper::historyKunjunganGrouped($registrasi_detail);

        $pekerjaan = \App\Helpers\SelectOption::get('pekerjaan') ?? [];

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

        return view('moduls.EMR.TtvMcu.index', compact(
            'registrasi_detail', 'riwayat', 'historyGrouped', 'emr_data',
            'formAction', 'isEdit', 'deleteAction', 'isView', 'emr_id', 'aksesCrud',
            'pekerjaan'
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

            return redirect()->back()->with('success', 'TTV MCU berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan TTV MCU: '.$e->getMessage())->withInput();
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

            return redirect()->back()->with('success', 'TTV MCU berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui TTV MCU: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'TTV MCU berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus TTV MCU: '.$e->getMessage());
        }
    }

    private function kosong(): array
    {
        return [
            'tanggal_ttv' => '', 'jam_ttv' => '',
            'keluhan_saat_ini' => '', 'riwayat_penyakit' => '',
            'riwayat_keluarga_ayah' => '', 'riwayat_keluarga_ibu' => '', 'riwayat_keluarga_lain' => '',
            'riwayat_merokok' => '0', 'riwayat_merokok_detail' => '',
            'riwayat_alkohol' => '0', 'riwayat_alkohol_detail' => '',
            'riwayat_olahraga' => '0', 'riwayat_olahraga_detail' => '',
            'riwayat_alergi' => '',
            'jenis_pekerjaan' => '',
            'sistolik' => '', 'diastolik' => '', 'nadi' => '', 'pernapasan' => '', 'suhu' => '',
            'berat_badan' => '', 'tinggi_badan' => '', 'bmi' => '',
            'lingkar_perut' => '', 'kategori_bmi' => '',
        ];
    }

    private function validated(Request $request): array
    {
        $rules = [
            'tanggal_ttv' => 'required|date',
            'jam_ttv' => 'required|date_format:H:i',
            'keluhan_saat_ini' => 'required|string|max:2000',
            'riwayat_penyakit' => 'required|string|max:2000',
            'riwayat_keluarga_ayah' => 'required|string|max:255',
            'riwayat_keluarga_ibu' => 'required|string|max:255',
            'riwayat_keluarga_lain' => 'nullable|string|max:255',
            'riwayat_merokok' => 'required|in:0,1',
            'riwayat_merokok_detail' => 'required_if:riwayat_merokok,1|nullable|string|max:255',
            'riwayat_alkohol' => 'required|in:0,1',
            'riwayat_alkohol_detail' => 'required_if:riwayat_alkohol,1|nullable|string|max:255',
            'riwayat_olahraga' => 'required|in:0,1',
            'riwayat_olahraga_detail' => 'required_if:riwayat_olahraga,1|nullable|string|max:255',
            'riwayat_alergi' => 'required|string|max:1000',
            'jenis_pekerjaan' => 'required|string|max:100',
            'sistolik' => 'required|numeric|between:60,260',
            'diastolik' => 'required|numeric|between:30,160',
            'nadi' => 'required|numeric|between:30,220',
            'pernapasan' => 'required|numeric|between:8,60',
            'suhu' => 'required|numeric|between:33,43',
            'berat_badan' => 'required|numeric|between:2,300',
            'tinggi_badan' => 'required|numeric|between:50,250',
            'lingkar_perut' => 'required|numeric|between:20,200',
        ];

        return array_merge(
            ['bmi' => null, 'kategori_bmi' => null],
            $request->validate($rules)
        );
    }

    private function filteredData(array $data, int $formId): array
    {
        $data = array_intersect_key($data, array_flip(EmrHelper::objekVariabels($formId)));

        // BMI & kategorinya dihitung ulang server dari TB/BB.
        $imt = GiziHelper::imt($data['berat_badan'] ?? null, $data['tinggi_badan'] ?? null);
        $data['bmi'] = $imt;
        $data['kategori_bmi'] = $imt === null ? null : match (true) {
            $imt < 18.5 => 'Kurus',
            $imt < 25 => 'Normal',
            $imt < 30 => 'Overweight',
            default => 'Obesitas',
        };

        // Radio gaya hidup = '0' → detail di-null-kan (bukan unset).
        foreach (['merokok', 'alkohol', 'olahraga'] as $kebiasaan) {
            if (($data['riwayat_'.$kebiasaan] ?? '0') === '0') {
                $data['riwayat_'.$kebiasaan.'_detail'] = null;
            }
        }

        foreach ($data as $key => $value) {
            if (is_string($value) && trim($value) === '') {
                $data[$key] = null;
            }
        }

        return $data;
    }
}
