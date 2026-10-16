<?php

namespace App\Http\Controllers\EMR\AsesmenRestraint;

use App\Helpers\AksesEhr;
use App\Helpers\EmrHelper;
use App\Http\Controllers\Controller;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Form Asesmen Pasien Restraint (form 121, slug `asesmen_restraint`).
 *
 * Indikasi klinis (gaduh gelisah), order dokter, kebutuhan restraint non-farma &
 * farma, kriteria penghentian, pengkajian fisik/mental, informasi & edukasi
 * (KONSEP_SURVEILANS_INFEKSI.md §8).
 *
 * Aturan server:
 * 1. Minimal satu kebutuhan restraint: salah satu `nonfarm_1..5` ATAU satu
 *    baris `farmak_*` terisi.
 * 2. `tgl_melepas` <-> `nama_petugas_melepas` dua arah (required_with).
 * 3. Field kondisional di-set null di `filteredData()` (baris tetap ada).
 * 4. Catatan KONSEP: field edukasi memakai `tgl_edukasi` (mapping KONSEP
 *    `tgl_edikasi` adalah typo) — konsisten seeder/controller/view.
 */
class AsesmenRestraintController extends Controller
{
    private const SLUG = 'asesmen_restraint';

    private const MAKS_FARMAK = 10;

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

        return view('moduls.EMR.AsesmenRestraint.index', compact(
            'registrasi_detail', 'riwayat', 'historyGrouped', 'emr_data',
            'formAction', 'isEdit', 'deleteAction', 'isView', 'emr_id', 'aksesCrud'
        ));
    }

    public function store(Request $request, $registrasi_detail_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'create'), 403);

        $user = Auth::user();
        if (! $user || ! $user->pegawai_id || ! $user->user_id) {
            return redirect()->back()->with('error', 'Sesi Anda Telah Habis Silahkan Login Kembali!');
        }

        try {
            $data = $this->validated($request, (int) $form_id);
            $data = $this->filteredData($data);

            EmrHelper::insert((int) $form_id, $data, (int) $registrasi_detail_id);

            return redirect()->back()->with('success', 'Asesmen Restraint berhasil disimpan.');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan Asesmen Restraint: '.$e->getMessage())->withInput();
        }
    }

    public function update(Request $request, $registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'update'), 403);

        try {
            $data = $this->validated($request, (int) $form_id);
            $data = $this->filteredData($data);

            EmrHelper::update((int) $emr_id, (int) $form_id, $data);

            return redirect()->back()->with('success', 'Asesmen Restraint berhasil diperbarui.');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui Asesmen Restraint: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Asesmen Restraint berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus Asesmen Restraint: '.$e->getMessage());
        }
    }

    private function validated(Request $request, int $form_id): array
    {
        $rules = [
            'tanggal_asesmen' => 'required|date',
            'waktu_asesmen' => 'required|date_format:H:i',
            'order_dokter' => 'required|string|max:2000',
            'status_mental' => 'required|in:Kooperatif,Gelisah,Menyerang,Tidak ada respon,Lainnya',
            'status_mental_lainnya' => 'required_if:status_mental,Lainnya|nullable|string|max:150',
            'kesadaran' => 'required|in:CM,Somnolen,Apatis,Soporus,Koma',
            'sistolik' => 'required|integer|min:50|max:300',
            'diastolik' => 'required|integer|min:20|max:200',
            'nadi' => 'required|integer|min:20|max:250',
            'pernapasan' => 'required|integer|min:5|max:80',
            'suhu' => 'required|numeric|min:30|max:45',
            'tgl_edukasi' => 'required|date',
            'jam_edukasi' => 'required|date_format:H:i',
            'nama_petugas' => 'required|string|max:150',
            'nama_keluarga' => 'required|string|max:150',
            'nama_petugas_melepas' => 'required_with:tgl_melepas|nullable|string|max:150',
            'tgl_melepas' => 'required_with:nama_petugas_melepas|nullable|date',
            'jam_melepas' => 'nullable|date_format:H:i',
            'stop_lainnya' => 'required_if:stop_6,1|nullable|string|max:500',
        ];

        for ($i = 1; $i <= 5; $i++) {
            $rules['gaduh_'.$i] = 'nullable|in:0,1';
            $rules['nonfarm_'.$i] = 'nullable|boolean';
            $rules['stop_'.$i] = 'nullable|in:0,1';
        }
        $rules['stop_6'] = 'nullable|in:0,1';

        // Loop satu variabel — dibaca harness via expandRuleLoops().
        for ($i = 1; $i <= self::MAKS_FARMAK; $i++) {
            $rules['farmak_'.$i] = 'nullable|string|max:150';
            $rules['farmak_dosis_'.$i] = 'nullable|string|max:100';
        }

        $validator = Validator::make($request->all(), $rules);
        $validator->after(function ($validator) use ($request) {
            // Minimal satu kebutuhan restraint (KONSEP §8): non-farmakologis
            // atau farmakologis.
            $adaNonfarm = false;
            for ($i = 1; $i <= 5; $i++) {
                if ($request->input('nonfarm_'.$i)) {
                    $adaNonfarm = true;
                    break;
                }
            }

            $adaFarmak = false;
            for ($i = 1; $i <= self::MAKS_FARMAK; $i++) {
                if (trim((string) $request->input('farmak_'.$i, '')) !== ''
                    || trim((string) $request->input('farmak_dosis_'.$i, '')) !== '') {
                    $adaFarmak = true;
                    break;
                }
            }

            if (! $adaNonfarm && ! $adaFarmak) {
                $validator->errors()->add('nonfarm_1', 'Tentukan minimal satu kebutuhan restraint non-farmakologis atau farmakologis.');
            }
        });
        $validator->validate();

        return array_merge(
            array_fill_keys(EmrHelper::objekVariabels($form_id), null),
            $validator->validated()
        );
    }

    private function filteredData(array $data): array
    {
        if (($data['status_mental'] ?? null) !== 'Lainnya') {
            $data['status_mental_lainnya'] = null;
        }

        if ((string) ($data['stop_6'] ?? '') !== '1') {
            $data['stop_lainnya'] = null;
        }

        if (trim((string) ($data['tgl_melepas'] ?? '')) === '') {
            $data['nama_petugas_melepas'] = null;
            $data['jam_melepas'] = null;
        }

        return $data;
    }

    private function kosong(): array
    {
        $d = [
            'tanggal_asesmen' => '', 'waktu_asesmen' => '', 'order_dokter' => '',
            'status_mental' => '', 'status_mental_lainnya' => '',
            'kesadaran' => '',
            'sistolik' => '', 'diastolik' => '', 'nadi' => '', 'pernapasan' => '', 'suhu' => '',
            'tgl_edukasi' => '', 'jam_edukasi' => '',
            'nama_petugas' => '', 'nama_keluarga' => '',
            'nama_petugas_melepas' => '', 'tgl_melepas' => '', 'jam_melepas' => '',
            'stop_lainnya' => '',
        ];

        for ($i = 1; $i <= 5; $i++) {
            $d['gaduh_'.$i] = '0';
            $d['nonfarm_'.$i] = null;
            $d['stop_'.$i] = '0';
        }
        $d['stop_6'] = '0';

        for ($i = 1; $i <= self::MAKS_FARMAK; $i++) {
            $d['farmak_'.$i] = '';
            $d['farmak_dosis_'.$i] = '';
        }

        return $d;
    }
}
