<?php

namespace App\Http\Controllers\EMR\BuktiKerohanian;

use App\Helpers\AksesEhr;
use App\Helpers\EmrHelper;
use App\Helpers\SelectOption;
use App\Http\Controllers\Controller;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

/**
 * Form Bukti Pelayanan Kerohanian (form 115, slug `bukti_kerohanian`).
 *
 * KONSEP_PSIKOSOSIAL_KEROHANIAN.md §6: dokumen bukti petugas kerohanian SUDAH
 * memberikan pelayanan (kapan & di mana) — dipakai saat audit akreditasi.
 * Identitas pasien read-only dari master; hanya blok petugas + "Pada"
 * (hari/tanggal/jam/tempat) yang disimpan.
 *
 * `hari_rohani` DITURUNKAN server dari `tanggal_rohani` di `filteredData()`
 * (deviasi terdokumentasi dari snippet `abort_if` 422 KONSEP §6): nilai
 * disimpan eksplisit (bukan computed saat baca) sesuai kebutuhan cetak, tapi
 * kecocokannya dijamin konstruktif sehingga formulir tidak mungkin salah hari.
 * Lokasi `date('w')` memakai peta lokal karena `config('app.locale')` = 'en'
 * sehingga `translatedFormat('l')` menghasilkan Bahasa Inggris.
 */
class BuktiKerohanianController extends Controller
{
    private const SLUG = 'bukti_kerohanian';

    /** Index `date('w')`: 0 = Minggu .. 6 = Sabtu. */
    private const HARI = ['MINGGU', 'SENIN', 'SELASA', 'RABU', 'KAMIS', 'JUMAT', 'SABTU'];

    public function index($registrasi_detail_id, $emr_id = null)
    {
        $registrasi_detail = RegistrasiDetail::with(['registrasi.pasien', 'bagian'])->findOrFail($registrasi_detail_id);

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

        return view('moduls.EMR.BuktiKerohanian.index', compact(
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
        $data = $this->filteredData($data);

        try {
            EmrHelper::insert((int) $form_id, $data, (int) $registrasi_detail_id);

            return redirect()->back()->with('success', 'Bukti Pelayanan Kerohanian berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan Bukti Pelayanan Kerohanian: '.$e->getMessage())->withInput();
        }
    }

    public function update(Request $request, $registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'update'), 403);

        $data = $this->validated($request, (int) $form_id);
        $data = $this->filteredData($data);

        try {
            EmrHelper::update((int) $emr_id, (int) $form_id, $data);

            return redirect()->back()->with('success', 'Bukti Pelayanan Kerohanian berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui Bukti Pelayanan Kerohanian: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Bukti Pelayanan Kerohanian berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus Bukti Pelayanan Kerohanian: '.$e->getMessage());
        }
    }

    private function validated(Request $request, int $form_id): array
    {
        $rules = [
            'nama_anggota_rohani' => 'required|string|max:150',
            'umur_anggota_rohani' => 'required|string|max:30',
            'jk_anggota_rohani' => 'required|in:L,P',
            'agama_anggota_rohani' => 'required|in:'.implode(',', array_column(SelectOption::get('agama'), 'value')),
            'tanggal_rohani' => 'required|date',
            'jam_rohani' => 'required|date_format:H:i',
            'hari_rohani' => 'required|in:SENIN,SELASA,RABU,KAMIS,JUMAT,SABTU,MINGGU',
            'tempat_rohani' => 'required|string|max:150',
        ];

        $validator = Validator::make($request->all(), $rules);
        $validator->validate();

        return array_merge(
            array_fill_keys(EmrHelper::objekVariabels($form_id), null),
            $validator->validated()
        );
    }

    /**
     * `hari_rohani` disinkronkan dengan `tanggal_rohani` (lihat docblock class)
     * — nilai tetap disimpan eksplisit, bukan dihitung saat baca.
     */
    private function filteredData(array $data): array
    {
        if (! empty($data['tanggal_rohani'])) {
            $wday = (int) date('w', strtotime($data['tanggal_rohani']));
            $data['hari_rohani'] = self::HARI[$wday] ?? ($data['hari_rohani'] ?? null);
        }

        return $data;
    }

    private function kosong(): array
    {
        return [
            'nama_anggota_rohani' => '', 'umur_anggota_rohani' => '',
            'jk_anggota_rohani' => '', 'agama_anggota_rohani' => '',
            'tanggal_rohani' => '', 'jam_rohani' => '',
            'hari_rohani' => '', 'tempat_rohani' => '',
        ];
    }
}
