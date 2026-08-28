# AGENTS.md

Laravel 13 (PHP 8.3) SIMRS app ("MediTechV2") — an Indonesian hospital EMR. All code, commit messages, and UI text are in Indonesian; match that convention.

## Running everything through Docker

- No PHP is installed on the host. All PHP/artisan commands must run in the app container:
  `docker compose exec app php artisan <cmd>`
- The compose file has only two services: `app` (php-cli + `artisan serve` on port 8000) and `vite`. Both use `network_mode: host`. There is **no** `db`/`redis`/`queue` service in `docker-compose.yml` — the README describes an outdated 4-service stack; ignore it.
- The PostgreSQL server is **external** (`.env`: `DB_HOST=192.168.149.168`, DB `meditech`, user `postgres`). Only `pdo_pgsql` matters; there is no local DB to start.
- `.env` is gitignored but required. `.env.example` has placeholder DB values. Never commit `.env` (it contains the DB password).
- The `.env` file is injected at container start (`env_file`), so after editing it you must recreate the containers: `docker compose up -d --force-recreate app vite` — otherwise the running app keeps the old values (e.g. a changed `OBJEK_*`/`JENIS_RAWAT_*` constant or DB setting is not picked up).
- Vite runs in its own container; frontend assets load via `@vite` dev server.

## Schema conventions (28 model + 9 EMR tables + 1 view)

The original 295 migrations (generated via `kitloong/laravel-migrations-generator` from a legacy SQL dump) were **deleted and recreated from scratch**. Now there are **39 clean migrations** — one per table referenced by the 28 Models in `app/Models/` plus the 9 EMR tables used via `DB::table()` (`emr`, `emr_detail`, `form`, `objek`, `objek_form_control`, `dashboard_menu`, `dashboard_menu_sub`, `dashboard_menu_sub_extra`, `akses_ehr`) plus the `header_ehr` view plus `bed` (tabel legacy tanpa Model, dipakai query filter `ListPasienRanapController` — lihat catatan di bawah). The 227 legacy non-model tables and all legacy views were dropped.

- **Auto-increment PKs**: every PK (`{table}_id`) uses `$table->increments(...)` (Postgres `serial`). **Jangan set PK secara manual** di controller/seeder — biarkan Eloquent/DB mengisinya via auto-increment. `GenerateHelper::getNextId()` sudah dihapus.
- **`GenerateHelper::resetSequence($table, $pk = null)`**: dipanggil di akhir `DatabaseSeeder` untuk menyetel ulang sequence mengikuti ID maksimal setelah seeder mengisi ID eksplisit (sefault PK = `{table}_id`). Ini penting agar insert baru lewat aplikasi (auto-increment) tidak bentrok dengan ID seeder.
- **`GenerateHelper::generateNoMr()`**: `MAX(no_mr)+1` over **all** `pasien` rows (no `status_batal` filter — the `unique_no_mr` constraint covers soft-deleted rows too), padded to 7 digits.
- **No Laravel timestamps**: tables use `input_time`/`mod_time` (timestamp(6)) + `input_user_id`/`mod_user_id`. All models set `public $timestamps = false`.
- **Soft delete via `status_batal`** (null = active, 1 = deleted), not `deleted_at`.
- **Semua query GET (index/dropdown/opsi/relasi) wajib memfilter `status_batal`**: record aktif hanya yang `status_batal IS NULL` **atau** `status_batal = 0`; selain itu (1, dst.) berarti batal/terhapus. Pakai pola `where(function ($q) { $q->whereNull('status_batal')->orWhere('status_batal', 0); })` (atau `->where('status_batal', '!=', 1)` yang setara) — **jangan hanya `whereNull`** (record seeder bisa ber-`status_batal=0`). Berlaku juga untuk `join`/`whereHas`/relasi, kecuali ada alasan eksplisit menyertakan record batal (mis. `generateNoMr()`).
- Model PKs are custom (`user_id`, `menu_id`, ...) and some use `$primaryKey`, `$fillable` — check the model before assuming column names. Semua model membiarkan `$incrementing` default `true` (PK auto-increment).
- Tabel legacy yang **tidak** ada Model (mis. `bed` dan 227 tabel lain) **tidak dibuatkan migration** — halaman/pemakaian yang menyentuh tabel tersebut akan error sampai migration/schema-nya ditambahkan. Catatan: `bed` (dipakai `ListPasienRanapController`) sudah dibuatkan migration `2026_08_28_000039_create_bed_table` karena dibutuhkan fitur filter ranap.

## Migrations & view

- Semua migration bersih, pakai `Schema::create` + `$table->increments(...)`; yang sidah ada `with `header_ehr` dibuat via `DB::statement("CREATE OR REPLACE VIEW ...")`.
- `migrate` wraps each migration in a transaction (rolls back on failure), so a failed run leaves the DB clean.
- The database at the configured host already holds the **real legacy data**. `migrate:fresh` akan **DROP semua tabel** (termasuk ~227 tabel legacy + semua view yang tidak punya migration) lalu recreate hanya 38 objek di atas — destruktif & irreversibel, pastikan dulu sebelum dijalankan.

## Seeders

`php artisan db:seed` runs (in order): `ReferensiBagianSeeder`, `BagianSeeder` (18 Poli ref=1/RJ + 19 Ruang Perawatan ref=2/RI + 2 IGD ref=3/IGD: Instalasi Gawat Darurat & IRD Obgyn; menghapus permanen record lama lalu seed ulang ber-id berurutan dari 1), `ModulMenuSubMenuSeeder` (6 modul → 10 menu → 28 sub_menu; sub_menu 24–28 semuanya di menu 9 Manajemen Master: 24 "Wilayah" `file_sub_menu='wilayah'`, 25 "Master Nasabah" `'nasabah'`, 26 "Master Kelas" `'kelas'`, 27 "Jadwal Dokter" `'jadwal_dokter'`, 28 "ICD" `'icd'`), `FormObjekSeeder` (form, objek, `objek_form_control`, plus `profesi`/`dashboard_menu`/`dashboard_menu_sub`/`akses_ehr` — bagian tidak lagi di sini, ada di `BagianSeeder`), `MasterPegawaiSeeder` (jabatan 1–8 + `status_kepegawaian` 1–4 + `pegawai` 1–3: Administrator Sistem/Perawat Jaga/Dokter Jaga), `UserSeeder` (admin/perawat/dokter + `user_akses`; admin diberi akses sub_menu `range(1,28)`), `WilayahSeeder` (data contoh master wilayah: 3 provinsi → 12 kabupaten → 10 kecamatan → 20 kelurahan), `KelasRuangSeeder` (terakhir). All are idempotent and safe to re-run (modul/menu/sub_menu/users use `updateOrInsert` keyed on the PK; `user_akses` uses plain auto-increment now — `getNextId` is gone). `DatabaseSeeder` calls `GenerateHelper::resetSequence()` (15 tabel) di akhir untuk menyetel ulang sequence setelah seeder mengisi ID eksplisit, agar insert auto-increment berikutnya dari aplikasi tidak bentrok. `FormObjekSeeder` sudah dihapus — objek form di-seed langsung dari seeder yang memakainya. Users are linked to a `pegawai` via `users.pegawai_id` and `users.nama_pegawai` is copied from the pegawai record (admin→1, perawat→2, dokter→3).

## Auth is plain-text

`AuthController::login` compares `user_password` directly (no bcrypt), and `users.user_password` is `varchar(30)`. Do **not** use the default `UserFactory` (it targets `name`/`email`/`password` columns that don't exist). Seed passwords as plain strings.

## Select options tanpa tabel database

Daftar pilihan dropdown yang **tidak** tersimpan di database (19 key di `App\Helpers\SelectOption::all()`: `jenis_kelamin`, `agama`, `golongan_darah`, `status_perkawinan`, `kebangsaan`, `suku`, `pendidikan`, `pekerjaan`, `disabilitas`, `status_kepegawaian`, `triase_igd`, `triase_igd_obgyn`, `cara_masuk_igd`, `hubungan_penanggung`, `indikasi_igd_obgyn`, `hubungan_penanggung_obgyn`, `asal_pasien_ranap`, `hubungan_keluarga_ranap`, `kategori_icd`) dipusatkan di `App\Helpers\SelectOption`. Jangan hardcode `<option>` di blade — ambil via `\App\Helpers\SelectOption::get($key)` untuk datanya, atau `\App\Helpers\SelectOption::render($key, $selected = null, $placeholder = null)` untuk output `<option>` siap pakai. Tambah pilihan baru cukup di array `all()`. **Poliklinik/dokter/ruang TIDAK ada di sini** (18 key di atas; poliklinik/dokter diambil dari tabel `bagian`/`jadwal_dokter`/`pegawai` — lihat section Jadwal Dokter) dan **kelas perawatan juga TIDAK di sini** — diambil dari tabel `kelas_ruang` (lihat section Master Kelas).

## Modular structure (semua modul)

The same modular layout applies to **every** modul, menu, and sub_menu in the sidebar:

- **Controllers**: `app/Http/Controllers/{Modul}/{Menu}/{SubMenu}/{SubMenu}Controller.php` — folder = StudlyCase of the modul name, the menu name, then the sub-menu. Examples: `Registrasi/Pendaftaran/DaftarRajal/DaftarRajalController.php`, `Registrasi/Pasien/DataPasien/DataPasienController.php`, `EMR/EmrDashboard/EmrDashboardController.php`, `Administrator/ManajemenUser/User/UserController.php`.
- **Views**: `resources/views/moduls/{modul}/{menu}/{sub_menu}/...` — folder **sama persis dengan path controller PascalCase** (misal `moduls/Registrasi/Pendaftaran/DaftarRajal/`, `moduls/EMR/Soap/`, `moduls/Administrator/ManajemenMaster/Pegawai/`). File blade CRUD memakai nama `index/create/edit.blade.php`; view landing non-CRUD memakai nama Pascal ala controller (misal `DaftarRajal.blade.php`, `ListPasienRanap.blade.php`, `NasabahPasien.blade.php`). Panggil via `view('moduls.{PascalPath}.{File}')` (titik, bukan slash), contoh `view('moduls.RawatInap.Pasien.ListPasien.ListPasienRanap')`. Sub-folder partial EMR tetap di `moduls/EMR/PartialForm/`.
- **`sub_menu.file_sub_menu`** sekarang menyimpan **path relatif ke view** (bisa ber-slash, misal `master/users`), bukan sekadar nama file. Sidebar merender link via `url($subMenu->file_sub_menu)`. Dua sumber route yang tersedia untuk path tsb:
  - Route **eksplisit** di `routes/web.php` (prioritas lebih tinggi) — untuk menu yang butuh data dari controller.
  - Route **otomatis** (lihat section *Auto-route sub_menu*) — untuk view statis/landing tanpa controller.
  Gunakan `'#'` hanya untuk sub-menu yang butuh id dinamis (EMR forms).

## Auto-route sub_menu (buat menu tanpa route manual di web.php)

`SubMenuRouteServiceProvider` (`bootstrap/providers.php`) mendaftarkan route GET secara otomatis untuk setiap `sub_menu` aktif yang `file_sub_menu`-nya bukan `'#'`:

- **Cara kerja**: di `$this->app->booted()` (sehabis web.php dimuat, jadi route eksplisit selalu menang), ia mengecek daftar `file_sub_menu` aktif dari tabel `sub_menu` (cache 24h, key `sub_menu_route_paths`), lalu untuk tiap path yang **belum** ada route-nya, daftarkan `Route::get('/{path}', SubMenuViewController::class)` dengan middleware `web`+`auth` dan nama `modul_view.{slug}`.
- **Cara pakai**: cukup buat view `resources/views/moduls/{path}.blade.php` (misal `moduls/master/users.blade.php` untuk `file_sub_menu='master/users'`), lalu isi `file_sub_menu` sebesar path itu di form Sub Menu. Route `/master/users` langsung tersedia — **tanpa** menulis route di web.php. View tetap wajib `@extends('layouts.app')` + `@section('content')`.
- `SubMenuViewController` (invokable, `App\Http\Controllers\SubMenu`) me-render view `moduls.{path}` dari path yang diminta; memakai `.web`, path yang invalid/traversal (`..`, dll.) ditolak (404), dan view yang tidak ada → 404.
- **Cache**: simpan/ubah/hapus sub_menu di `SubMenuController` memanggil `SubMenuRouteServiceProvider::flushPathCache()` (bersamaan dengan clear sidebar cache) → path otomatis ter-refresh. Route list dibangun per request, jadi tak perlu `php artisan route:cache` untuk dev (`artisan serve`).
- **Route cache manual** (`php artisan route:cache`) didukung penuh karena route memakai controller (bukan closure) — setelah `route:cache`, daftar path di-*snap*; tambah/ubah sub_menu baru lalu jalankan ulang `route:cache`. Jangan lupa `route:clear` saat kembali ke dev.

## Sidebar / menu behavior

- `SidebarComposer` builds the sidebar from `modul` → `menu` → `sub_menu`, filtered by the user's `user_akses.sub_menu_id`, and caches it 24h keyed `sidebar_moduls_user_{user_id}`. After changing modul/menu/sub_menu/user_akses, clear the cache (logout already does `Cache::forget`).
- A modul/menu only shows if it has at least one sub_menu assigned to the user.

## Administrator module (master data CRUD)

- Modul 6 "Administrator" drives CRUD pages for modul/menu/sub_menu/bagian/referensi_bagian_id/profesi/jabatan/pegawai/user/wilayah/nasabah/kelas/jadwal_dokter/icd. Resource routes at the root URI equal to `file_sub_menu` (`/modul`, `/menu`, `/sub_menu`, `/bagian`, `/referensi_bagian_id`, `/profesi`, `/jabatan`, `/pegawai`, `/user`, `/wilayah`, `/nasabah`, `/kelas`, `/jadwal_dokter`, `/icd`) but route names stay `admin.modul.*`, `admin.menu.*`, `admin.sub_menu.*`, `admin.bagian.*`, `admin.referensi_bagian_id.*`, `admin.profesi.*`, `admin.jabatan.*`, `admin.pegawai.*`, `admin.user.*`, `admin.wilayah.*`, `admin.nasabah.*`, `admin.kelas.*`, `admin.jadwal_dokter.*`, `admin.icd.*`.
- Menus: 9 "Manajemen Master" → `manajemen_master`, 10 "Manajemen User" → `manajemen_user` (see the modular structure section above for the full path pattern).
- All writes run inside `DB::beginTransaction()` and allocate PKs via Eloquent/DB auto-increment (no `getNextId`; mind the PK quirk above — `users` uses `'user_id'`; `resetSequence` is only used in seeders).
- Creating/editing a user **requires** `pegawai_id` (validated `required|exists:pegawai,pegawai_id`); `users.nama_pegawai` is copied from the linked pegawai record, so there is no manual name field in the user form.
- Every store/update/destroy clears the sidebar cache for **all** users (`Cache::forget('sidebar_moduls_user_'.$id)` per user), unlike logout which only clears the current user.

## Master wilayah (provinsi/kabupaten/kecamatan/kelurahan)

- Empat tabel `provinsi`/`kabupaten`/`kecamatan`/`kelurahan` **tidak punya kolom audit** (`input_time`/`mod_time`/`input_user_id`/`mod_user_id`) — hanya PK, parent id, `nama_*`, `status_batal smallint`, `kode_wilayah_*`. Jangan set kolom audit di seeder/controller. Model-nya (`App\Models\{Provinsi,Kabupaten,Kecamatan,Kelurahan}`) punya scope `aktif()` (`status_batal != 1 OR null`) dan relasi berantai (`provinsi→kabupaten→kecamatan→kelurahan`).
- CRUD wilayah di `Administrator\ManajemenMaster\Wilayah\WilayahController` memakai satu route resource `/wilayah` + query `?tab=provinsi|kabupaten|kecamatan|kelurahan` (form/index dibedakan per tab). Simpan record via pola `new Model; assign; save()` — `Model::create()` gagal karena PK tidak ada di `$fillable` (kolom PK NOT NULL → "Not null violation"). Soft-delete cascade (hapus provinsi → semua kabupaten/kecamatan/kelurahan di bawahnya, dst.) wajib pakai `whereIn('col', $ids->all())`, bukan `where('col', $collection)` (Query Builder menganggap Collection sebagai scalar → `Invalid parameter number`).
- API Select2 untuk cascade: `api.wilayah.{provinsi,kabupaten,kecamatan,kelurahan}` di `ApiWilayahController`, balikan `{results:[{id,text}]}`, difilter parent lewat `provinsi_id`/`kabupaten_id`/`kecamatan_id`, urut nama, hanya `aktif()`.
- Form pasien (tambah/edit) menyimpan hanya `kelurahan_id`; blok `.wilayah-cascade` berisi 4 `<select data-wilayah="..." data-url="...">` yang dimuat berjenjang dari `resources/js/select.js` (`initWilayahCascade`). Prefill edit dikirim dari controller sebagai variabel `$prefill` lalu dirender `data-prefill='@json($prefill)'` — **jangan** tulis array literal multi-elemen langsung di argumen `@json([...])`: `compileJson` memecah argumen dengan `explode(',')` sehingga array terpotong dan menghasilkan ParseError.

## Pencarian pasien & Select2 (resources/js/select.js)

- Semua init Select2 ada di `resources/js/select.js` (jQuery + Select2 dibundle via Vite; `app.js` hanya `import './select'` + handler drag-scroll untuk elemen `.drag-scroll`).
- Pencarian pasien (`.select2-pasien`), dipakai via komponen `x-select_pasien` (props `label`/`required`/`selected`): **preload semua pasien via `$.getJSON(url, {limit:1000})` → `<option>` asli → Select2 statis + matcher klien**. JANGAN ganti ke modul ajax Select2 untuk pencarian pasien — dropdown hasilnya kosong saat dibuka (sudah pernah dicoba & gagal).
- `pasangSelect2($el, opts)` menghancurkan instance lama (`select2('destroy')`) sebelum init ulang; Select2 mengabaikan opsi baru bila elemen sudah ter-init.
- Selector lain: statis `.select2`/`.select2-poliklinik`/`.select2-ruangan`/`.select2-dokter`; cascade wilayah via `.wilayah-cascade` (lihat section Master wilayah).
- Komponen select reusable (class-based di `app/View/Components/`): `x-select_poliklinik` (props `selected`/`name`/`id`/`label`/`placeholder`/`required`, data `bagian` referensi_bagian_id=RAJAL via filter `status_batal!=1 OR NULL` — jangan `whereNull`, record seeder ber-`status_batal=0`) dan `x-select_dokter` (data `pegawai` profesi_id=1 aktif, class `select2-dokter`, dipakai di form Jadwal Dokter).

## Nasabah (master + relasi pasien)

- **Master nasabah** (tabel `nasabah`) dikelola di Administrator → Manajemen Master → "Master Nasabah": controller `Administrator\ManajemenMaster\Nasabah\NasabahController`, route `Route::resource('nasabah', ...)->names('admin.nasabah')` (URI `/nasabah`), views `moduls/administrator/manajemen_master/nasabah/{index,create,edit}.blade.php`. Kolom `instalasi` (json, cast `array` di model) diisi checkbox bernilai `env('JENIS_RAWAT_*')` (RJ/RI/IGD/MCU).
- **Nasabah Pasien** (tabel `pasien_nasabah` = link pasien → penjamin) ada di Registrasi → Pasien → "Nasabah Pasien": controller `Registrasi\Pasien\NasabahPasien\NasabahPasienController`, route `/nasabah_pasien` (`nasabah_pasien.*`), views `moduls/registrasi/pasien/data_nasabah_pasien/`.
- Form Nasabah Pasien memilih **Nasabah** dari dropdown berisi record tabel `nasabah` (controller mengirim `$nasabahs` = nasabah aktif via `nasabahAktif()`); `resolveNasabah()` memakai `nasabah_id` terpilih, atau fallback `findOrCreateNasabah('')` → "Umum / Mandiri". **`jenis_nasabah` dihapus total**: tidak ada dropdown jenis (dulu dari `SelectOption`), kolom `jenis_nasabah` dibiarkan null; hak kelas rawat diambil dari tabel `kelas_ruang` (`pasien_nasabah.hak_kelas_id` → `kelas_ruang.kelas_ruang_id`, lihat section Master Kelas), bukan dari SelectOption.

## Master Kelas

- **Master kelas** (tabel `kelas_ruang`: `kelas_ruang_id` int PK, `nama_kelas_ruang` string, `kelas_khusus` varchar(10), `kelas_bpjs` smallint, + kolom audit `input_time`/`mod_time`/`input_user_id`/`mod_user_id` + `status_batal`) dikelola di Administrator → Manajemen Master → "Master Kelas" (sub_menu 26, `file_sub_menu='kelas'`): controller `Administrator\ManajemenMaster\Kelas\KelasController`, route `Route::resource('kelas', ...)->names('admin.kelas')` (URI `/kelas`), views `moduls/administrator/manajemen_master/kelas/{index,create,edit}.blade.php`.
- Model `App\Models\KelasRuang` punya `$primaryKey='kelas_ruang_id'` dan scope `aktif()`. Seeder data awal (`KelasRuangSeeder`, dipanggil terakhir di `DatabaseSeeder`): Kelas 1/2/3 (kelas_bpjs 1/2/3) + VIP/VVIP (`kelas_khusus='KHUSUS'`).
- Semua dropdown kelas perawatan (form Nasabah Pasien via `$kelas`, daftar ranap via `$kelasList`, label "Hak Kelas" di index via `$kelasMap`) memakai `KelasRuang::aktif()` — JANGAN pakai `SelectOption::get('kelas_perawatan')` (entry sudah dihapus dari `SelectOption`).

## Jadwal Dokter

- **Jadwal dokter** (tabel `jadwal_dokter`: `jadwal_dokter_id` int PK, `pegawai_id` → dokter, `bagian_id` → poliklinik, `hari` int 1–7 (1=Senin..7=Minggu, mapping di `JadwalDokterController::HARI`), `waktu_mulai`/`waktu_selesai` time, `kuota` int, `ruang_praktek` varchar(10), + kolom audit & `status_batal`) dikelola di Administrator → Manajemen Master → "Jadwal Dokter" (sub_menu 27, `file_sub_menu='jadwal_dokter'`): controller `Administrator\ManajemenMaster\JadwalDokter\JadwalDokterController`, route `Route::resource('jadwal_dokter', ...)->names('admin.jadwal_dokter')` (URI `/jadwal_dokter`), views `moduls/administrator/manajemen_master/jadwal_dokter/{index,create,edit}.blade.php`.
- Model `App\Models\JadwalDokter` punya relasi `pegawai()` & `bagian()` + scope `aktif()`. Dropdown Dokter hanya pegawai `profesi_id=1` (Dokter); dropdown Poliklinik = `bagian` `referensi_bagian_id=1` (RAWAT JALAN).
- **Dipakai di pendaftaran rawat jalan**: `DaftarRajalController` memfilter dropdown Poliklinik (distinct `bagian` dari jadwal aktif) & Dokter (distinct dokter dari jadwal aktif); tiap `<option>` dokter punya `data-bagian` (id poli yang dijadwal) dan JS `filterDokter()` menampilkan hanya dokter yang praktik di poli terpilih. JANGAN ganti ke `SelectOption::get('poliklinik')`/`('dokter_rj')` (entry memang tidak pernah ada).
- **Duplikat jadwal dicek di controller**: seorang dokter tidak boleh punya jadwal ganda di hari (`hari`) + poliklinik (`bagian_id`) yang sama — dicek `cekJadwalDuplikat($pegawaiId, $hari, $bagianId, $ignoreId = null)` di `store`/`update` (hanya record `aktif()`; `ignoreId` mengecualikan record yang sedang diedit). Dokter BOLEH praktik di poli berbeda di hari yang sama. `waktu_mulai`/`waktu_selesai` wajib format 24 jam (`date_format:H:i`) dan `kuota` wajib diisi.
- Gotcha: `$request->validate()` tidak menyertakan key nullable yang tidak dikirim — method `validated()` di controller (nasabah, jadwal dokter, dll.) wajib `array_merge` default `null` untuk semua field opsional, kalau tidak muncul "Undefined array key".

## Master ICD

- **Master ICD** (tabel `icd`: `icd_id` int PK, `kode_diagnosa` varchar(10), `nama_diagnosa` string, `kategori` varchar(10), `jenis_diagnosa` int, `penyakit_id` int, + kolom audit & `status_batal`) dikelola di Administrator → Manajemen Master → "ICD" (sub_menu 28, `file_sub_menu='icd'`): controller `Administrator\ManajemenMaster\Icd\IcdController`, route `Route::resource('icd', ...)->names('admin.icd')` (URI `/icd`), views `moduls/administrator/manajemen_master/icd/{index,create,edit}.blade.php`.
- Form menyimpan hanya `kode_diagnosa` (wajib) + `nama_diagnosa` (wajib) + `kategori` (opsional); `jenis_diagnosa`/`penyakit_id` di-null-kan (field legacy, belum dipakai form). `kategori` dipilih via `<select>` dari `SelectOption::render('kategori_icd', ...)` (opsi ICD-10 / ICD-9). Model `App\Models\ICD` punya `$primaryKey='icd_id'` dan scope `aktif()`.

## EMR dynamic forms

- Route `/emr/form/{form_name}/{registrasi_detail_id}/{emr_id?}` (`emr.dynamic.index`): `DynamicFormController` first tries `App\Http\Controllers\EMR\{Studly}\{Studly}Controller`, else the view `moduls.emr.{slug}.index`.
- Form/objek IDs are read from `.env` at runtime (e.g. `env('FORM_ID_SOAP')`, `env('OBJEK_ID_SISTOLIK')`, `env('JENIS_RAWAT_RJ')`), not from `config()`. Don't `config:cache` with these unset or the app loses its SIMRS constants.
- EMR dashboard menus come from the `header_ehr` view (built from `dashboard_menu`/`dashboard_menu_sub`/`dashboard_menu_sub_extra`; its `id_dash_menu` is the concatenated IDs) joined to `form.id_dash_menu` and `akses_ehr.profesi_id`; `profesi_id` comes from `session('profesi_id', 1)` and form flags (`ri`/`rj`/`igd`/`mcu`) control which forms appear per rawat type.

## Tests / lint

- Tests are phpunit (only the two `ExampleTest` stubs exist). `phpunit.xml` forces `sqlite :memory:`, so DB-bound code won't run under tests as-is; use `php artisan test` inside the container (`docker compose exec app php artisan test`).
- Formatting: `vendor/bin/pint` (no config file committed). No PHPStan/Psalm, no CI.
- `composer dev` runs `artisan serve` + queue + pail + vite via concurrently — only useful inside the app container.
