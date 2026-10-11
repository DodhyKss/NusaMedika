<?php

namespace App\Http\Controllers\EMR\TelaahResep;

use App\Helpers\AksesEhr;
use App\Helpers\EmrHelper;
use App\Helpers\FarmasiHelper;
use App\Http\Controllers\Controller;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * Form Telaah Resep (form 47, slug `telaah_resep`).
 *
 * Dilakukan farmasis SEBELUM obat diserahkan. Tujuannya memastikan resep
 * memenuhi 7 aspek dan mencatat temuan bila tidak.
 *
 * `nomor_resep_id` mengikat telaah ke satu baris resep farmasi
 * (`peresepan_obat.peresepan_obat_id`), meniru legacy yang menanyakan
 * `emr_detail.value = $_GET['resep_id']`.
 *
 * **`telaah_catatan` WAJIB di-isi server bila ada butir tidak tercentang.**
 * Ini dicek di server (bukan hanya aturan `required_if`) karena checkbox
 * kosong tidak terkirim sama sekali — aturan validasi berbasis `required_if`
 * tidak akan pernah tersentuh untuk key yang absen.
 */
class TelaahResepController extends Controller
{
    private const SLUG = 'telaah_resep';

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

        return view('moduls.EMR.TelaahResep.index', compact(
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

            return redirect()->back()->with('success', 'Telaah Resep berhasil disimpan.');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan Telaah Resep: '.$e->getMessage())->withInput();
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

            return redirect()->back()->with('success', 'Telaah Resep berhasil diperbarui.');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui Telaah Resep: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Telaah Resep berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus Telaah Resep: '.$e->getMessage());
        }
    }

    private function kosong(): array
    {
        $d = [
            'nomor_resep_id' => '', 'tanggal_telaah' => '', 'waktu_telaah' => '',
            'petugas_id' => '', 'telaah_catatan' => '', 'keterangan' => '', 'prioritas' => '',
        ];
        foreach (array_keys(FarmasiHelper::KRITERIA_TELAAH_RESEP) as $nomor) {
            $d['telaah_'.$nomor] = 'off';
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
            'telaah_catatan' => 'nullable|string|max:2000',
            'prioritas' => 'nullable|in:BIASA,CITO',
            'keterangan' => 'nullable|string|max:1000',
        ];

        // Tujuh butir telaah: nilai 'on'/'off' (pola hidden off + checkbox on).
        $rules['telaah_1'] = 'nullable|in:on,off';
        $rules['telaah_2'] = 'nullable|in:on,off';
        $rules['telaah_3'] = 'nullable|in:on,off';
        $rules['telaah_4'] = 'nullable|in:on,off';
        $rules['telaah_5'] = 'nullable|in:on,off';
        $rules['telaah_6'] = 'nullable|in:on,off';
        $rules['telaah_7'] = 'nullable|in:on,off';

        $opsional = ['telaah_catatan' => null, 'prioritas' => null, 'keterangan' => null];
        foreach (array_keys(FarmasiHelper::KRITERIA_TELAAH_RESEP) as $nomor) {
            $opsional['telaah_'.$nomor] = 'off';
        }

        return array_merge($opsional, $request->validate($rules));
    }

    /**
     * Server-side: `telaah_catatan` wajib bila ada butir yang TIDAK tercentang.
     *
     * Melempar ValidationException sehingga pesan tampil di `@error` field yang
     * sama dengan validasi biasa.
     */
    private function cekCatatanWajib(array $data): void
    {
        $hasil = FarmasiHelper::telaah('telaah_', FarmasiHelper::KRITERIA_TELAAH_RESEP, $data);

        if (! $hasil['lengkap'] && blank($data['telaah_catatan'] ?? null)) {
            throw ValidationException::withMessages([
                'telaah_catatan' => 'Catatan telaah wajib diisi karena ada butir yang tidak tercentang: '
                    .implode(', ', $hasil['gagal']).'.',
            ]);
        }
    }

    private function filteredData(array $data, int $formId): array
    {
        $data = array_intersect_key($data, array_flip(EmrHelper::objekVariabels($formId)));

        // Normalisasi setiap butir menjadi 'on' / 'off'.
        foreach (array_keys(FarmasiHelper::KRITERIA_TELAAH_RESEP) as $nomor) {
            $variabel = 'telaah_'.$nomor;
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
