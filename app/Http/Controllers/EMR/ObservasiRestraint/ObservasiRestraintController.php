<?php

namespace App\Http\Controllers\EMR\ObservasiRestraint;

use App\Helpers\AksesEhr;
use App\Helpers\EmrHelper;
use App\Http\Controllers\Controller;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Form Observasi Restraint (form 122, slug `observasi_restraint`).
 *
 * Observasi berkala selama restraint dipakai (KONSEP_SURVEILANS_INFEKSI.md §9).
 * **Time-series berbasis `flag_abnormal`** (pola form 110 Monitoring Transfusi):
 * indeks ronde disimpan di `emr_detail.flag_abnormal`, sehingga pembacaan WAJIB
 * memakai `EmrHelper::emrDetailRonde()` — `emrDetailByVariabel()` DILARANG
 * (pluck per variabel menimpa baris antar ronde).
 *
 * Deviasi terdokumentasi: payload FLAT (bukan `ronde[N][var]` bersarang
 * seperti KONSEP §9) — konsisten dengan form 110 & harness E2E.
 * Badge ronde overrun / "Pencatat" per baris = tampilan.
 */
class ObservasiRestraintController extends Controller
{
    private const SLUG = 'observasi_restraint';

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

        $rondes = [];
        $rondeAktif = 1;

        if (empty($emr_id)) {
            $emr_data = EmrHelper::wrapData($this->kosong());
            $formAction = route('emr.form.store', ['form_name' => self::SLUG, 'registrasi_detail_id' => $registrasi_detail_id]);
            $isEdit = false;
            $deleteAction = '';
            $isView = false;
        } else {
            $rondes = EmrHelper::emrDetailRonde((int) $emr_id);

            $rondeRequest = (int) request('ronde', 0);
            $rondeAktif = $rondeRequest > 0 ? $rondeRequest : (int) (max(array_keys($rondes)) ?: 1);

            $emr_data = EmrHelper::wrapData($rondes[$rondeAktif] ?? $this->kosong());
            $formAction = route('emr.form.update', ['form_name' => self::SLUG, 'registrasi_detail_id' => $registrasi_detail_id, 'emr_id' => $emr_id]);
            $isEdit = true;
            $deleteAction = route('emr.form.destroy', ['form_name' => self::SLUG, 'registrasi_detail_id' => $registrasi_detail_id, 'emr_id' => $emr_id]);
            $isView = request('action') === 'view';
        }

        $rondeBaru = $rondeAktif + 1;

        return view('moduls.EMR.ObservasiRestraint.index', compact(
            'registrasi_detail', 'riwayat', 'historyGrouped', 'emr_data',
            'formAction', 'isEdit', 'deleteAction', 'isView', 'emr_id', 'aksesCrud',
            'rondes', 'rondeAktif', 'rondeBaru'
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

            // Ronde pertama: tulis flag_abnormal = 1 (pola form 110).
            $emrId = DB::transaction(function () use ($form_id, $data, $registrasi_detail_id) {
                $id = EmrHelper::insert((int) $form_id, $data, (int) $registrasi_detail_id);
                DB::table('emr_detail')
                    ->where('emr_id', $id)
                    ->whereNull('flag_abnormal')
                    ->update(['flag_abnormal' => 1]);

                return $id;
            });

            return redirect()->back()->with('success', 'Observasi Restraint berhasil disimpan.');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan Observasi Restraint: '.$e->getMessage())->withInput();
        }
    }

    public function update(Request $request, $registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'update'), 403);

        $ronde = max(1, (int) $request->input('ronde', 1));

        try {
            $data = $this->validated($request, (int) $form_id);

            DB::transaction(function () use ($emr_id, $form_id, $data, $ronde) {
                EmrHelper::hapusRonde((int) $emr_id, $ronde);
                EmrHelper::insertRonde((int) $emr_id, (int) $form_id, $data, $ronde);
            });

            return redirect()->back()->with('success', 'Observasi Restraint (Ronde '.$ronde.') berhasil disimpan.');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui Observasi Restraint: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Observasi Restraint berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus Observasi Restraint: '.$e->getMessage());
        }
    }

    private function validated(Request $request, int $form_id): array
    {
        // Payload FLAT (deviasi terdokumentasi dari bentuk `ronde[N][var]` KONSEP).
        $rules = [
            'waktu_observasi' => 'required|date_format:H:i',
            'sistolik' => 'required|integer|min:50|max:300',
            'diastolik' => 'required|integer|min:20|max:200',
            'nadi' => 'required|integer|min:20|max:250',
            'pernapasan' => 'required|integer|min:5|max:80',
            'suhu' => 'required|numeric|min:30|max:45',
            'kognitif' => 'required|string|max:500',
            'luka' => 'required|string|max:1000',
            'asupan_makanan' => 'required|string|max:500',
            'posisi_fiksasi' => 'required|string|max:500',
            'panses_ec' => 'nullable|string|max:2000',
        ];

        $validator = Validator::make($request->all(), $rules);
        $validator->validate();

        return array_merge(
            array_fill_keys(EmrHelper::objekVariabels($form_id), null),
            $validator->validated()
        );
    }

    private function kosong(): array
    {
        return [
            'waktu_observasi' => '', 'sistolik' => '', 'diastolik' => '',
            'nadi' => '', 'pernapasan' => '', 'suhu' => '',
            'kognitif' => '', 'luka' => '', 'asupan_makanan' => '',
            'posisi_fiksasi' => '', 'panses_ec' => '',
        ];
    }
}
