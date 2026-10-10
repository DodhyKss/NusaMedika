<?php

namespace App\Http\Controllers\EMR\BundleVap;

use App\Helpers\AksesEhr;
use App\Helpers\EmrHelper;
use App\Http\Controllers\Controller;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Form Bundle Pencegahan VAP (slug `bundle_vap`).
 *
 * Berada di dashboard menu Catatan Keperawatan -> Observasi Harian -> Bundle
 * VAP, dan dipakai di SEMUA jenis rawat (RI/RJ/IGD/MCU).
 *
 * Form ini adalah checklist 10 butir pencegahan Ventilator-Associated
 * Pneumonia. Tiap butir bernilai Ya / Tidak / N/A. Skor, persen, dan kategori
 * kepatuhan adalah field TURUNAN yang DIHITUNG ULANG di server dari jawaban
 * mentah (nilai dari browser diabaikan), sehingga angkanya tidak bisa
 * dimanipulasi dari sisi klien.
 *
 * Butir `N/A` dikeluarkan dari pembagi: butir yang memang tidak berlaku —
 * misalnya suction subglottik pada pasien tanpa ETT — tidak boleh menurunkan
 * kepatuhan.
 */
class BundleVapController extends Controller
{
    private const SLUG = 'bundle_vap';

    /** Jumlah butir dalam bundle. */
    private const JUMLAH_BUTIR = 10;

    /** Ambang persen kepatuhan untuk kategori "Lengkap". */
    public const AMBANG_LENGKAP = 80;

    /**
     * Nilai per butir bundle.
     *
     * `varian` = nama variabel di emr_detail, `nilai` = label butir, `ket` =
     * penjelasan singkat yang ditampilkan di form.
     */
    public const BUTIR = [
        1 => ['varian' => 'vap_1', 'nilai' => 'Elevasi kepala tempat tidur 30–45 derajat', 'ket' => 'Posisi semi Fowler, kecuali ada kontraindikasi.'],
        2 => ['varian' => 'vap_2', 'nilai' => 'Sedasi minimal dan SAT/SBT harian', 'ket' => 'Evaluasi ulang tiap hari dan lakukan SAT/SBT bila memungkinkan.'],
        3 => ['varian' => 'vap_3', 'nilai' => 'Oral care chlorhexidine', 'ket' => 'Minimal 2 kali sehari atau sesuai kebijakan unit.'],
        4 => ['varian' => 'vap_4', 'nilai' => 'Profilaksis tukaklambung', 'ket' => 'Bila ada indikasi risiko tukak.'],
        5 => ['varian' => 'vap_5', 'nilai' => 'Profilaksis DVT', 'ket' => 'Bila tidak ada kontraindikasi perdarahan.'],
        6 => ['varian' => 'vap_6', 'nilai' => 'Suction subglottik di atas cuff ETT', 'ket' => 'Bila pasien terpasang ETT dengan cuff.'],
        7 => ['varian' => 'vap_7', 'nilai' => 'Hand hygiene sebelum dan sesudah tindakan', 'ket' => 'Five moments WHO.'],
        8 => ['varian' => 'vap_8', 'nilai' => 'Kontrol tekanan cuff 20–30 cmH2O', 'ket' => 'Bila terpasang ETT/Tracheostomy tube.'],
        9 => ['varian' => 'vap_9', 'nilai' => 'Ganti circuit ventilator hanya bila kotor', 'ket' => 'Jangan mengganti circuit secara terjadwal.'],
        10 => ['varian' => 'vap_10', 'nilai' => 'Evaluasi kesiapan weaning harian', 'ket' => 'Dokumentasikan readiness untuk lepas ventilator mekanik.'],
    ];

    /** Nilai jawaban yang valid untuk setiap butir. */
    public const JAWABAN = ['Ya', 'Tidak', 'N/A'];

    /**
     * Nilai default mode "buat baru" supaya blade tidak pernah
     * "Undefined array key" saat form dibuka kosong.
     */
    private const KOSONG = [
        'tanggal_bundle' => '',
        'waktu_bundle' => '',
        'vap_skor' => '',
        'vap_persen' => '',
        'vap_kategori' => '',
        'catatan' => '',
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

        // Ringkasan kepatuhan dihitung dari jawaban butir yang tampil di form,
        // supaya tampilan konsisten dengan nilai yang akan disimpan.
        $ringkasan = $this->hitungKepatuhan($emr_data->toArray());

        return view('moduls.EMR.BundleVap.index', compact(
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
            'ringkasan'
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

            return redirect()->back()->with('success', 'Bundle VAP berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan Bundle VAP: '.$e->getMessage())->withInput();
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

            return redirect()->back()->with('success', 'Bundle VAP berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui Bundle VAP: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Bundle VAP berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus Bundle VAP: '.$e->getMessage());
        }
    }

    /**
     * Hanya field yang terpetakan yang disimpan. Field TURUNAN (`vap_skor`,
     * `vap_persen`, `vap_kategori`) dihitung ulang di server dari jawaban butir
     * mentah — nilai yang dikirim browser untuk key itu diabaikan, sehingga
     * angka tidak bisa dimanipulasi dari sisi klien.
     */
    private function filteredData(array $data, int $formId): array
    {
        $data = array_intersect_key($data, array_flip(EmrHelper::objekVariabels($formId)));

        $hasil = $this->hitungKepatuhan($data);

        // Skor & persen tetap disimpan walau butir belum lengkap (jumlah
        // berjalan), supaya angka tetap terbaca di daftar riwayat. Kategori
        // hanya disimpan bila seluruh butir sudah terjawab (Ya/Tidak/N/A)
        // karena kepatuhan parsial bisa menyesatkan.
        $data['vap_skor'] = $hasil['skor'];
        $data['vap_persen'] = $hasil['persen'];
        $data['vap_kategori'] = $hasil['kategori'];

        return $data;
    }

    /**
     * Hitung skor, persen, dan kategori kepatuhan dari jawaban butir.
     *
     * Pembagi hanya menghitung butir yang terjawab Ya/Tidak; butir `N/A`
     * dikeluarkan karena memang tidak berlaku pada pasien tersebut. Bila
     * seluruh butir N/A (tidak ada yang dinilai), persen bernilai 0 dan
     * kategori "Belum Lengkap".
     *
     * @param  array<string, mixed>  $data
     * @return array{skor:int, dinilai:int, persen:float, kategori:?string, lengkap:bool, benar:array<int,bool>}
     */
    private function hitungKepatuhan(array $data): array
    {
        $skor = 0;
        $dinilai = 0;
        $terisi = 0;
        $benar = [];

        foreach (self::BUTIR as $butir) {
            $nilai = trim((string) ($data[$butir['varian']] ?? ''));
            $terisi += ($nilai !== '') ? 1 : 0;

            if ($nilai === 'Ya') {
                $skor++;
                $dinilai++;
                $benar[$butir['varian']] = true;
            } elseif ($nilai === 'Tidak') {
                $dinilai++;
                $benar[$butir['varian']] = false;
            } else {
                // Kosong atau N/A: tidak dinilai, tidak masuk pembagi.
                $benar[$butir['varian']] = null;
            }
        }

        $persen = $dinilai > 0 ? round(($skor / $dinilai) * 100, 1) : 0.0;
        $lengkap = $terisi === self::JUMLAH_BUTIR;

        $kategori = null;
        if ($lengkap && $dinilai > 0) {
            $kategori = $persen >= self::AMBANG_LENGKAP ? 'Lengkap' : 'Sebagian';
        }

        return [
            'skor' => $skor,
            'dinilai' => $dinilai,
            'terisi' => $terisi,
            'persen' => $persen,
            'kategori' => $kategori,
            'lengkap' => $lengkap,
            'benar' => $benar,
        ];
    }

    /**
     * Validasi input form. Tanggal dan jam WAJIB diisi manual (tanpa default).
     * Seluruh butir bundle wajib dijawab Ya/Tidak/N/A agar kategori kepatuhan
     * bisa dihitung.
     */
    private function validated(Request $request): array
    {
        $rules = [
            'tanggal_bundle' => 'required|date',
            'waktu_bundle' => 'required|date_format:H:i',
            'catatan' => 'nullable|string|max:2000',
        ];

        $defaults = ['catatan' => null];

        foreach (self::BUTIR as $butir) {
            $rules[$butir['varian']] = 'required|in:'.implode(',', self::JAWABAN);
            $defaults[$butir['varian']] = null;
        }

        return array_merge($defaults, $request->validate($rules));
    }
}
