# Konsep Asuhan Keperawatan Lanjutan (EMR)

**Status:** Rancangan / Konsep — belum diimplementasikan
**Tanggal:** 2026-10-10
**Acuan:** [`PANDUAN_IMPLEMENTASI_FORM_EMR.md`](PANDUAN_IMPLEMENTASI_FORM_EMR.md),
[`ANALISIS_KEKURANGAN_FORM.md`](ANALISIS_KEKURANGAN_FORM.md)
**Sumber legacy:** `/simrs_tenriawaru/FE/lib/modul/`

---

## 1. Ringkasan

Dokumen ini mencakup **12 form** yang menutup domain "Asuhan Keperawatan" pada
`ANALISIS_KEKURANGAN_FORM.md` §2 domain 2, 3, 4, dan 21.

| form_id | nama_form | slug | id_dash_menu | ri | rj | igd | mcu |
|---|---|---|---|---|---|---|---|
| 22 | Rencana Asuhan Keperawatan | `rencana_asuhan_keperawatan` | `2.73.26` | 1 | 0 | 0 | 0 |
| 23 | Issues Keperawatan | `issues_keperawatan` | `2.73.27` | 1 | 0 | 0 | 0 |
| 29 | Handover / Serah Terima | `handover` | `2.74.28` | 1 | 0 | 0 | 0 |
| 31 | Asesmen Kebutuhan Edukasi | `asesmen_kebutuhan_edukasi` | `2.75.29` | 1 | 1 | 0 | 0 |
| 34 | Asesmen Status Fungsional | `asesmen_status_fungsional` | `8.76.31` | 1 | 1 | 0 | 0 |
| 35 | Pengkajian Keperawatan Kritis | `pengkajian_keperawatan_kritis` | `2.75.30` | 1 | 0 | 1 | 0 |
| 54 | Catatan Keperawatan Rawat Jalan | `catatan_keperawatan_rajal` | `8.77.37` | 0 | 1 | 0 | 0 |
| 55 | Riwayat Penyakit | `riwayat_penyakit` | `8.77.35` | 1 | 1 | 1 | 0 |
| 56 | Riwayat Keluarga | `riwayat_keluarga` | `8.77.36` | 1 | 1 | 1 | 0 |
| 123 | Asesmen Akhir Kehidupan | `asesmen_akhir_kehidupan` | `8.76.33` | 1 | 0 | 1 | 0 |
| 124 | Uji Fungsi & Asesment | `uji_fungsi_asesment` | `8.76.34` | 0 | 1 | 0 | 1 |
| 125 | Respon Emosi & Status Mental | `respon_emosi` | `8.76.32` | 1 | 1 | 1 | 0 |

Objek baru yang dialokasikan: **236 – 290** (55 objek, band `236–290` menurut
[`ALOKASI_ID_GLOBAL.md`](ALOKASI_ID_GLOBAL.md) §1). Objek 1–177 yang sudah ada
di-*reuse* (tidak diduplikasi): 1–5, 13, 14, 17, 22, 23, 40, 41, 42, 43, 44, 45,
46, 47, 48, 49, 50, 51, 52, 53, 54–57, 59, 60, 61, 62, 77, 89–113, 114, 115,
137, 138, 140, 147, 155, 156.

> **Dua objek yang sebelumnya bentrok** kini masing-masing punya satu makna:
>
> | objek | `nama_objek` | Dipakai oleh |
> |---|---|---|
> | 281 | `Riwayat Perawatan Berulang` | form 55 `riwayat_perawatan_berulang` (satu-satunya) |
> | 284 | `Hubungan Keluarga` | form 56 `hubungan_keluarga` (satu-satunya) |
>
> Sebelumnya 281 dipakai ganda (`eksposisi_kritis` form 35 vs
> `riwayat_perawatan_berulang` form 55) dan 284 dipakai ganda (`catatan_kritis`
> form 35 vs `hubungan_keluarga` form 56). Karena band 236–290 sudah penuh
> (55/55) dan `ALOKASI_ID_GLOBAL.md` §1 melarang objek baru lintas dokumen,
> kedua field form 35 dipetakan ke objek 1–177 yang sudah ada:
> `eksposisi_kritis` → objek **2** (`Objective (O)`) dan `catatan_kritis` →
> objek **115** (`Catatan Keperawatan`). Kedua nama `variabel` tetap sama.

### 1.1 Struktur menu dashboard yang dipakai

```
dashboard_menu 2  "Catatan Keperawatan"
├── sub 73  "Asuhan Keperawatan"
│   ├── extra 26  "Rencana Asuhan Keperawatan"      → form 22  (2.73.26)
│   └── extra 27  "Issues Keperawatan"              → form 23  (2.73.27)
├── sub 74  "Serah Terima"
│   └── extra 28  "Handover"                        → form 29  (2.74.28)
├── sub 75  "Asesmen Klinis Keperawatan"
│   ├── extra 29  "Asesmen Kebutuhan Edukasi"      → form 31  (2.75.29)
│   └── extra 30  "Pengkajian Keperawatan Kritis"   → form 35  (2.75.30)
├── sub 19 "Balance Cairan"    → dipakai KONSEP_CAIRAN_BALANCE.md
└── sub 12 "Observasi Harian"  → sudah ada (form 13/14/15)

dashboard_menu 8  "Penilaian Klinis"   (MENU BARU)
├── sub 76  "Asesmen Pasien"
│   ├── extra 31 "Asesmen Status Fungsional"     → form 34  (8.76.31)
│   ├── extra 32 "Respon Emosi"                  → form 125 (8.76.32)
│   ├── extra 33 "Asesmen Akhir Kehidupan"      → form 123 (8.76.33)
│   └── extra 34 "Uji Fungsi Asesment"           → form 124 (8.76.34)
├── sub 77  "Anamnesis"
│   ├── extra 35 "Riwayat Penyakit"              → form 55  (8.77.35)
│   ├── extra 36 "Riwayat Keluarga"              → form 56  (8.77.36)
│   └── extra 37 "Catatan Keperawatan Rajal"     → form 54  (8.77.37)
├── sub 3  "Risiko Jatuh"  → dipakai KONSEP_RISIKO_JATUH_LANJUTAN.md
└── sub 4  "Penilaian Dekubitus" → dipakai KONSEP_RISIKO_JATUH_LANJUTAN.md
```

> Sub-menu **73–77** adalah band milik dokumen ini — `dashboard_menu_sub_id`
> bersifat **global**, jadi sub 1–12 (milik menu 1–5) **tidak boleh** dipakai di
> sini; lihat [`ALOKASI_ID_GLOBAL.md`](ALOKASI_ID_GLOBAL.md) §3.
> `dashboard_menu_sub_extra` **26–37** juga milik dokumen ini (1–5 sudah
> terpakai, 6–25 milik `KONSEP_CATATAN_MEDIS_LANJUTAN.md`); 46–65 dipakai
> dokumen cairan/nyeri/risiko jatuh.
>
> Baris sub 19 / 12 / 3 / 4 pada pohon di atas **bukan** milik dokumen ini —
> hanya ditampilkan sebagai konteks menu yang sudah ada atau dipakai dokumen lain.

---

## 2. Sumber Referensi Legacy

| Form | Berkas legacy |
|---|---|
| 22 Rencana Asuhan Keperawatan | `FE/lib/modul/rencana_asuhan_keperawatan.php`, `rak.php` (SDKI), `rak_rj.php`, `rak_ri.php` |
| 23 Issues Keperawatan | `FE/lib/modul/Masalah_cak.php` |
| 29 Handover | `FE/lib/modul/handover.php`, `handover_rawat_inap.php`, `handover_rawat_inap_act.php`, `handover_terima.php`, `handover_act.php` |
| 31 Asesmen Kebutuhan Edukasi | `FE/lib/modul/asesmen_kebutuhan_edukasi.php`, `asesmen_kebutuhan_edukasi_ulang.php` |
| 34 Asesmen Status Fungsional | `FE/lib/modul/pengkajian_awal_status_fungsional_pasien.php` |
| 35 Pengkajian Keperawatan Kritis | `FE/lib/modul/pengkajian_awal_keperawatan_kritis.php` |
| 54 Catatan Keperawatan Rawat Jalan | `FE/lib/modul/catatan_keperawatan_rajal.php` (halaman daftar anamnesis RJ) |
| 55 Riwayat Penyakit | `FE/lib/modul/riw_cak.php`, `FE/lib/func/set_default_data.php` |
| 56 Riwayat Keluarga | `FE/lib/modul/riwayat_keluarga_cak.php` |
| 123 Asesmen Akhir Kehidupan | `FE/lib/modul/assesment_pasien_akhir_kehidupan.php` (accordion), `waktu_assesmen.php` |
| 124 Uji Fungsi & Asesment | `FE/lib/modul/uji_fungsi_dan_assesment.php` |
| 125 Respon Emosi & Status Mental | `FE/lib/modul/respon_emosi_cak.php` |

---

## 3. Rencana Asuhan Keperawatan (form 22)

### Rasional

Carrier utama **Care Plan** SDKI. Perawat memilih diagnosa keperawatan dari master,
lalu sistem TML memunculkan **indikasi**, **kriteria hasil**, dan **intervensi** yang
terkait dengan diagnosa tersebut. Plan ini menjadi acuan Implementasi Keperawatan
(form 9) di hilir.

Pada legacy, tabel master penopangnya adalah:

- `diagnosa_keperawatan` — `diagnosa_keperawatan_id`, `nama_diagnosa`, `tujuan`,
  `judul_intervensi`, flag segmentasi (`diagnosa_umum` / `diagnosa_anak` /
  `diagnosa_bayi` / `diagnosa_obgyn`)
- `diagnosa_keperawatan_indikasi` — `nama_indikasi`, `hiperglikemi`
- `diagnosa_keperataan_indikasi_kriteria` — `kriteria`, `ds`, `hiperglikemi`
- `diagnosa_keperawatan_luaran` — `kode_luaran`, `nama_luaran`, `ds`, `hiperglikemi`
- `diagnosa_keperawatan_intervensi` — `kode_intervensi`, `nama_intervensi`

### Struktur Field

Satu baris per **diagnosa keperawatan** pada satu episode asuhan. Checkbox/daftar
dinormalkan menjadi **teks ringkas** (dipisah `;`) agar aman terhadap
`pluck('value','variabel')`.

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `diagnosa_keperawatan_id` | 236 | Diagnosa Keperawatan | select2 (master) | ya | Diagnosa yang dipilih; disimpan juga ke `nama_diagnosa_keperawatan` |
| `nama_diagnosa_keperawatan` | 237 | Nama Diagnosa Keperawatan | readonly (server) | ya | Snapshot nama diagnosa |
| `tujuan_keperawatan` | 238 | Tujuan Keperawatan | text + select satuan | ya | Durasi ("Setelah dilakukan tindakan keperawatan selama …") |
| `satuan_waktu_tujuan` | 239 | Satuan Waktu Tujuan | select (Menit/Jam) | ya | Legacy: RI = Jam, RJ = Menit |
| `kriteria_hasil` | 240 | Kriteria Hasil Keperawatan | textarea | ya | Hasil `diagnosa_keperawatan_luaran` terpilih, dipisah `;` |
| `intervensi_keperawatan` | 241 | Intervensi Keperawatan | textarea | ya | Hasil `diagnosa_keperawatan_intervensi` terpilih, dipisah `;` |
| `indikasi_keperawatan` | 1 | Subjective (S) | textarea | tidak | Reuse objek 1; berpasangan dengan `objektif_keperawatan` |
| `objektif_keperawatan` | 2 | Objective (O) | textarea | tidak | Reuse objek 2 |
| `catatan_rencana` | 77 | Keterangan | textarea | tidak | Reuse objek 77 |

> Kolom `kriteria_hasil` / `intervensi_keperawatan` **wajib** hanya bila baris
> diagnosa dipakai. Bila form boleh diisi tanpa diagnosa (form 23 yang menjadi
> pintu masuk masalah), dibuat `nullable`.

### Mapping

```php
22 => [
    'diagnosa_keperawatan_id'  => 236,
    'nama_diagnosa_keperawatan' => 237,   // snapshot, diisi server
    'tujuan_keperawatan'       => 238,
    'satuan_waktu_tujuan'      => 239,
    'kriteria_hasil'           => 240,
    'intervensi_keperawatan'   => 241,
    'indikasi_keperawatan'     => 1,      // reuse Subjective
    'objektif_keperawatan'     => 2,      // reuse Objective
    'catatan_rencana'          => 77,     // reuse Keterangan
],
```

### Dashboard & Form Master

```php
// dashboard_menu_sub
['dashboard_menu_sub_id' => 73, 'dashboard_menu_id' => 2, 'nama_sub_menu' => 'Asuhan Keperawatan'],
// dashboard_menu_sub_extra
['dashboard_menu_sub_extra_id' => 26, 'dashboard_menu_sub_id' => 73, 'nama_sub_menu_extra' => 'Rencana Asuhan Keperawatan'],
['dashboard_menu_sub_extra_id' => 27, 'dashboard_menu_sub_id' => 73, 'nama_sub_menu_extra' => 'Issues Keperawatan'],

// form
['form_id' => 22, 'nama_form' => 'Rencana Asuhan Keperawatan', 'slug' => 'rencana_asuhan_keperawatan', 'id_dash_menu' => '2.73.26', 'ri' => 1, 'rj' => 0, 'igd' => 0, 'mcu' => 0],
```

### Akses EHR

```php
// form 22 — Care Plan, khusus perawat; dokter read-only untukjp konsiliasi
['profesi_id' => 1, 'form_id' => 22, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
['profesi_id' => 2, 'form_id' => 22, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
```

### Validasi

```php
'diagnosa_keperawatan_id' => 'required|integer|exists:diagnosa_keperawatan,diagnosa_keperawatan_id',
'tujuan_keperawatan'       => 'required|numeric|min:1',
'satuan_waktu_tujuan'      => 'required|in:Menit,Jam',
'kriteria_hasil'           => 'nullable|string|max:2000',
'intervensi_keperawatan'   => 'nullable|string|max:2000',
'catatan_rencana'          => 'nullable|string|max:1000',
```

### Cetak

`print.blade.php` **wajib** — plan asuhan adalah dokumen yang wajib ada di Berkas
Pasien Rawat Inap. Route `/emr/rencana_asuhan_keperawatan/print/{emr_id}`.
Tampilkan tabel 3 kolom (Diagnosa | Tujuan & Kriteria Hasil | Intervensi) seperti
legacy, plus kop RSP.

---

## 4. Issues / Masalah Keperawatan (form 23)

### Rasional

Form "pendahulu" dari care plan. Pada legacy (`Masalah_cak.php`) bentuknya berbeda
berdasarkan jenis rawat:

- **RJ / RI**: satu `textarea` bebas (`masalah_keperawatan`, objek 242).
- **IGD**: dua dropdown berpasangan — **Masalah** (dari `diagnosa_keperawatan`
  dengan `diagnosa_umum = 1`) dan **Faktor** (dari `diagnosa_keperawatan` dengan
  `flag_hubungan = 1`), digabung menjadi label
  `"<masalah> berhubungan dengan <faktor>"`; kombinasi ganda ditolak.

NusaMedika menyederhanakan: **satu baris per masalah** (tabel/grid yang bisa tambah
baris di klien), dengan kolom masalah + faktor. Jumlah baris dibatasi 10 lewat
sufiks angka.

### Struktur Field

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `masalah_keperawatan_1` … `_10` | 242 | Masalah Keperawatan | select2 + input teks | ya (baris 1) | Diagnosa keperawatan chosen / free text |
| `faktor_berhubungan_1` … `_10` | 243 | Faktor Berhubungan Dengan | select2 | tidak | Sumber `flag_hubungan = 1` |

### Mapping

```php
23 => [
    'masalah_keperawatan_1' => 242, 'faktor_berhubungan_1' => 243,
    'masalah_keperawatan_2' => 242, 'faktor_berhubungan_2' => 243,
    'masalah_keperawatan_3' => 242, 'faktor_berhubungan_3' => 243,
    'masalah_keperawatan_4' => 242, 'faktor_berhubungan_4' => 243,
    'masalah_keperawatan_5' => 242, 'faktor_berhubungan_5' => 243,
    'masalah_keperawatan_6' => 242, 'faktor_berhubungan_6' => 243,
    'masalah_keperawatan_7' => 242, 'faktor_berhubungan_7' => 243,
    'masalah_keperawatan_8' => 242, 'faktor_berhubungan_8' => 243,
    'masalah_keperawatan_9' => 242, 'faktor_berhubungan_9' => 243,
    'masalah_keperawatan_10' => 242, 'faktor_berhubungan_10' => 243,
],
```

> Mapping ini **dihasilkan dalam loop** di seeder, bukan ditulis literal:
> ```php
> for ($i = 1; $i <= 10; $i++) {
>     $mappingMasalah['masalah_keperawatan_'.$i] = 242;
>     $mappingMasalah['faktor_berhubungan_'.$i] = 243;
> }
> $mapping[23] = $mappingMasalah;
> ```

### Dashboard & Form Master

```php
['dashboard_menu_sub_extra_id' => 27, 'dashboard_menu_sub_id' => 73, 'nama_sub_menu_extra' => 'Issues Keperawatan'],
['form_id' => 23, 'nama_form' => 'Issues Keperawatan', 'slug' => 'issues_keperawatan', 'id_dash_menu' => '2.73.27', 'ri' => 1, 'rj' => 0, 'igd' => 0, 'mcu' => 0],
```

### Akses EHR

```php
['profesi_id' => 1, 'form_id' => 23, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
['profesi_id' => 2, 'form_id' => 23, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
```

### Validasi

```php
'masalah_keperawatan_1' => 'required|string|max:255',
'faktor_berhubungan_1' => 'nullable|string|max:255',
// ... baris 2-10 nullable, diverifikasi manual di filteredData() bahwa
// masalah_N kosong => faktor_N juga dibuang (baris yatim).
```

`filteredData()` **wajib** membuang pasangan yatim: bila `masalah_keperawatan_N`
kosong maka `faktor_berhubungan_N` dihapus dari payload — bukan sekadar tidak
dikirim — supaya tidak ada `emr_detail` setengah terisi.

### Cetak

Tidak wajib (form intermediate). Sertakan di daftar regimen sebagai referensi.

---

## 5. Handover / Serah Terima (form 29)

### Rasional

Serah terima antar shift/antar petugas. Legacy 
**SBAR**: `handover_rawat_inap_act.php` mengambil `emr` terakhir dengan
`form_id = form_id_soap` dan probation membaca objek 1/2/3/5 sebagai S/B/A/R,
lalu menambahkan `handover_terima` (objek 1346) berisi `user_id` penerima.

NusaMedika: form mandiri (bukan report) berisi **isi serah terima** + jejak audit
penerima. Kolom `isi_serah_terima` di-*prefill* otomatis dari SBAR terakhir
(form 12) bila ada — **hanya teks prefill, tetap bisa diedit**, persis seperti
pola prefill S/B pada `SbarController`.

### Struktur Field

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `tanggal_serah_terima` | 244 | Tanggal Serah Terima | date | ya | Wajib manual, tanpa default |
| `waktu_serah_terima` | 245 | Waktu Serah Terima | time (`H:i`) | ya | Wajib manual |
| `shift` | 147 | Shift | select (Pagi/Siang/Sore/Malam) | ya | Reuse objek 147 |
| `jenis_serah_terima` | 40 | Diagnosa Medis | select | ya | Reuse objek 40 — status vital (A: Aktif / B: Ressah / C: Kritis / D: Meninggal) |
| `petugas_serah_id` | 246 | Petugas Serah Terima | select pegawai | ya | Dari `pegawai` (bisa perawat) |
| `nama_petugas_serah` | 247 | Nama Petugas Serah Terima | readonly (server) | ya | Snapshot |
| `petugas_terima_id` | 248 | Petugas Terima | select pegawai | ya | Dari `pegawai` |
| `nama_petugas_terima` | 249 | Nama Petugas Terima | readonly (server) | ya | Snapshot |
| `ruang_tujuan` | 250 | Ruang Tujuan Serah Terima | select (`bagian`) | ya | Wajib bila jenis serah terima = Transfer Ruangan |
| `waktu_konfirmasi_terima` | 251 | Waktu Konfirmasi Penerima | time (`H:i`) | ya | Legacy `handover_terima.php` — jejak pengakuan penerima |
| `jumlah_pasien_serah_terima` | 252 | Jumlah Pasien Diserahterimakan | number | ya | Legacy `handover_rawat_inap.php` menampilkan daftar per ruang |
| `status_kelengkapan_serah_terima` | 253 | Status Kelengkapan Serah Terima | radio | ya | Selesai / Belum Lengkap |
| `isi_serah_terima` | 2 | Objective (O) | textarea | ya | Reuse objek 2; prefill dari SBAR terakhir |
| `catatan_serah_terima` | 77 | Keterangan | textarea | tidak | Reuse objek 77 |

> Objek 1/2/3/4 (S/O/A/P) tidak dipakai di sini karena bentrok semantik dengan
> SOAP; cukup satu blok `isi_serah_terima`.

### Mapping

```php
29 => [
    'tanggal_serah_terima' => 244,
    'waktu_serah_terima'  => 245,
    'shift'                => 147,  // reuse objek SBAR
    'jenis_serah_terima'   => 40,   // reuse Diagnosa Medis (status)
    'petugas_serah_id'     => 246,
    'nama_petugas_serah'   => 247,
    'petugas_terima_id'    => 248,
    'nama_petugas_terima'  => 249,
    'ruang_tujuan'         => 250,
    'waktu_konfirmasi_terima' => 251,
    'jumlah_pasien_serah_terima' => 252,
    'status_kelengkapan_serah_terima' => 253,
    'isi_serah_terima'     => 2,    // reuse Objective
    'catatan_serah_terima' => 77,   // reuse Keterangan
],
```

### Dashboard & Form Master

```php
['dashboard_menu_sub_id' => 74, 'dashboard_menu_id' => 2, 'nama_sub_menu' => 'Serah Terima'],
['dashboard_menu_sub_extra_id' => 28, 'dashboard_menu_sub_id' => 74, 'nama_sub_menu_extra' => 'Handover'],
['form_id' => 29, 'nama_form' => 'Handover / Serah Terima', 'slug' => 'handover', 'id_dash_menu' => '2.74.28', 'ri' => 1, 'rj' => 0, 'igd' => 0, 'mcu' => 0],
```

### Akses EHR

```php
['profesi_id' => 1, 'form_id' => 29, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
['profesi_id' => 2, 'form_id' => 29, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 1],
```

> `akses_update = 0`: berita serah terima bersifat **append-only** (immutable).
> Kesalahan isi diperbaiki dengan menambah entri serah terima baru, bukan
> mengedit riwayat — ini juga aturan yang diminta akreditasi.

### Validasi

```php
'tanggal_serah_terima' => 'required|date',
'waktu_serah_terima'  => 'required|date_format:H:i',
'shift'                => 'required|in:Pagi,Siang,Sore,Malam',
'jenis_serah_terima'   => 'required|in:A,B,C,D',
'petugas_serah_id'     => 'required|integer|exists:pegawai,pegawai_id',
'petugas_terima_id'    => 'required|integer|exists:pegawai,pegawai_id',
'ruang_tujuan'         => 'nullable|required_if:jenis_serah_terima,B|integer|exists:bagian,bagian_id',
'waktu_konfirmasi_terima' => 'required|date_format:H:i',
'jumlah_pasien_serah_terima' => 'required|integer|min:1|max:200',
'status_kelengkapan_serah_terima' => 'required|in:Selesai,Belum Lengkap',
'isi_serah_terima'     => 'required|string|max:4000',
```

> `jenis_serah_terima` memakai objek 40 (`Diagnosa Medis`) dengan nilai
> `A` = antar shift, `B` = transfer ruangan, `C` = consul, `D` = Mortality review.
> Bila skema lokal memilih label lain, sesuaikan aturan `required_if`.

Tambahan (di luar `validate()`): `petugas_serah_id !== petugas_terima_id` → error
"Petugas serah dan penerima tidak boleh sama" (kecuali mode self-handover yang
diizinkan lewat flag `?self_handover=1`).

### Cetak

`print.blade.php` **wajib** — ditempel di papan serah terima setiap ruang.

---

## 6. Asesmen Kebutuhan Edukasi (form 31)

### Rasional

Menentukan apakah pasien mampu belajar dan menerima edukasi. Legacy
(`asesmen_kebutuhan_edukasi.php`, form_id 7) punya blok:

| Blok legacy | Opsi |
|---|---|
| `tingkat_pendidikan` | TK, SD, SMP, SMA, PT, Tidak Sekolah |
| `bicara` | Normal / Tidak (butuh penerjemah) |
| `bahasa` | Perlu Translater / Tidak Perlu Translater |
| `jenis_bahasa` | teks bebas |
| `nilai_kebudayaan` | Ada / Tidak Ada |
| `nilai_kebudayaannya` | teks bebas (nilai kebudayaan yang perlu dihormati) |
| `kemampuan_baca` | Ada / Tidak Ada |
| `hambatan_belajar` | Bahasa, Hilang Memori, Gangguan Penglihatan, Pendengaran, Motivasi Buruk, Gangguan Kognitif, Kesulitan Bicara, Tidak Ada, Lainnya |
| `kebutuhan_belajar` | Proses Penyakit, Pengobatan, Obat/Terapi, Manajemen Nyeri, Exercise, Perawatan, Nutrisi, Tindakan |
| `respon_emosi` | Tenang, Marah, Rendah Diri, Gelisah, Takut, Sedih, Menangis, Mudah Tersinggung, Cemas |
| `sedia_terima_edukasi` | Bersedia / Tidak |

Form `asesmen_kebutuhan_edukasi_ulang.php` menambahkan alasan asesmen ulang
(tindakan baru / terapi baru / perubahan kondisi / perubahan DPJP) dan materi yang
sudah diberikan.

### Struktur Field

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `tingkat_pendidikan` | 23 | Tingkat Pendidikan | radio | ya | Reuse objek 23; opsi TK/SD/SMP/SMA/PT/Tidak Sekolah |
| `kemampuan_bicara` | 254 | Kemampuan Bicara | radio (Normal/Tidak) | ya | "Tidak" memunculkan field penerjemah |
| `jenis_bahasa` | 255 | Jenis Bahasa | text | ya | Bahasa yang dipakai pasien |
| `perlu_penerjemah` | 256 | Perlu Penerjemah | radio (Perlu/Tidak Perlu) | ya | Reuse opsi legacy `bahasa` |
| `nilai_kebudayaan` | 257 | Nilai Kebudayaan | radio (Ada/Tidak Ada) | ya | |
| `keterangan_kebudayaan` | 258 | Keterangan Kebudayaan | textarea | ya bila Ada | Wajib bila `nilai_kebudayaan = Ada` |
| `kemampuan_baca` | 260 | Kemampuan Baca | radio (Ada/Tidak Ada) | ya | Reuse objek 260 |
| `hambatan_belajar` | 259 | Hambatan Belajar | checkbox group | ya | Minimal 1; `Lainnya` membuka text |
| `kebutuhan_belajar` | 261 | Kebutuhan Belajar | checkbox group | ya | Minimal 1 |
| `sedia_terima_edukasi` | 40 | Diagnosa Medis | radio (Bersedia/Tidak) | ya | **Reuse objek 40** — label field "Sedia Terima Edukasi" |
| `alasan_asesmen_ulang` | 77 | Keterangan | checkbox group | tidak | Reuse objek 77 |
| `materi_edukasi` | 114 | Keluhan Utama Harian | textarea | tidak | Reuse objek 114 — materi yang sudah diberikan |
| `respon_emosi_edukasi` | 3 | Assessment (A) | checkbox group | tidak | Reuse objek 3 |

### Mapping

```php
31 => [
    'tingkat_pendidikan'      => 23,   // reuse
    'kemampuan_bicara'       => 254,
    'jenis_bahasa'            => 255,
    'perlu_penerjemah'        => 256,
    'nilai_kebudayaan'        => 257,
    'keterangan_kebudayaan'   => 258,
    'kemampuan_baca'          => 260,
    'hambatan_belajar'        => 259,
    'kebutuhan_belajar'      => 261,
    'sedia_terima_edukasi'    => 40,   // reuse
    'alasan_asesmen_ulang'    => 77,   // reuse
    'materi_edukasi'          => 114,  // reuse
    'respon_emosi_edukasi'    => 3,    // reuse
],
```

> Reuse objek 3/40/77/114 untuk konten non-dedikasi sengaja: menambah objek baru
> untuk daftar generic akan menaikkan `objek` tanpa makna semantik dan membuat
> laporan lintas formelongasi. Objek khusus (254–261) hanya untuk konten yang
> benar-benar unik (bicara/bahasa/budaya/hambatan/kebutuhan belajar).

### Dashboard & Form Master

```php
['dashboard_menu_sub_id' => 75, 'dashboard_menu_id' => 2, 'nama_sub_menu' => 'Asesmen Klinis Keperawatan'],
['dashboard_menu_sub_extra_id' => 29, 'dashboard_menu_sub_id' => 75, 'nama_sub_menu_extra' => 'Asesmen Kebutuhan Edukasi'],
['form_id' => 31, 'nama_form' => 'Asesmen Kebutuhan Edukasi', 'slug' => 'asesmen_kebutuhan_edukasi', 'id_dash_menu' => '2.75.29', 'ri' => 1, 'rj' => 1, 'igd' => 0, 'mcu' => 0],
```

### Akses EHR

```php
['profesi_id' => 1, 'form_id' => 31, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 0],
['profesi_id' => 2, 'form_id' => 31, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 0],
```

> `akses_delete = 0` untuk kedua profesi: asesmen adalah bagian dari rekam medis
> yang tidak boleh dihapus.

### Validasi

```php
'tingkat_pendidikan'    => 'required|in:TK,SD,SMP,SMA,PT,Tidak Sekolah',
'kemampuan_bicara'     => 'required|in:Normal,Tidak',
'jenis_bahasa'          => 'required|string|max:100',
'perlu_penerjemah'      => 'required|in:Perlu Translater,Tidak Perlu Translater',
'nilai_kebudayaan'      => 'required|in:ada,tidak_ada',
'keterangan_kebudayaan' => 'nullable|required_if:nilai_kebudayaan,ada|string|max:500',
'kemampuan_baca'        => 'required|in:ada,tidak_ada',
'hambatan_belajar'      => 'required|array|min:1',
'hambatan_belajar.*'    => 'string',
'kebutuhan_belajar'    => 'required|array|min:1',
'kebutuhan_belajar.*'  => 'string',
'sedia_terima_edukasi'  => 'required|in:Bersedia,Tidak',
```

Checkbox group disimpan sebagai **JSON array** (`json_encode`) pada satu
`emr_detail.value` (kolom `value` bertipe `TEXT`).

### Cetak

`print.blade.php` **wajib** — lembarstoodience.edukasi ditandatangani pasien/
keluarga dan ditempel di rekam medis.

---

## 7. Asesmen Status Fungsional (form 34)

### Rasional

Menilai kemampuan ADL *(Activities of Daily Living)*. **PENTING:** pada legacy,
`pengkajian_awal_status_fungsional_pasien.php` **identik** dengan Barthel Index —
10 item dengan skor **maksimum 20** dan ambang 20 / 12–19 / 9–11 / 5–8 / 0–4.
`barthel_index.php` (form 33) memakai tabel **operasional Barthel** yang berbeda
nilai (lihat §13). Keduanya sengaja tetap dipisah di NusaMedika karena:

- form 33 = Barthel Index baku (10 item, skor maks 20) → dipakai untuk **rehabilitasi & discharge planning**
- form 34 = asesmen fungsional admission yang berbeda wording dan ambang, dilengkapi
  **Morse Fall Scale checkbox** yang juga dijumlahkan (lihat legacy `jml()`).

### Struktur Field

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `sf_makan` | 262 | Fungsional Makan (Feeding) | radio 0/1/2 | ya | |
| `sf_mandi` | 263 | Fungsional Mandi (Bathing) | radio 0/1 | ya | |
| `sf_grooming` | 264 | Fungsional Perawatan Diri (Grooming) | radio 0/1 | ya | Legacy menamai variabel `tujuan_keperawatan` — disamakan |
| `sf_berpakaian` | 265 | Fungsional Berpakaian | radio 0/1/2 | ya | |
| `sf_buang_air_kecil` | 266 | Fungsional Buang Air Kecil | radio 0/1/2 | ya | Legacy: "Bowel" (terbalik) — **dibetulkan** |
| `sf_buang_air_besar` | 267 | Fungsional Buang Air Besar | radio 0/1/2 | ya | Legacy: "Bladder" (terbalik) — **dibetulkan** |
| `sf_toilet` | 268 | Fungsional Penggunaan Toilet | radio 0/1/2 | ya | |
| `sf_transfer` | 269 | Fungsional Transfer | radio 0/1/2/3 | ya | |
| `sf_mobilitas` | 270 | Fungsional Mobilitas | radio 0/1/2/3 | ya | |
| `sf_tangga` | 271 | Fungsional Naik Turun Tangga | radio 0/1/2 | ya | |
| `sf_skor` | 272 | Skor Status Fungsional | readonly (server) | ya | **Turunan** |
| `sf_interpretasi` | 273 | Interpretasi Status Fungsional | readonly (server) | ya | **Turunan** |

### Kriteria Penilaian (rekap)

| Item | 0 | 1 | 2 | 3 |
|---|---|---|---|---|
| Makan (Feeding) | Tidak mampu | Butuh bantuan memotong, mengoles mentega | Mandiri | — |
| Mandi (Bathing) | Tergantung orang lain | Mandiri | — | — |
| Perawatan Diri (Grooming) | Butuh bantuan orang lain | Mandiri dalam perawatan muka, rambut gigi, bercukur | — | — |
| Berpakaian | Tergantung orang lain | Sebagian dibantu (mis. mengancing baju) | Mandiri | — |
| Buang Air Kecil | Inkontinensia / pakai kateter tidak terkontrol | Kadang inkontinensia (maks 1×/24 jam) | Kontinensia (teratur > 7 hari) | — |
| Buang Air Besar | Inkontinensia / tidak teratur / perlu enema | Kadang inkontinensia (sekali seminggu) | Kontinensia (teratur) | — |
| Penggunaan Toilet | Tergantung bantuan orang lain | Butuh bantuan, tapi dapat melakukan sebagian sendiri | Mandiri | — |
| Transfer | Tidak mampu | Butuh bantuan untuk bisa duduk (2 orang) | Bantuan kecil (1 orang) | Mandiri |
| Mobilitas | Immobile (tidak mampu) | Menggunakan kursi roda | Berjalan dengan bantuan 1 orang | Mandiri (meski memakai tongkat) |
| Naik Turun Tangga | Tidak mampu | Membutuhkan bantuan (alat bantu) | Mandiri | — |

**Skor maksimum = 20** (2+1+1+2+2+2+2+3+3+2).

### Ambang Interpretasi

| Total Skor | Interpretasi | Warna |
|---|---|---|
| 20 | Mandiri | hijau |
| 12–19 | Ketergantungan Ringan | biru |
| 9–11 | Ketergantungan Sedang | abu-abu |
| 5–8 | Ketergantungan Berat | kuning |
| 0–4 | Ketergantungan Total | merah |

### Perhitungan Server-Side

Semua nilai browser untuk `sf_skor` dan `sf_interpretasi` **diabaikan** dan
dihitung ulang di `filteredData()`:

```php
$item = ['sf_makan','sf_mandi','sf_grooming','sf_berpakaian','sf_buang_air_kecil',
         'sf_buang_air_besar','sf_toilet','sf_transfer','sf_mobilitas','sf_tangga'];
$total = 0; $terisi = 0;
foreach ($item as $v) {
    $n = $data[$v] ?? null;
    if ($n === null || $n === '') { continue; }
    $total += (int) $n; $terisi++;
}
$data['sf_skor']         = $terisi === count($item) ? $total : '';
$data['sf_interpretasi'] = match (true) {
    $terisi < count($item) => '',
    $total === 20 => 'Mandiri',
    $total >= 12  => 'Ketergantungan Ringan',
    $total >= 9   => 'Ketergantungan Sedang',
    $total >= 5   => 'Ketergantungan Berat',
    default        => 'Ketergantungan Total',
};
```

**Kategori hanya disimpan bila 10/10 item terisi** — persis aturan yang dipakai
`EwsHelper` untuk EWS parsial.

### Mapping

```php
34 => [
    'sf_makan' => 262, 'sf_mandi' => 263, 'sf_grooming' => 264, 'sf_berpakaian' => 265,
    'sf_buang_air_kecil' => 266, 'sf_buang_air_besar' => 267, 'sf_toilet' => 268,
    'sf_transfer' => 269, 'sf_mobilitas' => 270, 'sf_tangga' => 271,
    'sf_skor' => 272, 'sf_interpretasi' => 273,
],
```

### Dashboard & Form Master

```php
// dashboard_menu BARU
['dashboard_menu_id' => 8, 'nama_menu' => 'Penilaian Klinis'],
['dashboard_menu_sub_id' => 76, 'dashboard_menu_id' => 8, 'nama_sub_menu' => 'Asesmen Pasien'],
['dashboard_menu_sub_extra_id' => 31, 'dashboard_menu_sub_id' => 76, 'nama_sub_menu_extra' => 'Asesmen Status Fungsional'],
['form_id' => 34, 'nama_form' => 'Asesmen Status Fungsional', 'slug' => 'asesmen_status_fungsional', 'id_dash_menu' => '8.76.31', 'ri' => 1, 'rj' => 1, 'igd' => 0, 'mcu' => 0],
```

### Akses EHR

```php
['profesi_id' => 1, 'form_id' => 34, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 0],
['profesi_id' => 2, 'form_id' => 34, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 0],
```

### Validasi

```php
'sf_makan'             => 'required|in:0,1,2',
'sf_mandi'             => 'required|in:0,1',
'sf_grooming'          => 'required|in:0,1',
'sf_berpakaian'        => 'required|in:0,1,2',
'sf_buang_air_kecil'   => 'required|in:0,1,2',
'sf_buang_air_besar'   => 'required|in:0,1,2',
'sf_toilet'            => 'required|in:0,1,2',
'sf_transfer'          => 'required|in:0,1,2,3',
'sf_mobilitas'         => 'required|in:0,1,2,3',
'sf_tangga'            => 'required|in:0,1,2',
```

### Cetak

`print.blade.php` **wajib** — dipakai pada discharge planning & rujukan rehabilitasi.

---

## 8. Pengkajian Keperawatan Kritis (form 35)

### Rasional

Pengkajian thorough menggunakan pendekatan **ABCDE** seperti legacy. Legacy
`pengkajian_awal_keperawatan_kritis.php` (1721 baris) memuat:

| Section | Field legacy (objek) | Opsi |
|---|---|---|
| Masuk | `asal_masuk` (168) | IGD, Kamar Operasi, Ruangan, Lainnya + `asal_masuk_detail` |
| | `diagnosa_medis` (224) | dari `diagnosa_rawat` |
| **A — Airway** | `jalan_nafas` (25) | Tanpa Alat, OPA, ETT/TT |
| | `abdomen` (abdomen) | field pengukur OPA: 1–6 dan −1..4 |
| | `deviasi_trakea` | Ya/Tidak |
| | `bising_usus`/`posisi` | Tidak Produktif / Produktif |
| **B — Breathing** | `pernapasan` | Spontan / Dibantu alat |
| | `suara_nafas_terdengar` | Vesikuler, Wheezing, Ronchi, Tridor |
| | `agd` | pH, PaCO2, HCO3, BE, PaO2, Lainnya |
| | `oksigen` | Tidak terpasang IV Line / Terpasang IV Line |
| **C — Circulation** | `sistolik` / `diastolik` | angka |
| | `kekuatan_nadi` | Simetris / Asimetris |
| | `perdarahan` | Tidak Ada / Ada |
| | `wsd` | Tidak Ada / Terpasang WSD |
| | `elektrokardiografi` | Ya/Tidak |
| **D — Disability** | `kesadaran` | CM, Somnolen, Apatis, Soporus, Koma |
| | GCS `E/M/V` | Numerik 1–6 / 1–6 / 1–5 |
| | `pupil_kiri`/`pupil_kanan` | Kanan / Kiri |
| | `skala_nyeri` | VAS (0–10) atau FLACC (total 0–10) |
| **E — Exposure** | `konjungtiva` | Tidak Anemis / Anemis |
| | `mulut`/`mukosa_mulut` | Lembab / Kering / Lainnya |
| | `abdomen_txt` | Supel, Massa, Kolostomi, Distensi, Striae, Asites, Lainnya |
| | `kulit` | Turgor Kulit / Integritas Kulit |
| | `diit` (nutrisi) | Oral, Parenteral, TPN, Lainnya |
| | `data_image` (161) | canvas arsir lokasi |
| **Lain-lain** | `catatan` (458) | free text |
| **Masalah Keperawatan** | `diagnosa_keperawatan` | dropdown (reuse master form 22) |

### Struktur Field

Fokus form 35 pada **ringkasan ABCDE** yang belum tertangkap oleh Tanda Vital
(form 13) dan Tanda Vital–kritis. Field yang sudah ada di objek lain di-*reuse*.

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `asal_masuk` | 274 | Asal Masuk Pasien | radio | ya | IGD / Kamar Operasi / Ruangan / Lainnya |
| `asal_masuk_detail` | 275 | Rincian Asal Masuk | text | ya bila Lainnya | |
| `diagnosa_medis_kritis` | 40 | Diagnosa Medis | select (ICD) | ya | Reuse objek 40 |
| `jalan_nafas` | 276 | Jalan Nafas | radio | ya | Tanpa Alat / OPA / ETT/TT |
| `deviasi_trakea` | 277 | Deviasi Trakea | radio (Ya/Tidak) | ya | |
| `pernapasan_kritis` | 12 | Pernapasan | radio | ya | Reuse objek 12 (Spontan/Dibantu alat) |
| `suara_nafas_terdengar` | 278 | Suara Napas Terdengar | multi-select | ya | Vesikuler / Wheezing / Ronchi / Tridor |
| `sirkulasi_perfusi` | 279 | Sirkulasi dan Perfusi | radio | ya | Al Adequat / Gangguan Perfusi / Syok |
| `kesadaran_kritis` | 51 | Kesadaran | radio | ya | Reuse objek 51 (CM…Koma) |
| `gcs_e` / `gcs_m` / `gcs_v` | 54 / 55 / 56 | GCS Eye/Motorik/Verbal | radio | ya | Reuse objek GCS |
| `gcs_jumlah` | 57 | GCS Score | readonly (server) | ya | Reuse objek 57 |
| `skor_nyeri_kritis` | 282 | Skor Nyeri Kritis | readonly (server) | ya | **Turunan** VAS/FLACC, lihat §14 |
| `nilai_kritis` | 280 | Nilai Kritis | select | ya | Normal / Abnormal |
| `eksposisi_kritis` | 2 | Objective (O) | textarea | ya | Reuse objek 2 — Findings objektif E (konjungtiva, mukosa, kulit, abdomen) diringkas |
| `catatan_kritis` | 115 | Catatan Keperawatan | textarea | tidak | Reuse objek 115 — narasi kritis tim anestesi/perawat |
| `masalah_keperawatan_kritis` | 242 | Masalah Keperawatan | text | ya | Reuse objek 242 dari form 23 |
| `dpo` | 59 | DPO | checkbox | tidak | Reuse objek 59 |
| `alergi` | 17 | Alergi | textarea | tidak | Reuse objek 17 |

> Kesadaran kritis memakai objek 51 yang sudah ada; `SelectOption` key
> `kesadaran` sudah memuat CM/Somnolen/Apatis/Soporus/Koma.

### Kriteria Penilaian FLACC (dipakai untuk `skor_nyeri_kritis`)

| Skor | Wajah (Face) | Kaki (Legs) | Aktivitas (Activity) | Menangis (Cry) | Konsolabilitas |
|---|---|---|---|---|---|
| 0 | Tersenyum / tidak ada ekspresi khusus | Gerakan normal / relaksasi | Tidur, posisi normal, mudah bergerak | Tidak menangis (bangun/tidur) | Rileks |
| 1 | Terkadang menangis / menarik diri | Tidak tenang / tegang | Gelonganmenggeliat, berguling, kaku | Mengerang / merengek | Tenang bila dipeluk / diajak bicara |
| 2 | Sering menggetarkan dagu / mengatupkan rahang | Kaki dibuat menendang / menarik diri | Melengkungkan punggung / kaku / menghentak | Menangis terus-menerus, terhisak, menjerit | Sulit untuk menenangkan |

**Total 0–10.** Interpretasi: 0 = Tidak Nyeri · 1–3 = Nyeri Ringan · 4–6 = Nyeri Sedang · 7–10 = Nyeri Berat.

### Perhitungan Server-Side

```php
// gcs_jumlah = gcs_e + gcs_m + gcs_v (nilai browser diabaikan)
$e = (int) ($data['gcs_e'] ?? 0); $m = (int) ($data['gcs_m'] ?? 0); $v = (int) ($data['gcs_v'] ?? 0);
$data['gcs_jumlah'] = ($e && $m && $v) ? $e + $m + $v : '';

// skor_nyeri_kritis
$data['skor_nyeri_kritis'] = match ($data['metode_skala_nyeri_kritis'] ?? 'VAS') {
    'FLACC' => array_sum(array_map(fn ($k) => (int) ($data[$k] ?? 0),
        ['flacc_wajah','flacc_kaki','flacc_aktivitas','flacc_menangis','flacc_konsolabilitas'])),
    default => $data['skor_vas_kritis'] ?? '',
};
```

`metode_skala_nyeri_kritis`, `skor_vas_kritis`, `flacc_*` **tidak punya objek**
(kolom `NULL` di `objek_form_control`, lihat PANDUAN §2.6) karena nilainya sudah
terwakili oleh `skor_nyeri_kritis` (objek 282) dan objek 140.

### Mapping

```php
35 => [
    'asal_masuk'            => 274,
    'asal_masuk_detail'     => 275,
    'diagnosa_medis_kritis' => 40,   // reuse
    'jalan_nafas'           => 276,
    'deviasi_trakea'        => 277,
    'pernapasan_kritis'     => 12,   // reuse
    'suara_nafas_terdengar' => 278,
    'sirkulasi_perfusi'     => 279,
    'kesadaran_kritis'      => 51,   // reuse
    'gcs_e'                 => 54, 'gcs_m' => 55, 'gcs_v' => 56, 'gcs_jumlah' => 57, // reuse
    'skor_nyeri_kritis'     => 282,
    'nilai_kritis'          => 280,
    'eksposisi_kritis'      => 2,    // reuse Objective (O)
    'catatan_kritis'        => 115,  // reuse Catatan Keperawatan
    'masalah_keperawatan_kritis' => 242, // reuse objek form 23
    'dpo'                   => 59,   // reuse
    'alergi'                => 17,   // reuse
],
```

### Dashboard & Form Master

```php
['dashboard_menu_sub_extra_id' => 30, 'dashboard_menu_sub_id' => 75, 'nama_sub_menu_extra' => 'Pengkajian Keperawatan Kritis'],
['form_id' => 35, 'nama_form' => 'Pengkajian Keperawatan Kritis', 'slug' => 'pengkajian_keperawatan_kritis', 'id_dash_menu' => '2.75.30', 'ri' => 1, 'rj' => 0, 'igd' => 1, 'mcu' => 0],
```

### Akses EHR

```php
['profesi_id' => 1, 'form_id' => 35, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 0],
['profesi_id' => 2, 'form_id' => 35, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 0],
```

### Validasi

```php
'asal_masuk'            => 'required|in:IGD,Kamar Operasi,Ruangan,Lainnya',
'asal_masuk_detail'     => 'nullable|required_if:asal_masuk,Lainnya|string|max:200',
'diagnosa_medis_kritis' => 'required|string|max:255',
'jalan_nafas'           => 'required|in:Tanpa Alat,OPA,ETT/TT',
'deviasi_trakea'        => 'required|in:Ya,Tidak',
'pernapasan_kritis'     => 'required|in:Spontan,Dibantu alat',
'suara_nafas_terdengar' => 'required|array|min:1',
'sirkulasi_perfusi'     => 'required|in:Al Adequat,Gangguan Perfusi,Shock',
'kesadaran_kritis'      => 'required|in:CM,Somnolen,Apatis,Soporus,Koma',
'gcs_e'                 => 'required|integer|between:1,4',
'gcs_m'                 => 'required|integer|between:1,6',
'gcs_v'                 => 'required|integer|between:1,5',
'nilai_kritis'          => 'required|in:Normal,Abnormal',
'eksposisi_kritis'      => 'required|string|max:2000',
'catatan_kritis'        => 'nullable|string|max:2000',
```

### Cetak

`print.blade.php` **wajib** — dipakai sebagai lembar rujukan di depan patients
dan saat transfer (serah terima ke ICU).

---

## 9. Catatan Keperawatan Rawat Jalan (form 54)

### Rasional

Pada legacy `catatan_keperawatan_rajal.php` bukan form input, melainkan **daftar**
anamnesa RJ per pasien. Namun kebutuhan klinisnya nyata: nursing note bentuk SOAP
ringkas saat kunjungan rawat jalan. Karena itu form 44Expired dirancang sebagai
**SOAP ringkas per kunjungan**, konsisten dengan form 9 (Implementasi Keperawatan)
dan form 4 (Pengkajian Harian).

Field **100% reuse** — dokumen ini tidaktherapy menambah objek baru.

### Struktur Field

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `keluhan` | 114 | Keluhan Utama Harian | textarea | ya | Reuse objek 114 |
| `kesadaran` | 51 | Kesadaran | radio | ya | Reuse objek 51 |
| `td_sistolik` | 6 | Tekanan Darah Sistolik | number | tidak | Reuse |
| `td_diastolik` | 7 | Tekanan Darah Diastolik | number | tidak | Reuse |
| `nadi` | 10 | Nadi | number | tidak | Reuse |
| `pernapasan` | 12 | Pernapasan | number | tidak | Reuse |
| `suhu` | 11 | Suhu | number | tidak | Reuse |
| `saturasi` | 15 | Saturasi Oksigen | number | tidak | Reuse |
| `catatan_keperawatan` | 115 | Catatan Keperawatan | textarea | ya | Reuse objek 115 |
| `alergi` | 17 | Alergi | textarea | tidak | Reuse objek 17 |
| `tanggal_catatan` | 155 | Tanggal Observasi | date | ya | Reuse objek 155 |
| `waktu_catatan` | 156 | Waktu Observasi | time | ya | Reuse objek 156 |

### Mapping

```php
54 => [
    'keluhan' => 114, 'kesadaran' => 51,
    'td_sistolik' => 6, 'td_diastolik' => 7, 'nadi' => 10,
    'pernapasan' => 12, 'suhu' => 11, 'saturasi' => 15,
    'catatan_keperawatan' => 115, 'alergi' => 17,
    'tanggal_catatan' => 155, 'waktu_catatan' => 156,
],
```

### Dashboard & Form Master

```php
['dashboard_menu_sub_id' => 77, 'dashboard_menu_id' => 8, 'nama_sub_menu' => 'Anamnesis'],
['dashboard_menu_sub_extra_id' => 37, 'dashboard_menu_sub_id' => 77, 'nama_sub_menu_extra' => 'Catatan Keperawatan Rajal'],
['form_id' => 54, 'nama_form' => 'Catatan Keperawatan Rawat Jalan', 'slug' => 'catatan_keperawatan_rajal', 'id_dash_menu' => '8.77.37', 'ri' => 0, 'rj' => 1, 'igd' => 0, 'mcu' => 0],
```

> **Nama sub-extra WAJIB `Catatan Keperawatan Rajal`.** Aturan slug
> (`ALOKASI_ID_GLOBAL.md` §7): `form.slug` harus sama dengan
> `Str::slug(nama_sub_menu_extra, '_')`. Nama lama
> `'Catatan Keperawatan Rawat Jalan'` menghasilkan slug
> `catatan_keperawatan_rawat_jalan` yang **tidak** sama dengan slug form
> `catatan_keperawatan_rajal` sehingga form jadi yatim di dashboard.
> `nama_form` tetap "Catatan Keperawatan Rawat Jalan" karena nama form bukan
> sumber slug.

### Akses EHR

```php
['profesi_id' => 1, 'form_id' => 54, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 2, 'form_id' => 54, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
```

### Validasi

```php
'keluhan'             => 'required|string|max:1000',
'kesadaran'           => 'required|in:' . implode(',', array_keys(\App\Helpers\SelectOption::get('kesadaran'))),
'tanggal_catatan'     => 'required|date',
'waktu_catatan'       => 'required|date_format:H:i',
'catatan_keperawatan'  => 'required|string|max:2000',
'td_sistolik'         => 'nullable|integer|between:40,300',
'td_diastolik'        => 'nullable|integer|between:20,200',
'nadi'                => 'nullable|integer|between:20,250',
'pernapasan'          => 'nullable|integer|between:4,80',
'suhu'                => 'nullable|numeric|between:30,45',
'saturasi'            => 'nullable|integer|between:50,100',
'alergi'              => 'nullable|string|max:500',
```

### Cetak

Tidak wajib.

---

## 10. Riwayat Penyakit (form 55)

### Rasional

Anamnesis riwayat penyakit. Legacy `riw_cak.php` (1352 baris) memuat blok:

| Blok | Field legacy | Opsi |
|---|---|---|
| Diagnosa Medis | `diagnosa_medis` | dari `diagnosa_rawat` |
| Keluhan | `keluhan` | `_data_keluhan()`: Lemas, Demam, Pusing, Sakit Kepala, Batuk, Pilek, Sesak Napas, Kontrol, Obat Habis, Lainnya |
| Riwayat Penyakit Sebelumnya | `riwayat_penyakit_sebelumnya` | `_data_riwayat_penyakit()`: Hipertensi, DM, Typhoid, DHF, TB, Stroke, Asma, Lainnya |
| Riwayat Penyakit Sekarang | `riwayat_penyakit_sekarang` | teks bebas + kode ICD |
| Infeksius | `infeksius_flag`, `menular_melalui`, `infeksius_memerlukan_isolasi`, `infeksius_hasil_penunjang` | flag Ya/Tidak; Menular Melalui: Udara, Cairan Tubung, Kontak Langsung/Kulit, Makanan, Hewan, Susu/ASI, Air, Droplet |
| Imunologi | `imunologi_flag`, `imunologi_memerlukan_isolasi`, `imunologi_pembatasan_pengunjung`, `imunologi_hasil_penunjang` | Ya/Tidak |
| Riwayat Kemoterapi | `riwayat_kemoterapi` | Ya/Tidak + detail |
| Riwayat Radioterapi | `riwayat_radioterapi` | Ya/Tidak + detail |
| Riwayat Operasi | `riwayat_operasi` / `riw_ope_kemo` | Ya/Tidak + detail |
| Vaccin | `vaksin_covid` | checkbox tanggal |

Blok reproduksi (menarche, G-P-A, HPHT, trimester, TT, hamil bulan) **di luar
cakupan** dokumen ini — lihat catatan §16.

### Struktur Field

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `diagnosa_medis` | 40 | Diagnosa Medis | multi-select ICD | ya | Reuse objek 40 |
| `keluhan` | 13 | Keluhan Utama | checkbox + text | ya | Reuse objek 13; opsi dari `set_default_data.php` |
| `riwayat_penyakit_sebelumnya` | 41 | Riwayat Penyakit Sebelumnya | checkbox + text | ya | Reuse objek 41 |
| `riwayat_penyakit_sekarang` | 42 | Riwayat Penyakit Sekarang | textarea | ya | Reuse objek 42 |
| `infeksius_flag` | 43 | Infeksius (Flag) | radio Ya/Tidak | ya | Reuse objek 43 |
| `menular_melalui` | 44 | Menular Melalui | multi-select | ya bila Infeksius | Reuse objek 44 |
| `infeksius_memerlukan_isolasi` | 45 | Infeksius Memerlukan Isolasi | radio Ya/Tidak | ya bila Infeksius | Reuse objek 45 |
| `infeksius_hasil_penunjang` | 46 | Infeksius Hasil Penunjang | textarea | ya bila Infeksius | Reuse objek 46 |
| `imunologi_flag` | 47 | Imunologi (Flag) | radio Ya/Tidak | ya | Reuse objek 47 |
| `imunologi_memerlukan_isolasi` | 48 | Imunologi Memerlukan Isolasi | radio Ya/Tidak | ya bila Imunologi | Reuse objek 48 |
| `imunologi_pembatasan_pengunjung` | 49 | Imunologi Pembatasan Pengunjung | radio Ya/Tidak | ya bila Imunologi | Reuse objek 49 |
| `imunologi_hasil_penunjang` | 50 | Imunologi Hasil Penunjang | textarea | ya bila Imunologi | Reuse objek 50 |
| `riwayat_kemoterapi` | 52 | Riwayat Kemoterapi | radio Ya/Tidak | ya | Reuse objek 52 |
| `riwayat_radioterapi` | 53 | Riwayat Radioterapi | radio Ya/Tidak | ya | Reuse objek 53 |
| `riwayat_operasi` | 61 | Riwayat Operasi Kemo | radio Ya/Tidak | ya | Reuse objek 61 |
| `vaksin_covid` | 62 | Vaksin COVID | date (nullable) | tidak | Reuse objek 62 |
| `alergi` | 17 | Alergi | textarea | ya | Reuse objek 17 |
| `riwayat_perawatan_berulang` | 281 | Riwayat Perawatan Berulang | radio Ya/Tidak | ya | Objek baru — indikasi Lukas frequent encounter |
| `catatan_riwayat` | 77 | Keterangan | textarea | tidak | Reuse objek 77 |

### Mapping

```php
55 => [
    'diagnosa_medis'                     => 40,
    'keluhan'                            => 13,
    'riwayat_penyakit_sebelumnya'        => 41,
    'riwayat_penyakit_sekarang'          => 42,
    'infeksius_flag'                     => 43,
    'menular_melalui'                   => 44,
    'infeksius_memerlukan_isolasi'      => 45,
    'infeksius_hasil_penunjang'          => 46,
    'imunologi_flag'                     => 47,
    'imunologi_memerlukan_isolasi'       => 48,
    'imunologi_pembatasan_pengunjung'   => 49,
    'imunologi_hasil_penunjang'          => 50,
    'riwayat_kemoterapi'                 => 52,
    'riwayat_radioterapi'                => 53,
    'riwayat_operasi'                    => 61,
    'vaksin_covid'                       => 62,
    'alergi'                             => 17,
    'riwayat_perawatan_berulang'         => 281,
    'catatan_riwayat'                    => 77,
],
```

> `objek_id` 41/42 dipakai dua kali dilegacy dengan nama berbeda (`riwayat_penyakit_keluarga` = 62,
> `riwayat_penyakit_sebelumnya` = 225). NusaMedika tidak memakai angka legacy;
> pemetaan di atas mengikuti objek NusaMedika yang sudah ada.

### Dashboard & Form Master

```php
['dashboard_menu_sub_extra_id' => 35, 'dashboard_menu_sub_id' => 77, 'nama_sub_menu_extra' => 'Riwayat Penyakit'],
['form_id' => 55, 'nama_form' => 'Riwayat Penyakit', 'slug' => 'riwayat_penyakit', 'id_dash_menu' => '8.77.35', 'ri' => 1, 'rj' => 1, 'igd' => 1, 'mcu' => 0],
```

### Akses EHR

```php
['profesi_id' => 1, 'form_id' => 55, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 2, 'form_id' => 55, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
```

### Validasi

```php
'riwayat_penyakit_sebelumnya'  => 'required|array|min:1',
'riwayat_penyakit_sekarang'    => 'required|string|max:2000',
'infeksius_flag'               => 'required|in:ya,tidak',
'menular_melalui'             => 'nullable|required_if:infeksius_flag,ya|array|min:1',
'infeksius_memerlukan_isolasi' => 'nullable|required_if:infeksius_flag,ya|in:ya,tidak',
'infeksius_hasil_penunjang'    => 'nullable|required_if:infeksius_flag,ya|string|max:1000',
'imunologi_flag'               => 'required|in:ya,tidak',
'imunologi_memerlukan_isolasi' => 'nullable|required_if:imunologi_flag,ya|in:ya,tidak',
'imunologi_pembatasan_pengunjung' => 'nullable|required_if:imunologi_flag,ya|in:ya,tidak',
'imunologi_hasil_penunjang'    => 'nullable|required_if:imunologi_flag,ya|string|max:1000',
'riwayat_kemoterapi'           => 'required|in:ya,tidak',
'riwayat_radioterapi'          => 'required|in:ya,tidak',
'riwayat_operasi'              => 'required|in:ya,tidak',
'vaksin_covid'                 => 'nullable|date',
'alergi'                       => 'required|string|max:1000',
'riwayat_perawatan_berulang'   => 'required|in:ya,tidak',
```

Checkbox group disimpan sebagai JSON array.

### Cetak

`print.blade.php` **wajib** — form ini jadi bagian dari Paket Admisi.

---

## 11. Riwayat Keluarga (form 56)

### Rasional

Legacy `riwayat_keluarga_cak.php` sangat ringkas: **7 checkbox** penyakit keluarga
(`Asma`, `Diabetes`, `Hipertensi`, `TB`, `Cancer`, `Anemia`, `Lainnya`) + satu
input `hubungan_keluarga` (objek 389).

NusaMedika menambah dua kolom yang sudah ada di tabel `pasien` sehingga bisa
display ulang: nama anggota keluarga dan usianya (dipakai untuk screening
keluarga). Sisanya reuse.

### Struktur Field

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `penyakit_keluarga` | 283 | Riwayat Penyakit Keluarga | checkbox group | ya | 7 opsi legacy; minimal 1 |
| `hubungan_keluarga` | 284 | Hubungan Keluarga | text | ya | Legacy `hubungan_keluarga`; satu-satunya pemakai objek 284 |
| `catatan_keluarga` | 77 | Keterangan | textarea | tidak | Reuse objek 77 |

### Kriteria Penilaian

| Opsi legacy | Nilai |
|---|---|
| Asma | Ya/Tidak |
| Diabetes | Ya/Tidak |
| Hipertensi | Ya/Tidak |
| TB | Ya/Tidak |
| Cancer | Ya/Tidak |
| Anemia | Ya/Tidak |
| Lainnya | text bebas (wajib bila dicentang) |

### Mapping

```php
56 => [
    'penyakit_keluarga' => 283,
    'hubungan_keluarga' => 284,
    'catatan_keluarga'  => 77,
],
```

### Dashboard & Form Master

```php
['dashboard_menu_sub_extra_id' => 36, 'dashboard_menu_sub_id' => 77, 'nama_sub_menu_extra' => 'Riwayat Keluarga'],
['form_id' => 56, 'nama_form' => 'Riwayat Keluarga', 'slug' => 'riwayat_keluarga', 'id_dash_menu' => '8.77.36', 'ri' => 1, 'rj' => 1, 'igd' => 1, 'mcu' => 0],
```

### Akses EHR

```php
['profesi_id' => 1, 'form_id' => 56, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 2, 'form_id' => 56, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
```

### Validasi

```php
'penyakit_keluarga'   => 'required|array|min:1',
'penyakit_keluarga.*' => 'string',
hubungan_keluarga'    => 'required|string|max:100',
'catatan_keluarga'    => 'nullable|string|max:1000',
```

Validasi tambahan di `filteredData()`: bila `Lainnya` tidak termasuk array, hapus
teksnya.

### Cetak

Tidak wajib (ikut tercetak di Paket Admisi form 55).

---

## 12. Asesmen Akhir Kehidupan (form 123)

### Rasional

Asesmen pasien akhir kehidupan. Legacy `assesment_pasien_akhir_kehidupan.php` adalah
**accordion komposit** yang `include` lima berkas:

| Panel | Berkas legacy | Domain dokumen lain |
|---|---|---|
| A. Waktu Assesmen | `waktu_assesmen.php` | (di sini) |
| B. Assesmen Gejala | `assesmen_gejala.php` | `KONSEP_ASESMEN_GIZI.md` |
| C. Assesmen Spiritual | `assesmen_spiritual.php` | `KONSEP_PSIKOSOSIAL_KEROHANIAN.md` |
| D. Assesmen Psikologi | `assesmen_psikologi_pasien.php` | `KONSEP_PSIKOSOSIAL_KEROHANIAN.md` |
| E. Assesmen Kebutuhan | `assesmen_kebutuhan.php` | `KONSEP_DISCHARGE_PLANNING.md` |

Karena tiga panel sudah tercakup dokumen lain, form 123 di NusaMedika fokus pada
**kerangka waktu + gejala fisik terminal + keputusan paliatif**, dan panel
spiritual/psikologis akan ditambahkan sebagai **partial inklusif** setelah dokumen
psikososial terimplementasi (lihat §16).

### Struktur Field

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `tanggal_asesmen_akhir` | 137 | Tanggal Assesmen | date | ya | Reuse objek 137 |
| `waktu_asesmen_akhir` | 138 | Waktu Assesmen | time | ya | Reuse objek 138 |
| `waktu_asesmen_awal` | 285 | Waktu Asesmen Awal Kehidupan | date | ya | Legacy `tgl_assesmen_awal` (objek 1086) |
| `waktu_asesmen_akhir_hari` | 286 | Waktu Asesmen Akhir Kehidupan | date | ya | Legacy `tgl_assesmen_akhir` (objek 1087) |
| `kesadari_an_terminal` | 51 | Kesadaran | radio | ya | Reuse objek 51 |
| `nyeri_terakhir` | 14 | Nyeri | radio Ya/Tidak | ya | Reuse objek 14 |
| `skor_nyeri` | 140 | Skor Nyeri | number 0–10 | ya bila nyeri | Reuse objek 140 |
| `gejala_terminal` | 115 | Catatan Keperawatan | textarea | ya | Reuse objek 115 |
| `tindakan_paliatif` | 77 | Keterangan | textarea | ya | Reuse objek 77 |
| `catatan_keluarga` | 120 | Catatan Tambahan | textarea | tidak | Reuse objek 120 |

### Mapping

```php
123 => [
    'tanggal_asesmen_akhir'        => 137,  // reuse
    'waktu_asesmen_akhir'         => 138,  // reuse
    'waktu_asesmen_awal'          => 285,
    'waktu_asesmen_akhir_hari'    => 286,
    'kesadaran_terminal'           => 51,   // reuse
    'nyeri_terakhir'               => 14,   // reuse
    'skor_nyeri'                   => 140,  // reuse
    'gejala_terminal'              => 115,  // reuse
    'tindakan_paliatif'            => 77,   // reuse
    'catatan_keluarga'             => 120,  // reuse
],
```

### Dashboard & Form Master

```php
['dashboard_menu_sub_extra_id' => 33, 'dashboard_menu_sub_id' => 76, 'nama_sub_menu_extra' => 'Asesmen Akhir Kehidupan'],
['form_id' => 123, 'nama_form' => 'Asesmen Akhir Kehidupan', 'slug' => 'asesmen_akhir_kehidupan', 'id_dash_menu' => '8.76.33', 'ri' => 1, 'rj' => 0, 'igd' => 1, 'mcu' => 0],
```

### Akses EHR

```php
['profesi_id' => 1, 'form_id' => 123, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 0],
['profesi_id' => 2, 'form_id' => 123, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 0],
```

### Validasi

```php
'tanggal_asesmen_akhir'     => 'required|date',
'waktu_asesmen_akhir'      => 'required|date_format:H:i',
'waktu_asesmen_awal'       => 'required|date',
'waktu_asesmen_akhir_hari' => 'required|date|after_or_equal:waktu_asesmen_awal',
'kesadaran_terminal'        => 'required',
'nyeri_terakhir'            => 'required|in:Ya,Tidak',
'skor_nyeri'                => 'nullable|required_if:nyeri_terakhir,Ya|integer|between:0,10',
'gejala_terminal'           => 'required|string|max:2000',
'tindakan_paliatif'         => 'required|string|max:2000',
'catatan_keluarga'          => 'nullable|string|max:1000',
```

### Cetak

`print.blade.php` **wajib** — jadi lampiran Visum et lettanis.

---

## 13. Uji Fungsi & Asesment (form 124)

### Rasional

Lembar asesmen rehabilitasi medik/fisioterapi. Legacy `uji_fungsi_dan_assesment.php`
sederhana — kolom S/O/A/R yang isinya diambil dari EMR form 3 (Pengkjian Awal)
ketika membuat baris baru:

| Kolom | variabel legacy | objek NusaMedika |
|---|---|---|
| Tanggal Pelayanan | `tgl_pelayanan` | 155 (reuse) |
| Subjective | `anamnesa` | 1 (reuse) |
| Objective | `pemeriksaan_fisik` | 2 (reuse) |
| Assesment — Diagnosa medis | `diagnosis_medis` | 40 (reuse) |
| Assesment — Diagnosa fungsi | `diagnosis_fungsi` | 3 (reuse) |
| Goal | `goal` | 4 (reuse) |
| Suspek penyakit / collaborate | `suspek_penyakit` | 77 (reuse) |
| Tata laksana | `tata_laksana` | 5 (reuse) |
| Pemeriksaan penunjang | `pemeriksaan_penunjang` | 77 (reuse) |
| Tindakan program rehab | `tindakan_program_rehab_medik` | 5 (reuse) |
| Anjuran | `anjuran` | 77 (reuse) |
| Edukasi | `edukasi` | 77 (reuse) |
| Frekuensi kunjungan | `frekuensi_kunjungan` | 77 (reuse) |
| Evaluasi | `evaluasi` | 4 (reuse) |
| Waktu | (jam pelayanan) | 156 (reuse) |

**Semua reuse — nol objek baru.** Ini disengaja: bentuknya adalah SOAP dasar,
yang sudah punya objek canonical.

### Struktur Field

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `tanggal_pelayanan` | 155 | Tanggal Observasi | date | ya | Reuse |
| `waktu_pelayanan` | 156 | Waktu Observasi | time | ya | Reuse |
| `subjective` | 1 | Subjective (S) | textarea | ya | Reuse — prefill dari pengkajian awal |
| `objective` | 2 | Objective (O) | textarea | ya | Reuse |
| `diagnosis_fungsi` | 3 | Assessment (A) | textarea | ya | Reuse |
| `goal` | 4 | Planning (P) | textarea | ya | Reuse |
| `diagnosis_medis` | 40 | Diagnosa Medis | select ICD | ya | Reuse |
| `instruksi` | 5 | Instruksi (I) | textarea | ya | Reuse — tata laksana + tindakan program |
| `evaluasi` | 77 | Keterangan | textarea | ya | Reuse |
| `edukasi_anjuran` | 77 | Keterangan | textarea | ya | Reuse |
| `frekuensi_kunjungan` | 77 | Keterangan | select | ya | Reuse |

> Tiga field terakhir **berbagi objek 77** dengan tiga variabel berbeda. Ini
> disengaja dan aman karena `emr_detail` di-`pluck` per `variabel` (bukan per
> `objek_id`); `emrDetailByObjek()` yang dipakai untuk laporan perlu hati-hati
> (lihat §16, risiko R5).

### Mapping

```php
124 => [
    'tanggal_pelayanan'    => 155,
    'waktu_pelayanan'      => 156,
    'subjective'           => 1,
    'objective'            => 2,
    'diagnosis_fungsi'     => 3,
    'goal'                 => 4,
    'diagnosis_medis'      => 40,
    'instruksi'            => 5,
    'evaluasi'             => 77,
    'edukasi_anjuran'      => 77,
    'frekuensi_kunjungan'  => 77,
],
```

### Dashboard & Form Master

```php
['dashboard_menu_sub_extra_id' => 34, 'dashboard_menu_sub_id' => 76, 'nama_sub_menu_extra' => 'Uji Fungsi Asesment'],
['form_id' => 124, 'nama_form' => 'Uji Fungsi & Asesment', 'slug' => 'uji_fungsi_asesment', 'id_dash_menu' => '8.76.34', 'ri' => 0, 'rj' => 1, 'igd' => 0, 'mcu' => 1],
```

### Akses EHR

```php
['profesi_id' => 1, 'form_id' => 124, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 2, 'form_id' => 124, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
```

### Validasi

```php
'tanggal_pelayanan'   => 'required|date',
'waktu_pelayanan'     => 'required|date_format:H:i',
'subjective'          => 'required|string|max:2000',
'objective'           => 'required|string|max:2000',
'diagnosis_fungsi'    => 'required|string|max:1000',
'goal'                => 'required|string|max:1000',
'diagnosis_medis'     => 'required|string|max:255',
'instruksi'           => 'required|string|max:2000',
'evaluasi'            => 'required|string|max:2000',
'edukasi_anjuran'     => 'required|string|max:1000',
'frekuensi_kunjungan' => 'required|in:Harian,2x/Minggu,Mingguan,2x/Bulan,Bulanan,Once',
```

### Cetak

`print.blade.php` **wajib** —Authorization/okes`,(bool)``), dan menjadi lampiran
discharge.

---

## 14. Respon Emosi & Status Mental (form 125)

### Rasional

Asesmen psikososial dasar. Legacy `respon_emosi_cak.php` berisi tiga blok:

**a) Respon Emosi** (objek 41) — 8 opsi:
`Tenang`, `Marah / Mudah Tersinggung`, `Rendah Diri`, `Takut`, `Sedih`, `Cemas`,
`Gelisah Agitasi`, `Lainnya` (text).

**b) Status Mental** (objek 77) — 6 opsi:
`Orientasi Baik`, `Kooperatif`, `Tidak Ada Respon`, `Disorientasi`, `Menyerang`,
`Lainnya` (text).

**c) Kebutuhan Perlindungan** (objek 277) — radio `Ya/Tidak`, lalu bila `Ya`:
- `Kebutuhan Teridentifikasi` (objek 278): `Korban Kekerasan` / `Terlantar`
- `Identifikasi Kebutuhan Perlindungan` (objek 279) — 7 checkbox:
  `Tidak Mau Dikunjungi`, `Pembatasan Pengunjung`, `Ruang Khusus`, `CCTV`,
  `Perlindungan Security`, `Perlindungan Hukum`, `Dukungan Eksternal (Dinas/Yayasan Sosial)`

> Nomor objek legacy (41, 77, 277–279) **tidak boleh dipakai** karena sudah
> terisi di NusaMedika dengan makna lain (41 = Riwayat Penyakit Sebelumnya,
> 77 = Keterangan). Semua objek di bawah adalah baru.

### Struktur Field

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `respon_emosi` | 287 | Respon Emosi | checkbox group | ya | 8 opsi legacy |
| `status_mental` | 288 | Status Mental | checkbox group | ya | 6 opsi legacy |
| `kebutuhan_perlindungan` | 289 | Kebutuhan Perlindungan | radio Ya/Tidak | ya | |
| `kebutuhan_teridentifikasi` | 290 | Kebutuhan Teridentifikasi | radio | ya bila Ya | Victim Kekerasan / Terlantar |

Ketujuh butir `identifikasi_kebutuhan_perlindungan` disimpan sebagai **JSON array
pada `kebutuhan_perlindungan`** (satu variabel) agar tidak memerlukan objek
tambahan.

### Mapping

```php
125 => [
    'respon_emosi'            => 287,
    'status_mental'           => 288,
    'kebutuhan_perlindungan' => 289,   // kolom "Ya/Tidak" + JSON butir perlindungan
    'kebutuhan_teridentifikasi' => 290,
],
```

### Dashboard & Form Master

```php
['dashboard_menu_sub_extra_id' => 32, 'dashboard_menu_sub_id' => 76, 'nama_sub_menu_extra' => 'Respon Emosi'],
['form_id' => 125, 'nama_form' => 'Respon Emosi & Status Mental', 'slug' => 'respon_emosi', 'id_dash_menu' => '8.76.32', 'ri' => 1, 'rj' => 1, 'igd' => 1, 'mcu' => 0],
```

### Akses EHR

```php
['profesi_id' => 1, 'form_id' => 125, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 0],
['profesi_id' => 2, 'form_id' => 125, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 0],
```

### Validasi

```php
'respon_emosi'            => 'required|array|min:1',
'respon_emosi.*'          => 'string',
'status_mental'           => 'required|array|min:1',
'status_mental.*'         => 'string',
'kebutuhan_perlindungan' => 'required|in:ya,tidak',
'kebutuhan_teridentifikasi' => 'nullable|required_if:kebutuhan_perlindungan,ya|in:Korban Kekerasan,Terlantar',
```

Bila `kebutuhan_perlindungan = tidak`, `kebutuhan_teridentifikasi` **dibuang**
di `filteredData()`.

### Cetak

Tidak wajib.

---

## 15. Implementasi

Checklist berkas yang harus dibuat:

### Seeder (`database/seeders/EmrMasterSeeder.php`)

- [ ] `$menus` — tambah `['dashboard_menu_id' => 8, 'nama_menu' => 'Penilaian Klinis']`
- [ ] `$subMenus` — tambah 5 baris (band 73–77):
      `73 Asuhan Keperawatan (menu 2)`, `74 Serah Terima (menu 2)`,
      `75 Asesmen Klinis Keperawatan (menu 2)`, `76 Asesmen Pasien (menu 8)`,
      `77 Anamnesis (menu 8)`
- [ ] `$extras` — tambah 12 baris (id 26–37)
- [ ] `$forms` — tambah 12 baris (form_id 22, 23, 29, 31, 34, 35, 54, 55, 56, 123, 124, 125)
- [ ] `$objeks` — tambah 55 baris (236–290)
- [ ] `$mapping` — tambah 12 blok (`22 => [...]` … `125 => [...]`), blok 23
      digenerate loop 1..10
- [ ] `EmrHelper::backfillObjekId(22)` … `backfillObjekId(125)`
- [ ] `$akses` — tambah 24 baris

### Helper

- [ ] `app/Helpers/AsesmenKeperawatanHelper.php` — definisi item Barthel-like untuk
      form 34, definisi FLACC untuk form 35, dan (bersama
      `KONSEP_NYERI.md`) definisi FLACC bersama agar tidak terduplikasi.
      **Rekomendasi:** taruh FLACC di `NyeriHelper.php` (dipakai form 27, 35, 36).

### Controller

- [ ] `app/Http/Controllers/EMR/RencanaAsuhanKeperawatan/RencanaAsuhanKeperawatanController.php`
- [ ] `app/Http/Controllers/EMR/IssuesKeperawatan/IssuesKeperawatanController.php`
- [ ] `app/Http/Controllers/EMR/Handover/HandoverController.php`
- [ ] `app/Http/Controllers/EMR/AsesmenKebutuhanEdukasi/AsesmenKebutuhanEdukasiController.php`
- [ ] `app/Http/Controllers/EMR/AsesmenStatusFungsional/AsesmenStatusFungsionalController.php`
- [ ] `app/Http/Controllers/EMR/PengkajianKeperawatanKritis/PengkajianKeperawatanKritisController.php`
- [ ] `app/Http/Controllers/EMR/CatatanKeperawatanRajal/CatatanKeperawatanRajalController.php`
- [ ] `app/Http/Controllers/EMR/RiwayatPenyakit/RiwayatPenyakitController.php`
- [ ] `app/Http/Controllers/EMR/RiwayatKeluarga/RiwayatKeluargaController.php`
- [ ] `app/Http/Controllers/EMR/AsesmenAhirKehidupan/AsesmenAhirKehidupanController.php`
- [ ] `app/Http/Controllers/EMR/UjiFungsiAsesment/UjiFungsiAsesmentController.php`
- [ ] `app/Http/Controllers/EMR/ResponEmosi/ResponEmosiController.php`

> ⚠️ `Str::studly('uji_fungsi_asesment')` = `UjiFungsiAsesment`.
> `Str::studly('respon_emosi')` = `ResponEmosi`.
> `Str::studly('asesmen_akhir_kehidupan')` = `AsesmenAhirKehidupan`.

### View

- [ ] `resources/views/moduls/EMR/{Folder}/index.blade.php` untuk 12 form
- [ ] `print.blade.php` untuk form 22, 29, 31, 34, 35, 55, 123, 124
- [ ] Partial baru `PartialForm/_riwayat_keluarga.blade.php` (dipakai form 55 & 56)

### SelectOption

- [ ] Tambah key baru: `hambatan_belajar`, `kebutuhan_belajar`, `tingkat_pendidikan`,
      `karakteristik_status_fungsional`, `frekuensi_kunjungan_rehab`,
      `jenis_serah_terima`, `suara_nafas`, `sirkulasi_perfusi`, `nilai_kritis`,
      `respon_emosi`, `status_mental`, `asal_masuk_kritis`
- [ ] Key lama yang dipakai ulang: `shift` (form 29), `kesadaran` (form 34/35/123),
      `asal_pasien_ranap` (form 35)

### Dokumentasi

- [ ] `AGENTS.md` — tambahkan entri untuk form 22–35, 54–56, 123–125
- [ ] `docs/ANALISIS_KEKURANGAN_FORM.md` §6 — ubah baris KONSEP_ASUHAN_KEPERAWATAN_LANJUTAN.md menjadi "Rancangan"

### Verifikasi

```bash
docker compose up -d db
docker compose exec app php artisan migrate:fresh --seed
docker compose exec app ./vendor/bin/pint --dirty
```

---

## 16. Catatan & Risiko

| # | Risiko | Dampak | Mitigasi |
|---|---|---|---|
| R1 | **`id_dash_menu` 3 tingkat.** Semua form dokumen ini punya extra → id_dash_menu berbentuk `"8.76.31"` / `"2.73.26"`. Bandingkan dengan `===`, **jangan `==`** (bug lama `"1.1" == "1.10"`). | Form yatim dari dashboard & checkbox Akses EHR. | Verifikasi manual: `SELECT id_dash_menu FROM form WHERE form_id IN (22,23,...)` harus persis sama dengan `CONCAT_WS('.', menu, sub, extra)` dari `header_ehr`. |
| R2 | **Slug sub-extra ≠ slug form.** `EmrDashboardController` memakai `Str::slug($namaSubExtra, '_')` sebagai `form_name`. | Form tampil "Unsupported". | Sudah diverifikasi satu per satu di §"Dashboard & Form Master" tiap form. Tambahkan unit test yang meng-iterate `$forms` + `$extras` dan asserting kesamaan slug. |
| R3 | **Master diagnosa-keperawatan belum ada di NusaMedika.** Legacy punya 5 tabel (`diagnosa_keperawatan`, `..._indikasi`, `..._indikasi_kriteria`, `..._luaran`, `..._intervensi`). | Form 22 & 23 tidak bisa dropdown. | **Prasyarat:** migration + model `DiagnosaKeperawatan` + seeder minimal 30 diagnosa SDKI. Gunakan `SelectOption` bila master belum siap — bukan rekomendasi, hanya fallback sementara. |
| R4 | **Checkbox group disimpan sebagai JSON string.** Legacy memakai `detail_emr[x][]` sehingga 1 variabel punya banyak baris — pola yang justru **dilarang** di NusaMedika (§2.2 PANDUAN). | Kolom kedua tertimpa diam-diam. | Simpan sebagai `json_encode(array)` pada satu `variabel`; saat baca `json_decode($emr_data['x'], true)`. Tambahkan Partial `x-checkbox-group` yang konsisten. |
| R5 | **Beberapa variabel berbagi objek** (form 124: `evaluasi`, `edukasi_anjuran`, `frekuensi_kunjungan` → objek 77). `EmrHelper::emrDetailByObjek()` melakukan `pluck('value','objek_id')` → **salah satu akan tertimpa**. | Laporan berbasis objek kehilangan 2 dari 3 field. | Hindari `emrDetailByObjek()` pada form 124; kalau laporan butuh pengelompokan objek, panggil `emrDetailByVariabel()` lalu map manual. Alternatif lebih aman: alokasikan objek baru — perlu naik ke rentang setelah 290 (di luar alokasi dokumen ini). |
| R6 | **Precomputeorski BLANK di `objek_form_control`.** Field helper form 35 (`metode_skala_nyeri_kritis`, `skor_vas_kritis`, `flacc_*`) tidak dipetakan. | `filteredData()` membuangnya via `array_intersect_key`. | `filteredData()` **wajib membaca field ini sebelum** `array_intersect_key` (pola `ews_na` di `EwsHelper`, PANDUAN §2.6). |
| R7 | **Handover append-only.** `akses_update = 0`, tetapi route `emr.form.update` tetap bisa dipanggil langsung. | Klien bisa update via URL. | `update()` harus `abort_unless(AksesEhr::can($formId,'update'), 403)` — sudah inherent, tapi tetap test manual dengan akun perawat. |
| R8 | **Form 34 & 33 tumpang tindih.** Keduanya 10 item ADL. | Data membingungkan; laporan ganda. | Dokumentasikan perbedaan di label menu dan pada `x-emr-history-table` header; form 34 tidak diberi `print` badge "Barthel". |
| R9 | **Nomor objek legacy tidak boleh dipakai langsung** (41, 77, 155, 161, 224, 225, 226, 227–239, 243, 244, 248, 249, 277–279, 284, 355, 373, 374, 389, 429, 458, 498, 503, 509–532, 578, 603, 809, 810, 811, 1044–1049, 1062, 1070, 1086, 1087, 1101, 1287, 1332, 1333, 1346, 1351). | Data tercampur antar form — jebakan §5 PANDUAN. | Selalu pakai objek NusaMedika. Semua nomor legacy di atas hanya dirujuk di kolom "keterangan" tabel legacy. |
| R10 | **Blok reproduksi `riw_cak.php` belum termuat.** | Menarche/G-P-A/HPHT/TT tidak tertangkap. | Akan ditangani form `59–69` (`KONSEP_OBSTETRI_NEONATAL.md`) atau form obstetri tersendiri; jangan ditambah ke form 55 tanpa keputusan. |
| R11 | **Panel A–E pada `assesment_pasien_akhir_kehidupan.php` bertabrakan dengan dokumen lain.** | Isi form dobel. | Form 123 sengaja hanya mengimplementasikan panel A + gejala; panel C/D ditambahkan setelah `KONSEP_PSIKOSOSIAL_KEROHANIAN.md` selesai, sebagai partial inklusif. |
| R12 | **`emr_detail.variabel` varchar(250).** Nama variabel+suffix panjang masih aman (terpanjang `kebutuhan_teridentifikasi`, 27 karakter), tapi jangan menambah `_11`..`_20` pada form 23 tanpa mengecek panjang total. | Data tidak tersimpan / terpotong. | Assert `strlen($variabel) <= 250` di loop seeder. |
| R13 | **Waktu server vs DB 8 jam.** Semua timestamp harus ditulis PHP (`now()`), tidak `NOW()`. | Tanggal meleset 8 jam. | `EmrHelper::insert()` sudah memakai `now()` — jangan tulis langsung `DB::table('emr')->insert()` sendiri. |
| R14 | **Tambah 12 form + 55 objek (236–290).** Total objek jadi ± 500. | Halaman `Administrator/ManajemenEMR/Form` lambat. | Pertimbangkan filter objek per `form_id` (sudah ada) dan tambahkan pencarian objek by `nama_objek`. |