<?php

namespace App\Http\Controllers\EMR\DietPasien;

use App\Helpers\AksesEhr;
use App\Helpers\EmrHelper;
use App\Http\Controllers\Controller;
use App\Models\MasterDiagnosaDiet;
use App\Models\MasterDiet;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Form Diet Pasien (form 43, slug `diet_pasien`).
 *
 * Menetapkan jenis diet yang dipakai pasien beserta faktor penyakit yang
 * mendasarinya. Sumber pilihan: master `master_diet` & `master_diagnosa_diet`
 * (prasyarat PRE-05).
 *
 * **Field multi-pilih tanpa tipe array.** NusaMedika tidak punya tipe array di
 * `emr_detail`, dan legacy yang memakai `detail_emr[diagnosa_gizi][]` (N baris
 * dengan `variabel` sama) adalah pola yang DILARANG: `emrDetailByVariabel()`
 * melakukan `pluck('value','variabel')` sehingga baris kedua dan seterusnya
 * tertimpa dan HILANG saat form dibuka lagi.
 * Solusi: **satu baris, nilai dipisah koma** (`"DM tipe 2,Hematuria"`), lalu
 * dipecah lagi di view dengan `explode(',', ...)`.
 */
class DietPasienController extends Controller
{
    private const SLUG = 'diet_pasien';

    /** Prefix input HTML multi-pilih (dipisah koma sebelum disimpan). */
    private const INPUT_EDUKASI = 'jenis_diagnosa_diet[]';

    public function index($registrasi_detail_id, $emr_id = null)
    {
        $registrasi_detail = RegistrasiDetail::with('registrasi.pasien')->findOrFail($registrasi_detail_id);

        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'read'), 403);

        $aksesCrud = AksesEhr::flags((int) $form_id);
        $riwayat = EmrHelper::emrList((int) $form_id, (int) $registrasi_detail->registrasi_id);
        $historyGrouped = EmrHelper::historyKunjunganGrouped($registrasi_detail);

        $daftarDiet = MasterDiet::aktif()->orderBy('kode_diet')->get();
        $daftarDiagnosa = MasterDiagnosaDiet::aktif()->orderBy('kode_diagnosa_diet')->get();

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

        return view('moduls.EMR.DietPasien.index', compact(
            'registrasi_detail', 'riwayat', 'historyGrouped', 'emr_data',
            'formAction', 'isEdit', 'deleteAction', 'isView', 'emr_id', 'aksesCrud',
            'daftarDiet', 'daftarDiagnosa'
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

            return redirect()->back()->with('success', 'Diet Pasien berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan Diet Pasien: '.$e->getMessage())->withInput();
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

            return redirect()->back()->with('success', 'Diet Pasien berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui Diet Pasien: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Diet Pasien berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus Diet Pasien: '.$e->getMessage());
        }
    }

    private function kosong(): array
    {
        return [
            'jenis_diet' => '',
            'jenis_diagnosa_diet' => '',
            'keterangan' => '',
        ];
    }

    private function validated(Request $request): array
    {
        $rules = [
            // Divalidasi terhadap `nama_diet` (bukan kode) karena itu yang
            // disimpan di emr_detail — lihat MasterDiet.
            'jenis_diet' => 'required|string|exists:master_diet,nama_diet',
            // dikirim sebagai array dari checkbox, disimpan comma-separated.
            'jenis_diagnosa_diet' => 'required|array|min:1',
            'jenis_diagnosa_diet.*' => 'required|string|max:150',
            'keterangan' => 'nullable|string|max:1000',
        ];

        return array_merge(
            ['keterangan' => null],
            $request->validate($rules)
        );
    }

    private function filteredData(array $data, int $formId): array
    {
        $data = array_intersect_key($data, array_flip(EmrHelper::objekVariabels($formId)));

        // Array → satu string comma-separated (lihat docblock kelas).
        if (is_array($data['jenis_diagnosa_diet'] ?? null)) {
            $data['jenis_diagnosa_diet'] = implode(',', array_filter(
                array_map('trim', $data['jenis_diagnosa_diet']),
                fn ($x) => $x !== ''
            ));
        }

        foreach ($data as $key => $value) {
            if (is_string($value) && trim($value) === '') {
                $data[$key] = null;
            }
        }

        return $data;
    }
}
