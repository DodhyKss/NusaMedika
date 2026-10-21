<?php

namespace App\Http\Controllers\EMR\Output;

use App\Helpers\AksesEhr;
use App\Helpers\CairanHelper;
use App\Helpers\EmrHelper;
use App\Helpers\SelectOption;
use App\Http\Controllers\Controller;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Form Output Cairan (form 25, slug `output`).
 *
 * Mencatat cairan keluar: urine, drain, NGT, WSD, output lainnya, plus IWL
 * (Insensible Water Loss) yang dihitung otomatis dari berat badan & suhu
 * terakhir yang tercatat di EMR.
 *
 * `iwl` dan `total_output` SELALU dihitung ulang di server; nilai dari browser
 * dibuang.
 */
class OutputController extends Controller
{
    private const SLUG = 'output';

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

        // IWL memakai berat badan & suhu TERAKHIR yang tercatat di EMR; keduanya
        // bukan field form 25, jadi hanya ditampilkan sebagai informasi.
        $regDetId = (int) $registrasi_detail->registrasi_detail_id;
        $beratBadan = CairanHelper::nilaiTerakhir($regDetId, 'berat_badan');
        $suhu = CairanHelper::nilaiTerakhir($regDetId, 'suhu');

        return view('moduls.EMR.Output.index', compact(
            'registrasi_detail', 'riwayat', 'historyGrouped', 'emr_data',
            'formAction', 'isEdit', 'deleteAction', 'isView', 'emr_id', 'aksesCrud',
            'beratBadan', 'suhu'
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
            $data = $this->filteredData($data, (int) $form_id, (int) $registrasi_detail_id);

            DB::transaction(fn () => EmrHelper::insert((int) $form_id, $data, (int) $registrasi_detail_id));

            return redirect()->back()->with('success', 'Output Cairan berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan Output Cairan: '.$e->getMessage())->withInput();
        }
    }

    public function update(Request $request, $registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'update'), 403);

        $data = $this->validated($request);

        try {
            $data = $this->filteredData($data, (int) $form_id, (int) $registrasi_detail_id);

            DB::transaction(fn () => EmrHelper::update((int) $emr_id, (int) $form_id, $data));

            return redirect()->back()->with('success', 'Output Cairan berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui Output Cairan: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Output Cairan berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus Output Cairan: '.$e->getMessage());
        }
    }

    /**
     * Nilai kosong untuk form baru (12 variabel form 25).
     */
    private function kosong(): array
    {
        return [
            'urine' => '',
            'drain' => '',
            'ngt' => '',
            'wsd' => '',
            'iwl' => '',
            'output_lainnya' => '',
            'total_output' => '',
            'frekuensi_bab' => '',
            'karakter_bab' => '',
            'frekuensi_bak' => '',
            'karakter_bak' => '',
            'catatan' => '',
        ];
    }

    private function validated(Request $request): array
    {
        $opsional = [
            'drain' => null,
            'ngt' => null,
            'wsd' => null,
            'output_lainnya' => null,
            'frekuensi_bab' => null,
            'karakter_bab' => null,
            'frekuensi_bak' => null,
            'karakter_bak' => null,
            'catatan' => null,
        ];

        return array_merge($opsional, $request->validate([
            'urine' => 'required|numeric|min:0',
            'drain' => 'nullable|numeric|min:0',
            'ngt' => 'nullable|numeric|min:0',
            'wsd' => 'nullable|numeric|min:0',
            'output_lainnya' => 'nullable|numeric|min:0',

            'frekuensi_bab' => 'nullable|integer|min:0|max:20',
            'karakter_bab' => ['nullable', 'array'],
            'karakter_bab.*' => ['nullable', 'string', 'max:50'],

            'frekuensi_bak' => 'nullable|integer|min:0|max:30',
            'karakter_bak' => ['nullable', 'array'],
            'karakter_bak.*' => ['nullable', 'string', 'max:50'],

            'catatan' => 'nullable|string|max:1000',
        ]));
    }

    /**
     * IWL & total output dihitung ulang di server; nilai browser dibuang.
     * Nilai `karakter_bab` / `karakter_bak` yang tidak ada di daftar opsi
     * SelectOption ikut dibuang supaya yang tersimpan selalu nilai valid.
     */
    private function filteredData(array $data, int $formId, int $registrasiDetailId): array
    {
        $data = array_intersect_key($data, array_flip(EmrHelper::objekVariabels($formId)));

        $data['karakter_bab'] = $this->saringKarakter($data['karakter_bab'] ?? null, 'karakter_bab');
        $data['karakter_bak'] = $this->saringKarakter($data['karakter_bak'] ?? null, 'karakter_bak');

        $beratBadan = CairanHelper::nilaiTerakhir($registrasiDetailId, 'berat_badan');
        $suhu = CairanHelper::nilaiTerakhir($registrasiDetailId, 'suhu');

        $data['iwl'] = CairanHelper::hitungIWL($beratBadan ?? 0, $suhu ?? 0);

        $kunci = CairanHelper::OUTPUT_25;
        $kunci[] = 'iwl';
        $data['total_output'] = CairanHelper::total($data, $kunci);

        return $data;
    }

    /**
     * Ambil hanya nilai yang terdaftar pada SelectOption.
     */
    private function saringKarakter($nilai, string $key): array
    {
        if (! is_array($nilai)) {
            return [];
        }

        $boleh = array_column(SelectOption::get($key), 'value');

        return array_values(array_filter(
            array_map('strval', $nilai),
            fn ($v) => in_array($v, $boleh, true)
        ));
    }
}
