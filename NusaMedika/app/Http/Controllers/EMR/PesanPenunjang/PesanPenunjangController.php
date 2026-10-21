<?php

namespace App\Http\Controllers\EMR\PesanPenunjang;

use App\Helpers\AksesEhr;
use App\Helpers\EmrHelper;
use App\Helpers\SelectOption;
use App\Http\Controllers\Controller;
use App\Models\RegistrasiDetail;
use App\Models\Tindakan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Form Pesan Bedah / Cathlab / Endoscopy / ESWL (form 127, slug `pesan_penunjang`).
 *
 * Satu form gabungan empat legacy (`pesan_bedah`/`pesan_cathlab`/
 * `pesan_endoscopy`/`pesan_eswl`) dengan selector `jenis_pesan`
 * (KONSEP_FORMULIR_RUJUKAN.md §5).
 *
 * Aturan server & deviasi terdokumentasi:
 * - DEVIASI BESAR: TANPA filter kategori `jenis_pesan` → `jenis_tindakan`
 *   `exists:tindakan,tindakan_id` + guard server `Tindakan::aktif()`.
 *   Master `kategori_tindakan` hanya punya Laboratorium/Radiologi/Lainnya;
 *   legacy memakai tabel `detail_tindakan_bedah` yang tidak ada di skema baru.
 * - `kelas_id` TIDAK dimapping (read-only display dari `pasien_nasabah.hak_kelas_id`).
 * - `alat_khusus` array → `implode(', ')` jadi string SATU baris; dipecah
 *   lagi saat edit (pola dokumen_pendukung form 94).
 * - `unit_pelaksanaan_id` = semua `bagian` aktif (unit cathlab/endoscopy/ESWL
 *   tidak ada di master bagian lokal).
 * - `filteredData()`: `jenis_anestesi` null bila `jenis_pesan` bukan
 *   ENDOSCOPY/ESWL; `alat_khusus` null bila `pasang_alat != 'Ada'`.
 * - Cetak `print.blade.php` TIDAK diimplementasikan (`printUrl=''`).
 */
class PesanPenunjangController extends Controller
{
    private const SLUG = 'pesan_penunjang';

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

        // Opsi panjang dari SelectOption; enum pendek literal di blade/controller.
        $opsiPesan = SelectOption::get('jenis_pesan_penunjang');
        $opsiPraTindakan = SelectOption::get('pra_tindakan_penunjang');
        $opsiAnestesi = SelectOption::get('jenis_anestesi');
        $opsiAlat = SelectOption::get('alat_khusus_penunjang');

        // Unit pelaksanaan: semua bagian aktif (tak ada filter kategori unit).
        $bagians = DB::table('bagian')
            ->where(function ($q) {
                $q->whereNull('status_batal')->orWhere('status_batal', 0);
            })
            ->orderBy('nama_bagian')
            ->get(['bagian_id', 'nama_bagian']);

        // Katalog tindakan aktif — filter kategori TIDAK ada (deviasi §5).
        $tindakans = Tindakan::aktif()
            ->orderBy('nama_tindakan')
            ->get(['tindakan_id', 'nama_tindakan']);

        // Kelas nasabah read-only (TIDAK disimpan — resolusi tampilan saja).
        $kelasNama = DB::table('pasien_nasabah as pn')
            ->join('kelas_ruang as kr', 'kr.kelas_ruang_id', '=', 'pn.hak_kelas_id')
            ->where('pn.pasien_id', $registrasi_detail->registrasi->pasien_id)
            ->where(function ($q) {
                $q->whereNull('pn.status_batal')->orWhere('pn.status_batal', 0);
            })
            ->where(function ($q) {
                $q->whereNull('kr.status_batal')->orWhere('kr.status_batal', 0);
            })
            ->orderByDesc('pn.input_time')
            ->value('kr.nama_kelas_ruang');

        return view('moduls.EMR.PesanPenunjang.index', compact(
            'registrasi_detail', 'riwayat', 'historyGrouped', 'emr_data',
            'formAction', 'isEdit', 'deleteAction', 'isView', 'emr_id', 'aksesCrud',
            'opsiPesan', 'opsiPraTindakan', 'opsiAnestesi', 'opsiAlat',
            'bagians', 'tindakans', 'kelasNama'
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

            return redirect()->back()->with('success', 'Pesan Penunjang berhasil disimpan.');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan Pesan Penunjang: '.$e->getMessage())->withInput();
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

            return redirect()->back()->with('success', 'Pesan Penunjang berhasil diperbarui.');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui Pesan Penunjang: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Pesan Penunjang berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus Pesan Penunjang: '.$e->getMessage());
        }
    }

    private function validated(Request $request, int $form_id): array
    {
        $rules = [
            'jenis_pesan' => 'required|in:BEDAH,CATHLAB,ENDOSCOPY,ESWL',
            'bagian_asal' => 'required|integer|exists:bagian,bagian_id',
            'unit_pelaksanaan_id' => 'required|integer|exists:bagian,bagian_id',
            'diagnosa_medis' => 'required|string|max:2000',
            'jenis_tindakan' => 'required|integer|exists:tindakan,tindakan_id',
            'tgl_rencana_tindakan' => 'required|date|after_or_equal:today',
            'jam_rencana_tindakan' => 'required|date_format:H:i',
            'sc' => 'required|in:Ya,Tidak',
            'sc_emergency' => 'required|in:Ya,Tidak',
            'ic' => 'required|in:Ya,Tidak',
            'jam_keputusan_sc' => 'required_if:sc,Ya|nullable|date_format:H:i',
            'jenis_rawat_rencana' => 'required|in:RI,RJ',
            'tgl_rencana_ranap' => 'required_if:jenis_rawat_rencana,RI|nullable|date',
            'handphone' => 'required|string|max:13',
            'handphone_keluarga' => 'required|string|max:13',
            'pasang_alat' => 'required|in:Tidak Ada,Ada',
            'alat_khusus' => 'required_if:pasang_alat,Ada|nullable|array',
            'pra_tindakan' => 'required|in:'.implode(',', array_column(SelectOption::get('pra_tindakan_penunjang'), 'value')),
            'dokter_operator_id' => 'required|integer|exists:pegawai,pegawai_id',
            'dpjp_utama' => 'required|string|max:200',
            'jenis_anestesi' => 'required_if:jenis_pesan,ENDOSCOPY,ESWL|nullable|in:Umum,Spinal,Epidural,Lokal,Sedasi',
            'bb' => 'nullable|numeric|min:0',
            'tb' => 'nullable|numeric|min:0',
        ];

        $validator = Validator::make($request->all(), $rules);

        // Guard: tindakan harus AKTIF — pengganti filter kategori legacy
        // yang tidak mungkin (kategori_tindakan hanya Lab/Rad/Lain).
        $validator->after(function ($validator) use ($request) {
            $tindakanId = (int) $request->input('jenis_tindakan');
            if ($tindakanId > 0 && ! Tindakan::aktif()->where('tindakan_id', $tindakanId)->exists()) {
                $validator->errors()->add('jenis_tindakan', 'Tindakan yang dipilih tidak valid atau sudah tidak aktif.');
            }
        });

        $validator->validate();

        // Semua variabel mapping selalu ikut (nullable yang tidak dikirim → null)
        // supaya setiap baris emr_detail tercipta dan tampil lagi saat edit.
        $data = array_merge(
            array_fill_keys(EmrHelper::objekVariabels($form_id), null),
            $validator->validated()
        );

        // Array multi-pilih disimpan comma-separated dalam SATU baris.
        if (is_array($data['alat_khusus'] ?? null)) {
            $data['alat_khusus'] = $data['alat_khusus'] === [] ? null : implode(', ', $data['alat_khusus']);
        }

        return $data;
    }

    /**
     * Blok kondisional di-set null — baris emr_detail tetap ada
     * (simpan E2E penuh) tapi tidak menyesatkan.
     */
    private function filteredData(array $data): array
    {
        if (! in_array($data['jenis_pesan'] ?? '', ['ENDOSCOPY', 'ESWL'], true)) {
            $data['jenis_anestesi'] = null;
        }

        if (($data['pasang_alat'] ?? null) !== 'Ada') {
            $data['alat_khusus'] = null;
        }

        return $data;
    }

    /**
     * Nilai awal form BARU — `handphone` dari pasien, sisanya kosong.
     */
    private function kosong($registrasi_detail): array
    {
        $pasien = $registrasi_detail->registrasi->pasien ?? null;

        return [
            'jenis_pesan' => '',
            'bagian_asal' => '',
            'unit_pelaksanaan_id' => '',
            'diagnosa_medis' => '',
            'jenis_tindakan' => '',
            'tgl_rencana_tindakan' => '',
            'jam_rencana_tindakan' => '',
            'jam_keputusan_sc' => '',
            'jenis_rawat_rencana' => '',
            'tgl_rencana_ranap' => '',
            'handphone' => $pasien->no_hp ?? '',
            'handphone_keluarga' => '',
            'pasang_alat' => '',
            'alat_khusus' => '',
            'sc' => '',
            'sc_emergency' => '',
            'ic' => '',
            'pra_tindakan' => '',
            'jenis_anestesi' => '',
            'dokter_operator_id' => '',
            'dpjp_utama' => '',
            'bb' => '',
            'tb' => '',
        ];
    }
}
