<?php

namespace App\Http\Controllers\EMR\AlatInvasif;

use App\Helpers\AksesEhr;
use App\Helpers\EmrHelper;
use App\Helpers\SelectOption;
use App\Http\Controllers\Controller;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

/**
 * Form Alat Invasif (slug `alat_invasif`).
 *
 * Berada di dashboard menu Catatan Keperawatan -> Observasi Harian -> Alat
 * Invasif, dan dipakai di SEMUA jenis rawat (RI/RJ/IGD/MCU).
 *
 * Bentuknya SATU baris per EMR — satu catatan untuk satu alat invasif. Kalau
 * pasien memakai beberapa alat, buat EMR baru untuk masing-masing; itu sebabnya
 * form ini tidak memakai variabel bersuffix seperti Butir Bundle VAP atau baris
 * obat/BMHP di form Tindakan Medis.
 *
 * `lama_pemasangan` adalah field TURUNAN (jumlah hari dari tanggal pasang sampai
 * tanggal lepas) yang DIHITUNG ULANG di server — nilai dari browser diabaikan.
 * Bila tanggal lepas masih kosong, nilainya `null` (alat masih terpasang), bukan
 * `0`: nol akan terbaca sudah dilepas di hari yang sama dengan pemasangan.
 *
 * Daftar alat & lokasi memakai `App\Helpers\SelectOption`, bukan <option>
 * hardcode di blade.
 */
class AlatInvasifController extends Controller
{
    private const SLUG = 'alat_invasif';

    /**
     * Nilai default mode "buat baru" supaya blade tidak pernah
     * "Undefined array key" saat form dibuka kosong.
     */
    private const KOSONG = [
        'tanggal_pasang' => '',
        'jam_pasang' => '',
        'alat_invasif' => '',
        'lokasi_pemasangan' => '',
        'tanggal_lepas' => '',
        'jam_lepas' => '',
        'lama_pemasangan' => '',
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

        $opsiAlat = SelectOption::get('alat_invasif');
        $opsiLokasi = SelectOption::get('lokasi_alat_invasif');

        return view('moduls.EMR.AlatInvasif.index', compact(
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
            'opsiAlat',
            'opsiLokasi'
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

            return redirect()->back()->with('success', 'Alat Invasif berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan alat invasif: '.$e->getMessage())->withInput();
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

            return redirect()->back()->with('success', 'Alat Invasif berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui alat invasif: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Alat Invasif berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus alat invasif: '.$e->getMessage());
        }
    }

    /**
     * Hanya field yang terpetakan yang disimpan. Field TURUNAN
     * (`lama_pemasangan`) dihitung ulang di server dari tanggal pasang & lepas —
     * nilai yang dikirim browser untuk key itu diabaikan.
     */
    private function filteredData(array $data, int $formId): array
    {
        $data = array_intersect_key($data, array_flip(EmrHelper::objekVariabels($formId)));

        $data['lama_pemasangan'] = $this->lamaPemasangan($data);

        return $data;
    }

    /**
     * Lama pemasangan dalam satuan hari (pecahan), dihitung dari tanggal & jam
     * pemasangan sampai tanggal & jam pelepasan.
     *
     * Mengembalikan null bila:
     * - tanggal pemasangan belum diisi (alat kemungkinan sudah terpasang sejak
     *   sebelum masuk, jadi tidak ada titik awal yang bisa dihitung), atau
     * - tanggal pelepasan masih kosong (alat MASIH terpasang — belum ada durasi).
     *
     * Mengembalikan null juga bila tanggal lepas lebih awal daripada tanggal
     * pasang: data itu tidak masuk akal, dan lebih baik tidak tersimpan angka
     * negatif daripada menampilkan durasi negatif.
     */
    private function lamaPemasangan(array $data): ?int
    {
        $pasang = $this->waktuLengkap($data['tanggal_pasang'] ?? null, $data['jam_pasang'] ?? null);
        $lepas = $this->waktuLengkap($data['tanggal_lepas'] ?? null, $data['jam_lepas'] ?? null);

        if (! $pasang || ! $lepas) {
            return null;
        }

        if ($lepas->lessThan($pasang)) {
            return null;
        }

        // diffInDays memotong ke bawah; pembulatan ke atas supaya "pasang 08:00,
        // lepas 20:00 di hari yang sama" tetap terbaca sebagai 1 hari penuh.
        return (int) ceil($pasang->diffInDays($lepas, true));
    }

    /**
     * Gabungkan tanggal + jam jadi satu titik waktu. Jam bersifat opsional —
     * bila kosong, dipakai tengah hari supaya tanggal saja tetap bisa dihitung.
     */
    private function waktuLengkap($tanggal, $jam): ?Carbon
    {
        $tanggal = trim((string) $tanggal);

        if ($tanggal === '') {
            return null;
        }

        try {
            $waktu = Carbon::parse($tanggal.' '.trim((string) $jam));
        } catch (\Exception $e) {
            return null;
        }

        return $waktu;
    }

    /**
     * Validasi input form.
     *
     * Hanya jenis alat & lokasi yang wajib: tanggal pemasangan boleh kosong
     * karena alat tidak selalu dipasang di ruang perawatan — bisa sudah terpasang
     * sejak IGD, installment, atau dirujuk dari fasilitas lain, sehingga titik
     * pemasangannya tidak diketahui. Tanggal lepas juga boleh kosong selama alat
     * masih terpasang.
     */
    private function validated(Request $request): array
    {
        $alat = array_column(SelectOption::get('alat_invasif'), 'value');
        $lokasi = array_column(SelectOption::get('lokasi_alat_invasif'), 'value');

        return array_merge([
            'tanggal_pasang' => null,
            'jam_pasang' => null,
            'tanggal_lepas' => null,
            'jam_lepas' => null,
        ], $request->validate([
            'tanggal_pasang' => 'nullable|date',
            'jam_pasang' => 'nullable|date_format:H:i',
            'alat_invasif' => 'required|string|in:'.implode(',', $alat),
            'lokasi_pemasangan' => 'required|string|in:'.implode(',', $lokasi),
            'tanggal_lepas' => 'nullable|date',
            'jam_lepas' => 'nullable|date_format:H:i',
        ]));
    }
}
