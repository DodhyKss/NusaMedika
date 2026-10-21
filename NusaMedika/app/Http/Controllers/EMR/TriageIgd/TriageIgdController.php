<?php

namespace App\Http\Controllers\EMR\TriageIgd;

use App\Helpers\AksesEhr;
use App\Helpers\EmrHelper;
use App\Helpers\SelectOption;
use App\Http\Controllers\Controller;
use App\Models\RegistrasiDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * Form Triage IGD (form 19, slug `triage_igd`) - KHUSUS IGD.
 *
 * Pengkajian awal pasien di pintu IGD yang menjadi bukti objektif atas
 * penetapan prioritas triase: pemeriksaan fisik, keluhan utama, skala nyeri,
 * riwayat penyakit, skrining risiko jatuh, dan zona triase.
 *
 * Catatan implementasi:
 * - Form ini boleh dibuat berulang (retriase). Yang dibatasi adalah MENGUBAH
 *   catatan lama (lebih dari 24 jam), mengikuti perilaku legacy.
 * - `prioritas_triase` TIDAK menulis ulang `registrasi_detail.prioritas`:
 *   kolom registrasi memakai key SelectOption `triase_igd` (Merah/Kuning/
 *   Hijau/Hitam) yang formatnya berbeda. Nilai registrasi ditampilkan sebagai
 *   panel baca-saja sebagai pembanding.
 * - `zona_perawatan` & `respon_time` hanya metadata tampilan (badge di bawah
 *   select). Keduanya tidak punya baris `objek_form_control` sehingga otomatis
 *   dibuang `array_intersect_key()` bila browser mengirimnya.
 */
class TriageIgdController extends Controller
{
    private const SLUG = 'triage_igd';

    /**
     * Riwayat penyakit: 8 variabel bersuffix pada satu objek 42 + satu
     * variabel free text untuk pilihan "Lainnya".
     */
    private const RIWAYAT = [
        'riwayat_penyakit_1', 'riwayat_penyakit_2', 'riwayat_penyakit_3',
        'riwayat_penyakit_4', 'riwayat_penyakit_5', 'riwayat_penyakit_6',
        'riwayat_penyakit_7', 'riwayat_penyakit_8',
    ];

    /** Field opsional yang tidak punya rule `required`. */
    private const OPSIONAL = [
        'tipe_nafas', 'akral', 'alasan_kunjungan_lain', 'skor_nyeri',
        'lokasi_nyeri', 'frekuensi_nyeri', 'karakteristik_nyeri',
        'riwayat_penyakit_lain', 'catatan',
    ];

    /**
     * Metadata turunan prioritas triase untuk badge (identik dengan tabel
     * `triage_igd_cak.php` legacy). Tidak punya mapping, hanya tampilan.
     */
    private function metaTriase(): array
    {
        return [
            '1' => ['zona' => 'Zona Merah', 'waktu' => 'Segera', 'warna' => 'bg-red-100 text-red-700'],
            '2' => ['zona' => 'Zona Merah', 'waktu' => '< 10 Menit', 'warna' => 'bg-red-100 text-red-700'],
            '3' => ['zona' => 'Zona Kuning', 'waktu' => '< 30 Menit', 'warna' => 'bg-amber-100 text-amber-700'],
            '4' => ['zona' => 'Zona Kuning', 'waktu' => '< 60 Menit', 'warna' => 'bg-amber-100 text-amber-700'],
            '5' => ['zona' => 'Poliklinik', 'waktu' => '< 120 Menit', 'warna' => 'bg-emerald-100 text-emerald-700'],
            'HITAM' => ['zona' => '-', 'waktu' => '-', 'warna' => 'bg-slate-200 text-slate-800'],
        ];
    }

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

        // Riwayat KUNJUNKAN untuk dropdown "History" di panel kiri.
        // Riwayat emr sendiri sudah diambil komponen x-emr-history-table.
        $historyGrouped = EmrHelper::historyKunjunganGrouped($registrasi_detail);

        // Panel baca-saja: ringkasan pengkajian awal keperawatan pada kunjungan
        // yang sama, sebagai konteks sebelum petugas menetapkan triase.
        $ringkasan = $this->ringkasanKeperawatan($registrasi_detail);

        // Meta zona triase (waktu respons per prioritas) untuk badge riwayat,
        // tabel zona, dan warna baris pada JS.
        $metaTriase = $this->metaTriase();

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

        return view('moduls.EMR.TriageIgd.index', compact(
            'registrasi_detail', 'riwayat', 'historyGrouped', 'emr_data',
            'formAction', 'isEdit', 'deleteAction', 'isView', 'emr_id',
            'aksesCrud', 'ringkasan', 'metaTriase'
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

            return redirect()->back()->with('success', 'Triage IGD berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan Triage IGD: '.$e->getMessage())->withInput();
        }
    }

    public function update(Request $request, $registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'update'), 403);

        $this->jagaUmurCatatan($emr_id);

        $data = $this->validated($request);

        try {
            EmrHelper::update((int) $emr_id, (int) $form_id, $this->filteredData($data, (int) $form_id));

            return redirect()->back()->with('success', 'Triage IGD berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui Triage IGD: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        $this->jagaUmurCatatan($emr_id);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Triage IGD berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus Triage IGD: '.$e->getMessage());
        }
    }

    /**
     * Catatan triase yang sudah lewat 24 jam tidak boleh diubah atau dihapus
     * (meniru `catatan_triage_igd.php` legacy). Membuat triase baru / retriase
     * tetap diperbolehkan.
     */
    private function jagaUmurCatatan($emr_id): void
    {
        $emr = EmrHelper::emrById((int) $emr_id);

        abort_unless($emr, 404);
        abort_if(
            Carbon::parse($emr->input_time)->diffInHours(now()) > 24,
            403,
            'Triase yang sudah lebih dari 24 jam tidak dapat diubah atau dihapus.'
        );
    }

    /**
     * Ringkasan pengkajian awal keperawatan (form 3) terbaru pada kunjungan yang
     * sama. Bersifat baca-saja, tidak punya mapping di form ini.
     */
    private function ringkasanKeperawatan(RegistrasiDetail $registrasi_detail): array
    {
        $form_id = (int) EmrHelper::formIdBySlug('pengkajian_awal_keperawatan');

        if (! $form_id) {
            return ['ada' => false, 'nilai' => []];
        }

        $emr = EmrHelper::latestEmr($form_id, (int) $registrasi_detail->registrasi_detail_id);

        if (! $emr) {
            return ['ada' => false, 'nilai' => []];
        }

        return [
            'ada' => true,
            'waktu' => Carbon::parse($emr->tgl_jam)->format('d/m/Y H:i'),
            'nilai' => EmrHelper::latestValuesByVariabel($form_id, (int) $registrasi_detail->registrasi_detail_id, [
                'keluhan', 'diagnosa_medis', 'riwayat_penyakit_sebelumnya', 'alergi',
            ]),
        ];
    }

    /**
     * Nilai kosong untuk form baru. Tanggal & jam default ke sekarang supaya
     * triase pertama tidak perlu diisi manual (tetap boleh diubah untuk
     * triase susulan).
     */
    private function kosong(): array
    {
        $d = [
            'tanggal_triase' => now()->format('Y-m-d'),
            'waktu_triase' => now()->format('H:i'),
            'keadaan_umum' => '', 'berat_badan' => '', 'tinggi_badan' => '',
            'suhu' => '', 'jalan_nafas' => '', 'tipe_nafas' => '',
            'frekuensi_nafas' => '', 'saturasi' => '', 'sirkulasi' => '',
            'akral' => '', 'nadi' => '', 'td_sistolik' => '', 'td_diastolik' => '',
            'kesadaran' => '', 'pupil' => '', 'refleks_cahaya' => '',
            'jenis_anamnesis' => '', 'alasan_kunjungan' => '', 'alasan_kunjungan_lain' => '',
            'nyeri' => '', 'skor_nyeri' => '', 'lokasi_nyeri' => '',
            'frekuensi_nyeri' => '', 'karakteristik_nyeri' => '',
            'risiko_jatuh' => '', 'prioritas_triase' => '', 'catatan' => '',
            'riwayat_penyakit_lain' => '',
        ];

        foreach (self::RIWAYAT as $riwayat) {
            $d[$riwayat] = '';
        }

        return $d;
    }

    private function validated(Request $request): array
    {
        $opsional = array_fill_keys(self::OPSIONAL, null);

        // Sisa field riwayat penyakit ikut opsional karena seluruh bloknya
        // dibuang bila anamnesis = Auto Anamnesis.
        $opsional += array_fill_keys(self::RIWAYAT, null);

        $data = array_merge($opsional, $request->validate([
            // Blok A - waktu pengkajian
            'tanggal_triase' => 'required|date',
            'waktu_triase' => 'required|date_format:H:i',

            // Blok B - pemeriksaan fisik
            'keadaan_umum' => ['required', Rule::in($this->opsi('keadaan_umum_igd'))],
            'berat_badan' => 'required|numeric|min:0.5|max:400',
            'tinggi_badan' => 'required|numeric|min:30|max:250',
            'suhu' => 'required|numeric|min:30|max:45',
            'jalan_nafas' => ['required', Rule::in($this->opsi('jalan_nafas'))],
            'frekuensi_nafas' => 'required|integer|min:0|max:80',
            'saturasi' => 'required|integer|min:30|max:100',
            'sirkulasi' => ['required', Rule::in($this->opsi('sirkulasi'))],
            'akral' => 'nullable|string|max:100',
            'nadi' => 'required|integer|min:0|max:250',
            'td_sistolik' => 'required|integer|min:40|max:300',
            'td_diastolik' => 'required|integer|min:20|max:200',
            'kesadaran' => ['required', Rule::in($this->opsi('kesadaran'))],
            'pupil' => ['required', Rule::in($this->opsi('pupil'))],
            'refleks_cahaya' => 'required|integer|min:0|max:10',

            // Blok C - keluhan utama & anamnesis
            'jenis_anamnesis' => ['required', Rule::in($this->opsi('jenis_anamnesis'))],
            'alasan_kunjungan' => ['required', Rule::in($this->opsi('alasan_kunjungan_igd'))],
            'alasan_kunjungan_lain' => [
                Rule::requiredIf(fn () => $request->input('alasan_kunjungan') === 'Lainnya'),
                'nullable', 'string', 'max:200',
            ],

            // Blok D - skala nyeri
            'nyeri' => ['required', Rule::in(['Ya, Nyeri', 'Tidak Nyeri'])],
            'skor_nyeri' => [
                Rule::requiredIf(fn () => $request->input('nyeri') === 'Ya, Nyeri'),
                'nullable', 'integer', 'min:0', 'max:10',
            ],
            'lokasi_nyeri' => [
                Rule::requiredIf(fn () => $request->input('nyeri') === 'Ya, Nyeri'),
                'nullable', 'string', 'max:200',
            ],
            'frekuensi_nyeri' => ['nullable', Rule::in($this->opsi('frekuensi_nyeri'))],
            'karakteristik_nyeri' => ['nullable', Rule::in($this->opsi('karakteristik_nyeri'))],

            // Blok E - riwayat penyakit
            'riwayat_penyakit_lain' => [
                Rule::requiredIf(fn () => $request->input('riwayat_penyakit_8') === 'Lainnya'),
                'nullable', 'string', 'max:200',
            ],

            // Blok F - skrining risiko jatuh
            'risiko_jatuh' => ['required', Rule::in(['Beresiko', 'Tidak Beresiko'])],

            // Blok G - zona triase IGD
            'prioritas_triase' => ['required', Rule::in(['1', '2', '3', '4', '5', 'HITAM'])],

            // Blok H - catatan
            'catatan' => 'nullable|string|max:2000',
        ] + array_fill_keys(self::RIWAYAT, 'nullable|string|max:100')));

        // Tanggal & jam triase tidak boleh berada di masa depan.
        abort_if(
            Carbon::parse($data['tanggal_triase'].' '.$data['waktu_triase'])->isAfter(now()),
            422,
            'Tanggal dan jam triase tidak boleh berada di masa depan.'
        );

        return $data;
    }

    /**
     * Daftar `value` sebuah key SelectOption.
     */
    private function opsi(string $key): array
    {
        return array_column(SelectOption::get($key), 'value');
    }

    /**
     * Hanya field terpetakan yang disimpan. Aturan kondisional: field yang tidak
     * berlaku dibuang supaya tidak ada baris yatim di `emr_detail`, dan
     * field turunan (zona_perawatan, respon_time) otomatis hilang karena tidak
     * ada di mapping.
     */
    private function filteredData(array $data, int $formId): array
    {
        $data = array_intersect_key($data, array_flip(EmrHelper::objekVariabels($formId)));

        // Riwayat penyakit hanya bermakna bila informasi diperoleh dari pasien
        // sendiri atau dari orang lain (Allo Anamnesis).
        if (($data['jenis_anamnesis'] ?? '') !== 'Allo Anamnesis') {
            foreach (array_merge(self::RIWAYAT, ['riwayat_penyakit_lain']) as $riwayat) {
                $data[$riwayat] = null;
            }
        } elseif (($data['riwayat_penyakit_8'] ?? '') !== 'Lainnya') {
            $data['riwayat_penyakit_lain'] = null;
        }

        // Keluhan "Lainnya" wajib diisi bebas.
        if (($data['alasan_kunjungan'] ?? '') !== 'Lainnya') {
            $data['alasan_kunjungan_lain'] = null;
        }

        // Detail skala nyeri hanya berlaku bila pasien dinyatakan nyeri.
        if (($data['nyeri'] ?? '') !== 'Ya, Nyeri') {
            $data['skor_nyeri'] = null;
            $data['lokasi_nyeri'] = null;
            $data['frekuensi_nyeri'] = null;
            $data['karakteristik_nyeri'] = null;
        }

        // Nadi tidak teraba tidak mungkin bernilai 0 -> isi penanda agar tetap
        // valid secara tipe.
        if (($data['sirkulasi'] ?? '') === 'Nadi Tidak Teraba' && (int) ($data['nadi'] ?? 0) === 0) {
            $data['nadi'] = '-';
        }

        return $data;
    }
}
