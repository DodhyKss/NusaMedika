<?php

namespace App\Http\Controllers\EMR\Intake;

use App\Helpers\AksesEhr;
use App\Helpers\CairanHelper;
use App\Helpers\EmrHelper;
use App\Http\Controllers\Controller;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Form Intake Cairan (form 24, slug `intake`).
 *
 * Mencatat seluruh cairan yang masuk: 6 cabang infus (nama cairan dari master
 * `barang`), volume & sisa infus, IVFD, syringe pump, oral, spoel, transfusi,
 * dan intake lainnya.
 *
 * `total_intake` SELALU dihitung ulang di server. Baris infus yang nama
 * cairannya kosong dibuang seluruh kolomnya supaya tidak ada baris yatim.
 */
class IntakeController extends Controller
{
    private const SLUG = 'intake';

    public function index($registrasi_detail_id, $emr_id = null)
    {
        $registrasi_detail = RegistrasiDetail::with('registrasi.pasien')->findOrFail($registrasi_detail_id);

        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'read'), 403);

        $aksesCrud = AksesEhr::flags((int) $form_id);
        $riwayat = EmrHelper::emrList((int) $form_id, (int) $registrasi_detail->registrasi_id);
        $historyGrouped = EmrHelper::historyKunjunganGrouped($registrasi_detail);

        // Tidak boleh membuat data baru -> tampilkan riwayat terakhir.
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

        $barangs = CairanHelper::barangs();

        return view('moduls.EMR.Intake.index', compact(
            'registrasi_detail', 'riwayat', 'historyGrouped', 'emr_data',
            'formAction', 'isEdit', 'deleteAction', 'isView', 'emr_id', 'aksesCrud',
            'barangs'
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

        try {
            $simpan = $this->filteredData($data, (int) $form_id);

            $emr_id = DB::transaction(function () use ($form_id, $simpan, $registrasi_detail_id) {
                $id = EmrHelper::insert((int) $form_id, $simpan['data'], (int) $registrasi_detail_id);

                // EmrHelper belum mengisi flag_abnormal, jadi ditulis di sini.
                CairanHelper::tulisFlags($id, $simpan['flags']);

                return $id;
            });

            return redirect()->back()->with('success', 'Intake Cairan berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan Intake Cairan: '.$e->getMessage())->withInput();
        }
    }

    public function update(Request $request, $registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'update'), 403);

        $data = $this->validated($request);

        try {
            $simpan = $this->filteredData($data, (int) $form_id);

            DB::transaction(function () use ($form_id, $simpan, $emr_id) {
                EmrHelper::update((int) $emr_id, (int) $form_id, $simpan['data']);
                CairanHelper::tulisFlags((int) $emr_id, $simpan['flags']);
            });

            return redirect()->back()->with('success', 'Intake Cairan berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui Intake Cairan: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Intake Cairan berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus Intake Cairan: '.$e->getMessage());
        }
    }

    /**
     * Nilai kosong untuk form baru (36 variabel form 24).
     */
    private function kosong(): array
    {
        $d = ['total_intake' => ''];

        for ($i = 1; $i <= CairanHelper::MAKS_BARIS; $i++) {
            $d['nama_infus_'.$i] = '';
            $d['volume_infus_'.$i] = '';
            $d['sisa_infus_'.$i] = '';
            $d['ivfd_'.$i] = '';
            $d['syringe_pump_'.$i] = '';
        }

        foreach (CairanHelper::INTAKE_TAMBAHAN_24 as $kunci) {
            $d[$kunci] = '';
        }

        $d['catatan'] = '';

        return $d;
    }

    /**
     * Aturan validasi. Baris infus: `volume_infus_N` wajib diisi bila nama
     cairan pada baris yang sama dipilih (baris dipakai = harus lengkap).
     */
    private function validated(Request $request): array
    {
        $opsional = [
            'oral' => null,
            'spoel' => null,
            'transfusi' => null,
            'intake_lainnya' => null,
            'catatan' => null,
        ];

        for ($i = 1; $i <= CairanHelper::MAKS_BARIS; $i++) {
            $opsional['nama_infus_'.$i] = null;
            $opsional['volume_infus_'.$i] = null;
            $opsional['sisa_infus_'.$i] = null;
            $opsional['ivfd_'.$i] = null;
            $opsional['syringe_pump_'.$i] = null;
        }

        return array_merge($opsional, $request->validate([
            'nama_infus_1' => 'nullable|integer|exists:barang,barang_id',
            'volume_infus_1' => 'nullable|required_with:nama_infus_1|numeric|min:0',
            'sisa_infus_1' => 'nullable|numeric',
            'ivfd_1' => 'nullable|numeric|min:0',
            'syringe_pump_1' => 'nullable|numeric|min:0',

            'nama_infus_2' => 'nullable|integer|exists:barang,barang_id',
            'volume_infus_2' => 'nullable|required_with:nama_infus_2|numeric|min:0',
            'sisa_infus_2' => 'nullable|numeric',
            'ivfd_2' => 'nullable|numeric|min:0',
            'syringe_pump_2' => 'nullable|numeric|min:0',

            'nama_infus_3' => 'nullable|integer|exists:barang,barang_id',
            'volume_infus_3' => 'nullable|required_with:nama_infus_3|numeric|min:0',
            'sisa_infus_3' => 'nullable|numeric',
            'ivfd_3' => 'nullable|numeric|min:0',
            'syringe_pump_3' => 'nullable|numeric|min:0',

            'nama_infus_4' => 'nullable|integer|exists:barang,barang_id',
            'volume_infus_4' => 'nullable|required_with:nama_infus_4|numeric|min:0',
            'sisa_infus_4' => 'nullable|numeric',
            'ivfd_4' => 'nullable|numeric|min:0',
            'syringe_pump_4' => 'nullable|numeric|min:0',

            'nama_infus_5' => 'nullable|integer|exists:barang,barang_id',
            'volume_infus_5' => 'nullable|required_with:nama_infus_5|numeric|min:0',
            'sisa_infus_5' => 'nullable|numeric',
            'ivfd_5' => 'nullable|numeric|min:0',
            'syringe_pump_5' => 'nullable|numeric|min:0',

            'nama_infus_6' => 'nullable|integer|exists:barang,barang_id',
            'volume_infus_6' => 'nullable|required_with:nama_infus_6|numeric|min:0',
            'sisa_infus_6' => 'nullable|numeric',
            'ivfd_6' => 'nullable|numeric|min:0',
            'syringe_pump_6' => 'nullable|numeric|min:0',

            'oral' => 'nullable|numeric|min:0',
            'spoel' => 'nullable|numeric|min:0',
            'transfusi' => 'nullable|numeric|min:0',
            'intake_lainnya' => 'nullable|numeric|min:0',
            'catatan' => 'nullable|string|max:1000',
        ]));
    }

    /**
     * Data siap simpan + peta flag abnormal.
     *
     * @return array{data: array<string, mixed>, flags: array<string, int|null>}
     */
    private function filteredData(array $data, int $formId): array
    {
        $data = array_intersect_key($data, array_flip(EmrHelper::objekVariabels($formId)));

        $flags = [];

        // Buang baris yatim: nama cairan kosong -> seluruh kolom baris itu
        // ikut dibuang, bukan sekadar tidak divalidasi.
        for ($i = 1; $i <= CairanHelper::MAKS_BARIS; $i++) {
            if (blank($data['nama_infus_'.$i] ?? null)) {
                unset($data['volume_infus_'.$i], $data['sisa_infus_'.$i], $data['ivfd_'.$i]);
            } elseif (isset($data['sisa_infus_'.$i]) && $data['sisa_infus_'.$i] !== '') {
                // Sisa infus negatif = infuse melewati volume yang tersedia.
                $flags['sisa_infus_'.$i] = ((float) $data['sisa_infus_'.$i]) < 0 ? 1 : 0;
            }

            if (blank($data['syringe_pump_'.$i] ?? null)) {
                unset($data['syringe_pump_'.$i]);
            }
        }

        // Total intake = seluruh cabang infus + oral + spoel + transfusi
        // + syringe pump + intake lainnya (dihitung ulang, nilai browser dibuang).
        $kunci = CairanHelper::INTAKE_TAMBAHAN_24;
        for ($i = 1; $i <= CairanHelper::MAKS_BARIS; $i++) {
            $kunci[] = 'volume_infus_'.$i;
            $kunci[] = 'syringe_pump_'.$i;
        }
        $data['total_intake'] = CairanHelper::total($data, $kunci);

        // Snapshot nama cairan disimpan di variabel `catatan` (objek 77).
        $barangIds = [];
        for ($i = 1; $i <= CairanHelper::MAKS_BARIS; $i++) {
            if (! blank($data['nama_infus_'.$i] ?? null)) {
                $barangIds[] = (int) $data['nama_infus_'.$i];
            }
        }

        $catatan = trim(CairanHelper::stripSnapshot((string) ($data['catatan'] ?? '')));
        $snapshot = CairanHelper::snapshot($barangIds);
        $data['catatan'] = $snapshot === '' ? $catatan : ($catatan === '' ? $snapshot : $snapshot."\n".$catatan);

        return ['data' => $data, 'flags' => $flags];
    }
}
