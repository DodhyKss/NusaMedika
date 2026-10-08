<?php

namespace App\Http\Controllers\EMR\AssesmenAwalMedisRawatJalan;

use App\Helpers\AksesEhr;
use App\Helpers\EmrHelper;
use App\Helpers\EwsHelper;
use App\Http\Controllers\Controller;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Form Assesmen Awal Medis Rawat Jalan (slug `assesmen_awal_medis_rawat_jalan`).
 *
 * Penilaian awal dokter/paramedis saat pasien datang di rawat jalan:
 * keluhan utama, anamnesis, riwayat penyakit dahulu, tanda vital, EWS, dan
 * diagnosa kerja.
 *
 * Field TURUNAN — `bmi`, `total_ews`, `kategori_ews` — DIHITUNG ULANG di server
 * dari input yang sama (nilai dari browser diabaikan), sehingga angkanya tidak
 * bisa dimanipulasi dari sisi klien. Perhitungan EWS memakai
 * `App\Helpers\EwsHelper` dan memakai 7 parameter tanda vital yang juga tampil
 * di form ini, jadi petugas tidak perlu mengisi angka yang sama dua kali.
 *
 * Opsi nilai statis (kesadaran, tujuan kunjungan, oksigen) memakai
 * SelectOption, bukan <option> hardcode di blade.
 */
class AssesmenAwalMedisRawatJalanController extends Controller
{
    private const SLUG = 'assesmen_awal_medis_rawat_jalan';

    /**
     * Nilai default mode "buat baru" supaya blade tidak pernah
     * "Undefined array key" saat form dibuka kosong.
     */
    private const KOSONG = [
        'keluhan_utama' => '',
        'tanggal_assesmen' => '',
        'waktu_assesmen' => '',
        'dokter_pemeriksa_id' => '',
        'td_sistolik' => '',
        'td_diastolik' => '',
        'nadi' => '',
        'suhu' => '',
        'pernapasan' => '',
        'saturasi' => '',
        'berat_badan' => '',
        'tinggi_badan' => '',
        'bmi' => '',
        'kesadaran' => '',
        'nyeri' => '',
        'skor_nyeri' => '',
        'diagnosa_kerja' => '',
        'tujuan_kunjungan' => '',
        'catatan_tambahan' => '',
        'anamnesis' => '',
        'riwayat_penyakit_dahulu' => '',
        'oksigen' => '',
        'total_ews' => '',
        'kategori_ews' => '',
        'ews_tidak_diukur' => '',
    ];

    public function index($registrasi_detail_id, $emr_id = null)
    {
        $registrasi_detail = RegistrasiDetail::with('registrasi.pasien')->findOrFail($registrasi_detail_id);

        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'read'), 403);

        $aksesCrud = AksesEhr::flags((int) $form_id);

        $riwayat = EmrHelper::emrList((int) $form_id, (int) $registrasi_detail->registrasi_id);

        // Tidak boleh membuat data baru -> tampilkan riwayat terakhir.
        if (empty($emr_id) && ! ($aksesCrud['create'] ?? false) && $riwayat->isNotEmpty()) {
            return redirect()->route('emr.dynamic.index', [
                'form_name' => self::SLUG,
                'registrasi_detail_id' => $registrasi_detail_id,
                'emr_id' => $riwayat->first()->emr_id,
                'action' => 'view',
            ]);
        }

        // Riwayat KUNJUNGAN untuk dropdown "History" di panel kiri.
        // Riwayat emr sendiri sudah diambil komponen x-emr-history-table.
        $historyGrouped = EmrHelper::historyKunjunganGrouped($registrasi_detail);

        if (empty($emr_id)) {
            $emr_data = EmrHelper::wrapData(self::KOSONG);

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

        // Rincian skor EWS dihitung dari tanda vital yang tampil di form ini,
        // jadi tabel rinciannya konsisten dengan nilai yang akan disimpan.
        $ews = EwsHelper::hitung(
            $emr_data->toArray(),
            array_filter(explode(',', (string) ($emr_data['ews_tidak_diukur'] ?? '')))
        );

        return view('moduls.EMR.AssesmenAwalMedisRawatJalan.index', compact(
            'registrasi_detail',
            'riwayat',
            'emr_data',
            'historyGrouped',
            'formAction',
            'isEdit',
            'deleteAction',
            'isView',
            'emr_id',
            'aksesCrud',
            'ews'
        ));
    }

    public function store(Request $request, $registrasi_detail_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'create'), 403);

        $data = $this->validated($request);

        $registrasi_detail = RegistrasiDetail::findOrFail($registrasi_detail_id);

        $user = Auth::user();
        if (! $user || ! $user->pegawai_id || ! $user->user_id) {
            return redirect()->back()->with('error', 'Sesi Anda Telah Habis Silahkan Login Kembali!');
        }

        try {
            EmrHelper::insert((int) $form_id, $this->filteredData($data, (int) $form_id), (int) $registrasi_detail_id);

            return redirect()->back()->with('success', 'Assesmen Awal Medis berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan assesmen awal medis: '.$e->getMessage())->withInput();
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

            return redirect()->back()->with('success', 'Assesmen Awal Medis berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui assesmen awal medis: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Assesmen Awal Medis berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus assesmen awal medis: '.$e->getMessage());
        }
    }

    /**
     * Hanya field yang terpetakan yang disimpan. Field TURUNAN (`bmi`,
     * `total_ews`, `kategori_ews`) dihitung ulang di server dari tanda vital —
     * nilai yang dikirim browser untuk key itu diabaikan, sehingga angka tidak
     * bisa dimanipulasi dari sisi klien.
     */
    private function filteredData(array $data, int $formId): array
    {
        // Parameter yang tidak bisa diukur HARUS diambil SEBELUM
        // array_intersect_key: key `ews_na` memang sengaja tidak ada di
        // mapping, jadi langsung dibuang oleh filter tersebut.
        $na = $this->tidakDiukur($data);

        $data = array_intersect_key($data, array_flip(EmrHelper::objekVariabels($formId)));

        $berat = (float) ($data['berat_badan'] ?? 0);
        $tinggiCm = (float) ($data['tinggi_badan'] ?? 0);

        $data['bmi'] = ($berat > 0 && $tinggiCm > 0)
            ? round($berat / (($tinggiCm / 100) ** 2), 1)
            : null;

        // Skor nyeri hanya relevan bila pasien mengeluh nyeri.
        if (($data['nyeri'] ?? '') !== 'Ya') {
            $data['skor_nyeri'] = null;
        }

        // EWS memakai 7 parameter yang sudah ada di form ini (pernapasan,
        // SpO2, oksigen, sistolik, nadi, kesadaran, suhu) — jadi petugas tidak
        // mengisi angka yang sama dua kali.
        //
        // Parameter yang tidak bisa diukur ditandai lewat checkbox ews_na[] (sudah
        // dibaca di atas, sebelum di-filter). Penandanya ikut disimpan sebagai
        // daftar nama (comma separated) supaya jejaknya tetap terbaca.
        $ews = EwsHelper::hitung($data, $na);

        // Total SELALU disimpan: jumlah berjalan dari parameter yang sudah
        // dinilai, supaya angka tetap terbaca di daftar riwayat meski form
        // diisi sebagian. Kategori risiko disimpan bila semua parameter sudah
        // TERISI atau ditandai tidak diukur.
        $data['total_ews'] = $ews['total'];
        $data['kategori_ews'] = $ews['lengkap'] ? $ews['kategori'] : null;
        $data['ews_tidak_diukur'] = $na ? implode(',', $na) : null;

        return $data;
    }

    /**
     * Daftar parameter EWS yang ditandai "tidak diukur" oleh petugas.
     *
     * Sumbernya checkbox `ews_na[]`. Key `ews_na` sengaja TIDAK ada di tabel
     * `objek_form_control`, jadi `array_intersect_key()` di filteredData()
     * membuangnya sebelum disimpan — yang tersimpan hanya salinannya di
     * `ews_tidak_diukur`.
     *
     * @param  array<string, mixed>  $data
     * @return array<int, string>
     */
    private function tidakDiukur(array $data): array
    {
        $mentah = $data['ews_na'] ?? [];

        // Handle string (satu nilai) maupun comma separated, supaya aman
        // kalau checkbox mengirimnya sebagai skalar.
        if (is_string($mentah)) {
            $mentah = array_map('trim', explode(',', $mentah));
        }

        return array_values(array_intersect(EwsHelper::PARAMETER, array_filter((array) $mentah, 'is_string')));
    }

    /**
     * Validasi input form. Tanggal dan jam WAJIB diisi manual (tanpa default).
     */
    private function validated(Request $request): array
    {
        return array_merge([
            'td_sistolik' => null,
            'td_diastolik' => null,
            'nadi' => null,
            'suhu' => null,
            'pernapasan' => null,
            'saturasi' => null,
            'berat_badan' => null,
            'tinggi_badan' => null,
            'kesadaran' => null,
            'oksigen' => null,
            'skor_nyeri' => null,
            'catatan_tambahan' => null,
            'anamnesis' => null,
            'riwayat_penyakit_dahulu' => null,
            'ews_na' => [],
        ], $request->validate([
            'keluhan_utama' => 'required|string|max:2000',
            'tanggal_assesmen' => 'required|date',
            'waktu_assesmen' => 'required|date_format:H:i',
            'dokter_pemeriksa_id' => 'required|integer|exists:pegawai,pegawai_id',

            'td_sistolik' => 'nullable|numeric|min:0|max:300',
            'td_diastolik' => 'nullable|numeric|min:0|max:200',
            'nadi' => 'nullable|numeric|min:0|max:300',
            'suhu' => 'nullable|numeric|min:25|max:45',
            'pernapasan' => 'nullable|numeric|min:0|max:80',
            'saturasi' => 'nullable|numeric|min:0|max:100',
            'berat_badan' => 'nullable|numeric|min:0|max:500',
            'tinggi_badan' => 'nullable|numeric|min:0|max:300',

            'kesadaran' => 'nullable|string|max:100',
            'oksigen' => 'nullable|in:'.implode(',', EwsHelper::OKSIGEN),
            'nyeri' => 'required|in:Ya,Tidak',
            'skor_nyeri' => 'nullable|integer|min:0|max:10',
            'diagnosa_kerja' => 'required|string|max:2000',
            'tujuan_kunjungan' => 'required|string|max:100',
            'catatan_tambahan' => 'nullable|string|max:2000',
            'anamnesis' => 'nullable|string|max:5000',
            'riwayat_penyakit_dahulu' => 'nullable|string|max:5000',

            // Parameter EWS yang ditandai tidak bisa diukur.
            'ews_na' => 'nullable|array',
            'ews_na.*' => 'string|in:'.implode(',', EwsHelper::PARAMETER),
        ]));
    }
}
