# KONSEP FORM ICU & HEMODIALISA (EMR)

**Status:** Rancangan / Konsep — belum diimplementasikan
**Tanggal:** 2026-10-10
**Target:** form_id 52, 53, 102, 103, 104, 105 · objek_id 447–535 · `dashboard_menu` 12 (baru) sub 213–218
**Acuan:** [`PANDUAN_IMPLEMENTASI_FORM_EMR.md`](PANDUAN_IMPLEMENTASI_FORM_EMR.md),
[`ANALISIS_KEKURANGAN_FORM.md`](ANALISIS_KEKURANGAN_FORM.md)
**Sumber legacy:** `/simrs_tenriawaru/FE/lib/modul/`

---

## 1. Ringkasan

Dua domain sekaligus: **sel critical care** (kriteria masuk & keluar ICU) dan
**renal/hemodialisa** (pre/intra/post/triage HD). Keduanyarionut rawat inap
sehingga seluruh form `ri = 1`; IGD **tidak** — pasien masuk IGD belum melewati
triase ICU (form 19 `triage_igd` yang akan datang].

| form_id | nama_form | slug | id_dash_menu | ri | rj | igd | mcu |
|---|---|---|---|---|---|---|---|
| 52 | Kriteria Masuk ICU | `kriteria_masuk_icu` | `12.213` | 1 | 0 | 1 | 0 |
| 53 | Kriteria Keluar ICU | `kriteria_keluar_icu` | `12.214` | 1 | 0 | 0 | 0 |
| 102 | Pre Hemodialisa | `pre_hemodialisa` | `12.215` | 1 | 0 | 1 | 0 |
| 103 | Intra Hemodialisa | `intra_hemodialisa` | `12.216` | 1 | 0 | 1 | 0 |
| 104 | Post Hemodialisa | `post_hemodialisa` | `12.217` | 1 | 0 | 0 | 0 |
| 105 | Triage Hemodialisa | `triage_hemodialisa` | `12.218` | 0 | 1 | 0 | 0 |

> Pre & Triage HD boleh dibuka di rawat jalan (pasien HD biasanya pulang dengan
> jadwal tetap, tetapi tetap dialokasikan ke `ri` sebagai episodetreatment).

### 1.1 Alokasi `dashboard_menu` & sub-menu

```
dashboard_menu (BARU)
└── 12 = "ICU & Hemodialisa"                     [BARU — id 12 belum dipakai]
    ├── sub 213 = "Kriteria Masuk ICU"    → form 52  id_dash_menu "12.213"
    ├── sub 214 = "Kriteria Keluar ICU"   → form 53  id_dash_menu "12.214"
    ├── sub 215 = "Pre Hemodialisa"       → form 102 id_dash_menu "12.215"
    ├── sub 216 = "Intra Hemodialisa"     → form 103 id_dash_menu "12.216"
    ├── sub 217 = "Post Hemodialisa"      → form 104 id_dash_menu "12.217"
    └── sub 218 = "Triage Hemodialisa"    → form 105 id_dash_menu "12.218"
```

> `dashboard_menu_sub_id` bersifat **GLOBAL**. Band `KONSEP_ICU_HEMODIALISA.md`
> adalah **213–232** (`ALOKASI_ID_GLOBAL.md` §3); file ini memakai **213–218**.
> Band yang lebih rendah dipakai dokumen lain: `KONSEP_CATATAN_MEDIS_LANJUTAN.md`
> 13–32, `KONSEP_DISCHARGE_PLANNING.md` 33–52, `KONSEP_TRIASE_IGD.md` 53–72,
> `KONSEP_ASUHAN_KEPERAWATAN_LANJUTAN.md` 73–92, `KONSEP_CAIRAN_BALANCE.md` 93–112,
> `KONSEP_NYERI.md` 113–132, `KONSEP_RISIKO_JATUH_LANJUTAN.md` 133–152,
> `KONSEP_ASESMEN_GIZI.md` 153–172, `KONSEP_FARMAKASI.md` 173–192,
> `KONSEP_PENUNJANG_MEDIS_LANJUTAN.md` 193–212. Band yang lebih tinggi dipakai
> `KONSEP_FORMULIR_RUJUKAN.md` 233–252 dan seterusnya.
>
> Angka 1–6 di atas adalah **urutan posisi** di bawah menu 12, bukan PK. Tanpa
> `dashboard_menu_sub_extra` → `id_dash_menu` = `"12.213"` dst.

`Str::slug()` dan `Str::studly()`:

| `nama_sub_menu` | slug | Studly (folder) |
|---|---|---|
| Kriteria Masuk ICU | `kriteria_masuk_icu` | `KriteriaMasukIcu` |
| Kriteria Keluar ICU | `kriteria_keluar_icu` | `KriteriaKeluarIcu` |
| Pre Hemodialisa | `pre_hemodialisa` | `PreHemodialisa` |
| Intra Hemodialisa | `intra_hemodialisa` | `IntraHemodialisa` |
| Post Hemodialisa | `post_hemodialisa` | `PostHemodialisa` |
| Triage Hemodialisa | `triage_hemodialisa` | `TriageHemodialisa` |

> Tidak ada jebakan akronim di file ini (bandingkan SBAR → `Sbar`, ADIME →
> `Adime`). Tapi perhatikan **`Icu` bukan `ICU`**.

### 1.2 Alokasi objek

| Form | Objek baru | Rentang |
|---|---|---|
| 52 Kriteria Masuk ICU | 447–457 | 11 |
| 53 Kriteria Keluar ICU | 458–471 | 14 |
| 102 Pre Hemodialisa | 473–518 | 46 |
| 103 Intra Hemodialisa | 519–528 | 10 |
| 104 Post Hemodialisa | 529–532 | 4 |
| 105 Triage Hemodialisa | 533–535 | 3 |
| **Total** | | **447–535 (88 objek)** |

> **Id band yang tidak terpakai:** **472** (satu id dicadangkan, tidak
> dideklarasikan di `$objeks`). Band objek 447–535 tidak berubah; `id_dash_menu`
> tetap `12.213`–`12.218`.
>
> Objek 473–518 milik form 102 dan **tidak boleh dipakai form 103** — sebelumnya
> form 102 memakai 521/522/523 yang sebenarnya milik form 103, sehingga
> `lama_hd_rencana` bentrok dengan `bb_sebelumnya` dan `screening_akses_tanggal`
> dengan `qb`. Kedua belah kini memakai id masing-masing (lihat §5.5).

Objek **yang di-reuse**:

| objek_id | nama_objek | Dipakai di |
|---|---|---|
| 1 | Subjective (S) | 102 (subjektif pre-HD) |
| 2 | Objective (O) | 102 (objektif pre-HD) |
| 4 | Planning (P) | 102 (planning / tindakan keperawatan) |
| 6 | Tekanan Darah Sistolik | 52, 53, 102–105 |
| 7 | Tekanan Darah Diastolik | 52, 53, 102–105 |
| 8 | Berat Badan | 102–105 (BB saat ini) |
| 10 | Nadi | 52, 53, 102–105 |
| 11 | Suhu | 53, 102–105 |
| 12 | Pernapasan | 102–105 |
| 15 | Saturasi Oksigen | 102–105 |
| 16 | EWS | 102–105 (`EwsHelper`) |
| 17 | Alergi | 102 (riwayat alergi) |
| 40 | Diagnosa Medis | 52, 53 |
| 51 | Kesadaran | 52, 102 (`EwsHelper` + GCS) |
| 54 | GCS Eye | 52, 102 |
| 55 | GCS Motorik | 52, 102 |
| 56 | GCS Verbal | 52, 102 |
| 57 | GCS Score | 52, 53, 102 |
| 77 | Keterangan | 102, 103, 104 |

---

## 2. Sumber Referensi Legacy

| Yang diambil | File legacy |
|---|---|
| Kriteria masuk ICU (kriteria A–F, GCS, informed consent, prioritas I–III, kontraindikasi, TTD) | `simrs_tenriawaru/FE/lib/modul/kriteria_masuk_icu.php` (834 baris) |
| Kriteria keluar ICU (kriteria I–IV, kelayakan life support, Tensi/Nadi/Rr/Suhu/GCS/CVP, TTD anestesi) | `simrs_tenriawaru/FE/lib/modul/kriteria_keluar_icu.php` (500 baris) |
| Pre-HD (accordion A–E, data fokus lengkap, Access Screening) | `simrs_tenriawaru/FE/lib/modul/pre_hemodialisa.php` + partial `data_fokus_postHD.php`, `diagnosa_keperawatan_postHD.php`, `tindakan_keperawatan_postHD.php`, `evaluasi_postHD.php` |
| Intra-HD (data focus, KU pasien, TTV, Qb/UFR/UF Volume/D40%, lab, masalah/tindakan keperawatan, terapi obat, evaluasi) | `simrs_tenriawaru/FE/lib/modul/intra_hemodialisa.php` |
| Post-HD (data focus, KU, TBV, TTV, EWS, lab lengkap, UFG, Kt/V, Convective Volume, terapi tambahan) | `simrs_tenriawaru/FE/lib/modul/post_hemodialisa.php` |
| Evaluasi post-HD (partial) | `simrs_tenriawaru/FE/lib/modul/evaluasi_postHD.php` |
| Triage HD (keluhan, BB kering, BB sekarang, care plan, informed consent, administrasi) | `simrs_tenriawaru/FE/lib/modul/triage_hemodialisis.php` |
| Skala GCS + peta kesadaran/AVPU | `simrs_tenriawaru/FE/lib/modul/pre_hemodialisa.php` baris 244–279, `data_fokus_postHD.php` baris 250–280 |

---

## 3. Form 52 — Kriteria Masuk ICU

### 3.1 Rasional / tujuan klinis

Dokumen keputusan transfer ke ICU. Setiap kriteria klinis diberi **nilai** (hasil
pengukuran) dan **status** (terpenuhi / tidak). Kriteria yang Legacy gunakan:

| Grup | Kriteria |
|---|---|
| **A. Respirasi** | 1. Dewasa/geriatrik: RR < 10 atau > 30/mnt · 2. Anak 1 bln–1 th: < 25 atau > 60/mnt · 3. Anak 1–3 th: < 15 atau > 40/mnt · 4. Anak 3–12 th: < 15 atau > 40/mnt · 5. Anak 12–17 th: < 12 atau > 30/mnt · 6. SpO₂ < 90% · 7. Gagal nafas (*respiratory failure*) |
| **B. Tekanan darah** | 1. Sistole < 90 mmHg |
| **C. Nadi** | 1. Dewasa: < 50 (dengan gejala irama sinus) atau > 120/mnt · 2. Geriatrik: < 50 dan simptomatik atau > 100/mnt · 3. Anak 1 bln–1 th: < 70 atau > 180/mnt · 4. Anak > 1–3 th: < 70 atau > 180/mnt · 5. Anak > 3–12 th: < 60 atau > 160/mnt · 6. Anak > 12–17 th: < 50 atau > 140/mnt |
| **D. Gambaran EKG** | 1. Aritmia (VT, VF, Torsade de Pointes, AF Rapid, PVC kecuali occasional, SVT, PAC, AV Blok) · 2. PJK: IMA, iskemik luas |
| **E. Pasca bedah** | Bedah besar/khusus dengan lama operasi > 6 jam |
| **F. Pasien obat** | Pemberian obat yang perlu pemantauan khusus |
| **GCS** | E + M + V |
| **Tambahan** | Informed consent tindakan invasif · Prioritas masuk ICU (I/II/III) · Kontra indikasi masuk ICU |

### 3.2 Struktur Field

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `tanggal_kriteria` | 447 | Tanggal Kriteria ICU | date | ya | |
| `waktu_kriteria` | 448 | Waktu Kriteria ICU | time | ya | |
| `ruangan` | — | — | readonly | tidak | dari `registrasi_detail.bagian_id`, **tidak disimpan** |
| `diagnosa` | 40 | Diagnosa Medis | textarea | ya | reuse, default diagnosa primer |
| `dpjp` | 453 | DPJP / Dokter Jaga | text readonly | tidak | default `registrasi.penanggung_rawat` |
| `dpjp_icu` | 454 | DPJP ICU | select (pegawai) | ya | |
| `resp_1_nilai` … `resp_7_nilai` | 449 | Nilai Kriteria Klinis | number | ya | 7 item |
| `td_nilai` | 449 | Nilai Kriteria Klinis | number | ya | reuse objek |
| `nadi_1_nilai` … `nadi_6_nilai` | 449 | Nilai Kriteria Klinis | number | ya | 6 item, reuse |
| `ekg_1_nilai`, `ekg_2_nilai` | 449 | Nilai Kriteria Klinis | text | ya | 2 item, reuse |
| `pasca_bedah_nilai` | 449 | Nilai Kriteria Klinis | text | ya | reuse |
| `obat_nilai` | 449 | Nilai Kriteria Klinis | text | ya | reuse |
| `resp_1_status` … `resp_7_status` | 450 | Status Kriteria Klinis | radio `YA`/`TIDAK` | ya | 7 item |
| `td_status` | 450 | Status Kriteria Klinis | radio | ya | |
| `nadi_1_status` … `nadi_6_status` | 450 | Status Kriteria Klinis | radio | ya | |
| `ekg_1_status`, `ekg_2_status` | 450 | Status Kriteria Klinis | radio | ya | |
| `pasca_bedah_status` | 450 | Status Kriteria Klinis | radio | ya | |
| `obat_status` | 450 | Status Kriteria Klinis | radio | ya | |
| `gcs_e` | 54 | GCS Eye | select 1–4 | ya | reuse |
| `gcs_m` | 55 | GCS Motorik | select 1–6 | ya | reuse |
| `gcs_v` | 56 | GCS Verbal | select 1–5 | ya | reuse |
| `gcs_jumlah` | 57 | GCS Score | number | **turunan** | reuse; `gcs_e + gcs_m + gcs_v` |
| `informed_consent` | 455 | Informed Consent Tindakan Invasif | radio `YA`/`TIDAK` | ya | oksigen, kateter, NGT, intubasi |
| `prioritas_icu` | 456 | Prioritas Masuk ICU | radio `I`/`II`/`III` | ya | |
| `kontra_indikasi` | 457 | Kontra Indikasi Masuk ICU | radio `Ada`/`Tidak` | ya | |
| `jumlah_kriteria_terpenuhi` | 451 | Jumlah Kriteria Terpenuhi | number readonly | **turunan** | server |
| `kesimpulan` | 452 | Kesimpulan Kriteria Masuk ICU | badge readonly | **turunan** | server |
| `catatan` | 77 | Keterangan | textarea | tidak | reuse |

> **Pola "satu objek, banyak variabel bersuffix"** dipakai untuk
> `*_nilai` (objek 449, 18 variabel) dan `*_status` (objek 450, 18 variabel).
> Sah skema (`objek_form_control` = penunjuk; preseden objek 62 di form 3), dan
> mandatory untuk menghindari 36 objek.

### 3.3 Komputasi server-side

```php
// app/Helpers/IcuHelper.php (BARU)
public const KRITERIA_MASUK_ICU = [
    // prefix => jumlah item
    'resp' => 7, 'td' => 1, 'nadi' => 6, 'ekg' => 2, 'pasca_bedah' => 1, 'obat' => 1,
];

public static function jumlahKriteriaTerpenuhi(array $data): int
// menjumlahkan *_status == 'YA'

public static function kesimpulan(?int $terpenuhi): ?string
// belum ada ambang resmi → lihat catatan §12.6
```

- `gcs_jumlah` = `gcs_e + gcs_m + gcs_v`, **nilai browser dibuang**.
- `jumlah_kriteria_terpenuhi` **hanya dihitung bila semua `*_status` terisi**.
  Status yang kosong membuat angka menyesatkan (menghtiung "belum ada yang
  terpenuhi" = 0). Mirip aturan `$ews['lengkap']` di `EwsHelper`.
- Kesimpulan belum bisa ditentukan karena legacy **tidak menyimpan/menampilkan
  ambang** — hanya menampilkan centang per kriteria. NusaMedika **menampilkan
  jumlah kriteria** + centang, dan `kesimpulan` diisi manual (opsional).

### 3.4 Dashboard & Form Master

```php
['dashboard_menu_sub_id' => 213, 'dashboard_menu_id' => 12, 'nama_sub_menu' => 'Kriteria Masuk ICU'],
['form_id' => 52, 'nama_form' => 'Kriteria Masuk ICU', 'slug' => 'kriteria_masuk_icu', 'id_dash_menu' => '12.213', 'ri' => 1, 'rj' => 0, 'igd' => 1, 'mcu' => 0],
```

### 3.5 Mapping

```php
// $mapping[52]
52 => [
    'tanggal_kriteria'  => 447,
    'waktu_kriteria'    => 448,
    'diagnosa'          => 40,   // reuse
    'dpjp'              => 453,
    'dpjp_icu'          => 454,
    // --- Nilai kriteria (objek 449) ---
    'resp_1_nilai' => 449, 'resp_2_nilai' => 449, 'resp_3_nilai' => 449,
    'resp_4_nilai' => 449, 'resp_5_nilai' => 449, 'resp_6_nilai' => 449,
    'resp_7_nilai' => 449, 'td_nilai'     => 449,
    'nadi_1_nilai' => 449, 'nadi_2_nilai' => 449, 'nadi_3_nilai' => 449,
    'nadi_4_nilai' => 449, 'nadi_5_nilai' => 449, 'nadi_6_nilai' => 449,
    'ekg_1_nilai'  => 449, 'ekg_2_nilai'  => 449,
    'pasca_bedah_nilai' => 449, 'obat_nilai' => 449,
    // --- Status kriteria (objek 450) ---
    'resp_1_status' => 450, 'resp_2_status' => 450, 'resp_3_status' => 450,
    'resp_4_status' => 450, 'resp_5_status' => 450, 'resp_6_status' => 450,
    'resp_7_status' => 450, 'td_status'     => 450,
    'nadi_1_status' => 450, 'nadi_2_status' => 450, 'nadi_3_status' => 450,
    'nadi_4_status' => 450, 'nadi_5_status' => 450, 'nadi_6_status' => 450,
    'ekg_1_status'  => 450, 'ekg_2_status'  => 450,
    'pasca_bedah_status' => 450, 'obat_status' => 450,
    // --- GCS (reuse) ---
    'gcs_e' => 54, 'gcs_m' => 55, 'gcs_v' => 56, 'gcs_jumlah' => 57,
    // --- Penutup ---
    'informed_consent' => 455,
    'prioritas_icu'    => 456,
    'kontra_indikasi'  => 457,
    'jumlah_kriteria_terpenuhi' => 451,   // TURUNAN
    'kesimpulan'       => 452,            // TURUNAN
    'catatan'          => 77,             // reuse
],
```

**Jumlah baris `objek_form_control` form 52: 51** (18 nilai + 18 status + 15
lainnya).

### 3.6 Akses EHR

```php
// Dokter (1): full CRUD
['profesi_id' => 1, 'form_id' => 52, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
// Perawat (2): read — keputusan transfer bukan kewenangan perawat
['profesi_id' => 2, 'form_id' => 52, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
// Dokter Intensivis (profesi baru 16): read
['profesi_id' => 16, 'form_id' => 52, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
```

### 3.7 Validasi

| Field | Aturan |
|---|---|
| `tanggal_kriteria` / `waktu_kriteria` | `required\|date` / `required\|date_format:H:i` |
| `diagnosa` | `required\|string\|max:2000` |
| `dpjp_icu` | `required\|integer\|exists:pegawai,pegawai_id` |
| `resp_1_nilai` … `resp_5_nilai` | `required\|integer\|min:0\|max:120` (frekuensi napas) |
| `resp_6_nilai` | `required\|numeric\|min:0\|max:100` (SpO₂) |
| `resp_7_nilai` | `nullable\|string\|max:100` (deskripsi) |
| `td_nilai` | `required\|integer\|min:30\|max:300` (sistolik) |
| `nadi_N_nilai` | `required\|integer\|min:0\|max:250` |
| `ekg_N_nilai`, `pasca_bedah_nilai`, `obat_nilai` | `nullable\|string\|max:200` |
| `*_status` (18 field) | `required\|in:YA,TIDAK` |
| `gcs_e` / `gcs_m` / `gcs_v` | `required\|integer\|between:1,4` / `between:1,6` / `between:1,5` |
| `informed_consent` | `required\|in:YA,TIDAK` |
| `prioritas_icu` | `required\|in:I,II,III` |
| `kontra_indikasi` | `required\|in:Ada,Tidak` |

> Group A (respirasi) hanya relevan pada **satu** kelompok usia. Bila usia anak,
> criteria anak lain di-*disable* di UI — tetapi **server tetap mewajibkan semua
> 7 `*_status`** untuk menjaga keseragaman data. Alternatif: jangan mewajibkan
> yang tidak relevan (lihat §12.5).

### 3.8 Cetak

`/emr/kriteria_masuk_icu/print/{emr_id}` — formulir A4 dua kolom: Diagnosa +
tanda tangan DPJP & DPJP ICU di atas, tabel kriteria A–F di tengah, GCS +
informed consent + prioritas + kontraindikasi di bawah. **Wajib** karena
formulir ini diarsipkan sebagai dokumen keputusan masuk ICU.

---

## 4. Form 53 — Kriteria Keluar ICU

### 4.1 Rasional / tujuan klinis

Dokumen keputusan keluar ICU. Empat kriteria (verbatim dari legacy):

| # | Kriteria |
|---|---|
| **I** | **Pasien tidak lagi memerlukan alat atau obat untuk life-support** — 8 butir: Masker NRM · Masker RM · Jackson Rees · Ventilator · Dopamin · Dobutamin · Vasocon · Adrenalin |
| **II** | Terapi telah dinyatakan gagal, prognosis jangka pendek jelek, dan manfaat kelanjutan terapi intensif kecil (gagal multi organ tidak berespons terhadap terapi agresif) |
| **III** | **Pasien dalam kondisi stabil normal** (sesuai parameter *base line*) dan kemungkinan kebutuhan terapi intensif mendadak kecil/kurang — Tensi (mmHg) · Nadi (x/mnt) · Rr (x/mnt) · Suhu (°C) · GCS · CVP (mmHg) |
| **IV** | Manfaat terapi intensif kecil karena penyakit primernya sudah terminal, tidak berespons terhadap terapi ICU untuk penyakit akutnya, prognosis jangka pendek kecil, dan tidak ada terapi potensial untuk memperbaiki prognosisnya — ada kolom **Lain-lain** |

> **Perbaikan klinis terhadap legacy:** butir I di legacy memakai checkbox
> `YA` untuk *keberadaan* alat. Itu ambigu — apakah "ada alat" (favorit) atau
> "tidak memerlukan alat"? NusaMedika memakai label eksplisit:
> **"Masih membutuhkan (YA) / Sudah tidak membutuhkan (TIDAK)"** per butir, dan
> `kr1_status` = YA bila **semua** butir bernilai TIDAK.

### 4.2 Struktur Field

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `tanggal_keluar_icu` | 458 | Tanggal Keluar ICU | date | ya | |
| `waktu_keluar_icu` | 459 | Waktu Keluar ICU | time | ya | |
| `ruangan` | — | — | readonly | tidak | dari `registrasi_detail.bagian_id` |
| `diagnosa_keluar_icu` | 460 | Diagnosa Keluar ICU | textarea | ya | |
| `dokter_merawat` | — | — | readonly | tidak | dari `registrasi.penanggung_rawat`, **tidak disimpan** |
| `dokter_konsultan_icu` | 461 | Dokter Konsultan ICU | select (pegawai) | ya | anestesi/intensivist |
| `kr1_masker_nrm` | 462 | Kelayakan Life Support | radio `YA`/`TIDAK` | ya | 8 butir berbagi objek |
| `kr1_masker_rm` | 462 | Kelayakan Life Support | radio | ya | |
| `kr1_jackson_rees` | 462 | Kelayakan Life Support | radio | ya | |
| `kr1_ventilator` | 462 | Kelayakan Life Support | radio | ya | |
| `kr1_dopamin` | 462 | Kelayakan Life Support | radio | ya | |
| `kr1_dobutamin` | 462 | Kelayakan Life Support | radio | ya | |
| `kr1_vascon` | 462 | Kelayakan Life Support | radio | ya | |
| `kr1_adrenalin` | 462 | Kelayakan Life Support | radio | ya | |
| `kr1_status` | 471 | Status Kelayakan Keluar ICU | radio `YA`/`TIDAK` | **turunan** | YA bila semua 8 butir = TIDAK |
| `kr2_status` | 463 | Status Kriteria Keluar ICU | radio `YA`/`TIDAK` | ya | kriteria II |
| `kr3_status` | 471 | Status Kelayakan Keluar ICU | radio | ya | kriteria III |
| `kr3_tensi` | 464 | Parameter Stabil — Tekanan Darah | number | ya | mmHg |
| `kr3_nadi` | 465 | Parameter Stabil — Nadi | number | ya | x/mnt |
| `kr3_rr` | 466 | Parameter Stabil — Frekuensi Pernapasan | number | ya | x/mnt |
| `kr3_suhu` | 467 | Parameter Stabil — Suhu | number | ya | °C |
| `kr3_gcs` | 57 | GCS Score | number | ya | reuse |
| `kr3_cvp` | 468 | Parameter Stabil — CVP | number | ya | mmHg |
| `kr4_status` | 471 | Status Kelayakan Keluar ICU | radio | ya | kriteria IV |
| `kr4_lain` | 469 | Keterangan Kriteria IV | textarea | tidak | |
| `kesimpulan` | 470 | Kesimpulan Keluar ICU | badge readonly | **turunan** | server |
| `catatan` | 77 | Keterangan | textarea | tidak | reuse |

### 4.3 Komputasi server-side

```php
// app/Helpers/IcuHelper.php
public const BUTIR_LIFE_SUPPORT = [
    'kr1_masker_nrm', 'kr1_masker_rm', 'kr1_jackson_rees', 'kr1_ventilator',
    'kr1_dopamin', 'kr1_dobutamin', 'kr1_vascon', 'kr1_adrenalin',
];

public static function kr1Status(array $butir): ?string
// 'YA' bila SEMUA butir = 'TIDAK'; null bila ada butir kosong

public static function kesimpulanKeluar(array $kriteria): ?string
// 'LAYAK' bila kr1 = YA DAN salah satu dari kr2/kr3/kr4 = YA
// 'TIDAK LAYAK' bila kr2 = YA ATAU kr4 = YA (alasan Exit With Voluntary…
// null bila data belum lengkap
```

> **Aturan klinis Exit With Voluntary / Withholding Life-Sustaining Treatment
> (WLSBT) Legacy tidak ada.** NusaMedika hanya menampilkan kesimpulan berbasis
> kriteria I–IV apa adanya. **Rekomendasi klinis:** tambahkan tombol
> "Terapkan WLSBT (DNR)" sebagai field terpisah sebelum dipakai dimute —
> keputusan penatalaksanaan akhir pasien terminal tidak boleh hanya dengan
> mencentang kriteria II/IV (lihat §12.6).

### 4.4 Dashboard & Form Master

```php
['dashboard_menu_sub_id' => 214, 'dashboard_menu_id' => 12, 'nama_sub_menu' => 'Kriteria Keluar ICU'],
['form_id' => 53, 'nama_form' => 'Kriteria Keluar ICU', 'slug' => 'kriteria_keluar_icu', 'id_dash_menu' => '12.214', 'ri' => 1, 'rj' => 0, 'igd' => 0, 'mcu' => 0],
```

### 4.5 Mapping

```php
// $mapping[53]
53 => [
    'tanggal_keluar_icu'     => 458,
    'waktu_keluar_icu'       => 459,
    'diagnosa_keluar_icu'    => 460,
    'dokter_konsultan_icu'   => 461,
    'kr1_masker_nrm'         => 462,
    'kr1_masker_rm'          => 462,
    'kr1_jackson_rees'       => 462,
    'kr1_ventilator'         => 462,
    'kr1_dopamin'            => 462,
    'kr1_dobutamin'          => 462,
    'kr1_vascon'             => 462,
    'kr1_adrenalin'          => 462,
    'kr1_status'             => 471,   // TURUNAN
    'kr2_status'             => 463,
    'kr3_status'             => 471,
    'kr3_tensi'              => 464,
    'kr3_nadi'               => 465,
    'kr3_rr'                 => 466,
    'kr3_suhu'               => 467,
    'kr3_gcs'                => 57,    // reuse
    'kr3_cvp'                => 468,
    'kr4_status'             => 471,
    'kr4_lain'               => 469,
    'kesimpulan'             => 470,   // TURUNAN
    'catatan'                => 77,    // reuse
],
```

**Jumlah baris mapping: 25.**

### 4.6 Akses EHR

```php
['profesi_id' => 1,  'form_id' => 53, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 2,  'form_id' => 53, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
['profesi_id' => 16, 'form_id' => 53, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
['profesi_id' => 17, 'form_id' => 53, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 0],
```

> `profesi_id = 17` = **"Dokter Anestesi"** (baru, §11.2, `ALOKASI_ID_GLOBAL.md` §5) —
> pihak yang berwenang memutuskan keluar ICU.

### 4.7 Validasi

| Field | Aturan |
|---|---|
| `tanggal_keluar_icu` / `waktu_keluar_icu` | `required\|date` / `required\|date_format:H:i` |
| `diagnosa_keluar_icu` | `required\|string\|max:2000` |
| `dokter_konsultan_icu` | `required\|integer\|exists:pegawai,pegawai_id` |
| `kr1_*` (8 butir) | `required\|in:YA,TIDAK` |
| `kr2_status`, `kr3_status`, `kr4_status` | `required\|in:YA,TIDAK` |
| `kr3_tensi` | `required\|integer\|min:30\|max:300` |
| `kr3_nadi` | `required\|integer\|min:0\|max:250` |
| `kr3_rr` | `required\|integer\|min:0\|max:120` |
| `kr3_suhu` | `required\|numeric\|between:25,45` |
| `kr3_gcs` | `required\|integer\|between:3,15` |
| `kr3_cvp` | `required\|integer\|min:0\|max:50` |
| `kr4_lain` | `nullable\|string\|max:2000` |

**Validasi silang (server):**
1. Bila `kr2_status = YA` **atau** `kr4_status = YA` ⇒ `diagnosa_keluar_icu`
   dan `catatan` **wajib** diisi (alasan psikologis/legal withdrawal).
2. `tanggal_keluar_icu` tidak boleh sebelum `registrasi_detail.tgl_daftar`.

### 4.8 Cetak

`/emr/kriteria_keluar_icu/print/{emr_id}` — 4 kriteria dengan kolom YA/TIDAK,
tabel parameter stabil, TTD Dokter Anestesi/ICU. Arsip wajib.

---

## 5. Form 102 — Pre Hemodialisa

### 5.1 Rasional / tujuan klinis

Pengkajian pra-sesi HD. Legacy menyusunnya sebagai **accordion 5 bagian**:

```
A. DATA FOKUS          (data_fokus_postHD.php — 61 KB, paling besar)
B. DIAGNOSA KEPERAWATAN(diagnosa_keperawatan_postHD.php)
C. TINDAKAN KEPERAWATAN(tindakan_keperawatan_postHD.php)
D. EVALUASI            (evaluasi_postHD.php)
E. SCREENING AKSES     (screening_akses.php)
```

Blok A memuat rincian berikut:

| Blok | Field legacy (`variabel` → objek legacy) |
|---|---|
| Subjektif | `subjective` (1) |
| Objektif | `objective` (2) |
| BB Awal | `bb_awal` |
| Type mesin HD | `type_hd` (743) |
| Riwayat Alergi | `riwayat_alergi` |
| Dialiser | `dialiser` |
| TD | `sistolik` (6) / `diastolik` (7) |
| Nadi | `nadi` |
| RR | `pernapasan_hd` |
| Suhu | `suhu` |
| Berat Badan | `bb` (BB saat ini), `bb_sekarang` (700), `bb_kering` (702) |
| EWS | `ews` |
| Kesadaran + GCS | `gcs_eye` / `gcs_motorik` / `gcs_verbal` / `gcs_score` |
| HEPARINISASI | `dosis_sirkulasi`, `dosis` (bolus), `intervensi` (maintenance, 144), `tanpa_hipernasi` |
| Dialyzer/Dialysat/Akses | `dializer` (722), `dialisat` (723), `fistula` (724), `graft` (725), `sndl` (726), `cdl` (1245), `cdl_long` (1348), `femoral` (727) |
| Jam | `jam_mulai_op`, `jam_selesai_op` (343/344), `selisih_waktu_op` (345) |
| Ultra Filtrasi | `uf` (711), `qb` (712), `tbv` (713) |
| Laboratorium | `tgl_laborat` (728), `jam_laborat` (729), `hb` (583), `ureum` (703), `creatinine` (704), `hbsag` (589), `kalium` (705), `sgot` (706), `sgpt` (707) |
| Terapi tambahan | `transfusi` (730), `d40` (731), `gluconas` (732), `kcl` (733), `renxamin` (734), `erythropoietin` (1248) |
| Riwayat psikososial | `kendala_komunikasi` (1342), `perawat_rumah` (1111), `assesment_gaduh_gelisah` (263) |
| Lain | `riwayat_kesehatan_saat_ini`, `hasil_pemeriksaan_lain` (708) |

### 5.2 Struktur Field

**Identitas**

| variabel | objek_id | nama_objek | tipe | wajib |
|---|---|---|---|---|
| `tanggal_pertemuan` | 473 | Tanggal Pengkajian HD | date | ya |
| `waktu_pertemuan` | 474 | Waktu Pengkajian HD | time | ya |

**A. Data Fokus**

| variabel | objek_id | nama_objek | tipe | wajib |
|---|---|---|---|---|
| `subjective` | 1 | Subjective (S) | textarea | ya |
| `objective` | 2 | Objective (O) | textarea | ya |
| `bb_awal` | 477 | Berat Badan Awal Sesi HD | number (kg) | ya |
| `berat_badan` | 8 | Berat Badan | number (kg) | ya |
| `bb_kering` | 478 | Berat Badan Kering | number (kg) | ya |
| `type_hd` | 475 | Type Mesin HD | text | ya |
| `riwayat_alergi` | 17 | Alergi | textarea | ya |
| `dialiser` | 476 | Dialiser | text | ya |
| `td_sistolik` | 6 | Tekanan Darah Sistolik | number | ya |
| `td_diastolik` | 7 | Tekanan Darah Diastolik | number | ya |
| `nadi` | 10 | Nadi | number | ya |
| `pernapasan` | 12 | Pernapasan | number | ya |
| `suhu` | 11 | Suhu | number | ya |
| `saturasi` | 15 | Saturasi Oksigen | number | ya |
| `ews` | 16 | EWS | number | **turunan** `EwsHelper` |
| `kesadaran` | 51 | Kesadaran | select | ya |
| `gcs_e` / `gcs_m` / `gcs_v` / `gcs_jumlah` | 54/55/56/57 | GCS … | select / turunan | ya |
| `dializer` | 482 | Jenis Dialyzer | select | ya |
| `dialisat` | 483 | Jenis Dialysat | checkbox | tidak |
| `akses_fistula` | 484 | Jenis Akses Vaskuler | checkbox | ya |
| `akses_graft` | 484 | Jenis Akses Vaskuler | checkbox | ya |
| `akses_sndl` | 484 | Jenis Akses Vaskuler | checkbox | ya |
| `akses_cdl` | 484 | Jenis Akses Vaskuler | checkbox | ya |
| `akses_cdl_thermo` | 484 | Jenis Akses Vaskuler | checkbox | ya |
| `akses_femoral` | 484 | Jenis Akses Vaskuler | checkbox | ya |

**Heparinisasi**

| variabel | objek_id | nama_objek | tipe | wajib |
|---|---|---|---|---|
| `dosis_sirkulasi` | 485 | Dosis Sirkulasi (Heparin) | text | ya |
| `dosis_bolus` | 486 | Dosis Awal / Bolus (Heparin) | text | ya |
| `dosis_maintenance` | 487 | Dosis Maintenance (Heparin) | number (UI/jam) | ya |
| `tanpa_heparinisasi` | 488 | Tanpa Heparinisasi — Penyebab | textarea | **wajib bila `dosis_maintenance = 0`** |
| `prog_bilas` | 489 | Prog Bilas NS 0,9% 100 cc/jam | text | ya |

**Waktu & ultrafiltrasi**

| variabel | objek_id | nama_objek | tipe | wajib |
|---|---|---|---|---|
| `jam_mulai` | 490 | Jam Mulai HD | time | ya |
| `jam_selesai` | 491 | Jam Selesai HD | time | ya |
| `lama_hd` | 492 | Lama Dialisis (menit) | number | **turunan** (selisih jam) |
| `uf` | 493 | Ultra Filtrasi (ml) | number | ya |
| `qb` | 494 | Qb / Aliran Darah (ml/mnt) | number | ya |
| `tbv` | 495 | TBV / Total Blood Volume (ml) | number | tidak |

**Laboratorium**

| variabel | objek_id | nama_objek | tipe | wajib |
|---|---|---|---|---|
| `tgl_laborat` | 496 | Tanggal Laboratorium | date | ya |
| `jam_laborat` | 497 | Waktu Laboratorium | time | ya |
| `hb` | 498 | Hb (g/dL) | number | ya |
| `ureum` | 499 | Ureum (mg/dL) | number | ya |
| `creatinine` | 500 | Creatinine (mg/dL) | number | ya |
| `hbsag` | 501 | HBsAg | select `Negatif`/`Positif` | ya |
| `kalium` | 502 | Kalium (mEq/L) | number | ya |
| `sgot` | 503 | SGOT (U/L) | number | ya |
| `sgpt` | 504 | SGPT (U/L) | number | ya |
| `hasil_pemeriksaan_lain` | 508 | Hasil Pemeriksaan Lain | textarea | tidak |

**Terapi tambahan**

| variabel | objek_id | nama_objek | tipe | wajib | satuan |
|---|---|---|---|---|---|
| `transfusi` | 505 | Transfusi Darah | number | tidak | ml/paket |
| `d40` | 506 | D 40% | number | tidak | ampul |
| `ca_glukonas` | 507 | Ca. Gluconas | number | tidak | ampul |
| `kcl` | 509 | KCL | number | tidak | vial |
| `renxamin` | 510 | Renxamin | number | tidak | ml |
| `erythropoietin` | 511 | Erythropoietin | number | tidak | IU |

**Psikososial & asesmen**

| variabel | objek_id | nama_objek | tipe | wajib |
|---|---|---|---|---|
| `riwayat_kesehatan_saat_ini` | 512 | Riwayat Kesehatan Saat Ini | textarea | ya |
| `komunikasi_ada_kendala` | 513 | Kendala Komunikasi | checkbox | ya |
| `komunikasi_tidak_ada` | 513 | Kendala Komunikasi | checkbox | ya |
| `komunikasi_lain` | 513 | Kendala Komunikasi | checkbox | ya |
| `komunikasi_keterangan` | 513 | Kendala Komunikasi | text | ya (bila `komunikasi_lain`) |
| `perawat_rumah_ada` | 514 | Riwayat Psikososial / Perawat Rumah | checkbox | ya |
| `perawat_rumah_tidak` | 514 | Riwayat Psikososial / Perawat Rumah | checkbox | ya |
| `perawat_rumah_lain` | 514 | Riwayat Psikososial / Perawat Rumah | checkbox | ya |
| `perawat_rumah_keterangan` | 514 | Riwayat Psikososial / Perawat Rumah | text | ya (bila `lain`) |
| `gaduh_kondisi_saat_ini` | 515 | Asesmen Gaduh Gelisah | checkbox | ya |
| `gaduh_tenang` | 515 | Asesmen Gaduh Gelisah | checkbox | ya |
| `gaduh_gelisah` | 515 | Asesmen Gaduh Gelisah | checkbox | ya |
| `gaduh_takut_tindakan` | 515 | Asesmen Gaduh Gelisah | checkbox | ya |
| `gaduh_marah` | 515 | Asesmen Gaduh Gelisah | checkbox | ya |
| `gaduh_mudah_tersinggung` | 515 | Asesmen Gaduh Gelisah | checkbox | ya |

**B–D. Asuhan keperawatan & rencana sesi berikutnya**

| variabel | objek_id | nama_objek | tipe | wajib |
|---|---|---|---|---|
| `diagnosa_keperawatan` | 516 | Diagnosa Keperawatan | textarea | ya |
| `planning` | 4 | Planning (P) | textarea | ya |
| `evaluasi` | 517 | Evaluasi Keperawatan | textarea | ya |
| `tanggal_hd_berikutnya` | 518 | Tanggal HD Berikutnya | date | ya |
| `lama_hd_rencana` | 479 | Lama HD Rencana (jam) | number | ya |

**E. Screening Akses**

| variabel | objek_id | nama_objek | tipe | wajib |
|---|---|---|---|---|
| `screening_akses_tanggal` | 480 | Tanggal Screening Akses | date | ya |
| `screening_akses_catatan` | 481 | Catatan Screening Akses | textarea | tidak |

> Objek **524–528** disimpan untuk Intra/Post HD (§6, §7) — dipakai bersama agar
> "Lama Dialisis (menit)" & "Lama HD Rencana (jam)" tetap satu makna.
>
> Objek 479–481 menutup form 102 (rencana sesi berikutnya & screening akses).
> Sebelumnya ketiganya memakai 521/522/523 yang kini menjadi milik form 103
> (`bb_sebelumnya`, `qb`, `ufr`) — dipakai bersama agar
> "Berat Badan Sesi Sebelumnya", "Qb / Aliran Darah", dan "UFR" masing-masing
> punya satu makna. Band objek tidak berubah.

### 5.3 Opsi enum

| Field | Opsi |
|---|---|
| `dializer` | `F6HPS`, `F8 HPS`, `ELISIO 15 H`, `FX 80 CLASSIX`, `Lainnya` (bebas) |
| `dialisat` | `Bicarbonate`, `Acetate`, `Citrate` |
| `akses_*` | `AV. Fistula`, `AV. Graft`, `SN`, `CDL`, `CDL Long Therm`, `CDL Short Therm`, `Femoral` |
| `hbsag` | `Negatif`, `Positif` |
| `komunikasi_*` | `Ada kendala`, `Tidak ada`, `Lainnya` |
| `perawat_rumah_*` | `Ya ada yang merawat di rumah`, `Tidak ada`, `Lainnya` |
| `gaduh_*` | `Kondisi saat ini`, `Tenang`, `Gelisah`, `Takut terhadap tindakan`, `Marah`, `Mudah tersinggung` |

> Legacy memakai array `$arr_cara` berisi `Nasal Canul`, `Non Rebreathing Mask`,
> `Rebreathing Mask`, `Ventury`, `CPAP`, `Voltran` untuk HWHD — **tidak
> diimplementasikan** di form ini (belum ada form HD/CVVHD ter Dedicated).

### 5.4 Dashboard & Form Master

```php
['dashboard_menu_sub_id' => 215, 'dashboard_menu_id' => 12, 'nama_sub_menu' => 'Pre Hemodialisa'],
['form_id' => 102, 'nama_form' => 'Pre Hemodialisa', 'slug' => 'pre_hemodialisa', 'id_dash_menu' => '12.215', 'ri' => 1, 'rj' => 0, 'igd' => 1, 'mcu' => 0],
```

### 5.5 Mapping

```php
// $mapping[102]
102 => [
    'tanggal_pertemuan' => 473, 'waktu_pertemuan' => 474,

    // A. Data Fokus
    'subjective' => 1, 'objective' => 2,                       // reuse
    'bb_awal' => 477, 'berat_badan' => 8, 'bb_kering' => 478,   // reuse 8
    'type_hd' => 475, 'riwayat_alergi' => 17, 'dialiser' => 476,
    'td_sistolik' => 6, 'td_diastolik' => 7, 'nadi' => 10,
    'pernapasan' => 12, 'suhu' => 11, 'saturasi' => 15,         // reuse
    'ews' => 16, 'kesadaran' => 51,                             // reuse, TURUNAN
    'gcs_e' => 54, 'gcs_m' => 55, 'gcs_v' => 56, 'gcs_jumlah' => 57, // reuse
    'dializer' => 482, 'dialisat' => 483,
    'akses_fistula' => 484, 'akses_graft' => 484, 'akses_sndl' => 484,
    'akses_cdl' => 484, 'akses_cdl_thermo' => 484, 'akses_femoral' => 484,

    // Heparinisasi
    'dosis_sirkulasi' => 485, 'dosis_bolus' => 486,
    'dosis_maintenance' => 487, 'tanpa_heparinisasi' => 488, 'prog_bilas' => 489,

    // Waktu & ultrafiltrasi
    'jam_mulai' => 490, 'jam_selesai' => 491, 'lama_hd' => 492,   // TURUNAN
    'uf' => 493, 'qb' => 494, 'tbv' => 495,

    // Laboratorium
    'tgl_laborat' => 496, 'jam_laborat' => 497,
    'hb' => 498, 'ureum' => 499, 'creatinine' => 500,
    'hbsag' => 501, 'kalium' => 502, 'sgot' => 503, 'sgpt' => 504,
    'hasil_pemeriksaan_lain' => 508,

    // Terapi tambahan
    'transfusi' => 505, 'd40' => 506, 'ca_glukonas' => 507,
    'kcl' => 509, 'renxamin' => 510, 'erythropoietin' => 511,

    // Psikososial
    'riwayat_kesehatan_saat_ini' => 512,
    'komunikasi_ada_kendala' => 513, 'komunikasi_tidak_ada' => 513,
    'komunikasi_lain' => 513, 'komunikasi_keterangan' => 513,
    'perawat_rumah_ada' => 514, 'perawat_rumah_tidak' => 514,
    'perawat_rumah_lain' => 514, 'perawat_rumah_keterangan' => 514,
    'gaduh_kondisi_saat_ini' => 515, 'gaduh_tenang' => 515,
    'gaduh_gelisah' => 515, 'gaduh_takut_tindakan' => 515,
    'gaduh_marah' => 515, 'gaduh_mudah_tersinggung' => 515,

    // Asuhan keperawatan & rencana sesi berikutnya
    'diagnosa_keperawatan' => 516,
    'planning' => 4,                                              // reuse Planning (P)
    'evaluasi' => 517,
    'tanggal_hd_berikutnya' => 518,
    'lama_hd_rencana' => 479,

    // E. Screening Akses
    'screening_akses_tanggal' => 480,
    'screening_akses_catatan' => 481,
    'keterangan' => 77,                                           // reuse
],
```

**Jumlah baris mapping form 102: 80.**

### 5.6 Akses EHR

```php
['profesi_id' => 1, 'form_id' => 102, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 2, 'form_id' => 102, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 0],
['profesi_id' => 18, 'form_id' => 102, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 0],
```

> `profesi_id = 18` = **"Perawat Hemodialisa"** (baru, §11.2, `ALOKASI_ID_GLOBAL.md` §5).

### 5.7 Validasi

| Field | Aturan |
|---|---|
| `tanggal_pertemuan` / `waktu_pertemuan` | `required\|date` / `required\|date_format:H:i` |
| `bb_awal`, `berat_badan`, `bb_kering` | `required\|numeric\|min:20\|max:300` |
| `bb_kering` | ≤ `berat_badan` (validasi silang server) |
| `td_sistolik` / `td_diastolik` | `required\|integer\|min:50\|max:300` / `min:30\|max:200` |
| `nadi`, `pernapasan` | `required\|integer\|min:0\|max:250` / `min:0\|max:120` |
| `suhu` | `required\|numeric\|between:30,45` |
| `saturasi` | `required\|numeric\|min:50\|max:100` |
| `kesadaran` | `required\|string` (`SelectOption::get('kesadaran')`) |
| `gcs_e/m/v` | `required\|integer\|between:1,4` / `between:1,6` / `between:1,5` |
| `dializer`, `dialiser`, `type_hd` | `required\|string\|max:50` |
| `akses_*` | minimal satu tercentang (hidden `off` + checkbox `on`) |
| `dosis_sirkulasi`, `dosis_bolus` | `required\|string\|max:50` |
| `dosis_maintenance` | `required\|numeric\|min:0\|max:20000` |
| `tanpa_heparinisasi` | **wajib** bila `dosis_maintenance = 0` |
| `jam_mulai`, `jam_selesai` | `required\|date_format:H:i` · `jam_selesai > jam_mulai` |
| `uf`, `qb`, `tbv` | `required\|numeric\|min:0` / `min:0\|max:2000` / `min:0\|max:10000` |
| `hb`, `ureum`, `creatinine`, `kalium`, `sgot`, `sgpt` | `required\|numeric\|min:0` |
| `hbsag` | `required\|in:Negatif,Positif` |
| `transfusi`, `d40`, `ca_glukonas`, `kcl`, `renxamin`, `erythropoietin` | `nullable\|numeric\|min:0` |
| `diagnosa_keperawatan`, `planning`, `evaluasi`, `riwayat_kesehatan_saat_ini`, `riwayat_alergi` | `required\|string\|max:2000` |
| `tanggal_hd_berikutnya` | `required\|date\|after:tanggal_pertemuan` |
| `lama_hd_rencana` | `required\|numeric\|between:1,12` |
| `komunikasi_keterangan`, `perawat_rumah_keterangan` | **wajib** bila `*_lain` tercentang |

**Turunan (nilai browser dibuang):** `ews` (`EwsHelper::hitung()`), `gcs_jumlah`,
`lama_hd` (selisih `jam_selesai` − `jam_mulai`, dalam menit; bila jam selesai <
jam mulai ⇒ +1440).

### 5.8 Cetak

`/emr/pre_hemodialisa/print/{emr_id}` — lembar pengkajian pre-HD 2 halaman
(A–E), dipakai sebagai dokumen rekam medis hemodialisa. Wajib.

---

## 6. Form 103 — Intra Hemodialisa

### 6.1 Rasional / tujuan klinis

Monitoring **saat** sesi berjalan. Isinya lebih ringkas dari pre-HD: data fokus,
KU, TTV, parameter mesin (Qb, UFR, UF volume, D 40%), lab, dan asuhan
keperawatan selama sesi.

### 6.2 Struktur Field

| variabel | objek_id | nama_objek | tipe | wajib | reuse |
|---|---|---|---|---|---|
| `tanggal_pertemuan` | 473 | Tanggal Pengkajian HD | date | ya | reuse |
| `waktu_pertemuan` | 474 | Waktu Pengkajian HD | time | ya | reuse |
| `data_focus` | 519 | Data Focus | text | ya | |
| `ku_pasien` | 520 | KU Pasien | text | ya | |
| `td_sistolik` / `td_diastolik` | 6 / 7 | TD Sistolik / Diastolik | number | ya | reuse |
| `nadi` / `pernapasan` / `suhu` / `saturasi` | 10 / 12 / 11 / 15 | — | number | ya | reuse |
| `berat_badan` | 8 | Berat Badan | number | ya | reuse |
| `bb_sebelumnya` | 521 | Berat Badan Sesi Sebelumnya | number | ya | |
| `bb_kering` | 478 | Berat Badan Kering | number | ya | reuse |
| `ews` | 16 | EWS | number | **turunan** | reuse |
| `qb` | 522 | Qb / Aliran Darah (ml/mnt) | number | ya | |
| `ufr` | 523 | UFR / Ultrafiltration Rate (ml/jam) | number | ya | |
| `ufv` | 524 | Volume Ultrafiltrasi (L) | number | ya | |
| `d40` | 525 | D 40% (ampul) | number | ya | |
| `hb` | 498 | Hb (g/dL) | number | ya | reuse |
| `ureum` | 499 | Ureum (mg/dL) | number | ya | reuse |
| `creatinine` | 500 | Creatinine (mg/dL) | number | ya | reuse |
| `hbsag` | 501 | HBsAg | select | ya | reuse |
| `kalium` | 502 | Kalium (mEq/L) | number | ya | reuse |
| `sgot` | 503 | SGOT (U/L) | number | ya | reuse |
| `sgpt` | 504 | SGPT (U/L) | number | ya | reuse |
| `hasil_pemeriksaan_lain` | 508 | Hasil Pemeriksaan Lain | textarea | tidak | reuse |
| `masalah_keperawatan` | 526 | Masalah Keperawatan | textarea | ya | |
| `tindakan_keperawatan` | 527 | Tindakan Keperawatan | textarea | ya | |
| `terapi_obat` | 528 | Terapi Obat | textarea | ya | |
| `evaluasi` | 517 | Evaluasi Keperawatan | textarea | ya | reuse |
| `keterangan` | 77 | Keterangan | textarea | tidak | reuse |

### 6.3 Dashboard & Form Master

```php
['dashboard_menu_sub_id' => 216, 'dashboard_menu_id' => 12, 'nama_sub_menu' => 'Intra Hemodialisa'],
['form_id' => 103, 'nama_form' => 'Intra Hemodialisa', 'slug' => 'intra_hemodialisa', 'id_dash_menu' => '12.216', 'ri' => 1, 'rj' => 0, 'igd' => 1, 'mcu' => 0],
```

### 6.4 Mapping

```php
// $mapping[103]
103 => [
    'tanggal_pertemuan' => 473, 'waktu_pertemuan' => 474,   // reuse
    'data_focus' => 519, 'ku_pasien' => 520,
    'td_sistolik' => 6, 'td_diastolik' => 7,                 // reuse
    'nadi' => 10, 'pernapasan' => 12, 'suhu' => 11, 'saturasi' => 15, 'ews' => 16, // reuse
    'berat_badan' => 8, 'bb_kering' => 478,                  // reuse
    'bb_sebelumnya' => 521,
    'qb' => 522, 'ufr' => 523, 'ufv' => 524, 'd40' => 525,
    'hb' => 498, 'ureum' => 499, 'creatinine' => 500,
    'hbsag' => 501, 'kalium' => 502, 'sgot' => 503, 'sgpt' => 504,
    'hasil_pemeriksaan_lain' => 508,                         // reuse
    'masalah_keperawatan' => 526, 'tindakan_keperawatan' => 527, 'terapi_obat' => 528,
    'evaluasi' => 517,                                       // reuse
    'keterangan' => 77,                                      // reuse
],
```

**Jumlah baris mapping form 103: 31.**

### 6.5 Akses EHR

```php
['profesi_id' => 1, 'form_id' => 103, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 18, 'form_id' => 103, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 0],
['profesi_id' => 2, 'form_id' => 103, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 0],
```

### 6.6 Validasi

| Field | Aturan |
|---|---|
| `tanggal_pertemuan` / `waktu_pertemuan` | `required\|date` / `required\|date_format:H:i` |
| `data_focus`, `ku_pasien` | `required\|string\|max:2000` |
| TTV | sama seperti form 102 |
| `berat_badan`, `bb_sebelumnya`, `bb_kering` | `required\|numeric\|min:20\|max:300` |
| `berat_badan` | ≥ `bb_kering` (validasi silang) |
| `qb` | `required\|numeric\|between:0,600` |
| `ufr` | `required\|numeric\|between:0,2000` |
| `ufv` | `required\|numeric\|min:0\|max:20` |
| `d40` | `required\|numeric\|min:0\|max:20` |
| `hbsag` | `required\|in:Negatif,Positif` |
| Lab (HB, Ureum, Creatinine, Kalium, SGOT, SGPT) | `required\|numeric\|min:0` |
| `masalah_keperawatan`, `tindakan_keperawatan`, `terapi_obat`, `evaluasi` | `required\|string\|max:2000` |

> `ufv` (L) ≤ `uf`/1000 (ml → L) bila keduanya terisi — validasi silang.

### 6.7 Cetak

`/emr/intra_hemodialisa/print/{emr_id}` — lembar monitoring sesi, dipegang
perawat HD di ruang Hemodialisa. Format tabel agar bisa diisi berulang di kertas.

---

## 7. Form 104 — Post Hemodialisa

### 7.1 Rasional / tujuan klinis

Evaluasi **setelah** sesi. Menambahkan parameter yang hanya relevan
post-sesi: **UFG** (*Ultrafiltration Goal*), **Kt/V**, dan **Convective Volume**
(HDF/post-dilution).

### 7.2 Struktur Field

Struktur sama form 103, ditambah/diubah:

| variabel | objek_id | nama_objek | tipe | wajib | reuse |
|---|---|---|---|---|---|
| `tbv` | 529 | TBV / Total Blood Volume (ml) | number | ya | |
| `ufg` | 530 | UFG / Target Ultrafiltration (L) | number | ya | |
| `kt_v` | 531 | Kt/V | number | ya | |
| `convective_volume` | 532 | Convective Volume (L) | number | ya | |
| `transfusi` | 505 | Transfusi Darah | number | tidak | reuse |
| `d40` | 506 | D 40% | number | tidak | reuse |
| `ca_glukonas` | 507 | Ca. Gluconas | number | tidak | reuse |
| `kcl` | 509 | KCL | number | tidak | reuse |
| `renxamin` | 510 | Renxamin | number | tidak | reuse |
| `erythropoietin` | 511 | Erythropoietin | number | tidak | reuse |

Seluruh TTV, EWS, lab, BB (saat ini / sebelumnya / kering), masalah & tindakan
keperawatan, evaluasi **sama persis** dengan form 103 (objek 473, 474, 478, 8,
519–528, 6–16, 498–508, 517, 77).

### 7.3 Dashboard & Form Master

```php
// $subMenus — PK 217, dashboard_menu_id = 12, TANPA extra
['dashboard_menu_sub_id' => 217, 'dashboard_menu_id' => 12, 'nama_sub_menu' => 'Post Hemodialisa'],

// $forms
['form_id' => 104, 'nama_form' => 'Post Hemodialisa', 'slug' => 'post_hemodialisa', 'id_dash_menu' => '12.217', 'ri' => 1, 'rj' => 0, 'igd' => 0, 'mcu' => 0],
```

### 7.4 Mapping

```php
// $mapping[104]
104 => [
    'tanggal_pertemuan' => 473, 'waktu_pertemuan' => 474,   // reuse
    'data_focus' => 519, 'ku_pasien' => 520,                 // reuse
    'td_sistolik' => 6, 'td_diastolik' => 7,                 // reuse
    'nadi' => 10, 'pernapasan' => 12, 'suhu' => 11, 'saturasi' => 15, 'ews' => 16, // reuse
    'berat_badan' => 8, 'bb_kering' => 478,                  // reuse
    'bb_sebelumnya' => 521,                                  // reuse
    'hb' => 498, 'ureum' => 499, 'creatinine' => 500,        // reuse
    'hbsag' => 501, 'kalium' => 502, 'sgot' => 503, 'sgpt' => 504, // reuse
    'hasil_pemeriksaan_lain' => 508,                         // reuse
    'masalah_keperawatan' => 526, 'tindakan_keperawatan' => 527, 'terapi_obat' => 528, // reuse
    'evaluasi' => 517,                                       // reuse
    'keterangan' => 77,                                      // reuse
    // --- Parameter khas post-sesi ---
    'tbv'               => 529,
    'ufg'               => 530,
    'kt_v'              => 531,
    'convective_volume' => 532,
    // --- Terapi tambahan (reuse objek form 102) ---
    'transfusi'         => 505,
    'd40'               => 506,
    'ca_glukonas'       => 507,
    'kcl'               => 509,
    'renxamin'          => 510,
    'erythropoietin'    => 511,
],
```

**Jumlah baris mapping form 104: 37** (seluruh field form 103 §6.2 + 4
parameter khas post-sesi + 6 terapi tambahan).

### 7.5 Akses EHR

```php
// Dokter (1): full CRUD
['profesi_id' => 1, 'form_id' => 104, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
// Perawat Hemodialisa (profesi baru 18): create/read/update, TIDAK delete — identik form 103
['profesi_id' => 18, 'form_id' => 104, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 0],
// Perawat (2): create/read/update, TIDAK delete
['profesi_id' => 2, 'form_id' => 104, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 0],
```

### 7.6 Validasi tambahan

| Field | Aturan |
|---|---|
| `tbv` | `required\|numeric\|min:1000\|max:8000` |
| `ufg` | `required\|numeric\|min:0\|max:10` |
| `kt_v` | `required\|numeric\|min:0\|max:5` · **target ≥ 1,2** (efektivitas HD, SNKF) |
| `convective_volume` | `required\|numeric\|min:0\|max:50` |
| Field form 103 | seluruh aturan §6.6 tetap berlaku |

> **Kt/V adalah indikator mutu utama hemodialisa.** Bila `kt_v < 1.2`, sistem
> **WAJIB** menampilkan peringatan (bukan error — nilai post-HD bisa diisi
> sementara bila hasil belum keluar). Gunakan komponen `confirm-alert`, **bukan**
> `alert()`.

### 7.7 Cetak

`/emr/post_hemodialisa/print/{emr_id}` — lembar evaluasi post-sesi: blok data
fokus & KU, TTV, EWS, laboratorium pre/post, BB kering/terakhir/selisih, UFG,
Kt/V + badge adequat, Convective Volume, terapi tambahan, dan kolom TTD
perawat HD + dokter. Route non-CRUD:
`GET /emr/post_hemodialisa/print/{emr_id}`.

---

## 8. Form 105 — Triage Hemodialisa

### 8.1 Rasional / tujuan klinis

Triase awal pasien sebelum sesi HD: keluhan utama, berat badan kering &
sekarang, kesesuaian care plan, kelengkapan informed consent, dan kelengkapan
administrasi. Legacy 150 baris — form paling ringkas di file ini.

### 8.2 Struktur Field

| variabel | objek_id | nama_objek | tipe | wajib | reuse |
|---|---|---|---|---|---|
| `keluhan` | 13 | Keluhan Utama | textarea | ya | reuse |
| `bb_kering` | 478 | Berat Badan Kering | number (kg) | ya | reuse |
| `berat_badan` | 8 | Berat Badan | number (kg) | ya | reuse |
| `care_plan` | 533 | Care Plan | radio `sesuai`/`tidak_sesuai` | ya | |
| `informed_consent` | 534 | Informed Consent | checkbox `lengkap` | ya | |
| `administrasi` | 535 | Administrasi | checkbox `lengkap` | ya | |

> `informed_consent` & `administrasi` **checkbox dengan nilai `lengkap`** —
> mengikuti pola legacy persis (`value="lengkap"`, `objek_id` 1138 / 1352).

### 8.3 Dashboard & Form Master

```php
['dashboard_menu_sub_id' => 218, 'dashboard_menu_id' => 12, 'nama_sub_menu' => 'Triage Hemodialisa'],
['form_id' => 105, 'nama_form' => 'Triage Hemodialisa', 'slug' => 'triage_hemodialisa', 'id_dash_menu' => '12.218', 'ri' => 0, 'rj' => 1, 'igd' => 0, 'mcu' => 0],
```

### 8.4 Mapping

```php
// $mapping[105]
105 => [
    'keluhan'           => 13,   // reuse Keluhan Utama
    'bb_kering'         => 478,  // reuse
    'berat_badan'       => 8,    // reuse
    'care_plan'         => 533,
    'informed_consent'  => 534,
    'administrasi'      => 535,
],
```

**Jumlah baris mapping form 105: 6.**

### 8.5 Akses EHR

```php
['profesi_id' => 1, 'form_id' => 105, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 2, 'form_id' => 105, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 0],
['profesi_id' => 18, 'form_id' => 105, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 0],
```

### 8.6 Validasi

| Field | Aturan |
|---|---|
| `keluhan` | `required\|string\|max:2000` |
| `bb_kering`, `berat_badan` | `required\|numeric\|min:20\|max:300` |
| `care_plan` | `required\|in:sesuai,tidak_sesuai` |
| `informed_consent` | `nullable\|in:lengkap` (server mewajibkan nilai `lengkap`) |
| `administrasi` | `nullable\|in:lengkap` |

> **Peringatan klinis (bukan error):** bila `berat_badan - bb_kering > 3 kg`
> (gain BB) atau `berat_badan < bb_kering`, tampilkan peringatan di UI
> "Periksa pompa & akses vaskuler / status hidrasi" — **harus dihitung server**,
> bukan hanya JS.

---


## 9. Helper BARU — `HemodialisaHelper`

```php
<?php

namespace App\Helpers;

class HemodialisaHelper
{
    public const AKSES_VASKULER = [
        'akses_fistula'     => 'AV. Fistula',
        'akses_graft'       => 'AV. Graft',
        'akses_sndl'        => 'SN',
        'akses_cdl'         => 'CDL',
        'akses_cdl_thermo'  => 'CDL Long Therm',
        'akses_femoral'     => 'Femoral',
    ];

    public const DIALYSAT = [
        'Bicarbonate' => 'Bicarbonate',
        'Acetate'     => 'Acetate',
        'Citrate'     => 'Citrate',
    ];

    public const DIALYZER = ['F6HPS', 'F8 HPS', 'ELISIO 15 H', 'FX 80 CLASSIX'];

    /** Lama dialisis dalam menit; menangani jam selesai < jam mulai (sesi lintas tengah malam). */
    public static function lamaDialisis(?string $jamMulai, ?string $jamSelesai): ?int

    /** Peringatan gain/selisih berat badan; null bila data tidak lengkap. */
    public static function selisihBb(?float $bbSekarang, ?float $bbKering): array
    // ['selisih_kg' => float, 'kategori' => 'Gain BB'|'Susut BB'|'Ideal'|'Data Tidak Lengkap']

    /** Kt/V & efektivitas HD (SNKF: target >= 1,2). */
    public static function ktv(float $ktv): array
    // ['nilai' => float, 'adequat' => bool, 'label' => 'Adekuat'|'Belum Adekuat']

    /** Tekanan arteri rata-rata (MAP) dari sistolik & diastolik. */
    public static function meanArterialPressure(?float $sistolik, ?float $diastolik): ?float
}
```

Semua fungsi mengembalikan `null` (bukan `0`) bila input kosong/tidak wajar —
konsisten dengan `EwsHelper::skor()`.

---

## 10. SelectOption — key baru

```php
// app/Helpers/SelectOption.php
'dialyzer' => [
    ['value' => 'F6HPS',         'label' => 'F6HPS'],
    ['value' => 'F8 HPS',        'label' => 'F8 HPS'],
    ['value' => 'ELISIO 15 H',   'label' => 'ELISIO 15 H'],
    ['value' => 'FX 80 CLASSIX', 'label' => 'FX 80 CLASSIX'],
],
'dialysat' => [
    ['value' => 'Bicarbonate', 'label' => 'Bicarbonate'],
    ['value' => 'Acetate',     'label' => 'Acetate'],
    ['value' => 'Citrate',     'label' => 'Citrate'],
],
'akses_vaskuler' => [
    ['value' => 'AV. Fistula',      'label' => 'AV. Fistula'],
    ['value' => 'AV. Graft',        'label' => 'AV. Graft'],
    ['value' => 'SN',               'label' => 'SN'],
    ['value' => 'CDL',              'label' => 'CDL'],
    ['value' => 'CDL Long Therm',   'label' => 'CDL Long Therm / Short Therm'],
    ['value' => 'CDL Short Therm',  'label' => 'CDL Short Therm'],
    ['value' => 'Femoral',          'label' => 'Femoral'],
],
'hbsag' => [
    ['value' => 'Negatif', 'label' => 'Negatif'],
    ['value' => 'Positif', 'label' => 'Positif'],
],
'prioritas_icu' => [
    ['value' => 'I',   'label' => 'Prioritas I'],
    ['value' => 'II',  'label' => 'Prioritas II'],
    ['value' => 'III', 'label' => 'Prioritas III'],
],
'ya_tidak_icu' => [
    ['value' => 'YA',    'label' => 'YA'],
    ['value' => 'TIDAK', 'label' => 'TIDAK'],
],
'ada_tidak_icu' => [
    ['value' => 'Ada',   'label' => 'Ada'],
    ['value' => 'Tidak', 'label' => 'Tidak'],
],
'care_plan_hd' => [
    ['value' => 'sesuai',         'label' => 'Care Plan Sesuai'],
    ['value' => 'tidak_sesuai',  'label' => 'Care Plan Tidak Sesuai'],
],
'komunikasi_hd' => [
    ['value' => 'ada_kendala', 'label' => 'Ada Kendala Komunikasi'],
    ['value' => 'tidak_ada',   'label' => 'Tidak Ada Kendala'],
    ['value' => 'lain_lain',   'label' => 'Ada, Jelaskan'],
],
'gaduh_gelisah' => [
    ['value' => 'kondisi_saat_ini',     'label' => 'Kondisi Saat Ini'],
    ['value' => 'tenang',               'label' => 'Tenang'],
    ['value' => 'gelisah',              'label' => 'Gelisah'],
    ['value' => 'takut_terhadap_tindakan','label' => 'Takut Terhadap Tindakan'],
    ['value' => 'marah',                'label' => 'Marah'],
    ['value' => 'mudah_tersinggung',    'label' => 'Mudah Tersinggung'],
],
'perawat_rumah' => [
    ['value' => 'ada',   'label' => 'Ada Yang Merawat di Rumah'],
    ['value' => 'tidak', 'label' => 'Tidak Ada'],
    ['value' => 'lain',  'label' => 'Ada, Jelaskan'],
],
```

---

## 11. Implementasi

### 11.1 File yang harus dibuat

| Jenis | Path |
|---|---|
| Helper | `app/Helpers/IcuHelper.php` (kriteria ICU masuk/keluar) |
| Helper | `app/Helpers/HemodialisaHelper.php` (§9) |
| Controller | `app/Http/Controllers/EMR/KriteriaMasukIcu/KriteriaMasukIcuController.php` |
| Controller | `app/Http/Controllers/EMR/KriteriaKeluarIcu/KriteriaKeluarIcuController.php` |
| Controller | `app/Http/Controllers/EMR/PreHemodialisa/PreHemodialisaController.php` |
| Controller | `app/Http/Controllers/EMR/IntraHemodialisa/IntraHemodialisaController.php` |
| Controller | `app/Http/Controllers/EMR/PostHemodialisa/PostHemodialisaController.php` |
| Controller | `app/Http/Controllers/EMR/TriageHemodialisa/TriageHemodialisaController.php` |
| View | `resources/views/moduls/EMR/{KriteriaMasukIcu,KriteriaKeluarIcu,PreHemodialisa,IntraHemodialisa,PostHemodialisa,TriageHemodialisa}/index.blade.php` |
| View cetak | `…/{KriteriaMasukIcu,KriteriaKeluarIcu,PreHemodialisa,IntraHemodialisa,PostHemodialisa}/print.blade.php` |
| Edit | `database/seeders/EmrMasterSeeder.php` |
| Edit | `app/Helpers/SelectOption.php` |
| Edit | `routes/web.php` — 5 route `emr.{slug}.print` |
| Edit | Seeder Profesi (`profesi_id` 16, 17, 18) + `MasterPegawaiSeeder` |
| Edit | `BagianSeeder` — append `ICU` (referensi_bagian_id = 2) dan `HEMODIALISA` |
| Edit | `AGENTS.md` |

**Tidak ada migration baru** — semua form ini memakai `emr` / `emr_detail`.

### 11.2 Profesi baru

| profesi_id | Nama |
|---|---|
| 16 | Dokter Intensivis |
| 17 | Dokter Anestesi |
| 18 | Perawat Hemodialisa |

> Wajib di-seed **sebelum** `$akses`. `MasterPegawaiSeeder` diberi contoh pegawai
> aktif untuk ketiganya.

### 11.3 Checklist seeder

- [ ] `$menus` += `['dashboard_menu_id' => 12, 'nama_menu' => 'ICU & Hemodialisa']`
- [ ] `$subMenus` += PK 213–218 (`dashboard_menu_id = 12`, tanpa extra)
- [ ] `$forms` += form 52, 53, 102, 103, 104, 105
- [ ] `$objeks` += 447–535
- [ ] `$mapping[52]`, `$mapping[53]`, `$mapping[102]`, `$mapping[103]`, `$mapping[104]`, `$mapping[105]`
- [ ] `EmrHelper::backfillObjekId(52..53, 102..105)`
- [ ] `$akses` += 19 baris (52: 3 · 53: 4 · 102: 3 · 103: 3 · 104: 3 · 105: 3)
- [ ] Total baris `objek_form_control` baru: 49 + 25 + 78 + 33 + 37 + 6 = **228 baris**
- [ ] Semua `variabel` unik per form

### 11.4 Checklist controller & view

- [ ] `abort_unless(AksesEhr::can((int) $form_id, 'read'), 403)` di semua `index`
- [ ] `$emr_data['x'] ?? ''` — **bukan** `$emr_data ?? ''`
- [ ] Radio grup dibaca **seluruh elemen** (`getCheckedValue`-like), bukan `[0]`
- [ ] Checkbox multi-item: `<input type="hidden" name="x" value="off">` + checkbox `value="on"`
- [ ] `filteredData()` → `array_intersect_key()`
- [ ] Field turunan (`ews`, `gcs_jumlah`, `lama_hd`, `jumlah_kriteria_terpenuhi`, `kr1_status`, `kesimpulan`) **nilai browser dibuang**
- [ ] `@error` di setiap field
- [ ] `{{ $isView ? 'disabled' : '' }}` di `<fieldset>`
- [ ] Accordion (form 102) memakai `x-emr-accordion`; JS inline `<script>` (layouts.iframe **tidak** render `@stack('scripts')`)
- [ ] Tanpa komponen blade di dalam `<script>` (termasuk di komentar)
- [ ] Tampil/sembunyi lewat `style.display`, bukan class `hidden`
- [ ] Peringatan `Kt/V < 1.2` memakai komponen `confirm-alert` (`window.nusaConfirm`), **bukan** `alert()`
- [ ] `docker compose exec app ./vendor/bin/pint --dirty`

### 11.5 Urutan implementasi

1. Helper `IcuHelper` + `HemodialisaHelper` + unit test sederhana.
2. Profesi 16/17/18 + pegawai contoh + `BagianSeeder` (ICU, Hemodialisa).
3. Seeder `EmrMasterSeeder` (menu 12, sub 213–218, form, objek 447–535, mapping, akses).
4. Form 52 & 53 (ICU) — paling sederhana, validasi pola turunan.
5. Form 105 (Triage HD) — 6 field, paling cepat selesai.
6. Form 102 (Pre HD) — terbesar (78 mapping), gunakan accordion 5 bagian.
7. Form 103 & 104 (Intra/Post) — re-use structur form 102.
8. Cetak kelima form + update `AGENTS.md`.

---

## 12. Catatan & Risiko

| # | Risiko / Catatan | Mitigasi |
|---|---|---|
| 1 | **`id_dash_menu` wajib `"12.213"` dst., bukan `"12.1"`.** Kalau sub 213–218 tidak ter-seed, keenam form **yatim dari dashboard**. | Verifikasi `SELECT * FROM dashboard_menu_sub WHERE dashboard_menu_id = 12;` sebelum `db:seed`. |
| 2 | **`Str::studly()` menghasilkan `KriteriaMasukIcu`, bukan `KriteriaMasukICU`.** Folder controller/view **wajib** `KriteriaMasukIcu` / `KriteriaKeluarIcu`. | Sudah ditulis di §1.1. |
| 3 | **228 baris `objek_form_control` baru** dari 6 form — total objek_form_control kini > 900 baris. Seeder insert perlu di-batch. | Generate mapping via array literal per form (bukan loop bersarang) & `insert()` batch 500 bila perlu. |
| 4 | **89 objek baru**, total objek menjadi 177 + 89 = **266** — masih di bawah anggaran ± 500. | Tidak ada tindakan, tapifilter objek per menu di `ManajemenEMR → Form` makin penting. |
| 5 | **`kr1_*` legacy ambigu** (checkbox YA = "memerlukan alat"?). NusaMedika sudah memperbaiki labelnya (§4.1), tetapi ini **mengubah makna data** dibanding legacy. | Sudah dicatat; perlu konfirmasi klinis. |
| 6 | **Kriteria ICU masuk/keluar tidak punya ambang numerik resmi** di legacy — hanya centang per kriteria. `kesimpulan` tidak dapat dihitung otomatis. | `jumlah_kriteria_terpenuhi` & `kr1_status` yang dihitung; `kesimpulan` diisi manual/diisi kebijakan lokal. Jangan mengarang ambang. |
| 7 | **Keluar ICU untuk pasien terminal (WLSBT)** tidak boleh hanya berdasar kriteria II/IV. Legacy tidak punya field DNR. | Tambahkan field DNR terpisah **di luar rentang objek 447–535** (≥ 536) bila klinis meminta; jangan seizekan di form ini tanpa keputusan. |
| 8 | **Kelompok usia pada Kriteria Masuk ICU**: Legacy memuat 5 kriteria respirasi & 6 kriteria nadi yang saling tumpang tindih. Memaksakan semua `*_status` akan membingungkan. | Usia yang tidak relevan **di-disable** di UI; server tetap memvalidasi semua (konsistensi data). Alternatif: validasi bersyarat per kelompok usia — **tidak direkomendasikan** karena menambah kompleksitas. |
| 9 | **Puluhan field** dikumpulkan dari `data_fokus_postHD.php` (61 KB, bersyarat per klien lewat `$client_config` SBU-000000x). NusaMedika mengambil **field generik**, bukan field khusus klien tertentu. | Field khusus klien (mis. HD Weighted / `$arr_cara` HWHD) **dilewati**; akan ditambahkan sebagai form terpisah bila dibutuhkan. |
| 10 | **Angka `objek_id` legacy (583, 589, 700–752, …) tidak dipetakan 1:1 ke objek NusaMedika.** Yang dipetakan adalah **nama variabel & makna**, bukan nomor legacy. | Sudah jelas di §2 dan tabel §5.2. |
| 11 | **Kt/V < 1.2** adalah indikator mutu utama hemodialisa — wajib diperingatkan (bukan hanya disimpan). | `HemodialisaHelper::ktv()` + `confirm-alert`; catat di §7.6. |
| 12 | **Zone waktu**: `pasien.tgl_lahir` bertipe `date`; semua `timestamp` ditulis dari PHP (`now()`), **jangan** `NOW()`. | Sudah jadi konvensi global `AGENTS.md`. |