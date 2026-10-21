<?php

namespace App\Http\Controllers\EMR\IssuesKeperawatan;

use App\Helpers\AksesEhr;
use App\Helpers\EmrHelper;
use App\Http\Controllers\Controller;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Form Issues / Masalah Keperawatan (form 23, slug `issues_keperawatan`).
 *
 * Form "pendahulu" dari care plan (form 22). Satu baris per masalah: masalah
 * keperawatan + faktor berhubungan dengan. Maksimal 10 baris, digenerate dari
 * sufiks angka supaya tiap baris punya variabel sendiri pada `emr_detail`
 * (satu variabel tanpa sufiks akan tertimpa oleh `pluck('value','variabel')`).
 *
 * Baris yatim DIBUANG di `filteredData()`: bila `masalah_keperawatan_N` kosong
 * maka `faktor_berhubungan_N` ikut dibuang, supaya tidak ada `emr_detail`
 * setengah terisi.
 *
 * Catatan master: tabel `diagnosa_keperawatan` (dengan penanda `diagnosa_umum`
 * dan `flag_hubungan`) belum ada di NusaMedika - risiko R3 pada
 * docs/KONSEP_ASUHAN_KEPERAWATAN_LANJUTAN.md - sehingga kedua kolom diisi
 * bebas oleh petugas.
 */
class IssuesKeperawatanController extends Controller
{
    private const SLUG = 'issues_keperawatan';

    /** Jumlah baris masalah maksimum. */
    private const MAKS_BARIS = 10;

    /** Prefix nama variabel per kolom. */
    private const AWAL_MASALAH = 'masalah_keperawatan_';

    private const AWAL_FAKTOR = 'faktor_berhubungan_';

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

        return view('moduls.EMR.IssuesKeperawatan.index', compact(
            'registrasi_detail', 'riwayat', 'historyGrouped', 'emr_data',
            'formAction', 'isEdit', 'deleteAction', 'isView', 'emr_id', 'aksesCrud'
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

            return redirect()->back()->with('success', 'Issues Keperawatan berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan Issues Keperawatan: '.$e->getMessage())->withInput();
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

            return redirect()->back()->with('success', 'Issues Keperawatan berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui Issues Keperawatan: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Issues Keperawatan berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus Issues Keperawatan: '.$e->getMessage());
        }
    }

    /**
     * Nama variabel masalah ke-N.
     */
    public static function variabelMasalah(int $baris): string
    {
        return self::AWAL_MASALAH.$baris;
    }

    /**
     * Nama variabel faktor berhubungan ke-N.
     */
    public static function variabelFaktor(int $baris): string
    {
        return self::AWAL_FAKTOR.$baris;
    }

    /**
     * Nilai kosong untuk form baru.
     */
    private function kosong(): array
    {
        $d = [];

        for ($i = 1; $i <= self::MAKS_BARIS; $i++) {
            $d[self::variabelMasalah($i)] = '';
            $d[self::variabelFaktor($i)] = '';
        }

        return $d;
    }

    private function validated(Request $request): array
    {
        $rules = [
            self::variabelMasalah(1) => 'required|string|max:255',
            self::variabelFaktor(1) => 'nullable|string|max:255',
        ];

        for ($i = 2; $i <= self::MAKS_BARIS; $i++) {
            $rules[self::variabelMasalah($i)] = 'nullable|string|max:255';
            $rules[self::variabelFaktor($i)] = 'nullable|string|max:255';
        }

        // Semua kolom nullable pada baris 2-10: default null wajib agar
        // `validated()` tidak melempar "Undefined array key".
        $opsional = [];
        for ($i = 1; $i <= self::MAKS_BARIS; $i++) {
            $opsional[self::variabelMasalah($i)] = null;
            $opsional[self::variabelFaktor($i)] = null;
        }

        return array_merge($opsional, $request->validate($rules));
    }

    /**
     * Hanya field terpetakan yang disimpan. Baris yatim (faktor tanpa masalah)
     * dibuang supaya tidak ada `emr_detail` setengah terisi.
     */
    private function filteredData(array $data, int $formId): array
    {
        $data = array_intersect_key($data, array_flip(EmrHelper::objekVariabels($formId)));

        for ($i = 1; $i <= self::MAKS_BARIS; $i++) {
            $masalah = self::variabelMasalah($i);
            $faktor = self::variabelFaktor($i);

            // Normalisasi string kosong menjadi null supaya tidak tersimpan
            // sebagai baris kosong.
            if (trim((string) ($data[$masalah] ?? '')) === '') {
                $data[$masalah] = null;
                $data[$faktor] = null;

                continue;
            }

            if (trim((string) ($data[$faktor] ?? '')) === '') {
                $data[$faktor] = null;
            }
        }

        return $data;
    }
}
