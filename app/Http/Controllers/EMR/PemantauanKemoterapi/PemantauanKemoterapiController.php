<?php

namespace App\Http\Controllers\EMR\PemantauanKemoterapi;

use App\Helpers\AksesEhr;
use App\Helpers\EmrHelper;
use App\Http\Controllers\Controller;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Form Pemantauan Kemoterapi (form 107, slug `pemantauan_kemoterapi`).
 *
 * Pemantauan per jam selama pemberian kemoterapi (KONSEP_ONKOLOGI_TRANSFUSI §4):
 * tabel baris `*_N` (maks 20) — Jam / Cairan / Obat / Dosis / TTV / Skala
 * Nyeri / Keluhan / Efek Samping, plus premedikasi 1..5. Semua var baris
 * memakai SUFFIX (bukan array) karena `emrDetailByVariabel()` melakukan
 * `pluck('value','variabel')`.
 *
 * Aturan yang ditegakkan SERVER:
 * 1. Minimal satu baris (`jam_1..20`) wajib terisi.
 * 2. Bila `jam_N` terisi, seluruh kolom baris itu wajib terisi.
 * 3. `total_cairan` = jumlah `cairan_N` — nilai browser DIBUANG (turunan).
 */
class PemantauanKemoterapiController extends Controller
{
    private const SLUG = 'pemantauan_kemoterapi';

    /** Batas keras jumlah baris pemantauan. */
    public const MAKS_BARIS = 20;

    /** Jumlah baris premedikasi. */
    public const MAKS_PREMED = 5;

    /** Jumlah baris yang dirender sebelum pengguna menekan "Tambah Baris". */
    public const BARIS_AWAL = 5;

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

        // Berapa baris monitoring yang perlu ditampilkan (baris terisi + 1).
        $barisTerisi = 0;
        for ($i = 1; $i <= self::MAKS_BARIS; $i++) {
            if (! empty($emr_data['jam_'.$i])) {
                $barisTerisi = $i;
            }
        }
        $barisTampil = max(self::BARIS_AWAL, min(self::MAKS_BARIS, $barisTerisi + 1));

        // Nama perawat pemantau (prefill dari user login — field read-only).
        $perawatRuangan = Auth::user()->pegawai->nama_pegawai ?? ($emr_data['perawat_ruangan'] ?? '');

        return view('moduls.EMR.PemantauanKemoterapi.index', compact(
            'registrasi_detail', 'riwayat', 'historyGrouped', 'emr_data',
            'formAction', 'isEdit', 'deleteAction', 'isView', 'emr_id', 'aksesCrud',
            'barisTampil', 'perawatRuangan'
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
            $data = $this->filteredData($data, (int) $form_id);

            EmrHelper::insert((int) $form_id, $data, (int) $registrasi_detail_id);

            return redirect()->back()->with('success', 'Pemantauan Kemoterapi berhasil disimpan.');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan Pemantauan Kemoterapi: '.$e->getMessage())->withInput();
        }
    }

    public function update(Request $request, $registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'update'), 403);

        try {
            $data = $this->validated($request, (int) $form_id);
            $data = $this->filteredData($data, (int) $form_id);

            EmrHelper::update((int) $emr_id, (int) $form_id, $data);

            return redirect()->back()->with('success', 'Pemantauan Kemoterapi berhasil diperbarui.');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui Pemantauan Kemoterapi: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Pemantauan Kemoterapi berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus Pemantauan Kemoterapi: '.$e->getMessage());
        }
    }

    private function validated(Request $request, int $form_id): array
    {
        $rules = [
            'tanggal_pemantauan' => 'required|date',
            'jam_pemantauan' => 'required|date_format:H:i',
            'perawat_ruangan' => 'required|string|max:150',
            'premed_1' => 'nullable|string|max:150',
            'premed_dosis_1' => 'nullable|string|max:150',
            'premed_2' => 'nullable|string|max:150',
            'premed_dosis_2' => 'nullable|string|max:150',
            'premed_3' => 'nullable|string|max:150',
            'premed_dosis_3' => 'nullable|string|max:150',
            'premed_4' => 'nullable|string|max:150',
            'premed_dosis_4' => 'nullable|string|max:150',
            'premed_5' => 'nullable|string:max:150',
            'premed_dosis_5' => 'nullable|string|max:150',
        ];

        // Aturan baris monitoring ditulis dalam loop — dibaca harness E2E lewat
        // ekspansi loop (expandRuleLoops() di tools/lib_pay.php), jadi bentuk
        // penulisan WAJIB $rules['prefix_'.$b] = '...'.
        for ($b = 1; $b <= self::MAKS_BARIS; $b++) {
            $rules['jam_'.$b] = 'nullable|date_format:H:i';
            $rules['cairan_'.$b] = 'nullable|numeric|min:0|max:10000';
            $rules['obat_'.$b] = 'nullable|string|max:150';
            $rules['dosis_'.$b] = 'nullable|string|max:100';
            $rules['sistolik_'.$b] = 'nullable|integer|min:50|max:300';
            $rules['diastolik_'.$b] = 'nullable|integer|min:20|max:200';
            $rules['nadi_'.$b] = 'nullable|integer|min:20|max:250';
            $rules['pernapasan_'.$b] = 'nullable|integer|min:5|max:80';
            $rules['suhu_'.$b] = 'nullable|numeric|min:30|max:45';
            $rules['skala_nyeri_'.$b] = 'nullable|integer|min:0|max:10';
            $rules['keluhan_'.$b] = 'nullable|string|max:500';
            $rules['efek_samping_'.$b] = 'nullable|string|max:500';
        }

        $validator = Validator::make($request->all(), $rules);
        $validator->after(function ($validator) use ($request) {
            $adaBaris = false;

            for ($i = 1; $i <= self::MAKS_BARIS; $i++) {
                if (blank($request->input('jam_'.$i))) {
                    continue;
                }
                $adaBaris = true;

                // Bila satu baris terisi (jam_N ada), seluruh kolom baris wajib.
                foreach (['cairan', 'obat', 'dosis', 'sistolik', 'diastolik', 'nadi', 'pernapasan', 'suhu', 'skala_nyeri', 'keluhan', 'efek_samping'] as $kolom) {
                    if (blank($request->input($kolom.'_'.$i))) {
                        $validator->errors()->add($kolom.'_'.$i, "Kolom ini wajib diisi pada baris pemantauan #{$i} (baris sudah diisi).");
                    }
                }
            }

            if (! $adaBaris) {
                $validator->errors()->add('jam_1', 'Minimal satu baris pemantauan wajib diisi (isi kolom Jam).');
            }
        });
        $validator->validate();

        return array_merge(
            array_fill_keys(EmrHelper::objekVariabels($form_id), null),
            $validator->validated()
        );
    }

    /**
     * `total_cairan` = jumlah cairan_N — nilai browser dibuang (turunan,
     * PANDUAN §2.4).
     */
    private function filteredData(array $data, int $formId): array
    {
        $data = array_intersect_key($data, array_flip(EmrHelper::objekVariabels($formId)));

        $total = 0;
        for ($baris = 1; $baris <= self::MAKS_BARIS; $baris++) {
            $total += (float) ($data['cairan_'.$baris] ?? 0);
        }
        $data['total_cairan'] = $total;

        return $data;
    }

    private function kosong(): array
    {
        $d = [
            'tanggal_pemantauan' => '', 'jam_pemantauan' => '',
            'perawat_ruangan' => '', 'total_cairan' => null,
        ];

        for ($i = 1; $i <= self::MAKS_PREMED; $i++) {
            $d['premed_'.$i] = null;
            $d['premed_dosis_'.$i] = null;
        }

        for ($i = 1; $i <= self::MAKS_BARIS; $i++) {
            foreach (['jam', 'cairan', 'obat', 'dosis', 'sistolik', 'diastolik', 'nadi', 'pernapasan', 'suhu', 'skala_nyeri', 'keluhan', 'efek_samping'] as $kolom) {
                $d[$kolom.'_'.$i] = null;
            }
        }

        return $d;
    }
}
