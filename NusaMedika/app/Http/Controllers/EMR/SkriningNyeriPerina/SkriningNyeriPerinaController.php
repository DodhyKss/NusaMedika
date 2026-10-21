<?php

namespace App\Http\Controllers\EMR\SkriningNyeriPerina;

use App\Helpers\AksesEhr;
use App\Helpers\EmrHelper;
use App\Helpers\NyeriHelper;
use App\Http\Controllers\Controller;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Form Skrining Nyeri Perina (form 38, slug `skrining_nyeri_perina`).
 *
 * NIPS - Neonatal Infant Pain Scale, 6 parameter untuk neonatus.
 * `total_nips` dan `interpretasi_nyeri` SELALU dihitung ulang di server — nilai
 * dari browser dibuang.
 */
class SkriningNyeriPerinaController extends Controller
{
    private const SLUG = 'skrining_nyeri_perina';

    /** 6 parameter NIPS; sudah punya objek (328-333). */
    private const NIPS = [
        'nips_ekspresi_wajah', 'nips_tangisan', 'nips_pola_napas',
        'nips_gerakan_lengan', 'nips_gerakan_tungkai', 'nips_status_jaga',
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

        return view('moduls.EMR.SkriningNyeriPerina.index', compact(
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

            return redirect()->back()->with('success', 'Skrining Nyeri Perina berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan Skrining Nyeri Perina: '.$e->getMessage())->withInput();
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

            return redirect()->back()->with('success', 'Skrining Nyeri Perina berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui Skrining Nyeri Perina: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Skrining Nyeri Perina berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus Skrining Nyeri Perina: '.$e->getMessage());
        }
    }

    /**
     * Nilai kosong untuk form baru.
     */
    private function kosong(): array
    {
        return [
            'tanggal_nips' => now()->format('Y-m-d'),
            'waktu_nips' => now()->format('H:i'),
            'nips_ekspresi_wajah' => '',
            'nips_tangisan' => '',
            'nips_pola_napas' => '',
            'nips_gerakan_lengan' => '',
            'nips_gerakan_tungkai' => '',
            'nips_status_jaga' => '',
            'total_nips' => '',
            'interpretasi_nyeri' => '',
            'catatan' => '',
        ];
    }

    private function validated(Request $request): array
    {
        $opsional = [
            'total_nips' => null,
            'interpretasi_nyeri' => null,
            'catatan' => null,
        ];

        return array_merge($opsional, $request->validate([
            'tanggal_nips' => 'required|date',
            'waktu_nips' => 'required|date_format:H:i',

            'nips_ekspresi_wajah' => 'required|integer|between:0,1',
            'nips_tangisan' => 'required|integer|between:0,2',
            'nips_pola_napas' => 'required|integer|between:0,1',
            'nips_gerakan_lengan' => 'required|integer|between:0,1',
            'nips_gerakan_tungkai' => 'required|integer|between:0,1',
            'nips_status_jaga' => 'required|integer|between:0,1',

            'catatan' => 'nullable|string|max:1000',
        ]));
    }

    /**
     * Hanya field terpetakan yang disimpan. Total & interpretasi NIPS dihitung
     * ulang di server dari 6 parameter mentah, dan hanya disimpan bila 6/6
     * terisi (skor parsial selalu menyesatkan, pola EWS).
     */
    private function filteredData(array $data, int $formId): array
    {
        $data = array_intersect_key($data, array_flip(EmrHelper::objekVariabels($formId)));

        $jawaban = [];
        foreach (self::NIPS as $parameter) {
            $jawaban[$parameter] = $data[$parameter] ?? null;
        }

        $hasil = NyeriHelper::nips($jawaban);
        $data['total_nips'] = $hasil['total'];
        $data['interpretasi_nyeri'] = $hasil['interpretasi'] === '' ? null : $hasil['interpretasi'];

        return $data;
    }
}
