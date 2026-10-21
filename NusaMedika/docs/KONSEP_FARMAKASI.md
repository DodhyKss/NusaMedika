# KONSEP FORM FARMAKASI KLINIS (EMR)

**Status:** Rancangan / Konsep — belum diimplementasikan
**Tanggal:** 2026-10-10
**Target:** form_id 45–48 · objek_id 397–431 · `dashboard_menu` 11 (baru)
**Acuan:** [`PANDUAN_IMPLEMENTASI_FORM_EMR.md`](PANDUAN_IMPLEMENTASI_FORM_EMR.md),
[`ANALISIS_KEKURANGAN_FORM.md`](ANALISIS_KEKURANGAN_FORM.md)
**Sumber legacy:** `/simrs_tenriawaru/FE/lib/modul/`

---

## 1. Ringkasan

NusaMedika sudah punya **Order Resep** (form 5) — itu sisi peresepan. Yang belum
ada adalah sisi **farmasi klinik**: daftar terapi obat yang sedang berjalan,
rekonsiliasi obat saat admisi, telaah resep oleh farmasis, dan telaah obat saat
dispense. Keempat form ini menutup domain 15 pada `ANALISIS_KEKURANGAN_FORM.md`.

| form_id | nama_form | slug | id_dash_menu | ri | rj | igd | mcu |
|---|---|---|---|---|---|---|---|
| 45 | Daftar Terapi Obat | `daftar_terapi_obat` | `11.173` | 1 | 1 | 1 | 0 |
| 46 | Rekonsiliasi Obat | `rekonsiliasi_obat` | `11.174` | 1 | 1 | 1 | 0 |
| 47 | Telaah Resep | `telaah_resep` | `11.175` | 0 | 1 | 1 | 0 |
| 48 | Telaah Obat | `telaah_obat` | `11.176` | 1 | 0 | 0 | 0 |

> MCU tidak termasuk — Outside context MCU medication goes through form MCU 15.
> Form 47/48 mengikuti Episode II (resep & dispense) sehingga tersedia di RJ.

### 1.1 Alokasi `dashboard_menu` & sub-menu

```
dashboard_menu (BARU)
└── 11 = "Farmasi"                                [BARU — id 11 belum dipakai]
    ├── sub 173 = "Daftar Terapi Obat"   → form 45  id_dash_menu "11.173"
    ├── sub 174 = "Rekonsiliasi Obat"    → form 46  id_dash_menu "11.174"
    ├── sub 175 = "Telaah Resep"         → form 47  id_dash_menu "11.175"
    └── sub 176 = "Telaah Obat"          → form 48  id_dash_menu "11.176"
```

`dashboard_menu_sub_id` bersifat **GLOBAL**. Band `KONSEP_FARMAKASI.md`
adalah **173–192** (`ALOKASI_ID_GLOBAL.md` §3); file ini memakai **173–176**.
Angka 1–4 pada daftar di atas adalah **urutan posisi** di bawah menu 11,
bukan PK.
Tanpa `dashboard_menu_sub_extra` → `id_dash_menu` = `"11.173"`, dst.
Semua **tanpa extra** agar bisa diklik langsung sebagai link form.

### 1.2 Alokasi objek

| Form | Objek baru | Rentang |
|---|---|---|
| 45 Daftar Terapi Obat | 397–401 | 5 |
| 46 Rekonsiliasi Obat | 402–408 | 7 |
| 47 Telaah Resep | 409–419 | 11 |
| 48 Telaah Obat | 420–425 | 6 |
| **Total** | | **397–425 (29 objek)** |

> **Id band 397–431 yang tidak terpakai:** 426–431 (6 id dicadangkan, tidak
> dideklarasikan di `$objeks`). Band objek tidak berubah dan `id_dash_menu` tetap
> `11.173`–`11.176`. Objek pernah berlubang (403–407, 412) dan kini
> direnumber menjadi 397–425 tanpa lompatan ke band dokumen lain.

Objek **yang di-reuse**:

| objek_id | nama_objek | Dipakai di |
|---|---|---|
| 69 | Obat | 45 `obat_N` · 46 `*_nama_obat_N` (nama disimpan, bukan id) |
| 70 | Jumlah | 45 `jumlah_N` |
| 71 | S 1 | 45 `s_1_N` · 46 `*_freq_1_N` |
| 72 | S 2 | 45 `s_2_N` · 46 `*_freq_2_N` |
| 73 | Aturan Pakai | 45 `aturan_pakai_N` · 46 `*_dosis_N` |
| 74 | Rute Pemberian | 45 `rute_N` · 46 `*_rute_N` |
| 76 | Prioritas | 47, 48 |
| 77 | Keterangan | 45 `keterangan` · 46 `*_keterangan_N` · 47, 48 |
| 82 | Petugas Pelaksana | 47 `petugas_id` · 48 `petugas_id` |

> **Sengaja objek 69–74 dipakai untuk BARIS BERSUFFIX, bukan satu baris tanpa
> suffix.** Legacy memakai indeks array (`barang_id[]`, `sigma_1[barang_id]`) yang
> tidak bisa dipetakan ke `emr_detail`. NusaMedika memakai pola yang sudah
> dibuktikan di form 10 Tindakan Medis: `obat_1..obat_20`, `jumlah_1..jumlah_20`
> (§2.2 panduan). Detail di §7.

---

## 2. Sumber Referensi Legacy

| Yang diambil | File legacy |
|---|---|
| Daftar obat terapi berjalan, switch Stop Obat, ubah dosis × frekuensi, kode status pemberian | `simrs_tenriawaru/FE/lib/modul/daftar_terapi_obat.php` (+ `daftar_terapi_obat_stop_act.php`, `ajax/input_terapi_obat.php`) |
| Rekonsiliasi 3 tab (Admisi IGD / Ruangan Perawatan / Pulang) + edukasi pulang | `simrs_tenriawaru/FE/lib/modul/rekonsiliasi_obat.php` |
| Kriteria telaah resep (7 butir) | `simrs_tenriawaru/FE/lib/modul/template/telaah_resep_dispense.php` + helper `telaah_resep()` di `FE/lib/func/_func.php:687` |
| Kriteria telaah obat (5 butir) | `simrs_tenriawaru/FE/lib/modul/template/telaah_obat_dispense.php` + helper `telaah_obat()` di `_func.php:701` |
| Konteks pemanggilan telaah (terikat pada satu resep) | `simrs_tenriawaru/FE/lib/modul/entry_resep.php` |
| Sumber rute & satuan | helper `rute_pemberian_obat()`, `satuan_aturan_pakai()` di `_func.php` |

### 2.1 Kriteria telaah resep (helper `telaah_resep()`, verbatim)

1. Kejelasan Penulisan Resep
2. Tepat Cara Pemberian
3. Tepat Dosis
4. Tepat Frekuensi Pemberian
5. Tepat Obat
6. Tidak Ada Duplikasi
7. Tidak Ada Interaksi Obat

> Baris ke-8 yang dikomentari di legacy (`// "Sesuai Fornas"`) **tidak diaktifkan** —
> tetap tidak diaktifkan di NusaMedika sampai ada keputusan klinis tertulis.

### 2.2 Kriteria telaah obat (helper `telaah_obat()`, verbatim)

1. Tepat Pasien
2. Tepat Obat
3. Tepat Dosis
4. Tepat Cara Pemberian
5. Tepat Waktu Pemberian

### 2.3 Kode status pemberian obat (dari `daftar_terapi_obat.php` baris 432–440)

| Kode | Label/tooltip legacy | Arti |
|---|---|---|
| `1` (✓) | Setelah Obat Diberikan |_obat diberikan |
| `T` | Pasien Menolak | pasien menolak |
| `K` | Kondisi Pasien Menyebabkan di Tundanya Pemberian Suatu Obat | ditunda karena kondisi |
| `A` | Reaksi Alergi | alergi |
| `ESO` | Reaksi Efek Samping Obat Setelah Pemberian | efek samping |
| `TAP` | Obat Tidak Tersedia | tidak tersedia |
| `stop` | Obat Sudah di Stop | sudah dihentikan dokter |

---

## 3. Form 45 — Daftar Terapi Obat

### 3.1 Rasional / tujuan klinis

Daftar obat yang sedang berjalan pada pasien. Perawat **mencatat status
pemberian** per obat per hari; dokter (bukan perawat) boleh melakukan **Stop
Obat** dan **Ubah Dosis × Frekuensi**. Legacy menegakkan ini lewat
`$_SESSION["profesi_id"] != profesi_id_dokter` (input jam & tombol stop
disembunyikan untuk non-dokter).

NusaMedika membuat ini **dua mode dalam satu form**:

- **Mode perawat** — mengisi `status_N` (kode §2.3) per baris obat.
- **Mode dokter** — mengisi `s_1_N`/`s_2_N` (ubah dosis × frekuensi) dan dapat
  memberi `status_N = stop`.

Akses berbeda dikunci di controller lewat `AksesEhr` **dan** pemeriksaan
profesi pengguna (`AksesEhr::profesiId()`).

### 3.2 Struktur Field

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `tanggal_terapi` | 397 | Tanggal Daftar Terapi Obat | date | ya | tanggal pencatatan, tanpa default |
| `waktu_terapi` | 398 | Waktu Daftar Terapi Obat | time | ya | tanpa default |
| `obat_1` … `obat_20` | 69 | Obat | select2 (barang) | ≥ 1 baris | **hanya `barang_id`** — lihat §3.3 |
| `jumlah_1` … `jumlah_20` | 70 | Jumlah | number | opsional | jumlah per pemberian |
| `s_1_1` … `s_1_20` | 71 | S 1 | text | opsional | dosis/frekuensi bagian 1 |
| `s_2_1` … `s_2_20` | 72 | S 2 | text | opsional | dosis/frekuensi bagian 2 |
| `aturan_pakai_1` … `_20` | 73 | Aturan Pakai | text | opsional | "1 tablet setelah makan" |
| `rute_1` … `rute_20` | 74 | Rute Pemberian | select | opsional | |
| `status_1` … `status_20` | 399 | Status Pemberian Obat | select | ya per baris | kode §2.3 (`1`/`T`/`K`/`A`/`ESO`/`TAP`/`stop`) |
| `alasan_stop_1` … `_20` | 400 | Alasan Stop / Tunda Obat | text | opsional | **wajib** bila `status_N` ∈ {`stop`,`K`,`A`,`TAP`} |
| `jam_masuk_1` … `_20` | 401 | Jam Pemberian Obat | time | opsional | jam pemberian konkret |
| `dokter_instruksi_id` | 82 | Petugas Pelaksana | select (pegawai) | ya bila non-dokter | "Atas Instruksi Dokter" (legacy `user_id_dokter_instruksi`) |
| `keterangan` | 77 | Keterangan | textarea | opsional | reuse |

### 3.3 Mengapa hanya id obat, bukan snapshot nama

`TindakanMedisController` (form 10) **sengaja tidak menyimpan nama tindakan/barang**
di `emr_detail` — hanya `*_id`, dan nama di-resolve dari master saat tampilan.
Alasannya: bila master di-rename atau di-soft-delete, riwayat EMR tetap utuh dan
konsisten dengan master.

Form 45 mengikuti keputusan yang sama:

- `obat_N` = `barang_id` saja.
- Nama, satuan, dan jenis barang di-resolve dari `Barang::aktif()` saat render.
- **Tidak ada** variabel `nama_obat_N` — begitu pula tidak ada objek snapshot
  (menghemat 20 baris mapping dan 1 objek).

Konsekuensinya: bila `barang` di-soft-delete setelah disimpan, baris itu tidak
lagi menampilkan nama. `filteredData()` **menolak** `barang_id` yang sudah
soft-delete sehingga kasus ini tidak muncul untuk data baru.

### 3.4 Batas 20 baris & implikasi `objek_form_control`

| Prefiks | Variabel | Objek | Jumlah baris mapping |
|---|---|---|---|
| `obat_` | `obat_1`..`obat_20` | 69 | 20 |
| `jumlah_` | `jumlah_1`..`jumlah_20` | 70 | 20 |
| `s_1_` | `s_1_1`..`s_1_20` | 71 | 20 |
| `s_2_` | `s_2_1`..`s_2_20` | 72 | 20 |
| `aturan_pakai_` | `aturan_pakai_1`..`aturan_pakai_20` | 73 | 20 |
| `rute_` | `rute_1`..`rute_20` | 74 | 20 |
| `status_` | `status_1`..`status_20` | 399 | 20 |
| `alasan_stop_` | `alasan_stop_1`..`alasan_stop_20` | 400 | 20 |
| `jam_masuk_` | `jam_masuk_1`..`jam_masuk_20` | 401 | 20 |
| **Total baris bersuffix** | | | **180** |
| Header | `tanggal_terapi`, `waktu_terapi`, `dokter_instruksi_id`, `keterangan` | 397, 398, 82, 77 | 4 |
| **Total mapping form 45** | | | **184 baris `objek_form_control`** |

Ini digenerate di loop, **jangan ditulis manual** (preseden:
`$mappingBarisObat` di `EmrMasterSeeder` form 10):

```php
// database/seeders/EmrMasterSeeder.php
$mappingBarisTerapi = [];
for ($b = 1; $b <= 20; $b++) {
    $mappingBarisTerapi['obat_'.$b]          = 69;
    $mappingBarisTerapi['jumlah_'.$b]        = 70;
    $mappingBarisTerapi['s_1_'.$b]           = 71;
    $mappingBarisTerapi['s_2_'.$b]           = 72;
    $mappingBarisTerapi['aturan_pakai_'.$b]  = 73;
    $mappingBarisTerapi['rute_'.$b]          = 74;
    $mappingBarisTerapi['status_'.$b]= 399;
    $mappingBarisTerapi['alasan_stop_'.$b] = 400;
    $mappingBarisTerapi['jam_masuk_'.$b] = 401;
}
$mapping[45] = array_merge($mapping[45], $mappingBarisTerapi);
```

### 3.5 UX baris berulang — konsekuensi nyata

| Aspek | Dampak |
|---|---|
| Batas keras | Maksimal **20 obat** per kunjungan. Melebihi → tolak server dengan pesan jelas, bukan diam-diam memotong. Legacy tidak punya batas (berbasis array). |
| Render | 180 input dirender sekaligus = berat. **Wajib render lazily**: hanya N baris pertama (default 5) sampai tombol "Tambah Baris", memakai atribut `data-item` untuk indeks. |
| Indeks baris | Indeks diambil dari `tr.dataset.item`, **bukan** urutan array — menghapus baris di tengah tidak boleh menggeser pasangan `obat_N`/`jumlah_N` (bug yang sama pernah terjadi di Order Laboratorium). |
| Select2 | Selalu `select2('destroy')` sebelum init ulang; **jangan** `disabled` untuk menonaktifkan baris (Select2 mengunci status disabled saat init). |
| Baris kosong | Ditebak dari `obat_N` kosong → `filteredData()` **membuang** seluruh grup (`obat_N`..`jam_masuk_N`) supaya tidak ada baris yatim di `emr_detail`. |
| Tampilan riwayat | `x-emr-history-table` hanya menampilkan kolom non-bersuffix; isi baris obat ditampilkan lewat `<details>`/`Str::limit`. |

### 3.6 Dashboard & Form Master

```php
['dashboard_menu_sub_id' => 173, 'dashboard_menu_id' => 11, 'nama_sub_menu' => 'Daftar Terapi Obat'],
['form_id' => 45, 'nama_form' => 'Daftar Terapi Obat', 'slug' => 'daftar_terapi_obat', 'id_dash_menu' => '11.173', 'ri' => 1, 'rj' => 1, 'igd' => 1, 'mcu' => 0],
```

### 3.7 Mapping

```php
// $mapping[45]
45 => [
    'tanggal_terapi' => 397,
    'waktu_terapi' => 398,
    'dokter_instruksi_id'  => 82,   // reuse
    'keterangan'           => 77,   // reuse
    // + $mappingBarisTerapi (180 baris, §3.4)
],
```

### 3.8 Akses EHR

```php
// Dokter (1): full CRUD
['profesi_id' => 1, 'form_id' => 45, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
// Perawat (2): create/read/update, TIDAK delete (rekam terapi tidak boleh dihapus perawat)
['profesi_id' => 2, 'form_id' => 45, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 0],
// Apoteker (profesi_id 4 — sudah ada, §9.4): read-only
['profesi_id' => 4, 'form_id' => 45, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
```

> Karena `akses_ehr` tidak membedakan "dokter boleh Stop", **.Stop Obat & ubah
> dosis dikunci di dalam controller**, bukan hanya lewat checkbox akses:
> `abort_unless(AksesEhr::profesiId() === 1, 403)` pada aksi stop.

### 3.9 Validasi

| Field | Aturan |
|---|---|
| `tanggal_terapi` | `required\|date` |
| `waktu_terapi` | `required\|date_format:H:i` |
| `obat_N` | `nullable\|integer\|exists:barang,barang_id` · minimal satu baris terisi |
| `jumlah_N` | `nullable\|numeric\|min:0\|max:10000` |
| `s_1_N`, `s_2_N` | `nullable\|string\|max:10` (legacy `maxlength=2` untuk angka) |
| `aturan_pakai_N` | `nullable\|string\|max:255` |
| `rute_N` | `nullable\|string\|max:50` |
| `status_N` | `nullable\|in:1,T,K,A,ESO,TAP,stop` |
| `alasan_stop_N` | `nullable\|string\|max:255` · **wajib** bila `status_N` ∈ {stop, K, A, TAP} |
| `jam_masuk_N` | `nullable\|date_format:H:i` |
| `dokter_instruksi_id` | `nullable\|integer\|exists:pegawai,pegawai_id` |
| `keterangan` | `nullable\|string\|max:1000` |

Server WAJIB menolak: `obat_N` terisi tapi `jumlah_N`/`status_N` kosong pada
baris yang sama; `barang` di-soft-delete; `status = stop` tanpa alasan.

### 3.10 Cetak

`/emr/daftar_terapi_obat/print/{emr_id}` — kolom: No · Nama Obat · Dosis ·
Frekuensi · Aturan · Rute · Status · Alasan · Jam · Petugas.

---

## 4. Form 46 — Rekonsiliasi Obat

### 4.1 Rasional / tujuan klinis

Rekonsiliasi obat adalah kewajiban Joint Commission International dan menjadi
indikator mutu. Legacy memodelkannya sebagai **3 tab**:

| Tab legacy | Keterangan | Kapan |
|---|---|---|
| **Admisi IGD** | Riwayat Obat yang digunakan hingga saat ini (*Medication Taken Recently*) | Pasien masuk IGD |
| **Ruangan Perawatan** | Obat yang diresepkan saat ini (*Current Medication*) | Pasien masuk ruang |
| **Pulang** | Obat saat pulang (*Discharge Medication*) | Saat pulang |

Kolom tiap tab berbeda: tab IGD tidak punya `Satuan`/`Dokter`/`Tanggal Resep`/
`Keterangan`; tab Pulang menambah blok **Edukasi Penggunaan Obat Saat Pulang**.

Tab **Ruangan Perawatan** di-*auto-fill* dari resep terakhir pasien yang sudah
di-dispense saat entry (`rek_resep_terakhir()`). NusaMedika mereplikasi lewat
`RekonsiliasiHelper::resepTerakhir($registrasiDetailId)`.

### 4.2 Struktur Field

Tiga grup baris: `riwayat_`, `resep_`, `pulang_`. Jumlah baris per grup: **10**.

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `rek_tab_aktif` | 402 | Instrumen Rekonsiliasi | hidden/tab | ya | `igd` / `ri` / `pulang` |
| `riwayat_nama_obat_N` (N=1..10) | 69 | Obat | select2+text | ya | id barang **dan** nama (dipisah `id::nama`) |
| `riwayat_rute_N` | 74 | Rute Pemberian | select | ya | |
| `riwayat_dosis_N` | 73 | Aturan Pakai | text | ya | |
| `riwayat_freq_1_N` | 71 | S 1 | text | ya | |
| `riwayat_freq_2_N` | 72 | S 2 | text | tidak | |
| `riwayat_lanjut_N` | 403 | Lanjut pada Perawatan | radio | ya | `Yes` / `No` |
| `resep_nama_obat_N` | 69 | Obat | select2+text | ya | |
| `resep_rute_N` | 74 | Rute Pemberian | select | ya | |
| `resep_dosis_N` | 73 | Aturan Pakai | text | ya | |
| `resep_satuan_N` | 404 | Satuan Aturan Pakai | select | ya | |
| `resep_freq_1_N` | 71 | S 1 | text | ya | |
| `resep_freq_2_N` | 72 | S 2 | text | tidak | |
| `resep_dokter_N` | 405 | Dokter Preskripsi | select (pegawai) | ya | |
| `resep_tgl_mulai_N` | 406 | Tanggal Resep / Mulai | date | ya | |
| `resep_keterangan_N` | 77 | Keterangan | text | tidak | reuse |
| `pulang_*` | (sama `resep_*`) | | | | 1:1 dengan `resep_*` |
| `pulang_edukasi_aturan_minum` | 407 | Edukasi Obat Saat Pulang | checkbox | tidak | 4 opsi berbagi objek |
| `pulang_edukasi_dosis` | 407 | Edukasi Obat Saat Pulang | checkbox | tidak | |
| `pulang_edukasi_cara_penggunaan` | 407 | Edukasi Obat Saat Pulang | checkbox | tidak | |
| `pulang_edukasi_penyimpanan` | 407 | Edukasi Obat Saat Pulang | checkbox | tidak | |
| `catatan_rekonsiliasi` | 408 | Catatan Rekonsiliasi | textarea | tidak | mis. daftar obat yang dihentikan |

### 4.3 Jumlah baris `objek_form_control`

| Grup | Prefiks | Variabel | Jumlah baris | Objek |
|---|---|---|---|---|
| Riwayat | `riwayat_*` | 7 × 10 | **70** | 69, 71, 72, 73, 74, 403 |
| Resep | `resep_*` | 9 × 10 | **90** | 69, 71, 72, 73, 74, 77, 404, 405, 406 |
| Pulang | `pulang_*` | 9 × 10 | **90** | idem |
| Header + edukasi | — | 6 | **6** | 402, 407 (×4), 408 |
| **Total form 46** | | | **256 baris** | |

```php
// database/seeders/EmrMasterSeeder.php
$mappingBarisRekonsiliasi = [];
for ($b = 1; $b <= 10; $b++) {
    // Tab 1 — Riwayat Obat (Admisi IGD): tidak ada satuan/dokter/tgl/keterangan
    $mappingBarisRekonsiliasi['riwayat_nama_obat_'.$b] = 69;
    $mappingBarisRekonsiliasi['riwayat_rute_'.$b]      = 74;
    $mappingBarisRekonsiliasi['riwayat_dosis_'.$b]     = 73;
    $mappingBarisRekonsiliasi['riwayat_freq_1_'.$b]    = 71;
    $mappingBarisRekonsiliasi['riwayat_freq_2_'.$b]    = 72;
    $mappingBarisRekonsiliasi['riwayat_lanjut_'.$b] = 403;

    // Tab 2 — Obat Diresepkan Saat Ini (Ruang Perawatan)
    $mappingBarisRekonsiliasi['resep_nama_obat_'.$b]   = 69;
    $mappingBarisRekonsiliasi['resep_rute_'.$b]        = 74;
    $mappingBarisRekonsiliasi['resep_dosis_'.$b]       = 73;
    $mappingBarisRekonsiliasi['resep_satuan_'.$b] = 404;
    $mappingBarisRekonsiliasi['resep_freq_1_'.$b]      = 71;
    $mappingBarisRekonsiliasi['resep_freq_2_'.$b]      = 72;
    $mappingBarisRekonsiliasi['resep_dokter_'.$b] = 405;
    $mappingBarisRekonsiliasi['resep_tgl_mulai_'.$b] = 406;
    $mappingBarisRekonsiliasi['resep_keterangan_'.$b]  = 77;

    // Tab 3 — Obat Saat Pulang
    $mappingBarisRekonsiliasi['pulang_nama_obat_'.$b]   = 69;
    $mappingBarisRekonsiliasi['pulang_rute_'.$b]        = 74;
    $mappingBarisRekonsiliasi['pulang_dosis_'.$b]       = 73;
    $mappingBarisRekonsiliasi['pulang_satuan_'.$b] = 404;
    $mappingBarisRekonsiliasi['pulang_freq_1_'.$b]      = 71;
    $mappingBarisRekonsiliasi['pulang_freq_2_'.$b]      = 72;
    $mappingBarisRekonsiliasi['pulang_dokter_'.$b] = 405;
    $mappingBarisRekonsiliasi['pulang_tgl_mulai_'.$b] = 406;
    $mappingBarisRekonsiliasi['pulang_keterangan_'.$b]  = 77;
}
$mapping[46] = array_merge($mapping[46], $mappingBarisRekonsiliasi);
```

> **25 variabel × 10 baris = 250 baris mapping** hanya untuk form 46. Ini
> konsekuensi langsung dari tidak adanya tipe array di NusaMedika (panduan §2.2
> & §4.3). **Alternatif yang dipertimbangkan dan ditolak:** menambah
> tabel `rekonsiliasi_obat_detail` — ditolak agar konsisten dengan pola EMR
> seragam dan tidak menambah 4 migration baru. Direkomendasikan sebagai
> **iterasi 2** bila baris truly dinamis dibutuhkan.

### 4.4 Format `nama_obat_N`

Legacy menyimpan **nama** (bukan id) karena `rekonsiliasi_obat.php` bekerja pada
`barang` + `obat_racikan` (racikan tidak punya `barang_id`). NusaMedika:

- `<input type="hidden" name="resep_nama_obat_1" value="{barang_id}">` berisi **id**,
- `<select class="select2-ajax" data-target="resep_nama_obat_1">` berisi nama.
- Bila baris berisi **racikan** (tidak ada id barang), simpan
  `"R:{teks racikan}"` pada field yang sama dan resolve di view.

`filteredData()` memvalidasi: bila nilainya angka, wajib `exists:barang`;
bila diawali `R:`, teks racikan wajib non-kosong.

### 4.5 Dashboard & Form Master

```php
['dashboard_menu_sub_id' => 174, 'dashboard_menu_id' => 11, 'nama_sub_menu' => 'Rekonsiliasi Obat'],
['form_id' => 46, 'nama_form' => 'Rekonsiliasi Obat', 'slug' => 'rekonsiliasi_obat', 'id_dash_menu' => '11.174', 'ri' => 1, 'rj' => 1, 'igd' => 1, 'mcu' => 0],
```

### 4.6 Mapping

```php
// $mapping[46]
46 => [
    'rek_tab_aktif' => 402,
    'catatan_rekonsiliasi' => 408,
    'pulang_edukasi_aturan_minum' => 407,
    'pulang_edukasi_dosis' => 407,
    'pulang_edukasi_cara_penggunaan' => 407,
    'pulang_edukasi_penyimpanan' => 407,
    // + $mappingBarisRekonsiliasi (250 baris, §4.3)
],
```

### 4.7 Akses EHR

```php
['profesi_id' => 1, 'form_id' => 46, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 2, 'form_id' => 46, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 0],
['profesi_id' => 4, 'form_id' => 46, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 0],
```

### 4.8 Validasi

| Field | Aturan |
|---|---|
| `rek_tab_aktif` | `required\|in:igd,ri,pulang` |
| `*_nama_obat_N` | `required\|string\|max:255` |
| `*_rute_N` | `required\|string\|max:50` |
| `*_dosis_N` | `required\|string\|max:100` |
| `*_freq_1_N` | `required\|string\|max:10` |
| `*_freq_2_N` | `nullable\|string\|max:10` |
| `*_satuan_N` | `required\|string\|max:30` (tab resep/pulang saja) |
| `*_dokter_N` | `required\|integer\|exists:pegawai,pegawai_id` (tab resep/pulang) |
| `*_tgl_mulai_N` | `required\|date` (tab resep/pulang) |
| `riwayat_lanjut_N` | `required\|in:Yes,No` (tab riwayat) |
| `pulang_edukasi_*` | `nullable\|boolean` |
| `catatan_rekonsiliasi` | `nullable\|string\|max:2000` |

**Validasi silang (server, wajib):**

1. Baris dianggap ada bila `*_nama_obat_N` terisi. Baris kosong dibuang di
   `filteredData()` (seluruh 7/9 variabel grup).
2. `*_freq_1_N` & `*_freq_2_N` bila keduanya terisi harus berbentuk
   `"{angka} x {angka}"` bila digabung — simpan terpisah, **jangan** gabung.
3. Deteksi duplikat obat pada grup yang sama → **warning**, bukan error, karena
   satu resep sah bisa memuat obat sama dengan kekuatan berbeda; pembedaan
   dilakukan pada `nama_obat` (case-insensitive) + `dosis` + `rute`.

### 4.9 Cetak

`/emr/rekonsiliasi_obat/print/{emr_id}` — **hanya tab yang diaktifkan saat
pencarian** (`rek_tab_aktif`), seperti legacy `VIEW MODE` (baris 189–190).
Tabel `Obat yang Diresepkan Saat Ini` + blok `Edukasi Penggunaan Obat Saat Pulang`
dengan tanda ✓/✗.

---

## 5. Form 47 — Telaah Resep

### 5.1 Rasional / tujuan klinis

Telaah resep dilakukan **farmasis sebelum obat diserahkan** ke pasien/boras.
Tujuannya: memastikan resep memenuhi 7 aspek (§2.1) dan mencatat temuan bila
tidak. Legacy menampilkannya sebagai checkbox di halaman dispense dan
menyimpan-nya ke `emr` form `form_id_telaah_resep`, **terikat pada satu
`peresepan_obat_id`** (query `emr_detail.value = $_GET['resep_id']`).

NusaMedika mempertahankan ikatan ini: field `nomor_resep_id` (§5.2) adalah FK
logis ke tabel resep farmasi.

### 5.2 Struktur Field

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `nomor_resep_id` | 409 | Nomor Resep (ID Resep Farmasi) | select/number | ya | mengikat telaah ke satu resep |
| `tanggal_telaah` | 418 | Tanggal Telaah | date | ya | |
| `waktu_telaah` | 419 | Waktu Telaah | time | ya | |
| `petugas_id` | 82 | Petugas Pelaksana | select (pegawai) | ya | farmasis pemeriksa (reuse) |
| `telaah_1` | 410 | Telaah 1 - Kejelasan Penulisan Resep | checkbox | ya | |
| `telaah_2` | 411 | Telaah 2 - Tepat Cara Pemberian | checkbox | ya | |
| `telaah_3` | 412 | Telaah 3 - Tepat Dosis | checkbox | ya | |
| `telaah_4` | 413 | Telaah 4 - Tepat Frekuensi Pemberian | checkbox | ya | |
| `telaah_5` | 414 | Telaah 5 - Tepat Obat | checkbox | ya | |
| `telaah_6` | 415 | Telaah 6 - Tidak Ada Duplikasi | checkbox | ya | |
| `telaah_7` | 416 | Telaah 7 - Tidak Ada Interaksi Obat | checkbox | ya | |
| `telaah_catatan` | 417 | Catatan / Keterangan Telaah | textarea | **wajib bila ada butir tidak tercentang** | |
| `prioritas` | 76 | Prioritas | select | tidak | reuse; `BIASA` / `CITO` |
| `keterangan` | 77 | Keterangan | textarea | tidak | reuse |

### 5.3 Server-side computation

Tidak ada skor numerik — telaah bersifat **ya/tidak per butir**. Yang dihitung
server di `filteredData()`:

```php
// app/Helpers/FarmasiHelper.php (BARU)
public const KRITERIA_TELAAH_RESEP = [
    1 => 'Kejelasan Penulisan Resep',
    2 => 'Tepat Cara Pemberian',
    3 => 'Tepat Dosis',
    4 => 'Tepat Frekuensi Pemberian',
    5 => 'Tepat Obat',
    6 => 'Tidak Ada Duplikasi',
    7 => 'Tidak Ada Interaksi Obat',
];

public static function telaah(array $jawaban): array
// ['tercentang' => int, 'total' => 7, 'lengkap' => bool, 'gagal' => string[]] — daftar butir tidak tercentang
```

- Nilai browser untuk `telaah_catatan` **tetap dipakai** (bukan turunan), tetapi
  **wajib di-isi server bila `lengkap = false`** — jangan hanya Validasi client.
- Checkbox memakai pola **hidden `off` + checkbox `on`** dari legacy, agar nilai
  selalu tersimpan (`'off'` bila tidak dicentang) — lihat §7.3.

### 5.4 Dashboard & Form Master

```php
['dashboard_menu_sub_id' => 175, 'dashboard_menu_id' => 11, 'nama_sub_menu' => 'Telaah Resep'],
['form_id' => 47, 'nama_form' => 'Telaah Resep', 'slug' => 'telaah_resep', 'id_dash_menu' => '11.175', 'ri' => 0, 'rj' => 1, 'igd' => 1, 'mcu' => 0],
```

### 5.5 Mapping

```php
// $mapping[47]
47 => [
    'nomor_resep_id' => 409,
    'tanggal_telaah' => 418,
    'waktu_telaah' => 419,
    'petugas_id'      => 82,   // reuse
    'telaah_1' => 410,
    'telaah_2' => 411,
    'telaah_3' => 412,
    'telaah_4' => 413,
    'telaah_5' => 414,
    'telaah_6' => 415,
    'telaah_7' => 416,
    'telaah_catatan' => 417,
    'prioritas'       => 76,   // reuse
    'keterangan'      => 77,   // reuse
],
```

### 5.6 Akses EHR

```php
// Dokter (1): read — ia yang membaca hasil telaah
['profesi_id' => 1, 'form_id' => 47, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
// Perawat (2): read
['profesi_id' => 2, 'form_id' => 47, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
// Apoteker (4): full CRUD — ujung tombómnya
['profesi_id' => 4, 'form_id' => 47, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
```

### 5.7 Validasi

| Field | Aturan |
|---|---|
| `nomor_resep_id` | `required\|integer\|exists:peresepan_obat,peresepan_obat_id` |
| `tanggal_telaah` / `waktu_telaah` | `required\|date` / `required\|date_format:H:i` |
| `petugas_id` | `required\|integer\|exists:pegawai,pegawai_id` |
| `telaah_1..7` | `nullable\|boolean` (`'on'` / `'off'`) |
| `telaah_catatan` | `nullable\|string\|max:2000` · **server**: wajib bila ada butir `'off'` |
| `prioritas` | `nullable\|in:BIASA,CITO` |

### 5.8 Cetak

`/emr/telaah_resep/print/{emr_id}` —Format surat telaah resep (kop RS, nomor,
identitas pasien, daftar obat dari `peresepan_obat_detail`, hasil 7 butir,
nama & tanda tangan farmasis).

---

## 6. Form 48 — Telaah Obat

### 6.1 Rasional / tujuan klinis

Telaah obat dilakukan saat **proses dispense** — berbeda dari telaah resep:
yang diperiksa adalah kesesuaian obat yangigan dengan pasien, dosis, dan waktu
pemberian. Kriterinya 5 (§2.2).

### 6.2 Struktur Field

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `nomor_resep_id` | 409 | Nomor Resep (ID Resep Farmasi) | select/number | ya | reuse objek form 47 |
| `tanggal_telaah` | 418 | Tanggal Telaah | date | ya | reuse |
| `waktu_telaah` | 419 | Waktu Telaah | time | ya | reuse |
| `petugas_id` | 82 | Petugas Pelaksana | select (pegawai) | ya | reuse |
| `tobat_1` | 420 | Telaah Obat 1 - Tepat Pasien | checkbox | ya | |
| `tobat_2` | 421 | Telaah Obat 2 - Tepat Obat | checkbox | ya | |
| `tobat_3` | 422 | Telaah Obat 3 - Tepat Dosis | checkbox | ya | |
| `tobat_4` | 423 | Telaah Obat 4 - Tepat Cara Pemberian | checkbox | ya | |
| `tobat_5` | 424 | Telaah Obat 5 - Tepat Waktu Pemberian | checkbox | ya | |
| `tobat_catatan` | 425 | Catatan / Keterangan Telaah Obat | textarea | **wajib bila ada butir tidak tercentang** | |
| `keterangan` | 77 | Keterangan | textarea | tidak | reuse |

> Objek 418/419/409/82 **dipakai ulang** dari form 47 — maknanya identik
> (tanggal/waktu telaah, nomor resep, farmasis pemeriksa).

### 6.3 Server-side computation

```php
public const KRITERIA_TELAAH_OBAT = [
    1 => 'Tepat Pasien',
    2 => 'Tepat Obat',
    3 => 'Tepat Dosis',
    4 => 'Tepat Cara Pemberian',
    5 => 'Tepat Waktu Pemberian',
];

public static function telaahObat(array $jawaban): array
```

### 6.4 Dashboard & Form Master

```php
['dashboard_menu_sub_id' => 176, 'dashboard_menu_id' => 11, 'nama_sub_menu' => 'Telaah Obat'],
['form_id' => 48, 'nama_form' => 'Telaah Obat', 'slug' => 'telaah_obat', 'id_dash_menu' => '11.176', 'ri' => 1, 'rj' => 0, 'igd' => 0, 'mcu' => 0],
```

### 6.5 Mapping

```php
// $mapping[48]
48 => [
    'nomor_resep_id' => 409,  // reuse
    'tanggal_telaah' => 418,  // reuse
    'waktu_telaah' => 419,  // reuse
    'petugas_id'     => 82,   // reuse
    'tobat_1' => 420,
    'tobat_2' => 421,
    'tobat_3' => 422,
    'tobat_4' => 423,
    'tobat_5' => 424,
    'tobat_catatan' => 425,
    'keterangan'     => 77,   // reuse
],
```

### 6.6 Akses EHR

```php
// Dokter (1): read — membaca hasil telaah obat
['profesi_id' => 1, 'form_id' => 48, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
// Perawat (2): read
['profesi_id' => 2, 'form_id' => 48, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
// Apoteker (4): full CRUD
['profesi_id' => 4, 'form_id' => 48, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
```

### 6.7 Validasi

| Field | Aturan |
|---|---|
| `nomor_resep_id` | `required\|integer\|exists:peresepan_obat,peresepan_obat_id` |
| `tanggal_telaah` / `waktu_telaah` | `required\|date` / `required\|date_format:H:i` |
| `petugas_id` | `required\|integer\|exists:pegawai,pegawai_id` |
| `tobat_1..5` | `nullable\|boolean` |
| `tobat_catatan` | `nullable\|string\|max:2000` · **server**: wajib bila ada butir `'off'` |

### 6.8 Cetak

`/emr/telaah_obat/print/{emr_id}` —_newsletter dispensation telaah: identitas
resep, rincian obat, 5 butir, tanda tangan farmasis.

---

## 7. Pola Baris Bersuffix — Penjelasan Lengkap

### 7.1 Masalah

`EmrHelper::emrDetailByVariabel()` melakukan:

```php
return DB::table('emr_detail')->where('emr_id', $emrId)->pluck('value', 'variabel')->toArray();
```

`pluck('value', 'variabel')` menghasilkan **satu nilai per variabel** — baris
ketiga dan seterusnya untuk variabel yang sama **tertimpa diam-diam**. Legacy
menghindari ini karena tidak lewat `emr_detail` (`peresepan_dispense` +
`terapi_obat_dosis`), jadi tidak ada masalah. NusaMedika **harus** menyelesaikannya.

### 7.2 Solusi: prefiks + indeks numerik

```
obat_1, obat_2, ... obat_20          → objek 69
jumlah_1, jumlah_2, ... jumlah_20    → objek 70
```

Semua key unik ⇒ `pluck` tidak menimpa apa pun ⇒ seluruh baris terbaca utuh saat
form dibuka lagi, dan `array_intersect_key()` di `filteredData()` tetap
menyisakannya.

### 7.3 Aturan tambahan

| Aturan | Alasan |
|---|---|
| **Indeks baris diambil dari `tr.dataset.item`, bukan urutan array.** | Menghapus baris di tengah tidak boleh menggeser pasangan `obat_N`/`jumlah_N`. Bug identik pernah terjadi di Order Laboratorium & Tindakan Medis. |
| **Baris kosong dibuang** di `filteredData()` — bukan hanya tidak dikirim. | Kalau tidak, ada baris yatim `jumlah_7` tanpa `obat_7` yang akan muncul lagi saat form dibuka. |
| **Checkbox tak-mutual dalam satu variabel** (form 46 `pulang_edukasi_*`; form 47/48 `telaah_*` / `tobat_*`) memakai pola hidden+checkbox legacy: `<input type="hidden" name="x" value="off">` + `<input type="checkbox" name="x" value="on">`. | Menjamin nilai selalu tersimpan; `$emr_data['x'] == 'on'` selalu terdefinisi. |
| **Checkbox banyak item dalam satu variabel** **tidak boleh** memakai `name="x[]"`. | Akan menjadi N baris dengan `variabel` sama dan tertimpa oleh baris terakhir. Simpan sebagai hidden tunggal berisi daftar id, dipisah koma. |
| **Rendering bertahap** (form 46: 256 baris mapping ≈ 250 input). | Render 5 baris pertama; tombol "Tambah Baris" menambah baris berikutnya. Semua baris tetap ter-*submit* (hidden/disabled by index), bukan `display:none` tanpa `name`. |

### 7.4 Dampak ukuran

| Form | Baris mapping | Note |
|---|---|---|
| 45 Daftar Terapi Obat | 184 | 9 prefiks × 20 |
| 46 Rekonsiliasi Obat | 256 | 3 grup × (7–9 variabel) × 10 |
| 47 Telaah Resep | 14 | tetap |
| 48 Telaah Obat | 11 | tetap |
| **Total** | **465** | sementara objek master baru hanya 35 |

Total objek master setelah file ini: 177 → **206** (397–425). Sudah masuk
anggaran ± 500 yang disebut `ANALISIS_KEKURANGAN_FORM.md` §7, tapi **filter
objek per menu di `Administrator → Manajemen EMR → Form` became increasingly
wajib** — tanpa filter, halaman Form akan sulit dinavigasi.

---

## 8. SelectOption — key baru

```php
// app/Helpers/SelectOption.php
'rute_pemberian' => [
    ['value' => 'PO',  'label' => 'PO (Per Oral)'],
    ['value' => 'IV',  'label' => 'IV (Intravena)'],
    ['value' => 'IM',  'label' => 'IM (Intramuskular)'],
    ['value' => 'SC',  'label' => 'SC (Subkutan)'],
    ['value' => 'ID',  'label' => 'ID (Intradermal)'],
    ['value' => 'PR',  'label' => 'PR (Per Rektal)'],
    ['value' => 'VG',  'label' => 'VG (Vagina)'],
    ['value' => 'TOP', 'label' => 'TOP (Topikal)'],
    ['value' => 'INH', 'label' => 'INH (Inhalasi)'],
    ['value' => 'NGT', 'label' => 'NGT (Nasogastric Tube)'],
    ['value' => 'SV',  'label' => 'SV (Subcutaneous/Vaskular)'],
],
'satuan_aturan_pakai' => [
    ['value' => 'tablet',  'label' => 'Tablet'],
    ['value' => 'kapsul',  'label' => 'Kapsul'],
    ['value' => 'sirup',   'label' => 'Sirup'],
    ['value' => 'drops',   'label' => 'Tetes'],
    ['value' => 'ampul',   'label' => 'Ampul'],
    ['value' => 'vial',    'label' => 'Vial'],
    ['value' => 'sachet',  'label' => 'Sachet'],
    ['value' => 'suppositoria', 'label' => 'Suppositoria'],
    ['value' => 'mg',      'label' => 'mg'],
    ['value' => 'ml',      'label' => 'ml'],
    ['value' => 'IU',      'label' => 'IU'],
    ['value' => 'UI',      'label' => 'UI'],
    ['value' => 'butir',   'label' => 'Butir'],
],
'status_pemberian_obat' => [
    ['value' => '1',    'label' => '✓ Setelah Obat Diberikan'],
    ['value' => 'T',    'label' => 'T — Pasien Menolak'],
    ['value' => 'K',    'label' => 'K — Ditunda karena Kondisi Pasien'],
    ['value' => 'A',    'label' => 'A — Reaksi Alergi'],
    ['value' => 'ESO',  'label' => 'ESO — Efek Samping Obat'],
    ['value' => 'TAP',  'label' => 'TAP — Obat Tidak Tersedia'],
    ['value' => 'stop', 'label' => '■ Obat Sudah di Stop'],
],
'lanjut_perawatan' => [
    ['value' => 'Yes', 'label' => 'Ya (Obat Dilanjutkan)'],
    ['value' => 'No',  'label' => 'Tidak (Dihentikan)'],
],
'telaah_resep' => [
    ['value' => 'Kejelasan Penulisan Resep',   'label' => 'Kejelasan Penulisan Resep'],
    ['value' => 'Tepat Cara Pemberian',        'label' => 'Tepat Cara Pemberian'],
    ['value' => 'Tepat Dosis',                 'label' => 'Tepat Dosis'],
    ['value' => 'Tepat Frekuensi Pemberian',   'label' => 'Tepat Frekuensi Pemberian'],
    ['value' => 'Tepat Obat',                  'label' => 'Tepat Obat'],
    ['value' => 'Tidak Ada Duplikasi',         'label' => 'Tidak Ada Duplikasi'],
    ['value' => 'Tidak Ada Interaksi Obat',    'label' => 'Tidak Ada Interaksi Obat'],
],
'telaah_obat' => [
    ['value' => 'Tepat Pasien',           'label' => 'Tepat Pasien'],
    ['value' => 'Tepat Obat',             'label' => 'Tepat Obat'],
    ['value' => 'Tepat Dosis',            'label' => 'Tepat Dosis'],
    ['value' => 'Tepat Cara Pemberian',   'label' => 'Tepat Cara Pemberian'],
    ['value' => 'Tepat Waktu Pemberian',  'label' => 'Tepat Waktu Pemberian'],
],
'edukasi_obat_pulang' => [
    ['value' => 'Aturan Minum',     'label' => 'Aturan Minum'],
    ['value' => 'Dosis',            'label' => 'Dosis'],
    ['value' => 'Cara Penggunaan',  'label' => 'Cara Penggunaan'],
    ['value' => 'Penyimpanan',      'label' => 'Penyimpanan'],
],
```

---

## 9. Implementasi

### 9.1 File yang harus dibuat

| Jenis | Path |
|---|---|
| Helper (BARU) | `app/Helpers/FarmasiHelper.php` — `KRITERIA_TELAAH_*`, `telaah()`, `telaahObat()`, `resepTerakhir()`, `barangMap()` |
| Controller | `app/Http/Controllers/EMR/DaftarTerapiObat/DaftarTerapiObatController.php` |
| Controller | `app/Http/Controllers/EMR/RekonsiliasiObat/RekonsiliasiObatController.php` |
| Controller | `app/Http/Controllers/EMR/TelaahResep/TelaahResepController.php` |
| Controller | `app/Http/Controllers/EMR/TelaahObat/TelaahObatController.php` |
| View | `resources/views/moduls/EMR/{DaftarTerapiObat,RekonsiliasiObat,TelaahResep,TelaahObat}/index.blade.php` |
| Partial baris | `…/DaftarTerapiObat/partials/barisObat.blade.php` |
| Partial baris | `…/RekonsiliasiObat/partials/barisRiwayat.blade.php`, `barisResep.blade.php` |
| View cetak | `…/{DaftarTerapiObat,RekonsiliasiObat,TelaahResep,TelaahObat}/print.blade.php` |
| Edit | `database/seeders/EmrMasterSeeder.php` |
| Edit | `app/Helpers/SelectOption.php` |
| Edit | `routes/web.php` — 4 route `emr.{slug}.print` |
| Edit | `AGENTS.md` |

### 9.2 Checklist seeder

- [ ] `$menus` += `['dashboard_menu_id' => 11, 'nama_menu' => 'Farmasi']`
- [ ] `$subMenus` += PK 173–176 (`dashboard_menu_id = 11`, tanpa extra)
- [ ] `$forms` += form 45–48
- [ ] `$objeks` += 397–425
- [ ] `$mapping[45]` + `$mappingBarisTerapi` (180 baris)
- [ ] `$mapping[46]` + `$mappingBarisRekonsiliasi` (250 baris)
- [ ] `$mapping[47]`, `$mapping[48]`
- [ ] `EmrHelper::backfillObjekId(45..48)`
- [ ] `$akses` += 12 baris
- [ ] Cek duplikat `variabel` per form — generate via `collect($keys)->duplicates()` di lokal
- [ ] Semua `slug == Str::slug($nama_sub_menu, '_')`:
      `daftar_terapi_obat`, `rekonsiliasi_obat`, `telaah_resep`, `telaah_obat` ✓

### 9.3 Checklist controller & view

- [ ] Gate `AksesEhr::can()` di `index`/`store`/`update`/`destroy`
- [ ] `filteredData()` → `array_intersect_key()` **setelah** membuang baris kosong
- [ ] `Barang::aktif()` — barang soft-deleted tidak boleh dipilih/diterima
- [ ] `filteredData()` membuang `obat_N` yang tidak ada di `Barang::aktif()`
- [ ] `@error` di setiap field
- [ ] `old('x', $emr_data['x'] ?? '')` — bukan `$emr_data['x']`
- [ ] `{{ $isView ? 'disabled' : '' }}` di `<fieldset>`
- [ ] Select2 baris dinamis: `select2('destroy')` sebelum init; **jangan** `disabled`
- [ ] `tr.dataset.item` sebagai sumber indeks
- [ ] Tanpa tag komponen blade di dalam `<script>` (termasuk komentar)
- [ ] `@json($opsi)` dihitung di blok `@php` terpisah untuk array kompleks
- [ ] `docker compose exec app ./vendor/bin/pint --dirty`

### 9.4 Profesi — tidak ada profesi baru

**Apoteker = `profesi_id = 4`** dan **sudah ada** di `MasterPegawaiSeeder`
(`ALOKASI_ID_GLOBAL.md` §5). Jadi file ini **tidak menambah profesi baru
sama sekali** dan `$akses` tidak perlu menunggu seed profesi.

- **Jangan** mendefinisikan ulang `profesi_id = 4` — itu menimpa Apoteker.
- **Jangan** memakai `profesi_id = 14` sebagai "Apoteker" — **14 = Kerohanian**,
  milik `KONSEP_PSIKOSIAL_KEROHANIAN.md`.

Satu-satunya tambahan: `MasterPegawaiSeeder` diberi contoh pegawai apoteker aktif
(`profesi_id = 4`) agar form 45–48 bisa diisi di praktik.

### 9.5 Integrasi ke alur Order Resep (form 5)

Telaah Resep & Telaah Obat **dipanggil dari** halaman dispense modul Farmasi
(`Farmasi\Resep\ListPesananResep`), **bukan** dari dashboard pasien — tapi tetap
menulis ke `emr` form 47/48 agar terbaca dokter di dashboard. Form 47/48 tetap
*readable* di dashboard (akses read untuk Dokter/Perawat).

Route integrasi (non-CRUD, manual di `routes/web.php`):
`POST /farmasi/peresepan/{id}/telaah-resep` dan `…/telaah-obat` → redirect ke
form EMR 47/48 dengan `emr_id` yang baru dibuat, atau pre-fill POST.

---

## 10. Catatan & Risiko

| # | Risiko / Catatan | Mitigasi |
|---|---|---|
| 1 | **465 baris `objek_form_control` dari 4 form.** Seeder akan_insert 465 baris; `array_merge` dengan `$mapping[10]`/`$mapping[14]` yang sudah ada → total objek_form_control > 700 baris. | Generate via loop (bukan array literal). Pertimbangkan `insert()` batch per 500 baris bila koneksi timeout. |
| 2 | **Checkbox multi-item dalam satu variabel** (`pulang_edukasi_*` × 4 pada objek 407) — jangan pakai `name="x[]"`, akan menjadi 4 baris `variabel` sama → tertimpa. | Hidden tunggal berisi daftar id, dipisah koma; view `explode(',', ...)`. |
| 3 | **256 baris mapping form 46** = ±250 input. Render semuanya akan lambat di iframe EMR. | Render bertahap (5 baris awal + tombol tambah), `data-item` sebagai indeks, jangan `display:none` tanpa `name`. |
| 4 | **Limit 20 baris obat (form 45) dan 10 baris per grup (form 46)** adalah keputusan desain, bukanitmembatasan klinis. Pasien dengan > 20 obat (polyfarmasi) akan tertolak. | Naikkan batas bila perlu ( Independence jumlah mapping tumbuh linear). Jangan diam-diam memotong — tolak dengan pesan eksplisit. |
| 5 | **Telaah Resep/Obat terikat `peresepan_obat_id`** — tabel itu milik modul Farmasi yang belum terdefinisi di NusaMedika saat dokumen ini ditulis. | `nomor_resep_id` divalidasi `exists:peresepan_obat,...` **hanya setelah** tabel Farmasi tersedia; sampai itu, `nullable` + catatan. |
| 6 | ~~**Profesi 14 (Apoteker) belum ada.**~~ — SALAH. **Apoteker = 4 dan sudah ada**; `profesi_id = 14` = Kerohanian. | Pakai `profesi_id = 4`. Jangan seed `14` sebagai "Apoteker" — akan menimpa Kerohanian. |
| 7 | **`AKHIR_` `field` Snapshot**: `*_nama_obat_N` untuk racikan disimpan sebagai teks; `Barang::find()` akan null. View wajib fallback ke teks. | Format `"{id}"` atau `"R:{teks}"`; deteksi di view & helper. |
| 8 | **Akses `Stop Obat` tidak bisa diekspresikan di `akses_ehr`** (yang tersedia hanya create/read/update/delete). | Kunci di controller: `abort_unless(AksesEhr::profesiId() === 1, 403)` untuk aksi stop + ubah dosis. |
| 9 | `Str::studly('daftar_terapi_obat')` = `DaftarTerapiObat` ✓, `Str::studly('telaah_resep')` = `TelaahResep` ✓ — tidak ada jebakan akronim di file ini (berbeda dengan SBAR/ADIME). | Tidak ada tindakan. |
| 10 | Total objek menjadi 206, mendekati batas usability yang disebut `ANALISIS_KEKURANGAN_FORM.md` §7. | Prioritaskan filter/pencarian objek per menu di halaman `Administrator → ManajemenEMR → Form`. |