<?php

namespace App\Http\Controllers\Administrator\ManajemenEMR\KatalogFormBlanko;

use App\Http\Controllers\Controller;
use App\Models\FormBlanko;
use App\Models\FormBlankoField;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Administrator → Manajemen EMR → Katalog Form Blanko (form 129 / PRE-06).
 *
 * CRUD template form generator: metadata + field builder (sinkron field).
 * Rujukan: docs/KONSEP_KATALOG_FORM_BLANKO.md §5 (CRUD admin), §8.1
 * (validasi definisi), §4 (tipe kontrol).
 *
 * Deviasi terdokumentasi vs KONSEP §5.1:
 * - Field builder TANPA drag-and-drop — urutan baris diatur lewat tombol
 *   Naik/Turun (JS memindahkan baris); urutan payload = urutan tampil.
 * - Editor opsi memakai textarea per baris `value|label` (bukan editor
 *   struktur).  Editor validasi/setelan memakai input tunggal
 *   (min/max/maxLength/pattern; placeholder/rows/source/multiple/default).
 * - `objek_id` tidak ditampilkan di UI (opsional di tabel; selalu NULL) —
 *   form blanko tidak terikat model EMR objek.
 */
class KatalogFormBlankoController extends Controller
{
    /** Tipe kontrol yang didukung (KONSEP §4). */
    public const TIPE = [
        'text' => 'Teks',
        'textarea' => 'Teks Panjang',
        'number' => 'Angka',
        'date' => 'Tanggal',
        'datetime' => 'Tanggal & Waktu',
        'time' => 'Waktu',
        'select' => 'Dropdown',
        'radio' => 'Radio',
        'checkbox' => 'Checkbox (banyak)',
        'checkbox_single' => 'Checkbox (satu)',
        'select2' => 'Select2 (AJAX)',
        'signature' => 'Tanda Tangan',
    ];

    /** Kode field sistem yang dilarang dipakai pengguna (KONSEP §4.2). */
    private const SISTEM = ['__pasien', '__tanggal', '__petugas'];

    /** Batas jumlah field per template (KONSEP §4.3). */
    private const MAX_FIELDS = 120;

    /** Opsi filter adaptif (KONSEP §6.2). */
    public const FILTER = [
        'kategori_umur' => ['Neonatus', 'Anak', 'Dewasa', 'Geriatri'],
        'jenis_kelamin' => ['L', 'P'],
        'jenis_rawat' => ['RI', 'RJ', 'IGD', 'MCU'],
        'profesi_id' => [1, 2, 7],
    ];

    private const LABEL_PROFESI = [1 => 'Dokter', 2 => 'Perawat', 7 => 'Perekam Medis'];

    public function index(Request $request)
    {
        $q = FormBlanko::aktif()
            ->withCount(['fields' => function ($fq) {
                $fq->where(function ($w) {
                    $w->whereNull('status_batal')->orWhere('status_batal', 0);
                });
            }])
            ->orderBy('nama_blanko');

        if ($request->filled('kategori')) {
            $q->where('kategori', 'ilike', '%'.$request->string('kategori').'%');
        }
        if ($request->filled('departemen')) {
            $q->where('departemen', 'ilike', '%'.$request->string('departemen').'%');
        }
        if ($request->filled('status')) {
            $q->where('is_aktif', $request->integer('status'));
        }

        $data = $q->get();
        $filterOpsi = $this->filterOpsi();

        return view('moduls.Administrator.ManajemenEMR.KatalogFormBlanko.katalog_form_blanko', compact('data', 'filterOpsi'));
    }

    public function create()
    {
        return view('moduls.Administrator.ManajemenEMR.KatalogFormBlanko.katalog_form_blanko_form', [
            'row' => null,
            'fields' => [],
            'filterOpsi' => $this->filterOpsi(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatedMetadata($request);

        DB::beginTransaction();
        try {
            $blanko = new FormBlanko;
            $blanko->kode_blanko = $data['kode_blanko'];
            $blanko->nama_blanko = $data['nama_blanko'];
            $blanko->slug = $data['slug'];
            $blanko->deskripsi = $data['deskripsi'] ?? null;
            $blanko->menu_induk = $data['menu_induk'] ?? null;
            $blanko->sub_induk = $data['sub_induk'] ?? null;
            $blanko->extra_induk = $data['extra_induk'] ?? null;
            $blanko->kategori = $data['kategori'] ?? null;
            $blanko->departemen = $data['departemen'] ?? null;
            $blanko->is_aktif = isset($data['is_aktif']) ? 1 : 0;
            $blanko->ukuran_halaman = $data['ukuran_halaman'];
            $blanko->orientasi_halaman = $data['orientasi_halaman'];
            $blanko->tampilkan_identitas_pasien = isset($data['tampilkan_identitas_pasien']);
            $blanko->tampilkan_logo = isset($data['tampilkan_logo']);
            $blanko->kop_surat = $data['kop_surat'] ?? null;
            $blanko->filter_adaptif = $this->normalisasiFilter($data['filter_adaptif'] ?? null);
            $blanko->versi = 1;
            $blanko->input_time = now();
            $blanko->input_user_id = Auth::id();
            $blanko->status_batal = 0;
            $blanko->save();
            DB::commit();

            return redirect()
                ->route('admin.katalog_form_blanko.edit', ['katalog_form_blanko' => $blanko->form_blanko_id])
                ->with('success', 'Template berhasil dibuat. Silakan tambahkan field pada halaman edit.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menyimpan template: '.$e->getMessage())->withInput();
        }
    }

    public function edit($katalog_form_blanko)
    {
        $row = FormBlanko::aktif()
            ->with(['fields' => fn ($q) => $q->where(function ($w) {
                $w->whereNull('status_batal')->orWhere('status_batal', 0);
            })->orderBy('urutan')])
            ->findOrFail($katalog_form_blanko);

        return view('moduls.Administrator.ManajemenEMR.KatalogFormBlanko.katalog_form_blanko_form', [
            'row' => $row,
            'fields' => $row->fields,
            'filterOpsi' => $this->filterOpsi(),
        ]);
    }

    public function update(Request $request, $katalog_form_blanko)
    {
        $blanko = FormBlanko::aktif()->findOrFail($katalog_form_blanko);
        $data = $this->validatedMetadata($request, $blanko->form_blanko_id, fieldsRequired: true);

        $this->validateFields($request, $blanko, $data['fields']);

        DB::beginTransaction();
        try {
            $blanko->kode_blanko = $data['kode_blanko'];
            $blanko->nama_blanko = $data['nama_blanko'];
            $blanko->slug = $data['slug'];
            $blanko->deskripsi = $data['deskripsi'] ?? null;
            $blanko->menu_induk = $data['menu_induk'] ?? null;
            $blanko->sub_induk = $data['sub_induk'] ?? null;
            $blanko->extra_induk = $data['extra_induk'] ?? null;
            $blanko->kategori = $data['kategori'] ?? null;
            $blanko->departemen = $data['departemen'] ?? null;
            $blanko->is_aktif = isset($data['is_aktif']) ? 1 : 0;
            $blanko->ukuran_halaman = $data['ukuran_halaman'];
            $blanko->orientasi_halaman = $data['orientasi_halaman'];
            $blanko->tampilkan_identitas_pasien = isset($data['tampilkan_identitas_pasien']);
            $blanko->tampilkan_logo = isset($data['tampilkan_logo']);
            $blanko->kop_surat = $data['kop_surat'] ?? null;
            $blanko->filter_adaptif = $this->normalisasiFilter($data['filter_adaptif'] ?? null);
            $blanko->mod_time = now();
            $blanko->mod_user_id = Auth::id();
            $blanko->save();

            $this->sinkronField($blanko, $data['fields']);
            DB::commit();

            return back()->with('success', 'Katalog form blanko berhasil disimpan.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menyimpan katalog: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($katalog_form_blanko)
    {
        $blanko = FormBlanko::aktif()->findOrFail($katalog_form_blanko);
        $soft = ['status_batal' => 1, 'mod_time' => now(), 'mod_user_id' => Auth::id()];

        DB::beginTransaction();
        try {
            DB::table('form_blanko_pengisian')->where('form_blanko_id', $blanko->form_blanko_id)
                ->where(function ($q) {
                    $q->whereNull('status_batal')->orWhere('status_batal', 0);
                })
                ->update($soft);
            DB::table('form_blanko_field')->where('form_blanko_id', $blanko->form_blanko_id)
                ->where(function ($q) {
                    $q->whereNull('status_batal')->orWhere('status_batal', 0);
                })
                ->update($soft);
            DB::table('form_blanko')->where('form_blanko_id', $blanko->form_blanko_id)
                ->update(array_merge($soft, ['is_aktif' => 0]));
            DB::commit();

            return redirect()->route('admin.katalog_form_blanko.index')->with('success', 'Template beserta field & pengisiannya berhasil dihapus.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menghapus template: '.$e->getMessage());
        }
    }

    /**
     * Publish: set publish_time & naikkan versi (KONSEP §5.2).
     * Route manual (bukan CRUD bawaan auto-router) di routes/web.php.
     */
    public function publish($katalog_form_blanko)
    {
        $blanko = FormBlanko::aktif()->findOrFail($katalog_form_blanko);

        $jumlahField = FormBlankoField::where('form_blanko_id', $blanko->form_blanko_id)
            ->where(function ($q) {
                $q->whereNull('status_batal')->orWhere('status_batal', 0);
            })
            ->count();

        if ($jumlahField === 0) {
            return back()->with('error', 'Template belum punya field — tidak bisa dipublish.');
        }

        $blanko->versi = $blanko->versi + 1;
        $blanko->publish_time = now();
        $blanko->mod_time = now();
        $blanko->mod_user_id = Auth::id();
        $blanko->save();

        return back()->with('success', 'Template dipublish ke versi '.$blanko->versi.'.');
    }

    private function filterOpsi(): array
    {
        return [
            'kategori_umur' => self::FILTER['kategori_umur'],
            'jenis_kelamin' => ['L' => 'Laki-laki', 'P' => 'Perempuan'],
            'jenis_rawat' => self::FILTER['jenis_rawat'],
            'profesi_id' => self::LABEL_PROFESI,
        ];
    }

    private function validatedMetadata(Request $request, $ignoreId = null, bool $fieldsRequired = false): array
    {
        $data = $request->validate([
            'kode_blanko' => ['required', 'string', 'max:50', Rule::unique('form_blanko', 'kode_blanko')->ignore($ignoreId, 'form_blanko_id')],
            'slug' => ['required', 'string', 'max:150', 'regex:/^[a-z0-9_]+$/', Rule::unique('form_blanko', 'slug')->ignore($ignoreId, 'form_blanko_id')],
            'nama_blanko' => 'required|string|max:150',
            'deskripsi' => 'nullable|string|max:2000',
            'menu_induk' => 'nullable|integer',
            'sub_induk' => 'nullable|integer',
            'extra_induk' => 'nullable|integer',
            'kategori' => 'nullable|string|max:100',
            'departemen' => 'nullable|string|max:100',
            'is_aktif' => 'nullable|boolean',
            'ukuran_halaman' => 'required|in:A4,A5,Legal',
            'orientasi_halaman' => 'required|in:portrait,landscape',
            'tampilkan_identitas_pasien' => 'nullable|boolean',
            'tampilkan_logo' => 'nullable|boolean',
            'kop_surat' => 'nullable|string|max:10000',
            'filter_adaptif' => 'nullable|array',
            'filter_adaptif.kategori_umur' => 'nullable|array',
            'filter_adaptif.kategori_umur.*' => 'in:'.implode(',', self::FILTER['kategori_umur']),
            'filter_adaptif.jenis_kelamin' => 'nullable|array',
            'filter_adaptif.jenis_kelamin.*' => 'in:L,P',
            'filter_adaptif.jenis_rawat' => 'nullable|array',
            'filter_adaptif.jenis_rawat.*' => 'in:'.implode(',', self::FILTER['jenis_rawat']),
            'filter_adaptif.profesi_id' => 'nullable|array',
            'filter_adaptif.profesi_id.*' => 'in:'.implode(',', array_keys(self::LABEL_PROFESI)),
            'fields' => $fieldsRequired ? 'required|array' : 'nullable|array',
            'fields.*.form_blanko_field_id' => 'nullable|integer',
            'fields.*.kode_field' => 'required|string|max:100',
            'fields.*.label' => 'required|string|max:200',
            'fields.*.tipe_kontrol' => ['required', Rule::in(array_keys(self::TIPE))],
            'fields.*.grup' => 'nullable|string|max:100',
            'fields.*.wajib' => 'nullable|boolean',
            'fields.*.lebar_kolom' => 'required|integer|min:1|max:12',
            'fields.*.depends_on_field' => 'nullable|string|max:100',
            'fields.*.depends_on_value' => 'nullable|string|max:100',
            'fields.*.opsi_text' => 'nullable|string|max:20000',
            'fields.*.min' => 'nullable|numeric',
            'fields.*.max' => 'nullable|numeric',
            'fields.*.maxLength' => 'nullable|integer|min:1|max:65535',
            'fields.*.pattern' => 'nullable|string|max:255',
            'fields.*.placeholder' => 'nullable|string|max:255',
            'fields.*.rows' => 'nullable|integer|min:1|max:50',
            'fields.*.source' => 'nullable|string|max:255',
            'fields.*.multiple' => 'nullable|boolean',
            'fields.*.default' => 'nullable|string|max:255',
        ]);

        if (isset($data['slug']) && DB::table('form')->where('slug', $data['slug'])
            ->where(function ($q) {
                $q->whereNull('status_batal')->orWhere('status_batal', 0);
            })->exists()) {
            throw ValidationException::withMessages([
                'slug' => 'Slug sudah dipakai form EMR lain ('.$data['slug'].').',
            ]);
        }

        return $data;
    }

    /**
     * Validasi tambahan field (KONSEP §8.1) yang tidak bisa diekspresikan
     * sebagai aturan `validate()` biasa (silang antar baris payload).
     */
    private function validateFields(Request $request, FormBlanko $blanko, array $fields): void
    {
        $errors = [];

        if (count($fields) > self::MAX_FIELDS) {
            $errors['fields'] = 'Maksimal '.self::MAX_FIELDS.' field per template.';
        }

        $kodeSemua = array_map(fn ($f) => (string) ($f['kode_field'] ?? ''), $fields);
        if (count($kodeSemua) !== count(array_unique($kodeSemua))) {
            $errors['fields'] = 'Terdapat kode_field duplikat dalam daftar field.';
        }

        foreach ($fields as $i => $f) {
            $prefix = 'fields.'.$i;
            $kode = (string) ($f['kode_field'] ?? '');

            if (in_array($kode, self::SISTEM, true)) {
                $errors[$prefix.'.kode_field'] = 'Kode field sistem ('.implode(', ', self::SISTEM).') tidak boleh dipakai.';
            }

            // Unik per template — termasuk baris soft-delete (dibatasi UNIQUE di DB).
            $bentrok = FormBlankoField::where('form_blanko_id', $blanko->form_blanko_id)
                ->where('kode_field', $kode)
                ->where('form_blanko_field_id', '!=', (int) ($f['form_blanko_field_id'] ?? 0))
                ->exists();
            if ($bentrok) {
                $errors[$prefix.'.kode_field'] = 'Kode "'.$kode.'" sudah dipakai field lain di template ini.';
            }

            $tipe = (string) ($f['tipe_kontrol'] ?? '');
            $opsiText = trim((string) ($f['opsi_text'] ?? ''));
            if (in_array($tipe, ['select', 'radio', 'checkbox'], true) && $opsiText === '') {
                $errors[$prefix.'.opsi_text'] = 'Tipe '.$tipe.' butuh minimal satu opsi (per baris: value atau value|label).';
            }

            if ($tipe === 'select2' && trim((string) ($f['source'] ?? '')) === '') {
                $errors[$prefix.'.source'] = 'Tipe select2 wajib mengisi sumber (source) endpoint API.';
            }

            $depends = trim((string) ($f['depends_on_field'] ?? ''));
            if ($depends !== '') {
                $idx = array_search($depends, $kodeSemua, true);
                if ($idx === false) {
                    $errors[$prefix.'.depends_on_field'] = 'Field rujukan "'.$depends.'" tidak ada dalam template.';
                } elseif ($idx >= $i) {
                    $errors[$prefix.'.depends_on_field'] = 'Field rujukan "'.$depends.'" harus berada sebelum field ini (agar tidak sirkular).';
                }
                if (trim((string) ($f['depends_on_value'] ?? '')) === '') {
                    $errors[$prefix.'.depends_on_value'] = 'Isi nilai pemicu untuk kondisi "'.$depends.'".';
                }
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }

    /**
     * Sinkronkan daftar field: soft-delete field aktif yang hilang dari
     * payload, lalu insert/update (mengikuti KONSEP §5.1, dengan tambahan
     * revive baris soft-delete ber-kode sama agar UNIQUE constraint tidak
     * menolak field yang dihapus lalu dibuat ulang).
     */
    private function sinkronField(FormBlanko $blanko, array $fields): void
    {
        $kodeValid = array_filter(array_map(fn ($f) => (string) ($f['kode_field'] ?? ''), $fields));

        FormBlankoField::where('form_blanko_id', $blanko->form_blanko_id)
            ->where(function ($q) {
                $q->whereNull('status_batal')->orWhere('status_batal', 0);
            })
            ->whereNotIn('kode_field', $kodeValid)
            ->update(['status_batal' => 1, 'mod_time' => now(), 'mod_user_id' => Auth::id()]);

        foreach ($fields as $index => $f) {
            $payload = [
                'form_blanko_id' => $blanko->form_blanko_id,
                'kode_field' => $f['kode_field'],
                'label' => $f['label'],
                'tipe_kontrol' => $f['tipe_kontrol'],
                'urutan' => $index + 1,
                'grup' => ($f['grup'] ?? '') !== '' ? $f['grup'] : null,
                'wajib' => (bool) ($f['wajib'] ?? false),
                'lebar_kolom' => (int) ($f['lebar_kolom'] ?? 6),
                'depends_on_field' => ($f['depends_on_field'] ?? '') !== '' ? $f['depends_on_field'] : null,
                'depends_on_value' => ($f['depends_on_value'] ?? '') !== '' ? $f['depends_on_value'] : null,
                'opsi' => $this->parseOpsi($f['opsi_text'] ?? null),
                'validasi' => $this->parseValidasi($f),
                'setelan' => $this->parseSetelan($f),
                'objek_id' => null,
                'status_batal' => 0,
                'mod_time' => now(),
                'mod_user_id' => Auth::id(),
            ];

            $idField = (int) ($f['form_blanko_field_id'] ?? 0);

            if ($idField > 0) {
                // Model instance (bukan Builder::update) supaya cast JSON
                // opsi/validasi/setelan diterapkan; wajib scoped per template.
                $ada = FormBlankoField::where('form_blanko_field_id', $idField)
                    ->where('form_blanko_id', $blanko->form_blanko_id)
                    ->first();
                if ($ada) {
                    $ada->fill($payload);
                    $ada->save();
                }

                continue;
            }

            // Baris baru: hidupkan kembali baris soft-delete ber-kode sama
            // (UNIQUE (form_blanko_id, kode_field) mencakup baris batal).
            $pulih = FormBlankoField::where('form_blanko_id', $blanko->form_blanko_id)
                ->where('kode_field', $f['kode_field'])
                ->first();

            if ($pulih) {
                $pulih->fill($payload);
                $pulih->save();
            } else {
                $baru = new FormBlankoField($payload);
                $baru->input_time = now();
                $baru->input_user_id = Auth::id();
                $baru->save();
            }
        }
    }

    /** Opsi: per baris `value|label` (atau `value` saja) → array JSON. */
    private function parseOpsi($text): ?array
    {
        if (trim((string) $text) === '') {
            return null;
        }

        $out = [];
        foreach (preg_split('/\r\n|\r|\n/', (string) $text) as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            if (str_contains($line, '|')) {
                [$value, $label] = explode('|', $line, 2);
                $out[] = ['value' => trim($value), 'label' => trim($label)];
            } else {
                $out[] = ['value' => $line, 'label' => $line];
            }
        }

        return $out === [] ? null : $out;
    }

    private function parseValidasi(array $f): ?array
    {
        $out = [];
        foreach (['min', 'max', 'maxLength', 'pattern'] as $k) {
            $v = $f[$k] ?? '';
            if ($v !== '' && $v !== null) {
                $out[$k] = $k === 'pattern' ? (string) $v : (float) $v;
            }
        }

        return $out === [] ? null : $out;
    }

    private function parseSetelan(array $f): ?array
    {
        $out = [];
        if (($f['placeholder'] ?? '') !== '') {
            $out['placeholder'] = $f['placeholder'];
        }
        if (($f['rows'] ?? '') !== '') {
            $out['rows'] = (int) $f['rows'];
        }
        if (($f['source'] ?? '') !== '') {
            $out['source'] = $f['source'];
        }
        if (! empty($f['multiple'])) {
            $out['multiple'] = true;
        }
        if (($f['default'] ?? '') !== '') {
            $out['default'] = $f['default'];
        }

        return $out === [] ? null : $out;
    }

    /** Normalisasi filter adaptif → JSON (buang grup kosong). */
    private function normalisasiFilter(?array $filter): ?array
    {
        if (! is_array($filter)) {
            return null;
        }

        $out = [];
        foreach (array_keys(self::FILTER) as $k) {
            $vals = $filter[$k] ?? [];
            if (! is_array($vals)) {
                continue;
            }
            $vals = array_values(array_filter($vals, fn ($v) => $v !== '' && $v !== null));
            if ($vals !== []) {
                $out[$k] = $k === 'profesi_id' ? array_map('intval', $vals) : $vals;
            }
        }

        return $out === [] ? null : $out;
    }
}
