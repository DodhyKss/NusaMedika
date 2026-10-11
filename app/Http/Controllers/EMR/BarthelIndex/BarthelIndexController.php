<?php

namespace App\Http\Controllers\EMR\BarthelIndex;

use App\Helpers\AksesEhr;
use App\Helpers\DekubitusHelper;
use App\Helpers\EmrHelper;
use App\Http\Controllers\Controller;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * Form Barthel Index (form 33, slug `barthel_index`).
 *
 * Barthel Index baku (Mahoney & Barthel 1965) untuk mengukur kemandirian
 * aktivitas sehari-hari: 10 item, skor maksimum 20.
 *
 * ARAH SKOR (penting): skor BARTHEL RENDAH berarti ketergantungan TINGGI.
 * 0-4 Ketergantungan Total, 5-8 Berat, 9-11 Sedang, 12-19 Ringan, 20 Mandiri.
 * Berlawanan dengan Morse Fall Scale, sama dengan Norton.
 *
 * Perhatikan arah inkontinensia Barthel: skor 1 = KADANG inkontinen, skor 0 =
 * inkontinen. Tabel status fungsional (form 34 `asesmen_status_fungsional`)
 * memakai arah sebaliknya, jadi keduanya sengaja dipisah.
 *
 * `barthel_skor` dan `tingkat_ketergantungan` SELALU dihitung ulang di server
 * lewat `DekubitusHelper::hitungBarthel()`; nilai browser dibuang. Skor parsial
 * (bukan 10/10 butir) TIDAK disimpan - NULL.
 */
class BarthelIndexController extends Controller
{
    private const SLUG = 'barthel_index';

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
        $itemBarthel = DekubitusHelper::barthelItems();
        $kategoriBarthel = DekubitusHelper::kategoriBarthel();

        return view('moduls.EMR.BarthelIndex.index', compact(
            'registrasi_detail', 'riwayat', 'historyGrouped', 'emr_data',
            'formAction', 'isEdit', 'deleteAction', 'isView', 'emr_id', 'aksesCrud',
            'itemBarthel', 'kategoriBarthel'
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

            return redirect()->back()->with('success', 'Barthel Index berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan Barthel Index: '.$e->getMessage())->withInput();
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

            return redirect()->back()->with('success', 'Barthel Index berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui Barthel Index: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Barthel Index berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus Barthel Index: '.$e->getMessage());
        }
    }

    /**
     * Nilai kosong untuk form baru.
     */
    private function kosong(): array
    {
        $d = [
            'tanggal_barthel' => now()->format('Y-m-d'),
            'waktu_barthel' => now()->format('H:i'),
            'barthel_skor' => '',
            'tingkat_ketergantungan' => '',
            'catatan' => '',
        ];

        foreach (array_keys(DekubitusHelper::barthelItems()) as $variabel) {
            $d[$variabel] = '';
        }

        return $d;
    }

    private function validated(Request $request): array
    {
        // Skor & tingkat ketergantungan dihitung ulang di server; nilai browser
        // diabaikan, jadi keduanya hanya perlu default null.
        $opsional = [
            'barthel_skor' => null,
            'tingkat_ketergantungan' => null,
            'catatan' => null,
        ];

        $rules = [
            'tanggal_barthel' => 'required|date',
            'waktu_barthel' => 'required|date_format:H:i',
            'barthel_transfer' => ['required', 'integer', Rule::in(['0', '1', '2', '3'])],
            'barthel_mobilisasi' => ['required', 'integer', Rule::in(['0', '1', '2', '3'])],
            'barthel_toilet' => ['required', 'integer', Rule::in(['0', '1'])],
            'barthel_bersih' => ['required', 'integer', Rule::in(['0', '1'])],
            'barthel_bab' => ['required', 'integer', Rule::in(['0', '1', '2'])],
            'barthel_bak' => ['required', 'integer', Rule::in(['0', '1', '2'])],
            'barthel_mandi' => ['required', 'integer', Rule::in(['0', '1', '2'])],
            'barthel_berpakaian' => ['required', 'integer', Rule::in(['0', '1', '2'])],
            'barthel_makan' => ['required', 'integer', Rule::in(['0', '1', '2'])],
            'barthel_tangga' => ['required', 'integer', Rule::in(['0', '1', '2'])],
            'catatan' => 'nullable|string|max:1000',
        ];

        return array_merge($opsional, $request->validate($rules));
    }

    /**
     * Hanya field terpetakan yang disimpan. `barthel_skor` dan
     * `tingkat_ketergantungan` dihitung ulang di sini dari 10 butir jawaban.
     */
    private function filteredData(array $data, int $formId): array
    {
        $data = array_intersect_key($data, array_flip(EmrHelper::objekVariabels($formId)));

        $hasil = DekubitusHelper::hitungBarthel($data);

        $data['barthel_skor'] = $hasil['skor'];
        $data['tingkat_ketergantungan'] = $hasil['tingkat'];

        return $data;
    }
}
