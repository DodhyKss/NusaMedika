<?php

namespace App\Http\Controllers\EMR\DaftarTerapiObat;

use App\Helpers\AksesEhr;
use App\Helpers\EmrHelper;
use App\Helpers\FarmasiHelper;
use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * Form Daftar Terapi Obat (form 45, slug `daftar_terapi_obat`).
 *
 * Daftar obat yang sedang berjalan + kode status pemberian per baris
 * (diberikan / ditolak / ditunda / alergi / efek samping / tidak tersedia /
 * sudah di-stop). `FarmasiHelper::STATUS_PEMBERIAN` adalah sumber tunggal kode
 * dan artinya.
 *
 * **Hanya `barang_id` yang disimpan, bukan snapshot nama** — keputusan yang
 * sama dengan `TindakanMedisController` (form 10). Nama, satuan, dan jenis
 * barang di-resolve dari `Barang::aktif()` saat render. `filteredData()`
 * menolak `barang_id` yang sudah soft-delete.
 *
 * Baris memakai SUFFIX (`obat_1`..`obat_20`) bukan array — `emrDetailByVariabel()`
 * melakukan `pluck('value','variabel')`, jadi N baris untuk variabel sama akan
 * tertimpa baris terakhir dan baris kedua dst. HILANG saat form dibuka lagi.
 *
 * Aturan yang ditegakkan SERVER (bukan hanya Karnyalaga):
 * 1. `obat_N` terisi → `jumlah_N` dan `status_N` wajib terisi.
 * 2. `barang` yang sudah soft-delete ditolak.
 * 3. `status_N` ∈ {stop, K, A, TAP} → `alasan_stop_N` wajib.
 * 4. Maksimal 20 baris (batas keras `MAKS_BARIS`).
 */
class DaftarTerapiObatController extends Controller
{
    private const SLUG = 'daftar_terapi_obat';

    /** Batas keras jumlah obat per pencatatan. */
    public const MAKS_BARIS = 20;

    /** Jumlah baris yang dirender sebelum pengguna menekan "Tambah Baris". */
    public const BARIS_AWAL = 5;

    /** Prefix tiap kolom baris. */
    public const KOLOM = [
        'obat' => 'obat_',
        'jumlah' => 'jumlah_',
        's_1' => 's_1_',
        's_2' => 's_2_',
        'aturan_pakai' => 'aturan_pakai_',
        'rute' => 'rute_',
        'status' => 'status_',
        'alasan_stop' => 'alasan_stop_',
        'jam_masuk' => 'jam_masuk_',
    ];

    /** Opsi rute pemberian (dropdown). */
    public const OPSI_RUTE = ['Oral', 'Intravenous', 'Intramuscular', 'Subcutaneous', 'Rectal', 'Topikal', 'Inhalasi', 'Nasal', 'Lainnya'];

    public function index($registrasi_detail_id, $emr_id = null)
    {
        $registrasi_detail = RegistrasiDetail::with('registrasi.pasien')->findOrFail($registrasi_detail_id);

        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'read'), 403);

        $aksesCrud = AksesEhr::flags((int) $form_id);
        $riwayat = EmrHelper::emrList((int) $form_id, (int) $registrasi_detail->registrasi_id);
        $historyGrouped = EmrHelper::historyKunjunganGrouped($registrasi_detail);

        // Master barang untuk dropdown (nama & satuan ikut dikirim ke view agar
        // select2 bisa merender label tanpa query per baris).
        $barangMap = [];
        foreach (Barang::aktif()->get() as $b) {
            $barangMap[$b->barang_id] = ['nama' => $b->nama_barang, 'satuan' => $b->satuan_id ?? null];
        }

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

        // Berapa baris yang perlu ditampilkan saat edit (baris terisi).
        $barisTerisi = 0;
        for ($i = 1; $i <= self::MAKS_BARIS; $i++) {
            if (! empty($emr_data['obat_'.$i])) {
                $barisTerisi = $i;
            }
        }
        $barisTampil = max(self::BARIS_AWAL, min(self::MAKS_BARIS, $barisTerisi + 1));

        return view('moduls.EMR.DaftarTerapiObat.index', compact(
            'registrasi_detail', 'riwayat', 'historyGrouped', 'emr_data',
            'formAction', 'isEdit', 'deleteAction', 'isView', 'emr_id', 'aksesCrud',
            'barangMap', 'barisTampil'
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
        $this->cekBaris($data);

        try {
            EmrHelper::insert((int) $form_id, $data, (int) $registrasi_detail_id);

            return redirect()->back()->with('success', 'Daftar Terapi Obat berhasil disimpan.');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan Daftar Terapi Obat: '.$e->getMessage())->withInput();
        }
    }

    public function update(Request $request, $registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'update'), 403);

        $data = $this->validated($request);

        $data = $this->filteredData($data, (int) $form_id);
        $this->cekBaris($data);

        try {
            EmrHelper::update((int) $emr_id, (int) $form_id, $data);

            return redirect()->back()->with('success', 'Daftar Terapi Obat berhasil diperbarui.');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui Daftar Terapi Obat: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Daftar Terapi Obat berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus Daftar Terapi Obat: '.$e->getMessage());
        }
    }

    /**
     * Nama variabel kolom ke-N.
     */
    public static function v(string $kolom, int $baris): string
    {
        return self::KOLOM[$kolom].$baris;
    }

    private function kosong(): array
    {
        $d = ['tanggal_terapi' => '', 'waktu_terapi' => '', 'dokter_instruksi_id' => '', 'keterangan' => ''];

        for ($i = 1; $i <= self::MAKS_BARIS; $i++) {
            foreach (array_keys(self::KOLOM) as $kolom) {
                $d[self::v($kolom, $i)] = null;
            }
        }

        return $d;
    }

    private function validated(Request $request): array
    {
        $rules = [
            'tanggal_terapi' => 'required|date',
            'waktu_terapi' => 'required|date_format:H:i',
            'dokter_instruksi_id' => 'nullable|integer|exists:pegawai,pegawai_id',
            'keterangan' => 'nullable|string|max:1000',
        ];

        // Aturan baris ditulis dalam loop. Aturan ini dibaca harness E2E lewat
        // ekspansi loop (expandRuleLoops() di tools/lib_pay.php), sehingga
        // bentuk penulisan WAJIB $rules['prefix_'.$b] = '...'.
        $statusRule = 'nullable|in:1,T,K,A,ESO,TAP,stop';
        $ruteRule = 'nullable|in:Oral,Intravenous,Intramuscular,Subcutaneous,Rectal,Topikal,Inhalasi,Nasal,Lainnya';
        for ($b = 1; $b <= self::MAKS_BARIS; $b++) {
            $rules['obat_'.$b] = 'nullable|integer|exists:barang,barang_id';
            $rules['jumlah_'.$b] = 'nullable|numeric|min:0|max:10000';
            $rules['s_1_'.$b] = 'nullable|string|max:10';
            $rules['s_2_'.$b] = 'nullable|string|max:10';
            $rules['aturan_pakai_'.$b] = 'nullable|string|max:255';
            $rules['rute_'.$b] = $ruteRule;
            $rules['status_'.$b] = $statusRule;
            $rules['alasan_stop_'.$b] = 'nullable|string|max:255';
            $rules['jam_masuk_'.$b] = 'nullable|date_format:H:i';
        }

        $opsional = ['dokter_instruksi_id' => null, 'keterangan' => null];
        for ($b = 1; $b <= self::MAKS_BARIS; $b++) {
            foreach (array_keys(self::KOLOM) as $kolom) {
                $opsional[self::v($kolom, $b)] = null;
            }
        }

        return array_merge($opsional, $request->validate($rules));
    }

    /**
     * Krossobor baris. Melempar ValidationException agar pesan muncul di
     * `@error` field terkait, bukan diam-diam diterima.
     */
    private function cekBaris(array $data): void
    {
        $errors = [];
        $terisi = 0;

        for ($i = 1; $i <= self::MAKS_BARIS; $i++) {
            $obat = $data['obat_'.$i] ?? null;

            if (blank($obat)) {
                continue;
            }
            $terisi++;

            if (blank($data['jumlah_'.$i] ?? null)) {
                $errors['jumlah_'.$i] = "Jumlah obat wajib diisi pada baris #{$i}.";
            }
            if (blank($data['status_'.$i] ?? null)) {
                $errors['status_'.$i] = "Status pemberian wajib diisi pada baris #{$i}.";
            }
            if (in_array($data['status_'.$i] ?? null, FarmasiHelper::STATUS_PERLU_ALASAN, true)
                && blank($data['alasan_stop_'.$i] ?? null)) {
                $errors['alasan_stop_'.$i] = "Alasan stop/tunda wajib diisi pada baris #{$i}.";
            }
        }

        if ($terisi === 0) {
            $errors['obat_1'] = 'Minimal satu baris obat harus diisi.';
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }

    /**
     * Hanya field terpetakan yang disimpan. Baris kosong dibuang seluruhnya
     * (bukan hanya `obat_N`) supaya tidak ada baris yatim di `emr_detail`.
     */
    private function filteredData(array $data, int $formId): array
    {
        $data = array_intersect_key($data, array_flip(EmrHelper::objekVariabels($formId)));

        // Barang aktif saja — id yang sudah soft-delete ditolak diam-diam
        // dengan mengosongkan barisnya.
        $barangAktif = Barang::aktif()->pluck('barang_id')->map(fn ($x) => (int) $x)->all();

        for ($i = 1; $i <= self::MAKS_BARIS; $i++) {
            $obat = $data['obat_'.$i] ?? null;

            if (blank($obat) || ! in_array((int) $obat, $barangAktif, true)) {
                foreach (array_keys(self::KOLOM) as $kolom) {
                    $data[self::v($kolom, $i)] = null;
                }

                continue;
            }

            $data['obat_'.$i] = (int) $obat;

            // Alasan stop hanya relevan untuk status yang memerlukannya.
            if (! in_array($data['status_'.$i] ?? null, FarmasiHelper::STATUS_PERLU_ALASAN, true)) {
                $data['alasan_stop_'.$i] = null;
            }
        }

        foreach ($data as $key => $value) {
            if (is_string($value) && trim($value) === '') {
                $data[$key] = null;
            }
        }

        return $data;
    }
}
