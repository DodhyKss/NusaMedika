# KONSEP FORM GIZI & NUTRISI (EMR)

**Status:** Rancangan / Konsep — belum diimplementasikan
**Tanggal:** 2026-10-10
**Target:** form_id 39–44 · objek_id 362–396 · `dashboard_menu` 10 (baru)
**Acuan:** [`PANDUAN_IMPLEMENTASI_FORM_EMR.md`](PANDUAN_IMPLEMENTASI_FORM_EMR.md),
[`ANALISIS_KEKURANGAN_FORM.md`](ANALISIS_KEKURANGAN_FORM.md)
**Sumber legacy:** `/simrs_tenriawaru/FE/lib/modul/`

---

## 1. Ringkasan

Domain **Gizi & Nutrisi** adalah domain klinis yang sama sekali belum ada di NusaMedika
(`ANALISIS_KEKURANGAN_FORM.md` §2 domain 9). Enam form ini menutup celah tersebut:

| form_id | nama_form | slug | id_dash_menu | ri | rj | igd | mcu |
|---|---|---|---|---|---|---|---|
| 39 | Asesmen Gizi | `asesmen_gizi` | `10.153` | 1 | 1 | 1 | 0 |
| 40 | Detail Nutrisi | `detail_nutrisi` | `10.154` | 1 | 0 | 0 | 0 |
| 41 | Rekap Asesmen Nutrisi | `rekap_asesmen_nutrisi` | `10.155` | 1 | 0 | 0 | 0 |
| 42 | Pesan Makanan & Monitoring Asupan | `pesan_makanan` | `10.156` | 1 | 0 | 0 | 0 |
| 43 | Diet Pasien | `diet_pasien` | `10.157` | 1 | 1 | 1 | 0 |
| 44 | ADIME | `adime` | `10.158` | 1 | 0 | 0 | 0 |

> **Rencana:** menu 10 hanya rawat inap & rawat jalan. IGD tidak dit skeptis
> (pasien IGD belum punya asesmen gizi tercatat); MCU memakai asesmen grieve yang
> terpisah (lihat `KONSEP_FORM_MCU.md`).

### 1.1 Alokasi `dashboard_menu` & sub-menu

```
dashboard_menu (BARU)
└── 10 = "Gizi & Nutrisi"                       [BARU — id 10 belum dipakai]
    ├── sub 153 = "Asesmen Gizi"                → form 39  id_dash_menu "10.153"
    ├── sub 154 = "Detail Nutrisi"              → form 40  id_dash_menu "10.154"
    ├── sub 155 = "Rekap Asesmen Nutrisi"       → form 41  id_dash_menu "10.155"
    ├── sub 156 = "Pesan Makanan"               → form 42  id_dash_menu "10.156"
    ├── sub 157 = "Diet Pasien"                  → form 43  id_dash_menu "10.157"
    └── sub 158 = "ADIME"                        → form 44  id_dash_menu "10.158"
```

> **Penting — PK sub-menu bersifat global.** Aturan alokasi global
> (`ALOKASI_ID_GLOBAL.md` §3): setiap dokumen mendapat *band* sub 20 ID yang
> eksklusif, dan dokumen ini memakai band **153–172**. PK 1–12 sudah terpakai
> menu 1–5 (1 Soap, 2 Pengkajian Keperawatan, 3 Peresepan Obat soft-delete,
> 8 Implementasi Keperawatan, 9 Tindakan Medis, 10 Assesmen Awal RJ, 11 SBAR,
> 12 Observasi Harian) dan band 13–152 dialokasikan ke dokumen
> `KONSEP_*` lain. Angka "1–6" pada tabel di atas adalah **urutan posisi**
> sub-menu di bawah menu 10, bukan PK-nya. `id_dash_menu` **wajib**
> mengikuti PK nyata (`dashboard_menu_id.dashboard_menu_sub_id`) karena
> `header_ehr` membangun string itu dari baris DB — lihat §6 Implementasi.
> **Tanpa `dashboard_menu_sub_extra`** → `CONCAT_WS` melewati NULL sehingga
> `id_dash_menu` = `"10.153"`, bukan `"10.153."`.

### 1.2 Alokasi objek

| Form | Objek baru | Rentang |
|---|---|---|
| 39 Asesmen Gizi | 362–381 | 20 |
| 40 Detail Nutrisi | 382–386 | 5 |
| 41 Rekap Asesmen Nutrisi | 387 | 1 |
| 42 Pesan Makanan | 388–395 | 8 |
| 43 Diet Pasien | 396 | 1 |
| 44 ADIME | — (reuse 377/379/380) | 0 |
| **Total** | | **362–396 (35 objek)** |

Objek **yang di-reuse** (wajib, lihat `PANDUAN_IMPLEMENTASI_FORM_EMR.md` §3 Step 3):

| objek_id | nama_objek | Dipakai di |
|---|---|---|
| 1 | Subjective (S) | 39 (subjektif)… → dipakai form 102 |
| 2 | Objective (O) | 39 |
| 3 | Assessment (A) | 44 ADIME |
| 5 | Instruksi (I) | 44 ADIME |
| 6 | Tekanan Darah Sistolik | 39 (tanda vital) |
| 7 | Tekanan Darah Diastolik | 39 |
| 8 | Berat Badan | 39 (BB saat ini) |
| 9 | Tinggi Badan | 39 (TB/PB) |
| 10 | Nadi | 39 |
| 11 | Suhu | 39 |
| 12 | Pernapasan | 39 |
| 15 | Saturasi Oksigen | 39 |
| 17 | Alergi | 39 (alergi makanan) |
| 30 | Pantangan Makan | 39 |
| 40 | Diagnosa Medis | 39 |
| 41 | Riwayat Penyakit Sebelumnya | 39 (riwayat penyakit pasien) |
| 58 | BMI | 39 (IMT) |
| 76 | Prioritas | 42 (prioritas pesan makanan) |
| 77 | Keterangan | 43, 44 |
| 82 | Petugas Pelaksana | 41 (nama ahli gizi) |

---

## 2. Sumber Referensi Legacy

| Yang diambil | File legacy |
|---|---|
| Struktur asesmen gizi lengkap (riwayat diet, antropometri, biokimia, fisik klinis, rekomendasi) | `simrs_tenriawaru/FE/lib/modul/asesmen_gizi.php` |
| Skrining SGA/NRS (5 parameter, skor 1–5, total 0–25) | `simrs_tenriawaru/FE/lib/modul/skrining_gizi_dengan_sga.php` |
| **MNA-SF** untuk dewasa (4 item, skor 0–14) | `simrs_tenriawaru/FE/lib/modul/Nutrisi_cak_dewasa.php` |
| **MST** untuk lanjut usia (6 item A–F2, skor 0–14) | `simrs_tenriawaru/FE/lib/modul/Nutrisi_cak.php` |
| **MST-Anak** (4 item + 16 diagnosis khusus, skor 0–5) | `simrs_tenriawaru/FE/lib/modul/Nutrisi_cak_anak.php` |
| Wrapper yang memilih instrumen berdasarkan usia | `simrs_tenriawaru/FE/lib/modul/skrining_nutrisi.php` |
| Detail/evaluasi hasil skrining + status baca ahli gizi | `simrs_tenriawaru/FE/lib/modul/detail_nutrisi.php` |
| Rekap skrining nutrisi per ruang perawatan | `simrs_tenriawaru/FE/lib/modul/rekap_assesment_nutrisi.php` + `rekap_assesment_nutrisi_act.php` |
| Pesanan makanan & monitoring expel asup (jadwal distribusi + petugas) | `simrs_tenriawaru/FE/lib/modul/pesan_makanan_monitoring_asupan.php` |
| Form diet (jenis makanan, jenis diet faktor penyakit, keterangan) | `simrs_tenriawaru/FE/lib/modul/diit.php` |
| ADIME (assessment, diagnosamu, intervensi, monitoring, evaluasi, instruksi) | `simrs_tenriawaru/FE/lib/modul/adime.php` |
| Helper konversi usia & iterasi | `simrs_tenriawaru/FE/lib/func/_func.php` (`regulasiumur`, `umuraja`, `umurIndonesia`) |

> Field legacy memakai pasangan `detail_emr[var]` + `objek_id[var]`. NusaMedika
> memakai satu baris `objek_form_control` per variabel — **nilai `objek_id` legacy
> tidak dipakai sebagai nomor objek NusaMedika**. Yang diambil adalah *nama
> field, opsi, dan kriteria skornya*.

---

## 3. Form 39 — Asesmen Gizi

### 3.1 Rasional / tujuan klinis

Asesmen gizi adalah pengkajian awal yang dilakukan-tenaga gizi (atau perawat
terlatih) dalam 24 jam pertama pasien masuk. Output-nya menentukan: status gizi,
risiko malnutrisi, kebutuhan energi, dan bentuk intervensi gizi (diet, suplemen,
dukungan FDD, enteral/parenteral). Legacy memisahkannya menjadi lima blok
seksi — **Riwayat Klien**, **Riwayat Diet**, **Antropometri**, **Biokimia**,
**Fisik Klinis**, dan **Rekomendasi** — dan struktur itu dipertahankan.

### 3.2 Struktur Field

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `tanggal_asesmen` | 362 | Tanggal Asesmen Gizi | date | ya | tanpa default, diisi manual |
| `waktu_asesmen` | 363 | Waktu Asesmen Gizi | time | ya | tanpa default, diisi manual |
| `jenis_asesmen` | 364 | Jenis Asesmen Gizi | radio | ya | `Perlu Asuhan Gizi` / `Re-Asesmen` (legacy `jenis_asesmen_gizi` 1/2) |
| `umur` | — | — | text readonly | tidak | diturunkan dari `pasien.tgl_lahir` + `registrasi_detail.tgl_daftar`, **tidak disimpan** |
| `jenis_kelamin` | — | — | text readonly | tidak | diturunkan dari master pasien, **tidak disimpan** |
| `riwayat_penyakit` | 41 | Riwayat Penyakit Sebelumnya | text | ya | legacy `riwayat` |
| `riwayat_penyakit_keluarga` | 365 | Riwayat Penyakit Keluarga | text | tidak | legacy `riwayat_penyakit_keluarga` |
| `diagnosa_medis` | 40 | Diagnosa Medis | textarea | ya | diagnosa dokter pemeriksa |
| `alergi` | 17 | Alergi | textarea | tidak | **alergi makanan** (bukan alergi obat) |
| `ketidaksukaan_makanan` | 366 | Ketidaksukaan Makanan | text | tidak | |
| `pantangan_makan` | 30 | Pantangan Makan | text | tidak | |
| `pengalaman_diit` | 367 | Pengalaman Diet / Konsul Sebelumnya | radio | ya | `Tidak` / `Ada` |
| `asupan_smrs` | 368 | Asupan Makanan SMRS | text | tidak | legacy: radio Ada/Tidak + uraian; digabung satu field |
| `berat_badan` | 8 | Berat Badan | number (kg) | ya | BB saat ini |
| `tinggi_badan` | 9 | Tinggi Badan | number (cm) | ya | TB/PB |
| `bmi` | 58 | BMI | number (kg/m²) | **turunan** | `berat_badan / (tinggi_badan/100)²`, dihitung ulang server |
| `status_gizi` | — | — | badge readonly | **turunan** | diturunkan dari `bmi`, **tidak disimpan** (lihat §3.4) |
| `z_score` | 369 | Z-Score | number | tidak | khusus anak (< 18 tahun) |
| `lingkar_lengan` | 370 | Lingkar Lengan Atas | number (cm) | tidak | |
| `bb_biasanya` | 371 | Riwayat Penurunan Berat Badan | text | tidak | BB biasanya (kg) + durasi (bulan) dalam satu field terstruktur |
| `penurunan_bb_persen` | — | — | number readonly | **turunan** | `((bb_biasanya - bb) / bb_biasanya) * 100`, dihitung server |
| `biokimia` | 372 | Biokimia Terkait Gizi | textarea | tidak | albumin, prealbumin, Hb, iron, dll. |
| `nafsu_makan` | 373 | Nafsu Makan | radio | ya | `Baik` / `Kurang` / `Tidak Ada` |
| `gigi_geligi` | 374 | Gigi Geligi | radio | ya | `Lengkap` / `Tidak Lengkap` |
| `gangguan_menelan` | 375 | Gangguan Menelan / Menghisap / Mengunyah | checkbox | ya | 3 opsi dalam satu objek: `Menelan`, `Menghisap`, `Mengunyah` |
| `fkg_diare` | 376 | Keluhan Fisik Klinis Gizi | radio | ya | `Ada` / `Tidak` |
| `fkg_edema_ascites` | 376 | Keluhan Fisik Klinis Gizi | radio | ya | `Ada` / `Tidak` |
| `fkg_muntah` | 376 | Keluhan Fisik Klinis Gizi | radio | ya | `Ada` / `Tidak` |
| `fkg_konst<constipation>` | 376 | Keluhan Fisik Klinis Gizi | radio | ya | `Ada` / `Tidak` |
| `fkg_mual` | 376 | Keluhan Fisik Klinis Gizi | radio | ya | `Ada` / `Tidak` |
| `fkg_hilang_lemak` | 376 | Keluhan Fisik Klinis Gizi | radio | ya | `Ada` / `Tidak` (hilang lemak subkutan) |
| `td_sistolik` | 6 | Tekanan Darah Sistolik | number | tidak | bagian blok Tanda Vital |
| `td_diastolik` | 7 | Tekanan Darah Diastolik | number | tidak | |
| `nadi` | 10 | Nadi | number | tidak | |
| `suhu` | 11 | Suhu | number | tidak | |
| `pernapasan` | 12 | Pernapasan | number | tidak | |
| `saturasi` | 15 | Saturasi Oksigen | number | tidak | |
| `diagnosa_gizi` | 377 | Diagnosa Gizi | textarea | ya | dipakai bersama form 43 & 44 |
| `kebutuhan_energi` | 378 | Kebutuhan Energi Pasien | text | ya | kcal/hari, hasil perhitungan riotn |
| `intervensi_gizi` | 379 | Intervensi Gizi | textarea | ya | dipakai bersama form 44 |
| `monev` | 380 | Monitoring & Evaluasi (MONEV) | textarea | ya | dipakai bersama form 44 |
| `rekomendasi` | 381 | Sumber Rekomendasi | radio | ya | `DPJP` / `Ahli Gizi` (legacy `recomendation` 1/2) |
| `alergi` | 17 | Alergi | textarea | tidak | duplikat dengan baris pertama — **hanya satu** |

> **Objek 376 dipakai oleh 6 variabel.** Ini sah: `objek_form_control.objek_id` hanya
> penunjuk semantik, dan presedennya sudah ada di `EmrMasterSeeder` form 3
> (`'vaksin_covid'`, `'tanggal_covid_1'`, `'tanggal_covid_2'` → objek 62).
> Tujuannya agar 6 gejala klinis tidak memakan 6 objek.
>
> **Status gizi & % penurunan BB tidak disimpan** — dihitung di `filteredData()`/view
> dari `bmi` dan (`bb_biasanya`, `berat_badan`). Menyimpannya membuka peluang
> nilai tidak konsisten (user bisa POST nilai fake).

### 3.3 Status gizi dari BMI ( Indonesia, Belfanti )

Dihitung ulang di **server** (lihat §3.4) — ambang tetap sama dengan legacy:

| BMI (kg/m²) | Status Gizi |
|---|---|
| < 16,0 | Benar-benar Sangat Kekurangan Berat Badan |
| 16,0 – 16,9 | Sangat Kekurangan Berat Badan |
| 17,0 – 18,4 | Kekurangan Berat Badan |
| 18,5 – 24,9 | Normal |
| 25,0 – 29,9 | Kelebihan Berat Badan |
| 30,0 – 34,9 | Obesitas Kelas I |
| 35,0 – 39,9 | Obesitas Kelas II |
| ≥ 40,0 | Obesitas Kelas III |

> Untuk anak (< 18 tahun) **status gizi ditentukan dari Z-score WHO**, bukan BMI.
> Karena NusaMedika tidak punya tabel WHO, verifikasi: Z-score diisi manual oleh
> petugas (objek 369) dan badge "Status Gizi" disembunyikan bila usia < 18 tahun
> (lihat `GiziHelper::usia()`).

### 3.4 Komputasi server-side

```php
// app/Helpers/GiziHelper.php (BARU)
public static function imt(?float $bb, ?float $tb): ?float   // null bila tb <= 0
public static function statusGizi(?float $imt, int $umur): ?string
public static function penurunanBbPersen(?float $bb, ?float $bbBiasanya): ?float
public static function usia(string $tglLahir, string $tglDaftar): int
```

Semua fungsi mengembalikan `null` (bukan `0`) bila input kosong/tidak wajar —
mengikuti pola `EwsHelper::skor()` (§2.4 panduan).

### 3.5 Dashboard & Form Master

```php
// database/seeders/EmrMasterSeeder.php — $menus
['dashboard_menu_id' => 10, 'nama_menu' => 'Gizi & Nutrisi'],

// $subMenus  (PK 153–158, TANPA dashboard_menu_sub_extra)
['dashboard_menu_sub_id' => 153, 'dashboard_menu_id' => 10, 'nama_sub_menu' => 'Asesmen Gizi'],
['dashboard_menu_sub_id' => 154, 'dashboard_menu_id' => 10, 'nama_sub_menu' => 'Detail Nutrisi'],
['dashboard_menu_sub_id' => 155, 'dashboard_menu_id' => 10, 'nama_sub_menu' => 'Rekap Asesmen Nutrisi'],
['dashboard_menu_sub_id' => 156, 'dashboard_menu_id' => 10, 'nama_sub_menu' => 'Pesan Makanan'],
['dashboard_menu_sub_id' => 157, 'dashboard_menu_id' => 10, 'nama_sub_menu' => 'Diet Pasien'],
['dashboard_menu_sub_id' => 158, 'dashboard_menu_id' => 10, 'nama_sub_menu' => 'ADIME'],

// $forms
['form_id' => 39, 'nama_form' => 'Asesmen Gizi', 'slug' => 'asesmen_gizi', 'id_dash_menu' => '10.153', 'ri' => 1, 'rj' => 1, 'igd' => 1, 'mcu' => 0],
['form_id' => 40, 'nama_form' => 'Detail Nutrisi', 'slug' => 'detail_nutrisi', 'id_dash_menu' => '10.154', 'ri' => 1, 'rj' => 0, 'igd' => 0, 'mcu' => 0],
['form_id' => 41, 'nama_form' => 'Rekap Asesmen Nutrisi', 'slug' => 'rekap_asesmen_nutrisi', 'id_dash_menu' => '10.155', 'ri' => 1, 'rj' => 0, 'igd' => 0, 'mcu' => 0],
['form_id' => 42, 'nama_form' => 'Pesan Makanan & Monitoring Asupan', 'slug' => 'pesan_makanan', 'id_dash_menu' => '10.156', 'ri' => 1, 'rj' => 0, 'igd' => 0, 'mcu' => 0],
['form_id' => 43, 'nama_form' => 'Diet Pasien', 'slug' => 'diet_pasien', 'id_dash_menu' => '10.157', 'ri' => 1, 'rj' => 1, 'igd' => 1, 'mcu' => 0],
['form_id' => 44, 'nama_form' => 'ADIME', 'slug' => 'adime', 'id_dash_menu' => '10.158', 'ri' => 1, 'rj' => 0, 'igd' => 0, 'mcu' => 0],
```

> `Str::slug('ADIME', '_')` = `adime` ✓ (huruf kecil semua), dan
> `Str::studly('adime')` = `Adime` → folder controller/view **`Adime`**.

### 3.6 Mapping

```php
// $mapping[39]
39 => [
    'tanggal_asesmen'              => 362,
    'waktu_asesmen'                => 363,
    'jenis_asesmen'                => 364,   // Perlu Asuhan Gizi / Re-Asesmen
    'riwayat_penyakit'             => 41,    // reuse
    'riwayat_penyakit_keluarga'    => 365,
    'diagnosa_medis'               => 40,    // reuse
    'alergi'                       => 17,    // reuse
    'ketidaksukaan_makanan'        => 366,
    'pantangan_makan'              => 30,    // reuse
    'pengalaman_diit'              => 367,
    'asupan_smrs'                  => 368,
    'berat_badan'                  => 8,     // reuse
    'tinggi_badan'                 => 9,     // reuse
    'bmi'                          => 58,    // reuse, TURUNAN
    'z_score'                      => 369,
    'lingkar_lengan'               => 370,
    'bb_biasanya'                  => 371,
    'biokimia'                     => 372,
    'nafsu_makan'                  => 373,
    'gigi_geligi'                  => 374,
    'gangguan_menelan'             => 375,   // Menelan / Menghisap / Mengunyah
    'gangguan_menghisap'           => 375,
    'gangguan_mengunyah'           => 375,
    'fkg_diare'                    => 376,
    'fkg_edema_ascites'            => 376,
    'fkg_muntah'                   => 376,
    'fkg_konst<constipation'        => 376,
    'fkg_mual'                     => 376,
    'fkg_hilang_lemak'             => 376,
    'td_sistolik'                  => 6,     // reuse
    'td_diastolik'                 => 7,     // reuse
    'nadi'                         => 10,    // reuse
    'suhu'                         => 11,    // reuse
    'pernapasan'                   => 12,    // reuse
    'saturasi'                     => 15,    // reuse
    'diagnosa_gizi'                => 377,
    'kebutuhan_energi'             => 378,
    'intervensi_gizi'              => 379,
    'monev'                        => 380,
    'rekomendasi'                  => 381,
],
```

`$mapping[44]` memakai objek yang sama: `assessment` → 3, `diagnosa_gizi` → 377,
`intervensi` → 379, `monitor_*`/`evaluasi` → 380, `instruksi` → 5.

### 3.7 Akses EHR

```php
// $akses — Dokter (1) & Perawat (2) full CRUD
['profesi_id' => 1, 'form_id' => 39, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 2, 'form_id' => 39, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
```

### 3.8 Validasi

| Field | Aturan |
|---|---|
| `tanggal_asesmen` | `required\|date` |
| `waktu_asesmen` | `required\|date_format:H:i` |
| `jenis_asesmen` | `required\|in:Perlu Asuhan Gizi,Re-Asesmen` |
| `diagnosa_medis` | `required\|string\|max:1000` |
| `berat_badan` | `required\|numeric\|min:0.5\|max:500` |
| `tinggi_badan` | `required\|numeric\|min:20\|max:260` |
| `z_score` | `nullable\|numeric\|between:-6,6` |
| `lingkar_lengan` | `nullable\|numeric\|min:5\|max:90` |
| `nafsu_makan` | `required\|in:Baik,Kurang,Tidak Ada` |
| `gigi_geligi` | `required\|in:Lengkap,Tidak Lengkap` |
| `fkg_*` (6 field) | `required\|in:Ada,Tidak` |
| `diagnosa_gizi` / `intervensi_gizi` / `monev` | `required\|string\|max:2000` |
| `rekomendasi` | `required\|in:DPJP,Ahli Gizi` |

`bmi` **tidak pernah divalidasi dari browser** — dihitung di `filteredData()`.
Bila `tinggi_badan`/`berat_badan` kosong ⇒ `bmi = null` (bukan 0).

### 3.9 Cetak

`print.blade.php` + route `/emr/asesmen_gizi/print/{emr_id}` (pola `emr.soap.print`).
Layout: header `Informasi Pasien` → blok TTV → antropometri (BB/TB/IMT/status +
Z-score) → fisik klinis → MONEV → tanda tanganatheres `Ahli Gizi`.

---

## 4. Form 40 — Detail Nutrisi (SGA / MNA-SF / MST)

### 4.1 Rasional / tujuan klinis

Form ini adalah **skor skrining nutrisi**. Legacy memilih instrumen berdasarkan
usia pasien (`skrining_nutrisi.php` baris 96–105):

| Usia | Instrumen | Skor | Kesimpulan |
|---|---|---|---|
| 0–18 tahun | MST-Anak | 0–5 | 0 Risiko Rendah · 1–3 Risiko Sedang · 4–5 Risiko Tinggi |
| 19–59 tahun | MNA-SF | 0–14 | ≥ 2 → lakukan asesmen lanjut oleh ahli gizi |
| ≥ 60 tahun | MST | 0–14 | 12–14 Normal · 8–11 Berisiko Malnutrisi · 0–7 Malnutrisi |

Legacy juga punya **SGA/NRS** (`skrining_gizi_dengan_sga.php`) — 5 parameter,
skor 1–5, total 0–25. NusaMedika membuat instrumen **dapat dipilih manual**
(override), mengikuti pola `risiko_jatuh_instrumen` di `RisikoJatuhHelper`.

### 4.2 Struktur Field

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `instrumen` | 382 | Instrumen Skrining Gizi | select | ya | `SGA`, `MNA-SF`, `MST`, `MST-Anak` |
| `sga_1` … `sga_5` | 383 | Jawaban Item Instrumen Gizi | radio | sesuai instrumen | 5 item SGA/NRS, skor 1–5 |
| `mna_1` … `mna_4` | 383 | Jawaban Item Instrumen Gizi | radio | sesuai instrumen | MNA-SF, skor 0/2/1/3 |
| `mst_a` … `mst_f2` | 383 | Jawaban Item Instrumen Gizi | radio | sesuai instrumen | MST, skor 0–3 |
| `mstak_1` … `mstak_4` | 383 | Jawaban Item Instrumen Gizi | radio | sesuai instrumen | MST-Anak, skor 1/0 |
| `mstak_diag_1` … `mstak_diag_16` | 383 | Jawaban Item Instrumen Gizi | checkbox | tidak | 16 diagnosis khusus anak |
| `skor` | 384 | Skor Skrining Gizi | number readonly | **turunan** | jumlah skor item aktif, server |
| `kesimpulan` | 385 | Kesimpulan Gizi | badge readonly | **turunan** | server |
| `ahli_gizi_id` | 82 | Petugas Pelaksana | select (pegawai) | tidak | petugas yang membaca hasil |
| `tanggal_evaluasi` | 386 | Tanggal Evaluasi Ahli Gizi | date | ya | legacy `tgl_evaluasi` |

### 4.3 Kriteria SGA / NRS (5 parameter, skor 1–5)

| # | Parameter | 1 | 2 | 3 | 4 | 5 |
|---|---|---|---|---|---|---|
| 1 | Perubahan Berat Badan 6 bulan | Tidak ada penurunan | Kehilangan < 5% | 5–10% | 10–15% | > 15% |
| 2 | Asupan Makan | Tidak ada perubahan | Sub-optimal solid (bisa makan ½–¾ p) | Hanya cair / padat < ½ p | Hipokalori liquid (cair saja) | Tidak ada asfoten |
| 3 | Gejala Saluran Cerna | Tidak ada gejala | Nausea | Muntah / gangguan cerna | Diare | Severe anorexia |
| 4 | Kapasitas Fungsional | Normal | Aktifitas fisik terbatas (ringan) | Terbatas (sedang) | Sangat ringan (di tempat tidur) | Bedrest / berdiri dengan bantuan |
| 5 | Ko-morbiditas | — | 1 penyakit penyerta | 2 penyakit penyerta | 3 penyakit penyerta | Komorbiditas multiple + kondisi sangat berat |

> Baris ko-morbiditas legacy membedakan *lamaeterminasi* ("Sudah HD < 12 bulan" =
> 1, "1–2 th" = 2, "2–4 th atau usia > 75" = 3, "> 4 th" = 4). NusaMedika
> memakai label generik "jumlah penyakit penyerta" agar tidak menyiratkan
> hemodialisis pada form yang juga dipakai pasien non-HD.

**Kesimpulan (server):** total ≤ 6 → baik; 7–13 → colonization sedang;
14–19 → malnutrisi sedang; ≥ 20 → malnutrisi berat. **Perhatian:** legacy tidak
mempunyai tabel konversi SGA (hanya menampilkan total). Karena itu
`GiziHelper::kesimpulanSga()` **wajib dikunci bersama tim klinis** sebelum
implementasi — sementara ini Only store skor.

### 4.4 Kriteria MNA-SF (dewasa 19–59, skor 0–14)

| # | Item | Opsi & skor |
|---|---|---|
| A | Penurunan BB tidak diinginkan dalam 6 bulan terakhir? | 0 = Tidak ada penurunan BB · 2 = Tidak yakin / terasa baju lebih longgar |
| B | Jika **A = 2**, berapa penurunan BB? | 1 = 1–6 kg · 2 = 6–10 kg · 3 = > 10 kg (nonaktif bila A = 0) |
| C | Asupan menurun karena tidak nafsu makan? | 0 = Tidak · 1 = Ya |
| D | Pasien dengan diagnosis khusus? | 0 = Tidak · 3 = Ya. **15 kondisi** (list di bawah) |

**Total = A + B + C + D.** `≥ 2` → "Lakukan asesmen lanjut oleh ahli gizi".
Item B **hanya aktif bila A = 2** — nilai B diabaikan bila A = 0.

**15 Diagnosis khusus (dewasa)** — `Nutrisi_cak_dewasa.php` baris 2:

1. Dalam perawatan di ruang HCU dan ICU · 2. Menggunakan NGT atau alat bantu makan yang lain ·
3. Intake sulit (dengan *oral nutrition supplement*) · 4. CKD · 5. Penyakit saluran cerna akut/maldigesti ·
6. TB dengan atau tanpa HIV · 7. Diabetes Melitus · 8. Sirosis hepatis atau HCC ·
9. Post PCI · 10. Post operasi digestif · 11. Pre dan post operasi jantung · 12. Luka bakar ·
13. CHF · 14. Kanker dengan atau tanpa kemoterapi/radioterapi ·
15. Stroke (pendarahan maupun iskemik)

> **Perubahan desain yang disengaja:** MNA-SF asli hanya Ya/Tidak per diagnosis.
> NusaMedika menyimpan **daftar kondisi yang terdeteksi** (checkbox 15, dipetakan
> ke objek 383 dengan variabel bersuffix `mna_diag_1..15`), dan `diagnosa_khusus`
> dihitung server = `3` bila salah satu tercentang. Ini memberi informasi klinis
> tambahan tanpa menambah objek.

### 4.5 Kriteria MST (lanjut usia ≥ 60, skor 0–14)

| # | Item | Opsi & skor |
|---|---|---|
| A | Asupan makanan berkurang selama 3 bulan terakhir? | 0 = sangat berkurang · 1 = agak berkurang · 2 = tidak berkurang |
| B | Penurunan berat badan selama 3 bulan terakhir | 0 = > 3 kg · 1 = tidak tahu · 2 = 1–3 kg · 3 = tidak ada penurunan |
| C | Mobilitas | 0 = terbatas di tempat tidur/kursi · 1 = mampu bangun tapi tidak bepergian · 2 = dapat bepergian keluar rumah |
| D | Gangguan tekanan psikologis / penyakit berat dalam 3 bulan terakhir | 0 = Ya · 2 = Tidak |
| E | Gangguan neuropsikologis | 0 = depresi/kepikunan berat · 1 = kepikunan ringan · 2 = tidak ada gangguan |
| F1 | Indeks Massa Tubuh (fallback) | 0 = < 19 · 1 = 19 – < 21 · 2 = 21 – < 23 · 3 = ≥ 23 |
| F2 | Lingkar betis (cm) — **hanya bila F1 tidak tersedia** | 0 = < 31 · 1 = ≥ 31 |

> **F1 & F2 saling eksklusif.** Bila F1 terisi, F2 diabaikan dan radio F2
> dinonaktifkan (JS `selectF1`/`selectF2` di legacy). Diterjemahkan ke
> `GiziHelper::hitungMst()`: `if ($f1 !== null) { $f2 = 0; }`.
>
> Total skor maksimal = 2+3+2+2+2+3 = **14** (F1) atau 13 (F2).
> Kesimpulan: **12–14 Status Gizi Normal · 8–11 Berisiko Malnutrisi · 0–7 Malnutrisi.**

### 4.6 Kriteria MST-Anak (0–18 tahun, skor 0–5)

| # | Item | Ya | Tidak |
|---|---|---|---|
| 1 | Apakah pasien tampak kurus? | 1 | 0 |
| 2 | Penurunan berat badan selama 1 bulan terakhir (bayi < 1 th: BB tidak naik selama 3 bulan)? | 1 | 0 |
| 3 | Diare > 5×/hari dan muntah > 3×/hari dalam seminggu terakhir, atau asupan menurun ≥ 1 minggu | 1 | 0 |
| 4 | Ada penyakit/keadaan yang berisiko menyebabkan malnutrisi? | 1 | 0 |

Plus **16 diagnosis khusus anak** (`mstak_diag_1..16`), yang bila salah satu
tercentang menambah skor **+3** (legacy: `diagnosa_khusus` 0 atau 3):

1. Diare kronik (> 2 minggu) · 2. (Tersangka) Penyakit Jantung Bawaan ·
3. (Tersangka) Infeksi HIV · 4. (Tersangka) Kanker · 5. Penyakit Hati Kronik ·
6. Penyakit Ginjal Kronik · 7. TB Paru · 8. Terpasang Stoma · 9. Luka Bakar Luas ·
10. Trauma · 11. Kelainan anatomi mulut (mis. bibir sumbing) ·
12. Rencana/pasca operasi mayor (laparatomi, toraktomi) · 13. Kelainan Metabolik Bawaan ·
14. Retardasi Mental · 15. Keterlambatan Perkembangan · 16. Lain-lain (pertimbangan dokter)

**Total = item 1–4 (0–4) + diagnosis_khusus (0 atau 3) → maksimal 7.**
Kesimpulan: **0 Risiko Rendah · 1–3 Risiko Sedang · 4–5 Risiko Tinggi.**
(Untuk skor 6–7 yang hanya mungkin bila diagnosis khusus = 3, kategori tetap
"Risiko Tinggi".)

### 4.7 Komputasi server-side

```php
// app/Helpers/GiziHelper.php
public const INSTRUMEN = ['SGA', 'MNA-SF', 'MST', 'MST-Anak'];

public static function instrumenDefault(int $umur): string
public static function hitung(string $instrumen, array $jawaban): array
// ['skor' => int|null, 'kesimpulan' => 'Gizi Baik (Normal)'|'Gizi Kurang'|'Gizi Buruk'|...]
public static function diagnosisKhusus(array $terpilih): int   // 0 atau 3
```

Aturan wajib (`PANDUAN_IMPLEMENTASI_FORM_EMR.md` §2.4):

1. **Nilai skor & kesimpulan dari browser SELALU dibuang.** Hitung ulang dari
   jawaban item mentah.
2. Jika ada item instrumen aktif yang kosong → skor `null`, kesimpulan `null`
   (bukan "Normal"), supaya parse parsial tidak menyesatkan.
3. `instrumen` yang tidak dikirim (form render ulang) → fallback
   `instrumenDefault($usiaPasien)`, seperti `RisikoJatuhHelper::deteksiInstrumen()`.
4. Item di luar instrumen aktif (mis. `mst_a` saat instrumen = MNA-SF) —
   **dibuang dari payload**, bukan disimpan sebagai baris yatim.

### 4.8 Dashboard & Form Master

```php
['dashboard_menu_sub_id' => 154, 'dashboard_menu_id' => 10, 'nama_sub_menu' => 'Detail Nutrisi'],
['form_id' => 40, 'nama_form' => 'Detail Nutrisi', 'slug' => 'detail_nutrisi', 'id_dash_menu' => '10.154', 'ri' => 1, 'rj' => 0, 'igd' => 0, 'mcu' => 0],
```

### 4.9 Mapping

```php
// $mapping[40]
40 => [
    'instrumen'     => 382,   // SGA / MNA-SF / MST / MST-Anak
    // --- SGA / NRS (5 item, skor 1-5) ---
    'sga_1'         => 383,
    'sga_2'         => 383,
    'sga_3'         => 383,
    'sga_4'         => 383,
    'sga_5'         => 383,
    // --- MNA-SF (A-D) ---
    'mna_1'         => 383,   // A: penurunan BB Ya/Tidak (0/2)
    'mna_2'         => 383,   // B: 1-6 / 6-10 / >10 kg (1/2/3)
    'mna_3'         => 383,   // C: Nafsu makan (0/1)
    'mna_4'         => 383,   // D: Diagnosis khusus Ya/Tidak (0/3) -- TURUNAN
    'mna_diag_1'    => 383,   // 15 kondisi khusus dewasa (checkbox)
    // ... s/d 'mna_diag_15' => 383
    // --- MST (A-E, F1, F2) ---
    'mst_a'         => 383,
    'mst_b'         => 383,
    'mst_c'         => 383,
    'mst_d'         => 383,
    'mst_e'         => 383,
    'mst_f1'        => 383,   // IMT
    'mst_f2'        => 383,   // Lingkar betis (eksklusif dengan f1)
    // --- MST-Anak (4 item + 16 diagnosis khusus) ---
    'mstak_1'       => 383,
    'mstak_2'       => 383,
    'mstak_3'       => 383,
    'mstak_4'       => 383,
    'mstak_diag_1'  => 383,
    // ... s/d 'mstak_diag_16' => 383
    // --- TURUNAN ---
    'skor'          => 384,
    'kesimpulan'    => 385,
    'ahli_gizi_id'  => 82,    // reuse Petugas Pelaksana
    'tanggal_evaluasi' => 386,
],
```

> Baris `objek_form_control` yang di-generate: 4 (header) + 5 (SGA) + 4 (MNA) +
> 15 (MNA diag) + 7 (MST) + 20 (MST-Anak) + 2 (turunan) + 2 (ahli gizi) = **59 baris**.
> Semuanya dipetakan ke 4 objek (383–386 + reuse 82).

### 4.10 Akses EHR

```php
['profesi_id' => 1, 'form_id' => 40, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 2, 'form_id' => 40, 'level_id' => 1, 'bagian_id' => null, 'akses_read' => 1, 'akses_create' => 0, 'akses_update' => 0, 'akses_delete' => 0],
```

> Perawat **read-only** — dosis/BMI MNA-SF harus diisi petugas gizi, bukan perawat.
> (Legacy `skrining_nutrisi.php` memang preencherkannya perawat, tetapi isi
> boleh diperbaiki ke sistem.).

### 4.11 Validasi

| Field | Aturan |
|---|---|
| `instrumen` | `required\|in:SGA,MNA-SF,MST,MST-Anak` |
| `sga_1..5` | `nullable\|integer\|between:1,5` · wajib hanya bila instrumen = SGA |
| `mna_1` | `nullable\|integer\|in:0,2` |
| `mna_2` | `nullable\|integer\|between:1,3` (hanya relevan bila `mna_1 = 2`) |
| `mna_3` | `nullable\|integer\|in:0,1` |
| `mst_a` | `nullable\|integer\|between:0,2` |
| `mst_b` | `nullable\|integer\|between:0,3` |
| `mst_c` | `nullable\|integer\|between:0,2` |
| `mst_d` | `nullable\|integer\|in:0,2` |
| `mst_e` | `nullable\|integer\|between:0,2` |
| `mst_f1` | `nullable\|integer\|between:0,3` |
| `mst_f2` | `nullable\|integer\|between:0,1` |
| `mstak_1..4` | `nullable\|integer\|in:0,1` |
| `tanggal_evaluasi` | `required\|date` |

Skor/kesimpulan tidak divalidasi — selalu turunan.

### 4.12 Cetak

Cetak **ringkasan skrining** (1 halaman): identitas, instrumen, tabel item + skor,
skor total, kesimpulan, nama & tanggalelijke ahli gizi. Route
`/emr/detail_nutrisi/print/{emr_id}`.

---

## 5. Form 41 — Rekap Asesmen Nutrisi

### 5.1 Rasional / tujuan klinis

Bukan form input, melainkan **daftar kerja** seluruh pasien rawat inap di satu
ruang beserta status skrining nutrisinya — untuk-head nurse memantau pasien
yang belum diasesmen. Legacy menampilkannya per-ruang
(`referensi_bagian = instalasi ranap`) lalu me-expand daftar per kriteria
(`rekap_assesment_nutrisi.php`).

NusaMedika tidak punya form rekap di dalam dashboard pasien. Form ini
diwujudkan sebagai **form EMR read-mostly**: berisi satu baris status untuk
pasien yang sedang dibuka, dan **rekap lengkap tersedia lewat tombol "Cetak Rekap"**
yang memanggil `GiziHelper::rekapRuang($bagianId, $tglAwal, $tglAkhir)`.

### 5.2 Struktur Field

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `tanggal_rekap` | 362 | Tanggal Asesmen Gizi | date | ya | reuse objek form 39 |
| `status_asesmen` | 387 | Status Kelengkapan Asesmen Gizi | radio | ya | `Belum` / `Sudah` |
| `tanggal_asesmen_terakhir` | 362 | Tanggal Asesmen Gizi | date readonly | tidak | reuse; diisi dari EMR form 39 terakhir |
| `skor` | 384 | Skor Skrining Gizi | number readonly | tidak | reuse; dari form 40 terakhir |
| `kesimpulan` | 385 | Kesimpulan Gizi | badge readonly | tidak | reuse; dari form 40 terakhir |
| `ahli_gizi_id` | 82 | Petugas Pelaksana | readonly | tidak | reuse |
| `keterangan` | 77 | Keterangan | textarea | tidak | reuse |

### 5.3 Dashboard & Form Master

```php
['dashboard_menu_sub_id' => 155, 'dashboard_menu_id' => 10, 'nama_sub_menu' => 'Rekap Asesmen Nutrisi'],
['form_id' => 41, 'nama_form' => 'Rekap Asesmen Nutrisi', 'slug' => 'rekap_asesmen_nutrisi', 'id_dash_menu' => '10.155', 'ri' => 1, 'rj' => 0, 'igd' => 0, 'mcu' => 0],
```

### 5.4 Mapping

```php
// $mapping[41]
41 => [
    'tanggal_rekap'            => 362,  // reuse
    'status_asesmen'           => 387,
    'tanggal_asesmen_terakhir' => 362,  // reuse
    'skor'                     => 384,  // reuse
    'kesimpulan'               => 385,  // reuse
    'ahli_gizi_id'             => 82,   // reuse
    'keterangan'               => 77,   // reuse
],
```

### 5.5 Akses EHR

```php
['profesi_id' => 1, 'form_id' => 41, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 0],
['profesi_id' => 2, 'form_id' => 41, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 0],
['profesi_id' => 10, 'form_id' => 41, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
```

> `profesi_id = 10` (Ahli Gizi) — **sudah ada** di `MasterPegawaiSeeder.php`
> (`ALOKASI_ID_GLOBAL.md` §5). **Jangan** memakai ID 3: itu sudah milik Bidan.

### 5.6 Validasi

| Field | Aturan |
|---|---|
| `tanggal_rekap` | `required\|date` |
| `status_asesmen` | `required\|in:Belum,Sudah` |
| `tanggal_asesmen_terakhir`, `skor`, `kesimpulan`, `ahli_gizi_id` | `nullable` (readonly di UI) |
| `keterangan` | `nullable\|string\|max:1000` |

### 5.7 Cetak

`GiziHelper::rekapRuang()` menghasilkan tabel: No · Nama · No. MR · Umur ·
Tanggal Masuk · Status Asesmen · Skor · Kesimpulan · Ahli Gizi. Route manual
(non-CRUD, pola `PenunjangHelper`):
`GET /emr/rekap_asesmen_nutrisi/cetak?bagian_id=&tgl_awal=&tgl_akhir=`.

---

## 6. Form 42 — Pesan Makanan & Monitoring Asupan

### 6.1 Rasional / tujuan klinis

Form yang diisi **dapur / petugas Wound**: mencatat jadwal distribusi makan
pagi, siang, sore beserta petugas yangorant thereof, plus jenis pesanan yang
diminta. Legacy menambah tombol cetak **etiket diet** per jadwal
(`cetak_etiket_gizi.php?jadwal=Pagi|Siang|Sore|Malam`).

### 6.2 Struktur Field

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `tanggal_pesan` | 388 | Tanggal Pesan Makanan | date | ya | |
| `jenis_pesanan` | 389 | Jenis Pesanan Makanan | select | ya | `Jam Antar Dari Dapur Besar` / `Jam Awal Distribusi Makan Ke Pasien` / `Jam Selesai Makan Distribusi Ke Pasien` |
| `waktu_pagi` | 390 | Waktu Distribusi Pagi | time | ya | |
| `petugas_pagi` | 393 | Petugas Distribusi Pagi | text | ya | |
| `waktu_siang` | 391 | Waktu Distribusi Siang | time | ya | |
| `petugas_siang` | 394 | Petugas Distribusi Siang | text | ya | |
| `waktu_sore` | 392 | Waktu Distribusi Sore | time | ya | |
| `petugas_sore` | 395 | Petugas Distribusi Sore | text | ya | |
| `prioritas` | 76 | Prioritas | select | tidak | reuse; `BIASA` / `CITO` (swab pending) |
| `keterangan` | 77 | Keterangan | textarea | tidak | reuse |

> Legacy juga punya `snack_pagi` & `snack_siang`. Dihapus karena tidak ada
> jadwal distribution tersebut di master; bila diperlukan, tambahkan sebagai
> objek baru **di luar rentang dokumen ini** (≥ 397) — jangan pernah memakai
> objek milik dokumen lain.

### 6.3 Dashboard & Form Master

```php
['dashboard_menu_sub_id' => 156, 'dashboard_menu_id' => 10, 'nama_sub_menu' => 'Pesan Makanan'],
['form_id' => 42, 'nama_form' => 'Pesan Makanan & Monitoring Asupan', 'slug' => 'pesan_makanan', 'id_dash_menu' => '10.156', 'ri' => 1, 'rj' => 0, 'igd' => 0, 'mcu' => 0],
```

> `nama_sub_menu` = `Pesan Makanan` (bukan nama form yang panjang) karena
> **slug sub-menu menjadi `form_name` di URL** dan `Str::slug('Pesan Makanan','_')`
> = `pesan_makanan` ✓.

### 6.4 Mapping

```php
// $mapping[42]
42 => [
    'tanggal_pesan' => 388,
    'jenis_pesanan' => 389,
    'waktu_pagi'   => 390,
    'waktu_siang'  => 391,
    'waktu_sore'   => 392,
    'petugas_pagi' => 393,
    'petugas_siang'=> 394,
    'petugas_sore' => 395,
    'prioritas'    => 76,   // reuse
    'keterangan'   => 77,   // reuse
],
```

### 6.5 Akses EHR

```php
['profesi_id' => 1, 'form_id' => 42, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
['profesi_id' => 2, 'form_id' => 42, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 10, 'form_id' => 42, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
```

### 6.6 Validasi

| Field | Aturan |
|---|---|
| `tanggal_pesan` | `required\|date` |
| `jenis_pesanan` | `required\|string` (dari `SelectOption::render('jenis_pesanan_makanan')`) |
| `waktu_pagi` / `waktu_siang` / `waktu_sore` | `required\|date_format:H:i` |
| `petugas_pagi` / `_siang` / `_sore` | `required\|string\|max:255` |
| `prioritas` | `nullable\|in:BIASA,CITO` |
| `keterangan` | `nullable\|string\|max:1000` |

> Validasi **keunikan**: satu tanggal + satu jenis pesanan hanya boleh satu baris
> per `registrasi_detail_id`. Dicek di controller (`Rule::unique` tidak cocok
> karena `emr` bukan tabel tunggal) → query `emr` + `emr_detail` sebelum simpan.

### 6.7 Cetak

**Etiket diet** — route manual `GET /emr/pesan_makanan/etiket/{emr_id}?jadwal=Pagi|Siang|Sore|Malam`
menghasilkan label kecil 6×4 cm berisi nama pasien, no. MR, umur, diagnosis, dan
jenis diet (dari form 43). Template PDF sederhana (DomPDF/print CSS), **bukan**
`print.blade.php` karena ukurannya harus potong kertas.

---

## 7. Form 43 — Diet Pasien

### 7.1 Rasional / tujuan klinis

Menetapkan jenis diet yang dipakai pasien (Diet Mual, Diet Diabetes, Diet
Rendah Garam, …) beserta faktor penyakit yang mendasarinya. Legacy membacanya
dari master `jenis_diit` & `jenis_diagnosa_diit`.

### 7.2 Struktur Field

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `jenis_diet` | 396 | Jenis Diet Pasien | select | ya | master diet |
| `jenis_diagnosa_diet` | 377 | Diagnosa Gizi | multi-select | ya | reuse objek 377; **bisa banyak**, disimpan comma-separated |
| `keterangan` | 77 | Keterangan | textarea | tidak | reuse |

> **Field multi-pilih.** NusaMedika tidak punya tipe array di `emr_detail`.
> Legacy menyimpan `detail_emr[diagnosa_gizi][]` → beberapa baris `emr_detail`
> dengan `variabel` sama — **pola yang tidak boleh dipakai** karena
> `emrDetailByVariabel()` melakukan `pluck('value','variabel')`.
> Solusi: **satu baris, nilai dipisah koma** (`"DM tipe 2,Hematuria"`),
> dipecah kembali di view dengan `explode(',', ...)`. Ini wajib dicatat di
> `AGENTS.md`.

### 7.3 Dashboard & Form Master

```php
['dashboard_menu_sub_id' => 157, 'dashboard_menu_id' => 10, 'nama_sub_menu' => 'Diet Pasien'],
['form_id' => 43, 'nama_form' => 'Diet Pasien', 'slug' => 'diet_pasien', 'id_dash_menu' => '10.157', 'ri' => 1, 'rj' => 1, 'igd' => 1, 'mcu' => 0],
```

### 7.4 Mapping

```php
// $mapping[43]
43 => [
    'jenis_diet'          => 396,
    'jenis_diagnosa_diet' => 377,  // reuse Diagnosa Gizi, comma-separated
    'keterangan'          => 77,   // reuse
],
```

### 7.5 Akses EHR

```php
['profesi_id' => 1, 'form_id' => 43, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 2, 'form_id' => 43, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
['profesi_id' => 10, 'form_id' => 43, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
```

### 7.6 Validasi

| Field | Aturan |
|---|---|
| `jenis_diet` | `required\|string\|exists:master_diet,nama_diet` (master baru, §6.4) |
| `jenis_diagnosa_diet` | `required\|array\|min:1` → dikirim sebagai array, disimpan comma-separated |
| `keterangan` | `nullable\|string\|max:1000` |

### 7.7 Cetak

Cetak **rekap diet ruang** per tanggal (`GiziHelper::rekapDiet($bagianId, $tgl)`),
menampilkan tiap pasien + jenis diet — Trigger untuk Mp accords. Route manual.

---

## 8. Form 44 — ADIME

### 8.1 Rasional / tujuan klinis

ADIME = **Assesment – Diagnosa – Intervensi – Monitoring – Evaluasi**, kerangka
dokumentasi asuhan yang dipakai Legacy sebagai form terpisah dari asesmen gizi.
Perawat mengisi; ahli gizi yang memberikan hasil akhir.

### 8.2 Struktur Field

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `assessment` | 3 | Assessment (A) | textarea | ya | reuse |
| `diagnosa_gizi` | 377 | Diagnosa Gizi | textarea | ya | reuse |
| `intervensi` | 379 | Intervensi Gizi | textarea | ya | reuse |
| `monitor_asupan_makanan` | 380 | Monitoring & Evaluasi (MONEV) | checkbox | ya | reuse; 3 opsi berbagi objek |
| `monitor_hasil_lab` | 380 | Monitoring & Evaluasi (MONEV) | checkbox | ya | reuse |
| `monitor_berat_badan` | 380 | Monitoring & Evaluasi (MONEV) | checkbox | ya | reuse |
| `evaluasi` | 380 | Monitoring & Evaluasi (MONEV) | textarea | ya | reuse |
| `instruksi` | 5 | Instruksi (I) | textarea | ya | reuse — "Instruksi Dokter, Perawat, Dietisen, Farmasi" |
| `tanggal_asesmen` | 362 | Tanggal Asesmen Gizi | date | ya | reuse |
| `waktu_asesmen` | 363 | Waktu Asesmen Gizi | time | ya | reuse |

> **Checkbox tanpa variabel NULL.** Unlike `ews_na[]` (form 11) yang boleh
> `objek_id = NULL`, tiga `monitor_*` di sini **wajib** punya mapping ke objek 380
> supaya tetap tercatat di laporan. Nilai `on`/`off`.

### 8.3 Dashboard & Form Master

```php
['dashboard_menu_sub_id' => 158, 'dashboard_menu_id' => 10, 'nama_sub_menu' => 'ADIME'],
['form_id' => 44, 'nama_form' => 'ADIME', 'slug' => 'adime', 'id_dash_menu' => '10.158', 'ri' => 1, 'rj' => 0, 'igd' => 0, 'mcu' => 0],
```

### 8.4 Mapping

```php
// $mapping[44]
44 => [
    'assessment'            => 3,    // reuse Assessment (A)
    'diagnosa_gizi'         => 377,
    'intervensi'            => 379,
    'monitor_asupan_makanan'=> 380,
    'monitor_hasil_lab'     => 380,
    'monitor_berat_badan'   => 380,
    'evaluasi'              => 380,
    'instruksi'             => 5,    // reuse Instruksi (I)
    'tanggal_asesmen'       => 362,
    'waktu_asesmen'         => 363,
],
```

### 8.5 Akses EHR

```php
['profesi_id' => 1, 'form_id' => 44, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 2, 'form_id' => 44, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 10, 'form_id' => 44, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
```

### 8.6 Validasi

| Field | Aturan |
|---|---|
| `assessment`, `diagnosa_gizi`, `intervensi`, `evaluasi`, `instruksi` | `required\|string\|max:2000` |
| `monitor_*` (3) | `nullable\|boolean` (dikirim sebagai `'on'` / `'off'`) |
| `tanggal_asesmen` / `waktu_asesmen` | `required\|date` / `required\|date_format:H:i` |

### 8.7 Cetak

`/emr/adime/print/{emr_id}` — layout A–D–I–M–E block + tanda tangan.

---

## 9. SelectOption — key baru

```php
// app/Helpers/SelectOption.php — array $all()
'jenis_asesmen_gizi' => [
    ['value' => 'Perlu Asuhan Gizi', 'label' => 'Perlu Asuhan Gizi'],
    ['value' => 'Re-Asesmen',        'label' => 'Re-Asesmen'],
],
'ya_tidak' => [
    ['value' => 'Ada',   'label' => 'Ada'],
    ['value' => 'Tidak', 'label' => 'Tidak'],
],
'nafsu_makan' => [
    ['value' => 'Baik',     'label' => 'Baik'],
    ['value' => 'Kurang',   'label' => 'Kurang'],
    ['value' => 'Tidak Ada','label' => 'Tidak Ada'],
],
'gigi_geligi' => [
    ['value' => 'Lengkap',        'label' => 'Lengkap'],
    ['value' => 'Tidak Lengkap',  'label' => 'Tidak Lengkap'],
],
'gangguan_makan' => [
    ['value' => 'Menelan',   'label' => 'Menelan'],
    ['value' => 'Menghisap', 'label' => 'Menghisap'],
    ['value' => 'Mengunyah', 'label' => 'Mengunyah'],
],
'rekomendasi_gizi' => [
    ['value' => 'DPJP',      'label' => 'DPJP Merekomendasikan kepada Dr. Gizi Klinik'],
    ['value' => 'Ahli Gizi', 'label' => 'Ahli Gizi Merekomendasikan kepada Dr. Gizi Klinik'],
],
'instrumen_gizi' => [
    ['value' => 'SGA',      'label' => 'SGA / NRS (Subjective Global Assessment)'],
    ['value' => 'MNA-SF',   'label' => 'MNA-SF (Malnutrition Screening - Short Form)'],
    ['value' => 'MST',      'label' => 'MST (Malnutrition Screening Test) - Lansia'],
    ['value' => 'MST-Anak', 'label' => 'MST (Malnutrition Screening Test) - Anak'],
],
'jenis_pesanan_makanan' => [
    ['value' => 'Jam Antar Dari Dapur Besar',              'label' => 'Jam Antar Dari Dapur Besar'],
    ['value' => 'Jam Awal Distribusi Makan Ke Pasien',     'label' => 'Jam Awal Distribusi Makan Ke Pasien'],
    ['value' => 'Jam Selesai Makan Distribusi Ke Pasien',  'label' => 'Jam Selesai Makan Distribusi Ke Pasien'],
],
'monitoring_gizi' => [
    ['value' => 'Asupan Makanan', 'label' => 'Monitoring Asupan Makanan'],
    ['value' => 'Hasil Lab',      'label' => 'Monitoring Hasil Lab'],
    ['value' => 'Berat Badan',    'label' => 'Berat Badan'],
],
'diagnosa_khusus_dewasa' => [ /* 15 entri, §4.4 */ ],
'diagnosa_khusus_anak'   => [ /* 16 entri, §4.6 */ ],
```

`jenis_diet` **tidak** masuk `SelectOption` — butuh CRUD (lihat §10.4).

---

## 10. Implementasi

### 10.1 File yang harus dibuat

| Jenis | Path |
|---|---|
| Helper (BARU) | `app/Helpers/GiziHelper.php` |
| Controller | `app/Http/Controllers/EMR/AsesmenGizi/AsesmenGiziController.php` |
| Controller | `app/Http/Controllers/EMR/DetailNutrisi/DetailNutrisiController.php` |
| Controller | `app/Http/Controllers/EMR/RekapAsesmenNutrisi/RekapAsesmenNutrisiController.php` |
| Controller | `app/Http/Controllers/EMR/PesanMakanan/PesanMakananController.php` |
| Controller | `app/Http/Controllers/EMR/DietPasien/DietPasienController.php` |
| Controller | `app/Http/Controllers/EMR/Adime/AdimeController.php` |
| View | `resources/views/moduls/EMR/{AsesmenGizi,DetailNutrisi,RekapAsesmenNutrisi,PesanMakanan,DietPasien,Adime}/index.blade.php` |
| View cetak | `…/{AsesmenGizi,DetailNutrisi,Adime}/print.blade.php` |
| Migration | `2026_10_XX_0000XX_create_master_diet_table.php` |
| Migration | `2026_10_XX_0000XX_create_master_diagnosa_diet_table.php` |
| Model | `App\Models\MasterDiet`, `App\Models\MasterDiagnosaDiet` |
| Controller admin | `Administrator\ManajemenMaster\MasterDiet\…`, `…MasterDiagnosaDiet\…` |
| Seeder | `MasterDietSeeder`, `MasterDiagnosaDietSeeder` (20 jenis diet umum) |
| Edit | `database/seeders/EmrMasterSeeder.php` (menu 10, sub 153–158, form 39–44, objek 362–396, mapping, `backfillObjekId(39..44)`, akses) |
| Edit | `app/Helpers/SelectOption.php` |
| Edit | `routes/web.php` — route cetak non-CRUD saja |
| Edit | `AGENTS.md` |

### 10.2 Checklist seeder

- [ ] `$menus` += `['dashboard_menu_id' => 10, 'nama_menu' => 'Gizi & Nutrisi']`
- [ ] `$subMenus` += 6 baris PK 153–158 (`dashboard_menu_id = 10`, tanpa extra)
- [ ] `$forms` += 6 baris form 39–44
- [ ] `$objeks` += 362–396
- [ ] `$mapping[39..44]`
- [ ] `EmrHelper::backfillObjekId(39)` … `backfillObjekId(44)`
- [ ] `$akses` += 18 baris (Dokter / Perawat / Ahli Gizi × 6 form)
- [ ] Semua `variabel` unik per form (dicek: form 40 punya 59 baris mapping — tidak boleh ada duplikat)
- [ ] Semua `slug == Str::slug($nama_sub_menu, '_')`

### 10.3 Checklist controller & view

- [ ] `abort_unless(AksesEhr::can((int)$form_id, 'read'), 375)` di `index`
- [ ] Redirect ke riwayat terakhir bila `! create` (pola `ImplementasiKeperawatanController`)
- [ ] `$historyGrouped = EmrHelper::historyKonautenGrouped($registrasi_detail)`
- [ ] `$emr_data` dibaca **per-variabel**: `$emr_data['x'] ?? ''` — bukan `$emr_data ?? ''`
- [ ] `filteredData()` memakai `array_intersect_key($data, array_flip(EmrHelper::objekVariabels($formId)))`
- [ ] Field turunan (`bmi`, `skor`, `kesimpulan`, `status_gizi`) dihitung ulang server, nilai browser dibuang
- [ ] Radio grup dibaca **seluruh elemen**, bukan `querySelectorAll(...)[0]`
- [ ] `@error` ada di setiap field
- [ ] `{{ $isView ? 'disabled' : '' }}` di `<fieldset>`
- [ ] `old('x', $emr_data['x'] ?? '')` — bukan `$emr_data['x']` langsung
- [ ] Tanpa komponen blade di dalam `<script>` (termasuk di komentar)
- [ ] Tampil/sembunyi lewat `style.display`, bukan class `hidden` (Tailwind v4)
- [ ] `docker compose exec app ./vendor/bin/pint --dirty`

### 10.4 Master baru

`master_diet` (`master_diet_id`, `kode_diet`, `nama_diet`, `keterangan`, audit,
`status_batal`) dan `master_diagnosa_diet` (`master_diagnosa_diet_id`,
`nama_diagnosa_diet`, `keterangan`, audit, `status_batal`), dikelola di
`Administrator → Manajemen Master`, `referensi_bagian_id = 6` tidak relevan.
Sudah ada padanannya di master `Implementasi` — ikuti polanya persis
(`kode_*` unique, `Rule::unique(...)->ignore($id, '<pk>')`).

---

## 11. Catatan & Risiko

| # | Risiko / Catatan | Mitigasi |
|---|---|---|
| 1 | **Tidak ada tabel array di `emr_detail`.** Field multi-pilih (`jenis_diagnosa_diet`, `mna_diag_*`) **wajib** disimpan satu baris. Legacy memakai `detail_emr[x][]` → banyak baris dengan `variabel` sama → **data hilang** di `emrDetailByVariabel()`. | Checkbox → hidden input tunggal berisi daftar id terpilih, dipisah koma.-Javascript validate minimal 1. |
| 2 | **Satu objek untuk banyak variabel** (objek 376 → 6 gejala, 383 → 51 item instrumen, 377/379/380 dipakai 3 form). Sah secara skema (preseden objek 62), tapi saat **laporan objek** (`emrDetailByObjek`) nilai-lq menjadi ambigu. | Tambahkan kolom/label pengenal pada laporan; jangan andalkan `emrDetailByObjek` untuk field suffix. |
| 3 | **Tabel konversi SGA belum ada.** Legacy hanya menampilkan total skor tanpa kategori. implementasi buta akan menghasilkan angka tanpa makna. | `kesimpulan` SGA **tidak disimpan** sampai tim klinis menetapkan ambang. Tampilkan hanya skor. |
| 4 | **Klifikasi usia untuk pemilihan instrumen.** Legacy memakai helper `umuraja()`; NusaMedika `pasien.tgl_lahir` bertipe `date` (sudah di-migrasi). | `GiziHelper::usia()` memakai `Carbon::parse`, wajib `'Y-m-d'` (peringatan `TIMESTAMP` MySQL 1970–2038 sudah pernah menimpa). |
| 5 | **Ahli Gizi = `profesi_id = 10`, bukan 3.** Akses `akses_ehr` untuk form 41/42/43/44 merujuk `10`; ID 3 sudah dipakai Bidan dan tidak boleh ditimpa. | Profesi 10 sudah ada di `MasterPegawaiSeeder.php` (`ALOKASI_ID_GLOBAL.md` §5) — tidak perlu seeding baru. Pastikan tidak ada baris `akses_ehr` yang masih memakai `profesi_id = 3`. |
| 6 | **Objek 362–396 habis.** Form 42 Snack Pagi/Siang (2 field × 2) dan tabel WHO Z-score tidak dialokasikan. | Alokasikan objek baru **≥ 397** (band `KONSEP_FARMAKASI.md`) dan jangan memakai objek milik dokumen lain. |
| 7 | **Dapur sebagai pengguna form 42** bukan pengguna non-medis yang punya `akses_ehr` (seluruh user default berprofesi 1/2/5/13). | Tambahkan user khusus dapur dengan `pegawai.profesi_id = 10` (Ahli Gizi), lalu set akses via halaman Akses EHR. |
| 8 | **`id_dash_menu` harus identik dengan PK nyata.** Kalau sub-menu 153–158 belum ter-seed, seluruh form yatim dari dashboard (gejala: URL langsung tetap 375 karena gate, dashboard kosong). | Verifikasi: `SELECT * FROM dashboard_menu_sub WHERE dashboard_menu_id = 10;` sebelum `db:seed`. |
| 9 | `Str::slug('ADIME','_')` = `adime` ✓ tetapi `Str::studly('adime')` = `Adime` — folder **WAJIB** `Adime`, bukan `ADIME`. | Sudah dijelaskan di §3.5. |
| 10 | Form 41 & 43 adalah form "rekap/preset" — bila ditulis sebagai CRUD biasa, isinya menjadi duplikat EMR form 39/40. | Controller form 41 **hanya** membaca `EmrHelper::latestEmr()`/`latestValuesByVariabel()` untuk isi; field yang disimpan hanya `status_asesmen` + `keterangan`. |