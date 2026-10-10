# Konsep Form EMR: Katalog Form Blanko (Form Generator)

Rancangan & desain **generic form builder** — form_id **129**
(`katalog_form_blanko`, `id_dash_menu = '5.373'`). Berbeda dengan seluruh form
EMR lain di folder `docs/`, dokumen ini **tidak** memakai model
`form` + `objek` + `objek_form_control` + `emr` + `emr_detail`. Ia memakai
**tabel sendiri** (`form_blanko`, `form_blanko_field`) plus tabel transaksi
(`form_blanko_pengisian`), karena definisi field-nya berubah sewaktu-waktu dan
dikelola administrator, bukan developer.

**Status:** Rancangan / Konsep — belum diimplementasikan
**Tanggal:** 2026-10-10
**Sumber legacy:** `/simrs_tenriawaru/FE/lib/modul/katalog_form_blanko.php`
dan `katalog_form_blanko_act.php`
**Alokasi:** **tidak memakai objek sama sekali** — dokumen ini memakai tabel
sendiri (`form_blanko`, `form_blanko_field`, `form_blanko_pengisian`), bukan
`objek` + `objek_form_control`. Band objek pada `ALOKASI_ID_GLOBAL.md` §1 untuk
dokumen ini sengaja kosong ("tabel sendiri", 0 objek). Yang dialokasikan hanya
`dashboard_menu_sub_id` **373** di bawah menu **5 "Formulir"** (menu yang sudah
ada; band sub §3 `ALOKASI_ID_GLOBAL.md` = 373–392).

---

## 1. Ringkasan

| form_id | nama_form | slug | id_dash_menu | ri | rj | igd | mcu |
|---|---|---|---|---|---|---|---|
| 129 | Katalog Form Blanko | `katalog_form_blanko` | `5.373` | 1 | 1 | 1 | 1 |

> **Rantai `slug` (PANDUAN §2.1).** `nama_sub_menu` = `Katalog Form Blanko`
> → `Str::slug('Katalog Form Blanko', '_')` = `katalog_form_blanko` = `form.slug`.
> Sesuai aturan slug, tanpa deviasi.
>
> **`dashboard_menu_sub_id` bersifat global.** Sub menu ini memakai **373** —
> band sub dokumen ini dimulai dari 373 (`ALOKASI_ID_GLOBAL.md` §3). Angka 1–12
> milik menu 1–5 dan tidak boleh dipakai ulang.

> **Flag rawat `0` semua.** Baris di tabel `form` tetap daftarkan form 129 agar
> konsisten dengan `ANALISIS_KEKURANGAN_FORM.md` P4 baris 129, tetapi
> `id_dash_menu` di-set `NULL` **atau** tetap `5.373` dengan flag rawat 0.
> Rekomendasi: **id_dash_menu = `5.373`** supaya halaman katalog bisa diakses
> dari menu Formulir di dashboard pasien, tetapi karena semua flag rawat 0,
> form tidak akan muncul di daftar form per pasien. Halaman pengisian dibuat
> sebagai **route terpisah** (bukan lewat `DynamicFormController`).

## 1.1 Sumber Referensi Legacy

| Berkas legacy | Isi yang diambil |
|---|---|
| `katalog_form_blanko.php` | UI "Pusat Cetak Formulir Rekam Medis (Blanko EMR)": hierarki **Menu Utama > Sub-Menu > Sub-Menu Extra > Formulir**, parameter klinis (rentang umur, jenis kelamin, jenis pelayanan, profesi PPA, spesialisasi dokter, kategori khusus keperawatan), Analisa Masalah SDKI, tombol "Tampilkan Form" & "Cetak / Simpan PDF" |
| `katalog_form_blanko_act.php` | `resolveFormFile()` (memetakan hierarki menu ke berkas modul), `buildFilterSnippets()` (filter `akses_ehr` + flag rawat), `getDummyRegDetailIdByAge()` (mock registrasi per kategori umur), `sanitizeModulCodeForBlanko()` (menetralkan query `emr` agar blanko tidak pernah menarik data pasien) |

Konsep legacy menyebut **format dokumen adaptif**: satu blanko yang menyesuaikan
isi berdasarkan rentang umur, jenis kelamin, jenis pelayanan, dan profesi PPA.
NusaMedika mempertahankan konsep adaptif, tetapi mengganti "pilih berkas modul
yang akan ditampilkan" dengan **form builder** — definisi field disimpan di
database, bukan di berkas PHP.

---

## 2. Konsep & Perbedaan dengan Model `objek` / `emr_detail`

### 2.1 Yang Statis dan Kaku

EmR NusaMedika tidak punya skema deklaratif form:

```
1. Seeder    database/seeders/EmrMasterSeeder.php  -> menu + form + objek + mapping + akses
2. Helper    app/Helpers/{Helper}.php               -> khusus, opsional
3. Controller app/Http/Controllers/EMR/{Studly}/   -> satu controller per form
4. View      resources/views/moduls/EMR/{Studly}/   -> satu blade per form
```

Artinya menambah satu field = 4 artefak kode + deploy. Katalog Form Blanko
membalik arah ini: **field didefinisikan dari UI administrator**, dan
pengisiannya dirender generik oleh **satu** controller + **satu** blade yang
membaca definisi field.

### 2.2 Mengapa butuh tabel sendiri

| Aspek | Model objek / emr_detail | Model form_blanko |
|---|---|---|
| Definisi field | di `objek_form_control` — satu baris per variabel per form, ditulis developer lewat seeder | di `form_blanko_field` — bisa ditambah/diubah **user** dari halaman admin |
| Label & urutan field | tidak ada; label diambil dari `objek.nama_objek`, urutan dari `objek_form_control_id` | ada (`label`, `urutan`, `bobot`, `tipe_kontrol`) |
| Tipe kontrol & opsi | tidak ada; tiap form Hard-code `<input>`/`<select>` di Blade | ada (`tipe_kontrol`, `opsi` JSON) |
| Aturan tampil bersyarat | tidak ada; logika `disabled` ditulis manual di JS tiap form | ada (`depends_on_field`, `depends_on_value`) |
| Nilai | satu baris `emr_detail` per variabel (teks) | satu kolom JSON per pengisian |
| Jumlah form | ~15 form, tiap form punya kode sendiri | tidak terbatas; 1 controller untuk semuanya |
| `id_dash_menu` | wajib ada agar muncul di dashboard | opsional |
| Perubahan definisi | butuh migration + seeder + deploy | CRUD biasa dari menu Administrator |

### 2.3 Mengapa tidak bisa "dipaksa" ke `emr_detail`

- `emr_detail.variabel` adalah nama kolom dari kode — tidak bisa dibuat user.
- `emr_detail.value` bertipe `text`: menyimpan checkbox multi-select harus
  di-serialize, dan **urutan baris** akan hilang saat dibaca ulang karena
  `emrDetailByVariabel()` melakukan `pluck('value','variabel')` sehingga
  duplikat saling menimpa.
- `objek_form_control` tidak punya kolom tipe kontrol/label/urutan — menambah
  kolom-kolom itu ke sana akan menggerakkan **seluruh** form existing.
- `form.slug` harus menunjuk folder controller/view `Str::studly(slug)`; blanko
  tidak punya folder sendiri.

Karena itu blanko butuh penyimpanan mandiri, namun tetap **terhubung** ke EMR
pasien melalui `registrasi_detail_id` agar bisa dilihat dari dashboard.

---

## 3. Desain Tabel

Tiga tabel. Dual-driver **MySQL 8 / PostgreSQL** sesuai `AGENTS.md`:
`increments()` (auto-increment / serial), JSON memakai `$table->json(...)`
(**bukan** `jsonb`), pencarian case-insensitive memakai `like` (**bukan**
`ilike`), kolom audit `input_time` / `mod_time` + `input_user_id` /
`mod_user_id`, soft delete lewat `status_batal`.

### 3.1 Migration — definisi form

`database/migrations/2026_10_10_000001_create_form_blanko_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Katalog Form Blanko (form generator).
 *
 * Berbeda dengan tabel `form` / `objek` / `objek_form_control`, tabel ini
 * BUKAN bagian dari model EMR objek. Store definisi field di tabel anak
 * `form_blanko_field` (kolom JSON), sehingga satu baris = satu template form.
 *
 * Dual-driver: `increments()` (MySQL AUTO_INCREMENT / PG serial),
 * `$table->json()` (MySQL JSON / PG json), tidak memakai `jsonb`.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_blanko', function (Blueprint $table) {
            $table->increments('form_blanko_id');

            // --- audit (tanpa created_at/updated_at, sesuai konvensi repo) ---
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();

            // --- identitas template ---
            $table->string('kode_blanko', 50)->unique();
            $table->string('nama_blanko', 150);
            $table->string('slug', 150)->unique();
            $table->text('deskripsi')->nullable();

            // --- hierarki (meniru dashboard_menu -> sub -> sub_extra) ---
            $table->integer('menu_induk')->nullable();      // dashboard_menu_id
            $table->integer('sub_induk')->nullable();       // dashboard_menu_sub_id
            $table->integer('extra_induk')->nullable();     // dashboard_menu_sub_extra_id

            // --- klasifikasi/kategori untuk filter katalog ---
            $table->string('kategori', 100)->nullable();
            $table->string('departemen', 100)->nullable();  //cedes: unit pembuat
            $table->smallInteger('is_aktif')->default(1);

            // --- cetak ---
            $table->string('ukuran_halaman', 20)->default('A4');   // A4 | A5 | Legal
            $table->string('orientasi_halaman', 10)->default('portrait'); // portrait|landscape
            $table->boolean('tampilkan_identitas_pasien')->default(true);
            $table->boolean('tampilkan_logo')->default(true);
            $table->text('kop_surat')->nullable();

            // --- adaptif (paralel dengan filter di katalog_form_blanko.php) ---
            // JSON berisi: kategori_umur[], jenis_kelamin[], jenis_rawat[], profesi_id[]
            $table->json('filter_adaptif')->nullable();

            // --- revisi definisi ---
            $table->smallInteger('versi')->default(1);
            $table->timestamp('publish_time', 6)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_blanko');
    }
};
```

### 3.2 Migration — definisi field

`database/migrations/2026_10_10_000002_create_form_blanko_field_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Field milik satu template Form Blanko.
 *
 * Tidak memakai FK constraint ke `dashboard_menu*` / `form` / `objek`
 * karena posisinya opsional (form dapat berdiri sendiri sebagai dokumen
 * non-EMR, misalnya "Lembar Verifikasi Farmasi").
 *
 * `opsi` dan `validasi` disimpan sebagai JSON agar tipe kontrol baru tidak
 * memerlukan migration baru.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_blanko_field', function (Blueprint $table) {
            $table->increments('form_blanko_field_id');

            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();

            $table->integer('form_blanko_id');

            // --- field ---
            $table->string('kode_field', 100);          // snake_case, unik per form
            $table->string('label', 200);
            $table->text('keterangan')->nullable();     // helper text di bawah input
            $table->string('tipe_kontrol', 30);         // lihat §4
            $table->smallInteger('urutan')->default(0);
            $table->string('grup', 100)->nullable();     // judul section / legend
            $table->boolean('wajib')->default(false);
            $table->smallInteger('lebar_kolom')->default(6); // 1..12 grid bootstrap

            // --- aturan tampil bersyarat (adaptive) ---
            $table->string('depends_on_field', 100)->nullable();
            $table->string('depends_on_value', 100)->nullable();

            // --- JSON ---
            // opsi: [{value, label}] untuk select/radio/checkbox/datalist
            $table->json('opsi')->nullable();
            // validasi: {min, max, maxLength, pattern, step, message}
            $table->json('validasi')->nullable();
            // setelan: {placeholder, default, rows, cols, accept, multiple}
            $table->json('setelan')->nullable();

            // Direferensikan juga ke objek EMR bila ingin ikut masuk laporan
            // objek (opsional; boleh NULL).
            $table->integer('objek_id')->nullable();

            $table->index(['form_blanko_id', 'urutan']);
            $table->unique(['form_blanko_id', 'kode_field']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_blanko_field');
    }
};
```

### 3.3 Migration — data pengisian

`database/migrations/2026_10_10_000003_create_form_blanko_pengisian_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Satu baris = satu kali pengisian satu template Form Blanko untuk satu
 * registrasi_detail. Semua nilai field disimpan dalam satu kolom JSON
 * (`data`) dengan bentuk { "kode_field": nilai, ... }.
 *
 * Kolom `ringkasan` menyimpan salinan nilai field yang ditandai
 * `tampilkan_di_riwayat` (untuk kolom ringkasan pada `x-emr-history-table`).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_blanko_pengisian', function (Blueprint $table) {
            $table->increments('form_blanko_pengisian_id');

            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();

            $table->integer('form_blanko_id');
            $table->integer('registrasi_detail_id');

            // Selalu JSON — dipakai MySQL dan PostgreSQL.
            $table->json('data');

            // Ringkasan tampilan riwayat (label pendek, mis. berisi tanggal).
            $table->json('ringkasan')->nullable();

            // Jumlah field terisi — untuk penyaringan cepat tanpa parse JSON.
            $table->smallInteger('jumlah_terisi')->default(0);

            $table->index(['form_blanko_id', 'registrasi_detail_id']);
            $table->index('registrasi_detail_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_blanko_pengisian');
    }
};
```

### 3.4 Model

```php
// app/Models/FormBlanko.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class FormBlanko extends Model
{
    protected $table = 'form_blanko';

    protected $primaryKey = 'form_blanko_id';

    public $timestamps = false;

    protected $fillable = [
        'kode_blanko', 'nama_blanko', 'slug', 'deskripsi',
        'menu_induk', 'sub_induk', 'extra_induk',
        'kategori', 'departemen', 'is_aktif',
        'ukuran_halaman', 'orientasi_halaman',
        'tampilkan_identitas_pasien', 'tampilkan_logo', 'kop_surat',
        'filter_adaptif', 'versi', 'publish_time',
    ];

    protected $casts = [
        'filter_adaptif' => 'array',
        'is_aktif'      => 'boolean',
        'versi'         => 'integer',
        'publish_time'  => 'datetime',
    ];

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereNull('status_batal')->orWhere('status_batal', 0);
        })->where('is_aktif', 1);
    }

    public function fields()
    {
        return $this->hasMany(FormBlankoField::class, 'form_blanko_id', 'form_blanko_id')
            ->where(function ($q) { $q->whereNull('status_batal')->orWhere('status_batal', 0); })
            ->orderBy('urutan');
    }

    public function pengisian()
    {
        return $this->hasMany(FormBlankoPengisian::class, 'form_blanko_id', 'form_blanko_id');
    }
}
```

```php
// app/Models/FormBlankoField.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormBlankoField extends Model
{
    protected $table = 'form_blanko_field';

    protected $primaryKey = 'form_blanko_field_id';

    public $timestamps = false;

    protected $fillable = [
        'form_blanko_id', 'kode_field', 'label', 'keterangan',
        'tipe_kontrol', 'urutan', 'grup', 'wajib', 'lebar_kolom',
        'depends_on_field', 'depends_on_value',
        'opsi', 'validasi', 'setelan', 'objek_id',
    ];

    protected $casts = [
        'opsi'     => 'array',
        'validasi' => 'array',
        'setelan'  => 'array',
        'wajib'    => 'boolean',
    ];
}
```

```php
// app/Models/FormBlankoPengisian.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class FormBlankoPengisian extends Model
{
    protected $table = 'form_blanko_pengisian';

    protected $primaryKey = 'form_blanko_pengisian_id';

    public $timestamps = false;

    protected $fillable = [
        'form_blanko_id', 'registrasi_detail_id', 'data', 'ringkasan', 'jumlah_terisi',
    ];

    protected $casts = [
        'data'         => 'array',
        'ringkasan'    => 'array',
        'jumlah_terisi'=> 'integer',
    ];

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereNull('status_batal')->orWhere('status_batal', 0);
        });
    }

    public function blanko()
    {
        return $this->belongsTo(FormBlanko::class, 'form_blanko_id', 'form_blanko_id');
    }
}
```

> **Gotcha `data` JSON.** Karena `data` bertipe `array`, nilai **string kosong**
> (`''`), `null`, `0`, dan `false` berbeda makna. Selalu normalisasi dengan
> `array_filter($payload, fn ($v) => $v !== null && $v !== '')` sebelum disimpan
> supaya jumlah field kosong tidak ikut terhitung pada `jumlah_terisi`.

---

## 4. Tipe Field yang Didukung

Kolom `form_blanko_field.tipe_kontrol` — 12 tipe. Renderer berada di satu blade
partial (`moduls/EMR/KatalogFormBlanko/partials/field.blade.php`) dengan
`@switch`.

| tipe_kontrol | Render | Nilai disimpan | Kolom JSON | Catatan |
|---|---|---|---|---|
| `text` | `<input type="text">` | string | `validasi.maxLength` | |
| `textarea` | `<textarea>` | string | `validasi.maxLength`, `setelan.rows` | |
| `number` | `<input type="number">` | number | `validasi.min/max/step` | |
| `date` | `<input type="date">` | `Y-m-d` | | |
| `datetime` | `<input type="datetime-local">` | `Y-m-d\TH:i` | | |
| `time` | `<input type="time">` | `H:i` | | |
| `select` | `<select>` | string (satu) | `opsi` | `setelan.multiple=true` → array |
| `radio` | radio group | string | `opsi` | |
| `checkbox` | checkbox group | **array** string | `opsi` | kosong → `[]` |
| `checkbox_single` | satu checkbox | `0`/`1` | | untuk Ya/Tidak |
| `select2` | Select2 AJAX | string (`id`) | `setelan.source` | untuk master DB (dokter, barang,ICD) |
| `signature` | `<canvas>` → data URL | string (base64 PNG) | `setelan.maxWidthPx` | **opsional**, butuh storage file |

### 4.1 Aturan tampil bersyarat (`depends_on_*`)

Mirroring parameter adaptif legacy (`katalog_form_blanko.php`): rentang umur,
jenis kelamin, jenis pelayanan, profesi PPA. Field bertanda
`depends_on_field = "usia_kategori"` dan `depends_on_value = "Anak"` hanya
dirender bila `data['usia_kategori'] === 'Anak'`. Diterjemahkan ke atribut
`data-depends-on` / `data-depends-value` pada elemen pembungkus, dan sebuah
fungsi kecil di JS menyetel `style.display` — **harus** tetap melepas class
`hidden` (gotcha Tailwind v4 di `AGENTS.md`).

### 4.2 Field sistem otomatis

Tiga `kode_field` ini dicadangkan dan tidak bisa diedit pengguna:

| kode_field | tipe | sumber |
|---|---|---|
| `__pasien` | read-only | `registrasi_detail` → `registrasi` → `pasien` |
| `__tanggal` | read-only | `now()` (PHP, **bukan** `NOW()` MySQL) |
| `__petugas` | read-only | `Auth::user()->nama_pegawai` |

Ditampilkan sebagai baris identitas di header, bukan field yang disimpan.

### 4.3 Batas yang perlu ditegakkan

- Maks **120 field** per template (agar `data` JSON tetap wajar).
- Maks **50 baris** untuk `repeatable` (tipe `table_repeat`) bila diaktifkan
  pada fase berikutnya.
- Nilai teks maksimum **65.535** karakter per field (batas kolom TEXT MySQL).

---

## 5. Alur CRUD Admin

Letak: **Administrator → Manajemen EMR → Katalog Form Blanko**
(sub_menu baru, `file_sub_menu = 'Administrator/ManajemenEMR/KatalogFormBlanko/katalog_form_blanko'`,
route auto `admin.katalog_form_blanko.*`).

| Method | Rute | Fungsi |
|---|---|---|
| `index` | `GET /katalog_form_blanko` | daftar template + filter kategori/departemen/aktif |
| `create` | `GET /katalog_form_blanko/create` | form metadata (tanpa field) |
| `store` | `POST /katalog_form_blanko` | simpan metadata, redirect ke `edit` |
| `edit` | `GET /katalog_form_blanko/{form_blanko}/edit` | metadata + **field builder** |
| `update` | `PUT /katalog_form_blanko/{form_blanko}` | simpan metadata + sinkron field |
| `destroy` | `DELETE /katalog_form_blanko/{form_blanko}` | soft-delete template + field + pengisian |
| `publish` | `POST /katalog_form_blanko/{form_blanko}/publish` | set `publish_time`, naikkan `versi` |

### 5.1 Field builder (the core)

Halaman `edit` menampilkan:

1. **Panel kiri** — daftar field terurut (drag-and-drop untuk `urutan`,
   tombol tambah di dalam grup/section).
2. **Panel kanan** — form edit field: `label`, `kode_field` (auto-slug dari
   label, editable), `tipe_kontrol`, `grup`, `urutan`, `wajib`, `lebar_kolom`,
   `depends_on_field`, `depends_on_value`, editor **opsi** (untuk
   select/radio/checkbox), editor **validasi** (min/max/maxLength/pattern),
   editor **setelan** (placeholder/default/rows/multiple).
3. **Pratinjau langsung** — render field persis seperti blade patients.

Controller (`Administrator\ManajemenEMR\KatalogFormBlanko\KatalogFormBlankoController`):

```php
public function update(Request $request, FormBlanko $katalog_form_blanko)
{
    $data = $request->validate([
        'kode_blanko'  => 'required|string|max:50|unique:form_blanko,kode_blanko,'.$katalog_form_blanko->form_blanko_id,
        'nama_blanko'  => 'required|string|max:150',
        'deskripsi'    => 'nullable|string|max:2000',
        'kategori'     => 'nullable|string|max:100',
        'departemen'   => 'nullable|string|max:100',
        'is_aktif'     => 'nullable|boolean',
        'ukuran_halaman' => 'required|in:A4,A5,Legal',
        'orientasi_halaman' => 'required|in:portrait,landscape',
        'fields'       => 'required|array',
    ]);

    DB::beginTransaction();
    try {
        $katalog_form_blanko->update([
            'kode_blanko' => $data['kode_blanko'],
            'nama_blanko' => $data['nama_blanko'],
            // ... kolom lain dari $data
            'mod_time'    => now(),
            'mod_user_id' => Auth::id(),
        ]);

        $this->sinkronField($katalog_form_blanko, $data['fields']);

        DB::commit();
    } catch (\Throwable $e) {
        DB::rollBack();
        return back()->with('error', 'Gagal menyimpan katalog: '.$e->getMessage())->withInput();
    }

    return back()->with('success', 'Katalog form blanko berhasil disimpan.');
}

/**
 * Sinkronkan daftar field: soft-delete field lama yang tidak ada di payload,
 * lalu insert/update field yang ada. Memakai ID field pada payload supaya
 * urutan dan soft-delete tidak saling menimpa.
 */
private function sinkronField(FormBlanko $blanko, array $fields): void
{
    $kodeValid = array_column($fields, 'kode_field');

    // Soft-delete field yang dihapus di UI.
    FormBlankoField::where('form_blanko_id', $blanko->form_blanko_id)
        ->where(function ($q) { $q->whereNull('status_batal')->orWhere('status_batal', 0); })
        ->whereNotIn('kode_field', $kodeValid)
        ->update(['status_batal' => 1, 'mod_time' => now(), 'mod_user_id' => Auth::id()]);

    foreach ($fields as $index => $f) {
        $payload = [
            'form_blanko_id'    => $blanko->form_blanko_id,
            'kode_field'        => $f['kode_field'],
            'label'             => $f['label'],
            'keterangan'        => $f['keterangan'] ?? null,
            'tipe_kontrol'      => $f['tipe_kontrol'],
            'urutan'            => $index + 1,
            'grup'              => $f['grup'] ?? null,
            'wajib'             => (bool) ($f['wajib'] ?? false),
            'lebar_kolom'       => $f['lebar_kolom'] ?? 6,
            'depends_on_field'  => $f['depends_on_field'] ?? null,
            'depends_on_value'  => $f['depends_on_value'] ?? null,
            'opsi'              => $f['opsi'] ?: null,
            'validasi'          => $f['validasi'] ?: null,
            'setelan'           => $f['setelan'] ?: null,
            'mod_time'          => now(),
            'mod_user_id'       => Auth::id(),
        ];

        if (! empty($f['form_blanko_field_id'])) {
            FormBlankoField::where('form_blanko_field_id', $f['form_blanko_field_id'])
                ->update($payload);
        } else {
            $payload['input_time'] = now();
            $payload['input_user_id'] = Auth::id();
            FormBlankoField::insert($payload);
        }
    }
}
```

> **Transaksi wajib.** `sinkronField()` melakukan beberapa operasi; bila gagal di
> tengah, template akan punya metadata baru tetapi field lama — kondisi yang
> merusak. `DB::beginTransaction()` + `rollBack()` menutup celah ini (mengikuti
> pola `Administrator\ManajemenUser\User\UserController`).

### 5.2 Publish & versioning

`publish` menaikkan `versi` dan mengeset `publish_time`. Semua `form_blanko_pengisian`
menyimpan `versi` indirectly lewat isi JSON — bila definisi field berubah setelah
pengisian, tampilan riwayat **memakai definisi versi saat pengisian** atau
menyimpan salinan definisi pada `ringkasan`. Rekomendasi sederhana: simpan
`versi` sebagai key di dalam `data`:

```php
$data['__versi'] = $blanko->versi;
```

### 5.3 Soft delete berantai

`destroy()` harus menutup tiga tabel dalam satu transaksi:

```php
DB::beginTransaction();
// 1. pengisian
DB::table('form_blanko_pengisian')->where('form_blanko_id', $id)
    ->where(function ($q) { $q->whereNull('status_batal')->orWhere('status_batal', 0); })
    ->update(['status_batal' => 1, 'mod_time' => now(), 'mod_user_id' => Auth::id()]);
// 2. field
DB::table('form_blanko_field')->where('form_blanko_id', $id)
    ->where(function ($q) { $q->whereNull('status_batal')->orWhere('status_batal', 0); })
    ->update(['status_batal' => 1, 'mod_time' => now(), 'mod_user_id' => Auth::id()]);
// 3. template
DB::table('form_blanko')->where('form_blanko_id', $id)
    ->update(['status_batal' => 1, 'is_aktif' => 0, 'mod_time' => now(), 'mod_user_id' => Auth::id()]);
DB::commit();
```

Meniru pola soft-delete berantai di
`Administrator\ManajemenEMR\DashboardMenu\DashboardMenuController`.

---

## 6. Alur Pengisian Pasien

### 6.1 Halaman katalog (untuk pasien)

`EMR\KatalogFormBlanko\KatalogFormBlankoController` — **tidak** memakai
`DynamicFormController`. Rute manual di `routes/web.php`:

```php
// daftar katalog (dengan filter adaptif)
Route::get('/emr/katalog-form-blanko/{registrasi_detail_id}', [KatalogFormBlankoController::class, 'index'])
    ->whereNumber('registrasi_detail_id')->name('emr.katalog_form_blanko.index');

//isi satu template
Route::get('/emr/katalog-form-blanko/{form_blanko}/{registrasi_detail_id}/{pengisian_id?}', [KatalogFormBlankoController::class, 'isi'])
    ->whereNumber('registrasi_detail_id')->whereNumber('pengisian_id')->name('emr.katalog_form_blanko.isi');

Route::post('/emr/katalog-form-blanko/{form_blanko}/{registrasi_detail_id}', [KatalogFormBlankoController::class, 'store'])
    ->name('emr.katalog_form_blanko.store');

Route::put('/emr/katalog-form-blanko/{form_blanko}/{registrasi_detail_id}/{pengisian_id}', [KatalogFormBlankoController::class, 'update'])
    ->name('emr.katalog_form_blanko.update');

Route::delete('/emr/katalog-form-blanko/{form_blanko}/{registrasi_detail_id}/{pengisian_id}', [KatalogFormBlankoController::class, 'destroy'])
    ->name('emr.katalog_form_blanko.destroy');

// cetak blanko kosong (tanpa data pasien) — untuk kebutuhan manual/downtime
Route::get('/emr/katalog-form-blanko/cetak/{form_blanko}', [KatalogFormBlankoController::class, 'cetakBlanko'])
    ->whereNumber('form_blanko')->name('emr.katalog_form_blanko.cetak');
```

> Route manual EMR **wajib** diberi `->whereNumber(param)` agar tidak mencuri
> path milik auto-route (lihat `AGENTS.md` — Auto-route sub_menu). Jalur
> `/emr/katalog-form-blanko/...` tidak bersinggungan dengan `/emr/form/...` milik
> `DynamicFormController`, sehingga tidak bentrok.

### 6.2 Filter adaptif (paralel legacy)

`index()` memfilter template berdasarkan query string:

```php
$filter = [
    'kategori_umur' => $request->query('kategori_umur'),  // Neonatus|Anak|Dewasa|Geriatri
    'jenis_kelamin' => $request->query('jenis_kelamin'),  // L|P
    'jenis_rawat'   => $request->query('jenis_rawat'),    // RI|RJ|IGD
    'profesi_id'    => $request->query('profesi_id'),     // 1=Dokter, 2=Perawat, 0=Semua
];

$daftar = FormBlanko::aktif()
    ->where(function ($q) use ($filter) {
        foreach (['kategori_umur', 'jenis_kelamin', 'jenis_rawat'] as $k) {
            $v = $filter[$k] ?? null;
            if ($v) {
                $q->whereJsonContains('filter_adaptif', [$k => $v]);
            }
        }
        if ($profesi = (int) ($filter['profesi_id'] ?? 0)) {
            $q->whereJsonContains('filter_adaptif', ['profesi_id' => $profesi]);
        }
    })
    ->orderBy('nama_blanko')
    ->get();
```

> **`whereJsonContains` kompatibel dua driver** di Laravel 13 (MySQL & PG).
> Alternatif yang lebih sering dipakai di repo ini: `->whereRaw('JSON_EXTRACT...')`
> **tidak** kompatibel PG — **dilarang**. Bila ragu, filter di PHP
> (`collect($daftar)->filter(fn ($b) => ...)`) karena jumlah template blanko
> kecil.

### 6.3 Render generik

`moduls/EMR/KatalogFormBlanko/isi.blade.php`:

```blade
@extends('layouts.iframe')

@section('content')
    @php
        $fields = $blanko->fields;   // sudah urut
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat {{ $blanko->nama_blanko }}"
        :titleForm="$blanko->nama_blanko"
        :subtitleForm="$blanko->deskripsi"
        :historyGrouped="$historyGrouped"
        :routeName="null"
        :routeUrl="url('emr/katalog-form-blanko/'.$blanko->form_blanko_id)"
        :registrasiDetailId="$registrasi_detail->registrasi_detail_id"
        :formAction="$formAction"
        :isEdit="$isEdit"
        :deleteAction="$deleteAction"
        :emrId="$pengisian_id"
        :isView="$isView"
        :printUrl="''"
        :canCreate="$aksesCrud['create']"
        :canRead="$aksesCrud['read']"
        :canUpdate="$aksesCrud['update']"
        :canDelete="$aksesCrud['delete']">

        <x-slot name="listRiwayat">
            {{-- x-emr-history-table bekerja untuk tabel emr; untuk blanko
                 gunakan tabel riwayat sendiri (lihat §6.4). --}}
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>
            @if ($blanko->tampilkan_identitas_pasien)
                <x-informasi-pasien :registrasiDetail="$registrasi_detail" />
            @endif

            @foreach ($fields->groupBy('grup') as $grup => $fieldsGrup)
                <x-emr-accordion :title="$grup ?: 'Detail'" :open="true">
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                        @foreach ($fieldsGrup as $field)
                            @include('moduls.EMR.KatalogFormBlanko.partials.field', ['field' => $field])
                        @endforeach
                    </div>
                </x-emr-accordion>
            @endforeach
        </fieldset>

    </x-emr-split-layout>

    <script>
    // Tampil/sembunyi field bersyarat. WAJIB lepas class `hidden` + set
    // style.display (lihat gotcha Tailwind v4 di AGENTS.md).
    document.addEventListener('DOMContentLoaded', function () {
        function sinkronConditional() {
            document.querySelectorAll('[data-depends-on]').forEach(function (wrap) {
                var sumber = document.querySelector('[name="data[' + wrap.dataset.dependsOn + ']"]');
                var cocok = sumber && sumber.value === wrap.dataset.dependsValue;
                wrap.classList.toggle('hidden', !cocok);
                wrap.style.display = cocok ? '' : 'none';
            });
        }
        document.querySelectorAll('[data-depends-on]').forEach(function () { });
        document.addEventListener('change', sinkronConditional);
        sinkronConditional();
    });
    </script>
@endsection
```

> **Gotcha `@json()` dan `@php` bersarang.** Daftar opsi tiap field harus
> dihitung di blok `@php` lalu diteruskan sebagai **variabel tunggal** ke
> `@json()`. Argumen `@json()` dipecah `explode(',')` sehingga ekspresi kompleks
> di dalamnya menghasilkan `ParseError`. Contoh benar sudah dipakai di
> `moduls/EMR/BundleVap/index.blade.php`.

### 6.4 Simpan

```php
public function store(Request $request, FormBlanko $form_blanko, $registrasi_detail_id)
{
    abort_unless(Auth::check(), 403);

    $blanko  = $form_blanko;
    $fields  = $blanko->fields;
    $input   = $request->input('data', []);

    // 1. Buang key yang bukan field terdaftar (anti mass-assignment).
    $kodeValid = $fields->pluck('kode_field')->all();
    $bersih = array_intersect_key($input, array_flip($kodeValid));

    // 2. Normalisasi checkbox multi-select menjadi array.
    foreach ($fields->where('tipe_kontrol', 'checkbox') as $f) {
        $bersih[$f->kode_field] = (array) ($bersih[$f->kode_field] ?? []);
    }

    // 3. Buang nilai kosong agar `jumlah_terisi` akurat.
    $bersih = array_filter($bersih, fn ($v) => $v !== null && $v !== '' && $v !== []);

    $bersih['__versi'] = $blanko->versi;

    FormBlankoPengisian::create([
        'form_blanko_id'       => $blanko->form_blanko_id,
        'registrasi_detail_id' => (int) $registrasi_detail_id,
        'data'                 => $bersih,
        'ringkasan'            => $this->ringkasan($fields, $bersih),
        'jumlah_terisi'        => count($bersih),
    ]);

    return back()->with('success', 'Form blanko berhasil disimpan.');
}

/**
 * Ringkasan singkat untuk tabel riwayat: ambil 3 field pertama yang terisi
 * sesuai urutan definisi.
 */
private function ringkasan($fields, array $data): array
{
    $out = [];
    foreach ($fields as $f) {
        if (count($out) >= 3) break;
        if (! empty($data[$f->kode_field])) {
            $out[$f->kode_field] = mb_strimwidth((string) $data[$f->kode_field], 0, 40, '...');
        }
    }
    return $out;
}
```

### 6.5 Riwayat

Karena tabel bukan `emr`, komponen `x-emr-history-table` (yang memanggil
`EmrHelper::getHistoryForForm()`) **tidak bisa dipakai**. Buat tabel riwayat
sendiri di partial `riwayat.blade.php` yang query langsung:

```php
$riwayat = FormBlankoPengisian::aktif()
    ->where('form_blanko_id', $form_blanko_id)
    ->whereIn('registrasi_detail_id', $daftarDetailId)
    ->with('blanko')
    ->orderByDesc('form_blanko_pengisian_id')
    ->limit(50)
    ->get();
```

Kolom yang ditampilkan: tanggal, petugas, `ringkasan` (3 field pertama).

---

## 7. Integrasi Dashboard

### 7.1 Seeder

```php
// $subMenus (menu 5 "Formulir", band sub 373-392 sesuai ledger §3)
['dashboard_menu_sub_id' => 373, 'dashboard_menu_id' => 5, 'nama_sub_menu' => 'Katalog Form Blanko'],

// $forms — TANPA objek & TANPA mapping
['form_id' => 129, 'nama_form' => 'Katalog Form Blanko', 'slug' => 'katalog_form_blanko', 'id_dash_menu' => '5.373', 'ri' => 1, 'rj' => 1, 'igd' => 1, 'mcu' => 1],

// $akses — tetap di-seed supaya entri menu muncul di header_ehr
['profesi_id' => 1, 'form_id' => 129, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 2, 'form_id' => 129, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 0],
```

> Form 129 **wajib** punya baris `akses_ehr`: `EmrDashboardController` melakukan
> INNER JOIN ke `akses_ehr`, sehingga tanpa baris tersebut entri menu
> "Katalog Form Blanko" tidak muncul di `header_ehr`.
> Opsi `akses_create/read/update` bercabang untuk peran: admin boleh
>Maintenance, perawat hanya mengisi.

`EmrMasterSeeder` **tidak** menambahkan baris `$objeks` maupun `$mapping` untuk
form 129 — itu justru perbedaan ESD dari model EMR.

### 7.2 Tampilan di dashboard pasien

- Menu **Formulir → Katalog Form Blanko** muncul sebagai link di
  `header_ehr` (`id_dash_menu = '5.373'`).
- Karena `ri/rj/igd/mcu` semuanya 0, form tidak masuk daftar form per rawat.
- Link diarahkan ke route katalog **milik controller blanko**, bukan ke
  `/emr/form/katalog_form_blanko/...` (yang tidak ada controller
  `EMR\KatalogFormBlanko\KatalogFormBlankoController` di router generik —
  sehingga `DynamicFormController` akan mencarinya dan gagal, lalu merender
  `Unsupported.blade.php`).

Karena itu **wajib** menambahkan pengalihan di `EmrDashboardController`:

```php
// sebelum render link form
if ($form->slug === 'katalog_form_blanko') {
    $url = route('emr.katalog_form_blanko.index', [
        'registrasi_detail_id' => $registrasi_detail_id,
    ]);
} else {
    $url = route('emr.dynamic.index', [...]);
}
```

Alternatif yang lebih bersih: set `id_dash_menu = NULL` untuk form 129 dan
buat entry menu terpisah di dashboard. **Rekomendasi:** pakai approach
pengalihan di atas agar tetap satu menu.

### 7.3 Menu Administrator

Sub_menu baru di tabel `sub_menu`:

```php
// Modul 6 "Administrator" -> Menu 8 "Manajemen EMR"
[
    'sub_menu_id'        => <id baru>,
    'modul_id'           => 6,
    'menu_id'            => 8,
    'nama_sub_menu'      => 'Katalog Form Blanko',
    'file_sub_menu'      => 'Administrator/ManajemenEMR/KatalogFormBlanko/katalog_form_blanko',
    'urutan'             => <terakhir + 1>,
    'status_batal'       => 0,
],
```

`file_sub_menu` **wajib** menyimpan path lengkap beserta nama blade
(`.blade.php` dihilangkan) — basename `katalog_form_blanko` menjadi URI dan
prefix nama route (`admin.katalog_form_blanko.*`). Setelah insert, jalankan
`php artisan route:clear && cache:clear` agar route & sidebar terbangun ulang.

Folder yang harus dibuat persis:

```
app/Http/Controllers/Administrator/ManajemenEMR/KatalogFormBlanko/KatalogFormBlankoController.php
resources/views/moduls/Administrator/ManajemenEMR/KatalogFormBlanko/katalog_form_blanko.blade.php
resources/views/moduls/Administrator/ManajemenEMR/KatalogFormBlanko/katalog_form_blanko_form.blade.php
resources/views/moduls/Administrator/ManajemenEMR/KatalogFormBlanko/katalog_form_blanko_field.blade.php
```

> **Gotcha leaf unik.** `file_sub_menu` memakai basename sebagai identitas URI
> di seluruh aplikasi. `katalog_form_blanko` **belum dipakai** oleh sub_menu
> lain — pastikan dengan
> `SubMenu::idByPath('Administrator/ManajemenEMR/KatalogFormBlanko/katalog_form_blanko')`
> sebelum membuat.

---

## 8. Validasi

### 8.1 Validasi definisi (admin)

```php
// kode_field wajib snake_case & unik per template
'fields.*.kode_field'  => 'required|string|max:100|regex:/^[a-z][a-z0-9_]*$/',
'fields.*.label'       => 'required|string|max:200',
'fields.*.tipe_kontrol'=> 'required|in:text,textarea,number,date,datetime,time,select,radio,checkbox,checkbox_single,select2,signature',
'fields.*.lebar_kolom' => 'required|integer|min:1|max:12',
'fields.*.opsi'        => 'required_if:tipe_kontrol,select,radio,checkbox|array|min:1',
'fields.*.opsi.*.value'=> 'required|string|max:100',
'fields.*.opsi.*.label'=> 'required|string|max:200',
// kode_field sistem tidak boleh dipakai user
'fields.*.kode_field'  => 'not_in:__pasien,__tanggal,__petugas',
```

- `select2` wajib menyertakan `setelan.source` (nama endpoint API yang sah).
- `depends_on_field` harus menunjuk `kode_field` yang **ada** di template yang
  sama dan berada **sebelum**nya pada `urutan` (agar tidak circular).
- Jumlah field dibatasi 120 (dihitung di `withValidator()`).

### 8.2 Validasi pengisian (petugas)

Validasi dibangun **dinamis dari definisi field**:

```php
private function rulesDariDefinisi(FormBlanko $blanko): array
{
    $rules = [];

    foreach ($blanko->fields as $f) {
        $v = $f->validasi ?? [];
        $base = match ($f->tipe_kontrol) {
            'number'    => 'numeric',
            'date'      => 'date',
            'datetime'  => 'date',
            'time'      => 'date_format:H:i',
            'checkbox'  => 'array',
            default     => 'string',
        };

        $parts = [$f->wajib ? 'required' : 'nullable', $base];

        if (isset($v['min']))       { $parts[] = 'min:'.$v['min']; }
        if (isset($v['max']))       { $parts[] = 'max:'.$v['max']; }
        if (isset($v['maxLength'])) { $parts[] = 'max:'.$v['maxLength']; }
        if (isset($v['pattern']))   { $parts[] = 'regex:'.$v['pattern']; }
        if (isset($v['step']))      { $parts[] = 'numeric'; }

        $rules['data.'.$f->kode_field] = implode('|', $parts);
    }

    return $rules;
}
```

Field `wajib` **harus** punya `required`; field checkbox `required` berarti
minimal satu opsi tercentang (Laravel menafsirkan `required` pada `array`
sebagai array tidak kosong — benar untuk checkbox group).

### 8.3 Validasi lintas-field (opsional)

Bila template punya field `depends_on`, nilai field yang bergantung ikut
divalidasi hanya ketika kondisi terpenuhi:

```php
foreach ($blanko->fields->whereNotNull('depends_on_field') as $f) {
    $kondisi = $input['data'][$f->depends_on_field] ?? null;
    if ($kondisi === $f->depends_on_value) {
        $rules['data.'.$f->kode_field] = ($rules['data.'.$f->kode_field] ?? 'nullable').'|required';
    }
}
```

---

## 9. Cetak

Dua mode:

### 9.1 Cetak blanko kosong (formulir manual / downtime)

Route `emr.katalog_form_blanko.cetak` — **tidak** butuh pasien. `sanitizeModulCodeForBlanko()`
di legacy menetralkan query EMR; di sini tidak relevan karena tidak ada kode modul
yang dieksekusi.

`print_blanko.blade.php`:

```blade
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $blanko->nama_blanko }}</title>
    <style>
        @page { size: {{ $blanko->ukuran_halaman }}
                {{ $blanko->orientasi_halaman }}; margin: 12mm; }
        body { font-family: "DejaVu Sans", Arial, sans-serif; font-size: 11pt; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 4px 6px; vertical-align: top; }
        .kosong { min-height: 22mm; }
    </style>
</head>
<body>
    @if ($blanko->tampilkan_logo)
        <img src="{{ asset('images/logo.png') }}" alt="" style="height: 24mm">
    @endif
    {!! nl2br(e($blanko->kop_surat)) !!}
    <h2 style="text-align:center">{{ $blanko->nama_blanko }}</h2>
    <p>{{ $blanko->deskripsi }}</p>

    <table>
        @foreach ($blanko->fields as $f)
            <tr>
                <th style="width: 32%">{{ $f->label }}{{ $f->wajib ? ' *' : '' }}</th>
                <td class="kosong"></td>
            </tr>
        @endforeach
    </table>
</body>
</html>
```

> **Blade dan `@page`.** Aturan `@page` harus berada di dalam `<style>` literal
> (bukan di atribut `style=`), jika tidak diabaikan printer.

### 9.2 Cetak pengisian

`print.blade.php` untuk data yang tersimpan: header identitas pasien (bila
`tampilkan_identitas_pasien`), baris `__tanggal` & `__petugas`, lalu tabel dua
kolom (label | nilai) sesuai urutan definisi. Field bertipe `signature` dirender
sebagai `<img src="data:image/png;base64,...">`.

Tombol cetak dari panel riwayat memakai
`route('emr.katalog_form_blanko.print', …)` — rute manual terpisah dari
`emr.dynamic.index`, tidak menggunakan `emr.soap.print` karena `emr_id` di sini
adalah `form_blanko_pengisian_id`.

---

## 10. Risiko

| Risiko | Mitigasi |
|---|---|
| Data gateway lolos ke `form_blanko_pengisian.data` | `array_intersect_key($input, array_flip($kodeValid))` sebelum disimpan — **wajib** |
| `whereJsonContains` tidak kompatibel PG | Verifikasi; bila gagal, filter di PHP (`Collection::filter`) karena jumlah template kecil |
| `jsonb` dipakai | **Dilarang.** Pakai `$table->json(...)` (lihat AGENTS.md dual-driver) |
| `ilike` dipakai untuk pencarian nama blanko | Gunakan `like` (MySQL `utf8mb4_unicode_ci` sudah case-insensitive) |
| `->orderBy('form_blanko_id')` pada `form_blanko_field` | Gunakan indeks komposit `['form_blanko_id','urutan']` |
| Route manual `/emr/katalog-form-blanko/...` menabrak auto-route | Beri `->whereNumber()` pada semua parameter numerik |
| `form_blanko.slug` bentrok dengan `form.slug` yang lain | Validasi unik saat create; slug tidak dipakai router generik sehingga tetap harus unik agar URL tidak ambigu |
| `DynamicFormController` merender `Unsupported.blade.php` | Tulis pengalihan di `EmrDashboardController` untuk slug `katalog_form_blanko` (§7.2) |
| Field wajib tapi tidak punya pesan error yang jelas | Pesan validasi dinamis diisi dari `validasi.message` bila ada, default `Kolom :label wajib diisi` |
| `@json()` dengan ekspresi kompleks | Hitung daftar opsi di blok `@php`, kirim sebagai variabel tunggal |
| Class `hidden` tidak menyembunyikan elemen | Gunakan helper yang **lepas class `hidden` + set `style.display`** |
| Isi JSON melebihi ukuran kolom | Batasi 120 field, teks per field <= 65.535 karakter (batas TEXT MySQL) |
| Soft delete template tidak menutup pengisian | `destroy()` memakai satu `DB::transaction()` untuk tiga tabel |
| Field `signature` disimpan sebagai base64 di dalam JSON | Bila fitur ini diaktifkan, simpan di tabel `dokumen_emr` terpisah, bukan di JSON |
| Index `form_blanko_pengisian.data` tidak dibuat (MySQL JSON) | Tidak di-index - pencarian cukup per `registrasi_detail_id` + `form_blanko_id` |
| Definisi field berubah setelah pengisian lama | Simpan `__versi` di dalam `data`; tampilan riwayat memakai definisi versi tersebut bila diperlukan |
| Auto-increment 3 tabel baru belum disetel ulang | Tambahkan `'form_blanko', 'form_blanko_field', 'form_blanko_pengisian'` ke `resetSequences()` pada `DatabaseSeeder` |
