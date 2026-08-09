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

## Legacy DB schema conventions (critical)

The 294 migrations were generated from a pre-existing SIMRS PostgreSQL database (dev dep `kitloong/laravel-migrations-generator`). They are **not** standard Laravel:

- **No auto-increment**: every PK is a plain `integer` with no sequence. Inserts (e.g. seeders) must supply explicit IDs and use `updateOrInsert` keyed on the PK.
- **`GenerateHelper::getNextId($table, $pk = null)`** (in `App\Helpers\GenerateHelper` — replaces the deleted `SequenceHelper`): resolves `nextval` on candidate sequences, else falls back to `MAX+1`. It checks `pg_class` before `nextval` — a raw `nextval` on a missing sequence aborts any open PostgreSQL transaction (the `MAX` fallback then also fails), so never call `nextval` directly inside a transaction without the existence check. Pass the real PK explicitly when the column is not `{table}_id` (e.g. `getNextId('users', 'user_id')`; the default would wrongly build `users_id`).
- **`GenerateHelper::generateNoMr()`**: `MAX(no_mr)+1` over **all** `pasien` rows (no `status_batal` filter — the `unique_no_mr` constraint covers soft-deleted rows too), padded to 7 digits.
- **No Laravel timestamps**: tables use `input_time`/`mod_time` (timestamp(6)) + `input_user_id`/`mod_user_id`. All models set `public $timestamps = false`.
- **Soft delete via `status_batal`** (null = active, 1 = deleted), not `deleted_at`.
- Model PKs are custom (`user_id`, `menu_id`, ...) and some use `$primaryKey`, `$fillable` — check the model before assuming column names.

## Migrations gotchas

- Many `011341` migrations run raw `DB::statement("CREATE VIEW ...")` against the live DB. Some require an existing extension/function; if a fresh-DB error appears, that's expected until the extension is installed.
- `pg_stat_statements` view migrations: must first run `CREATE EXTENSION IF NOT EXISTS pg_stat_statements`, and the extension itself creates those views as members — so use `CREATE OR REPLACE VIEW` (a plain `DROP VIEW` fails with "extension requires it").
- `2026_07_26_999999_add_missing_indexes_and_foreign_keys` wraps every statement in try/catch and prints `Failed to apply index on X` — those messages are non-fatal by design, don't "fix" them.
- `migrate` wraps each migration in a transaction (rolls back on failure), so a failed run leaves the DB clean.
- The database at the configured host already holds the real legacy data; running `migrate`/`db:seed` against it writes to a live system.

## Seeders

`php artisan db:seed` runs (in order): `ReferensiBagianSeeder`, `BagianSeeder` (18 Poli ref=1/RJ + 19 Ruang Perawatan ref=2/RI + 2 IGD ref=3/IGD: Instalasi Gawat Darurat & IRD Obgyn; menghapus permanen record lama lalu seed ulang ber-id berurutan dari 1), `ModulMenuSubMenuSeeder` (6 modul → 10 menu → 25 sub_menu; sub_menu 24 "Wilayah" dan 25 "Master Nasabah" ada di menu 9 Manajemen Master dengan `file_sub_menu='wilayah'`/`'nasabah'`), `FormObjekSeeder` (form, objek, `objek_form_control`, plus `profesi`/`dashboard_menu`/`dashboard_menu_sub`/`akses_ehr` — bagian tidak lagi di sini, ada di `BagianSeeder`), `MasterPegawaiSeeder` (jabatan 1–8 + `status_kepegawaian` 1–4 + `pegawai` 1–3: Administrator Sistem/Perawat Jaga/Dokter Jaga), `UserSeeder` (admin/perawat/dokter + `user_akses`; admin diberi akses sub_menu `range(1,25)`), `WilayahSeeder` (data contoh master wilayah: 3 provinsi → 12 kabupaten → 10 kecamatan → 20 kelurahan). All are idempotent and safe to re-run (modul/menu/sub_menu/users use `updateOrInsert` keyed on the PK; `user_akses` keys on `(user_id, sub_menu_id)` and only assigns `user_akses_id` for new rows via `GenerateHelper::getNextId`). Users are linked to a `pegawai` via `users.pegawai_id` and `users.nama_pegawai` is copied from the pegawai record (admin→1, perawat→2, dokter→3).

## Auth is plain-text

`AuthController::login` compares `user_password` directly (no bcrypt), and `users.user_password` is `varchar(30)`. Do **not** use the default `UserFactory` (it targets `name`/`email`/`password` columns that don't exist). Seed passwords as plain strings.

## Select options tanpa tabel database

Daftar pilihan dropdown yang **tidak** tersimpan di database (jenis kelamin, agama, golongan darah, status perkawinan, jaminan, shift, poliklinik, dokter/ruang/bed, triase, cara masuk, hubungan, dll.) dipusatkan di `App\Helpers\SelectOption`. Jangan hardcode `<option>` di blade — ambil via `\App\Helpers\SelectOption::get($key)` untuk datanya, atau `\App\Helpers\SelectOption::render($key, $selected = null, $placeholder = null)` untuk output `<option>` siap pakai. Tambah pilihan baru cukup di array `all()`. **Kelas perawatan TIDAK di sini** — diambil dari tabel `kelas_ruang` (lihat section Master Kelas).

## Modular structure (semua modul)

The same modular layout applies to **every** modul, menu, and sub_menu in the sidebar:

- **Controllers**: `app/Http/Controllers/{Modul}/{Menu}/{SubMenu}/{SubMenu}Controller.php` — folder = StudlyCase of the modul name, the menu name, then the sub-menu. Examples: `Registrasi/Pendaftaran/DaftarRajal/DaftarRajalController.php`, `Registrasi/Pasien/DataPasien/DataPasienController.php`, `EMR/EmrDashboard/EmrDashboardController.php`, `Administrator/ManajemenUser/User/UserController.php`.
- **Views**: `resources/views/moduls/{modul}/{menu}/{sub_menu}/...` — folders are snake_case: e.g. `moduls/registrasi/pendaftaran/daftar_rj/daftar_rajal.blade.php`, `moduls/emr/soap/index.blade.php`, `moduls/administrator/manajemen_master/pegawai/index.blade.php`. The sub-menu's landing view file is named after `file_sub_menu`.
- **`sub_menu.file_sub_menu`** stores the sub-menu file name (e.g. `daftar_rajal`, `modul`, `pegawai`), not a path — the sidebar renders it via `url($subMenu->file_sub_menu)` and a matching route exists at the same URI. Use `'#'` only for sub-menus that need a dynamic id (EMR forms).

## Sidebar / menu behavior

- `SidebarComposer` builds the sidebar from `modul` → `menu` → `sub_menu`, filtered by the user's `user_akses.sub_menu_id`, and caches it 24h keyed `sidebar_moduls_user_{user_id}`. After changing modul/menu/sub_menu/user_akses, clear the cache (logout already does `Cache::forget`).
- A modul/menu only shows if it has at least one sub_menu assigned to the user.

## Administrator module (master data CRUD)

- Modul 6 "Administrator" drives CRUD pages for modul/menu/sub_menu/bagian/profesi/jabatan/pegawai/user/wilayah/nasabah/kelas. Resource routes at the root URI equal to `file_sub_menu` (`/modul`, `/menu`, `/sub_menu`, `/bagian`, `/profesi`, `/jabatan`, `/pegawai`, `/user`, `/wilayah`, `/nasabah`, `/kelas`) but route names stay `admin.modul.*`, `admin.menu.*`, `admin.sub_menu.*`, `admin.bagian.*`, `admin.profesi.*`, `admin.jabatan.*`, `admin.pegawai.*`, `admin.user.*`, `admin.wilayah.*`, `admin.nasabah.*`, `admin.kelas.*`.
- Menus: 9 "Manajemen Master" → `manajemen_master`, 10 "Manajemen User" → `manajemen_user` (see the modular structure section above for the full path pattern).
- All writes run inside `DB::beginTransaction()` and allocate PKs via `GenerateHelper::getNextId` (mind the PK quirk above — `users` uses `'user_id'`).
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
- Selector lain: statis `.select2`/`.select2-poliklinik`/`.select2-ruangan`; cascade wilayah via `.wilayah-cascade` (lihat section Master wilayah).

## Nasabah (master + relasi pasien)

- **Master nasabah** (tabel `nasabah`) dikelola di Administrator → Manajemen Master → "Master Nasabah": controller `Administrator\ManajemenMaster\Nasabah\NasabahController`, route `Route::resource('nasabah', ...)->names('admin.nasabah')` (URI `/nasabah`), views `moduls/administrator/manajemen_master/nasabah/{index,create,edit}.blade.php`. Kolom `instalasi` (json, cast `array` di model) diisi checkbox bernilai `env('JENIS_RAWAT_*')` (RJ/RI/IGD/MCU).
- **Nasabah Pasien** (tabel `pasien_nasabah` = link pasien → penjamin) ada di Registrasi → Pasien → "Nasabah Pasien": controller `Registrasi\Pasien\NasabahPasien\NasabahPasienController`, route `/nasabah_pasien` (`nasabah_pasien.*`), views `moduls/registrasi/pasien/data_nasabah_pasien/`.
- Form Nasabah Pasien memilih **Nasabah** dari dropdown berisi record tabel `nasabah` (controller mengirim `$nasabahs` = nasabah aktif via `nasabahAktif()`); `resolveNasabah()` memakai `nasabah_id` terpilih, atau fallback `findOrCreateNasabah('')` → "Umum / Mandiri". **`jenis_nasabah` dihapus total**: tidak ada dropdown jenis (dulu dari `SelectOption`), kolom `jenis_nasabah` dibiarkan null; hak kelas rawat diambil dari tabel `kelas_ruang` (`pasien_nasabah.hak_kelas_id` → `kelas_ruang.kelas_ruang_id`, lihat section Master Kelas), bukan dari SelectOption.

## Master Kelas

- **Master kelas** (tabel `kelas_ruang`: `kelas_ruang_id` int PK, `nama_kelas_ruang` string, `kelas_khusus` varchar(10), `kelas_bpjs` smallint, + kolom audit `input_time`/`mod_time`/`input_user_id`/`mod_user_id` + `status_batal`) dikelola di Administrator → Manajemen Master → "Master Kelas" (sub_menu 26, `file_sub_menu='kelas'`): controller `Administrator\ManajemenMaster\Kelas\KelasController`, route `Route::resource('kelas', ...)->names('admin.kelas')` (URI `/kelas`), views `moduls/administrator/manajemen_master/kelas/{index,create,edit}.blade.php`.
- Model `App\Models\KelasRuang` punya `$primaryKey='kelas_ruang_id'` dan scope `aktif()`. Seeder data awal (`KelasRuangSeeder`, dipanggil terakhir di `DatabaseSeeder`): Kelas 1/2/3 (kelas_bpjs 1/2/3) + VIP/VVIP (`kelas_khusus='KHUSUS'`).
- Semua dropdown kelas perawatan (form Nasabah Pasien via `$kelas`, daftar ranap via `$kelasList`, label "Hak Kelas" di index via `$kelasMap`) memakai `KelasRuang::aktif()` — JANGAN pakai `SelectOption::get('kelas_perawatan')` (entry sudah dihapus dari `SelectOption`).
- Gotcha: `$request->validate()` tidak menyertakan key nullable yang tidak dikirim — method `validated()` di kedua controller nasabah wajib `array_merge` default `null` untuk semua field opsional, kalau tidak muncul "Undefined array key".

## EMR dynamic forms

- Route `/emr/form/{form_name}/{registrasi_detail_id}/{emr_id?}` (`emr.dynamic.index`): `DynamicFormController` first tries `App\Http\Controllers\EMR\{Studly}\{Studly}Controller`, else the view `moduls.emr.{slug}.index`.
- Form/objek IDs are read from `.env` at runtime (e.g. `env('FORM_ID_SOAP')`, `env('OBJEK_ID_SISTOLIK')`, `env('JENIS_RAWAT_RJ')`), not from `config()`. Don't `config:cache` with these unset or the app loses its SIMRS constants.
- EMR dashboard menus come from the `header_ehr` view (built from `dashboard_menu`/`dashboard_menu_sub`/`dashboard_menu_sub_extra`; its `id_dash_menu` is the concatenated IDs) joined to `form.id_dash_menu` and `akses_ehr.profesi_id`; `profesi_id` comes from `session('profesi_id', 1)` and form flags (`ri`/`rj`/`igd`/`mcu`) control which forms appear per rawat type.

## Tests / lint

- Tests are phpunit (only the two `ExampleTest` stubs exist). `phpunit.xml` forces `sqlite :memory:`, so DB-bound code won't run under tests as-is; use `php artisan test` inside the container (`docker compose exec app php artisan test`).
- Formatting: `vendor/bin/pint` (no config file committed). No PHPStan/Psalm, no CI.
- `composer dev` runs `artisan serve` + queue + pail + vite via concurrently — only useful inside the app container.
