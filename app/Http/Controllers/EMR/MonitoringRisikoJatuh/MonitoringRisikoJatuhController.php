<?php

namespace App\Http\Controllers\EMR\MonitoringRisikoJatuh;

use App\Helpers\AksesEhr;
use App\Helpers\EmrHelper;
use App\Helpers\MonitoringRisikoJatuhHelper;
use App\Helpers\RisikoJatuhHelper;
use App\Helpers\SelectOption;
use App\Http\Controllers\Controller;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Form Monitoring & Evaluasi Risiko Jatuh (form 30, slug
 * `monitoring_risiko_jatuh`).
 *
 * Ini BUKAN instrumen baru. Form ini mem-*reuse* seluruh instrumen pengkajian
 * risiko jatuh (objek 89-113: HDS / MFS / TUG / Sydney) supaya laporan risiko
 * jatuh bisa menjumlahkan dan membandingkan antar waktu, lalu menambah bagian
 * monitoring yang belum ada di form mana pun: status perbandingan, kejadian
 * jatuh, dan checklist intervensi pencegahan.
 *
 * ARAH SKOR risiko jatuh: skor TINGGI berarti risiko TINGGI - berlawanan dengan
 * Norton & Barthel. `status_monitoring` karena itu membandingkan angka skor apa
 * adanya: skor turun = Membaik, skor naik = Memburuk.
 *
 * SEMUA field turunan - `risiko_jatuh_skor`, `risiko_jatuh_label`,
 * `risiko_jatuh_intervensi`, `risiko_jatuh_ringkasan`, dan `status_monitoring` -
 * dihitung ulang di server; nilai browser dibuang tanpa dibacakan (R4 doc).
 *
 * Instrumen default TIDAK dihitung dari angka batas legacy (17/18), melainkan
 * lewat `RisikoJatuhHelper::instrumenUntukUsia()` (batas 13/14) supaya konsisten
 * dengan form 3 dan form 4 (R7 doc).
 *
 * R3 doc: objek 92 dipakai dua variabel (`risiko_jatuh_intervensi` teks turunan
 * dan `intervensi_pencegahan` JSON checklist). Form ini karena itu tidak boleh
 * memakai `EmrHelper::emrDetailByObjek()`.
 */
class MonitoringRisikoJatuhController extends Controller
{
    private const SLUG = 'monitoring_risiko_jatuh';

    /** Field turunan server: dibuang dari browser lalu dihitung ulang. */
    private const FIELD_HITUNGAN = [
        'risiko_jatuh_instrumen',
        'risiko_jatuh_skor',
        'risiko_jatuh_label',
        'risiko_jatuh_intervensi',
        'risiko_jatuh_ringkasan',
        'status_monitoring',
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
            $emr_data = EmrHelper::wrapData($this->kosong($registrasi_detail));
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

        $usia = RisikoJatuhHelper::hitungUsia($registrasi_detail->registrasi->pasien->tgl_lahir ?? null);

        // Definisi instrumen, ambang TUG, dan checklist intervensi dikirim ke
        // blade supaya tabel radio dan badge di JS identik dengan server.
        $semuaInstrumen = RisikoJatuhHelper::semuaInstrumen();
        $norma = RisikoJatuhHelper::normaTug($usia);
        $intervensi = MonitoringRisikoJatuhHelper::intervensiPencegahan();

        // Skor penilaian sebelumnya, untuk menampilkan status perbandingan
        // ketik form dibuka (nilai final tetap dihitung ulang saat simpan).
        $skorSebelumnya = $this->skorSebelumnya((int) $form_id, (int) $registrasi_detail_id, $emr_id ? (int) $emr_id : null);
        $statusSebelumnya = MonitoringRisikoJatuhHelper::statusMonitoring(
            $skorSebelumnya,
            $emr_data['risiko_jatuh_skor'] ?? null
        );

        return view('moduls.EMR.MonitoringRisikoJatuh.index', compact(
            'registrasi_detail', 'riwayat', 'historyGrouped', 'emr_data',
            'formAction', 'isEdit', 'deleteAction', 'isView', 'emr_id', 'aksesCrud',
            'usia', 'semuaInstrumen', 'norma', 'intervensi',
            'skorSebelumnya', 'statusSebelumnya'
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
            EmrHelper::insert(
                (int) $form_id,
                $this->filteredData($data, (int) $form_id, (int) $registrasi_detail_id),
                (int) $registrasi_detail_id
            );

            return redirect()->back()->with('success', 'Monitoring Risiko Jatuh berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan Monitoring Risiko Jatuh: '.$e->getMessage())->withInput();
        }
    }

    public function update(Request $request, $registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'update'), 403);

        $data = $this->validated($request);

        try {
            EmrHelper::update(
                (int) $emr_id,
                (int) $form_id,
                $this->filteredData($data, (int) $form_id, (int) $registrasi_detail_id, (int) $emr_id)
            );

            return redirect()->back()->with('success', 'Monitoring Risiko Jatuh berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui Monitoring Risiko Jatuh: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Monitoring Risiko Jatuh berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus Monitoring Risiko Jatuh: '.$e->getMessage());
        }
    }

    /**
     * Nilai kosong untuk form baru. Instrumen default mengikuti usia pasien
     * lewat `RisikoJatuhHelper::instrumenUntukUsia()` (batas 13/14, bukan
     * batas legacy 17/18) supaya konsisten dengan form 3 dan form 4.
     */
    private function kosong($registrasi_detail): array
    {
        $usia = RisikoJatuhHelper::hitungUsia($registrasi_detail->registrasi->pasien->tgl_lahir ?? null);

        $d = [
            'tanggal_monitoring_jatuh' => now()->format('Y-m-d'),
            'waktu_monitoring_jatuh' => now()->format('H:i'),
            'risiko_jatuh_instrumen' => RisikoJatuhHelper::instrumenUntukUsia($usia),
            'risiko_jatuh_skor' => '',
            'risiko_jatuh_label' => '',
            'risiko_jatuh_intervensi' => '',
            'risiko_jatuh_ringkasan' => '',
            'status_monitoring' => '',
            'kejadian_jatuh' => 'Tidak',
            'waktu_kejadian_jatuh' => '',
            'keterangan_kejadian_jatuh' => '',
            'tindak_lanjut_jatuh' => '',
            'intervensi_pencegahan' => [],
            'berat_badan' => '',
            'kesadaran' => '',
            'catatan' => '',
        ];

        foreach (array_keys(MonitoringRisikoJatuhHelper::items()) as $variabel) {
            $d[$variabel] = '';
        }

        $d['tug_detik'] = '';

        return $d;
    }

    private function validated(Request $request): array
    {
        // Semua field nullable butuh default null, karena `$request->validate()`
        // tidak menyertakan key yang tidak terkirim dan `filteredData()` memakai
        // `array_intersect_key()` - tanpa default ini baris emr_detail-nya hilang.
        $opsional = [
            'waktu_kejadian_jatuh' => null,
            'keterangan_kejadian_jatuh' => null,
            'tindak_lanjut_jatuh' => null,
            'berat_badan' => null,
            'kesadaran' => null,
            'catatan' => null,
        ];

        foreach (self::FIELD_HITUNGAN as $variabel) {
            $opsional[$variabel] = null;
        }

        foreach (array_keys(MonitoringRisikoJatuhHelper::items()) as $variabel) {
            $opsional[$variabel] = null;
        }

        $opsional['tug_detik'] = null;
        $opsional['intervensi_pencegahan'] = [];

        // Catatan: `risiko_jatuh_instrumen` sengaja tidak `required`. Nilai dari
        // browser hanya dipakai sebagai Instrument fallback; kalau kosong atau
        // tidak dikenal, `MonitoringRisikoJatuhHelper::instrumenAktif()`
        // mendeteksinya dari jawaban item yang ikut terkirim (R9 doc).
        $rules = [
            'tanggal_monitoring_jatuh' => 'required|date',
            'waktu_monitoring_jatuh' => 'required|date_format:H:i',
            'risiko_jatuh_instrumen' => ['nullable', Rule::in(['HDS', 'MFS', 'TUG', 'SYDNEY'])],
            'status_monitoring' => ['nullable', Rule::in(array_column(SelectOption::get('status_monitoring_jatuh'), 'value'))],
            'kejadian_jatuh' => ['required', Rule::in(array_column(SelectOption::get('ya_tidak_kapital'), 'value'))],
            'waktu_kejadian_jatuh' => 'nullable|date|required_if:kejadian_jatuh,Ya',
            'keterangan_kejadian_jatuh' => 'nullable|required_if:kejadian_jatuh,Ya|string|max:1000',
            'tindak_lanjut_jatuh' => 'nullable|required_if:kejadian_jatuh,Ya|string|max:1000',
            'intervensi_pencegahan' => ['required', 'array', 'min:1'],
            'intervensi_pencegahan.*' => ['string', 'max:200'],
            'berat_badan' => ['nullable', 'numeric', 'between:1,300'],
            'kesadaran' => ['nullable', Rule::in(array_column(SelectOption::get('kesadaran'), 'value'))],
            'catatan' => ['nullable', 'string', 'max:1000'],
            'tug_detik' => ['nullable', 'required_if:risiko_jatuh_instrumen,TUG', 'numeric', 'min:0', 'max:300'],
        ];

        // Field turunan: nilainya dibuang di filteredData(), tapi tetap diberi
        // type longgar supaya kiriman yang tidak wajar tidak merusak validasi.
        foreach (['risiko_jatuh_skor', 'risiko_jatuh_label'] as $variabel) {
            $rules[$variabel] = ['nullable', 'string', 'max:255'];
        }

        foreach (['risiko_jatuh_intervensi', 'risiko_jatuh_ringkasan'] as $variabel) {
            $rules[$variabel] = ['nullable', 'string', 'max:1000'];
        }

        // Jawaban item instrumen. Instrumen ditentukan SERVER, jadi kelengkapan
        // per instrumen tidak bisa ditulis lewat `required_if` statis di sini -
        // dipakai bentuk string `required_if` yang aman (Laravel memanggilnya
        // tanpa argumen closure, lihat AGENTS.md section Triage IGD).
        foreach (MonitoringRisikoJatuhHelper::items() as $variabel => $item) {
            $rules[$variabel] = [
                'nullable',
                Rule::in(array_column($item['opsi'], 'value')),
                'required_if:risiko_jatuh_instrumen,'.$item['instrumen'],
            ];
        }

        // TUG: durasi dalam detik. R8 doc - TUG tidak bisa dipakai pada pasien
        // yang tidak dapat berjalan, jadi wajib diisi begitu instrumen TUG aktif.
        // Aturannya sudah ada di blok `$rules` di atas.

        return array_merge($opsional, $request->validate($rules));
    }

    /**
     * Hanya field terpetakan yang disimpan.
     *
     * 1. Skor, label, intervensi, ringkasan, dan status monitoring dihitung
     *    ULANG di server; nilai browser tidak pernah dipakai.
     * 2. Jawaban item instrumen yang tidak dipilih ikut instrument EMG dikosongkan
     *    (di-set `null`, bukan `unset()`, supaya baris `emr_detail`-nya tetap
     *    ada dan kelengkapan mapping tetap utuh).
     */
    private function filteredData(array $data, int $formId, int $registrasiDetailId, ?int $emrId = null): array
    {
        $usia = $this->usiaPasien($registrasiDetailId);
        $items = MonitoringRisikoJatuhHelper::items();

        // Instrumen efektif: nilai browser hanya dipakai bila valid, kalau tidak
        // dideteksi dari jawaban item yang ikut terkirim (R9 doc).
        $kode = MonitoringRisikoJatuhHelper::instrumenAktif($data['risiko_jatuh_instrumen'] ?? null, $data);

        $hasil = MonitoringRisikoJatuhHelper::hitung($kode, $data, $usia);

        $data['risiko_jatuh_instrumen'] = $hasil['instrumen'];
        $data['risiko_jatuh_skor'] = $hasil['skor'];
        $data['risiko_jatuh_label'] = $hasil['label'];
        $data['risiko_jatuh_intervensi'] = $hasil['intervensi'];
        $data['risiko_jatuh_ringkasan'] = $hasil['ringkasan'];

        // Status monitoring = perbandingan dengan penilaian sebelumnya pada
        // registrasi_detail yang sama. Saat update, emr yang sedang diedit
        // sendiri harus dikecualikan supaya tidak dibandingkan dengan dirinya.
        $data['status_monitoring'] = MonitoringRisikoJatuhHelper::statusMonitoring(
            $this->skorSebelumnya($formId, $registrasiDetailId, $emrId),
            $hasil['skor']
        );

        // Jawaban instrumen lain dikosongkan (null), bukan dibuang, supaya tidak
        // ada baris yatim di emr_detail (konvensi yang dipakai form lain).
        foreach ($items as $variabel => $item) {
            if ($item['instrumen'] !== $hasil['instrumen']) {
                $data[$variabel] = null;
            }
        }

        if ($hasil['instrumen'] !== 'TUG') {
            $data['tug_detik'] = null;
        }

        // Field kondisional: dibuang (null) bila tidak ada kejadian jatuh.
        if (($data['kejadian_jatuh'] ?? '') !== 'Ya') {
            $data['waktu_kejadian_jatuh'] = null;
            $data['keterangan_kejadian_jatuh'] = null;
            $data['tindak_lanjut_jatuh'] = null;
        }

        // Checklist intervensi: rapikan (trim + buang kosong + tanpa duplikat)
        // dan disimpan sebagai array; `EmrHelper::storeDetails()` yang mengubah
        // array menjadi JSON.
        $terpilih = $data['intervensi_pencegahan'] ?? [];
        $terpilih = is_array($terpilih) ? $terpilih : [$terpilih];
        $terpilih = array_values(array_unique(array_filter(array_map(
            fn ($t) => trim((string) $t),
            $terpilih
        ), fn ($t) => $t !== '')));

        $data = array_intersect_key($data, array_flip(EmrHelper::objekVariabels($formId)));
        $data['intervensi_pencegahan'] = $terpilih;

        return $data;
    }

    /** Usia pasien (tahun) dari registrasi_detail, dipakai norma TUG. */
    private function usiaPasien(int $registrasiDetailId): ?int
    {
        $tglLahir = DB::table('registrasi_detail')
            ->join('registrasi', 'registrasi_detail.registrasi_id', '=', 'registrasi.registrasi_id')
            ->join('pasien', 'registrasi.pasien_id', '=', 'pasien.pasien_id')
            ->where('registrasi_detail.registrasi_detail_id', $registrasiDetailId)
            ->value('pasien.tgl_lahir');

        return RisikoJatuhHelper::hitungUsia($tglLahir);
    }

    /**
     * Skor `risiko_jatuh_skor` dari penilaian SEBELUM-nya pada registrasi_detail
     * yang sama. `$skipEmrId` dipakai saat update supaya emr yang sedang diedit
     * tidak dibandingkan dengan dirinya sendiri.
     */
    private function skorSebelumnya(int $formId, int $registrasiDetailId, ?int $skipEmrId = null): ?float
    {
        $query = DB::table('emr')
            ->where('form_id', $formId)
            ->where('registrasi_detail_id', $registrasiDetailId)
            ->where(function ($q) {
                $q->whereNull('status_batal')->orWhere('status_batal', 0);
            });

        if ($skipEmrId) {
            $query->where('emr_id', '!=', $skipEmrId);
        }

        $emr_id = $query->orderByDesc('tgl_jam')->orderByDesc('emr_id')->value('emr_id');

        if (! $emr_id) {
            return null;
        }

        $nilai = DB::table('emr_detail')
            ->where('emr_id', $emr_id)
            ->where('variabel', 'risiko_jatuh_skor')
            ->where(function ($q) {
                $q->whereNull('status_batal')->orWhere('status_batal', 0);
            })
            ->value('value');

        return ($nilai === null || $nilai === '') ? null : (float) $nilai;
    }
}
