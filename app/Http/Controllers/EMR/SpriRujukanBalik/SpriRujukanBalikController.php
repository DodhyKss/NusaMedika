<?php

namespace App\Http\Controllers\EMR\SpriRujukanBalik;

use App\Helpers\AksesEhr;
use App\Helpers\DiagnosisHelper;
use App\Helpers\EmrHelper;
use App\Http\Controllers\Controller;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Form SPRI & Program Rujukan Balik (form 128, slug `spri_rujukan_balik`).
 *
 * Dua dokumen dalam satu form (KONSEP_FORMULIR_RUJUKAN.md §6):
 * SPRI (Surat Pernyataan Rawat Inap) & Program Rujukan Balik (PRB).
 *
 * Aturan server & deviasi terdokumentasi:
 * - Prefill saat BARU saja: `anamnesis` ← SOAP `subjective`,
 *   `pemeriksaan_fisik` ← SOAP `objective` (form 2), `diagnosis` ←
 *   DiagnosisHelper::terakhir (objek 40 — form 2 tak punya variabel
 *   `diagnosis`), `alasan` ← teks default KONSEP. Edit → nilai tersimpan.
 * - `terapi_obat` prefill "resep terakhir" TIDAK diimplementasikan
 *   (FarmasiHelper tidak punya API ringkas) — textarea kosong, deviasi.
 * - `filteredData()`: SPRI → `tanggal_kontrol` null (PRB → `no_sep`/
 *   `no_rujukan` dipertahankan, sesuai KONSEP).
 * - `required_if:jenis_formulir,PROGRAM RUJUKAN BALIK` tetap literal —
 *   nilai berspasi aman untuk Laravel/harness (yang pecah argumen hanya
 *   koma), saran `Rule::in()` KONSEP tidak diperlukan.
 * - Cetak `print.blade.php` TIDAK diimplementasikan (`printUrl=''`).
 */
class SpriRujukanBalikController extends Controller
{
    private const SLUG = 'spri_rujukan_balik';

    private const ALASAN_PRB = 'Pasien telah stabil, mohon dapat dilanjutkan pengobatan di Faskes Tingkat I, Kontrol Kembali ke RS Setelah 3 Bulan';

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
            $emr_data = EmrHelper::wrapData($this->kosong($registrasi_detail));
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

        // Enum lokal 2 opsi — bukan key SelectOption.
        $opsiFormulir = ['SPRI', 'PROGRAM RUJUKAN BALIK'];

        return view('moduls.EMR.SpriRujukanBalik.index', compact(
            'registrasi_detail', 'riwayat', 'historyGrouped', 'emr_data',
            'formAction', 'isEdit', 'deleteAction', 'isView', 'emr_id', 'aksesCrud',
            'opsiFormulir'
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

            return redirect()->back()->with('success', 'SPRI & Program Rujukan Balik berhasil disimpan.');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan SPRI & Program Rujukan Balik: '.$e->getMessage())->withInput();
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

            return redirect()->back()->with('success', 'SPRI & Program Rujukan Balik berhasil diperbarui.');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui SPRI & Program Rujukan Balik: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'SPRI & Program Rujukan Balik berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus SPRI & Program Rujukan Balik: '.$e->getMessage());
        }
    }

    private function validated(Request $request, int $form_id): array
    {
        $rules = [
            'jenis_formulir' => 'required|in:SPRI,PROGRAM RUJUKAN BALIK',
            'no_sep' => 'required_if:jenis_formulir,SPRI|nullable|string|max:50',
            'no_rujukan' => 'nullable|string|max:50',
            'alasan' => 'required|string|max:1000',
            'persiapan_tindakan' => 'required|string|max:1000',
            'rencana_pemeriksaan_penunjang' => 'required|string|max:1000',
            'terapi_obat' => 'required|string|max:2000',
            'anjuran' => 'required|string|max:2000',
            'tanggal_kontrol' => 'required_if:jenis_formulir,PROGRAM RUJUKAN BALIK|nullable|date|after_or_equal:today',
            'dokter_pengarah_id' => 'required|integer|exists:pegawai,pegawai_id',
            'faskes_tujuan' => 'required|string|max:150',
            'diagnosis' => 'required|string|max:2000',
            'anamnesis' => 'required|string|max:4000',
            'pemeriksaan_fisik' => 'required|string|max:4000',
        ];

        $validator = Validator::make($request->all(), $rules);
        $validator->validate();

        return array_merge(
            array_fill_keys(EmrHelper::objekVariabels($form_id), null),
            $validator->validated()
        );
    }

    /**
     * SPRI → `tanggal_kontrol` dibuang; PRB → `no_sep`/`no_rujukan` tetap.
     */
    private function filteredData(array $data): array
    {
        if (($data['jenis_formulir'] ?? null) === 'SPRI') {
            $data['tanggal_kontrol'] = null;
        }

        return $data;
    }

    /**
     * Nilai awal form BARU: prefill SOAP (subjective/objective), diagnosa
     * terakhir (objek 40), dan alasan default PRB — semuanya boleh diedit.
     */
    private function kosong($registrasi_detail): array
    {
        $rdid = (int) $registrasi_detail->registrasi_detail_id;
        $soap = EmrHelper::latestValuesByVariabel(2, $rdid, ['subjective', 'objective']);

        return [
            'jenis_formulir' => 'SPRI',
            'no_sep' => '',
            'no_rujukan' => '',
            'alasan' => self::ALASAN_PRB,
            'persiapan_tindakan' => '',
            'rencana_pemeriksaan_penunjang' => '',
            'terapi_obat' => '',
            'anjuran' => '',
            'tanggal_kontrol' => '',
            'dokter_pengarah_id' => '',
            'faskes_tujuan' => '',
            'diagnosis' => DiagnosisHelper::terakhir($rdid),
            'anamnesis' => $soap['subjective'] ?? '',
            'pemeriksaan_fisik' => $soap['objective'] ?? '',
        ];
    }
}
