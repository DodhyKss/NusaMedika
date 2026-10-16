<?php

namespace App\Http\Controllers\EMR\SurveilansCvcPicc;

use App\Helpers\AksesEhr;
use App\Helpers\EmrHelper;
use App\Http\Controllers\Controller;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Form Surveilans Pasien Terpasang CVC/PICC (form 116, slug `surveilans_cvc_picc`).
 *
 * Bundle pencegahan CLABSI harian (KONSEP_SURVEILANS_INFEKSI.md §3).
 *
 * Aturan server:
 * 1. Grup lokasi / nomor kateter / lumen: tepat SATU anggota bernilai `Ya`
 *    (bila tanpa kolom "lainnya", minimal satu wajib `Ya`); bila nol `Ya`
 *    pada grup lokasi/kateter, kolom teks "lainnya" wajib diisi.
 * 2. `tanggal_lepas` hanya bila `bsi_lepas = Ya` (dikosongkan di filteredData).
 * 3. Butir `bsi_1..13` WAJIB bersuffix — 13 butir dipetakan ke 3 objek.
 */
class SurveilansCvcPiccController extends Controller
{
    private const SLUG = 'surveilans_cvc_picc';

    private const JAWABAN = ['Ya', 'Tidak', 'N/A'];

    private const LOKASI = ['lokasi_subclavia', 'lokasi_jugularis', 'lokasi_femoralis'];

    private const CATH = ['cath_12', 'cath_7', 'cath_5', 'cath_4', 'cath_3'];

    private const LUMEN = ['cvc_4lumen', 'cvc_3lumen', 'cvc_2lumen', 'cvc_1lumen'];

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

        return view('moduls.EMR.SurveilansCvcPicc.index', compact(
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

            return redirect()->back()->with('success', 'Surveilans CVC/PICC berhasil disimpan.');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan Surveilans CVC/PICC: '.$e->getMessage())->withInput();
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

            return redirect()->back()->with('success', 'Surveilans CVC/PICC berhasil diperbarui.');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui Surveilans CVC/PICC: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Surveilans CVC/PICC berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus Surveilans CVC/PICC: '.$e->getMessage());
        }
    }

    private function validated(Request $request, int $form_id): array
    {
        $rules = [
            'tanggal_evaluasi' => 'required|date',
            'waktu_evaluasi' => 'required|date_format:H:i',
            'lokasi_lainnya' => 'nullable|string|max:500',
            'cath_lainnya' => 'nullable|string|max:500',
            'tanggal_pasang' => 'nullable|date',
            'tanggal_lepas' => 'nullable|date|after_or_equal:tanggal_pasang',
            'keterangan' => 'nullable|string|max:2000',
        ];

        foreach (array_merge(self::LOKASI, self::CATH, self::LUMEN, ['bsi_pasang', 'bsi_lepas']) as $key) {
            $rules[$key] = 'required|in:'.implode(',', self::JAWABAN);
        }

        for ($butir = 1; $butir <= 13; $butir++) {
            $rules['bsi_'.$butir] = 'required|in:'.implode(',', self::JAWABAN);
        }

        $validator = Validator::make($request->all(), $rules);
        $validator->after(function ($validator) use ($request) {
            $this->cekSatuYa($validator, $request, self::LOKASI, 'lokasi_lainnya', 'lokasi pemasangan');
            $this->cekSatuYa($validator, $request, self::CATH, 'cath_lainnya', 'nomor kateter');
            $this->cekSatuYa($validator, $request, self::LUMEN, null, 'jenis lumen');
        });
        $validator->validate();

        return array_merge(
            array_fill_keys(EmrHelper::objekVariabels($form_id), null),
            $validator->validated()
        );
    }

    /**
     * Tepat satu anggota grup bernilai `Ya`. Bila grup punya kolom teks
     * "lainnya" ($lainnyaKey), nol `Ya` diizinkan asalkan teks itu terisi.
     */
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

    /**
     * Field kondisional di-set null (bukan di-unset) supaya baris
     * `emr_detail`-nya tetap ada dan `simpan` E2E penuh.
     */
    private function filteredData(array $data): array
    {
        foreach ([[self::LOKASI, 'lokasi_lainnya'], [self::CATH, 'cath_lainnya']] as [$keys, $lainnya]) {
            $ya = count(array_filter($keys, fn ($k) => ($data[$k] ?? null) === 'Ya'));
            if ($ya > 0) {
                $data[$lainnya] = null;
            }
        }

        if (($data['bsi_lepas'] ?? null) !== 'Ya') {
            $data['tanggal_lepas'] = null;
        }

        return $data;
    }

    private function kosong(): array
    {
        $d = [
            'tanggal_evaluasi' => '', 'waktu_evaluasi' => '',
            'lokasi_lainnya' => '', 'cath_lainnya' => '',
            'tanggal_pasang' => '', 'tanggal_lepas' => '', 'keterangan' => '',
        ];

        foreach (array_merge(self::LOKASI, self::CATH, self::LUMEN, ['bsi_pasang', 'bsi_lepas']) as $key) {
            $d[$key] = '';
        }

        for ($butir = 1; $butir <= 13; $butir++) {
            $d['bsi_'.$butir] = '';
        }

        return $d;
    }
}
