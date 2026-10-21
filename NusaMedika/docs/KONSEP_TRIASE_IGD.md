# Konsep Form EMR: Triage IGD

**Status:** Rancangan / Konsep — belum diimplementasikan
**Tanggal:** 2026-10-10
**Acuan:** [`PANDUAN_IMPLEMENTASI_FORM_EMR.md`](PANDUAN_IMPLEMENTASI_FORM_EMR.md),
[`ANALISIS_KEKURUGAN_FORM.md`](ANALISIS_KEKURANGAN_FORM.md) §3 P1 & §2 domain 18,
[`KONSEP_GAWAT_DARURAT.md`](KONSEP_GAWAT_DARURAT.md),
[`AGENTS.md`](../AGENTS.md)
**Sumber data:** SIMRS Tenriawaru (legacy PHP/PostgreSQL) di
`/simrs_tenriawaru/FE/lib/modul/`

---

## 1. Ringkasan

Satu form: triase IGD. Domain "IGD & triase" di NusaMedika saat ini hanya berupa
**data registrasi** — `registrasi_detail.prioritas`, `.triase`, `.lokasi_rawat`,
`.cara_masuk` — tanpa **form klinis** yang mencatat pengkajian awal pasien
sebelum prioritas ditetapkan.

| form_id | nama_form | slug | id_dash_menu | ri | rj | igd | mcu | objek baru |
|---|---|---|---|---|---|---|---|---|
| 19 | Triage IGD | `triage_igd` | `7.53` | 0 | 0 | 1 | 0 | 225–235 (11) |

> **Menu BARU.** `dashboard_menu_id = 7` dengan `nama_menu = 'Gawat Darurat'`
> (`ALOKASI_ID_GLOBAL.md` §2). Sub-menu **53 = Triage IGD** (tanpa extra, dipakai
> form 19) dan **54 = Triage IGD OBGYN** (tanpa extra, **belum dipakai form apa
> pun** — dicadangkan untuk `KONSEP_OBSTETRI_NEONATAL.md`). Alasan §4.3.
>
> **Band sub-menu.** `dashboard_menu_sub_id` bersifat **GLOBAL**, bukan per-menu
> (`ALOKASI_ID_GLOBAL.md` §0 dan §3). Sub 1–12 sudah dipakai seeder baseline dan
> **tidak boleh** dipakai ulang; band dokumen ini adalah **53–72**. Karena itu
> kedua sub di sini di-number **53** dan **54**, bukan 1 dan 2. `id_dash_menu`
> dihitung ulang menjadi `7.53`.
>
> **Rentang objek.** Dokumen ini memakai `objek_id` **225–235** (11 objek baru).
> Hampir seluruh tanda vital, GCS, EWS, Saturasi, Kesadaran, dan Nyeri
> **me-reuse objek 1–177**;
> hanya konsep yang belum punya padanan yang dibuat baru.

### Ringkasan objek baru & alasan

| objek_id | nama_objek | Alasan tidak bisa reuse objek yang ada |
|---|---|---|
| 225 | Keadaan Umum | Tidak ada objek "Keadaan Umum / KU" pada rentang 1–177 |
| 226 | Jalan Nafas | Objek 19 adalah "Cara Pemberian Oksigen", bukan patent jalan napas |
| 227 | Sirkulasi | Tidak ada objek "Sirkulasi"; nadi (objek 10) hanya menyimpan angka |
| 228 | Akral | Tidak ada objek "Akral" |
| 229 | Pupil | Tidak ada objek "Pupil" |
| 230 | Refleks Cahaya | Tidak ada objek "Refleks Cahaya" |
| 231 | Jenis Anamnesis | Objek 63 "Alloanamnesa" hanya dipakai form pengkajian awal keperawatan, bukan triase |
| 232 | Lokasi Nyeri | Objek 14 "Nyeri" & 140 "Skor Nyeri" sudah ada; lokasi belum |
| 233 | Frekuensi Nyeri | Tidak ada |
| 234 | Karakteristik Nyeri | Tidak ada |
| 235 | Prioritas Triase IGD | `registrasi_detail.prioritas` **bukan** objek EMR: nilainya per registrasi, bukan per pengkajian, dan tidak bisa di-history |

### Objek yang di-reuse (ringkas)

| Konsep | objek_id |
|---|---|
| Tanggal / Waktu pengkajian | 155 / 156 |
| Berat badan / Tinggi badan | 8 / 9 |
| Suhu | 11 |
| Frekuensi napas | 12 |
| Saturasi | 15 |
| Nadi | 10 |
| Tekanan darah | 6 / 7 |
| Kesadaran | 51 |
| Skor nyeri | 140 |
| Keluhan utama / alasan kunjungan | 13 |
| Riwayat penyakit | 42 |
| Nyeri Ya/Tidak | 14 |
| Risiko jatuh (tingkat) | 91 |
| Keterangan | 77 |

---

## 2. Sumber Referensi Legacy

| File legacy | Kontribusi ke desain |
|---|---|
| `FE/lib/modul/entry_triage_igd.php` | Sumber utama struktur form (sekitar 1.400 baris markup). Enam section berurutan: **PEMERIKSAAN FISIK**, **KELUHAN UTAMA**, **SKALA NYERI**, **RIWAYAT PENYAKIT**, **SKRINING RESIKO JATUH**, **ZONA TRIAGE IGD**. Field yang dipetakan ke `detail_emr[...]`: `tgl_pertemuan`, `jam_pertemuan`, `ku`, `bb`, `tb`, `suhu`, `jalan_nafas`, `tipe_nafas`, `pernapasan`, `frekuensi_nafas`, `saturasi`, `sirkulasi`, `akral`, `nadi`, `sistolik`, `diastolik`, `detail_kesadaran`, `pupil`, `refleksi_cahaya`, `anamnesis`, `keluhan`, `detail_keluhan`, `riwayat`, `detail_riwayat`, `nyeri`, `lokasi_nyeri`, `skala_nyeri`, `frekuensi_nyeri`, `karakteristik_nyeri`, `resiko_jatuh`, `triage`. |
| `FE/lib/modul/triage_igd_cak.php` | Widget **ZONA TRIAGE IGD** (98 baris). Tabel 5 kolom: *Prioritas Triage · Keterangan · Zona Perawatan · Respon Time*. Enam baris opsi: `1` Prioritas 1 / Resuscitation / Zona Merah / Segera; `2` Prioritas 2 / Emergency / Zona Merah / < 10 Menit; `3` Prioritas 3 / Urgent / Zona Kuning / < 30 Menit; `4` Prioritas 4 / Semi Urgent / Zona Kuning / < 60 Menit; `5` Prioritas 5 / Non Urgent / Poliklinik / < 120 Menit; `HITAM` / DOA / – / –. Radio `name="detail_emr[triage]"`. |
| `FE/lib/modul/catatan_triage_igd.php` | Shell daftar riwayat pengkajian triase IGD. Menetapkan dua aturan operasional: (1) tombol EDIT/HAPUS **nonaktif** bila usia catatan > 23 jam; (2) meskipun tombol tetap tampil, aksi hanya aktif bila `pegawai_id` penulis catatan sama dengan pengguna aktif. |
| `FE/lib/func/keperawatan_template.php` → `head_pasien_dalam_form()` | Header read-only di atas form: No MR, Nama Pasien, Tempat/Tanggal Lahir + Umur, Tanggal Layanan, Nasabah + Kelas, No Bukti Layanan (SEP). |
| `FE/lib/func/keperawatan_template.php` → `assesment_perawat_dalam_form()` | Ringkasan read-only pengkajian awal keperawatan yang juga dirender di `dar_entry.php`; di NusaMedika dipakai sebagai panel konteks. |
| `FE/lib/modul/dar_entry.php` | Menunjukkan pola header read-only yang sama dipakai pada form IGD lain — dipakai untuk konsistensi tampilan. |

### Daftar opsi yang diambil persis dari legacy

| Field | Opsi (nilai → label) |
|---|---|
| Keadaan Umum | `Baik`, `Sedang`, `Lemas` |
| Jalan Nafas | `N/A`, `PATEN`, `GURGLING`, `SNORRING`, `STRIDOR` |
| Tipe Nafas | `N/A` (legacy hanya menyediakan satu opsi) |
| Sirkulasi | `Nadi Teraba`, `Nadi Tidak Teraba` |
| Tingkat Kesadaran | `1`, `2`, `3`, `4`, `5` |
| Pupil | `Baik`, `Tidak Baik`, `Lambat` |
| Alasan Kunjungan | `Lemas`, `Demam`, `Pusing`, `Sakit Kepala`, `Batuk`, `Pilek`, `Sesak Napas`, `Kontrol`, `Obat Habis`, `Lainnya` |
| Riwayat Penyakit | `Hipertensi`, `DM`, `Typhoid`, `DHF`, `TB`, `Stroke`, `Asma`, `Lainnya` |
| Nyeri | `Ya, Nyeri`, `Tidak Nyeri` |
| Skala Nyeri | `0` … `10` |
| Frekuensi Nyeri | `Sering`, `Kadang`, `Jarang` |
| Karakteristik Nyeri | `Terbakar`, `Tertusuk`, `Tertindih`, `Menyebar`, `Berdenyut` |
| Risiko Jatuh | `Beresiko`, `Tidak Beresiko` (radio `value=1` / `value=2`) |
| Anamnesis | `Auto Anamnesis`, `Allo Anamnesis` |
| Prioritas Triase | 6 baris pada tabel §4.2 |

---

## 3. Rasional & tujuan klinis

Triase adalah **langkah pertama** pasien bertemu tenaga kesehatan, dan
keputusannya menentukan **waktu tunggu** dan **lokasi perawatan**. NusaMedika
kini mencatat prioritas IGD di level registrasi saja — tanpa pengkajian klinis
yang menjadi dasar keputusan prioritas tersebut. Akibatnya, triase tidak dapat
audit, tidak dapat dipakai sebagai data mutu pelayanan, dan tidak pernah
menjadi rujukan form berikutnya.

Tujuan klinis di NusaMedika:

1. **Merekam bukti objektif** atas keputusan prioritas: keadaan umum, jalan
   napas, sirkulasi, kesadaran, pupil, refleks cahaya.
2. **Mencatat keluhan utama dan riwayat penyakit** sebagai dasar anamnesis
   yang dipakai form SOAP, DAR, dan form 57 Visum.
3. **Memberi baseline tanda vital** yang dibandingkan dengan triase berikutnya
   (retriase) untuk menilai respons terapi.
4. **Menjadi rujukan** untuk memverifikasi nilai `registrasi_detail.prioritas`
   agar daftar antrean IGD dan form ini tidak berbeda pendapat.
5. **Menyediakan skrining risiko jatuh** di pintu masuk — hal yang sering
   terlewat karena fokus petugas IGD pada tanda vital saja.

---

## 4. Form 19 — Triage IGD

### 4.1 Rasional tambahan: bentuk form

Legacy memecah pengkajian triase menjadi **dua form terpisah**: daftar riwayat
(`catatan_triage_igd.php`) dan entry pengkajian (`entry_triage_igd.php`) yang
dipanggil lewat AJAX. NusaMedika tidak memiliki pola AJAX terpisah — karena
route generik `/emr/form/{slug}/{registrasi_detail_id}/{emr_id}` sudah
menyediakan daftar riwayat lewat komponen `x-emr-history-table`. Maka form 19
adalah **satu form** dengan panel riwayat di kiri, mengikuti seluruh form EMR
nama lain (`ImplementasiKeperawatan`, `SBAR`, `TandaVital`).

### 4.2 Struktur Field

#### Blok A — Waktu Pengkajian

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `tanggal_triase` | 155 | Tanggal Observasi | date | ya | **Reuse objek 155.** Legacy `tgl_pertemuan`. Default `today()`, boleh diubah (triase susulan). |
| `waktu_triase` | 156 | Waktu Observasi | time (H:i) | ya | **Reuse objek 156.** Legacy `jam_pertemuan`. |

#### Blok B — Pemeriksaan Fisik

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `keadaan_umum` | 225 | Keadaan Umum | select | ya | Legacy `ku`: `Baik` / `Sedang` / `Lemas`. **SelectOption key baru: `keadaan_umum_igd`.** |
| `berat_badan` | 8 | Berat Badan | number | ya | Reuse. |
| `tinggi_badan` | 9 | Tinggi Badan | number | ya | Reuse. |
| `suhu` | 11 | Suhu | decimal | ya | Reuse. |
| `jalan_nafas` | 226 | Jalan Nafas | select | ya | Legacy `jalan_nafas`: `N/A` / `PATEN` / `GURGLING` / `SNORRING` / `STRIDOR`. **SelectOption key baru: `jalan_nafas`.** |
| `tipe_nafas` | 226 | Jalan Nafas | select | tidak | **Reuse objek 226** (variabel bersuffix). Legacy `tipe_nafas` — hanya satu opsi (`N/A`), jadi praktis opsional. |
| `frekuensi_nafas` | 12 | Pernapasan | number | ya | **Reuse objek 12.** Legacy `frekuensi_nafas` = frekuensi napas per menit. |
| `saturasi` | 15 | Saturasi Oksigen | number | ya | Reuse. Persentase. |
| `sirkulasi` | 227 | Sirkulasi | select | ya | Legacy `sirkulasi`: `Nadi Teraba` / `Nadi Tidak Teraba`. **SelectOption key baru: `sirkulasi`.** |
| `akral` | 228 | Akral | text | tidak | Legacy `akral` — free text. Lihat catatan §8.3 (legacy disables input ini). |
| `nadi` | 10 | Nadi | number | ya | Reuse. Per menit. |
| `td_sistolik` | 6 | Tekanan Darah Sistolik | number | ya | Reuse. |
| `td_diastolik` | 7 | Tekanan Darah Diastolik | number | ya | Reuse. |
| `kesadaran` | 51 | Kesadaran | select | ya | **Reuse objek 51.** Legacy `detail_kesadaran` (1–5). Di NusaMedika memakai key SelectOption `kesadaran` yang sudah ada (`Compos Mentis`, …). |
| `pupil` | 229 | Pupil | select | ya | Legacy `pupil`: `Baik` / `Tidak Baik` / `Lambat`. **SelectOption key baru: `pupil`.** |
| `refleks_cahaya` | 230 | Refleks Cahaya | number | ya | Legacy `refleksi_cahaya`, satuan mm. Tanpa SelectOption — input numeric biasa. |

#### Blok C — Keluhan Utama & Anamnesis

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `jenis_anamnesis` | 231 | Jenis Anamnesis | radio | ya | Legacy `anamnesis`: `Auto Anamnesis` / `Allo Anamnesis`. **SelectOption key baru: `jenis_anamnesis`.** |
| `alasan_kunjungan` | 13 | Keluhan Utama | select | ya | **Reuse objek 13.** Legacy `keluhan` — daftar *Alasan Kunjungan*: `Lemas`, `Demam`, `Pusing`, `Sakit Kepala`, `Batuk`, `Pilek`, `Sesak Napas`, `Kontrol`, `Obat Habis`, `Lainnya`. **SelectOption key baru: `alasan_kunjungan_igd`.** |
| `alasan_kunjungan_lain` | 13 | Keluhan Utama | text | kondisional | **Reuse objek 13** (variabel bersuffix). Legacy `detail_keluhan`. **Wajib bila `alasan_kunjungan = 'Lainnya'`.** |

#### Blok D — Skala Nyeri

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `nyeri` | 14 | Nyeri | radio | ya | **Reuse objek 14.** Legacy `nyeri`: `Ya, Nyeri` / `Tidak Nyeri`. |
| `skor_nyeri` | 140 | Skor Nyeri | select 0–10 | kondisional | **Reuse objek 140.** **Wajib bila `nyeri = 'Ya, Nyeri'`.** |
| `lokasi_nyeri` | 232 | Lokasi Nyeri | text | kondisional | Legacy `lokasi_nyeri` (free text). Wajib bila `nyeri = 'Ya, Nyeri'`. |
| `frekuensi_nyeri` | 233 | Frekuensi Nyeri | select | kondisional | Legacy `frekuensi_nyeri`: `Sering` / `Kadang` / `Jarang`. **SelectOption key baru: `frekuensi_nyeri`.** |
| `karakteristik_nyeri` | 234 | Karakteristik Nyeri | select | kondisional | Legacy `karakteristik_nyeri`: `Terbakar` / `Tertusuk` / `Tertindih` / `Menyebar` / `Berdenyut`. **SelectOption key baru: `karakteristik_nyeri`.** |

#### Blok E — Riwayat Penyakit

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `riwayat_penyakit_1` | 42 | Riwayat Penyakit Sekarang | select | ya | **Reuse objek 42** dengan 8 variabel bersuffix. Legacy `riwayat` — daftar: `Hipertensi`, `DM`, `Typhoid`, `DHF`, `TB`, `Stroke`, `Asma`, `Lainnya`. **SelectOption key baru: `riwayat_penyakit_igd`.** |
| `riwayat_penyakit_2` | 42 | Riwayat Penyakit Sekarang | select | ya | |
| `riwayat_penyakit_3` | 42 | Riwayat Penyakit Sekarang | select | ya | |
| `riwayat_penyakit_4` | 42 | Riwayat Penyakit Sekarang | select | ya | |
| `riwayat_penyakit_5` | 42 | Riwayat Penyakit Sekarang | select | ya | |
| `riwayat_penyakit_6` | 42 | Riwayat Penyakit Sekarang | select | ya | |
| `riwayat_penyakit_7` | 42 | Riwayat Penyakit Sekarang | select | ya | |
| `riwayat_penyakit_8` | 42 | Riwayat Penyakit Sekarang | select | ya | |
| `riwayat_penyakit_lain` | 42 | Riwayat Penyakit Sekarang | text | kondisional | **Reuse objek 42** (variabel bersuffix). Legacy `detail_riwayat`. **Wajib bila `riwayat_penyakit_8 = 'Lainnya'`.** |

#### Blok F — Skrining Risiko Jatuh

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `risiko_jatuh` | 91 | Risiko Jatuh - Tingkat Risiko | radio | ya | **Reuse objek 91.** Legacy `resiko_jatuh`: `Beresiko` (value 1) / `Tidak Beresiko` (value 2). Nilai disimpan sebagai label, bukan angka. |

#### Blok G — Zona Triase IGD

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `prioritas_triase` | 235 | Prioritas Triase IGD | select | ya | Legacy `triage` (objek 34). **SelectOption key baru: `prioritas_triase_igd`** dengan 6 opsi bertingkat seperti tabel §4.2. |

Tabel opsi (harus identik dengan `triage_igd_cak.php`):

| Nilai | Prioritas | Keterangan | Zona Perawatan | Respon Time |
|---|---|---|---|---|
| `1` | Prioritas 1 | Resuscitation | Zona Merah | Segera |
| `2` | Prioritas 2 | Emergency | Zona Merah | < 10 Menit |
| `3` | Prioritas 3 | Urgent | Zona Kuning | < 30 Menit |
| `4` | Prioritas 4 | Semi Urgent | Zona Kuning | < 60 Menit |
| `5` | Prioritas 5 | Non Urgent | Poliklinik | < 120 Menit |
| `HITAM` | HITAM | DOA | – | – |

**Turunan (tidak disimpan):** `zona_perawatan` dan `respon_time` dihitung dari
`prioritas_triase` untuk ditampilkan sebagai badge di bawah select — mengikuti
pola `EwsHelper::KATEGORI_WARNA`. Kolom ini **tidak** punya baris
`objek_form_control`; nilainya dibuang bila dikirim browser.

#### Blok H — Catatan

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `catatan` | 77 | Keterangan | textarea | tidak | **Reuse objek 77.** |

#### Panel read-only — Header Pasien

Mengiku `head_pasien_dalam_form()`: No MR, Nama Pasien, Tempat/Tanggal Lahir +
Umur, Tanggal Layanan, Nasabah + Kelas, No SEP. Diambil dari
`RegistrasiDetail::with('registrasi.pasien')`, bukan dari `emr_detail`.

#### Panel read-only — Pengkajian Awal Keperawatan (terbaru)

Menampilkan ringkasan EMR form 3 (Pengkajian Awal Keperawatan) atau form 11
pada `registrasi_detail` yang sama, melalui
`EmrHelper::latestValuesByVariabel()`. **Tidak punya mapping.**

### 4.3 Dashboard & Form Master

```php
// ======== $objeks ========
// Hampir semua field triase me-reuse objek yang sudah ada (tanda vital, GCS,
// EWS, Saturasi, Kesadaran, Skor Nyeri, Keluhan Utama, Riwayat Penyakit,
// Risiko Jatuh, Tanggal/Waktu Observasi, Keterangan). Yang baru hanya konsep
// yang belum punya padanan pada objek 1-177.
225 => 'Keadaan Umum',              // legacy: ku (Baik / Sedang / Lemas)
226 => 'Jalan Nafas',                // legacy: jalan_nafas + tipe_nafas (2 variabel)
227 => 'Sirkulasi',                  // legacy: sirkulasi
228 => 'Akral',                     // legacy: akral
229 => 'Pupil',                     // legacy: pupil
230 => 'Refleks Cahaya',            // legacy: refleksi_cahaya
231 => 'Jenis Anamnesis',            // legacy: anamnesis (Auto / Allo)
232 => 'Lokasi Nyeri',              // legacy: lokasi_nyeri
233 => 'Frekuensi Nyeri',           // legacy: frekuensi_nyeri
234 => 'Karakteristik Nyeri',       // legacy: karakteristik_nyeri
235 => 'Prioritas Triase IGD',       // legacy: triage (1-5 + HITAM)
```

```php
// ======== $menus ========
// Menu BARU. Tambah setelah menu 5 "Formulir" (menu 6 dipakai
// "Resume & Discharge" — lihat KONSEP_DISCHARGE_PLANNING.md).
$menus = [
    ['dashboard_menu_id' => 1, 'nama_menu' => 'Catatan Medis'],
    ['dashboard_menu_id' => 2, 'nama_menu' => 'Catatan Keperawatan'],
    ['dashboard_menu_id' => 3, 'nama_menu' => 'Resep'],
    ['dashboard_menu_id' => 4, 'nama_menu' => 'Order'],
    ['dashboard_menu_id' => 5, 'nama_menu' => 'Formulir'],
    ['dashboard_menu_id' => 6, 'nama_menu' => 'Resume & Discharge'],
    ['dashboard_menu_id' => 7, 'nama_menu' => 'Gawat Darurat'],
];

$subMenus = [
    // ... baris baseline (menu 1 sub 13–15, menu 6 sub 33–34) yang sudah ada ...
    // Menu 7 "Gawat Darurat" — Triage IGD (tanpa extra, link langsung).
    // Str::slug('Triage IGD','_') = "triage_igd" WAJIB sama dengan form.slug.
    // Sub 53, BUKAN 1 — dashboard_menu_sub_id bersifat global (ALOKASI_ID_GLOBAL.md §0).
    ['dashboard_menu_sub_id' => 53, 'dashboard_menu_id' => 7, 'nama_sub_menu' => 'Triage IGD'],
    // Menu 7 "Gawat Darurat" — Triage IGD OBGYN (tanpa extra).
    // DICADANGKAN: belum ada form yang memakainya. Disiapkan agar dokumen
    // KONSEP_OBSTETRI_NEONATAL.md (form "Registrasi IGD OBGYN" di legacy)
    // tinggal menambah $forms tanpa menyentuh $menus/$subMenus lagi.
    // PENTING: jangan menaruh form apa pun dengan id_dash_menu = '7.54' sampai
    // form tersebut benar-benar ada — leaf tanpa form akan tampil sebagai
    // tautan mati di dashboard pasien.
    ['dashboard_menu_sub_id' => 54, 'dashboard_menu_id' => 7, 'nama_sub_menu' => 'Triage IGD OBGYN'],
];

$forms = [
    // Triage IGD: pengkajian awal + penetapan prioritas. KHUSUS IGD.
    // id_dash_menu "7.53".
    ['form_id' => 19, 'nama_form' => 'Triage IGD', 'slug' => 'triage_igd', 'id_dash_menu' => '7.53', 'ri' => 0, 'rj' => 0, 'igd' => 1, 'mcu' => 0],
];
```

> **Perbedaan dengan `SelectOption::triase_igd`.** Helper sudah punya key
> `triase_igd` dengan 4 nilai berwarna (`Merah`, `Kuning`, `Hijau`, `Hitam`) yang
> dipakai **registrasi IGD**. Form 19 memakai key **baru**
> `prioritas_triase_igd` dengan 6 nilai legacy (`1`–`5` + `HITAM`) karena
> protektron akrual level 1/2 dipisahkan menjadi Prioritas 1 dan 2, dan karena
> nilai `HITAM` memakai string, bukan angka. **Jangan memaksa satu key untuk dua
> keperluan** — kolom registrasi dan kolom EMR akan berbeda format.

### 4.4 Mapping

```php
$mapping[19] = [
    // Blok A — waktu pengkajian
    'tanggal_triase'      => 155,  // reuse "Tanggal Observasi"
    'waktu_triase'        => 156,  // reuse "Waktu Observasi"

    // Blok B — pemeriksaan fisik
    'keadaan_umum'        => 225,
    'berat_badan'         => 8,    // reuse
    'tinggi_badan'        => 9,    // reuse
    'suhu'                => 11,   // reuse
    'jalan_nafas'         => 226,
    'tipe_nafas'          => 226,
    'frekuensi_nafas'     => 12,   // reuse "Pernapasan"
    'saturasi'            => 15,   // reuse
    'sirkulasi'           => 227,
    'akral'               => 228,
    'nadi'                => 10,   // reuse
    'td_sistolik'         => 6,    // reuse
    'td_diastolik'        => 7,    // reuse
    'kesadaran'           => 51,   // reuse "Kesadaran"
    'pupil'               => 229,
    'refleks_cahaya'      => 230,

    // Blok C — keluhan utama & anamnesis
    'jenis_anamnesis'     => 231,
    'alasan_kunjungan'    => 13,   // reuse "Keluhan Utama"
    'alasan_kunjungan_lain'=> 13,  // reuse (variabel bersuffix)

    // Blok D — skala nyeri
    'nyeri'               => 14,   // reuse "Nyeri"
    'skor_nyeri'          => 140,  // reuse "Skor Nyeri"
    'lokasi_nyeri'        => 232,
    'frekuensi_nyeri'    => 233,
    'karakteristik_nyeri' => 234,

    // Blok E — riwayat penyakit
    'riwayat_penyakit_1'    => 42,  // reuse "Riwayat Penyakit Sekarang"
    'riwayat_penyakit_2'    => 42,
    'riwayat_penyakit_3'    => 42,
    'riwayat_penyakit_4'    => 42,
    'riwayat_penyakit_5'    => 42,
    'riwayat_penyakit_6'    => 42,
    'riwayat_penyakit_7'    => 42,
    'riwayat_penyakit_8'    => 42,
    'riwayat_penyakit_lain' => 42,

    // Blok F — skrining risiko jatuh
    'risiko_jatuh'       => 91,   // reuse "Risiko Jatuh - Tingkat Risiko"

    // Blok G — zona triase
    'prioritas_triase'   => 235,

    // Blok H
    'catatan'            => 77,   // reuse "Keterangan"
];
```

> Objek 42 dipakai **9 variabel** (`riwayat_penyakit_1..8` + `_lain`) dan objek
> 13 dipakai **2 variabel**. Pola "N variabel → 1 objek" ini sudah dipakai form 3
> (`vaksin_covid`, `tanggal_covid_1`, `tanggal_covid_2` → objek 62) dan form 16
> (`pf_kepala`…`pf_ekstremitas` → objek 188). Konsekuensi wajib: **dibaca dengan
> `EmrHelper::emrDetailByVariabel()`**, bukan `emrDetailByObjek()`.

### 4.5 Akses EHR

```php
// Triage IGD (form 19): Perawat create/read/update/delete (perawat yang
// melakukan triase di pintu IGD) dan Dokter read/update/delete tanpa create
// (dokter boleh merevisi, tidak membuat triase pertama).
// WAJIB di-seed: EmrDashboardController INNER JOIN akses_ehr.
['profesi_id' => 1, 'form_id' => 19, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 2, 'form_id' => 19, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
```

Tambahkan `EmrHelper::backfillObjekId(19);` di `EmrMasterSeeder` setelah loop
mapping.

> **Perbedaan dengan baseline form 1–15.** Semua form yang sudah ada
> memberi `akses_create = 1` untuk Dokter **dan** Perawat. Form 19 dibedakan
> karena secara alur hanya perawat IGD yang menetapkan triase. Bila tim
> memutuskan dokter boleh membuat triase (mis. saat perawat tidak tersedia),
> ubah `akses_create` Dokter ke `1` — tidak ada perubahan kode.

### 4.6 Validasi

```php
private function validated(Request $request): array
{
    return array_merge([
        'tipe_nafas'             => null,
        'akral'                  => null,
        'alasan_kunjungan_lain'   => null,
        'skor_nyeri'             => null,
        'lokasi_nyeri'           => null,
        'frekuensi_nyeri'        => null,
        'karakteristik_nyeri'    => null,
        'riwayat_penyakit_1'     => null,  // … s/d riwayat_penyakit_8
        'riwayat_penyakit_lain'  => null,
        'catatan'                => null,
    ], $request->validate([
        'tanggal_triase' => 'required|date',
        'waktu_triase'   => 'required|date_format:H:i',

        'keadaan_umum'   => ['required', Rule::in(['Baik', 'Sedang', 'Lemas'])],
        'berat_badan'    => 'required|numeric|min:0.5|max:400',
        'tinggi_badan'   => 'required|numeric|min:30|max:250',
        'suhu'           => 'required|numeric|min:30|max:45',
        'jalan_nafas'    => ['required', Rule::in(['N/A', 'PATEN', 'GURGLING', 'SNORRING', 'STRIDOR'])],
        'frekuensi_nafas'=> 'required|integer|min:0|max:80',
        'saturasi'       => 'required|integer|min:30|max:100',
        'sirkulasi'      => ['required', Rule::in(['Nadi Teraba', 'Nadi Tidak Teraba'])],
        'nadi'           => ['required', 'integer', 'min:0|max:250'],
        'td_sistolik'    => 'required|integer|min:40|max:300',
        'td_diastolik'   => 'required|integer|min:20|max:200',
        'kesadaran'      => 'required|string|max:50',
        'pupil'          => ['required', Rule::in(['Baik', 'Tidak Baik', 'Lambat'])],
        'refleks_cahaya' => 'required|integer|min:0|max:10',

        'jenis_anamnesis'  => ['required', Rule::in(['Auto Anamnesis', 'Allo Anamnesis'])],
        'alasan_kunjungan' => ['required', Rule::in(array_column(SelectOption::get('alasan_kunjungan_igd'), 'value'))],

        'nyeri'          => ['required', Rule::in(['Ya, Nyeri', 'Tidak Nyeri'])],

        'risiko_jatuh'    => ['required', Rule::in(['Beresiko', 'Tidak Beresiko'])],
        'prioritas_triase'=> ['required', Rule::in(['1', '2', '3', '4', '5', 'HITAM'])],
    ]));
}
```

Aturan kondisional di `filteredData()`:

| Kondisi | Aturan |
|---|---|
| `jenis_anamnesis = 'Auto Anamnesis'` | Blok riwayat penyakit (`riwayat_penyakit_*`) **dibuang** dari payload. |
| `alasan_kunjungan = 'Lainnya'` | `alasan_kunjungan_lain` **wajib**; selain itu dibuang. |
| `nyeri = 'Tidak Nyeri'` | `skor_nyeri`, `lokasi_nyeri`, `frekuensi_nyeri`, `karakteristik_nyeri` dibuang. |
| `nyeri = 'Ya, Nyeri'` | `skor_nyeri` dan `lokasi_nyeri` **wajib**. |
| `riwayat_penyakit_8 = 'Lainnya'` | `riwayat_penyakit_lain` **wajib**; selain itu dibuang. |
| `sirkulasi = 'Nadi Tidak Teraba'` | `nadi` **wajib** dan harus > 0 (bila 0, isi `-` agar tetap valid). |
| Field computed | `zona_perawatan` & `respon_time` tidak punya baris mapping; bila dikirim, otomatis terbuang `array_intersect_key()`. |
| Sinkronisasi registrasi | `prioritas_triase` yang tersimpan **tidak** menulis ulang `registrasi_detail.prioritas` secara otomatis (dijalankan manual dari menu IGD). Alasan §8.3. |

**Validasi rentang waktu:**

```php
abort_if(
    Carbon::parse($data['tanggal_triase'] . ' ' . $data['waktu_triase'])->isAfter(now()),
    422,
    'Tanggal dan jam triase tidak boleh berada di masa depan.'
);
```

### 4.7 Cetak

**Tidak wajib** untuk form ini. Triase adalah catatan kerja, bukan dokumen yang
dibawa pasien. Legacy pun tidak menyediakan print khusus — hanya
`printDiv` generik.

Bila INSTITUSI meminta, tambahkan `print.blade.php` berisi: header pasien, blok
pemeriksaan fisik, triase dengan badge zona, riwayat penyakit, dan triase —
mengikuti `HalDepanSKKBayi.php` sebagai contoh tata letak. Sifat **opsional**,
tidak masuk scope implementasi awal.

---

## 5. Implementasi

Tidak ada migration baru dan **tidak ada route manual**.

### 5.1 Seeder

- [ ] `database/seeders/EmrMasterSeeder.php`:
  - `$menus` — tambah `['dashboard_menu_id' => 7, 'nama_menu' => 'Gawat Darurat']`.
  - `$subMenus` — tambah sub **53** (`Triage IGD`) dan sub **54** (`Triage IGD OBGYN`) di bawah `dashboard_menu_id = 7`. **Bukan 1 & 2** — `dashboard_menu_sub_id` global (`ALOKASI_ID_GLOBAL.md` §3).
  - `$objeks` — tambah `225 => 'Keadaan Umum'` … `235 => 'Prioritas Triase IGD'`.
  - `$mapping` — tambah `$mapping[19]`.
  - `$akses` — tambah 2 baris (Dokter read/update/delete, Perawat full CRUD).
  - `EmrHelper::backfillObjekId(19);`

### 5.2 SelectOption — key baru (10)

```php
'keadaan_umum_igd' => [
    ['value' => 'Baik',   'label' => 'Baik',   'class' => 'text-emerald-600'],
    ['value' => 'Sedang', 'label' => 'Sedang', 'class' => 'text-amber-500'],
    ['value' => 'Lemas',  'label' => 'Lemas',  'class' => 'text-red-600'],
],
'jalan_nafas' => [
    ['value' => 'N/A',      'label' => 'N/A (tidak dapat dinilai)'],
    ['value' => 'PATEN',    'label' => 'PATEN'],
    ['value' => 'GURGLING', 'label' => 'GURGLING'],
    ['value' => 'SNORRING', 'label' => 'SNORRING'],
    ['value' => 'STRIDOR',  'label' => 'STRIDOR'],
],
'sirkulasi' => [
    ['value' => 'Nadi Teraba',       'label' => 'Nadi Teraba'],
    ['value' => 'Nadi Tidak Teraba', 'label' => 'Nadi Tidak Teraba'],
],
'pupil' => [
    ['value' => 'Baik',       'label' => 'Baik'],
    ['value' => 'Tidak Baik', 'label' => 'Tidak Baik'],
    ['value' => 'Lambat',     'label' => 'Lambat'],
],
'jenis_anamnesis' => [
    ['value' => 'Auto Anamnesis',  'label' => 'Auto Anamnesis'],
    ['value' => 'Allo Anamnesis',  'label' => 'Allo Anamnesis'],
],
'alasan_kunjungan_igd' => [
    ['value' => 'Lemas',         'label' => 'Lemas'],
    ['value' => 'Demam',         'label' => 'Demam'],
    ['value' => 'Pusing',        'label' => 'Pusing'],
    ['value' => 'Sakit Kepala',  'label' => 'Sakit Kepala'],
    ['value' => 'Batuk',         'label' => 'Batuk'],
    ['value' => 'Pilek',         'label' => 'Pilek'],
    ['value' => 'Sesak Napas',   'label' => 'Sesak Napas'],
    ['value' => 'Kontrol',       'label' => 'Kontrol'],
    ['value' => 'Obat Habis',    'label' => 'Obat Habis'],
    ['value' => 'Lainnya',       'label' => 'Lainnya'],
],
'riwayat_penyakit_igd' => [
    ['value' => 'Hipertensi', 'label' => 'Hipertensi'],
    ['value' => 'DM',        'label' => 'Diabetes Melitus'],
    ['value' => 'Typhoid',   'label' => 'Typhoid'],
    ['value' => 'DHF',       'label' => 'Demam Berdarah (DHF)'],
    ['value' => 'TB',        'label' => 'Tuberkulosis'],
    ['value' => 'Stroke',    'label' => 'Stroke'],
    ['value' => 'Asma',      'label' => 'Asma'],
    ['value' => 'Lainnya',   'label' => 'Lainnya'],
],
'frekuensi_nyeri' => [
    ['value' => 'Sering', 'label' => 'Sering'],
    ['value' => 'Kadang', 'label' => 'Kadang'],
    ['value' => 'Jarang', 'label' => 'Jarang'],
],
'karakteristik_nyeri' => [
    ['value' => 'Terbakar',  'label' => 'Terbakar'],
    ['value' => 'Tertusuk',  'label' => 'Tertusuk'],
    ['value' => 'Tertindih', 'label' => 'Tertindih'],
    ['value' => 'Menyebar',  'label' => 'Menyebar'],
    ['value' => 'Berdenyut', 'label' => 'Berdenyut'],
],
'prioritas_triase_igd' => [
    ['value' => '1',      'label' => 'Prioritas 1 — Resuscitation',  'class' => 'text-red-600'],
    ['value' => '2',      'label' => 'Prioritas 2 — Emergency',       'class' => 'text-red-600'],
    ['value' => '3',      'label' => 'Prioritas 3 — Urgent',          'class' => 'text-amber-500'],
    ['value' => '4',      'label' => 'Prioritas 4 — Semi Urgent',     'class' => 'text-amber-500'],
    ['value' => '5',      'label' => 'Prioritas 5 — Non Urgent',      'class' => 'text-emerald-500'],
    ['value' => 'HITAM',  'label' => 'HITAM — DOA',                  'class' => 'text-slate-800'],
],
```

> Total **10** key baru — `keadaan_umum_igd`,
> `jalan_nafas`, `sirkulasi`, `pupil`, `jenis_anamnesis`,
> `alasan_kunjungan_igd`, `riwayat_penyakit_igd`, `frekuensi_nyeri`,
> `karakteristik_nyeri`, `prioritas_triase_igd`.

Metadata turunan untuk badge, dikirim ke view lewat `@json` (bukan `@json(...)`
dengan ekspresi majemuk — gotcha Blade PANDUAN):

```php
// Di controller index():
$metaTriase = [
    '1'     => ['zona' => 'Zona Merah',  'waktu' => 'Segera',       'warna' => 'bg-red-100 text-red-700'],
    '2'     => ['zona' => 'Zona Merah',  'waktu' => '< 10 Menit',   'warna' => 'bg-red-100 text-red-700'],
    '3'     => ['zona' => 'Zona Kuning', 'waktu' => '< 30 Menit',   'warna' => 'bg-amber-100 text-amber-700'],
    '4'     => ['zona' => 'Zona Kuning', 'waktu' => '< 60 Menit',   'warna' => 'bg-amber-100 text-amber-700'],
    '5'     => ['zona' => 'Poliklinik',  'waktu' => '< 120 Menit',  'warna' => 'bg-emerald-100 text-emerald-700'],
    'HITAM' => ['zona' => '—',           'waktu' => '—',            'warna' => 'bg-slate-200 text-slate-800'],
];
return view('moduls.EMR.TriageIgd.index', compact(..., 'metaTriase'));
```

### 5.3 Controller & View

- [ ] `app/Http/Controllers/EMR/TriageIgd/TriageIgdController.php`
  — `Str::studly('triage_igd')` = `TriageIgd` (bukan `TriageIGD`).
  Method `index/store/update/destroy`, plus `ringkasanKeperawatan()` untuk panel
  read-only.
- [ ] `resources/views/moduls/EMR/TriageIgd/index.blade.php`
  — 8 accordion sesuai blok §4.2, memakai `x-emr-split-layout` +
    `x-emr-history-table` (identik dengan `ImplementasiKeperawatan`).
- [ ] `resources/views/moduls/EMR/TriageIgd/partials/zonaTriase.blade.php`
  — tabel 6 baris + badge; statis, tidak perlu blade component global.
- [ ] Route: **tidak perlu** — generic route EMR menangani.

### 5.4 Blade — catatan teknis wajib

| Catatan | Sumber |
|---|---|
| Tampilkan/sembunyikan blok Nyeri dan Riwayat **lewat inline `style.display`**, bukan class `hidden` — Tailwind v4 mencetak `.hidden` sebelum `.inline-flex` sehingga elemen tetap terlihat. | AGENTS.md — Form Tindakan Medis |
| **Jangan** menulis tag komponen blade di dalam `<script>`, bahkan di komentar. | AGENTS.md |
| **Jangan** bersarang blok `@php` di dalam `@php`/`@foreach`. | AGENTS.md |
| Opsi `<option>` yang dirender lewat JS harus dihitung di blok `@php` lalu dipakai sebagai satu variabel `@json($arr)`. | AGENTS.md |
| Select2 pada elemen dinamis harus `destroy` dulu sebelum `init` ulang. | AGENTS.md |
| `$emr_data` **wajib** dibaca per-variabel (`$emr_data['keadaan_umum'] ?? ''`), bukan `$emr_data` scalar. | AGENTS.md — Risiko Jatuh |

### 5.5 Review sebelum merge (PANDUAN §4)

- [ ] `Str::slug('Triage IGD','_') === 'triage_igd'`.
- [ ] `id_dash_menu = '7.53'` cocok dengan PK yang benar-benar di-seed (menu 7 + sub 53).
- [ ] Sub 53 & 54 tidak bentrok dengan `dashboard_menu_sub` yang sudah ada
      (`DB::table('dashboard_menu_sub')->whereIn('dashboard_menu_sub_id',[53,54])`).
- [ ] Semua `variabel` unik per form — `riwayat_penyakit_1..8` + `_lain` (9), `alasan_kunjungan` + `_lain` (2), `jalan_nafas` + `tipe_nafas` (2).
- [ ] `array_intersect_key()` di `filteredData()`.
- [ ] `@error()` ada di setiap field; `<fieldset {{ $isView ? 'disabled' : '' }}>`; `old('x', $emr_data['x'] ?? '')`.
- [ ] Tidak ada `objek_form_control` untuk `zona_perawatan` / `respon_time`.
- [ ] `docker compose exec app ./vendor/bin/pint --dirty`.
- [ ] Entri `AGENTS.md` diperbarui.

---

## 6. Catatan & Risiko

### 6.1 Risiko alokasi objek

| Risiko | Mitigasi |
|---|---|
| Objek 225–235 bentrok dengan dokumen lain. | Rentang dinyatakan eksplisit di §1. Verifikasi dengan `DB::table('objek')->where('objek_id','>',224)->orderBy('objek_id')->get()` sebelum menjalankan seeder. |
| Objek 42 dipakai 9 variabel, objek 13 dipakai 2. | **Wajib** baca via `EmrHelper::emrDetailByVariabel()`. Laporan antar-form berbasis objek akan melihat banyak baris dengan `objek_id` sama. |
| Objek 42 juga dipakai form 1, 3, 17, 20, 57 dengan makna berbeda. | `objek_form_control` memetakan per form — query laporan **wajib** memfilter `form_id`. |

### 6.2 Risiko menu

| Risiko | Mitigasi |
|---|---|
| Menu 7 belum ada → `id_dash_menu = '7.53'` yatim. | `$menus` harus ditambah **pada seeder yang sama** dengan `$subMenus`. |
| Menu 6 sudah dipakai "Resume & Discharge" (dokumen discharge planning). | Verifikasi `DB::table('dashboard_menu')->where('dashboard_menu_id', 7)->first()` sebelum seeding; bila sudah ada dengan nama lain, geser ke ID berikutnya dan sesuaikan `id_dash_menu`. |
| Sub 1–2 terlihat "kosong" di menu 7 tapi **tidak boleh dipakai** — keduanya sudah jadi milik menu 1–5. | `dashboard_menu_sub_id` **global**. Band dokumen ini 53–72 (`ALOKASI_ID_GLOBAL.md` §3); sub 53/54 dipilih agar `id_dash_menu` = `7.53`. |
| Sub 54 (`Triage IGD OBGYN`) tanpa form → tautan mati. | Sudah diberi komentar peringatan di §4.3. Jangan menaruh form dengan `id_dash_menu = '7.54'` sampai form-nya benar-benar ada. |
| Perbandingan `id_dash_menu` dengan `==`. | Gunakan `===` di `AksesEhrController`. |

### 6.3 Risiko klinis & operasional

| Risiko | Mitigasi |
|---|---|
| **Akral tidak bisa diisi.** | Di legacy, input `akral` diberi atribut `disabled` sehingga praktis tidak pernah terisi. Di NusaMedika field **diaktifkan** (free text) karena berisi assessment kemampuan neurologis pasien — data yang relevan untuk triase kepala. Bila tim klinis ingin menghapusnya, cukup keluarkan dari `$mapping[19]` — jangan simpan sebagai data mati. |
| **Akral ambigu.** | Dalam klinik Indonesia istilah "akral" bisa berarti kapasitas vital ATAU arteri akral. Label di blade ditulis **"Akral (Kapasitas Vital)"** untuk menghindari keraguan; isi tetap free text. |
| **Prioritas triase dan `registrasi_detail.prioritas` bisa berbeda.** | Registrasi IGD sudah punya kolom `prioritas` dengan key SelectOption `triase_igd` (4 warna: Merah/Kuning/Hijau/Hitam), sedangkan form ini memakai 6 nilai legacy (`1`-`5` + `HITAM`). **Mitigasi:** form 19 **tidak** menulis ulang kolom registrasi — sinkronisasi otomatis akan merusak data registrasi lama. Rekomendasi: tampilkan nilai registrasi sebagai panel read-only pembanding di bawah select triase; ubah registrasi lewat menu IGD yang sudah ada. |
| **Riwayat triase diedit setelah 24 jam.** | Legacy menonaktifkan tombol edit/delete bila usia catatan > 23 jam. Diterjemahkan menjadi guard di `update()`/`destroy()`: `abort_if($emr->input_time->diffInHours(now()) > 24, 403, "Triase yang sudah lebih dari 24 jam tidak dapat diubah.")`. |
| **Retriase tertukar dengan triase baru.** | Form ini **boleh** dibuat berulang (retriase setelah terapi). Yang dibatasi adalah **mengedit catatan lama**, bukan membuat catatan baru. |
| **Triase tidak terisi sebelum pasien masuk ruang.** | Tidak ada integrasi otomatis ke modul Rawat Inap pada tahap ini; hanya badge di dashboard pasien. |

### 6.4 Risiko performa

| Risiko | Mitigasi |
|---|---|
| Panel read-only pengkajian awal memanggil `latestValuesByVariabel()` untuk 2 form. | Gunakan `EmrHelper::latestEmr()` sekali per form lalu `latestValuesByVariabel()`; bukan query loop. |
| Tabel `objek` tumbuh ke ± 500 baris. | administrator → Manajemen EMR → Form sudah menyediakan tab Objek; tambahkan filter bila perlu. |

### 6.5 Yang **tidak** dicakup dokumen ini

| Topik | Ditujukan untuk |
|---|---|
| Triage IGD OBGYN / Registrasi IGD OBGYN (legacy `triase_igd_obgyn`, `HalDepanSKKBayi.php`) | `KONSEP_OBSTETRI_NEONATAL.md` — sub **54** menu 7 sudah dicadangkan |
| Konfirmasi IGD / verifikasi dokter IGD | `KONSEP_GAWAT_DARURAT.md` |
| Alur antrean & pemanggilan pasien (`registrasi_urut`) | Modul Registrasi, bukan EMR |
| Bed Management & transfer IGD → ruang | Fitur Bed Management yang sudah ada; form 94 di `KONSEP_BEDAH_ANESTESI.md` |
| Skala pembayaran IGD (BPJS) | Modul penjaminan, di luar cakupan |
