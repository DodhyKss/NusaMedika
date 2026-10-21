<?php

namespace App\Http\Controllers\EMR\TelaahObat;

use App\Helpers\AksesEhr;
use App\Helpers\EmrHelper;
use App\Helpers\FarmasiHelper;
use App\Http\Controllers\Controller;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * Form Telaah Obat (form 48, slug `telaah_obat`).
 *
 * Dilakukan saat proses DISPENSE — berbeda dari telaah resep: yang diperiksa
 * adalah kesesuaian obat yang diberikan dengan pasien, dosis, dan waktu
 * pemberian. 5 kriteria (`FarmasiHelper::KRITERIA_TELAAH_OBAT`).
 *
 * Objek 409/418/419/82 (nomor resep, tanggal/waktu telaah, petugas) DIPAKAI
 * ULANG dari form 47 — maknanya identik.
 *
 * `tobat_catatan` WAJIB diisi server bila ada butir tidak tercentang.
 */
class TelaahObatController extends Controller
{
    private const SLUG = 'telaah_obat';

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

        return view('moduls.EMR.TelaahObat.index', compact(
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

        $data = $this->filteredData($data, (int) $form_id);
        $this->cekCatatanWajib($data);

        try {
            EmrHelper::insert((int) $form_id, $data, (int) $registrasi_detail_id);

            return redirect()->back()->with('success', 'Telaah Obat berhasil disimpan.');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan Telaah Obat: '.$e->getMessage())->withInput();
        }
    }

    public function update(Request $request, $registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'update'), 403);

        $data = $this->validated($request);

        $data = $this->filteredData($data, (int) $form_id);
        $this->cekCatatanWajib($data);

        try {
            EmrHelper::update((int) $emr_id, (int) $form_id, $data);

            return redirect()->back()->with('success', 'Telaah Obat berhasil diperbarui.');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui Telaah Obat: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Telaah Obat berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus Telaah Obat: '.$e->getMessage());
        }
    }

    private function kosong(): array
    {
        $d = [
            'nomor_resep_id' => '', 'tanggal_telaah' => '', 'waktu_telaah' => '',
            'petugas_id' => '', 'tobat_catatan' => '', 'keterangan' => '',
        ];
        foreach (array_keys(FarmasiHelper::KRITERIA_TELAAH_OBAT) as $nomor) {
            $d['tobat_'.$nomor] = 'off';
        }

        return $d;
    }

    private function validated(Request $request): array
    {
        $rules = [
            'nomor_resep_id' => 'required|integer|exists:peresepan_obat,peresepan_obat_id',
            'tanggal_telaah' => 'required|date',
            'waktu_telaah' => 'required|date_format:H:i',
            'petugas_id' => 'required|integer|exists:pegawai,pegawai_id',
            'tobat_catatan' => 'nullable|string|max:2000',
            'keterangan' => 'nullable|string|max:1000',
        ];

        $rules['tobat_1'] = 'nullable|in:on,off';
        $rules['tobat_2'] = 'nullable|in:on,off';
        $rules['tobat_3'] = 'nullable|in:on,off';
        $rules['tobat_4'] = 'nullable|in:on,off';
        $rules['tobat_5'] = 'nullable|in:on,off';

        $opsional = ['tobat_catatan' => null, 'keterangan' => null];
        foreach (array_keys(FarmasiHelper::KRITERIA_TELAAH_OBAT) as $nomor) {
            $opsional['tobat_'.$nomor] = 'off';
        }

        return array_merge($opsional, $request->validate($rules));
    }

    private function cekCatatanWajib(array $data): void
    {
        $hasil = FarmasiHelper::telaah('tobat_', FarmasiHelper::KRITERIA_TELAAH_OBAT, $data);

        if (! $hasil['lengkap'] && blank($data['tobat_catatan'] ?? null)) {
            throw ValidationException::withMessages([
                'tobat_catatan' => 'Catatan telaah obat wajib diisi karena ada butir yang tidak tercentang: '
                    .implode(', ', $hasil['gagal']).'.',
            ]);
        }
    }

    private function filteredData(array $data, int $formId): array
    {
        $data = array_intersect_key($data, array_flip(EmrHelper::objekVariabels($formId)));

        foreach (array_keys(FarmasiHelper::KRITERIA_TELAAH_OBAT) as $nomor) {
            $variabel = 'tobat_'.$nomor;
            $data[$variabel] = FarmasiHelper::nilaiTelaah($data[$variabel] ?? null);
        }

        foreach ($data as $key => $value) {
            if (is_string($value) && trim($value) === '') {
                $data[$key] = null;
            }
        }

        return $data;
    }
}
