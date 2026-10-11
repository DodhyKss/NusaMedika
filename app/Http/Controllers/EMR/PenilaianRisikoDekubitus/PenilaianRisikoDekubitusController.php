<?php

namespace App\Http\Controllers\EMR\PenilaianRisikoDekubitus;

use App\Helpers\AksesEhr;
use App\Helpers\DekubitusHelper;
use App\Helpers\EmrHelper;
use App\Helpers\SelectOption;
use App\Http\Controllers\Controller;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * Form Penilaian Risiko Dekubitus / Skala Norton (form 32, slug
 * `penilaian_risiko_dekubitus`).
 *
 * Lima item Skala Norton, tiap item 4 opsi dengan skor 4 -> 1, total 5-20.
 *
 * ARAH SKOR (penting, sering terbalik): skor NORTON RENDAH berarti risiko
 * TINGGI. Total < 12 = Risiko Tinggi, 12-15 = Risiko Sedang, 16-20 = Tidak
 * Berisiko. Kategori ditulis penuh ("Risiko Tinggi Terjadi Dekubitus") bukan
 * hanya "Tinggi" supaya tidak salah dibaca di layar.
 *
 * `skor_norton` dan `kategori_dekubitus` SELALU dihitung ulang di server lewat
 * `DekubitusHelper::hitungNorton()`; nilai browser dibuang tanpa dibacakan.
 * Skor parsial (bukan 5/5 butir) TIDAK disimpan - NULL, karena 3 dari 5 butir
 * bisa menghasilkan skor yang terlihat aman padahal belum lengkap.
 */
class PenilaianRisikoDekubitusController extends Controller
{
    private const SLUG = 'penilaian_risiko_dekubitus';

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

        // Definisi item + tabel ambang dikirim ke blade supaya tabel radio dan
        // warna badge di JS identik dengan perhitungan server.
        $itemNorton = DekubitusHelper::nortonItems();
        $kategoriNorton = DekubitusHelper::kategoriNorton();

        return view('moduls.EMR.PenilaianRisikoDekubitus.index', compact(
            'registrasi_detail', 'riwayat', 'historyGrouped', 'emr_data',
            'formAction', 'isEdit', 'deleteAction', 'isView', 'emr_id', 'aksesCrud',
            'itemNorton', 'kategoriNorton'
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

            return redirect()->back()->with('success', 'Penilaian Risiko Dekubitus berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan Penilaian Risiko Dekubitus: '.$e->getMessage())->withInput();
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

            return redirect()->back()->with('success', 'Penilaian Risiko Dekubitus berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui Penilaian Risiko Dekubitus: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Penilaian Risiko Dekubitus berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus Penilaian Risiko Dekubitus: '.$e->getMessage());
        }
    }

    /**
     * Nilai kosong untuk form baru.
     */
    private function kosong(): array
    {
        $d = [
            'tanggal_norton' => now()->format('Y-m-d'),
            'waktu_norton' => now()->format('H:i'),
            'skor_norton' => '',
            'kategori_dekubitus' => '',
            'kesadaran' => '',
            'catatan' => '',
        ];

        foreach (array_keys(DekubitusHelper::nortonItems()) as $variabel) {
            $d[$variabel] = '';
        }

        return $d;
    }

    private function validated(Request $request): array
    {
        // Skor & kategori dihitung ulang di server; nilai browser diabaikan.
        $opsional = [
            'skor_norton' => null,
            'kategori_dekubitus' => null,
            'kesadaran' => null,
            'catatan' => null,
        ];

        // Radios 1-4 ditulis sebagai array `Rule::in` supaya nilai di luar
        // rentang tetap ditolak walau string rules di-longgar.
        $rules = [
            'tanggal_norton' => 'required|date',
            'waktu_norton' => 'required|date_format:H:i',
            'norton_kondisi_fisik_umum' => ['required', 'integer', Rule::in(['1', '2', '3', '4'])],
            'norton_kesadaran' => ['required', 'integer', Rule::in(['1', '2', '3', '4'])],
            'norton_aktivitas' => ['required', 'integer', Rule::in(['1', '2', '3', '4'])],
            'norton_mobilitas' => ['required', 'integer', Rule::in(['1', '2', '3', '4'])],
            'norton_inkontinensia' => ['required', 'integer', Rule::in(['1', '2', '3', '4'])],
            'kesadaran' => ['nullable', Rule::in(array_column(SelectOption::get('kesadaran'), 'value'))],
            'catatan' => 'nullable|string|max:1000',
        ];

        return array_merge($opsional, $request->validate($rules));
    }

    /**
     * Hanya field terpetakan yang disimpan. `skor_norton` dan
     * `kategori_dekubitus` dihitung ulang di sini dari 5 butir jawaban.
     */
    private function filteredData(array $data, int $formId): array
    {
        $data = array_intersect_key($data, array_flip(EmrHelper::objekVariabels($formId)));

        $hasil = DekubitusHelper::hitungNorton($data);

        $data['skor_norton'] = $hasil['skor'];
        $data['kategori_dekubitus'] = $hasil['kategori'];

        return $data;
    }
}
