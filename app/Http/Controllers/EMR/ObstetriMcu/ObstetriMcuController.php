<?php

namespace App\Http\Controllers\EMR\ObstetriMcu;

use App\Helpers\AksesEhr;
use App\Helpers\EmrHelper;
use App\Http\Controllers\Controller;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Form Obstetri MCU (form 73, slug `obstetri_mcu`).
 *
 * Anamnesis obstetri untuk pasien perempuan. Pasien laki-laki memilih `L` —
 * seluruh field obstetri lalu DIKOSONGKAN & DIBUANG dari payload
 * (`filteredData()`, bukan sekadar tidak divalidasi).
 *
 * Ketika `jenis_kelamin_pasien = P`, seluruh field obstetri wajib dan
 * `kehamilan_g >= kehamilan_p + kehamilan_a` divalidasi.
 * `waktu_paps` tidak boleh di masa depan.
 */
class ObstetriMcuController extends Controller
{
    private const SLUG = 'obstetri_mcu';

    /** Variabel obstetri yang hanya berlaku untuk `jenis_kelamin_pasien = P`. */
    private const VARIABEL_OBSTETRI = [
        'menarche', 'haid_terakhir', 'siklus_haid', 'nyeri_haid',
        'kb_metode', 'kb_aktif',
        'kehamilan_g', 'kehamilan_p', 'kehamilan_a', 'keguguran_terakhir',
        'keguguran_keterangan', 'hamil_terakhir', 'hamil_terakhir_lama',
        'jml_anak_lahir', 'jml_anak_hidup',
        'waktu_paps', 'sc', 'coitus', 'coitus_terakhir',
        'discharge', 'gatal', 'hari', 'hormon', 'normal',
    ];

    /** Opsi metode kontrasepsi. */
    private const KB_METODE = [
        'Tidak Ada', 'Pil', 'IUD', 'Suntik', 'Implan', 'Kondom', 'Lainnya',
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

        return view('moduls.EMR.ObstetriMcu.index', compact(
            'registrasi_detail', 'riwayat', 'historyGrouped', 'emr_data',
            'formAction', 'isEdit', 'deleteAction', 'isView', 'emr_id', 'aksesCrud'
        ) + ['kbMetode' => self::KB_METODE, 'variabelObstetri' => self::VARIABEL_OBSTETRI]);
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

            return redirect()->back()->with('success', 'Obstetri MCU berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan Obstetri MCU: '.$e->getMessage())->withInput();
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

            return redirect()->back()->with('success', 'Obstetri MCU berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui Obstetri MCU: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Obstetri MCU berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus Obstetri MCU: '.$e->getMessage());
        }
    }

    private function kosong(): array
    {
        $d = ['jenis_kelamin_pasien' => 'P'];
        foreach (self::VARIABEL_OBSTETRI as $v) {
            $d[$v] = '';
        }

        return $d;
    }

    private function validated(Request $request): array
    {
        // Field obstetri memakai `required_if:jenis_kelamin_pasien,P` — bentuk
        // string literal (bukan closure) supaya aturannya terbaca oleh harness
        // `tools/lib_pay.php` dan konsisten dengan konvensi Fase P2/P3.
        $rules = [
            'jenis_kelamin_pasien' => 'required|in:L,P',
            'menarche' => 'required_if:jenis_kelamin_pasien,P|nullable|string|max:10',
            'haid_terakhir' => 'required_if:jenis_kelamin_pasien,P|nullable|date',
            'siklus_haid' => 'required_if:jenis_kelamin_pasien,P|nullable|string|max:100',
            'nyeri_haid' => 'required_if:jenis_kelamin_pasien,P|nullable|in:Ya,Tidak',
            'kb_metode' => 'required_if:jenis_kelamin_pasien,P|nullable|in:Tidak Ada,Pil,IUD,Suntik,Implan,Kondom,Lainnya',
            'kb_aktif' => 'required_if:jenis_kelamin_pasien,P|nullable|in:Ya,Tidak',
            'kehamilan_g' => 'required_if:jenis_kelamin_pasien,P|nullable|integer|between:0,30',
            'kehamilan_p' => 'required_if:jenis_kelamin_pasien,P|nullable|integer|between:0,30',
            'kehamilan_a' => 'required_if:jenis_kelamin_pasien,P|nullable|integer|between:0,30',
            'keguguran_terakhir' => 'nullable|date|before_or_equal:today',
            'keguguran_keterangan' => 'nullable|string|max:255',
            'hamil_terakhir' => 'nullable|date|before_or_equal:today',
            'hamil_terakhir_lama' => 'nullable|string|max:100',
            'jml_anak_lahir' => 'required_if:jenis_kelamin_pasien,P|nullable|integer|between:0,30',
            'jml_anak_hidup' => 'required_if:jenis_kelamin_pasien,P|nullable|integer|between:0,30',
            'waktu_paps' => 'nullable|date|before_or_equal:today',
            'sc' => 'nullable|string|max:255',
            'coitus' => 'nullable|string|max:255',
            'coitus_terakhir' => 'nullable|date|before_or_equal:today',
            'discharge' => 'nullable|string|max:255',
            'gatal' => 'nullable|string|max:255',
            'hari' => 'nullable|string|max:100',
            'hormon' => 'nullable|string|max:255',
            'normal' => 'nullable|string|max:255',
        ];

        // Validasi lintas-field: G >= P + A hanya bila perempuan & semuanya terisi.
        $validator = \Validator::make($request->all(), $rules);
        if ($request->input('jenis_kelamin_pasien') === 'P') {
            $validator->after(function ($validator) use ($request) {
                $g = $request->input('kehamilan_g');
                $p = $request->input('kehamilan_p');
                $a = $request->input('kehamilan_a');
                if (is_numeric($g) && is_numeric($p) && is_numeric($a) && (int) $g < (int) $p + (int) $a) {
                    $validator->errors()->add('kehamilan_g', 'Jumlah kehamilan (G) tidak boleh kurang dari jumlah persalinan (P) + abortus (A).');
                }
            });
        }
        $validator->validate();

        return array_merge(
            array_fill_keys(self::VARIABEL_OBSTETRI, null),
            $validator->validated()
        );
    }

    private function filteredData(array $data, int $formId): array
    {
        $data = array_intersect_key($data, array_flip(EmrHelper::objekVariabels($formId)));

        // Pasien laki-laki: seluruh field obstetri DIKOSONGKAN (null) — bukan
        // unset, supaya baris emr_detail tetap ada dan mapping terverifikasi.
        if (($data['jenis_kelamin_pasien'] ?? 'P') === 'L') {
            foreach (self::VARIABEL_OBSTETRI as $v) {
                $data[$v] = null;
            }
        }

        foreach ($data as $key => $value) {
            if (is_string($value) && trim($value) === '') {
                $data[$key] = null;
            }
        }

        return $data;
    }
}
