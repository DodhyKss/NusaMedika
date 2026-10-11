<?php

namespace App\Http\Controllers\EMR\ResponEmosi;

use App\Helpers\AksesEhr;
use App\Helpers\EmrHelper;
use App\Helpers\SelectOption;
use App\Http\Controllers\Controller;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * Form Respon Emosi & Status Mental (form 125, slug `respon_emosi`).
 *
 * Asesmen psikososial dasar: respon emosi, status mental, dan kebutuhan
 * perlindungan (mis. korban kekerasan atau terlantar).
 *
 * Ketujuh butir "identifikasi kebutuhan perlindungan" TIDAK punya objek sendiri -
 * mapping form ini hanya punya 4 variabel - sehingga butir tersebut disimpan
 * sebagai JSON di variabel `kebutuhan_perlindungan` bersama flag ya/tidak:
 *
 *   {"butuh":"ya","butir":["CCTV","Ruang Khusus"]}
 *
 * Kolom `value` bertipe TEXT, jadi muat. Validasi tetap memakai bentuk
 * `in:ya,tidak` pada input radio-nya.
 */
class ResponEmosiController extends Controller
{
    private const SLUG = 'respon_emosi';

    /** Checkbox group yang disimpan sebagai JSON array. */
    private const GRUP = ['respon_emosi', 'status_mental'];

    /** Nama input checkbox butir perlindungan - tidak punya objek. */
    private const BUTIR_PERLINDUNGAN = 'identifikasi_perlindungan';

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

        return view('moduls.EMR.ResponEmosi.index', compact(
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

            return redirect()->back()->with('success', 'Respon Emosi berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan Respon Emosi: '.$e->getMessage())->withInput();
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

            return redirect()->back()->with('success', 'Respon Emosi berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui Respon Emosi: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Respon Emosi berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus Respon Emosi: '.$e->getMessage());
        }
    }

    /**
     * Nilai kosong untuk form baru.
     */
    private function kosong(): array
    {
        return [
            'respon_emosi' => '',
            'status_mental' => '',
            'kebutuhan_perlindungan' => '',
            'kebutuhan_teridentifikasi' => '',
        ];
    }

    private function validated(Request $request): array
    {
        $flag = array_column(SelectOption::get('flag_ya_tidak'), 'value');

        $opsional = [
            'kebutuhan_teridentifikasi' => null,
            self::BUTIR_PERLINDUNGAN => null,
        ];

        return array_merge($opsional, $request->validate([
            'respon_emosi' => ['required', 'array', 'min:1'],
            'respon_emosi.*' => 'string|max:255',

            'status_mental' => ['required', 'array', 'min:1'],
            'status_mental.*' => 'string|max:255',

            'kebutuhan_perlindungan' => ['required', Rule::in($flag)],
            'kebutuhan_teridentifikasi' => [
                'nullable',
                'required_if:kebutuhan_perlindungan,ya',
                Rule::in(array_column(SelectOption::get('kebutuhan_teridentifikasi'), 'value')),
            ],

            self::BUTIR_PERLINDUNGAN => ['nullable', 'array'],
            self::BUTIR_PERLINDUNGAN.'.*' => 'string|max:255',
        ]));
    }

    /**
     * Hanya field terpetakan yang disimpan. Grup kosong menjadi null, butir
     * perlindungan digabung ke dalam variabel `kebutuhan_perlindungan` sebagai
     * JSON, dan `kebutuhan_teridentifikasi` dibuang bila tidak ada kebutuhan
     * perlindungan.
     */
    private function filteredData(array $data, int $formId): array
    {
        // Butir perlindungan tidak punya objek: baca SEBELUM array_intersect_key.
        $butir = $data[self::BUTIR_PERLINDUNGAN] ?? [];
        $butir = is_array($butir) ? array_values($butir) : [];

        $data = array_intersect_key($data, array_flip(EmrHelper::objekVariabels($formId)));

        foreach (self::GRUP as $grup) {
            $nilai = $data[$grup] ?? null;
            $data[$grup] = is_array($nilai) && $nilai !== [] ? $nilai : null;
        }

        $butuh = ($data['kebutuhan_perlindungan'] ?? '') === 'ya';
        $data['kebutuhan_perlindungan'] = json_encode([
            'butuh' => $butuh ? 'ya' : 'tidak',
            'butir' => $butuh ? $butir : [],
        ]);

        if (! $butuh) {
            $data['kebutuhan_teridentifikasi'] = null;
        }

        return $data;
    }
}
