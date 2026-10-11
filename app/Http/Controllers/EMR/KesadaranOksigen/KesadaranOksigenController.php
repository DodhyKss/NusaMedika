<?php

namespace App\Http\Controllers\EMR\KesadaranOksigen;

use App\Helpers\AksesEhr;
use App\Helpers\EmrHelper;
use App\Helpers\SelectOption;
use App\Http\Controllers\Controller;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * Form Kesadaran & Pemberian Oksigen (form 28, slug `kesadaran_oksigen`).
 *
 * GCS lengkap (Eye / Motorik / Verbal), AVPU yang diturunkan dari total GCS,
 * DPO, serta pemberian oksigen (jenis, flow rate, cara pemberian, ETT, dan
 * saturasi).
 *
 * `gcs_jumlah` dan `kesadaran` SELALU dihitung ulang di server; nilai dari
 * browser dibuang.
 */
class KesadaranOksigenController extends Controller
{
    private const SLUG = 'kesadaran_oksigen';

    /** Deskripsi tiap skor GCS (dipakai di blade & server). */
    public const GCS_EYE = [
        4 => 'Buka mata spontan',
        3 => 'Buka mata bila dirangsang suara',
        2 => 'Buka mata bila dirangsang nyeri',
        1 => 'Tidak bisa buka mata dengan dirangsang apa pun',
    ];

    public const GCS_MOTORIK = [
        6 => 'Mengikuti perintah',
        5 => 'Mengetahui tempat rangsang nyeri (localisasi nyeri)',
        4 => 'Hanya menarik bagian tubuh bila dirangsang',
        3 => 'Timbul fleksi abnormal bila diberi rangsang nyeri',
        2 => 'Timbul ekstensi abnormal bila diberi rangsang nyeri',
        1 => 'Tidak ada gerakan dengan rangsang apa pun',
    ];

    public const GCS_VERBAL = [
        5 => 'Komunikasi verbal baik, jawaban baik',
        4 => 'Bingung, disorientasi waktu, orang, dan tempat',
        3 => 'Dengan rangsangan hanya ada kata-kata',
        2 => 'Dengan rangsangan hanya keluar suara',
        1 => 'Tidak ada respons verbal',
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

        return view('moduls.EMR.KesadaranOksigen.index', compact(
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

            return redirect()->back()->with('success', 'Kesadaran & Pemberian Oksigen berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan Kesadaran & Pemberian Oksigen: '.$e->getMessage())->withInput();
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

            return redirect()->back()->with('success', 'Kesadaran & Pemberian Oksigen berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui Kesadaran & Pemberian Oksigen: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Kesadaran & Pemberian Oksigen berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus Kesadaran & Pemberian Oksigen: '.$e->getMessage());
        }
    }

    /**
     * Nilai kosong untuk form baru (15 variabel form 28).
     */
    private function kosong(): array
    {
        return [
            'tanggal_kesadaran' => now()->format('Y-m-d'),
            'waktu_kesadaran' => now()->format('H:i'),
            'gcs_e' => '',
            'gcs_m' => '',
            'gcs_v' => '',
            'gcs_jumlah' => '',
            'kesadaran' => '',
            'dpo' => '',
            'pemberian_o2' => 'Air',
            'flow_rate' => '',
            'cara_oksigen' => '',
            'ett' => 'Tidak',
            'persentase_oksigen' => '',
            'saturasi' => '',
            'keterangan' => '',
        ];
    }

    private function validated(Request $request): array
    {
        $opsional = [
            'dpo' => null,
            'flow_rate' => null,
            'cara_oksigen' => null,
            'persentase_oksigen' => null,
            'keterangan' => null,
        ];

        return array_merge($opsional, $request->validate([
            'tanggal_kesadaran' => 'required|date',
            'waktu_kesadaran' => 'required|date_format:H:i',

            'gcs_e' => 'required|integer|between:1,4',
            'gcs_m' => 'required|integer|between:1,6',
            'gcs_v' => 'required|integer|between:1,5',

            'dpo' => 'nullable|string|max:20',

            'pemberian_o2' => ['required', Rule::in(array_column(SelectOption::get('oksigen'), 'value'))],
            'flow_rate' => ['nullable', 'required_if:pemberian_o2,Oksigen', 'numeric', 'min:0', 'max:60'],
            'cara_oksigen' => [
                'nullable',
                'required_if:pemberian_o2,Oksigen',
                Rule::in(array_column(SelectOption::get('cara_pemberian_o2'), 'value')),
            ],

            'ett' => ['required', Rule::in(['Ya', 'Tidak'])],
            'persentase_oksigen' => 'nullable|numeric|between:21,100',

            'saturasi' => 'required|integer|between:50,100',
            'keterangan' => 'nullable|string|max:1000',
        ]));
    }

    /**
     * Total GCS dan tingkat kesadaran dihitung ulang di server (nilai browser
     * dibuang). Field kondisional yang tidak berlaku ikut dibuang.
     */
    private function filteredData(array $data, int $formId): array
    {
        $data = array_intersect_key($data, array_flip(EmrHelper::objekVariabels($formId)));

        $e = (int) ($data['gcs_e'] ?? 0);
        $m = (int) ($data['gcs_m'] ?? 0);
        $v = (int) ($data['gcs_v'] ?? 0);

        $total = ($e && $m && $v) ? $e + $m + $v : null;

        $data['gcs_jumlah'] = $total;
        $data['kesadaran'] = $this->kesadaran($total);

        // DPO hanya berflag Ya / tidak.
        $data['dpo'] = ($data['dpo'] ?? null) === 'Ya' ? 'Ya' : null;

        // Tanpa oksigen, flow rate & cara pemberian tidak berlaku.
        if (($data['pemberian_o2'] ?? '') !== 'Oksigen') {
            $data['flow_rate'] = null;
            $data['cara_oksigen'] = null;
        }

        // Tanpa ETT, persentase oksigen tidak berlaku.
        if (($data['ett'] ?? '') !== 'Ya') {
            $data['persentase_oksigen'] = null;
        }

        return $data;
    }

    /**
     * Pemetaan total GCS ke tingkat kesadaran (AVPU gaya legacy).
     */
    private function kesadaran(?int $total): ?string
    {
        return match (true) {
            $total === null => null,
            $total === 3 => 'Coma',
            $total >= 4 && $total <= 6 => 'Soporus',
            $total >= 7 && $total <= 9 => 'Somnolen',
            $total >= 10 && $total <= 11 => 'Delirium',
            $total >= 12 && $total <= 14 => 'Apatis',
            $total === 15 => 'Compos Mentis',
            default => null,
        };
    }
}
