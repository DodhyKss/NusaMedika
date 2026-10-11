<?php

namespace App\Http\Controllers\EMR\AsesmenStatusFungsional;

use App\Helpers\AksesEhr;
use App\Helpers\AsesmenKeperawatanHelper;
use App\Helpers\EmrHelper;
use App\Http\Controllers\Controller;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Form Asesmen Status Fungsional (form 34, slug `asesmen_status_fungsional`).
 *
 * Penilaian kemampuan ADL (Activities of Daily Living) dengan 10 item ala
 * Barthel, skor maksimum 20. Sengaja DIPISAH dari form 33 (Barthel Index
 * baku) karena ambang dan wording-nya berbeda - form 34 dipakai untuk asesmen
 * fungsional saat admisi, form 33 untuk rehabilitasi dan discharge planning.
 *
 * `sf_skor` dan `sf_interpretasi` SELALU dihitung ulang di server dari 10 item
 * mentah; nilai browser dibuang. Kategori hanya disimpan bila 10/10 item
 * terisi - skor parsial selalu terlalu rendah dan menyesatkan (pola yang sama
 * dengan EwsHelper untuk EWS parsial).
 */
class AsesmenStatusFungsionalController extends Controller
{
    private const SLUG = 'asesmen_status_fungsional';

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

        // Definisi item + ambang interpretasi dikirim ke blade agar tabel radio
        // dan warna badge di JS identik dengan perhitungan server.
        $itemFungsional = AsesmenKeperawatanHelper::fungsionalItems();
        $interpretasi = [
            ['label' => 'Mandiri', 'min' => 20, 'max' => 20, 'class' => 'text-emerald-600'],
            ['label' => 'Ketergantungan Ringan', 'min' => 12, 'max' => 19, 'class' => 'text-blue-600'],
            ['label' => 'Ketergantungan Sedang', 'min' => 9, 'max' => 11, 'class' => 'text-slate-600'],
            ['label' => 'Ketergantungan Berat', 'min' => 5, 'max' => 8, 'class' => 'text-amber-600'],
            ['label' => 'Ketergantungan Total', 'min' => 0, 'max' => 4, 'class' => 'text-red-600'],
        ];

        return view('moduls.EMR.AsesmenStatusFungsional.index', compact(
            'registrasi_detail', 'riwayat', 'historyGrouped', 'emr_data',
            'formAction', 'isEdit', 'deleteAction', 'isView', 'emr_id', 'aksesCrud',
            'itemFungsional', 'interpretasi'
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

            return redirect()->back()->with('success', 'Asesmen Status Fungsional berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan Asesmen Status Fungsional: '.$e->getMessage())->withInput();
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

            return redirect()->back()->with('success', 'Asesmen Status Fungsional berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui Asesmen Status Fungsional: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Asesmen Status Fungsional berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus Asesmen Status Fungsional: '.$e->getMessage());
        }
    }

    /**
     * Nilai kosong untuk form baru.
     */
    private function kosong(): array
    {
        $d = [];

        foreach (array_keys(AsesmenKeperawatanHelper::fungsionalItems()) as $variabel) {
            $d[$variabel] = '';
        }

        return array_merge($d, ['sf_skor' => '', 'sf_interpretasi' => '']);
    }

    private function validated(Request $request): array
    {
        $rules = [];
        foreach (AsesmenKeperawatanHelper::fungsionalItems() as $variabel => $item) {
            $rules[$variabel] = ['required', 'in:'.implode(',', array_column($item['opsi'], 'value'))];
        }

        $opsional = ['sf_skor' => null, 'sf_interpretasi' => null];

        return array_merge($opsional, $request->validate($rules));
    }

    /**
     * Skor & interpretasi dihitung ulang dari 10 item mentah. Nilai dari browser
     * untuk `sf_skor` / `sf_interpretasi` dibuang tanpa dibacakan.
     */
    private function filteredData(array $data, int $formId): array
    {
        $data = array_intersect_key($data, array_flip(EmrHelper::objekVariabels($formId)));

        $hasil = AsesmenKeperawatanHelper::hitungFungsional($data);

        $data['sf_skor'] = $hasil['lengkap'] ? $hasil['skor'] : null;
        $data['sf_interpretasi'] = $hasil['lengkap'] ? $hasil['interpretasi'] : null;

        return $data;
    }
}
