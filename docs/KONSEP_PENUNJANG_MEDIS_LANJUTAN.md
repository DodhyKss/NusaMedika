# KONSEP ORDER PENUNJANG MEDIS LANJUTAN (Patologi Anatomi, Mikrobiologi, POCT)

**Status:** Rancangan / Konsep — belum diimplementasikan
**Tanggal:** 2026-10-10
**Target:** form_id 49–51 · objek_id 432–446 · `dashboard_menu` 4 (Order) sub 193–195
**Acuan:** [`KONSEP_PENUNJANG_MEDIS.md`](KONSEP_PENUNJANG_MEDIS.md),
[`PANDUAN_IMPLEMENTASI_FORM_EMR.md`](PANDUAN_IMPLEMENTASI_FORM_EMR.md),
[`AGENTS.md`](../AGENTS.md)
**Sumber legacy:** `/simrs_tenriawaru/FE/lib/modul/`

---

## 1. Ringkasan

NusaMedika sudah punya **Order Laboratorium** (form 6) dan **Order Radiologi**
(form 7), keduanya di menu **Order** (`dashboard_menu` 4). Yang belum ada adalah
tiga order penunjang yang secara klinis berdiri sendiri di legacy Tenriawaru:
**Patologi Anatomi**, **Mikrobiologi**, dan **POCT**.

| form_id | nama_form | slug | id_dash_menu | ri | rj | igd | mcu |
|---|---|---|---|---|---|---|---|
| 49 | Order Patologi | `order_patologi` | `4.193` | 1 | 1 | 1 | 0 |
| 50 | Order Mikrobiologi | `order_mikrobiologi` | `4.194` | 1 | 1 | 1 | 0 |
| 51 | Order POCT | `order_poct` | `4.195` | 1 | 1 | 1 | 0 |

### 1.1 Posisi di menu Order (sudah ada)

```
Order (dashboard_menu_id 4)                          [SUDAH ADA]
├── sub 4  = "Order Resep"    → form 5  id_dash_menu "4.4"    [existing]
├── sub 5  = "Laboratorium"   → form 6  id_dash_menu "4.5"    [existing]
├── sub 6  = "Radiologi"      → form 7  id_dash_menu "4.6"    [existing]
├── sub 193 = "Order Patologi"    → form 49 id_dash_menu "4.193" [BARU]
├── sub 194 = "Order Mikrobiologi"→ form 50 id_dash_menu "4.194" [BARU]
└── sub 195 = "Order POCT"        → form 51 id_dash_menu "4.195" [BARU]
```

> **`dashboard_menu_sub_id` bersifat GLOBAL.** Band
> `KONSEP_PENUNJANG_MEDIS_LANJUTAN.md` adalah **193–212**
> (`ALOKASI_ID_GLOBAL.md` §3); file ini memakai **193–195**. Sub 1–12 sudah
> terpakai. Angka "7, 8, 9" pada rencana awal adalah **urutan posisi** di bawah
> menu `Order` (setelah 4, 5, 6 yang sudah ada), bukan PK.
>
> **`nama_sub_menu` WAJIB = `Order Patologi` / `Order Mikrobiologi` /
> `Order POCT`** karena `EmrDashboardController` memakai
> `Str::slug($nama_sub_menu, '_')` sebagai `form_name` di URL. Kalau dinamai
> `Patologi Anatomi`, slug-nya `patologi_anatomi` dan form 49 (slug
> `order_patologi`) akan **yatim dari dashboard**.

Tanpa `dashboard_menu_sub_extra` → `id_dash_menu` = `"4.193"` dst.

### 1.2 Alokasi objek

| Form | Objek baru | Rentang |
|---|---|---|
| 49 Order Patologi | 432–433 | 2 |
| 50 Order Mikrobiologi | 434–437 | 4 |
| 51 Order POCT | 438–442 | 5 |
| **Total** | | **432–442 (11 objek)** |

> **Id band 432–446 yang tidak terpakai:** 443–446 (4 id dicadangkan, tidak
> dideklarasikan di `$objeks`). Band objek tidak berubah dan `id_dash_menu` tetap
> `4.193`–`4.195`. Objek pernah berlubang (433–434, 437–438) dan kini
> direnumber menjadi 432–442 tanpa lompatan ke band dokumen lain.

Objek **yang di-reuse** (mutlak — jangan buat objek baru untuk hal yang sudah ada):

| objek_id | nama_objek | Dipakai di |
|---|---|---|
| 40 | Diagnosa Medis | 49 `diagnosa_patologi`, 50 `diagnosa`, 51 `diagnosa_poct` |
| 75 | Tindakan | 49/50/51 — item baris diturunkan dari Master Tindakan via `group_tindakan` |
| 76 | Prioritas | 49/50/51 `prioritas` (`BIASA` / `CITO`) |
| 77 | Keterangan | 49 `catatan`, 50 `catatan`, 51 `catatan` |
| 70 | Jumlah | jumlah item pada baris detail order |
| 78 | Hasil | entri hasil petugas |
| 79 | Nilai Normal | rujukan hasil |
| 80 | Satuan Hasil | satuan hasil |
| 81 | Flag Abnormal | 0 normal / 1 abnormal |
| 82 | Petugas Pelaksana | petugas penerima/entri hasil |

---

## 2. Sumber Referensi Legacy

| Yang diambil | File legacy |
|---|---|
| Form order Patologi Anatomi (tanggal, diagnosa/indikasi, catatan, jenis permintaan Biasa/Cito, pilih group tindakan) | `simrs_tenriawaru/FE/lib/modul/patologi_anatomi.php` |
| Form order Mikrobiologi (tanggal lab, jenis permintaan, diagnosa, catatan, unit pemeriksaan, jenis pemeriksaan Dalam/Luar, pilih tindakan via modal) | `simrs_tenriawaru/FE/lib/modul/mikrobiologi.php` |
| Form order & entri hasil POCT (hasil `POSITIVE`/`NEGATIVE`/`NIL`, tanggal/jam/hasil/unit/rujukan/flag, tambah item tindakan + jumlah, jenis permintaan Biasa/Cito, `ket_catatan`) | `simrs_tenriawaru/FE/lib/modul/poct.php` |
| Antrian & Petroleum mikrobiologi | `simrs_tenriawaru/FE/lib/modul/antrian_laboratorium_mikrobiologi.php` |
| Antrian Patologi Anatomi | `simrs_tenriawaru/FE/lib/modul/antrian_laboratorium_patologi_anatomi.php` |
| Referensi grup tindakan per unit | `simrs_tenriawaru/FE/lib/modul/ajax/ajax.tindakan.mikrobiologi.php` |

### 2.1 Perbedaan penting terhadap Order Laboratorium

| Aspek | Order Laboratorium (existing) | Order Patologi / Mikrobiologi / POCT |
|---|---|---|
| Sumber item | Semua `Tindakan::aktif()` difilter `data-bagian` via `PenunjangHelper::tindakanBagianMap()` | **Ditambah** filter per *group* tindakan (legacy `tindakan_group`): patologi & mikrobiologi dikelompokkan per group (mis. *Kultur*, *Histopatologi*), POCT adalah daftar tindakan yang tetap (fixed list) |
| Kolom per baris | `satuan_hasil`, `nilai_normal` diisi **manual per baris** | Mikrobiologi menambah `jenis_pemeriksaan` (Dalam/Luar) & spesimen; POCT menambah tanggal/jam hasil per pengukuran |
| Hasil | `hasil` (text) + `flag_abnormal` | POCT: `hasil` berupa **POSITIVE/NEGATIVE/NIL** per pengukuran + `flag_abnormal` |
| Billing | `PenunjangHelper::buatOrder()` membuat `registrasi_detail` tujuan | **Sama persis** — wajib, agar konsisten dengan `KONSEP_PENUNJANG_MEDIS.md` §7.1 |

---

## 3. Form 49 — Order Patologi Anatomi

### 3.1 Rasional / tujuan klinis

Patologi Anatomi (histopatologi) diminta untuk pemeriksaan noninvasif jaringan —
biopsi, sitologi, dan blok kecil jaringan. Order memerlukan **indikasi/diagnosa
klinis** yang jelas karena patolog secara rutin menolak spesimen tanpa indikasi,
dan memerlukan prioritas (CITO untuk *frozen section* intra-operatif).

### 3.2 Struktur Field (header order)

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `tanggal_pemeriksaan` | 432 | Tanggal Pemeriksaan Patologi Anatomi | date | ya | legacy `tgl_periksa_anatomi` (default hari ini) |
| `diagnosa_patologi` | 40 | Diagnosa Medis | textarea | ya | legacy `anatomiDiagnosa`; label UI **"Indikasi Pemeriksaan"** |
| `catatan` | 77 | Keterangan | textarea | tidak | legacy `ketCatatan`; reuse |
| `jenis_pesanan` | 433 | Jenis Pesanan Patologi Anatomi | select | ya | **group tindakan** (legacy `jenisPesananAnatomi`) |
| `prioritas` | 76 | Prioritas | radio | ya | `BIASA` / `CITO` (legacy `AnatomiPermintaan` 0/1) |
| `bagian_tujuan_id` | — | — | select | ya | unit Patologi Anatomi; **tidak disimpan** di `emr_detail` — disimpan di tabel `order_patologi.bagian_tujuan_id` |

**Item baris** — disimpan di tabel `order_patologi_detail`, **bukan** `emr_detail`:

| Kolom tabel | Sumber | Keterangan |
|---|---|---|
| `tindakan_id` | `Tindakan::aktif()` dalam group terpilih | reuse objek 75 |
| `nama_tindakan` | snapshot dari master | reuse objek 75 |
| `harga` | `PenunjangHelper::tarif()` per `kelas_ruang` | |
| `jumlah` | 1 | reuse objek 70 |
| `spesimen` | select: `Biopsi`, `Sitologi`, `Kspecimen`, `Blocks` | kolom baru |
| `hasil` | diisi petugas | reuse objek 78 |
| `flag_abnormal` | 0/1 | reuse objek 81 |

### 3.3 Dashboard & Form Master

```php
// $subMenus — PK 193–195, dashboard_menu_id = 4, TANPA extra
['dashboard_menu_sub_id' => 193, 'dashboard_menu_id' => 4, 'nama_sub_menu' => 'Order Patologi'],
['dashboard_menu_sub_id' => 194, 'dashboard_menu_id' => 4, 'nama_sub_menu' => 'Order Mikrobiologi'],
['dashboard_menu_sub_id' => 195, 'dashboard_menu_id' => 4, 'nama_sub_menu' => 'Order POCT'],

// $forms
['form_id' => 49, 'nama_form' => 'Order Patologi', 'slug' => 'order_patologi', 'id_dash_menu' => '4.193', 'ri' => 1, 'rj' => 1, 'igd' => 1, 'mcu' => 0],
['form_id' => 50, 'nama_form' => 'Order Mikrobiologi',  'slug' => 'order_mikrobiologi', 'id_dash_menu' => '4.194', 'ri' => 1, 'rj' => 1, 'igd' => 1, 'mcu' => 0],
['form_id' => 51, 'nama_form' => 'Order POCT',          'slug' => 'order_poct',          'id_dash_menu' => '4.195', 'ri' => 1, 'rj' => 1, 'igd' => 1, 'mcu' => 0],
```

### 3.4 Mapping

```php
// $mapping[49]
49 => [
    'tanggal_pemeriksaan' => 432,
    'diagnosa_patologi'   => 40,   // reuse Diagnosa Medis
    'catatan'             => 77,   // reuse Keterangan
    'jenis_pesanan' => 433,
    'prioritas'           => 76,   // reuse Prioritas
],
```

> `bagian_tujuan_id` **sengaja tidak ada di mapping** — nilainya disimpan di tabel
> `order_patologi` (bukan `emr_detail`). Sama seperti form 6/7 yang juga tidak
> memetakan header order ke `emr_detail`.

### 3.5 Akses EHR

Pola **identik dengan form 6 (Laboratorium)** — `KONSEP_PENUNJANG_MEDIS.md` §8.4:

```php
// Order Patologi (form 49): Dokter create/read/update/delete; Perawat read; Patolog (profesi baru) read
['profesi_id' => 1,  'form_id' => 49, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 2,  'form_id' => 49, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
['profesi_id' => 15, 'form_id' => 49, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
```

> `profesi_id = 15` **"Patolog Anatomi"** (baru, §7.3 — sesuai `ALOKASI_ID_GLOBAL.md` §5, ID 15 memang dialokasikan ke file ini). Perawat read-only
> konsisten dengan form 6 — tidak ada revisi di sini, order tetap dokter.

### 3.6 Validasi

| Field | Aturan |
|---|---|
| `tanggal_pemeriksaan` | `required\|date` |
| `diagnosa_patologi` | `required\|string\|max:2000` |
| `catatan` | `nullable\|string\|max:1000` |
| `jenis_pesanan` | `required\|integer\|exists:group_tindakan,group_tindakan_id` |
| `prioritas` | `required\|in:BIASA,CITO` |
| `bagian_tujuan_id` | `required\|integer\|exists:bagian,bagian_id` · bagian harus `referensi_bagian_id = 6` |
| `tindakan_id[]` | minimal satu baris; tiap `tindakan_id` wajib ada di `group_tindakan_tindakan` untuk `jenis_pesanan` terpilih |

Server WAJIB menolak tindakan yang group-nya tidak cocok dengan bagian tujuan
(pola `LaboratoriumController::validatedItems()`).

### 3.7 Cetak

`print.blade.php` **Permintaan Pemeriksaan Patologi Anatomi** — kop RS, nomor
order, identitas pasien, indikasi, group pemeriksaan, daftar tindakan, prioritas
CITO disorot, dan kolom TTD dokter peminta. Route `/emr/order_patologi/print/{emr_id}`
di mana `emr_id` = `order_patologi_id`.

---

## 4. Form 50 — Order Mikrobiologi

### 4.1 Rasional / tujuan klinis

Mikrobiologi (kultur & sensitifitas) memerlukan data yang lebih spesifik dari
laboratorium biasa: **jenis pemeriksaan** (Dalam/Luar), **spesimen sumber**, dan
group tindakan. Legacy menanyakan "Tindakan Dalam / Tindakan Luar" untuk
menentukan apakah bahan dikirim ke laboratorium luar (bi-culture ke rujukan).

### 4.2 Struktur Field (header order)

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `tanggal_pemeriksaan` | 434 | Tanggal Pemeriksaan Mikrobiologi | date | ya | legacy `tglPesanLab` |
| `diagnosa` | 40 | Diagnosa Medis | textarea | ya | reuse objek 40 |
| `catatan` | 77 | Keterangan | textarea | tidak | legacy `ket_catatan`; reuse |
| `unit_pemeriksaan` | 436 | Unit Mikrobiologi | select | ya | legacy `pemeriksaan` (dropdown `bagian` mikrobiologi) |
| `jenis_pemeriksaan` | 435 | Jenis Pemeriksaan Mikrobiologi | radio | ya | legacy `jenisPemeriksaan`: `Tindakan Dalam` / `Tindakan Luar` |
| `spesimen` | 437 | Spesimen Sumber Mikrobiologi | select | ya | **penambahan** — mikrobiologi wajib menyebut sumber spesimen |
| `jumlah_spesimen` | 70 | Jumlah | number | ya | legacy tidak ada, tapi wajib untuk kultur (1/2/3 tabung) |
| `prioritas` | 76 | Prioritas | radio | ya | reuse |

### 4.3 Opsi `spesimen` (diturunkan dari praktik kultur)

`Blood`, `Urine`, `Sputum`, `Throat Swab`, `Wound / Abscess`, `Vaginal Swab`,
`Rectal Swab`, `Stool`, `Sputum Tracheal`, `Catheter Urine`, `Pleural Fluid`,
`Ascites Fluid`, `Cerebrospinal Fluid`, `Tissue Biopsy`, `Lainnya`.

> Daftar ini **harus dikonfirmasi mikrobiolog klinis** sebelum di-hardcode ke
> `SelectOption`; sementara ini dibuat statis dengan catatan perlu review.

### 4.4 Dashboard & Form Master

```php
['dashboard_menu_sub_id' => 194, 'dashboard_menu_id' => 4, 'nama_sub_menu' => 'Order Mikrobiologi'],
['form_id' => 50, 'nama_form' => 'Order Mikrobiologi', 'slug' => 'order_mikrobiologi', 'id_dash_menu' => '4.194', 'ri' => 1, 'rj' => 1, 'igd' => 1, 'mcu' => 0],
```

### 4.5 Mapping

```php
// $mapping[50]
50 => [
    'tanggal_pemeriksaan' => 434,
    'diagnosa'             => 40,   // reuse
    'catatan'              => 77,   // reuse
    'unit_pemeriksaan' => 436,
    'jenis_pemeriksaan' => 435,
    'spesimen' => 437,
    'jumlah_spesimen'      => 70,   // reuse Jumlah
    'prioritas'            => 76,   // reuse
],
```

### 4.6 Akses EHR

```php
['profesi_id' => 1,  'form_id' => 50, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 2,  'form_id' => 50, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
['profesi_id' => 13, 'form_id' => 50, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
```

> `profesi_id = 13` (Analis Laboratorium) — **sudah ada**, dipakai apa adanya
> seperti pada form 6.

### 4.7 Validasi

| Field | Aturan |
|---|---|
| `tanggal_pemeriksaan` | `required\|date` |
| `diagnosa` | `required\|string\|max:2000` |
| `unit_pemeriksaan` | `required\|integer\|exists:bagian,bagian_id` |
| `jenis_pemeriksaan` | `required\|in:Dalam,Luar` |
| `spesimen` | `required\|string\|max:50` |
| `jumlah_spesimen` | `required\|integer\|between:1,10` |
| `prioritas` | `required\|in:BIASA,CITO` |
| `catatan` | `nullable\|string\|max:1000` |

**Validasi khusus `jenis_pemeriksaan = Luar`:** wajib `catatan` berisi nama
laboratorium tujuan rujukan (minimal 5 karakter). Tanpa itu klaim tidak bisa
diaudit.

### 4.8 Cetak

`Permintaan Pemeriksaan Mikrobiologi` — memuat kolom spesimen, jumlah,
dalam/luar, dan nomor laboratorium rujukan bila `Luar`. Route
`/emr/order_mikrobiologi/print/{emr_id}`.

---

## 5. Form 51 — Order POCT

### 5.1 Rasional / tujuan klinis

POCT = *Point-of-Care Testing*. Berbeda dengan order laboratorium, POCT:

- **Dapat dilakukan langsung di unit perawatan** (perawat/dokter), tidak melalui
  laboratorium sentral.
- Satu tindakan dapat memiliki **beberapa hasil** (mis. glukometri 6× sehari).
  Legacy menyimpan hasil sebagai `nama_tindakan` ber-`SPLIT_PART` dengan
  pemisah `<br>` — pola yang **tidak bisa dipertahankan** (lihat §5.4).
- Hasil berupa **`POSITIVE` / `NEGATIVE` / `NIL`** untuk parameter uji cepat
  (HBsAg, HIV, SARS-CoV-2), atau angka untuk parameter kuantitatif (glukosa).

### 5.2 Struktur Field (header order)

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `tanggal_order` | 438 | Tanggal Order POCT | date | ya | |
| `diagnosa_poct` | 40 | Diagnosa Medis | textarea | ya | legacy `diagnosis`; reuse objek 40 |
| `indikasi` | 439 | Indikasi / Alasan POCT | textarea | tidak | |
| `catatan` | 77 | Keterangan | textarea | tidak | legacy `ket_catatan`; reuse |
| `prioritas` | 76 | Prioritas | radio | ya | reuse |

### 5.3 Struktur Field (per hasil pemeriksaan)

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `hasil_1` … `hasil_20` | 442 | Hasil POCT | select | ya per baris | `POSITIVE` / `NEGATIVE` / `NIL` (untuk uji cepat) |
| `nilai_1` … `nilai_20` | 442 | Hasil POCT | number | ya bila kuantitatif | mis. glukosa mg/dL |
| `tanggal_hasil_1` … `_20` | 440 | Tanggal Hasil POCT | date | ya | |
| `waktu_hasil_1` … `_20` | 441 | Waktu Hasil POCT | time | ya | |

> Objek **442 dipakai oleh `hasil_N` dan `nilai_N`** (dua variabel, satu objek).
> Sah skema — preseden objek 62 di form 3.
> Kolom **satuan** & **nilai rujukan** diambil dari `tindakan_harga`/master
> tindakan, tidak disimpan per baris (menghemat 2 objek).

### 5.4 Penyimpanan hasil — keputusan desain

Legacy menyimpan N hasil dalam **satu** `order_lab_detail.nama_tindakan` dengan
pemisah `<br>`, lalu dibaca `SPLIT_PART(..., 4)`. NusaMedika **tidak** mengulang
pola ini karena:

1. `SPLIT_PART` **tidak ada di MySQL** (hanya PostgreSQL) — melanggar aturan
   dual-driver di `AGENTS.md`.
2. Satu kolom menyimpan data terstruktur = tidak bisa difilter/ditotalkan.

**Keputusan:** hasil POCT disimpan di tabel **`order_poct_detail`** dengan
satu baris per **(order, tindakan, waktu pengukuran)**:

```
order_poct_detail
├── order_poct_detail_id      PK
├── order_poct_id             FK
├── tindakan_id               FK master (reuse objek 75)
├── nama_tindakan             varchar(255)   snapshot
├── tanggal_hasil             date
├── waktu_hasil               time
├── hasil                     varchar(50)    -- 'POSITIVE' | 'NEGATIVE' | 'NIL' | angka
├── flag_abnormal             smallint       (reuse objek 81)
├── status                    smallint       0 belum, 1 selesai
├── petugas_id                integer        (reuse objek 82)
└── audit + status_batal
```

Artinya `hasil_1..20`, `nilai_1..20`, `tanggal_hasil_1..20`, `waktu_hasil_1..20`
**tidak dipetakan ke `emr_detail`** — semuanya ditulis langsung ke tabel detail
oleh controller, seperti `satuan_hasil`/`nilai_normal` pada form 6 (yang juga
diisi manual per baris tetapi disimpan di `order_laboratorium_detail`).

Konsekuensi: objek 440–442 pada form 51 **hanya dipakai untuk ringkasan pada
`emr_detail`** (hasil terakhir & tanggal hasil terakhir per tindakan, supaya
ringkasan dashboard EMR bisa dibaca tanpa JOIN). Bila ini deemed berlebihan,
hapus 440–442 dan alokasikan ulang (rentang 432–446 tidak boleh berubah).

### 5.5 Dashboard & Form Master

```php
['dashboard_menu_sub_id' => 195, 'dashboard_menu_id' => 4, 'nama_sub_menu' => 'Order POCT'],
['form_id' => 51, 'nama_form' => 'Order POCT', 'slug' => 'order_poct', 'id_dash_menu' => '4.195', 'ri' => 1, 'rj' => 1, 'igd' => 1, 'mcu' => 0],
```

### 5.6 Mapping

```php
// $mapping[51]
51 => [
    'tanggal_order' => 438,
    'diagnosa_poct' => 40,   // reuse
    'indikasi'      => 439,
    'catatan'       => 77,   // reuse
    'prioritas'     => 76,   // reuse
    'tanggal_hasil_terakhir' => 440,
    'waktu_hasil_terakhir' => 441,
    'hasil_terakhir' => 442,
],
```

### 5.7 Akses EHR

```php
// POCT boleh diisi PERAWAT (hasil di tempat), jadi create diizinkan untuk Perawat
['profesi_id' => 1, 'form_id' => 51, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 2, 'form_id' => 51, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 0],
['profesi_id' => 13, 'form_id' => 51, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
```

> **Perbedaan dari form 6/7:** Perawat mendapat `akses_create` untuk POCT,
> karena POINT of care test memang dilakukan perawat. Delete tetap 0.

### 5.8 Validasi

| Field | Aturan |
|---|---|
| `tanggal_order` | `required\|date` |
| `diagnosa_poct` | `required\|string\|max:2000` |
| `indikasi` | `nullable\|string\|max:1000` |
| `prioritas` | `required\|in:BIASA,CITO` |
| `tanggal_hasil_terakhir` | `required\|date` (sinkron dari detail) |
| `waktu_hasil_terakhir` | `required\|date_format:H:i` |
| `hasil_terakhir` | `required\|string\|max:50` |

Per-item (di luar `emr_detail`):

| Field | Aturan |
|---|---|
| `tindakan_id[]` | minimal 1, `exists:tindakan,tindakan_id`, aktif |
| `hasil` | `required\|string\|max:50` |
| `tanggal_hasil` | `required\|date\|before_or_equal:today` |
| `waktu_hasil` | `required\|date_format:H:i` |

Server WAJIB menolak **duplikat** `(tindakan_id, tanggal_hasil, waktu_hasil)`
dalam satu order, dan menolak tindakan yang bukan POCT (yaitu tidak ada di
`group_tindakan` bernama `POCT`).

### 5.9 Cetak

`/emr/order_poct/print/{emr_id}` — lembar hasil POCT per tanggal (grafik
terkendali: `glukosa` = line chart). Route non-CRUD:
`GET /emr/order_poct/grafik/{emr_id}?tanggal=`.

---

## 6. Skema Database (BARU)

### 6.1 Prinsip

> **WAJIB mirror `order_laboratorium` + `order_laboratorium_detail` dan
> `order_radiologi` + `order_radiologi_detail`.** Semua kolom, konvensi PK,
> dan indeks disalin; yang berubah hanya nama tabel dan 1–2 kolom domain.

### 6.2 Tabel yang dibuat

| Tabel | PK |Fk/ kolom domain tambahan |
|---|---|---|
| `order_patologi` | `order_patologi_id` | `spesimen` dipindah ke detail |
| `order_patologi_detail` | `order_patologi_detail_id` | `spesimen` (string 50), `jumlah` (int, default 1) |
| `order_mikrobiologi` | `order_mikrobiologi_id` | `jenis_pemeriksaan` (`Dalam`/`Luar`), `spesimen`, `jumlah_spesimen`, `lab_rujukan` |
| `order_mikrobiologi_detail` | `order_mikrobiologi_detail_id` | `jumlah` (int, default 1) |
| `order_poct` | `order_poct_id` | — (header sama labourasi) |
| `order_poct_detail` | `order_poct_detail_id` | `tanggal_hasil` (date), `waktu_hasil` (time), `hasil` (string 50) |

### 6.3 Contoh migration — `order_patologi`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_patologi', function (Blueprint $table) {
            $table->increments('order_patologi_id');
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();

            $table->string('no_order', 30);                    // PAT-20260919-0001
            $table->integer('pasien_id');
            $table->integer('registrasi_id');
            $table->integer('registrasi_detail_id');           // detail ASAL
            $table->integer('registrasi_detail_tujuan_id');    // detail BARU di bagian patologi
            $table->integer('bagian_asal_id');
            $table->integer('bagian_tujuan_id');
            $table->integer('dokter_id')->nullable();
            $table->string('prioritas', 15)->nullable();       // BIASA / CITO
            $table->smallInteger('status_order')->default(0);  // 0 Menunggu, 1 Diproses, 2 Selesai, 3 Batal
            $table->timestamp('tanggal_order')->nullable();
            $table->timestamp('tanggal_terima')->nullable();
            $table->timestamp('tanggal_hasil')->nullable();
            $table->timestamp('tanggal_pemeriksaan')->nullable(); // field legacy tgl_periksa_anatomi
            $table->integer('petugas_pelaksana_id')->nullable();
            $table->smallInteger('kelas_id')->nullable();
            $table->integer('hak_kelas_id')->nullable();
            $table->text('keterangan')->nullable();

            $table->index('registrasi_id');
            $table->index('registrasi_detail_id');
            $table->index('registrasi_detail_tujuan_id');
            $table->index('bagian_tujuan_id');
            $table->index('status_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_patologi');
    }
};
```

### 6.4 `order_patologi_detail`

```php
Schema::create('order_patologi_detail', function (Blueprint $table) {
    $table->increments('order_patologi_detail_id');
    $table->timestamp('input_time', 6)->nullable();
    $table->integer('input_user_id')->nullable();
    $table->timestamp('mod_time', 6)->nullable();
    $table->integer('mod_user_id')->nullable();
    $table->smallInteger('status_batal')->nullable();

    $table->integer('order_patologi_id');
    $table->integer('tindakan_id')->nullable();
    $table->string('nama_tindakan', 255);          // snapshot
    $table->decimal('harga', 12, 2)->default(0);   // snapshot tarif per kelas
    $table->integer('jumlah')->default(1);
    $table->string('spesimen', 50)->nullable();    // Biopsi / Sitologi / Blocks / Kspecimen
    $table->string('satuan_hasil', 20)->nullable();
    $table->string('nilai_normal', 100)->nullable();
    $table->text('hasil')->nullable();
    $table->smallInteger('flag_abnormal')->nullable();  // 0 normal, 1 abnormal
    $table->smallInteger('status')->default(0);         // 0 belum hasil, 1 selesai
    $table->integer('petugas_id')->nullable();

    $table->index('order_patologi_id');
    $table->index('tindakan_id');
});
```

### 6.5 `order_mikrobiologi` (+ detail)

Header **sama persis** dengan `order_patologi`, ditambah:

```php
$table->string('jenis_pemeriksaan', 15)->nullable(); // Dalam / Luar
$table->string('spesimen', 50)->nullable();
$table->integer('jumlah_spesimen')->default(1);
$table->string('lab_rujukan', 255)->nullable();       // wajib bila Luar
```

Detail **sama** dengan `order_patologi_detail` tanpa kolom `spesimen`.

### 6.6 `order_poct` (+ detail)

Header **sama** dengan `order_patologi`. Detail **berbeda** (hasil berulang):

```php
Schema::create('order_poct_detail', function (Blueprint $table) {
    $table->increments('order_poct_detail_id');
    $table->timestamp('input_time', 6)->nullable();
    $table->integer('input_user_id')->nullable();
    $table->timestamp('mod_time', 6)->nullable();
    $table->integer('mod_user_id')->nullable();
    $table->smallInteger('status_batal')->nullable();

    $table->integer('order_poct_id');
    $table->integer('tindakan_id')->nullable();
    $table->string('nama_tindakan', 255);
    $table->decimal('harga', 12, 2)->default(0);
    $table->date('tanggal_hasil');
    $table->time('waktu_hasil');
    $table->string('hasil', 50)->nullable();          // POSITIVE / NEGATIVE / NIL / angka
    $table->smallInteger('flag_abnormal')->nullable();
    $table->smallInteger('status')->default(0);
    $table->integer('petugas_id')->nullable();

    $table->index('order_poct_id');
    $table->index('tindakan_id');
    $table->index(['tindakan_id', 'tanggal_hasil']);
});
```

### 6.7 Aturan dual-driver MySQL/PostgreSQL (`AGENTS.md`)

| Aturan | Penerapan |
|---|---|
| PK selalu `$table->increments(...)` (MySQL `AUTO_INCREMENT` / PG `serial`) | sudah dipakai di semua 6 tabel |
| Kolom JSON pakai `$table->json(...)` — **jangan** `jsonb` | tidak ada kolom JSON di tabel ini |
| **Jangan** pakai `ILIKE`; pakai `'like'` (MySQL) | pencarian `no_order`/pasien pakai `like` |
| **Jangan** pakai `SPLIT_PART` (fungsi PostgreSQL) | Inilah alasan pola POCT legacy tidak dipertahankan (§5.4). |
| **Jangan** pakai `ON CONFLICT` (PG) | pakai `updateOrInsert` Laravel |
| Timestamp harus ditulis dari PHP (`now()`), **jangan** `NOW()` | zona waktu app = Asia/Makassar, container db = UTC (selisih 8 jam) |
| Soft delete `status_batal` nullable; filter `whereNull OR = 0` (bukan `whereNull` saja) | Scope `aktif()` di model |

### 6.8 Model

| Model | `$table` | `$primaryKey` | Relasi wajib |
|---|---|---|---|
| `App\Models\OrderPatologi` | `order_patologi` | `order_patologi_id` | `pasien`, `dokter`, `bagianAsal`, `bagianTujuan`, `registrasiDetail`, `registrasiDetailTujuan`, `petugasPelaksana`, `details` |
| `App\Models\OrderPatologiDetail` | `order_patologi_detail` | `order_patologi_detail_id` | `order`, `tindakan` |
| `App\Models\OrderMikrobiologi` | `order_mikrobiologi` | `order_mikrobiologi_id` | idem |
| `App\Models\OrderMikrobiologiDetail` | `order_mikrobiologi_detail` | `order_mikrobiologi_detail_id` | `order`, `tindakan` |
| `App\Models\OrderPoct` | `order_poct` | `order_poct_id` | idem |
| `App\Models\OrderPoctDetail` | `order_poct_detail` | `order_poct_detail_id` | `order`, `tindakan` |

Semua `public $timestamps = false;`, scope `aktif()`, dan mengikuti
`OrderLaboratorium` (lihat `app/Models/OrderLaboratorium.php`).

---

## 7. Implementasi

### 7.1 ⚠️ MIRROR LaboratoriumController, bukan pola EMR biasa

Ketiga form ini **BUKAN** form `emr`/`emr_detail` biasa. `LaboratoriumController`
menyimpan ke tabel `order_laboratorium`, dan `$emr_id` pada URL
`/emr/form/laboratorium/{registrasi_detail_id}/{emr_id}` **bermakna
`order_laboratorium_id`**. `OrderResepController` (form 5) mengikuti pola sama.

Berkas yang harus dibuat — **harus mirror**:

```
app/Http/Controllers/EMR/OrderPatologi/OrderPatologiController.php
app/Http/Controllers/EMR/OrderMikrobiologi/OrderMikrobiologiController.php
app/Http/Controllers/EMR/OrderPoct/OrderPoctController.php
resources/views/moduls/EMR/OrderPatologi/index.blade.php
resources/views/moduls/EMR/OrderMikrobiologi/index.blade.php
resources/views/moduls/EMR/OrderPoct/index.blade.php
```

Method yang harus ada di masing-masing controller (identik dengan
`LaboratoriumController`):

| Method | Tanggung jawab |
|---|---|
| `index($registrasi_detail_id, $emr_id = null, $form_name = null)` | Gate `AksesEhr::can('read')`, kirim `$bagianList`, `$tindakans`, `$tindakanBagianMap`, `$orders`, `$edit`, `$editDetails`, `$isView`. **Redirect paksa ke `?action=view`** bila `status_order !== 0` |
| `store()` | `validatedHeader()` + `validatedItems()` → `PenunjangHelper::buatOrder('patologi', …)` dalam satu transaksi |
| `update()` | validasi sama → `PenunjangHelper::ubahOrder()` (hanya bila `status_order === 0`) |
| `destroy()` | `PenunjangHelper::batalkan()` — **soft cancel, bukan hapus fisik** |
| `validatedHeader()` | `array_merge` default untuk field opsional |
| `validatedItems()` | iterasi `$request->input('tindakan_id', [])`; **indeks baris diambil dari `data-item`** untuk field per-baris (`satuan_hasil`, `nilai_normal`, `spesimen`) |

`PenunjangHelper` harus **diextend**, bukan diduplikasi:

```php
// app/Helpers/PenunjangHelper.php — revisi
private static function normalizeJenis(string $jenis): string
{
    // sekarang menerima: lab, rad, patologi, mikrobiologi, poct
}

private static function tables(string $jenis): array
{
    return match ($jenis) {
        'lab'           => ['order_laboratorium', 'order_laboratorium_id', 'order_laboratorium_detail', 'order_laboratorium_id'],
        'rad'           => ['order_radiologi',   'order_radiologi_id',   'order_radiologi_detail',   'order_radiologi_id'],
        'patologi'      => ['order_patologi',     'order_patologi_id',    'order_patologi_detail',    'order_patologi_id'],
        'mikrobiologi'  => ['order_mikrobiologi', 'order_mikrobiologi_id','order_mikrobiologi_detail','order_mikrobiologi_id'],
        'poct'          => ['order_poct',         'order_poct_id',        'order_poct_detail',        'order_poct_id'],
        default         => throw new \InvalidArgumentException('Jenis penunjang tidak valid.'),
    };
}

public static function generateNoOrder(string $jenis): string
{
    // prefix: LAB / RAD / PAT / MIK / POCT
}
```

> **`$jenis` menentukan prefix nomor order:** `LAB-20260919-0001`,
> `PAT-…`, `MIK-…`, `POCT-…`.

### 7.2 Filter `group_tindakan` — tambahan untuk order form

`LaboratoriumController` memfilter tindakan via `PenunjangHelper::tindakanBagianMap()`
(tindakan → group → bagian). Untuk patologi & mikrobiologi, **group bukan sekadar
pemilik unit tetapi juga alat pemilih**. Tambahkan:

```php
// app/Helpers/PenunjangHelper.php — tambahan
public static function tindakanGroupTervalidasi(int $bagianTujuanId, ?int $groupTujuanId = null): array
// [tindakan_id => bagian_id], difilter juga oleh group_tujuan_id bila diberikan
```

View memakai `data-bagian` (dan `data-group`) pada `<option>` untuk
`filterOptions()` — disables opsi yang tidak cocok, **plus** validasi server
sebagai jaring pengaman (karnyalaga hanya guard klien; browser bisa POST langsung).

### 7.3 Profesi baru

`profesi_id = 15` **"Patolog Anatomi"**. Update seeder profesi +
`MasterPegawaiSeeder` (contoh pegawai aktif dengan `bagian_id` =
INSTALASI PATOLOGI ANATOMI). `BagianSeeder` **append** (bukan sisip di tengah)
`INSTALASI PATOLOGI ANATOMI` dan `INSTALASI MIKROBIOLOGI` dengan
`referensi_bagian_id = 6`.

> `BagianSeeder` menghapus seluruh `bagian` lalu seed ulang ber-id berurutan —
> **append di akhir list** dan lookup selalu **per NAMA bagian**, bukan id
> hardcode (konvensi `AGENTS.md`).

### 7.4 Checklist seeder

- [ ] `$subMenus` += PK 193, 194, 195 (`dashboard_menu_id = 4`)
- [ ] `$forms` += form 49, 50, 51
- [ ] `$objeks` += 432–446
- [ ] `$mapping[49]`, `$mapping[50]`, `$mapping[51]`
- [ ] `EmrHelper::backfillObjekId(49..51)`
- [ ] `$akses` += 9 baris
- [ ] `Str::slug('Order Patologi','_')` = `order_patologi` ✓ · `Order Mikrobiologi` → `order_mikrobiologi` ✓ · `Order POCT` → `order_poct` ✓
- [ ] `Str::studly('order_patologi')` = `OrderPatologi` ✓

### 7.5 Checklist view

- [ ] `$actionUrl` / `$deleteUrl` memakai `route('emr.form.*', ['form_name' => 'order_patologi', …])`
- [ ] Slot `listRiwayat` me-render `$orders` (bukan `x-emr-history-table` — sumbernya tabel order)
- [ ] Tombol Edit & Batal **hanya tampil bila `status_order === 0`**
- [ ] `@json($opsi)` dihitung di blok `@php` terpisah
- [ ] `filterOptions()` disable opsi tidak cocok
- [ ] Indeks baris dari `tr.dataset.item`, bukan urutan array
- [ ] `@error` di setiap field
- [ ] Tanpa komponen blade di dalam `<script>` (termasuk di komentar)

### 7.6 Modul Daftar Pesanan (entri hasil)

Setiap order baru butuh halaman **Daftar Pesanan** di modul `PenunjangMedis`
(mirip `DaftarPesananLaboratoriumController`):

| Sub-menu | `file_sub_menu` | Aksi |
|---|---|---|
| Daftar Pesanan Patologi Anatomi | `PenunjangMedis/PatologiAnatomi/DaftarPesananPatologiAnatomi/daftar_pesanan_patologi_anatomi` | pilih-bagian (session), terima, simpan hasil, selesai, batal, cetak |
| Daftar Pesanan Mikrobiologi | `PenunjangMedis/Mikrobiologi/DaftarPesananMikrobiologi/daftar_pesanan_mikrobiologi` | idem + input **hasil & sensitifitas antimikroba** |
| Daftar Pesanan POCT | `PenunjangMedis/Poct/DaftarPesananPoct/daftar_pesanan_poct` | idem + input hasil per waktu |

Route aksi **non-CRUD** ditulis manual di `routes/web.php` (auto-router hanya
urus CRUD per sub_menu):

```
POST /daftar_pesanan_patologi_anatomi/pilih-bagian
POST /order_patologi/{id}/terima
POST /order_patologi/{id}/simpan-hasil
POST /order_patologi/{id}/selesai
POST /order_patologi/{id}/batal
GET  /order_patologi/{id}/cetak
POST /order_mikrobiologi/{id}/{terima|simpan-hasil|selesai|batal}
GET  /order_mikrobiologi/{id}/cetak
POST /order_poct/{id}/{terima|simpan-hasil|selesai|batal}
GET  /order_poct/{id}/cetak
```

> `{id}` wajib `->whereNumber('id')` agar tidak mencuri path milik auto-route
> (`AGENTS.md`, *Auto-route sub_menu*).

### 7.7 File lain yang harus dibuat / diubah

| Jenis | Path |
|---|---|
| Migration | `2026_10_XX_create_order_patologi_table.php` (+ detail) |
| Migration | `2026_10_XX_create_order_mikrobiologi_table.php` (+ detail) |
| Migration | `2026_10_XX_create_order_poct_table.php` (+ detail) |
| Model | `OrderPatologi`, `OrderPatologiDetail`, `OrderMikrobiologi`, `OrderMikrobiologiDetail`, `OrderPoct`, `OrderPoctDetail` |
| Helper | Revisi `app/Helpers/PenunjangHelper.php` |
| Helper | `app/Helpers/OrderGrupHelper.php` (filter `group_tindakan` + versi/harga) |
| Controller | `…/PenunjangMedis/PatologiAnatomi/DaftarPesananPatologiAnatomi/…` |
| Controller | `…/PenunjangMedis/Mikrobiologi/DaftarPesananMikrobiologi/…` |
| Controller | `…/PenunjangMedis/Poct/DaftarPesananPoct/…` |
| View | `moduls/PenunjangMedis/{PatologiAnatomi,Mikrobiologi,Poct}/…` |
| Seeder | `BagianSeeder` (append 2 bagian), `MasterPegawaiSeeder` (profesi 15), `TindakanSeeder` (contoh tindakan patologi/mikrobiologi/POCT) |
| Seeder | `GroupTindakanSeeder` (append group: HISTOPATOLOGI, SITOLOGI, KULTUR, KULTUR + SENSITIVITAS, POCT) |
| Edit | `database/seeders/DatabaseSeeder.php` — tambah 6 tabel ke `GenerateHelper::resetSequence()` |
| Edit | `app/Helpers/SelectOption.php` — `spesimen_patologi`, `spesimen_mikrobiologi`, `jenis_pemeriksaan_mikrobiologi`, `hasil_poct` |
| Edit | `routes/web.php`, `AGENTS.md` |

### 7.8 Urutan implementasi

1. Migration 6 tabel + model 6 + `resetSequence()`.
2. `BagianSeeder` append 2 bagian + `GroupTindakanSeeder` append 5 group + `TindakanSeeder` contoh.
3. Revisi `PenunjangHelper` (`tables()`, `normalizeJenis()`, `generateNoOrder()`, `tindakanGroupTervalidasi()`).
4. Seeder `EmrMasterSeeder` (menu sub 193–195, form 49–51, objek 432–446, mapping, akses).
5. Controller + view EMR untuk ketiga order.
6. Daftar Pesanan (t terima/simpan hasil/selesai/batal/cetak).
7. Cetak & grafik POCT.
8. Update `AGENTS.md`.

---

## 8. Catatan & Risiko

| # | Risiko / Catatan | Mitigasi |
|---|---|---|
| 1 | **`id_dash_menu` wajib `"4.193"` dst., bukan `"4.7"`.** Kalau sub-menu 193–195 tidak ter-seed, ketiga form **yatim dari dashboard** (URL langsung tetap bisa diakses karena gate, tapi menu tidak muncul). | Verifikasi `SELECT * FROM dashboard_menu_sub WHERE dashboard_menu_id = 4;` sebelum `db:seed`. |
| 2 | **`nama_sub_menu` = `Order Patologi` (bukan `Patologi Anatomi`)** — kalau tidak, `Str::slug` tidak sama dengan `form.slug` → form tampil sebagai "Unsupported". | Sudah dikunci di §1.1 & §3.3. |
| 3 | **Menggandakan `PenunjangHelper` per jenis akan cepat menjadi tidak terkendali.** 5 jenis order = 5 cabang `if` di setiap method. | Ekstensi `tables()` dengan `match` (§7.1). Bila jenis ke-6/7 muncul, pertimbangkan tabel generik `order_penunjang` + `jenis` — **tapi** jangan sampai ada dua pola hidup bersamaan. |
| 4 | **`SPLIT_PART` (PostgreSQL) tidak boleh dipakai.** Legacy POCT bergantung padanya. | Hasil POCT dinormalisasi ke `order_poct_detail` (§5.4, §6.7). |
| 5 | **`GenerateHelper::resetSequence()`** harus dijalankan untuk 6 tabel baru **setelah** seeder mengisi id eksplisit, kalau tidak insert auto-increment berikutnya akan bentrok. | Tambah 6 nama tabel di `DatabaseSeeder::resetSequences()`. |
| 6 | **`BagianSeeder` menghapus & seed ulang seluruh `bagian`.** Menambah 2 unit penunjang di tengah list akan menggeser id existing dan merusak data. | **Append di akhir**, lookup per NAMA bagian. |
| 7 | **Blok POCT `hasil_1..20` yang saya definisikan di §5.3 tidak jadi dipakai** — hasil disimpan di `order_poct_detail`. Objek 440–442 hanya menyimpan ringkasan hasil terakhir. | Sudah dijelaskan di §5.4. Bila reviews menolak ringkasan, hapus 440–442 (tidak boleh menggeser objek 432–439). |
| 8 | **POCT boleh diisi perawat** (create untuk profesi 2) — berbeda dari form 6/7. Ini keputusan klinis yang harus dikonfirmasi(Kebijakan TOCT di IGD: perawat melakukan glukometri). Bila ditolak, samakan dengan form 6. | Tandai di review; ubah satu baris `$akses`. |
| 9 | **`profesi_id = 15` belum ada.** Tanpa itu baris akses form 49 tidak pernah berlaku. | Seed profesi **sebelum** `$akses`. |
| 10 | Tiga form order baru menambah **6 tabel** — skema jadi 9 tabel order. Pertimbangkan konsolidasi pada iterasi berikutnya bila jumlah terus bertambah. | Dicatat; **jangan** doing sekarang agar `order_laboratorium`/`order_radiologi` yang sudah running tidak tersentuh. |