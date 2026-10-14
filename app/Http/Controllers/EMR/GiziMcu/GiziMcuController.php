<?php

namespace App\Http\Controllers\EMR\GiziMcu;

use App\Helpers\AksesEhr;
use App\Helpers\EmrHelper;
use App\Helpers\GiziHelper;
use App\Http\Controllers\Controller;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Form Gizi MCU (form 82, slug `gizi_mcu`).
 *
 * Penilaian status gizi oleh dokter SpGizi. `bmi` dan `kategori_bmi` adalah
 * field TURUNAN — dihitung ulang di `filteredData()` dari `berat_badan` /
 * `tinggi_badan`; nilai browser dibuang (PANDUAN §2.4).
 */
class GiziMcuController extends Controller
{
    private const SLUG = 'gizi_mcu';

    /** Opsi status gizi (penilaian klinis dokter, bukan turunan BMI). */
    private const STATUS_GIZI = ['Kurus', 'Normal', 'Overweight', 'Obesitas', 'Malnutrisi'];

    public function index($registrasi_detail_id, $emr_id = null)
    {
        $registrasi_detail = RegistrasiDetail::with('registrasi.pasien')->findOrFail($registrasi_detail_id);

        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'read'), 403);

        $aksesCrud = AksesEhr::flags((int) $form_id);
        $riwayat = EmrHelper::emrList((int) $form_id, (int) $registrasi_detail->registrasi_id);
        $historyGrouped = EmrHelper::historyKunjunganGrouped($registrasi_detail);

        // Prefill TB/BB dari form 71 (TTV MCU) — episode MCU yang sama.
        $ttv = EmrHelper::latestValuesByVariabel(
            (int) EmrHelper::formIdBySlug('ttv_mcu'),
            (int) $registrasi_detail_id,
            ['tinggi_badan', 'berat_badan']
        );

        if (empty($emr_id) && ! ($aksesCrud['create'] ?? false) && $riwayat->isNotEmpty()) {
            return redirect()->route('emr.dynamic.index', [
                'form_name' => self::SLUG,
                'registrasi_detail_id' => $registrasi_detail_id,
                'emr_id' => $riwayat->first()->emr_id,
                'action' => 'view',
            ]);
        }

        if (empty($emr_id)) {
            $emr_data = EmrHelper::wrapData(array_merge($this->kosong(), array_filter($ttv)));
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

        return view('moduls.EMR.GiziMcu.index', compact(
            'registrasi_detail', 'riwayat', 'historyGrouped', 'emr_data',
            'formAction', 'isEdit', 'deleteAction', 'isView', 'emr_id', 'aksesCrud'
        ) + ['statusGiziOpsi' => self::STATUS_GIZI]);
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

            return redirect()->back()->with('success', 'Gizi MCU berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan Gizi MCU: '.$e->getMessage())->withInput();
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

            return redirect()->back()->with('success', 'Gizi MCU berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui Gizi MCU: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Gizi MCU berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus Gizi MCU: '.$e->getMessage());
        }
    }

    private function kosong(): array
    {
        return [
            'tinggi_badan' => '', 'berat_badan' => '',
            'bmi' => '', 'kategori_bmi' => '',
            'lingkar_lengan_atas' => '', 'status_gizi' => '',
            'pola_diet' => '', 'risiko_gizi' => '',
            'diagnosa' => '', 'rekomendasi' => '',
        ];
    }

    private function validated(Request $request): array
    {
        $rules = [
            'tinggi_badan' => 'required|numeric|between:50,250',
            'berat_badan' => 'required|numeric|between:2,300',
            'lingkar_lengan_atas' => 'required|numeric|between:10,60',
            'status_gizi' => 'required|in:Kurus,Normal,Overweight,Obesitas,Malnutrisi',
            'pola_diet' => 'required|string|max:1000',
            'risiko_gizi' => 'required|string|max:1000',
            'diagnosa' => 'required|string|max:2000',
            'rekomendasi' => 'required|string|max:2000',
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
        // Label kategori mengikuti konsep MCU (Kurus/Normal/Overweight/Obesitas),
        // bukan label klinis panjang GiziHelper::statusGizi.
        $imt = GiziHelper::imt($data['berat_badan'] ?? null, $data['tinggi_badan'] ?? null);
        $data['bmi'] = $imt;
        $data['kategori_bmi'] = $imt === null ? null : match (true) {
            $imt < 18.5 => 'Kurus',
            $imt < 25 => 'Normal',
            $imt < 30 => 'Overweight',
            default => 'Obesitas',
        };

        foreach ($data as $key => $value) {
            if (is_string($value) && trim($value) === '') {
                $data[$key] = null;
            }
        }

        return $data;
    }
}
