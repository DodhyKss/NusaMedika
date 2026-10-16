<?php

namespace App\Http\Controllers\EMR\DoubleCheckHighAlert;

use App\Helpers\AksesEhr;
use App\Helpers\EmrHelper;
use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Form Double Check Obat High Alert (form 111, slug `double_check_high_alert`).
 *
 * Keselamatan pemberian obat high alert (KONSEP_ONKOLOGI_TRANSFUSI §8): dua
 * lapis — **5 benar** oleh dua perawat per baris obat (identitas/obat/dosis/
 * cara/waktu), lalu **verifikasi ganda** identitas/jumlah/jenis obat. Daftar
 * obat dari master barang ber `flag_alert = 1` (prefill via datalist).
 *
 * Aturan yang ditegakkan SERVER:
 * 1. `ha_obat_N` terisi ⇒ 10 checkbox 5-benar baris itu wajib tercentang.
 * 2. `perawat_2_id` wajib berbeda dari `perawat_1_id` (`different`).
 * 3. `jumlah_baris` = baris terisi terakhir — dihitung server (turunan).
 *
 * Deviasi terdokumentasi: `ha_obat_N` tanpa `exists:barang,...` — legacy
 * memakai datalist teks bebas (nama obat), bukan id; validasi `exists` akan
 * menolak teks bebas.
 */
class DoubleCheckHighAlertController extends Controller
{
    private const SLUG = 'double_check_high_alert';

    /** Batas keras jumlah baris obat high alert. */
    public const MAKS_BARIS = 20;

    /** Jumlah baris yang dirender sebelum pengguna menekan "Tambah Baris". */
    public const BARIS_AWAL = 5;

    /** Kolom 5 benar per perawat per baris. */
    public const KOLOM_5_BENAR = ['identitas', 'obat', 'dosis', 'cara', 'waktu'];

    public function index($registrasi_detail_id, $emr_id = null)
    {
        $registrasi_detail = RegistrasiDetail::with('registrasi.pasien')->findOrFail($registrasi_detail_id);

        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'read'), 403);

        $aksesCrud = AksesEhr::flags((int) $form_id);
        $riwayat = EmrHelper::emrList((int) $form_id, (int) $registrasi_detail->registrasi_id);
        $historyGrouped = EmrHelper::historyKunjunganGrouped($registrasi_detail);

        // Prefill datalist: master barang ber flag_alert = 1 (tabel
        // peresepan_obat_dispense tidak ada — tanpa linkage resep).
        $opsiObatHa = Barang::aktif()
            ->where('flag_alert', 1)
            ->orderBy('nama_barang')
            ->pluck('nama_barang')
            ->all();

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

        // Berapa baris yang perlu ditampilkan saat edit (baris terisi + 1).
        $barisTerisi = 0;
        for ($i = 1; $i <= self::MAKS_BARIS; $i++) {
            if (trim((string) ($emr_data['ha_obat_'.$i] ?? '')) !== '') {
                $barisTerisi = $i;
            }
        }
        $barisTampil = max(self::BARIS_AWAL, min(self::MAKS_BARIS, $barisTerisi + 1));

        return view('moduls.EMR.DoubleCheckHighAlert.index', compact(
            'registrasi_detail', 'riwayat', 'historyGrouped', 'emr_data',
            'formAction', 'isEdit', 'deleteAction', 'isView', 'emr_id', 'aksesCrud',
            'opsiObatHa', 'barisTampil'
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
        $data = $this->filteredData($data, (int) $form_id);
        $this->cekBaris($data);

        try {
            EmrHelper::insert((int) $form_id, $data, (int) $registrasi_detail_id);

            return redirect()->back()->with('success', 'Double Check Obat High Alert berhasil disimpan.');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan Double Check Obat High Alert: '.$e->getMessage())->withInput();
        }
    }

    public function update(Request $request, $registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'update'), 403);

        $data = $this->validated($request, (int) $form_id);
        $data = $this->filteredData($data, (int) $form_id);
        $this->cekBaris($data);

        try {
            EmrHelper::update((int) $emr_id, (int) $form_id, $data);

            return redirect()->back()->with('success', 'Double Check Obat High Alert berhasil diperbarui.');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui Double Check Obat High Alert: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Double Check Obat High Alert berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus Double Check Obat High Alert: '.$e->getMessage());
        }
    }

    private function validated(Request $request, int $form_id): array
    {
        $rules = [
            'tanggal_pemberian' => 'required|date',
            'waktu_pemberian' => 'required|date_format:H:i',
            'cek_identitas_p1' => 'required|in:SESUAI,TIDAK',
            'cek_identitas_p2' => 'required|in:SESUAI,TIDAK',
            'cek_jumlah_p1' => 'required|in:SESUAI,TIDAK',
            'cek_jumlah_p2' => 'required|in:SESUAI,TIDAK',
            'cek_jenis_p1' => 'required|in:SESUAI,TIDAK',
            'cek_jenis_p2' => 'required|in:SESUAI,TIDAK',
            'perawat_1_id' => 'required|integer|exists:pegawai,pegawai_id',
            'perawat_2_id' => 'required|integer|exists:pegawai,pegawai_id|different:perawat_1_id',
        ];

        // Aturan baris ditulis dalam loop — dibaca harness E2E via
        // expandRuleLoops() (bentuk WAJIB $rules['prefix_'.$b] = '...').
        // 10 checkbox ditulis literal per baris: evaluasiKunci() harness hanya
        // mengevaluasi SATU variabel loop, key bersarang ('...'.$kolom.'...'.$b)
        // akan gagal evaluasi dan aturannya tidak terbaca.
        // KONSEP menulis `required_if:ha_obat_N,!=,` (rusak) — dipakai
        // nullable|boolean + guard server cekBaris() sebagai gantinya.
        for ($b = 1; $b <= self::MAKS_BARIS; $b++) {
            $rules['ha_obat_'.$b] = 'nullable|string|max:150';
            $rules['ha_p1_identitas_'.$b] = 'nullable|boolean';
            $rules['ha_p1_obat_'.$b] = 'nullable|boolean';
            $rules['ha_p1_dosis_'.$b] = 'nullable|boolean';
            $rules['ha_p1_cara_'.$b] = 'nullable|boolean';
            $rules['ha_p1_waktu_'.$b] = 'nullable|boolean';
            $rules['ha_p2_identitas_'.$b] = 'nullable|boolean';
            $rules['ha_p2_obat_'.$b] = 'nullable|boolean';
            $rules['ha_p2_dosis_'.$b] = 'nullable|boolean';
            $rules['ha_p2_cara_'.$b] = 'nullable|boolean';
            $rules['ha_p2_waktu_'.$b] = 'nullable|boolean';
        }

        $validator = Validator::make($request->all(), $rules);
        $validator->validate();

        return array_merge(
            array_fill_keys(EmrHelper::objekVariabels($form_id), null),
            $validator->validated()
        );
    }

    /**
     * Kross-check baris: obat terisi ⇒ 10 checkbox 5-benar wajib tercentang.
     * Melempar ValidationException supaya pesan muncul di @error field terkait.
     */
    private function cekBaris(array $data): void
    {
        $errors = [];

        for ($i = 1; $i <= self::MAKS_BARIS; $i++) {
            if (trim((string) ($data['ha_obat_'.$i] ?? '')) === '') {
                continue;
            }

            foreach (self::KOLOM_5_BENAR as $kolom) {
                foreach (['p1', 'p2'] as $perawat) {
                    $kunci = 'ha_'.$perawat.'_'.$kolom.'_'.$i;
                    if (! in_array($data[$kunci] ?? null, [1, '1', true, 'on', 'true'], true)) {
                        $errors[$kunci] = 'Centang 5 benar Perawat '.($perawat === 'p1' ? '1' : '2')." ({$kolom}) pada baris obat #{$i}.";
                    }
                }
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }

    /**
     * `jumlah_baris` = baris obat terisi terakhir — turunan, dihitung server
     * (PANDUAN §2.4); checkbox ternormalisasi `'1'`/`null`.
     */
    private function filteredData(array $data, int $formId): array
    {
        $data = array_intersect_key($data, array_flip(EmrHelper::objekVariabels($formId)));

        $jumlah = 0;
        for ($baris = 1; $baris <= self::MAKS_BARIS; $baris++) {
            if (trim((string) ($data['ha_obat_'.$baris] ?? '')) !== '') {
                $jumlah = $baris;
            }
            foreach (self::KOLOM_5_BENAR as $kolom) {
                foreach (['p1', 'p2'] as $perawat) {
                    $kunci = 'ha_'.$perawat.'_'.$kolom.'_'.$baris;
                    $data[$kunci] = in_array($data[$kunci] ?? null, [1, '1', true, 'on', 'true'], true) ? '1' : null;
                }
            }
        }
        $data['jumlah_baris'] = $jumlah;

        return $data;
    }

    private function kosong(): array
    {
        $d = [
            'tanggal_pemberian' => '', 'waktu_pemberian' => '',
            'cek_identitas_p1' => '', 'cek_identitas_p2' => '',
            'cek_jumlah_p1' => '', 'cek_jumlah_p2' => '',
            'cek_jenis_p1' => '', 'cek_jenis_p2' => '',
            'perawat_1_id' => '', 'perawat_2_id' => '',
            'jumlah_baris' => null,
        ];

        for ($i = 1; $i <= self::MAKS_BARIS; $i++) {
            $d['ha_obat_'.$i] = null;
            foreach (self::KOLOM_5_BENAR as $kolom) {
                $d['ha_p1_'.$kolom.'_'.$i] = null;
                $d['ha_p2_'.$kolom.'_'.$i] = null;
            }
        }

        return $d;
    }
}
