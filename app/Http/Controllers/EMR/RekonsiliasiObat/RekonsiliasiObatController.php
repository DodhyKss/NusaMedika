<?php

namespace App\Http\Controllers\EMR\RekonsiliasiObat;

use App\Helpers\AksesEhr;
use App\Helpers\EmrHelper;
use App\Helpers\FarmasiHelper;
use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Form Rekonsiliasi Obat (form 46, slug `rekonsiliasi_obat`).
 *
 * Kewajiban Joint Commission International. Legacy memodelkannya sebagai 3 tab:
 *   1. `igd`   — Riwayat obat sebelum admisi
 *   2. `ri`    — Obat yang sedang diresepkan selama perawatan
 *   3. `pulang` — Obat yang dibawa pulang
 *
 * Tiga grup baris x 10 baris, setiap variabel me-REUSE objek yang sudah ada
 * (69 Obat, 71 S1, 72 S2, 73 Aturan Pakai, 74 Rute, 77 Keterangan, 404 Satuan,
 * 405 Dokter, 406 Tanggal Resep, 403 Lanjut pada Perawatan).
 *
 * **Format `*_nama_obat_N`** (docs/KONSEP_FARMAKASI.md §4.4):
 * - angka             → `barang_id` (barang master, wajib ada di `barang`)
 * - `R:{teks racikan}` → racikan, tidak punya `barang_id`
 * Kedua bentuk disimpan di field yang sama lalu di-resolve di view.
 *
 * **Baris dianggap ada bila `*_nama_obat_N` terisi.** Baris kosong dibuang
 * SELURUH variabel grupnya di `filteredData()` supaya tidak ada baris yatim.
 */
class RekonsiliasiObatController extends Controller
{
    private const SLUG = 'rekonsiliasi_obat';

    /** Jumlah baris per grup. */
    public const MAKS_BARIS = 10;

    /** Baris yang dirender lebih dulu di view. */
    public const BARIS_AWAL = 3;

    /** Tab aktif yang tersedia. */
    public const OPSI_TAB = [
        'igd' => 'Riwayat Obat (Admisi IGD)',
        'ri' => 'Obat Diresepkan Saat Ini (Ruang Perawatan)',
        'pulang' => 'Obat Saat Pulang',
    ];

    /** Prefix grup + kolomnya (urutan = urutan kolom di tabel). */
    public const GRUP = [
        'riwayat' => ['nama_obat', 'rute', 'dosis', 'freq_1', 'freq_2', 'lanjut'],
        'resep' => ['nama_obat', 'rute', 'dosis', 'satuan', 'freq_1', 'freq_2', 'dokter', 'tgl_mulai', 'keterangan'],
        'pulang' => ['nama_obat', 'rute', 'dosis', 'satuan', 'freq_1', 'freq_2', 'dokter', 'tgl_mulai', 'keterangan'],
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

        $barangMap = [];
        foreach (Barang::aktif()->get() as $b) {
            $barangMap[$b->barang_id] = $b->nama_barang;
        }

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

        return view('moduls.EMR.RekonsiliasiObat.index', compact(
            'registrasi_detail', 'riwayat', 'historyGrouped', 'emr_data',
            'formAction', 'isEdit', 'deleteAction', 'isView', 'emr_id', 'aksesCrud',
            'barangMap'
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

            return redirect()->back()->with('success', 'Rekonsiliasi Obat berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan Rekonsiliasi Obat: '.$e->getMessage())->withInput();
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

            return redirect()->back()->with('success', 'Rekonsiliasi Obat berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui Rekonsiliasi Obat: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Rekonsiliasi Obat berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus Rekonsiliasi Obat: '.$e->getMessage());
        }
    }

    /**
     * Nama variabel baris: `riwayat_dosis_3`, `resep_dokter_2`, dst.
     */
    public static function v(string $grup, string $kolom, int $baris): string
    {
        return $grup.'_'.$kolom.'_'.$baris;
    }

    private function kosong(): array
    {
        $d = [
            'rek_tab_aktif' => 'igd',
            'catatan_rekonsiliasi' => '',
            'pulang_edukasi_aturan_minum' => null,
            'pulang_edukasi_dosis' => null,
            'pulang_edukasi_cara_penggunaan' => null,
            'pulang_edukasi_penyimpanan' => null,
        ];

        foreach (self::GRUP as $grup => $kolom) {
            for ($i = 1; $i <= self::MAKS_BARIS; $i++) {
                foreach ($kolom as $k) {
                    $d[self::v($grup, $k, $i)] = null;
                }
            }
        }

        return $d;
    }

    private function validated(Request $request): array
    {
        $rules = [
            'rek_tab_aktif' => 'required|in:igd,ri,pulang',
            'catatan_rekonsiliasi' => 'nullable|string|max:2000',
            'pulang_edukasi_aturan_minum' => 'nullable|in:on,off',
            'pulang_edukasi_dosis' => 'nullable|in:on,off',
            'pulang_edukasi_cara_penggunaan' => 'nullable|in:on,off',
            'pulang_edukasi_penyimpanan' => 'nullable|in:on,off',
        ];

        // Aturan baris ditulis dalam loop; dibaca harness E2E lewat
        // ekspansi loop (expandRuleLoops() di tools/lib_pay.php).
        for ($b = 1; $b <= self::MAKS_BARIS; $b++) {
            $rules['riwayat_nama_obat_'.$b] = 'nullable|string|max:255';
            $rules['riwayat_rute_'.$b] = 'nullable|string|max:50';
            $rules['riwayat_dosis_'.$b] = 'nullable|string|max:100';
            $rules['riwayat_freq_1_'.$b] = 'nullable|string|max:10';
            $rules['riwayat_freq_2_'.$b] = 'nullable|string|max:10';
            $rules['riwayat_lanjut_'.$b] = 'nullable|in:Yes,No';

            $rules['resep_nama_obat_'.$b] = 'nullable|string|max:255';
            $rules['resep_rute_'.$b] = 'nullable|string|max:50';
            $rules['resep_dosis_'.$b] = 'nullable|string|max:100';
            $rules['resep_satuan_'.$b] = 'nullable|string|max:30';
            $rules['resep_freq_1_'.$b] = 'nullable|string|max:10';
            $rules['resep_freq_2_'.$b] = 'nullable|string|max:10';
            $rules['resep_dokter_'.$b] = 'nullable|integer|exists:pegawai,pegawai_id';
            $rules['resep_tgl_mulai_'.$b] = 'nullable|date';
            $rules['resep_keterangan_'.$b] = 'nullable|string|max:255';

            $rules['pulang_nama_obat_'.$b] = 'nullable|string|max:255';
            $rules['pulang_rute_'.$b] = 'nullable|string|max:50';
            $rules['pulang_dosis_'.$b] = 'nullable|string|max:100';
            $rules['pulang_satuan_'.$b] = 'nullable|string|max:30';
            $rules['pulang_freq_1_'.$b] = 'nullable|string|max:10';
            $rules['pulang_freq_2_'.$b] = 'nullable|string|max:10';
            $rules['pulang_dokter_'.$b] = 'nullable|integer|exists:pegawai,pegawai_id';
            $rules['pulang_tgl_mulai_'.$b] = 'nullable|date';
            $rules['pulang_keterangan_'.$b] = 'nullable|string|max:255';
        }

        $opsional = [
            'catatan_rekonsiliasi' => null,
            'pulang_edukasi_aturan_minum' => 'off',
            'pulang_edukasi_dosis' => 'off',
            'pulang_edukasi_cara_penggunaan' => 'off',
            'pulang_edukasi_penyimpanan' => 'off',
        ];
        foreach (self::GRUP as $grup => $kolom) {
            for ($i = 1; $i <= self::MAKS_BARIS; $i++) {
                foreach ($kolom as $k) {
                    $opsional[self::v($grup, $k, $i)] = null;
                }
            }
        }

        return array_merge($opsional, $request->validate($rules));
    }

    /**
     * Buang baris kosong per grup, validasi bentuk `nama_obat`, dan set null
     * untuk field yang tidak relevan.
     */
    private function filteredData(array $data, int $formId): array
    {
        $data = array_intersect_key($data, array_flip(EmrHelper::objekVariabels($formId)));

        $barangAktif = Barang::aktif()->pluck('barang_id')->map(fn ($x) => (int) $x)->all();

        foreach (self::GRUP as $grup => $kolom) {
            for ($i = 1; $i <= self::MAKS_BARIS; $i++) {
                $kunciNama = self::v($grup, 'nama_obat', $i);
                $nama = trim((string) ($data[$kunciNama] ?? ''));

                // Bentuk `id::nama` dipakai hanya bila UI menyimpan nama;
                // server menyimpan ID saja supaya nama master tetap menjadi satu-satunya sumber.
                if (str_contains($nama, '::')) {
                    $nama = trim(explode('::', $nama, 2)[0]);
                }

                if ($nama === '') {
                    foreach ($kolom as $k) {
                        $data[self::v($grup, $k, $i)] = null;
                    }

                    continue;
                }

                // Bentuk racikan `R:{teks}` tetap disimpan apa adanya.
                if (! str_starts_with($nama, 'R:') && ! in_array((int) $nama, $barangAktif, true)) {
                    $data[$kunciNama] = null;
                } else {
                    $data[$kunciNama] = $nama;
                }
            }
        }

        // Checkbox edukasi: normalisasi on/off.
        foreach (['pulang_edukasi_aturan_minum', 'pulang_edukasi_dosis', 'pulang_edukasi_cara_penggunaan', 'pulang_edukasi_penyimpanan'] as $variabel) {
            $data[$variabel] = FarmasiHelper::tercentang($data[$variabel] ?? null) ? 'on' : 'off';
        }

        foreach ($data as $key => $value) {
            if (is_string($value) && trim($value) === '') {
                $data[$key] = null;
            }
        }

        return $data;
    }
}
