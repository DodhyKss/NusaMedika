<?php

namespace App\Http\Controllers\EMR\ChecklistKesiapanAnestesi;

use App\Helpers\AksesEhr;
use App\Helpers\EmrHelper;
use App\Http\Controllers\Controller;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

/**
 * Form Check List Kesiapan Anestesi (form 90, slug `checklist_kesiapan_anestesi`).
 *
 * Daftar periksa kesiapan induksi anestesi (KONSEP §8): mesin/alat,
 * oksigen & ventilasi, monitor & akses vaskular, farmasi, persetujuan &
 * dokumentasi. Hanya penilaian Ok / Tidak Ok (bukan checklist tercentang).
 *
 * Dua guard server:
 *  - `consent_dokumentasi_ok = 'Tidak Ok'` ⇒ store/update DITOLAK (hard gate
 *    persetujuan — keputusan keselamatan).
 *  - `catatan_kesiapan` wajib min. 5 karakter bila ada butir "Tidak Ok".
 */
class ChecklistKesiapanAnestesiController extends Controller
{
    private const SLUG = 'checklist_kesiapan_anestesi';

    private const BUTIR = [
        'mesin_anestesi_ok' => 'Mesin & Alat Anestesi',
        'oksigen_ventilasi_ok' => 'Oksigen & Ventilasi',
        'monitor_akses_ok' => 'Monitor & Akses Vaskular',
        'farmasi_persiapan_ok' => 'Farmasi & Persiapan',
        'consent_dokumentasi_ok' => 'Persetujuan & Dokumentasi',
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

        return view('moduls.EMR.ChecklistKesiapanAnestesi.index', compact(
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

        $data = $this->validated($request, (int) $form_id);

        try {
            EmrHelper::insert((int) $form_id, $data, (int) $registrasi_detail_id);

            return redirect()->back()->with('success', 'Check List Kesiapan Anestesi berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan Check List Kesiapan: '.$e->getMessage())->withInput();
        }
    }

    public function update(Request $request, $registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'update'), 403);

        $data = $this->validated($request, (int) $form_id);

        try {
            EmrHelper::update((int) $emr_id, (int) $form_id, $data);

            return redirect()->back()->with('success', 'Check List Kesiapan Anestesi berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui Check List Kesiapan: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Check List Kesiapan Anestesi berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus Check List Kesiapan: '.$e->getMessage());
        }
    }

    private function validated(Request $request, int $form_id): array
    {
        $rules = [
            'tanggal_checklist' => 'required|date',
            'jam_checklist' => 'required|date_format:H:i',
            'mesin_anestesi_ok' => 'required|in:Ok,Tidak Ok',
            'mesin_anestesi_keterangan' => 'required_if:mesin_anestesi_ok,Tidak Ok|string|max:2000',
            'oksigen_ventilasi_ok' => 'required|in:Ok,Tidak Ok',
            'monitor_akses_ok' => 'required|in:Ok,Tidak Ok',
            'farmasi_persiapan_ok' => 'required|in:Ok,Tidak Ok',
            'consent_dokumentasi_ok' => 'required|in:Ok,Tidak Ok',
            'catatan_kesiapan' => 'required|string|max:2000',
        ];

        $validator = Validator::make($request->all(), $rules);

        $validator->after(function ($validator) use ($request) {
            $input = $request->all();

            // Hard gate KONSEP §8: persetujuan & dokumentasi "Tidak Ok" ⇒
            // store/update ditolak (bukan sekadar pesan).
            if (($input['consent_dokumentasi_ok'] ?? null) === 'Tidak Ok') {
                $validator->errors()->add(
                    'consent_dokumentasi_ok',
                    'Persetujuan & dokumentasi Tidak Ok — check list kesiapan anestesi tidak dapat disimpan.'
                );
            }

            // Catatan wajib menjelaskan ketidaksapan bila ada butir "Tidak Ok".
            $adaTidakOk = false;
            foreach (array_keys(self::BUTIR) as $k) {
                if (($input[$k] ?? null) === 'Tidak Ok') {
                    $adaTidakOk = true;
                    break;
                }
            }
            if ($adaTidakOk && mb_strlen(trim((string) ($input['catatan_kesiapan'] ?? ''))) < 5) {
                $validator->errors()->add(
                    'catatan_kesiapan',
                    'Catatan kesiapan wajib diisi (min. 5 karakter) bila ada butir berstatus Tidak Ok.'
                );
            }
        });

        $validator->validate();

        return array_merge(
            array_fill_keys(EmrHelper::objekVariabels($form_id), null),
            $validator->validated()
        );
    }

    private function kosong(): array
    {
        return [
            'tanggal_checklist' => '',
            'jam_checklist' => '',
            'mesin_anestesi_ok' => '',
            'mesin_anestesi_keterangan' => '',
            'oksigen_ventilasi_ok' => '',
            'monitor_akses_ok' => '',
            'farmasi_persiapan_ok' => '',
            'consent_dokumentasi_ok' => '',
            'catatan_kesiapan' => '',
        ];
    }
}
