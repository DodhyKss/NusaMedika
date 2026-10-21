# Konsep Form EMR: Discharge Planning & Pemulangan Pasien

**Status:** Rancangan / Konsep — belum diimplementasikan
**Tanggal:** 2026-10-10
**Acuan:** [`PANDUAN_IMPLEMENTASI_FORM_EMR.md`](PANDUAN_IMPLEMENTASI_FORM_EMR.md),
[`ANALISIS_KEKURANGAN_FORM.md`](ANALISIS_KEKURANGAN_FORM.md) §3 P1,
[`AGENTS.md`](../AGENTS.md)
**Sumber data:** SIMRS Tenriawaru (legacy PHP/PostgreSQL) di
`/simrs_tenriawaru/FE/lib/modul/`

---

## 1. Ringkasan

Dua form ini menutup domain "discharge & pulang" yang masih kosong di NusaMedika.
Keduanya hanya relevan untuk **rawat inap** — `ri = 1`, `rj = igd = mcu = 0`.

| form_id | nama_form | slug | id_dash_menu | ri | rj | igd | mcu | objek baru |
|---|---|---|---|---|---|---|---|---|
| 17 | Discharge Planning | `discharge_planning` | `6.33` | 1 | 1 | 1 | 0 | 200–214 (15) |
| 18 | Pemulangan Pasien | `pemulangan_pasien` | `6.34` | 1 | 0 | 0 | 0 | 215–224 (10) |

> **Menu BARU.** `dashboard_menu_id = 6` dengan `nama_menu = 'Resume & Discharge'`
> (`ALOKASI_ID_GLOBAL.md` §2). Sub-menu **33 = Discharge Planning** (tanpa
> extra) dan **34 = Pemulangan Pasien** (tanpa extra). Menu ini belum ada di
> `EmrMasterSeeder` — `$menus` saat ini hanya berisi 1–5. Wajib ditambahkan
> bersama `$subMenus` di §3.3.
>
> **Band sub-menu.** `dashboard_menu_sub_id` bersifat **GLOBAL**, bukan per-menu
> (`ALOKASI_ID_GLOBAL.md` §0 dan §3). Sub 1–12 sudah dipakai seeder baseline dan
> **tidak boleh** dipakai ulang; band dokumen ini adalah **33–52**. Karena itu
> kedua sub di sini di-number **33** dan **34**, bukan 1 dan 2. `id_dash_menu`
> dihitung ulang menjadi `6.33` dan `6.34`.
>
> **Rentang objek.** Dokumen ini memakai `objek_id` **200–224** (25 objek baru).
> Vital sign, GCS, EWS, skor nyeri, alasan pulang, Diagnosa Medis, Tanggal/Waktu
> Observasi, Keterangan, dan `dirujuk_ke` (objek 186) **me-reuse objek yang
> sudah ada**. Allokasi berikutnya (dokumen triage IGD) mulai dari **225**.
>
> **Objek 186 (`Dirujuk Ke`) DIDEKLARASIKAN di
> [`KONSEP_CATATAN_MEDIS_LANJUTAN.md`](KONSEP_CATATAN_MEDIS_LANJUTAN.md)**
> (form 16, band objek 178–199) dan dipakai ulang di sini oleh form 18.
> Ini **sah** menurut `ALOKASI_ID_GLOBAL.md` §1 — 186 adalah satu-satunya objek
> > 177 yang boleh dipakai lintas dokumen. Dokumen ini **tidak** mendeklarasikan
> ulang objek 186 di `$objeks`; cukup memetakan variabelnya lewat
> `objek_form_control`.

### Ringkasan sebaran objek baru

| Rentang | Milik | Jumlah |
|---|---|---|
| 200–214 | Form 17 Discharge Planning | 15 |
| 215–224 | Form 18 Pemulangan Pasien | 10 |
| **Total** | | **25** |

### Pembagian tanggung jawab kedua form

| Form | Kapan diisi | Oleh | Isi |
|---|---|---|---|
| **17 Discharge Planning** | 24–72 jam setelah masuk, dan di-update setiap ada perubahan rencana | Perawat bersama Dokter | Penilaian kesiapan pulang: pengaruh kondisi, kebutuhan bantuan sehari-hari, kebutuhan edukasi, keterampilan khusus, rencana tanggal pulang. |
| **18 Pemulangan Pasien** | Saat pasien benar-benar dipulangkan | Perawat | Dokumentasi kondisi akhir, tanda vital saat pulang,hasil pemeriksaan, anjuran pulang, barang & hasil yang diserahkan, masalah keperawatan selama dirawat, rencana kontrol. |

Legacy memodelkan pemisahan ini dengan jelas: `discharge_planning.php` (947 baris)
adalah formulir perencanaan yang diisi berulang, sedangkan `pemulangan_pasien.php`
(408 baris) memakai `$dataForm` dengan 7 sub-form yang **hanya bisa dibuka bila
pasien sudah berstatus persiapan pulang** di `bed_log`
(`status_bed_log = persiapan_pulang`). NusaMedika mempertahankan aturan itu di §5.6.

---

## 2. Sumber Referensi Legacy

| File legacy | Kontribusi ke desain |
|---|---|
| `FE/lib/modul/discharge_planning.php` | Form 9 "Discharge Planning" — formulir terpanjang di legacy (947 baris). Field teridentifikasi: `tgl_masuk_rs`, `jam_masuk_rs`, `diagnosis`, `estimasi_hari_rawat`, `tgl_ren_pulang`, `jam_ren_pulang`; blok **Pengaruh** `pengaruh_pasien_kel`, `pengaruh_kerja`, `pengaruh_keuangan` (radio `value=0` Ya / `value=1` Tidak); teks "Antisipasi Masalah" (nama variabelnya di sistem lama salah ketik); blok **Kebutuhan Bantuan Sehari-hari** `detail_bantuan[1..10]` berpasangan dengan checkbox tersembunyi `bantuan[1..10]`; blok **Tersedia & Kebutuhan Bantuan** `tinggal_sendiri`, `membantu_pasien`, `gunakan_alat_medis`, `perlu_alat_bantu`, `perlu_perawatan_khusus`, `masalah_kebutuhan_pribadi`, `nyeri_kronis`, `perlu_edukasi`, `keterampilan_khusus`; blok **Kebutuhan Edukasi** 6 butir + `kebutuhan_edukasi_lain`. |
| `FE/lib/modul/pemulangan_pasien.php` | Form 32 "Pemulangan Pasien" (shell). Mengatur `$dataForm` 7 sub-form; mengambil TTV terakhir dari form Tanda Vital (`objek_id in (6,7,12,13,14,18,69,239,238,240,241)`); mengambil `alasan_pulang` dari `bed_log`; menampilkan alert *"Persiapkan Pulang Firstly dari Bed Management"* bila `bed_log_id` kosong. Metadata `tgl_pertemuan` / `jam_pertemuan`. |
| `FE/lib/modul/kondisi_pulang.php` | Sub-form 1 **KONDISI SAAT PULANG**. Field: `alasan` (indikatorPulang), `rujukannya` (Puskesmas/Faskes/Rumah Sakit), `makassar` (Ya/Tidak), `wafat` (DOA / Meninggal Di IGD / <48 Jam / >48 Jam); blok TTV `sistolik`/`diastolik`/`nadi`/`pernapasan`/`suhu`/`skala_nyeri`/`ews`; GLASGOW COMA SCALE `gcs_eye`/`gcs_motorik`/`gcs_verbal`/`gcs_score`; **Diet / Nutrisi** `diet` (Oral/NGT/Diet Khusus) + `detail_diet` (batas cairan); **B.A.B** `BAB` (Normal / Ileostomy-Colostomy); **B.A.K** `BAK` (Normal / Inkontinensia) + `detail_BAK` (kateter & tanggal pemasangan); **Luka / Operasi** `luka` (Bersih/Kering) + `detail_luka`; **Transfer & Mobilisasi** `transfer` (Mandiri / Dengan Pengawasan / Dibantu Sebagian / Dibantu Penuh). |
| `FE/lib/modul/edukasi_pulang.php` | Sub-form 2 **EDUKASI / PENYULUHAN YANG SUDAH DIBERIKAN** — 7 checkbox: *Penyakit & Pengobatannya*, *Perawatan di rumah*, *Perawatan Ibu & Bayi*, *Mengatasi Nyeri*, *Perawatan Luka*, *Persiapan Lingkungan & fasilitas di rumah*, *Nasehat Keluarga Berencana*. |
| `FE/lib/modul/diagnosa_pulang.php` | Sub-form 3 **DIAGNOSA KEPERAWATAN / MASALAH KEBIDANAN SELAMA DIRAWAT** — input `name="detail_emr[nyeri][17]"`. |
| `FE/lib/modul/nyeri_pulang.php` | Sub-form 4 **MANAJEMEN NYERI** — 3 field: `obat` (*Obat yang diminum / anti nyeri*), `efek` (*Efek samping yang mungkin timbul*), `ke_rs` (*Bila nyeri bertambah berat segera ke RS*). |
| `FE/lib/modul/anjuran_pulang.php` | Sub-form 5 **ANJURAN KEPERAWATAN SETELAH PULANG** — satu textarea `anjuran`. |
| `FE/lib/modul/hasil_pulang.php` | Sub-form 6 **BARANG DAN HASIL PEMERIKSAAN YANG DISERAHKAN PADA KELUARGA** — jumlah lembar `hasil_lab`, `rontgen`, `ct_scan`, `mri`, `usg`, `surat_sakit`; radio Ada/Tidak `asuransi`, `resume`, `buku_bayi`, `Gol_darah`, `skk_bayi`; teks `penyerah_bayi`, `lain_pulang`. |
| `FE/lib/modul/kontrol_pulang.php` | Sub-form 7 **RENCANA KONTROL SELANJUTNYA** — read-only dari form Konsultasi (`Kontrol Rawat Jalan Post Rawat`): `tgl_pertemuan`, `dokter_penerima_konsul`, `target_perawatan`/`bagian_id`. |
| `FE/lib/func/_func.php` → `indikatorPulang()` | Nilai resmi kondisi pulang: `1` Atas Persetujuan Dokter, `2` Dirujuk, `3` Atas Permintaan Sendiri, `4` Meninggal, `5` Lainnya. |
| `FE/lib/modul/PersiapanPulang.php`, `PersiapanPulangNew.php`, `CekPersiapanPulang.php` | Formulir persiapan pulang di Bed Management; sumber `bed_log.status_bed_log = persiapan_pulang` yang dibaca `pemulangan_pasien.php`. |
| `FE/lib/modul/BatalPulang.php`, `BatalPulangNew.php` | Alur pembatalan pulang yang membaca data discharge/pemulangan — referensi aturan transisi status. |

---

## 3. Form 17 — Discharge Planning

### 3.1 Rasional & tujuan klinis

Discharge planning adalah **proses**, bukan sekadar dokumen. Legacy memodelkannya
sebagai satu formulir panjang berisi tiga pertanyaan yang harus terjawab sebelum
pasien boleh pulang:

1. **Apakah pasien mampu_lookup mandiri di rumah dan apakah ada yang berubah?**
   (blok *Pengaruh*, *Tersedia*, *Kebutuhan Bantuan Sehari-hari*).
2. **Apakah ada risiko yang belum teratasi?**
   (`nyeri_kronis`, `masalah_kebutuhan_pribadi`, ` Shaw anticipates masalah`).
3. **Apa yang harus dipelajari pasien/keluarga?**
   (`perlu_edukasi`, blok *Kebutuhan Edukasi*, `keterampilan_khusus`).

Tujuan klinis di NusaMedika:

1. Mengubah discharge planning dari **checklist kertas** menjadi **data
   terstruktur** yang dapat diaudit untuk akreditasi.
2. Membuat **estimasi lama dirawat** dan **tanggal rencana pulang** menjadi field
   yang dapat dibandingkan dengan tanggal pulang sebenarnya — bahan analisis
   efisiensi layanan.
3. Menjadi **input form 18** (dibaca saat penyusunan dokumen pulang) dan
   **form 8 Konsultasi** (jenis `RENCANA_KONTROL`).

### 3.2 Struktur Field

#### Blok A — Identitas & Perencanaan

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `tanggal_masuk_rs` | 200 | Tanggal Masuk RS | date (readonly) | ya | Legacy `tgl_masuk_rs`. Di-prefill dari `registrasi.tgl_masuk`, tidak dapat diubah user. Objek baru karena objek 155 "Tanggal Observasi" bermakna tanggal pengukuran, bukan tanggal admisi. |
| `waktu_masuk_rs` | 156 | Waktu Observasi | time (readonly) | ya | **Reuse objek 156.** Dari `registrasi.tgl_masuk`. |
| `diagnosis_medis` | 40 | Diagnosa Medis | select2 ICD | ya | **Reuse objek 40.** Legacy `diagnosis`. Prefill dari diagnosa primer dokter terakhir pada `registrasi_detail` yang sama (logika `care_plan.php:50-83`). |
| `estimasi_hari_rawat` | 201 | Estimasi / Rencana Hari Rawat | number | ya | Legacy `estimasi_hari_rawat`, satuan hari. |
| `tanggal_ren_pulang` | 202 | Rencana Tanggal Pemulangan | date | ya | Legacy `tgl_ren_pulang`. Default dihitung: `tanggal_masuk_rs + estimasi_hari_rawat`. |
| `waktu_ren_pulang` | 156 | Waktu Observasi | time | tidak | **Reuse objek 156.** Legacy `jam_ren_pulang`. |

#### Blok B — Pengaruh Perubahan Kondisi (radio `Ya` / `Tidak`)

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `pengaruh_pasien_kel` | 203 | Pengaruh Perubahan Kondisi | radio Ya/Tidak | ya | Legacy `pengaruh_pasien_kel`. Pertanyaan: apakah ada pengaruh pada pasien/keluarga? |
| `pengaruh_kerja` | 203 | Pengaruh Perubahan Kondisi | radio Ya/Tidak | ya | Legacy `pengaruh_kerja`. |
| `pengaruh_keuangan` | 203 | Pengaruh Perubahan Kondisi | radio Ya/Tidak | ya | Legacy `pengaruh_keuangan`. |

> Ketiganya dipetakan ke **satu objek 203** dengan 3 variabel bersuffix —
> semuanya adalah pertanyaan "Apakah ada X yang berubah?" dan dilaporkan
> sebagai satu blok.

#### Blok C — Aspek Kebutuhan Pasien (radio `Ya` / `Tidak`)

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `antisipati_masalah` | 204 | Antisipasi Masalah | textarea | ya | Nama variabel di sistem lama salah ketik (huruf awal hilang); ditulis ulang di sini agar konsisten. Isi bebas: masalah yang diperkirakan muncul setelah pulang. |
| `tinggal_sendiri` | 206 | Tinggal & Bantuan Pelayan | radio Ya/Tidak | ya | Legacy `tinggal_sendiri`. |
| `membantu_pasien` | 206 | Tinggal & Bantuan Pelayan | radio Ya/Tidak | ya | Legacy `membantu_pasien`. |
| `gunakan_alat_medis` | 207 | Menggunakan Alat Medis di Rumah | radio Ya/Tidak | ya | Legacy `gunakan_alat_medis`. |
| `perlu_alat_bantu` | 208 | Perlu Alat Bantu | radio Ya/Tidak | ya | Legacy `perlu_alat_bantu`. Bila `Ya`, tampilkan text keterangan alat (masuk `catatan`). |
| `perlu_perawatan_khusus` | 209 | Perlu Perawatan Khusus | radio Ya/Tidak | ya | Legacy `perlu_perawatan_khusus`. |
| `masalah_kebutuhan_pribadi` | 210 | Masalah Kebutuhan Pribadi | radio Ya/Tidak | ya | Legacy `masalah_kebutuhan_pribadi`. |
| `nyeri_kronis` | 211 | Nyeri Kronis | radio Ya/Tidak | ya | Legacy `nyeri_kronis`. Bila `Ya`, form 18 mewajibkan sub-form *Manajemen Nyeri*. |
| `perlu_edukasi` | 212 | Perlu Edukasi | radio Ya/Tidak | ya | Legacy `perlu_edukasi`. Bila `Tidak`, Blok E dilewati. |
| `keterampilan_khusus` | 213 | Keterampilan Khusus | radio Ya/Tidak | ya | Legacy `keterampilan_khusus`. |

> `tinggal_sendiri` dan `membantu_pasien` dipetakan ke **satu objek 206**
> (dua variabel bersuffix) karena keduanya menggambarkan satu keputusan:
> "apakah pasien tinggal sendiri atau perlu dibantu?".

#### Blok D — Kebutuhan Bantuan Sehari-hari (ADL, checkbox)

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `adl_menyiapkan_makanan` | 205 | Kebutuhan Bantuan Sehari-hari | checkbox | ya | Legacy `menyiapkan_makanan`. |
| `adl_makan` | 205 | Kebutuhan Bantuan Sehari-hari | checkbox | ya | Legacy `makan`. |
| `adl_diet` | 205 | Kebutuhan Bantuan Sehari-hari | checkbox | ya | Legacy `diet`. |
| `adl_menyiapkan_obat` | 205 | Kebutuhan Bantuan Sehari-hari | checkbox | ya | Legacy `menyiapkan_obat`. |
| `adl_minum_obat` | 205 | Kebutuhan Bantuan Sehari-hari | checkbox | ya | Legacy `minum_obat`. |
| `adl_mandi` | 205 | Kebutuhan Bantuan Sehari-hari | checkbox | ya | Legacy `mandi`. |
| `adl_berpakaian` | 205 | Kebutuhan Bantuan Sehari-hari | checkbox | ya | Legacy `berpakaian`. |
| `adl_transportasi` | 205 | Kebutuhan Bantuan Sehari-hari | checkbox | ya | Legacy `transportasi`. |
| `adl_edukasi_kesehatan` | 205 | Kebutuhan Bantuan Sehari-hari | checkbox | ya | Legacy `edukasi_kesehatan`. |
| `adl_edukasi_lain` | 205 | Kebutuhan Bantuan Sehari-hari | text | tidak | Legacy `edukasi_lain`. |

#### Blok E — Kebutuhan Edukasi (checkbox)

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `edukasi_obat_obat` | 214 | Kebutuhan Edukasi | checkbox | ya | Legacy `obat_ubation`. |
| `edukasi_nutrisi` | 214 | Kebutuhan Edukasi | checkbox | ya | Legacy `nutrisi`. |
| `edukasi_perawatan_luka` | 214 | Kebutuhan Edukasi | checkbox | ya | Legacy `perawatan_luka`. |
| `edukasi_mobilisasi` | 214 | Kebutuhan Edukasi | checkbox | ya | Legacy `mobilisasi_bertahap`. |
| `edukasi_manajemen_nyeri` | 214 | Kebutuhan Edukasi | checkbox | ya | Legacy `manajemen_nyeri`. |
| `edukasi_insulin_sc` | 214 | Kebutuhan Edukasi | checkbox | ya | Legacy `pemberian_insulin_sc`. |
| `edukasi_lain_1` | 214 | Kebutuhan Edukasi | text | tidak | Legacy `ket_edukasi_lain_1`. |
| `edukasi_lain_2` | 214 | Kebutuhan Edukasi | text | tidak | Legacy `ket_edukasi_lain_2`. |

#### Blok F — Catatan

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `catatan` | 77 | Keterangan | textarea | tidak | **Reuse objek 77.** |

> **Pola dual-input legacy TIDAK dipakai.** Legacy memakai checkbox tersembunyi
> `name="detail_emr[bantuan][1]" value="menyiapkan_makanan"` yang selalu terkirim
> + checkbox `detail_bantuan[1]` yang mengirim `"on"`. Di NusaMedika cukup
> `<input type="checkbox" name="adl_makan" value="1">` dan dibaca dengan
> `$request->boolean('adl_makan')`. Kolom `value` yang kosong **tidak disimpan**
> sama sekali (dibuang di `filteredData()`), bukan disimpan sebagai string kosong.
>
> Kolom "Sudah diberikan" pada legacy (`sudah_diberikan_*`) **tidak** dijadikan
> field terpisah. Yang direkam di form 18 adalah *edukasi yang sudah diberikan*
> (blok `edukasi_1..7`). Ini menghindari status ganda yang bisa tidak sinkron
> antara rencana dan realisasi.

### 3.3 Dashboard & Form Master

```php
// ======== $objeks ========
// Objek 186 "Dirujuk Ke" TIDAK dideklarasikan di sini — ia milik
// KONSEP_CATATAN_MEDIS_LANJUTAN.md (form 16) dan hanya di-reuse di §4.4
// (ALOKASI_ID_GLOBAL.md §1).
// Sisanya (tanda vital, GCS, EWS, skor nyeri, diagnosa, tanggal/waktu,
// Keterangan, Instruksi) REUSE objek yang sudah ada.
200 => 'Tanggal Masuk RS',                       // Discharge Planning (form 17)
201 => 'Estimasi / Rencana Hari Rawat',
202 => 'Rencana Tanggal Pemulangan',
203 => 'Pengaruh Perubahan Kondisi',             // 3 variabel bersuffix
204 => 'Antisipasi Masalah',
205 => 'Kebutuhan Bantuan Sehari-hari',         // 10 variabel bersuffix (ADL)
206 => 'Tinggal & Bantuan Pelayan',              // 2 variabel bersuffix
207 => 'Menggunakan Alat Medis di Rumah',
208 => 'Perlu Alat Bantu',
209 => 'Perlu Perawatan Khusus',
210 => 'Masalah Kebutuhan Pribadi',
211 => 'Nyeri Kronis',
212 => 'Perlu Edukasi',
213 => 'Keterampilan Khusus',
214 => 'Kebutuhan Edukasi',                     // 8 variabel bersuffix

215 => 'Kondisi Saat Pulang',                    // Pemulangan Pasien (form 18)
216 => 'Meninggal',
217 => 'Diet / Nutrisi',                         // 2 variabel
218 => 'Eliminasi BAB & BAK',                    // 3 variabel
219 => 'Luka / Operasi',                        // 2 variabel
220 => 'Transfer & Mobilisasi',
221 => 'Edukasi / Penyuluhan yang Sudah Diberikan', // 8 variabel
222 => 'Manajemen Nyeri',                        // 3 variabel
223 => 'Barang & Hasil yang Disyerahkan',       // 13 variabel
224 => 'Masalah Keperawatan / Kebidanan Selama Dirawat',
```

```php
// ======== $menus ========
// Menu BARU. Tambah setelah menu 5 "Formulir".
$menus = [
    ['dashboard_menu_id' => 1, 'nama_menu' => 'Catatan Medis'],
    ['dashboard_menu_id' => 2, 'nama_menu' => 'Catatan Keperawatan'],
    ['dashboard_menu_id' => 3, 'nama_menu' => 'Resep'],
    ['dashboard_menu_id' => 4, 'nama_menu' => 'Order'],
    ['dashboard_menu_id' => 5, 'nama_menu' => 'Formulir'],
    ['dashboard_menu_id' => 6, 'nama_menu' => 'Resume & Discharge'],
];

$subMenus = [
    // ... baris 1–12 baseline + 13–15 (menu 1) + 33–34 (menu 6) dokumen lain
    //     lihat KONSEP_CATATAN_MEDIS_LANJUTAN.md / ALOKASI_ID_GLOBAL.md §3 ...
    // Menu 6 "Resume & Discharge" — Discharge Planning (tanpa extra, link langsung).
    // Str::slug('Discharge Planning','_') = "discharge_planning" WAJIB sama dengan form.slug.
    // Sub 33, BUKAN 1 — dashboard_menu_sub_id bersifat global (ALOKASI_ID_GLOBAL.md §0).
    ['dashboard_menu_sub_id' => 33, 'dashboard_menu_id' => 6, 'nama_sub_menu' => 'Discharge Planning'],
    // Menu 6 "Resume & Discharge" — Pemulangan Pasien (tanpa extra, link langsung).
    // Str::slug('Pemulangan Pasien','_') = "pemulangan_pasien" WAJIB sama dengan form.slug.
    ['dashboard_menu_sub_id' => 34, 'dashboard_menu_id' => 6, 'nama_sub_menu' => 'Pemulangan Pasien'],
];

$forms = [
    // Discharge Planning: perencanaan pulang untuk pasien rawat inap.
    // KHUSUS rawat inap. id_dash_menu "6.33".
    ['form_id' => 17, 'nama_form' => 'Discharge Planning', 'slug' => 'discharge_planning', 'id_dash_menu' => '6.33', 'ri' => 1, 'rj' => 1, 'igd' => 1, 'mcu' => 0],
    // Pemulangan Pasien: ringkasan & instruksi saat pasien dipulangkan.
    // KHUSUS rawat inap. id_dash_menu "6.34".
    ['form_id' => 18, 'nama_form' => 'Pemulangan Pasien', 'slug' => 'pemulangan_pasien', 'id_dash_menu' => '6.34', 'ri' => 1, 'rj' => 0, 'igd' => 0, 'mcu' => 0],
];
```

> **Tidak ada `dashboard_menu_sub_extra` baru.** Leaf `6.33` dan `6.34` keduanya
> tanpa extra → `id_dash_menu` berupa dua segmen. Band extra dokumen ini tidak
> ada (`ALOKASI_ID_GLOBAL.md` §4); extra 6–25 milik
> `KONSEP_CATATAN_MEDIS_LANJUTAN.md` (extra 6 = `DAR`), 26–45 milik
> `KONSEP_ASUHAN_KEPERAWATAN_LANJUTAN.md`, 46–65 ke dokumen cairan/nyeri/risiko
> jatuh.

### 3.4 Mapping

```php
$mapping[17] = [
    // Blok A — identitas & perencanaan
    'tanggal_masuk_rs'   => 200,
    'waktu_masuk_rs'     => 156,  // reuse "Waktu Observasi"
    'diagnosis_medis'    => 40,   // reuse "Diagnosa Medis"
    'estimasi_hari_rawat'=> 201,
    'tanggal_ren_pulang' => 202,
    'waktu_ren_pulang'   => 156,  // reuse "Waktu Observasi"

    // Blok B — pengaruh perubahan kondisi
    'pengaruh_pasien_kel'=> 203,
    'pengaruh_kerja'     => 203,
    'pengaruh_keuangan'  => 203,

    // Blok C — aspek kebutuhan pasien
    'antisipati_masalah'        => 204,
    'tinggal_sendiri'           => 206,
    'membantu_pasien'           => 206,
    'gunakan_alat_medis'        => 207,
    'perlu_alat_bantu'          => 208,
    'perlu_perawatan_khusus'    => 209,
    'masalah_kebutuhan_pribadi'=> 210,
    'nyeri_kronis'              => 211,
    'perlu_edukasi'             => 212,
    'keterampilan_khusus'       => 213,

    // Blok D — kebutuhan bantuan sehari-hari (ADL)
    'adl_menyiapkan_makanan'=> 205,
    'adl_makan'             => 205,
    'adl_diet'              => 205,
    'adl_menyiapkan_obat'   => 205,
    'adl_minum_obat'        => 205,
    'adl_mandi'             => 205,
    'adl_berpakaian'        => 205,
    'adl_transportasi'      => 205,
    'adl_edukasi_kesehatan' => 205,
    'adl_edukasi_lain'      => 205,

    // Blok E — kebutuhan edukasi
    'edukasi_obat_obat'          => 214,
    'edukasi_nutrisi'            => 214,
    'edukasi_perawatan_luka'     => 214,
    'edukasi_mobilisasi'         => 214,
    'edukasi_manajemen_nyeri'    => 214,
    'edukasi_insulin_sc'         => 214,
    'edukasi_lain_1'             => 214,
    'edukasi_lain_2'             => 214,

    // Blok F
    'catatan' => 77,   // reuse "Keterangan"
];
```

> **Objek 205 dipakai oleh 10 variabel dan objek 214 oleh 8 variabel.** Ini
> disengaja dan mengikuti preseden form 14 (`vap_1..vap_10` → objek berbeda)
> dan form 3 (`tanggal_covid_1`/`_2` → satu objek). Konsekuensi wajib:
> **dibaca dengan `EmrHelper::emrDetailByVariabel()`**, bukan
> `emrDetailByObjek()` yang akan collapsesemua nilai menjadi satu.

### 3.5 Akses EHR

```php
// Discharge Planning (form 17): Dokter & Perawat create/read/update/delete.
// WAJIB di-seed: EmrDashboardController INNER JOIN akses_ehr.
['profesi_id' => 1, 'form_id' => 17, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 2, 'form_id' => 17, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],

// Pemulangan Pasien (form 18): Dokter & Perawat create/read/update/delete.
['profesi_id' => 1, 'form_id' => 18, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 2, 'form_id' => 18, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
```

Tambahkan `EmrHelper::backfillObjekId(17);` dan `EmrHelper::backfillObjekId(18);`
di `EmrMasterSeeder` setelah loop mapping.

### 3.6 Validasi

```php
private function validated(Request $request): array
{
    return array_merge([
        'waktu_ren_pulang'                => null,
        'adl_edukasi_lain'                => null,
        'edukasi_lain_1'                  => null,
        'edukasi_lain_2'                  => null,
        'catatan'                         => null,
    ], $request->validate([
        'tanggal_masuk_rs'    => 'required|date',
        'diagnosis_medis'     => ['required', 'integer', 'exists:icd,icd_id'],
        'estimasi_hari_rawat' => 'required|integer|min:0|max:365',
        'tanggal_ren_pulang'  => 'required|date|after_or_equal:tanggal_masuk_rs',
        'waktu_ren_pulang'    => 'nullable|date_format:H:i',

        'pengaruh_pasien_kel' => ['required', Rule::in(['Ya', 'Tidak'])],
        'pengaruh_kerja'      => ['required', Rule::in(['Ya', 'Tidak'])],
        'pengaruh_keuangan'   => ['required', Rule::in(['Ya', 'Tidak'])],

        'antisipati_masalah'         => 'required|string|max:2000',
        'tinggal_sendiri'            => ['required', Rule::in(['Ya', 'Tidak'])],
        'membantu_pasien'            => ['required', Rule::in(['Ya', 'Tidak'])],
        'gunakan_alat_medis'         => ['required', Rule::in(['Ya', 'Tidak'])],
        'perlu_alat_bantu'           => ['required', Rule::in(['Ya', 'Tidak'])],
        'perlu_perawatan_khusus'     => ['required', Rule::in(['Ya', 'Tidak'])],
        'masalah_kebutuhan_pribadi' => ['required', Rule::in(['Ya', 'Tidak'])],
        'nyeri_kronis'               => ['required', Rule::in(['Ya', 'Tidak'])],
        'perlu_edukasi'              => ['required', Rule::in(['Ya', 'Tidak'])],
        'keterampilan_khusus'        => ['required', Rule::in(['Ya', 'Tidak'])],

        'catatan' => 'nullable|string|max:2000',
    ]));
}
```

Checkbox ADL & edukasi tidak divalidasi satu per satu — dibaca sebagai boolean
lalu dinormalisasi:

```php
foreach (['adl_menyiapkan_makanan', 'adl_makan', 'adl_diet', 'adl_menyiapkan_obat',
          'adl_minum_obat', 'adl_mandi', 'adl_berpakaian', 'adl_transportasi',
          'adl_edukasi_kesehatan', 'edukasi_obat_obat', 'edukasi_nutrisi',
          'edukasi_perawatan_luka', 'edukasi_mobilisasi', 'edukasi_manajemen_nyeri',
          'edukasi_insulin_sc'] as $flag) {
    $data[$flag] = $request->boolean($flag) ? 'Ya' : 'Tidak';
}
```

Aturan kondisional di `filteredData()`:

| Kondisi | Aturan |
|---|---|
| `perlu_ed-Etballsukai = 'Tidak'` | Seluruh 8 variabel Blok E **dibuang** dari payload. |
| `perlu_alat_bantu = 'Tidak'` | `catatan` tetap boleh tersimpan (dipakai umum). |
| `adl_edukasi_lain` kosong | Dibiarkan `null`; `array_intersect_key()` tetap memabulkannya sebagai null. |
| Field computed | Tidak ada field hitungan pada form ini. `waktu_masuk_rs`, `tanggal_masuk_rs`, `diagnosis_medis` di-set server dari `registrasi` + EMR terakhir, bukan dari browser. |

### 3.7 Cetak

**Wajib.** Formulir discharge planning biasa ditempel di chart pasien dan
menjadi bahan audit.

- Route: `GET /emr/discharge_planning/print/{emr_id}` →
  `DischargePlanningController@print`.
- View: `resources/views/moduls/EMR/DischargePlanning/print.blade.php`.
- Isi dokumen:
  1. Kop + judul **FORMULIR DISCHARGE PLANNING**.
  2. Identitas pasien & episode (No MR, nama, umur, DPJP, ruang, tanggal masuk).
  3. Diagnosa medis + estimasi hari dirawat + rencana tanggal pulang.
  4. Tabel **Pengaruh Perubahan Kondisi** (3 baris Ya/Tidak).
  5. Tabel **Kebutuhan Bantuan Sehari-hari** (10 baris checkbox, tanda centang).
  6. Tabel **Aspek Kebutuhan Pasien** (10 baris Ya/Tidak).
  7. Tabel **Kebutuhan Edukasi** (8 baris).
  8. Blok **Antisipasi Masalah** + **Catatan**.
  9. Blok tanda tangan perawat & dokter.

---

## 4. Form 18 — Pemulangan Pasien

### 4.1 Rasional & tujuan klinis

Form 18 adalah **dokumen yang dibawa pulang**. Ia merekam apa yang terjadi pada
saat pasien keluar, apa yang sudah diberikan, apa yang dibawa pulang,
dan kapan harus kontrol lagi.

Tujuan klinis di NusaMedika:

1. Membekukan **kondisi klinis saat pulang** (TTV, GCS, EWS, Skor Nyeri) sebagai
   pembanding dengan kondisi saat masuk — dasar penilaian outcome.
2. Merekam **bantuan yang dibutuhkan** (diet, eliminasi, luka, mobilisasi) agar
   perawat berikutnya tahu apa yang harus dilanjutkan.
3. Merekam **edukasi yang benar-benar diberikan** (bukan yang direncanakan),
   sesuai blok *EDUKASI/PENYULUHAN YANG SUDAH DIBERIKAN* di legacy.
4. Merekam **barang & hasil yang diserahkan** — mencegah klaim kehilangan
   hasil laboratorium/radiologi.
5. Menyediakan **resume pulang** untuk rujukan dan penjamin.

### 4.2 Struktur Field

#### Blok A — Identitas Waktu

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `tanggal_pulang` | 155 | Tanggal Observasi | date (readonly) | ya | **Reuse objek 155.** Legacy `tgl_pertemuan`. |
| `waktu_pulang` | 156 | Waktu Observasi | time (readonly) | ya | **Reuse objek 156.** Legacy `jam_pertemuan`. |

#### Blok B — Kondisi Saat Pulang

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `kondisi_pulang` | 215 | Kondisi Saat Pulang | select | ya | Legacy `alasan` (`indikatorPulang()`). Opsi memakai key SelectOption **`alasan_pulang`**. |
| `catatan_alasan_pulang` | 77 | Keterangan | textarea | ya | **Reuse objek 77.** |
| `dirujuk_ke` | 186 | Dirujuk Ke | radio | kondisional | **Reuse objek 186** (dibuat di form 16). Legacy `rujukannya`: `Puskesmas` / `Faskes` / `Rumah Sakit`. Wajib bila `kondisi_pulang = 'Dirujuk'`. |
| `rujuk_di_area_sama` | 186 | Dirujuk Ke | radio | kondisional | **Reuse objek 186** (variabel bersuffix). Legacy `makassar` (Ya/Tidak) — "Apakah rujukan berada di [kabupaten/kota]?". Wajib bila `kondisi_pulang = 'Dirujuk'`. |
| `meninggal` | 216 | Meninggal | radio | kondisional | Legacy `wafat`: `DOA` / `Meninggal Di IGD` / `< 48 Jam` / `> 48 Jam` / `Lahir Meninggal`. Wajib bila `kondisi_pulang = 'Meninggal'`. |
| `catatan_meninggal` | 77 | Keterangan | text | kondisional | **Reuse objek 77.** Legacy `tanggal_wafat` + `jam_wafat` digabung. Wajib bila `kondisi_pulang = 'Meninggal'`. |

#### Blok C — Tanda Vital & Skor Saat Pulang

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `td_sistolik` | 6 | Tekanan Darah Sistolik | number | ya | Reuse. Parameter EWS. |
| `td_diastolik` | 7 | Tekanan Darah Diastolik | number | ya | Reuse. |
| `nadi` | 10 | Nadi | number | ya | Reuse. Parameter EWS. |
| `pernapasan` | 12 | Pernapasan | number | ya | Reuse. Parameter EWS. |
| `suhu` | 11 | Suhu | decimal | ya | Reuse. Parameter EWS. |
| `skor_nyeri` | 140 | Skor Nyeri | select 0–10 | ya | Reuse. |
| `total_ews` | 142 | Total Skor EWS | readonly | — | **Turunan** `EwsHelper::hitung()`. |
| `kategori_ews` | 143 | Kategori Risiko EWS | badge readonly | — | **Turunan** `EwsHelper::KATEGORI_WARNA`. |
| `gcs_e` | 54 | GCS Eye | select 1–4 | ya | Reuse. |
| `gcs_m` | 55 | GCS Motorik | select 1–6 | ya | Reuse. |
| `gcs_v` | 56 | GCS Verbal | select 1–5 | ya | Reuse. |
| `gcs_jumlah` | 57 | GCS Score | readonly | — | **Turunan** = `gcs_e + gcs_m + gcs_v`. |

> `berat_badan` (objek 8) dan `tinggi_badan` (objek 9) **tidak** diisi di form 18 —
> legacy juga tidak memintanya pada sub-form ini. BMI (objek 58) tidak dihitung.

#### Blok D — Diet, Eliminasi, Luka & Mobilisasi

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `diet_jenis` | 217 | Diet / Nutrisi | select | ya | Legacy `diet`: `Oral` / `NGT` / `Diet Khusus`. **SelectOption key baru: `diet_jenis`.** |
| `diet_keterangan` | 217 | Diet / Nutrisi | text | kondisional | **Reuse objek 217.** Legacy `detail_diet` — batas cairan (ml/hari). Wajib bila `diet_jenis = 'Diet Khusus'`. |
| `bab` | 218 | Eliminasi BAB & BAK | select | ya | Legacy `BAB`: `Normal` / `Ileostomy / Colostomy`. **SelectOption key baru: `eliminasi_bab`.** |
| `bak` | 218 | Eliminasi BAB & BAK | select | ya | Legacy `BAK`: `Normal` / `Inkontinensia`. **SelectOption key baru: `eliminasi_bak`.** |
| `bak_keterangan` | 218 | Eliminasi BAB & BAK | text | kondisional | **Reuse objek 218.** Legacy `detail_BAK` — "Kateter, Tgl Pemasangan :". Wajib bila `bak = 'Inkontinensia'`. |
| `luka` | 219 | Luka / Operasi | select | ya | Legacy `luka`: `Bersih` / `Kering`. **SelectOption key baru: `kondisi_luka`.** |
| `luka_keterangan` | 219 | Luka / Operasi | textarea | kondisional | **Reuse objek 219.** Legacy `detail_luka` — "Ada Cairan dari Luka, Jelaskan :". Wajib bila `luka = 'Kering'`. |
| `transfer` | 220 | Transfer & Mobilisasi | select | ya | Legacy `transfer`: `Mandiri` / `Dengan Pengawasan` / `Dibantu Sebagian` / `Dibantu Penuh`. **SelectOption key baru: `tingkat_kemandirian`.** |

#### Blok E — Edukasi / Penyuluhan yang Sudah Diberikan (checkbox)

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `edukasi_1` | 221 | Edukasi / Penyuluhan yang Sudah Diberikan | checkbox | ya | *Penyakit & Pengobatannya*. |
| `edukasi_2` | 221 | Edukasi / Penyuluhan yang Sudah Diberikan | checkbox | ya | *Perawatan di rumah*. |
| `edukasi_3` | 221 | Edukasi / Penyuluhan yang Sudah Diberikan | checkbox | ya | *Perawatan Ibu & Bayi*. |
| `edukasi_4` | 221 | Edukasi / Penyuluhan yang Sudah Diberikan | checkbox | ya | *Mengatasi Nyeri*. |
| `edukasi_5` | 221 | Edukasi / Penyuluhan yang Sudah Diberikan | checkbox | ya | *Perawatan Luka*. |
| `edukasi_6` | 221 | Edukasi / Penyuluhan yang Sudah Diberikan | checkbox | ya | *Persiapan Lingkungan & fasilitas di rumah*. |
| `edukasi_7` | 221 | Edukasi / Penyuluhan yang Sudah Diberikan | checkbox | ya | *Nasehat Keluarga Berencana*. |
| `edukasi_keterangan` | 221 | Edukasi / Penyuluhan yang Sudah Diberikan | textarea | tidak | **Reuse objek 221.** Catatan tambahan materi edukasi. |

> Nomor variabel sengaja berupa `edukasi_1..7` (bukan nama semantik) mengikuti
> legacy, tetapi **label tampil** memakai teks lengkap agar tidak ambigu.

#### Blok F — Manajemen Nyeri

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `nyeri_terapi` | 222 | Manajemen Nyeri | textarea | ya | Legacy `obat` — "Obat yang diminum / anti nyeri". |
| `nyeri_efek_samping` | 222 | Manajemen Nyeri | textarea | ya | Legacy `efek` — "Efek samping yang mungkin timbul". |
| `nyeri_kapan_ke_rs` | 222 | Manajemen Nyeri | textarea | ya | Legacy `ke_rs` — "Bila nyeri bertambah berat segera ke RS". |

Wajib **bila** EMR Discharge Planning (form 17) terakhir untuk episode ini
memiliki `nyeri_kronis = 'Ya'` atau `Manajemen Nyeri` tercentang.
Bila tidak, blok disembunyikan dan ketiga variabel dibuang dari payload.

#### Blok G — Anjuran & Masalah Keperawatan Selama Dirawat

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `masalah_keperawatan` | 224 | Masalah Keperawatan / Kebidanan Selama Dirawat | select2 multi | ya | Legacy `diagnosa_pulang.php` — input `detail_emr[nyeri][17]`. Sumber daftar: Master Implementasi / diagnose keperawatan bila tersedia; sementara diisi free-tag dari `SelectOption`. |
| `anjuran_pulang` | 5 | Instruksi (I) | textarea | ya | **Reuse objek 5.** Legacy `anjuran` — anjuran keperawatan setelah pulang. |
| `keterangan` | 77 | Keterangan | textarea | tidak | **Reuse objek 77.** |

#### Blok H — Barang & Hasil yang Disyerahkan kepada Keluarga

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `serah_lab` | 223 | Barang & Hasil yang Disyerahkan | number (lembar) | tidak | Legacy `hasil_lab`. |
| `serah_rontgen` | 223 | Barang & Hasil yang Disyerahkan | number (lembar) | tidak | Legacy `rontgen`. |
| `serah_ct_scan` | 223 | Barang & Hasil yang Disyerahkan | number (lembar) | tidak | Legacy `ct_scan`. |
| `serah_mri` | 223 | Barang & Hasil yang Disyerahkan | number (lembar) | tidak | Legacy `mri`. |
| `serah_usg` | 223 | Barang & Hasil yang Disyerahkan | number (lembar) | tidak | Legacy `usg` — "Hasil USG / ECHO". |
| `serah_surat_sakit` | 223 | Barang & Hasil yang Disyerahkan | number (lembar) | tidak | Legacy `surat_sakit`. |
| `serah_surat_asuransi` | 223 | Barang & Hasil yang Disyerahkan | checkbox | ya | Legacy `asuransi` (Ada/Tidak). |
| `serah_resume` | 223 | Barang & Hasil yang Disyerahkan | checkbox | ya | Legacy `resume` — "Resume Pasien Pulang". |
| `serah_buku_bayi` | 223 | Barang & Hasil yang Disyerahkan | checkbox | ya | Legacy `buku_bayi`. |
| `serah_gol_darah` | 223 | Barang & Hasil yang Disyerahkan | checkbox | ya | Legacy `Gol_darah` — "Kartu Gol. Darah". |
| `serah_skl_bayi` | 223 | Barang & Hasil yang Disyerahkan | checkbox | ya | Legacy `skk_bayi` — "Surat Keterangan Lahir". |
| `serah_penyerah_bayi` | 223 | Barang & Hasil yang Disyerahkan | text | tidak | Legacy `penyerah_bayi` — "Bayi diserahkan oleh". |
| `serah_lainnya` | 223 | Barang & Hasil yang Disyerahkan | text | tidak | Legacy `lain_pulang` — "Lain-lain". |

#### Blok I — Rencana Kontrol (read-only, **tidak ada mapping**)

Menampilkan daftar form 8 Konsultan dengan
`jenis_konsultasi = 'RENCANA_KONTROL'` pada `registrasi_id` episode yang sama —
sumber data yang sama dengan `kontrol_pulang.php`. Kolom: No, Tanggal & Jam,
Nama Dokter & Spesialisasi, Poli Tujuan, plus catatan *"Harap datang 1 jam sebelum
jam pemeriksaan untuk melakukan pendaftaran"*.

### 4.3 Dashboard & Form Master

Sudah ditulis di §3.3. Ringkas:

```php
['dashboard_menu_sub_id' => 34, 'dashboard_menu_id' => 6, 'nama_sub_menu' => 'Pemulangan Pasien'],

['form_id' => 18, 'nama_form' => 'Pemulangan Pasien', 'slug' => 'pemulangan_pasien', 'id_dash_menu' => '6.34', 'ri' => 1, 'rj' => 0, 'igd' => 0, 'mcu' => 0],
```

### 4.4 Mapping

```php
$mapping[18] = [
    // Blok A
    'tanggal_pulang'      => 155,  // reuse "Tanggal Observasi"
    'waktu_pulang'        => 156,  // reuse "Waktu Observasi"

    // Blok B — kondisi pulang
    'kondisi_pulang'       => 215,
    'catatan_alasan_pulang'=> 77,   // reuse "Keterangan"
    // Objek 186 "Dirujuk Ke" DIDEKLARASIKAN di KONSEP_CATATAN_MEDIS_LANJUTAN.md
    // (form 16) — di sini hanya me-reuse. Sah: satu-satunya objek > 177 yang
    // boleh dipakai lintas dokumen (ALOKASI_ID_GLOBAL.md §1).
    'dirujuk_ke'          => 186,  // reuse objek form 16 "Dirujuk Ke"
    'rujuk_di_area_sama'  => 186,  // reuse (variabel bersuffix)
    'meninggal'           => 216,
    'catatan_meninggal'   => 77,   // reuse "Keterangan"

    // Blok C — TTV saat pulang
    'td_sistolik'         => 6,
    'td_diastolik'        => 7,
    'nadi'                => 10,
    'pernapasan'          => 12,
    'suhu'                => 11,
    'skor_nyeri'          => 140,
    'total_ews'           => 142,  // TURUNAN
    'kategori_ews'        => 143,  // TURUNAN
    'gcs_e'               => 54,
    'gcs_m'               => 55,
    'gcs_v'               => 56,
    'gcs_jumlah'          => 57,   // TURUNAN

    // Blok D — diet, eliminasi, luka, mobilisasi
    'diet_jenis'          => 217,
    'diet_keterangan'     => 217,
    'bab'                 => 218,
    'bak'                 => 218,
    'bak_keterangan'      => 218,
    'luka'                => 219,
    'luka_keterangan'     => 219,
    'transfer'            => 220,

    // Blok E — edukasi yang sudah diberikan
    'edukasi_1'           => 221,
    'edukasi_2'           => 221,
    'edukasi_3'           => 221,
    'edukasi_4'           => 221,
    'edukasi_5'           => 221,
    'edukasi_6'           => 221,
    'edukasi_7'           => 221,
    'edukasi_keterangan'  => 221,

    // Blok F — manajemen nyeri
    'nyeri_terapi'        => 222,
    'nyeri_efek_samping'  => 222,
    'nyeri_kapan_ke_rs'   => 222,

    // Blok G
    'masalah_keperawatan'  => 224,
    'anjuran_pulang'      => 5,    // reuse "Instruksi (I)"
    'keterangan'          => 77,   // reuse "Keterangan"

    // Blok H — barang & hasil diserahkan
    'serah_lab'            => 223,
    'serah_rontgen'        => 223,
    'serah_ct_scan'        => 223,
    'serah_mri'            => 223,
    'serah_usg'            => 223,
    'serah_surat_sakit'    => 223,
    'serah_surat_asuransi' => 223,
    'serah_resume'         => 223,
    'serah_buku_bayi'      => 223,
    'serah_gol_darah'      => 223,
    'serah_skl_bayi'       => 223,
    'serah_penyerah_bayi'  => 223,
    'serah_lainnya'        => 223,
];
```


### 4.5 Akses EHR

```php
['profesi_id' => 1, 'form_id' => 18, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 2, 'form_id' => 18, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
```

### 4.6 Validasi

```php
private function validated(Request $request): array
{
    return array_merge([
        'rujuk_di_area_sama'    => null,
        'meninggal'             => null,
        'catatan_meninggal'     => null,
        'diet_keterangan'       => null,
        'bak_keterangan'        => null,
        'luka_keterangan'       => null,
        'edukasi_keterangan'    => null,
        'nyeri_terapi'          => null,
        'nyeri_efek_samping'    => null,
        'nyeri_kapan_ke_rs'     => null,
        'serah_penyerah_bayi'   => null,
        'serah_lainnya'         => null,
        'keterangan'            => null,
    ], $request->validate([
        'kondisi_pulang'       => ['required', Rule::in(array_column(SelectOption::get('alasan_pulang'), 'value'))],
        'catatan_alasan_pulang'=> 'required|string|max:1000',

        'td_sistolik'          => 'required|integer|min:40|max:300',
        'td_diastolik'         => 'required|integer|min:20|max:200',
        'nadi'                 => 'required|integer|min:20|max:250',
        'pernapasan'           => 'required|integer|min:5|max:60',
        'suhu'                 => 'required|numeric|min:30|max:45',
        'skor_nyeri'           => 'required|integer|min:0|max:10',
        'gcs_e'                => 'required|integer|min:1|max:4',
        'gcs_m'                => 'required|integer|min:1|max:6',
        'gcs_v'                => 'required|integer|min:1|max:5',

        'diet_jenis'           => ['required', Rule::in(['Oral', 'NGT', 'Diet Khusus'])],
        'bab'                  => ['required', Rule::in(['Normal', 'Ileostomy / Colostomy'])],
        'bak'                  => ['required', Rule::in(['Normal', 'Inkontinensia'])],
        'luka'                 => ['required', Rule::in(['Bersih', 'Kering'])],
        'transfer'             => ['required', Rule::in(['Mandiri', 'Dengan Pengawasan', 'Dibantu Sebagian', 'Dibantu Penuh'])],

        'masalah_keperawatan'   => 'required|array|min:1',
        'anjuran_pulang'       => 'required|string|max:3000',

        'serah_lab'            => 'nullable|integer|min:0|max:99',
        'serah_rontgen'        => 'nullable|integer|min:0|max:99',
        'serah_ct_scan'        => 'nullable|integer|min:0|max:99',
        'serah_mri'            => 'nullable|integer|min:0|max:99',
        'serah_usg'            => 'nullable|integer|min:0|max:99',
        'serah_surat_sakit'    => 'nullable|integer|min:0|max:99',
    ]));
}
```

Checkbox & angka dinormalisasi di `filteredData()`:

```php
foreach (['serah_surat_asuransi', 'serah_resume', 'serah_buku_bayi',
          'serah_gol_darah', 'serah_skl_bayi', 'edukasi_1', 'edukasi_2',
          'edukasi_3', 'edukasi_4', 'edukasi_5', 'edukasi_6', 'edukasi_7'] as $flag) {
    $data[$flag] = $request->boolean($flag) ? 'Ya' : 'Tidak';
}
```

Aturan kondisional di `filteredData()`:

| Kondisi | Aturan |
|---|---|
| `kondisi_pulang = 'Dirujuk'` | `dirujuk_ke` wajib; `rujuk_di_area_sama` wajib. Selain itu keduanya dibuang. |
| `kondisi_pulang = 'Meninggal'` | `meninggal` + `catatan_meninggal` wajib. Selain itu dibuang. |
| Selain dua di atas | `dirujuk_ke`, `rujuk_di_area_sama`, `meninggal`, `catatan_meninggal` dibuang. |
| `diet_jenis ≠ 'Diet Khusus'` | `diet_keterangan` dibuang. |
| `bak = 'Normal'` | `bak_keterangan` dibuang. |
| `luka = 'Bersih'` | `luka_keterangan` dibuang. |
| Form 17 terakhir: `nyeri_kronis = 'Tidak'` | `nyeri_terapi`, `nyeri_efek_samping`, `nyeri_kapan_ke_rs` dibuang. |
| `tanggal_pulang`, `waktu_pulang` | Di-set dari `Carbon::now()`; nilai browser **tidak dipakai**. |
| Field computed | `total_ews`, `kategori_ews`, `gcs_jumlah` dihitung ulang di server. |

**Prasyarat "persiapan pulang"** — mengikuti legacy:

```php
// Pemulangan hanya boleh diisi bila pasien sudah berstatus persiapan pulang.
$bedLog = BedLog::where('pasien_id', $pasienId)
    ->where('registrasi_detail_id', $registrasi_detail_id)
    ->where('status_bed_log', BedLog::STATUS_PERSIAPAN_PULANG) // konstanta existing
    ->where(fn ($q) => $q->whereNull('status_batal')->orWhere('status_batal', 0))
    ->latest('bed_log_id')->first();

abort_if($isBaru && ! $bedLog, 422,
    'Persiapkan pulang terlebih dahulu dari Bed Management sebelum mengisi dokumen pemulangan.');
```

Bila tabel `bed_log` belum tersedia di DB target, guard ini dibuat **opsional**
di balik config `EMR_PEMULANGAN_CEK_BED_LOG` (default `true`) agar implementasi
tidak terhenti.

### 4.7 Cetak

**Wajib.** Dokumen ini diberikan kepada pasien/keluarga.

- Route: `GET /emr/pemulangan_pasien/print/{emr_id}` →
  `PemulanganPasienController@print`.
- View: `resources/views/moduls/EMR/PemulanganPasien/print.blade.php`.
- Isi dokumen (mengikuti urutan `$dataForm` legacy):
  1. Kop + judul **SUMMARY PEMULANGAN PASIEN**.
  2. Identitas pasien, episode, DPJP, tanggal & jam pulang.
  3. **Kondisi Saat Pulang** — alasan pulang, catatan, rujukan/meninggal.
  4. Tanda vital, skala nyeri, EWS, GCS.
  5. **Diet / Nutrisi**, **Eliminasi**, **Luka / Operasi**, **Transfer & Mobilisasi**.
  6. **Edukasi / Penyuluhan yang Sudah Diberikan** — daftar centang.
  7. **Masalah Keperawatan / Kebidanan Selama Dirawat**.
  8. **Manajemen Nyeri** (bila ada).
  9. **Anjuran Keperawatan Setelah Pulang**.
  10. **Barang & Hasil yang Disyerahkan**.
  11. **Rencana Kontrol Selanjutnya** (read-only) + catatan "Harap datang 1 jam sebelum jam pemeriksaan".
  12. Blok tanda tangan perawat & dokter.

---

## 5. Implementasi

Tidak ada migration baru dan tidak ada route EMR manual. Route `print` mengikuti
pola `Soap` dan `Konsultasi`.

### 5.1 Form 17 — Discharge Planning

- [ ] `database/seeders/EmrMasterSeeder.php` — `$menus` tambah baris 6; `$subMenus` tambah baris **33 & 34** (bukan 1 & 2); objek 200–214; `$mapping[17]`; 2 baris `$akses`.
- [ ] `app/Helpers/SelectOption.php` — tidak ada key baru (Blok B/C/D/E memakai array `Ya`/`Tidak` dan checkbox langsung; `alasan_pulang` sudah ada).
- [ ] `app/Http/Controllers/EMR/DischargePlanning/DischargePlanningController.php` — `index/store/update/destroy/print`, plus `diagnosisPrefill()` (mengambil diagnosa primer dokter terakhir) dan `form18Preview()`.
- [ ] `resources/views/moduls/EMR/DischargePlanning/index.blade.php` — accordion: Identitas & Perencanaan, Pengaruh, Kebutuhan Bantuan Sehari-hari, Aspek Kebutuhan, Kebutuhan Edukasi, Catatan.
- [ ] `resources/views/moduls/EMR/DischargePlanning/print.blade.php`.
- [ ] `routes/web.php` — `Route::get('/emr/discharge_planning/print/{emr_id}', [DischargePlanningController::class, 'print'])->name('emr.discharge_planning.print');`

### 5.2 Form 18 — Pemulangan Pasien

- [ ] `EmrMasterSeeder.php` — objek 215–224; `$mapping[18]`; 2 baris `$akses`.
- [ ] `app/Helpers/SelectOption.php` — 5 key baru: `diet_jenis`, `eliminasi_bab`, `eliminasi_bak`, `kondisi_luka`, `tingkat_kemandirian`.
- [ ] `app/Http/Controllers/EMR/PemulanganPasien/PemulanganPasienController.php` — `index/store/update/destroy/print`, plus `syaratPulang()`, `dischargeTerakhir()`, `rencanaKontrol()`.
- [ ] `resources/views/moduls/EMR/PemulanganPasien/index.blade.php` — 8 accordion sesuai blok §4.2.
- [ ] `resources/views/moduls/EMR/PemulanganPasien/print.blade.php`.
- [ ] `routes/web.php` — `emr.pemulangan_pasien.print`.

### 5.3 SelectOption — key baru

```php
'diet_jenis' => [
    ['value' => 'Oral',          'label' => 'Oral'],
    ['value' => 'NGT',           'label' => 'Nasogastric Tube (NGT)'],
    ['value' => 'Diet Khusus',   'label' => 'Diet Khusus'],
],
'eliminasi_bab' => [
    ['value' => 'Normal',                    'label' => 'Normal'],
    ['value' => 'Ileostomy / Colostomy',    'label' => 'Ileostomy / Colostomy'],
],
'eliminasi_bak' => [
    ['value' => 'Normal',        'label' => 'Normal'],
    ['value' => 'Inkontinensia', 'label' => 'Inkontinensia'],
],
'kondisi_luka' => [
    ['value' => 'Bersih', 'label' => 'Bersih'],
    ['value' => 'Kering', 'label' => 'Kering'],
],
'tingkat_kemandirian' => [
    ['value' => 'Mandiri',           'label' => 'Mandiri'],
    ['value' => 'Dengan Pengawasan',  'label' => 'Dengan Pengawasan'],
    ['value' => 'Dibantu Sebagian',   'label' => 'Dibantu Sebagian'],
    ['value' => 'Dibantu Penuh',      'label' => 'Dibantu Penuh'],
],
```

### 5.4 Review sebelum merge (PANDUAN §4)

- [ ] `Str::slug('Discharge Planning','_') === 'discharge_planning'`.
- [ ] `Str::slug('Pemulangan Pasien','_') === 'pemulangan_pasien'`.
- [ ] `id_dash_menu` `'6.33'` dan `'6.34'` cocok dengan PK yang benar-benar di-seed
      (menu 6 + sub 33/34).
- [ ] Sub 33 & 34 **tidak** bentrok dengan baris `dashboard_menu_sub` yang sudah ada
      (`DB::table('dashboard_menu_sub')->whereIn('dashboard_menu_sub_id',[33,34])`).
- [ ] `form.slug` == `Str::slug(nama_sub_menu)` untuk kedua form.
- [ ] Semua `variabel` unik per form — perhatikan `edukasi_1..7` (form 18) **tidak** boleh bentrok dengan `edukasi_*` (form 17); keduanya pada form berbeda sehingga aman.
- [ ] `array_intersect_key()` di `filteredData()`.
- [ ] Field computed (`total_ews`, `kategori_ews`, `gcs_jumlah`) dihitung ulang di server.
- [ ] `tanggal_masuk_rs`/`waktu_masuk_rs`/`diagnosis_medis` diisi dari master, bukan browser.
- [ ] `@error()` ada di setiap field; `<fieldset {{ $isView ? 'disabled' : '' }}>`; `old('x', $emr_data['x'] ?? '')`.
- [ ] `docker compose exec app ./vendor/bin/pint --dirty`.
- [ ] Entri `AGENTS.md` diperbarui.

---

## 6. Catatan & Risiko

### 6.1 Risiko alokasi objek

| Risiko | Mitigasi |
|---|---|
| Objek 200–224 bentrok dengan dokumen lain. | Rentang dinyatakan eksplisit di §1. Verifikasi dengan `DB::table('objek')->where('objek_id','>',199)->orderBy('objek_id')->get()` sebelum menjalankan seeder. |
| Objek 205 dipakai 10 variabel, objek 214 dipakai 8, objek 221 8, objek 223 13. | **Wajib** baca via `EmrHelper::emrDetailByVariabel()`. Laporan antar-form berbasis objek akan melihat banyak baris dengan `objek_id` sama — itu konsekuensi yang diharapkan, bukan bug. |
| Objek 186 dipakai dua form (16 & 18). | Objek bersifat global; `objek_form_control` memetakan per form, jadi tidak bentrok. **Objek 186 dideklarasikan sekali di `KONSEP_CATATAN_MEDIS_LANJUTAN.md`** dan di-*reuse* di sini — jangan menambah baris `186 => 'Dirujuk Ke'` di `$objeks` dokumen ini (`ALOKASI_ID_GLOBAL.md` §1). Query laporan **wajib** memfilter `form_id`. |

### 6.2 Risiko menu

| Risiko | Mitigasi |
|---|---|
| Menu 6 belum ada → `id_dash_menu = '6.33'` yatim. | `$menus` harus ditambah **pada seeder yang sama** dengan `$subMenus`. Jangan hanya menambah form. |
| Sub 1–2 terlihat "kosong" di menu 6 tapi **tidak boleh dipakai** — keduanya sudah jadi milik menu 1–5. | `dashboard_menu_sub_id` bersifat **global**. Band dokumen ini 33–52 (`ALOKASI_ID_GLOBAL.md` §3); sub 33/34 dipilih agar `id_dash_menu` = `6.33`/`6.34`. |
| Menu 1–5 punya `dashboard_menu_id` 1–5; memilih ID 6 bentrok bila tabel sudah punya baris lain dengan ID 6. | Cek `DB::table('dashboard_menu')->where('dashboard_menu_id', 6)->first()` sebelum seeding. Bila sudah ada dengan nama lain, gunakan ID berikutnya dan sesuaikan `id_dash_menu` di kedua form. |
| Perbandingan `id_dash_menu` dengan `==`. | `"6.33" == "6.3"` tidak mungkin terjadi saat ini, tapi tetap gunakan `===` di `AksesEhrController` agar aman bila sub 3 ditambahkan nanti. |

### 6.3 Risiko klinis & operasional

| Risiko | Mitigasi |
|---|---|
| Discharge planning diisi terlambat (saat sudah pulang). | Panel menampilkan tanggal & umur data discharge planning terakhir; bila sudah lewat, tampilkan badge "Perlu Diperbarui". |
| Pasien pulang tanpa dokumen pemulangan. | Guard `syaratPulang()` menolak penyimpanan bila `bed_log` belum berstatus persiapan pulang. Jika `bed_log` belum tersedia di DB target, guard dibuat opsional. |
| Status "dirujuk" tanpa tujuan rujukan. | Aturan kondisional §4.6 menolak penyimpanan. |
| Medication handover tidak tercatat. | Blok *Manajemen Nyeri* & *Anjuran Pulang* (objek 5) — integrasi penuh dengan Order Resep (form 5) untuk daftar obat pulang adalah pekerjaan lanjutan, tidak masuk dokumen ini. |
| Kartu kendali diisi ganda. | `filteredData()` membuang seluruh checkbox yang tidak terkirim; tidak ada baris `value = ''` yang tersimpan. |

### 6.4 Risiko performa

| Risiko | Mitigasi |
|---|---|
| `form 18` memuat 8 blok + data turunan dari `bed_log` + form 8 Konsultasi. | Gunakan `EmrHelper::latestEmr()` + `latestValuesByVariabel()` untuk form 17/8, dan query berindeks `bed_log (pasien_id, registrasi_detail_id, status_bed_log)`. |
| Tabel `objek` tumbuh ke ± 500 baris. | administrator → Manajemen EMR → Form sudah menyediakan tab Objek; tambahkan filter bila perlu. |

### 6.5 Yang **tidak** dicakup dokumen ini

| Topik | Ditujukan untuk |
|---|---|
| Resume Keperawatan (`resume_keperawatan.php`) | `KONSEP_ASUHAN_KEPERAWATAN_LANJUTAN.md` |
| Asesmen kebutuhan edukasi yang terpisah (`asesmen_kebutuhan_edukasi.php`) | `KONSEP_ASUHAN_KEPERAWATAN_LANJUTAN.md` (form 31) |
| Home Care & Perencanaan Pulang (`home_care`, `perencanaan_pulang`) | Dokumen konsep tersendiri (belum dialokasikan) |
| Transfer pasien antar ruang (`transfer_pasien_antar_ruangan.php`) | `KONSEP_BEDAH_ANESTESI.md` (form 94) |
