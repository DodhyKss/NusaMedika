<?php

namespace App\Http\Controllers\EMR\SuratKeteranganSehat;

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
 * Form Surat Keterangan Sehat (form 126, slug `surat_keterangan_sehat`).
 *
 * Surat keterangan sehat keperluan administrasi (KONSEP_FORMULIR_RUJUKAN.md §4).
 *
 * Aturan server & deviasi terdokumentasi:
 * - `no_surat` TIDAK divalidasi dari input — dibentuk server saat `store()`
 *   dengan format `{urut:02d} / KDPM / SKS / {bulan ROMAWI} / {tahun}`
 *   (legacy `_romawiBulan(date('m'))` = I..XII; `config/app.php` locale `en`
 *   sehingga Carbon `translatedFormat('M')` TIDAK dipakai — "Oct" ≠ "X").
 *   Saat `update()` nilai lama dipertahankan.
 * - `keterangan` = DUA `required_if` bertaut (sintaks KONSEP ber-kutip-koma
 *   `"SEHAT DENGAN CATATAN MEDIS,TIDAK SEHAT"` INVALID — koma memecah argumen
 *   `required_if` jadi nilai terpisah).
 * - Prefill saat BARU saja: bb/tb/sistolik/diastolik dari tanda vital
 *   terakhir pada kunjungan yang sama (objek 8/9/6/7, emr terbaru non-batal);
 *   tetap boleh diubah petugas.
 * - Cetak `print.blade.php` TIDAK diimplementasikan (`printUrl=''`).
 */
class SuratKeteranganSehatController extends Controller
{
    private const SLUG = 'surat_keterangan_sehat';

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
            // Prefill tanda vital terakhir — saat edit nilai tersimpan menang.
            $emr_data = EmrHelper::wrapData(array_merge($this->kosong(), $this->prefillVital((int) $registrasi_detail_id)));
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

        // Enum lokal (3 opsi & 2 opsi) — bukan key SelectOption.
        $opsiFisik = ['SEHAT', 'SEHAT DENGAN CATATAN MEDIS', 'TIDAK SEHAT'];
        $opsiMata = ['Ada', 'Tidak Ada'];

        return view('moduls.EMR.SuratKeteranganSehat.index', compact(
            'registrasi_detail', 'riwayat', 'historyGrouped', 'emr_data',
            'formAction', 'isEdit', 'deleteAction', 'isView', 'emr_id', 'aksesCrud',
            'opsiFisik', 'opsiMata'
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

            // Nomor surat dibentuk server — nilai browser diabaikan.
            $data['no_surat'] = $this->nomorSurat();

            EmrHelper::insert((int) $form_id, $data, (int) $registrasi_detail_id);

            return redirect()->back()->with('success', 'Surat Keterangan Sehat berhasil disimpan.');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan Surat Keterangan Sehat: '.$e->getMessage())->withInput();
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

            // Nomor surat lama dipertahankan saat edit.
            $data['no_surat'] = EmrHelper::emrDetailByVariabel((int) $emr_id)['no_surat'] ?? $this->nomorSurat();

            EmrHelper::update((int) $emr_id, (int) $form_id, $data);

            return redirect()->back()->with('success', 'Surat Keterangan Sehat berhasil diperbarui.');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui Surat Keterangan Sehat: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Surat Keterangan Sehat berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus Surat Keterangan Sehat: '.$e->getMessage());
        }
    }

    private function validated(Request $request, int $form_id): array
    {
        $rules = [
            'pemeriksaan_fisik' => 'required|in:SEHAT,SEHAT DENGAN CATATAN MEDIS,TIDAK SEHAT',
            'mata' => 'required|in:Ada,Tidak Ada',
            // Dua required_if bertaut (bukan satu nilai ber-kutip-koma).
            'keterangan' => 'required_if:pemeriksaan_fisik,SEHAT DENGAN CATATAN MEDIS|required_if:pemeriksaan_fisik,TIDAK SEHAT|nullable|string|max:1000',
            'bb' => 'required|numeric|min:1|max:400',
            'tb' => 'required|numeric|min:30|max:250',
            'sistolik' => 'required|integer|min:50|max:300',
            'diastolik' => 'required|integer|min:20|max:200',
        ];

        $validator = Validator::make($request->all(), $rules);
        $validator->validate();

        return array_merge(
            array_fill_keys(EmrHelper::objekVariabels($form_id), null),
            $validator->validated()
        );
    }

    /**
     * Tidak ada aturan kondisional yang dibuang — semua field selalu ikut.
     */
    private function filteredData(array $data): array
    {
        return $data;
    }

    /**
     * Nomor surat server-side: `{urut:02d} / KDPM / SKS / {bulan romawi} / {tahun}`.
     * Bulan ROMAWI (I..XII) mengikuti legacy `_romawiBulan(date('m'))` —
     * locale app `en` membuat `translatedFormat('M')` menghasilkan "Oct".
     * `now()` PHP dipakai, bukan `NOW()` SQL (server database bisa berjalan UTC).
     */
    private function nomorSurat(): string
    {
        $urut = (int) DB::table('emr_detail')
            ->where('objek_id', 739)
            ->where(function ($q) {
                $q->whereNull('status_batal')->orWhere('status_batal', 0);
            })
            ->count() + 1;

        $romawi = ['', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
        $bulan = $romawi[(int) now()->format('m')];

        return sprintf('%02d', $urut).' / KDPM / SKS / '.$bulan.' / '.now()->format('Y');
    }

    /**
     * Tanda vital terakhir (bb 8, tb 9, sistolik 6, diastolik 7) pada
     * registrasi_detail yang sama — emr terbaru menang, nilai kosong dilewati.
     * Legacy memakai query `json_agg`; pola yang sama disederhanakan di sini.
     */
    private function prefillVital(int $registrasiDetailId): array
    {
        $rows = DB::table('emr_detail as d')
            ->join('emr as e', 'e.emr_id', '=', 'd.emr_id')
            ->where('e.registrasi_detail_id', $registrasiDetailId)
            ->whereIn('d.objek_id', [6, 7, 8, 9])
            ->whereNotNull('d.value')
            ->where('d.value', '!=', '')
            ->where(function ($q) {
                $q->whereNull('d.status_batal')->orWhere('d.status_batal', 0);
            })
            ->where(function ($q) {
                $q->whereNull('e.status_batal')->orWhere('e.status_batal', 0);
            })
            ->orderByDesc('e.input_time')
            ->orderByDesc('d.emr_detail_id')
            ->select('d.objek_id', 'd.value')
            ->get();

        $hasil = [];
        foreach ($rows as $row) {
            if (! isset($hasil[$row->objek_id])) {
                $hasil[$row->objek_id] = $row->value;
            }
        }

        return [
            'bb' => $hasil[8] ?? '',
            'tb' => $hasil[9] ?? '',
            'sistolik' => $hasil[6] ?? '',
            'diastolik' => $hasil[7] ?? '',
        ];
    }

    private function kosong(): array
    {
        return [
            'no_surat' => '',
            'pemeriksaan_fisik' => '',
            'mata' => '',
            'keterangan' => '',
            'bb' => '',
            'tb' => '',
            'sistolik' => '',
            'diastolik' => '',
        ];
    }
}
