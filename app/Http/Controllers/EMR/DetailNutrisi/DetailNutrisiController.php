<?php

namespace App\Http\Controllers\EMR\DetailNutrisi;

use App\Helpers\AksesEhr;
use App\Helpers\EmrHelper;
use App\Helpers\GiziHelper;
use App\Http\Controllers\Controller;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Form Detail Nutrisi (form 40, slug `detail_nutrisi`).
 *
 * Skor skrining gizi. Instrumen dipilih manual (override) tetapi punya default
 * berdasarkan usia pasien:
 * - 0-18 tahun → MST-Anak (skor 0-5)
 * - 19-59 tahun → MNA-SF  (skor 0-14, `>= 2` → perlu asesmen lanjut)
 * - >= 60 tahun → MST     (skor 0-14)
 * - SGA/NRS              (5 parameter, skor 1-5, total 0-25)
 *
 * Field TURUNAN yang SELALU dihitung ulang di server, nilai browser dibuang:
 * - `skor`       → `GiziHelper::hitung($instrumen, $jawaban)`
 * - `kesimpulan` → ikut dari helper yang sama
 *
 * Aturan wajib (docs/KONSEP_ASESMEN_GIZI.md §4.7):
 * 1. Skor & kesimpulan dari browser SELALU dibuang.
 * 2. Bila ada item instrumen aktif yang kosong → skor `null`, bukan "Normal".
 * 3. `instrumen` yang tidak dikirim → fallback `instrumenDefault($usia)`.
 * 4. Item di luar instrumen aktif dibuang dari payload (di-set null, bukan
 *    `unset()` — supaya baris `emr_detail` tetap ada dan mapping lengkap).
 */
class DetailNutrisiController extends Controller
{
    private const SLUG = 'detail_nutrisi';

    /** Label untuk tiap instrumen (dipakai di view & judul panel). */
    public const LABEL_INSTRUMEN = [
        'SGA' => 'SGA / NRS',
        'MNA-SF' => 'MNA-SF',
        'MST' => 'MST',
        'MST-Anak' => 'MST-Anak',
    ];

    /** Keterangan ambang skor per instrumen (dipakai di subjudul panel). */
    public const KETERANGAN_INSTRUMEN = [
        'SGA' => '5 parameter, skor 1-5 per parameter. Total 0-25.',
        'MNA-SF' => '4 butir, skor 0-14. Skor >= 2 berarti perlu asesmen lanjut oleh ahli gizi.',
        'MST' => '6 butir, skor 0-14. 12-14 Normal · 8-11 Berisiko Malnutrisi · 0-7 Malnutrisi.',
        'MST-Anak' => '4 butir, skor 0-5. 0 Risiko Rendah · 1-3 Risiko Sedang · 4-5 Risiko Tinggi.',
    ];

    public function index($registrasi_detail_id, $emr_id = null)
    {
        $registrasi_detail = RegistrasiDetail::with('registrasi.pasien')->findOrFail($registrasi_detail_id);

        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'read'), 403);

        $aksesCrud = AksesEhr::flags((int) $form_id);
        $riwayat = EmrHelper::emrList((int) $form_id, (int) $registrasi_detail->registrasi_id);
        $historyGrouped = EmrHelper::historyKunjunganGrouped($registrasi_detail);

        $pasien = optional($registrasi_detail->registrasi)->pasien;
        $usia = GiziHelper::usia(
            optional($pasien)->tgl_lahir ? (string) $pasien->tgl_lahir : null,
            $registrasi_detail->tgl_daftar ? (string) $registrasi_detail->tgl_daftar : null
        );
        $instrumenDefault = GiziHelper::instrumenDefault($usia);

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

        return view('moduls.EMR.DetailNutrisi.index', compact(
            'registrasi_detail', 'riwayat', 'historyGrouped', 'emr_data',
            'formAction', 'isEdit', 'deleteAction', 'isView', 'emr_id', 'aksesCrud',
            'usia', 'instrumenDefault'
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

            return redirect()->back()->with('success', 'Detail Nutrisi berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan Detail Nutrisi: '.$e->getMessage())->withInput();
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

            return redirect()->back()->with('success', 'Detail Nutrisi berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui Detail Nutrisi: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Detail Nutrisi berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus Detail Nutrisi: '.$e->getMessage());
        }
    }

    /**
     * Butir SGA / NRS lengkap dengan label parameternya.
     */
    public static function labelSga(): array
    {
        return [
            'sga_1' => 'Perubahan Berat Badan 6 bulan',
            'sga_2' => 'Asupan Makan',
            'sga_3' => 'Gejala Saluran Cerna',
            'sga_4' => 'Kapasitas Fungsional',
            'sga_5' => 'Ko-morbiditas',
        ];
    }

    /**
     * Butir MNA-SF lengkap dengan label parameternya.
     */
    public static function labelMna(): array
    {
        return [
            'mna_1' => 'A. Penurunan Nafsu Makan',
            'mna_2' => 'B. Derajat Penurunan Berat Badan',
            'mna_3' => 'C. Gangguan Psikologis',
            'mna_4' => 'D. Diagnosis Khusus',
        ];
    }

    /**
     * Butir MST lengkap dengan label parameternya.
     */
    public static function labelMst(): array
    {
        return [
            'mst_a' => 'Penurunan Berat Badan',
            'mst_b' => 'Gangguan Pencernaan',
            'mst_c' => 'Kemampuan Fungsional',
            'mst_d' => 'Penyakit Signaling Kronis',
            'mst_e' => 'Stressor Psikologis',
            'mst_f1' => 'Body Mass Index',
            'mst_f2' => 'Lingkar Betis (alternatif bila IMT tidak diukur)',
        ];
    }

    /**
     * Butir MST-Anak lengkap dengan label parameternya.
     */
    public static function labelMstak(): array
    {
        return [
            'mstak_1' => 'Apakah pasien tampak kurus?',
            'mstak_2' => 'Penurunan berat badan 1 bulan terakhir (bayi < 1 th: BB tidak naik 3 bulan)',
            'mstak_3' => 'Diare > 5x/hari & muntah > 3x/hari seminggu, atau asupan menurun >= 1 minggu',
            'mstak_4' => 'Ada penyakit/keadaan berisiko menyebabkan malnutrisi',
        ];
    }

    /**
     * Nilai kosong untuk form baru.
     */
    private function kosong(): array
    {
        $d = [
            'instrumen' => GiziHelper::instrumenDefault(null),
            'tanggal_evaluasi' => '',
            'ahli_gizi_id' => '',
        ];

        foreach (GiziHelper::INSTRUMEN as $instrumen) {
            foreach (GiziHelper::variabelInstrumen($instrumen) as $variabel) {
                $d[$variabel] = null;
            }
        }

        $d['skor'] = null;
        $d['kesimpulan'] = null;

        return $d;
    }

    /**
     * Aturan validasi (docs/KONSEP_ASESMEN_GIZI.md §4.11).
     *
     * SELURUH item instrumen `nullable` — bukan `required`. Alasannya: item
     * yang wajib bergantung instrumen aktif, sedangkan `required` akan menolak
     * form yang sedang instrumentation-nya berbeda. Aturan "wajib" ditangani
     * oleh `GiziHelper::hitung()` yang mengembalikan skor null bila ada butir
     * instrumen aktif yang kosong — jadi parsial tidak pernah menghasilkan
     * "Normal" yang menyesatkan.
     */
    private function validated(Request $request): array
    {
        $rules = [
            'instrumen' => 'required|in:SGA,MNA-SF,MST,MST-Anak',
            'tanggal_evaluasi' => 'required|date',
            'ahli_gizi_id' => 'nullable|integer|exists:pegawai,pegawai_id',
        ];

        // --- SGA / NRS (5 parameter, skor 1-5) ---
        $rules['sga_1'] = 'nullable|integer|between:1,5';
        $rules['sga_2'] = 'nullable|integer|between:1,5';
        $rules['sga_3'] = 'nullable|integer|between:1,5';
        $rules['sga_4'] = 'nullable|integer|between:1,5';
        $rules['sga_5'] = 'nullable|integer|between:2,5';

        // --- MNA-SF (A-D) ---
        $rules['mna_1'] = 'nullable|integer|in:0,2';
        $rules['mna_2'] = 'nullable|integer|between:1,3';
        $rules['mna_3'] = 'nullable|integer|in:0,1';
        $rules['mna_4'] = 'nullable|integer|in:0,3';

        // --- MST (A-E, F1, F2) ---
        $rules['mst_a'] = 'nullable|integer|between:0,2';
        $rules['mst_b'] = 'nullable|integer|between:0,3';
        $rules['mst_c'] = 'nullable|integer|between:0,2';
        $rules['mst_d'] = 'nullable|integer|in:0,2';
        $rules['mst_e'] = 'nullable|integer|between:0,2';
        $rules['mst_f1'] = 'nullable|integer|between:0,3';
        $rules['mst_f2'] = 'nullable|integer|between:0,1';

        // --- MST-Anak (4 item) ---
        $rules['mstak_1'] = 'nullable|integer|in:0,1';
        $rules['mstak_2'] = 'nullable|integer|in:0,1';
        $rules['mstak_3'] = 'nullable|integer|in:0,1';
        $rules['mstak_4'] = 'nullable|integer|in:0,1';

        // Diagnosis khusus (checkbox 0/1): MNA 15 butir + MST-Anak 16 butir.
        // Ditulis eksplisit (bukan loop) supaya setiap aturan mudah dibaca
        // dan terbaca harness E2E yang men-parsing source controller.
        $mnaDiag = [
            'mna_diag_1', 'mna_diag_2', 'mna_diag_3', 'mna_diag_4', 'mna_diag_5',
            'mna_diag_6', 'mna_diag_7', 'mna_diag_8', 'mna_diag_9', 'mna_diag_10',
            'mna_diag_11', 'mna_diag_12', 'mna_diag_13', 'mna_diag_14', 'mna_diag_15',
        ];
        $rules['mna_diag_1'] = 'nullable|in:0,1';
        $rules['mna_diag_2'] = 'nullable|in:0,1';
        $rules['mna_diag_3'] = 'nullable|in:0,1';
        $rules['mna_diag_4'] = 'nullable|in:0,1';
        $rules['mna_diag_5'] = 'nullable|in:0,1';
        $rules['mna_diag_6'] = 'nullable|in:0,1';
        $rules['mna_diag_7'] = 'nullable|in:0,1';
        $rules['mna_diag_8'] = 'nullable|in:0,1';
        $rules['mna_diag_9'] = 'nullable|in:0,1';
        $rules['mna_diag_10'] = 'nullable|in:0,1';
        $rules['mna_diag_11'] = 'nullable|in:0,1';
        $rules['mna_diag_12'] = 'nullable|in:0,1';
        $rules['mna_diag_13'] = 'nullable|in:0,1';
        $rules['mna_diag_14'] = 'nullable|in:0,1';
        $rules['mna_diag_15'] = 'nullable|in:0,1';
        $rules['mstak_diag_1'] = 'nullable|in:0,1';
        $rules['mstak_diag_2'] = 'nullable|in:0,1';
        $rules['mstak_diag_3'] = 'nullable|in:0,1';
        $rules['mstak_diag_4'] = 'nullable|in:0,1';
        $rules['mstak_diag_5'] = 'nullable|in:0,1';
        $rules['mstak_diag_6'] = 'nullable|in:0,1';
        $rules['mstak_diag_7'] = 'nullable|in:0,1';
        $rules['mstak_diag_8'] = 'nullable|in:0,1';
        $rules['mstak_diag_9'] = 'nullable|in:0,1';
        $rules['mstak_diag_10'] = 'nullable|in:0,1';
        $rules['mstak_diag_11'] = 'nullable|in:0,1';
        $rules['mstak_diag_12'] = 'nullable|in:0,1';
        $rules['mstak_diag_13'] = 'nullable|in:0,1';
        $rules['mstak_diag_14'] = 'nullable|in:0,1';
        $rules['mstak_diag_15'] = 'nullable|in:0,1';
        $rules['mstak_diag_16'] = 'nullable|in:0,1';

        $mstakDiag = [];
        for ($i = 1; $i <= 16; $i++) {
            $mstakDiag[] = 'mstak_diag_'.$i;
        }

        // Default null untuk semua key opsional supaya `validated()` tidak
        // melempar "Undefined array key" saat browser tidak mengirimnya.
        $opsional = ['ahli_gizi_id' => null];
        foreach ($mnaDiag as $variabel) {
            $opsional[$variabel] = null;
        }
        foreach ($mstakDiag as $variabel) {
            $opsional[$variabel] = null;
        }
        foreach (GiziHelper::INSTRUMEN as $instrumen) {
            foreach (GiziHelper::variabelInstrumen($instrumen) as $variabel) {
                $opsional[$variabel] = null;
            }
        }

        return array_merge($opsional, $request->validate($rules));
    }

    /**
     * Hanya field terpetakan yang disimpan, dengan skor/kesimpulan dihitung ulang.
     */
    private function filteredData(array $data, int $formId): array
    {
        $data = array_intersect_key($data, array_flip(EmrHelper::objekVariabels($formId)));

        $instrumen = $data['instrumen'] ?? null;
        if (! in_array($instrumen, GiziHelper::INSTRUMEN, true)) {
            $instrumen = GiziHelper::instrumenDefault(null);
            $data['instrumen'] = $instrumen;
        }

        // Kumpulkan seluruh variabel item milik instrumen aktif.
        $variabelAktif = GiziHelper::variabelInstrumen($instrumen);

        // Item di luar instrumen aktif → null (bukan unset, supaya baris
        // emr_detail tetap ada dan kelengkapan mapping terverifikasi).
        foreach (GiziHelper::INSTRUMEN as $instrumenLain) {
            if ($instrumenLain === $instrumen) {
                continue;
            }
            foreach (GiziHelper::variabelInstrumen($instrumenLain) as $variabel) {
                if (in_array($variabel, $variabelAktif, true)) {
                    continue;
                }
                $data[$variabel] = null;
            }
        }

        // Checkbox diagnosis khusus → 1/null (bukan string "on").
        foreach ($variabelAktif as $variabel) {
            if (str_contains($variabel, '_diag_')) {
                $data[$variabel] = ! empty($data[$variabel]) ? 1 : null;
            }
        }

        // Skor & kesimpulan SELALU dihitung ulang dari jawaban mentah —
        // nilai yang dikirim browser dibuang total.
        $hasil = GiziHelper::hitung($instrumen, $data);
        $data['skor'] = $hasil['skor'];
        $data['kesimpulan'] = $hasil['kesimpulan'];

        return $data;
    }
}
