<?php

namespace App\Http\Controllers\EMR\VisusMataMcu;

use App\Helpers\AksesEhr;
use App\Helpers\EmrHelper;
use App\Http\Controllers\Controller;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Form Visus Mata MCU (form 72, slug `visus_mata_mcu`).
 *
 * Pengukuran visus Snellen di poli mata. Nilai AKG (visus dasar) dipakai
 * untuk surat keterangan MCU.
 *
 * Terminologi legacy dipertahankan apa adanya (`visus_od_tod`, `visus_od_tos`,
 * `visus_os_pd` — KONSEP_FORM_MCU §6 catatan) dan dibuat OPSIONAL karena nama
 * kolomnya tidak konsisten secara optometris (warisan kolom lama).
 *
 * `visus_od`/`visus_os` (objek 645/646, form 77 Mata MCU) adalah field yang
 * di-BACA dari form ini — di form 77 field itu read-only; form ini menyimpan
 * nilai lengkapnya sendiri (objek 625/626).
 */
class VisusMataMcuController extends Controller
{
    private const SLUG = 'visus_mata_mcu';

    /** Opsi visus non-angka (buta / tidak diuji). */
    private const VISUS_TEKS = ['NPL', 'HD', 'BB', 'FD', 'ED', 'ND', 'CD', 'VK', 'PL', '-'];

    /**
     * Pola format visus: pecahan (`6/6`) atau salah satu kode teks.
     *
     * Ditulis literal sebagai const (bukan string interpolasi) supaya aturan
     * validasi berbentuk ARRAY bisa dibaca utuh oleh harness `tools/lib_pay.php`
     * tanpa perlu mengevaluasi pemanggilan method.
     */
    private const POLA_VISUS = '/^(\d+\/\d+|NPL|HD|BB|FD|ED|ND|CD|VK|PL|\-)$/';

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

        return view('moduls.EMR.VisusMataMcu.index', compact(
            'registrasi_detail', 'riwayat', 'historyGrouped', 'emr_data',
            'formAction', 'isEdit', 'deleteAction', 'isView', 'emr_id', 'aksesCrud'
        ) + ['visusTeks' => self::VISUS_TEKS]);
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

            return redirect()->back()->with('success', 'Visus Mata MCU berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan Visus Mata MCU: '.$e->getMessage())->withInput();
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

            return redirect()->back()->with('success', 'Visus Mata MCU berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui Visus Mata MCU: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Visus Mata MCU berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus Visus Mata MCU: '.$e->getMessage());
        }
    }

    private function kosong(): array
    {
        return [
            'visus_od_avod' => '', 'visus_od_c' => '', 'visus_od_add' => '',
            'visus_od_tod' => '', 'visus_od_tos' => '',
            'visus_os_avos' => '', 'visus_os_c' => '', 'visus_os_add' => '', 'visus_os_pd' => '',
            'tolak' => '',
            'tes_konfrontasi' => '', 'buta_warna' => '',
        ];
    }

    private function validated(Request $request): array
    {
        // Regex memuat karakter `|`, jadi aturan disusun sebagai ARRAY —
        // bentuk string `regex:...|...` akan dipecah validator sebagai
        // pemisah aturan dan gagal parse ("No ending delimiter").
        $visus = ['required', 'string', 'max:10', 'regex:'.self::POLA_VISUS];
        $visusOpsional = ['nullable', 'string', 'max:10', 'regex:'.self::POLA_VISUS];

        $rules = [
            'visus_od_avod' => $visus,
            'visus_od_c' => $visus,
            'visus_od_add' => $visus,
            'visus_od_tod' => $visusOpsional,
            'visus_od_tos' => $visusOpsional,
            'visus_os_avos' => $visus,
            'visus_os_c' => $visus,
            'visus_os_add' => $visus,
            'visus_os_pd' => $visusOpsional,
            'tolak' => 'nullable|string|max:255',
            'tes_konfrontasi' => 'required|string|max:255',
            'buta_warna' => 'required|in:Tidak,Ishihara Test',
        ];

        return $request->validate($rules);
    }

    private function filteredData(array $data, int $formId): array
    {
        $data = array_intersect_key($data, array_flip(EmrHelper::objekVariabels($formId)));

        foreach ($data as $key => $value) {
            if (is_string($value) && trim($value) === '') {
                $data[$key] = null;
            }
        }

        return $data;
    }
}
