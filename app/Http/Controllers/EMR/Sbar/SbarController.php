<?php

namespace App\Http\Controllers\EMR\Sbar;

use App\Helpers\AksesEhr;
use App\Helpers\EmrHelper;
use App\Helpers\SelectOption;
use App\Http\Controllers\Controller;
use App\Models\RegistrasiDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Form SBAR (slug `sbar`) — Situation / Background / Assessment / Recommendation.
 *
 * Berbeda dengan SOAP (catatan untuk klinisi sendiri), SBAR adalah format
 * KOMUNIKASI antar petugas: serah terima antar shift, permintaan konsultasi,
 * atau transfer antar unit. Karena itu isinya berupa pesan terstruktur, bukan
 * lembar asesmen.
 *
 * Unsur S & B di-prefill otomatis dari EMR form lain pada kunjungan yang sama
 * (Assesmen Awal Medis, Pengkajian Awal/Harian, SOAP, Implementasi Keperawatan)
 * supaya petugas tidak perlu menyalin ulang data yang sudah ada. Hasil prefill
 * HANYA teks — petugas tetap bebas mengubahnya sebelum disimpan.
 *
 * Tidak ada field turunan: seluruh isian disimpan apa adanya. Validasi tetap
 * memaksa keempat unsur S/B/A/R terisi supaya pesan tidak pernah setengah.
 */
class SbarController extends Controller
{
    private const SLUG = 'sbar';

    /**
     * Nilai default mode "buat baru" supaya blade tidak pernah
     * "Undefined array key" saat form dibuka kosong.
     */
    private const KOSONG = [
        'tanggal_sbar' => '',
        'waktu_sbar' => '',
        'shift' => '',
        'urgensi' => '',
        'cara_komunikasi' => '',
        'penerima_id' => '',
        's_situation' => '',
        'b_background' => '',
        'a_assessment' => '',
        'r_recommendation' => '',
        'alergi' => '',
        'catatan_tambahan' => '',
    ];

    /**
     * Form yang datanya dipakai untuk prefill S & B, beserta variabel yang
     * diambil. Urutan = prioritas terbaru.
     */
    private const SUMBER = [
        'assesmen_awal_medis_rawat_jalan' => [
            'keluhan_utama', 'diagnosa_kerja', 'kesadaran',
            'td_sistolik', 'td_diastolik', 'nadi', 'suhu', 'pernapasan', 'saturasi',
            'total_ews', 'kategori_ews', 'riwayat_penyakit_dahulu',
        ],
        'pengkajian_awal_keperawatan' => [
            'diagnosa_medis', 'keluhan', 'riwayat_penyakit_sebelumnya',
            'riwayat_penyakit_sekarang', 'alergi',
        ],
        'pengkajian_harian_keperawatan' => ['harian_keluhan'],
        'soap' => ['subjective', 'objective', 'assessment', 'planning', 'instruksi'],
        'implementasi_keperawatan' => ['implementasi_id', 'respon_implementasi'],
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

        // Riwayat KUNJUNKAN untuk dropdown "History" di panel kiri.
        // Riwayat emr sendiri sudah diambil komponen x-emr-history-table.
        $historyGrouped = EmrHelper::historyKunjunganGrouped($registrasi_detail);

        // Opsi dropdown diambil dari SelectOption, bukan <option> hardcode.
        $opsiShift = SelectOption::get('shift');
        $opsiUrgensi = SelectOption::get('urgensi_sbar');
        $opsiKomunikasi = SelectOption::get('cara_komunikasi');

        // Konteks klinis untuk prefill S & B + ringkasan baca-saja di view.
        $konteks = $this->konteksKlinis($registrasi_detail);

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

        // Ringkasan klinis yang dirender di atas textarea S/B, sekaligus nilai
        // awal textarea tersebut saat membuat data baru.
        $ringkasan = [
            'pasien' => $konteks['pasien'],
            'vital' => $konteks['vital'],
            'alergi' => $konteks['alergi'],
            'terapi' => $konteks['terapi'],
            'soap' => $konteks['soap'],
            'harian' => $konteks['harian'],
            'pernah_ada' => $konteks['pernah_ada'],
            'prefill_s' => $konteks['prefill_s'],
            'prefill_b' => $konteks['prefill_b'],
        ];

        return view('moduls.EMR.Sbar.index', compact(
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
            'ringkasan',
            'opsiShift',
            'opsiUrgensi',
            'opsiKomunikasi'
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

            return redirect()->back()->with('success', 'SBAR berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan SBAR: '.$e->getMessage())->withInput();
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

            return redirect()->back()->with('success', 'SBAR berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui SBAR: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'SBAR berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus SBAR: '.$e->getMessage());
        }
    }

    /**
     * Hanya field yang terpetakan yang disimpan. SBAR tidak punya field
     * turunan — prefill S & B hanya berlaku di form, bukan disimpan terpisah.
     */
    private function filteredData(array $data, int $formId): array
    {
        return array_intersect_key($data, array_flip(EmrHelper::objekVariabels($formId)));
    }

    /**
     * Validasi input form. Tanggal dan jam WAJIB diisi manual (tanpa default).
     */
    private function validated(Request $request): array
    {
        return array_merge([
            'catatan_tambahan' => null,
            'alergi' => null,
        ], $request->validate([
            'tanggal_sbar' => 'required|date',
            'waktu_sbar' => 'required|date_format:H:i',
            'shift' => 'nullable|in:Pagi,Siang,Sore,Malam',
            'urgensi' => 'required|in:Biasa,Segera,Mendesak',
            'cara_komunikasi' => 'required|in:Tatap Muka,Telepon,WhatsApp,Surat',
            'penerima_id' => 'required|integer|exists:pegawai,pegawai_id',

            's_situation' => 'required|string|max:3000',
            'b_background' => 'required|string|max:3000',
            'a_assessment' => 'required|string|max:3000',
            'r_recommendation' => 'required|string|max:3000',

            'alergi' => 'nullable|string|max:500',
            'catatan_tambahan' => 'nullable|string|max:2000',
        ]));
    }

    /**
     * Kumpulkan data klinis dari form EMR lain pada kunjungan yang sama.
     *
     * Data diambil dari EMR TERBARU per form (bukan seluruh riwayat), supaya
     * prefill relevan dengan kondisi pasien saat ini. Nilai diambil apa adanya
     * (tidak disimpan ke SBAR) — hanya dipakai mengisi textarea saat membuat.
     *
     * @return array{pasien:array, vital:array, alergi:?string, terapi:?string, soap:array, harian:?string, pernah_ada:bool, prefill_s:?string, prefill_b:?string}
     */
    private function konteksKlinis($registrasi_detail): array
    {
        $rd = $registrasi_detail;
        $registrasi = $rd->registrasi;
        $pasien = $registrasi?->pasien;

        // Identitas pasien (untuk Situation).
        $identitas = [];
        if ($pasien) {
            $identitas[] = 'Nama: '.($pasien->nama_pasien ?? '-');
            $identitas[] = 'No. MR: '.($pasien->no_mr ?? '-');
            $jenisKelamin = ($pasien->jenis_kelamin ?? '') === 'P' ? 'Perempuan' : (($pasien->jenis_kelamin ?? '') === 'L' ? 'Laki-laki' : '-');
            $identitas[] = 'Jenis Kelamin: '.$jenisKelamin;
            if ($pasien->tgl_lahir) {
                try {
                    // diffInYears() mengembalikan float (mis. 36,39), bulatkan
                    // supaya tampil sebagai "36 tahun".
                    $umur = (int) round(Carbon::parse($pasien->tgl_lahir)->diffInYears(now()));
                    $identitas[] = 'Umur: '.$umur.' tahun';
                } catch (\Exception $e) {
                    // tgl_lahir tidak bisa di-parse — lewati umur saja.
                }
            }
        }

        $bagian = null;
        try {
            $bagian = $rd->bagian?->nama_bagian;
        } catch (\Exception $e) {
            $bagian = null;
        }

        $tglMasuk = null;
        try {
            $tglMasuk = $registrasi?->tgl_masuk ? Carbon::parse($registrasi->tgl_masuk)->format('d/m/Y H:i') : null;
        } catch (\Exception $e) {
            $tglMasuk = null;
        }

        $vital = [];
        $alergi = null;
        $terapi = null;
        $soap = [];
        $harian = null;
        $pernahAda = false;

        // Ambil nilai terbaru per form sumber.
        $latest = [];
        foreach (self::SUMBER as $slug => $variabels) {
            $formId = EmrHelper::formIdBySlug($slug);
            if (! $formId) {
                continue;
            }

            if (! EmrHelper::latestEmr((int) $formId, (int) $rd->registrasi_detail_id)) {
                continue;
            }

            $pernahAda = true;
            $latest[$slug] = EmrHelper::latestValuesByVariabel((int) $formId, (int) $rd->registrasi_detail_id, $variabels);
        }

        // Vital + diagnosa dari Assesmen Awal (RJ) atau Pengkajian Awal.
        $vitalKeys = ['td_sistolik', 'td_diastolik', 'nadi', 'suhu', 'pernapasan', 'saturasi', 'kesadaran'];
        foreach (['assesmen_awal_medis_rawat_jalan', 'pengkajian_awal_keperawatan'] as $slug) {
            $src = $latest[$slug] ?? [];
            if (! $src) {
                continue;
            }
            foreach ($vitalKeys as $k) {
                if (! isset($vital[$k]) && trim((string) ($src[$k] ?? '')) !== '') {
                    $vital[$k] = $src[$k];
                }
            }
        }
        if (! isset($vital['total_ews']) && ! empty($latest['assesmen_awal_medis_rawat_jalan']['total_ews'])) {
            $vital['total_ews'] = $latest['assesmen_awal_medis_rawat_jalan']['total_ews'];
            if (! empty($latest['assesmen_awal_medis_rawat_jalan']['kategori_ews'])) {
                $vital['kategori_ews'] = $latest['assesmen_awal_medis_rawat_jalan']['kategori_ews'];
            }
        }

        // Alergi: dari pengkajian awal, atau dari form pengkajian harian.
        foreach (['pengkajian_awal_keperawatan', 'assesmen_awal_medis_rawat_jalan'] as $slug) {
            $a = trim((string) ($latest[$slug]['alergi'] ?? ''));
            if ($a !== '') {
                $alergi = $a;
                break;
            }
        }

        // Therapi/intervensi dari Implementasi Keperawatan terbaru.
        $impl = $latest['implementasi_keperawatan'] ?? [];
        if ($impl) {
            $parts = [];
            if (trim((string) ($impl['respon_implementasi'] ?? '')) !== '') {
                $parts[] = $impl['respon_implementasi'];
            }
            if ($parts) {
                $terapi = implode(' | ', $parts);
            }
        }

        // SOAP untuk Assessment.
        $soapSrc = $latest['soap'] ?? [];
        foreach (['subjective', 'objective', 'assessment', 'planning', 'instruksi'] as $k) {
            $v = trim((string) ($soapSrc[$k] ?? ''));
            if ($v !== '') {
                $soap[$k] = $v;
            }
        }

        // Keluhan harian (pengkajian harian) untuk Assessment.
        $harian = trim((string) ($latest['pengkajian_harian_keperawatan']['harian_keluhan'] ?? '')) ?: null;

        // --- Prefill S: identitas + masalah utama ---
        $sLines = [];
        if ($identitas) {
            $sLines[] = implode(', ', $identitas);
        }
        if ($bagian) {
            $sLines[] = 'Tempat: '.$bagian.' (rawat '.($registrasi?->jenis_rawat ?? '-').')';
        }
        if ($tglMasuk) {
            $sLines[] = 'Masuk sejak: '.$tglMasuk;
        }

        // Masalah utama: diagnosa kerja / diagnosa medis / keluhan.
        $masalah = null;
        foreach ([
            ['assesmen_awal_medis_rawat_jalan', 'diagnosa_kerja'],
            ['pengkajian_awal_keperawatan', 'diagnosa_medis'],
        ] as [$slug, $key]) {
            $v = trim((string) ($latest[$slug][$key] ?? ''));
            if ($v !== '') {
                $masalah = $v;
                break;
            }
        }
        if ($masalah) {
            $sLines[] = 'Masalah: '.$masalah;
        }

        // Keluhan utama / harian.
        $keluhan = trim((string) ($latest['assesmen_awal_medis_rawat_jalan']['keluhan_utama'] ?? ''))
            ?: trim((string) ($latest['pengkajian_awal_keperawatan']['keluhan'] ?? ''));
        if ($keluhan === '' && $harian) {
            $keluhan = $harian;
        }
        if ($keluhan !== '') {
            $sLines[] = 'Keluhan: '.$keluhan;
        }

        $prefillS = $sLines ? implode("\n", $sLines)."\n" : null;

        // --- Prefill B: riwayat, alergi, vital, terapi sebelumnya ---
        $bLines = [];
        $riwayat = trim((string) ($latest['assesmen_awal_medis_rawat_jalan']['riwayat_penyakit_dahulu'] ?? ''))
            ?: trim((string) ($latest['pengkajian_awal_keperawatan']['riwayat_penyakit_sebelumnya'] ?? ''));
        if ($riwayat !== '') {
            $bLines[] = 'Riwayat: '.$riwayat;
        }
        if ($alergi !== null) {
            $bLines[] = 'Alergi: '.$alergi;
        }

        // Vital (ringkas).
        if ($vital) {
            $parts = [];
            if (! empty($vital['td_sistolik'])) {
                $parts[] = 'TD '.$vital['td_sistolik'].(empty($vital['td_diastolik']) ? '' : '/'.$vital['td_diastolik']);
            }
            foreach (['nadi' => 'Nadi', 'suhu' => 'Suhu', 'pernapasan' => 'RR', 'saturasi' => 'SpO2', 'kesadaran' => 'Kesadaran'] as $k => $lbl) {
                if (! empty($vital[$k])) {
                    $parts[] = $lbl.' '.$vital[$k];
                }
            }
            if ($parts) {
                $bLines[] = 'Vital terakhir: '.implode(', ', $parts);
            }
            if (! empty($vital['total_ews'])) {
                $bLines[] = 'EWS: '.$vital['total_ews'].(empty($vital['kategori_ews']) ? '' : ' ('.$vital['kategori_ews'].')');
            }
        }

        if ($terapi) {
            $bLines[] = 'Intervensi sebelumnya: '.$terapi;
        }

        $prefillB = $bLines ? implode("\n", $bLines)."\n" : null;

        return [
            'pasien' => [
                'identitas' => $identitas,
                'nama' => $pasien?->nama_pasien,
                'no_mr' => $pasien?->no_mr,
                'bagian' => $bagian,
                'jenis_rawat' => $registrasi?->jenis_rawat,
                'tgl_masuk' => $tglMasuk,
            ],
            'vital' => $vital,
            'alergi' => $alergi,
            'terapi' => $terapi,
            'soap' => $soap,
            'harian' => $harian,
            'pernah_ada' => $pernahAda,
            'prefill_s' => $prefillS,
            'prefill_b' => $prefillB,
        ];
    }
}
