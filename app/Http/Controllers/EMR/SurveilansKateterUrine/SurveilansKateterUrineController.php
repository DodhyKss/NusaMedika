<?php

namespace App\Http\Controllers\EMR\SurveilansKateterUrine;

use App\Helpers\AksesEhr;
use App\Helpers\EmrHelper;
use App\Http\Controllers\Controller;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Form Surveilans Pasien Terpasang Kateter Urine (form 118,
 * slug `surveilans_kateter_urine`).
 *
 * Bundle pencegahan CA-UTI: 11 item pencegahan + 7 gejala ISK
 * (KONSEP_SURVEILANS_INFEKSI.md §5).
 *
 * Aturan server:
 * 1. Grup jenis (Silikon/Folly) dan grup nomor kateter: tepat SATU `Ya`;
 *    grup jenis dengan nol `Ya` diizinkan bila `jenis_kath_lainnya` terisi.
 * 2. Badge "Kriteria CA-UTI terpenuhi" = informasional di tampilan JS,
 *    TIDAK memblokir penyimpanan (KONSEP §5).
 */
class SurveilansKateterUrineController extends Controller
{
    private const SLUG = 'surveilans_kateter_urine';

    private const JAWABAN = ['Ya', 'Tidak', 'N/A'];

    private const JENIS = ['jenis_kath_silikon', 'jenis_kath_folly'];

    private const NOMOR = ['no_kath_6', 'no_kath_8', 'no_kath_10', 'no_kath_12', 'no_kath_14'];

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

        return view('moduls.EMR.SurveilansKateterUrine.index', compact(
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

            return redirect()->back()->with('success', 'Surveilans Kateter Urine berhasil disimpan.');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan Surveilans Kateter Urine: '.$e->getMessage())->withInput();
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

            return redirect()->back()->with('success', 'Surveilans Kateter Urine berhasil diperbarui.');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui Surveilans Kateter Urine: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Surveilans Kateter Urine berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus Surveilans Kateter Urine: '.$e->getMessage());
        }
    }

    private function validated(Request $request, int $form_id): array
    {
        $rules = [
            'tanggal_evaluasi' => 'required|date',
            'waktu_evaluasi' => 'required|date_format:H:i',
            'jenis_kath_lainnya' => 'nullable|string|max:500',
            'tanggal_pasang' => 'required|date',
            'tanggal_lepas' => 'nullable|date|after_or_equal:tanggal_pasang',
        ];

        foreach (array_merge(self::JENIS, self::NOMOR) as $key) {
            $rules[$key] = 'required|in:'.implode(',', self::JAWABAN);
        }

        for ($i = 1; $i <= 11; $i++) {
            $rules['isk_'.$i] = 'required|in:'.implode(',', self::JAWABAN);
        }
        for ($i = 1; $i <= 7; $i++) {
            $rules['isk_gejala_'.$i] = 'required|in:'.implode(',', self::JAWABAN);
        }

        $validator = Validator::make($request->all(), $rules);
        $validator->after(function ($validator) use ($request) {
            $this->cekSatuYa($validator, $request, self::JENIS, 'jenis_kath_lainnya', 'jenis kateter');
            $this->cekSatuYa($validator, $request, self::NOMOR, null, 'nomor kateter');
        });
        $validator->validate();

        return array_merge(
            array_fill_keys(EmrHelper::objekVariabels($form_id), null),
            $validator->validated()
        );
    }

    private function cekSatuYa($validator, Request $request, array $keys, ?string $lainnyaKey, string $label): void
    {
        $ya = count(array_filter($keys, fn ($k) => $request->input($k) === 'Ya'));

        if ($ya > 1) {
            $validator->errors()->add($keys[0], "Hanya satu {$label} boleh bernilai Ya.");

            return;
        }

        if ($ya === 0) {
            if ($lainnyaKey !== null && $request->filled($lainnyaKey)) {
                return;
            }
            $validator->errors()->add($lainnyaKey ?? $keys[0], $lainnyaKey
                ? "Pilih salah satu {$label} atau isi kolom lainnya."
                : "Pilih salah satu {$label}.");
        }
    }

    private function filteredData(array $data): array
    {
        $ya = count(array_filter(self::JENIS, fn ($k) => ($data[$k] ?? null) === 'Ya'));
        if ($ya > 0) {
            $data['jenis_kath_lainnya'] = null;
        }

        return $data;
    }

    private function kosong(): array
    {
        $d = [
            'tanggal_evaluasi' => '', 'waktu_evaluasi' => '',
            'jenis_kath_lainnya' => '',
            'tanggal_pasang' => '', 'tanggal_lepas' => '',
        ];

        foreach (array_merge(self::JENIS, self::NOMOR) as $key) {
            $d[$key] = '';
        }
        for ($i = 1; $i <= 11; $i++) {
            $d['isk_'.$i] = '';
        }
        for ($i = 1; $i <= 7; $i++) {
            $d['isk_gejala_'.$i] = '';
        }

        return $d;
    }
}
