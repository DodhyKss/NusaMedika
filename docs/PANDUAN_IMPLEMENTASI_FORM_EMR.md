# Panduan Implementasi Form EMR — NusaMedika

**Status:** Acuan developer — wajib dibaca sebelum menambah form EMR baru
**Berlaku untuk:** semua dokumen `KONSEP_*.md` di folder ini

---

## 1. Arsitektur Singkat

Form EMR di NusaMedika **tidak** punya skema deklaratif. Tidak ada kolom tipe
kontrol, label, urutan, atau opsi di database. Setiap form = 4 artefak:

```
1. Seeder    database/seeders/EmrMasterSeeder.php   → menu + form + objek + mapping + akses
2. Helper    app/Helpers/{Helper}.php                → khusus (mis. RisikoJatuhHelper), opsional
3. Controller app/Http/Controllers/EMR/{Studly}/{Studly}Controller.php
4. View      resources/views/moduls/EMR/{Studly}/index.blade.php
```

> **Tidak ada migration baru** kecuali form butuh tabel master baru.
> **Tidak ada route baru.** Rute sudah generik di `routes/web.php`.

### Cara routing bekerja

`routes/web.php` (jangan diubah — sudah dipatenn):

```
GET    /emr/form/{form_name}/{registrasi_detail_id}/{emr_id?}   → emr.dynamic.index
POST   /emr/form-store/{form_name}/{registrasi_detail_id}       → emr.form.store
PUT    /emr/form-update/{form_name}/{registrasi_detail_id}/{emr_id}
DELETE /emr/form-delete/{form_name}/{registrasi_detail_id}/{emr_id}
```

`DynamicFormController` mendispatch:

1. Cari `App\Http\Controllers\EMR\{Str::studly($form_name)}\{Str::studly($form_name)}Controller@index`
2. Kalau tidak ada, cari view `moduls.EMR.{$form_name}.index`
3. Kalau tidak ada → `moduls/EMR/Unsupported.blade.php`

Jadi nama folder **wajib** `Str::studly($slug)`:
| slug | Studly | Folder controller/view |
|---|---|---|
| `tanda_vital` | `TandaVital` | `EMR/TandaVital/` |
| `sbar` | `Sbar` | `EMR/Sbar/` |
| `bundle_vap` | `BundleVap` | `EMR/BundleVap/` |

---

## 2. Aturan Golden (WAJIB dipatuhi)

### 2.1 Rantai `slug` — pemicu bug paling sering

```
form.slug  ==  Str::slug(nama_sub_menu, '_')          // kalau sub menu TANPA extra
           ==  Str::slug(nama_sub_menu_extra, '_')     // kalau sub menu DENGAN extra
```

`id_dash_menu` dibentuk view `header_ehr`:
```sql
CONCAT_WS('.', dashboard_menu_id, dashboard_menu_sub_id, dashboard_menu_sub_extra_id)
```
`CONCAT_WS` **melewati NULL**, jadi sub tanpa extra → `"2.8"` bukan `"2.8."`.

**Contoh benar:**
```php
// sub 9 tanpa extra, menu 1
['dashboard_menu_sub_id' => 9, 'dashboard_menu_id' => 1, 'nama_sub_menu' => 'Tindakan Medis'],
// form:
['form_id' => 10, 'nama_form' => 'Tindakan Medis', 'slug' => 'tindakan_medis', 'id_dash_menu' => '1.9', ...]
```

**Contoh salah** (form tidak akan muncul):
```php
['nama_sub_menu' => 'Tindakan Medis']  // slug = 'tindakan_medis'  ✅
['nama_sub_menu' => 'Tindakan Medis Sirup']  // slug = 'tindakan_medis_sirup'  ❌ form yatim
```

### 2.2 `variabel` harus unik per form

`EmrHelper::emrDetailByVariabel()` melakukan `pluck('value', 'variabel')` —
duplikat **saling menimpa diam-diam**. Untuk field berulang, wajib sufiks angka:

```php
// ❌ salah
'obat' => 69, 'jumlah' => 70,   // kolom ke-2 menimpa kolom ke-1

// ✅ benar (pola form 10 Tindakan Medis)
'obat_1' => 69, 'jumlah_1' => 70,
'obat_2' => 69, 'jumlah_2' => 70,
'obat_3' => 69, 'jumlah_3' => 70,
// ... sampai _20
```

### 2.3 `akses_ehr` wajib ada

`EmrDashboardController` INNER JOIN ke `akses_ehr`. Tanpa baris `akses_ehr`:
- form tidak muncul di dashboard
- URL langsung tetap 403

Profesi yang sudah dipakai: `1` = Dokter, `2` = Perawat, `5` = Radiografer,
`13` = Analis Laboratorium.

### 2.4 Field computed dihitung server-side

Nilai dari browser **selalu dibuang** untuk field hitungan (BMI, skor, EWS, GCS).
Hitung ulang di `filteredData()`.

### 2.5 Object snapshot

Untuk field yang merujuk master (`*_id` + `nama_*`), nama diisi ulang dari master
saat menyimpan — bukan dari input browser. Agar riwayat tetap terbaca bila master berubah.

### 2.6 `objek_id` boleh NULL

Untuk field yang tidak punya makna semantik (validasi-only, transien) biarkan NULL.
Contoh: `ews_na[]`.

---

## 3. Checklist Implementasi

### Step 1 — Seeder: menu

Tambah di `$subMenus` (atau `$extras` bila butuh 3 level).

```php
// Menu 1 "Catatan Medis", sub TANPA extra
['dashboard_menu_sub_id' => 13, 'dashboard_menu_id' => 1, 'nama_sub_menu' => 'Resume Medis'],

// Menu 2 "Catatan Keperawatan", sub DENGAN extra
['dashboard_menu_sub_extra_id' => 6, 'dashboard_menu_sub_id' => 13,
 'nama_sub_menu_extra' => 'Monitoring Cairan'],
```

> ID baru **wajib** > ID tertinggi sekarang (sub = 12, extra = 5).
> `updateOrInsert` berbasis PK, jadi pakai ID tetap agar idempoten.

### Step 2 — Seeder: form

```php
['form_id' => 16, 'nama_form' => 'Resume Medis', 'slug' => 'resume_medis',
 'id_dash_menu' => '1.13', 'ri' => 1, 'rj' => 0, 'igd' => 0, 'mcu' => 0],
```

- `ri`/`rj`/`igd`/`mcu` = flag tampil per jenis rawat
- `id_dash_menu` = NULL → tidak muncul di dashboard (hanya via URL)

### Step 3 — Seeder: objek

Tambah di `$objeks`. **Objek ID baru mulai dari 178** (sekarang terpakai 1–177).

```php
178 => 'Tanggal Resume',
179 => 'Diagnosa Utama Resume',
180 => 'Skor Resume',
```

> **Reuse dulu.** Cek daftar objek 1–177 sebelum buat baru — vital sign,
> kesadaran, EWS, obesitas, dsb. sudah ada dan dipakai lintas form.
> Buat objek baru hanya kalau memang tidak ada padanannya.

> Dari 177 objek menjadi ± 500-an. Pertimbangkan filter/pencarian objek per menu
> di `Administrator/ManajemenEMR/Form` agar tetap terkendali.

### Step 4 — Seeder: mapping

```php
16 => [
    'tanggal_resume' => 178,
    'diagnosa_utama' => 179,
    'skor_resume'    => 180,
],
```

### Step 5 — Seeder: akses_ehr

```php
['profesi_id' => 1, 'form_id' => 16, 'level_id' => 1, 'bagian_id' => null,
 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
```

Idempoten: `insert` dilewati bila kombinasi `profesi_id + form_id` sudah ada.

### Step 6 — Seeder: backfill

```php
EmrHelper::backfillObjekId(16);
```

### Step 7 — Controller

Salin pola `ImplementasiKeperawatanController`. Kerangka:

```php
<?php

namespace App\Http\Controllers\EMR\ResumeMedis;

use App\Helpers\AksesEhr;
use App\Helpers\EmrHelper;
use App\Http\Controllers\Controller;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResumeMedisController extends Controller
{
    private const SLUG = 'resume_medis';

    public function index($registrasi_detail_id, $emr_id = null)
    {
        $registrasi_detail = RegistrasiDetail::with('registrasi.pasien')->findOrFail($registrasi_detail_id);

        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'read'), 403);

        $aksesCrud = AksesEhr::flags((int) $form_id);
        $riwayat = EmrHelper::emrList((int) $form_id, (int) $registrasi_detail->registrasi_id);
        $historyGrouped = EmrHelper::historyKunjunganGrouped($registrasi_detail);

        // Tidak boleh create -> tampilkan riwayat terakhir.
        if (empty($emr_id) && ! ($aksesCrud['create'] ?? false) && $riwayat->isNotEmpty()) {
            return redirect()->route('emr.dynamic.index', [
                'form_name' => self::SLUG,
                'registrasi_detail_id' => $registrasi_detail_id,
                'emr_id' => $riwayat->first()->emr_id,
                'action' => 'view',
            ]);
        }

        if (empty($emr_id)) {
            $emr_data = EmrHelper::wrapData([
                'tanggal_resume' => '',
                'diagnosa_utama' => '',
                'skor_resume'    => '',
            ]);
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

        return view('moduls.EMR.ResumeMedis.index', compact(
            'registrasi_detail', 'riwayat', 'historyGrouped', 'emr_data',
            'formAction', 'isEdit', 'deleteAction', 'isView', 'emr_id', 'aksesCrud'
        ));
    }

    public function store(Request $request, $registrasi_detail_id) { /* ... */ }
    public function update(Request $request, $registrasi_detail_id, $emr_id) { /* ... */ }
    public function destroy($registrasi_detail_id, $emr_id) { /* ... */ }

    private function validated(Request $request): array
    {
        return array_merge([
            // field opsional → default null agar tidak lolos required
        ], $request->validate([
            // 'field' => 'required|...',
        ]));
    }

    private function filteredData(array $data, int $formId): array
    {
        // Simpan HANYA field yang terpetakan.
        return array_intersect_key($data, array_flip(EmrHelper::objekVariabels($formId)));
    }
}
```

`store()` wajib cek sesi (`Auth::user()->pegawai_id` + `user_id`) — lihat
`ImplementasiKeperawatanController::store()`.

### Step 8 — Blade

```blade
@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $titleForm = 'Form Resume Medis';
        $subtitleForm = 'Deskripsi singkat form.';
        $routeName = null;
        $routeUrl = url('emr/form/resume_medis');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Resume"
        :titleForm="$titleForm" :subtitleForm="$subtitleForm"
        :historyGrouped="$historyGrouped" :routeName="$routeName" :routeUrl="$routeUrl"
        :registrasiDetailId="$registrasiDetailId" :formAction="$formAction"
        :isEdit="$isEdit" :deleteAction="$deleteAction" :emrId="$emr_id" :isView="$isView"
        :canCreate="$aksesCrud['create']" :canRead="$aksesCrud['read']"
        :canUpdate="$aksesCrud['update']" :canDelete="$aksesCrud['delete']">

        <x-slot name="listRiwayat">
            <x-emr-history-table
                slug="resume_medis"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="['diagnosa_utama' => 'Diagnosa Utama']"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>
            <!-- sections -->
        </fieldset>
    </x-emr-split-layout>
@endsection
```

Komponen tersedia di `resources/views/components/`:

| Komponen | Fungsi |
|---|---|
| `emr-split-layout` | Layout 2 panel (riwayat kiri, form kanan) |
| `emr-history-table` | Tabel riwayat pengisian |
| `emr-accordion` | Section accordion |
| `informasi-pasien` | Header info pasien |
| `confirm-alert` | Konfirmasi hapus |
| `select_dokter` / `select_pegawai` / `select_poliklinik` / `select_pasien` / `select_ruang_perawatan` | Dropdown DB |

Partial field reusable di `resources/views/moduls/EMR/PartialForm/`:
`harian_balance`, `harian_keluhan`, `informasi_pasien`,
`observasi_harian_kesadaran_oksigen`, `observasi_harian_penilaian_nyeri`,
`observasi_harian_tanda_vital`, `pemeriksaan_fisik`, `pengkajian_nyeri`,
`pengkajian_risiko_jatuh`, `riwayat_alergi`, `riwayat_penyakit`.

### Step 9 — Opsi dropdown

Tambahkan key baru di `app/Helpers/SelectOption.php` (31 key sekarang).
Jangan hardcode `<option>` di Blade kalau opsi bisa dipakai form lain.

### Step 10 — Cetak (opsional)

Kalau perlu PDF, buat `print.blade.php` + route `emr.soap.print` style
(`/emr/{slug}/print/{emr_id}`) seperti `Soap` dan `Konsultasi`.

### Step 11 — Dokumentasi

Tambahkan/ubah entri form di `AGENTS.md` — **saat ini forms 13–15 belum
terdokumentasi di sana**, itu kekurangannya.

---

## 4. Checklist Review Sebelum Merge

- [ ] `slug` form == `Str::slug(nama_sub_menu/extra)`
- [ ] `id_dash_menu` cocok dengan PK menu yang benar-benar di-seed
- [ ] `form_id` > 15, `objek_form_control_id`/`objek_id` > 177, tanpa bentrok
- [ ] Semua `variabel` unik dalam form
- [ ] Ada baris `akses_ehr` untuk setiap profesi yang boleh mengisi
- [ ] `EmrHelper::backfillObjekId(N)` dipanggil
- [ ] Folder controller/view == `Str::studly($slug)`
- [ ] `filteredData()` memakai `array_intersect_key` — tidak ada data_gateway lolos
- [ ] Field computed dihitung ulang di server
- [ ] `*_nama` snapshot diisi dari master, bukan dari browser
- [ ] `@error` ada di setiap field yang punya validasi
- [ ] `{{ $isView ? 'disabled' : '' }}` ada di `<fieldset>`
- [ ] `old('x', $emr_data['x'] ?? '')` dipakai (bukan `$emr_data['x']` langsung)
- [ ] Test: buka form → create → edit → delete → view, semua.OK
- [ ] `pint` dijalankan: `docker compose exec app ./vendor/bin/pint --dirty`
- [ ] Entri `AGENTS.md` diperbarui

---

## 5. Jebakan yang Sudah Pernah Terjadi

| Jebakan | Gejala | Pencegahan |
|---|---|---|
| `slug` tidak sama dengan nama sub menu | Form tidak muncul di dashboard | Section 2.1 |
| `id_dash_menu` tidak ada di `dashboard_menu*` | Form tidak muncul, `AksesEhr` kosong | Verifikasi PK menu |
| Bandingkan ID dengan `==` | `"1.1" == "1.10"` → **true**, bug | Pakai `===` |
| `variabel` duplikat | Nilai saling timpa diam-diam | Sufiks angka |
| Lupa `akses_ehr` | 403 padahal form ada | Step 5 |
| `Str::studly('sbar')` = `Sbar` bukan `SBAR` | Folder salah, form `Unsupported` | Cek tabel Section 1 |
| `objek_id` bentrok antar form | Data tercampur antar form | ID baru monotonic |
| objects over-query | Halaman lambat di 500+ objek | Filter di admin Form |

---

## 6. Eksekusi Seeder

```bash
docker compose up -d db
docker compose exec app php artisan migrate:fresh --seed
```

> ⚠️ `migrate:fresh` **DROP semua tabel**. Backup dulu bila ada data nyata.
> Setelah seeder dijalankan, `GenerateHelper::resetSequence()` di
> `DatabaseSeeder` menyetel auto-increment — jangan bypass.

Kalau hanya menambah form EMR dan tidak ingin reset semua, jalankan seeder
tertentu lewat `tinker`, tapi **idempotensi** `EmrMasterSeeder` (`updateOrInsert`
berbasis PK) membuatnya aman diulang.