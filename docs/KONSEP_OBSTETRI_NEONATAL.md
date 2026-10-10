# Konsep Form Obstetri & Neonatal — NusaMedika

**Status:** Rancangan / Konsep — belum diimplementasikan
**Tanggal:** 2026-10-10
**Cakupan:** `form_id` **59–69** (11 form)
**Acuan wajib:** [`PANDUAN_IMPLEMENTASI_FORM_EMR.md`](PANDUAN_IMPLEMENTASI_FORM_EMR.md), `AGENTS.md`, [`ANALISIS_KEKURANGAN_FORM.md`](ANALISIS_KEKURANGAN_FORM.md)
**Alokasi `objek_id`:** **536–610** (75 objek baru); objek 1–177 dipakai ulang.

---

## 1. Ringkasan

| form_id | nama_form | slug | id_dash_menu | ri | rj | igd | mcu | objek baru |
|---|---|---|---|---|---|---|---|---|
| 59 | Asesmen Khusus Kebidanan & Penyakit Kandungan | `asesmen_kebidanan` | `13.253` | 1 | 1 | 0 | 0 | 536–546 (11) |
| 60 | Pengkajian Awal Keperawatan Perina | `pengkajian_perina` | `13.254` | 1 | 0 | 0 | 0 | 547–561 (15) |
| 61 | Pengkajian Awal Keperawatan Non-Perina | `pengkajian_non_perina` | `13.255` | 1 | 0 | 0 | 0 | 562 (1) |
| 62 | Kala I–IV | `kala_persalinan` | `13.256` | 1 | 0 | 0 | 0 | 563–570 (8) |
| 63 | Riwayat Persalinan Sekarang | `riwayat_persalinan` | `13.257` | 1 | 0 | 0 | 0 | 571–578 (8) |
| 64 | Pelaksanaan Bayi Baru Lahir | `bayi_baru_lahir` | `13.258` | 1 | 0 | 0 | 0 | 579–582 (4) |
| 65 | Riwayat Resusitasi Bayi | `riwayat_resusitasi_bayi` | `13.259` | 1 | 0 | 0 | 0 | 583–586 (4) |
| 66 | Penilaian Bayi dengan APGAR Score | `penilaian_bayi_dengan_apgar_score` | `13.260` | 1 | 0 | 0 | 0 | 587–592 (6) |
| 67 | Down Score (Neonatal) | `down_score` | `13.261` | 1 | 0 | 0 | 0 | 593–598 (6) |
| 68 | Inisiasi Menyusui Dini | `inisiasi_menyusui_dini` | `13.262` | 1 | 0 | 0 | 0 | 599 (1) |
| 69 | Skrining MDPK/MPP | `skrining_mpp` | `13.263` | 1 | 1 | 0 | 0 | 600–610 (11) |

> Slug wajib mengikuti sub-menu (`ALOKASI_ID_GLOBAL.md` §7): form 66 memakai
> `Str::slug('Penilaian Bayi dengan APGAR Score','_')` = `penilaian_bayi_dengan_apgar_score`,
> form 68 memakai `Str::slug('Inisiasi Menyusui Dini','_')` = `inisiasi_menyusui_dini`
> (tanpa huruf `s` ganda di "Menyususui"). Bila slug ≠ sub-menu, form yatim di
> dashboard (PANDUAN §2.1).
>
> ⚠️ **Penyimpangan dari ledger (menunggu koreksi `ALOKASI_ID_GLOBAL.md` §6 baris 68 &
> §7 baris 68):** ledger menulis slug `inisisasi_menyusui_dini` — huruf `s` ganda masih
> tersisa di kata **"Inisiasi"**. `Str::slug('Inisiasi Menyusui Dini','_')` sebenarnya
> menghasilkan `inisiasi_menyusui_dini`. Dokumen ini memakai hasil `Str::slug()` yang
> benar, karena slug ≠ sub-menu membuat form yatim di dashboard. Ledger perlu diubah
> satu karakter (`inisisasi` → `inisiasi`) agar sinkron.

### 1.1 Struktur menu

Menu EMR baru `dashboard_menu_id = 13`, 11 sub-menu **tanpa extra** → `id_dash_menu = "13.N"`.

```php
// $menus
['dashboard_menu_id' => 13, 'nama_menu' => 'Kebidanan & Neonatal'],

// $subMenus
['dashboard_menu_sub_id' => 253,  'dashboard_menu_id' => 13, 'nama_sub_menu' => 'Asesmen Kebidanan'],
['dashboard_menu_sub_id' => 254,  'dashboard_menu_id' => 13, 'nama_sub_menu' => 'Pengkajian Perina'],
['dashboard_menu_sub_id' => 255,  'dashboard_menu_id' => 13, 'nama_sub_menu' => 'Pengkajian Non Perina'],
['dashboard_menu_sub_id' => 256,  'dashboard_menu_id' => 13, 'nama_sub_menu' => 'Kala Persalinan'],
['dashboard_menu_sub_id' => 257,  'dashboard_menu_id' => 13, 'nama_sub_menu' => 'Riwayat Persalinan'],
['dashboard_menu_sub_id' => 258,  'dashboard_menu_id' => 13, 'nama_sub_menu' => 'Bayi Baru Lahir'],
['dashboard_menu_sub_id' => 259,  'dashboard_menu_id' => 13, 'nama_sub_menu' => 'Riwayat Resusitasi Bayi'],
['dashboard_menu_sub_id' => 260,  'dashboard_menu_id' => 13, 'nama_sub_menu' => 'Penilaian Bayi dengan APGAR Score'],
['dashboard_menu_sub_id' => 261,  'dashboard_menu_id' => 13, 'nama_sub_menu' => 'Down Score'],
['dashboard_menu_sub_id' => 262, 'dashboard_menu_id' => 13, 'nama_sub_menu' => 'Inisiasi Menyusui Dini'],
['dashboard_menu_sub_id' => 263, 'dashboard_menu_id' => 13, 'nama_sub_menu' => 'Skrining MPP'],
```

Tidak memakai `dashboard_menu_sub_extra` — semua form adalah leaf. ID extra 6+ dibiarkan untuk domain lain.

---

## 2. Sumber Referensi Legacy

Path relatif terhadap `/simrs_tenriawaru/FE/lib/modul/`.

| Form | File legacy | Peran |
|---|---|---|
| 59 | `asesmen_khusus_kebidinan_dan_penyakit_kandungan.php` (container) → `data_subjektif.php` | G/P/A, riwayat obstetrik, riwayat operasi, penyakit, KB, haid |
| 59/62 | `data_objektif.php` | Hasil objektif per kala |
| 60 | `perina.php` (container) + `info_perina.php`, `kehamilan_sekarang_perina.php`, `kepala_perina.php`, `tubuh_perina.php`, `ekstremitas_perina.php`, `punggung_perina.php`, `genitalia_perina.php` | Pengkajian perina, 15 sub-form accordion |
| 61 | `non_perina.php` | Pengkajian awal umum 15 sub-form; instrumen risiko jatuh by usia; prefill dari triage IGD |
| 62 | `persalinan_spontan.php` → `data_kala.php` → `kala.php` / `kebidanan_kala.php` | Kala I–IV, objek `kala` = 1053 |
| 63 | `riwayat_persalinan_sekarang_perina.php` | Penolong persalinan, kala I–II, plasenta, keadaan ibu |
| 64 | `pelaksanaan_bayi_baru_lahir_perina.php` | Penilaian & tindakan bayi baru lahir |
| 65 | `riwayat_resusitasi_perina.php` | Tindakan resusitasi + parameter CPAP |
| 66 | `penilaian_bayi_dengan_apgar_score_perina.php` | APGAR 5 tanda × 5 menit (objek 697) |
| 67 | `down_score_perina.php` | Down Score 5 item (objek 695, total 696) + interpretasi |
| 68 | `inisiasi_menyusu_dini_perina.php` | Radio Ya/Tidak (objek 693) |
| 69 | `skrining_mpp.php` | Skrining MPP + Form A (asesmen) + Form B (implementasi) |

`medikasi_pada_bayi_perina.php` dan `skrining_nyeri_perina.php` tidak dipetakan ke form terpisah: medication masuk riwayat obat form 61, skrining nyeri memakai partial `pengkajian_nyeri` yang sudah ada.

---

## 3. Form 59 — Asesmen Khusus Kebidanan & Penyakit Kandungan

### Rasional

Form pertama yang dibuka dokter obstetrik/bidan sebelum persalinan. Menghimpun data obstetris (G/P/A, riwayat persalinan sebelumnya), komplikasi kehamilan, riwayat operasi/penyakit, kontrasepsi, dan riwayat menstruasi sebagai dasar penentuan JAMAH dan kebutuhan ANC.

### Struktur Field

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `tanggal_asesmen` | 536 | Tanggal Asesmen Kebidanan | date | ya | default hari ini saat create |
| `jam_asesmen` | 537 | Jam Asesmen Kebidanan | time | ya | default now saat create |
| `dokter_pemeriksa_id` | 538 | Dokter Pemeriksa Kebidanan | `x-select_dokter` | ya | `pegawai` profesi 1 |
| `obstetrik_g` | 539 | Obstetrik G | number | ya | |
| `obstetrik_p` | 540 | Obstetrik P | number | ya | |
| `obstetrik_a` | 541 | Obstetrik A | number | ya | |
| `riwayat_obst_tempat_1..5` | 542 | Riwayat Obstetrik | text | tidak | 5 baris × 9 kolom, suffix kolom+baris |
| `riwayat_obst_tahun_1..5` | 542 | Riwayat Obstetrik | number | tidak | idem |
| `riwayat_obst_kehamilan_1..5` | 542 | Riwayat Obstetrik | text | tidak | idem |
| `riwayat_obst_jenis_persalinan_1..5` | 542 | Riwayat Obstetrik | text | tidak | idem |
| `riwayat_obst_penyulit_1..5` | 542 | Riwayat Obstetrik | text | tidak | idem |
| `riwayat_obst_nifas_1..5` | 542 | Riwayat Obstetrik | text | tidak | idem |
| `riwayat_obst_sex_1..5` | 542 | Riwayat Obstetrik | text | tidak | idem |
| `riwayat_obst_bb_1..5` | 542 | Riwayat Obstetrik | text | tidak | idem |
| `riwayat_obst_keadaan_1..5` | 542 | Riwayat Obstetrik | text | tidak | Normal / kelainan |
| `komplikasi_hyperemesis` | 543 | Komplikasi & Keluhan Kebidanan | checkbox | tidak | |
| `komplikasi_toksemia` | 543 | Komplikasi & Keluhan Kebidanan | checkbox | tidak | |
| `komplikasi_eklampsi` | 543 | Komplikasi & Keluhan Kebidanan | checkbox | tidak | |
| `komplikasi_pph` | 543 | Komplikasi & Keluhan Kebidanan | checkbox | tidak | pendarahan postpartum |
| `riwayat_operasi_pernah` | 544 | Riwayat Operasi Ibu | radio | ya | Pernah / Tidak Pernah |
| `riwayat_operasi_jenis` | 544 | Riwayat Operasi Ibu | text | ya bila Pernah | |
| `riwayat_operasi_detail` | 544 | Riwayat Operasi Ibu | text | tidak | |
| `penyakit_jantung` | 545 | Riwayat Penyakit Lain Ibu | checkbox | tidak | |
| `penyakit_diabetes` | 545 | Riwayat Penyakit Lain Ibu | checkbox | tidak | |
| `penyakit_paru` | 545 | Riwayat Penyakit Lain Ibu | checkbox | tidak | |
| `penyakit_vemerik` | 545 | Riwayat Penyakit Lain Ibu | checkbox | tidak | |
| `penyakit_lainnya` | 545 | Riwayat Penyakit Lain Ibu | checkbox | tidak | memunculkan isian |
| `penyakit_keterangan` | 545 | Riwayat Penyakit Lain Ibu | text | ya bila Lainnya | |
| `program_kb_pernah` | 546 | Riwayat Reproduksi & Menstruasi | radio | ya | |
| `program_kb_jenis` | 546 | Riwayat Reproduksi & Menstruasi | text | ya bila Pernah | |
| `program_kb_sejak` | 546 | Riwayat Reproduksi & Menstruasi | text | tidak | |
| `menarche` | 546 | Riwayat Reproduksi & Menstruasi | text | ya | umur menarche |
| `haid_terakhir` | 546 | Riwayat Reproduksi & Menstruasi | date | ya | |
| `haid_lama` | 546 | Riwayat Reproduksi & Menstruasi | text | ya | hari |
| `haid_sebelumnya` | 546 | Riwayat Reproduksi & Menstruasi | date | tidak | |
| `haid_lama_2` | 546 | Riwayat Reproduksi & Menstruasi | text | tidak | |
| `siklus_haid` | 546 | Riwayat Reproduksi & Menstruasi | text | ya | |
| `dismenore` | 546 | Riwayat Reproduksi & Menstruasi | radio | ya | |
| `dismenore_sejak` | 546 | Riwayat Reproduksi & Menstruasi | text | ya bila Ya | |
| `keteraturan_haid` | 546 | Riwayat Reproduksi & Menstruasi | radio | ya | Teratur / Tidak Teratur |
| `keluhan` | 13 | Keluhan Utama (reuse) | textarea | tidak | |

### Dashboard & Form Master

```php
['form_id' => 59, 'nama_form' => 'Asesmen Khusus Kebidanan & Penyakit Kandungan', 'slug' => 'asesmen_kebidanan', 'id_dash_menu' => '13.253', 'ri' => 1, 'rj' => 1, 'igd' => 0, 'mcu' => 0],
```

### Mapping

```php
59 => [
    'tanggal_asesmen' => 536, 'jam_asesmen' => 537, 'dokter_pemeriksa_id' => 538,
    'obstetrik_g' => 539, 'obstetrik_p' => 540, 'obstetrik_a' => 541,
    'komplikasi_hyperemesis' => 543, 'komplikasi_toksemia' => 543,
    'komplikasi_eklampsi' => 543, 'komplikasi_pph' => 543,
    'riwayat_operasi_pernah' => 544, 'riwayat_operasi_jenis' => 544, 'riwayat_operasi_detail' => 544,
    'penyakit_jantung' => 545, 'penyakit_diabetes' => 545, 'penyakit_paru' => 545,
    'penyakit_vemerik' => 545, 'penyakit_lainnya' => 545, 'penyakit_keterangan' => 545,
    'program_kb_pernah' => 546, 'program_kb_jenis' => 546, 'program_kb_sejak' => 546,
    'menarche' => 546, 'haid_terakhir' => 546, 'haid_lama' => 546, 'haid_sebelumnya' => 546,
    'haid_lama_2' => 546, 'siklus_haid' => 546, 'dismenore' => 546,
    'dismenore_sejak' => 546, 'keteraturan_haid' => 546,
    'keluhan' => 13,
],
// Riwayat obstetrik: 5 baris × 9 kolom, satu objek, semua variabel unik
$kolomObstetrik = ['tempat_bersalin', 'tahun', 'kehamilan', 'jenis_persalinan', 'penyulit', 'nifas', 'sex', 'bb', 'keadaan'];
for ($baris = 1; $baris <= 5; $baris++) {
    foreach ($kolomObstetrik as $kolom) {
        $mapping[59]['riwayat_obst_'.$kolom.'_'.$baris] = 542;
    }
}
```

### Akses EHR

```php
['profesi_id' => 1, 'form_id' => 59, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 2, 'form_id' => 59, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
```

### Validasi

- `tanggal_asesmen` `required|date`; `jam_asesmen` `required|date_format:H:i`.
- `obstetrik_g|p|a` `required|integer|min:0`; `withValidator`: `p + a <= g` bila `g > 0`.
- Kondisional: `riwayat_operasi_jenis` wajib bila `riwayat_operasi_pernah = 'Pernah'`; `penyakit_keterangan` wajib bila `penyakit_lainnya` tercentang; `program_kb_jenis` wajib bila `program_kb_pernah = 'Pernah'`; `dismenore_sejak` wajib bila `dismenore = 'Ya'`.
- `filteredData()` membuang sel `riwayat_obst_*` yang kosong.

### Cetak

`resources/views/moduls/EMR/AsesmenKebidanan/print.blade.php` (route `/emr/{slug}/print/{emr_id}`). Kop, tabel riwayat obstetrik, paraf dokter & bidan.

---

## 4. Form 60 — Pengkajian Awal Keperawatan Perina

### Rasional

Pengkajian awal keperawatan untuk ibu yang baru melahirkan / dalam masa nifas. Legacy `perina.php` adalah satu form accordion 15 sub-form. Di NusaMedika blok bayi **dipecah menjadi form 63–67** agar dapat diisi awak berbeda dan riwayatnya tidak tenggelam dalam satu form besar. Form 60 fokus pada **profil ibu + kehamilan + SAP neonatal head-to-toe**.

**Keputusan alokasi objek:** butir SAP yang selalu dibaca bersama dipetakan ke **satu objek grup** (objek 561) dengan awalan variabel berbeda (`kepala_*`, `tubuh_*`, …). Konsekuensinya dibahas di §11 Catatan & Risiko.

### Struktur Field — Identitas (547–558)

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `tanggal_pengkajian` | 547 | Tanggal Pengkajian Perina | date | ya | |
| `jam_pengkajian` | 548 | Jam Pengkajian Perina | time | ya | |
| `nama_bayi` | 549 | Identitas Bayi | text | ya | prefilled `pasien.nama_pasien` |
| `tanggal_lahir_bayi` | 549 | Identitas Bayi | date | ya | |
| `jam_lahir_bayi` | 549 | Identitas Bayi | time | ya | |
| `jenis_kelamin_bayi` | 549 | Identitas Bayi | radio | ya | L / P |
| `berat_badan_lahir` | 8 | Berat Badan (reuse) | number | ya | kg — pesan satuan |
| `tinggi_badan_lahir` | 9 | Tinggi Badan (reuse) | number | ya | cm |
| `lingkar_kepala_bayi` | 549 | Identitas Bayi | number | ya | cm |
| `denyut_jantung_bayi` | 10 | Nadi (reuse) | number | ya | x/menit |
| `pernapasan_bayi` | 12 | Pernapasan (reuse) | number | ya | x/menit |
| `suhu_rectal_bayi` | 549 | Identitas Bayi | number | ya | °C |
| `tanda_pengenal_bayi` | 549 | Identitas Bayi | text | ya | |
| `tempat_lahir_bayi` | 549 | Identitas Bayi | text | ya | |
| `nama_ibu` | 550 | Identitas Ibu | text | ya | prefilled `pasien.ibu_pasien_id` |
| `umur_ibu` | 550 | Identitas Ibu | text | ya | |
| `agama_ibu` | 21 | Agama (reuse) | select | ya | `SelectOption::agama` |
| `pendidikan_ibu` | 23 | Tingkat Pendidikan (reuse) | select | ya | `SelectOption::pendidikan` |
| `pekerjaan_ibu` | 24 | Pekerjaan (reuse) | select | ya | `SelectOption::pekerjaan` |
| `alamat_ibu` | 550 | Identitas Ibu | textarea | ya | |
| `nama_ayah` | 551 | Identitas Ayah | text | ya | |
| `umur_ayah` | 551 | Identitas Ayah | text | ya | |
| `agama_ayah` | 21 | Agama (reuse) | select | tidak | |
| `pendidikan_ayah` | 23 | Tingkat Pendidikan (reuse) | select | tidak | |
| `pekerjaan_ayah` | 24 | Pekerjaan (reuse) | select | tidak | |

### Struktur Field — Kehamilan & SAP (552–561)

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `tinggi_ibu` | 552 | Kehamilan Sekarang | number | ya | cm |
| `berat_ibu` | 552 | Kehamilan Sekarang | number | ya | kg |
| `presentasi_bayi` | 552 | Kehamilan Sekarang | text | ya | cephalic/breech/transverse |
| `antenatal_care` | 553 | Pemeriksaan Antenatal | radio | ya | 1 teratur / 2 tidak teratur |
| `lokasi_antenatal_care` | 553 | Pemeriksaan Antenatal | text | ya bila 1 | |
| `usg_ibu_1..5` | 554 | USG Ibu | text | tidak | baris berulang |
| `hpht` | 555 | HPHT | date | ya | |
| `taksiran_partus` | 556 | Taksiran Partus | date | ya | |
| `imunisasi_hamil` | 557 | Imunisasi Selama Kehamilan | radio | ya | Lengkap / Tidak Lengkap |
| `penyakit_hamil_anemia` | 558 | Penyakit Selama Hamil | checkbox | ya | Anemia |
| `penyakit_hamil_jantung` | 558 | Penyakit Selama Hamil | checkbox | ya | Penyakit Jantung |
| `penyakit_hamil_hipertensi` | 558 | Penyakit Selama Hamil | checkbox | ya | Hipertensi |
| `penyakit_hamil_tb` | 558 | Penyakit Selama Hamil | checkbox | ya | Tuberkulosis |
| `penyakit_hamil_diabetes` | 558 | Penyakit Selama Hamil | checkbox | ya | Diabetes |
| `penyakit_hamil_sifilis` | 558 | Penyakit Selama Hamil | checkbox | ya | Sifilis |
| `penyakit_hamil_lain` | 558 | Penyakit Selama Hamil | checkbox | ya | Lainnya |
| `penyakit_hamil_lainnya` | 558 | Penyakit Selama Hamil | text | ya bila Lainnya | |
| `komplikasi_pendarahan` | 559 | Komplikasi Kehamilan | checkbox | ya | |
| `komplikasi_peb` | 559 | Komplikasi Kehamilan | checkbox | ya | |
| `komplikasi_infeksi` | 559 | Komplikasi Kehamilan | checkbox | ya | |
| `komplikasi_disproporsi` | 559 | Komplikasi Kehamilan | checkbox | ya | disproporsi feto-pelvik |
| `komplikasi_lain` | 559 | Komplikasi Kehamilan | checkbox | ya | |
| `komplikasi_lainnya` | 559 | Komplikasi Kehamilan | text | ya bila Lainnya | |
| `golongan_darah_ibu` | 560 | Laboratorium Ibu | select | ya | `SelectOption::golongan_darah` |
| `hb_ibu` | 560 | Laboratorium Ibu | number | ya | |
| `leukosit_ibu` | 560 | Laboratorium Ibu | number | ya | |
| `gula_darah_ibu` | 560 | Laboratorium Ibu | number | ya | |
| `urine_lengkap_ibu` | 560 | Laboratorium Ibu | text | ya | |

**Kelompok "Butir Pengkajian Perina" — objek grup 561.** Seluruh baris berikut dipetakan ke objek 561; wajib = tidak.

| grup | variabel | opsi |
|---|---|---|
| Kebiasaan ibu | `anamnesis_makanan_kualitatif`, `anamnesis_makanan_kuantitatif`, `riwayat_obat_ibu`, `jamu_ibu`, `merokok_ibu`, `kebiasaan_ibu_lain` | teks bebas |
| Kepala | `kepala_1..5`, `kepala_lain` | Normal · Tidak Normal · Caput Succedaneum · Cephal Hematom · Lainnya |
| Ubun-ubun | `ubun_ubun_1..3` | Besar · Cekung · Cembung |
| Mata | `mata_1..5` | Simetris · Tidak Simetris · Sclera Kuning · Perdarahan · Normal |
| Telinga | `telinga_1..3`, `telinga_lain` | Simetris · Tidak Simetris · Lainnya |
| Mulut | `mulut_1..5`, `mulut_lain` | Simetris · Tidak Simetris · Labio schizis · Palato schizis · Lainnya |
| Hidung | `hidung_1..4` | Lubang ada · Tidak ada · Kotor · Tidak Kotor |
| Leher | `leher_1..3` | Pergerakan aktif · Kaku · Tidak Bereaksi |
| Menangis | `menangis_1..3` | Kuat · Lemah · Merintih |
| Warna kulit | `warna_kulit` | Merah Muda · Pucat · Sianosis · Ikterus · Vernik · Rambut Lanugo · Rashes · Hidrasi |
| Gerakan | `pergerakan_bayi` | Aktif · Lemah |
| Eliminasi atas | `meconium` | Ada · Tidak ada |
| Toraks | `dada_bayi` | Simetris · Tidak simetris |
| Abdomen | `perut_bayi` | Lembek · Kembung · Teraba Hernia |
| Umbilikal | `umbilikal` | Normal · Omphalocel |
| Bising usus | `bising_usus` | Ada · Tidak ada |
| Tangan | `jari_tangan`, `pergerakan_tangan`, `garis_tangan`, `tangan_keriput`, `tangan_terkelupas` | Polidactili/Syndactili/Normal; Aktif/Lemah; garis jelas/tidak; Keriput/Tidak; Terkelupas/Tidak |
| Kaki | `jari_kaki`, `pergerakan_kaki`, `garis_kaki`, `kaki_keriput`, `kaki_terkelupas` | idem |
| Kuku | `kuku` | Panjang · Pendek |
| Punggung | `punggung_1..6`, `punggung_lain` | Normal · Kifosis · Lordosis · Skoliosis · Spina Bifida · Lainnya |
| Anus | `anus_1..3`, `anus_lain` | Normal · Atresia Ani · Lainnya |
| Genitalia pria | `genitalia_pria_1..5`, `genitalia_pria_lain` | Normal · Hipospadia · Phimosis · Episdalia · Lainnya |
| Genitalia wanita | `labio_mayora`, `labio_minora`, `keluaran_genitalia_1..3` | Normal/Tidak normal; tidak ada pengeluaran · Lendir · Darah |
| Eliminasi | `tgl_bab_pertama`, `jam_bab_pertama`, `tgl_bak_pertama`, `jam_bak_pertama` | date/time |

### Dashboard & Form Master

```php
['form_id' => 60, 'nama_form' => 'Pengkajian Awal Keperawatan Perina', 'slug' => 'pengkajian_perina', 'id_dash_menu' => '13.254', 'ri' => 1, 'rj' => 0, 'igd' => 0, 'mcu' => 0],
```

### Mapping

```php
60 => [
    'tanggal_pengkajian' => 547, 'jam_pengkajian' => 548,
    'nama_bayi' => 549, 'tanggal_lahir_bayi' => 549, 'jam_lahir_bayi' => 549, 'jenis_kelamin_bayi' => 549,
    'berat_badan_lahir' => 8, 'tinggi_badan_lahir' => 9, 'lingkar_kepala_bayi' => 549,
    'denyut_jantung_bayi' => 10, 'pernapasan_bayi' => 12, 'suhu_rectal_bayi' => 549,
    'tanda_pengenal_bayi' => 549, 'tempat_lahir_bayi' => 549,
    'nama_ibu' => 550, 'umur_ibu' => 550, 'agama_ibu' => 21, 'pendidikan_ibu' => 23,
    'pekerjaan_ibu' => 24, 'alamat_ibu' => 550,
    'nama_ayah' => 551, 'umur_ayah' => 551, 'agama_ayah' => 21, 'pendidikan_ayah' => 23, 'pekerjaan_ayah' => 24,
    'tinggi_ibu' => 552, 'berat_ibu' => 552, 'presentasi_bayi' => 552,
    'antenatal_care' => 553, 'lokasi_antenatal_care' => 553,
    'hpht' => 555, 'taksiran_partus' => 556, 'imunisasi_hamil' => 557,
    'penyakit_hamil_anemia' => 558, 'penyakit_hamil_jantung' => 558, 'penyakit_hamil_hipertensi' => 558,
    'penyakit_hamil_tb' => 558, 'penyakit_hamil_diabetes' => 558, 'penyakit_hamil_sifilis' => 558,
    'penyakit_hamil_lain' => 558, 'penyakit_hamil_lainnya' => 558,
    'komplikasi_pendarahan' => 559, 'komplikasi_peb' => 559, 'komplikasi_infeksi' => 559,
    'komplikasi_disproporsi' => 559, 'komplikasi_lain' => 559, 'komplikasi_lainnya' => 559,
    'golongan_darah_ibu' => 560, 'hb_ibu' => 560, 'leukosit_ibu' => 560,
    'gula_darah_ibu' => 560, 'urine_lengkap_ibu' => 560,
    // Kelompok "Butir Pengkajian Perina" — objek grup 561
    'anamnesis_makanan_kualitatif' => 561, 'anamnesis_makanan_kuantitatif' => 561,
    'riwayat_obat_ibu' => 561, 'jamu_ibu' => 561, 'merokok_ibu' => 561, 'kebiasaan_ibu_lain' => 561,
],
for ($baris = 1; $baris <= 5; $baris++) { $mapping[60]['usg_ibu_'.$baris] = 554; }

$butirPerina = ['kepala' => 5, 'ubun_ubun' => 3, 'mata' => 5, 'telinga' => 3, 'mulut' => 5,
    'hidung' => 4, 'leher' => 3, 'menangis' => 3, 'punggung' => 6, 'anus' => 3,
    'genitalia_pria' => 5, 'keluaran_genitalia' => 3];
foreach ($butirPerina as $prefix => $jumlah) {
    for ($i = 1; $i <= $jumlah; $i++) { $mapping[60][$prefix.'_'.$i] = 561; }
}
foreach ([
    'kepala_lain', 'telinga_lain', 'mulut_lain', 'punggung_lain', 'anus_lain', 'genitalia_pria_lain',
    'warna_kulit', 'pergerakan_bayi', 'meconium', 'dada_bayi', 'perut_bayi', 'umbilikal', 'bising_usus',
    'jari_tangan', 'pergerakan_tangan', 'jari_kaki', 'pergerakan_kaki', 'garis_tangan', 'tangan_keriput',
    'tangan_terkelupas', 'garis_kaki', 'kaki_keriput', 'kaki_terkelupas', 'kuku',
    'labio_mayora', 'labio_minora',
    'tgl_bab_pertama', 'jam_bab_pertama', 'tgl_bak_pertama', 'jam_bak_pertama',
] as $variabel) {
    $mapping[60][$variabel] = 561;
}
```

### Akses EHR

```php
['profesi_id' => 1, 'form_id' => 60, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 2, 'form_id' => 60, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
```

### Validasi

- `berat_badan_lahir` `required|numeric|min:0.3|max:6` dengan pesan *"Pastikan BB menggunakan satuan kilogram."*
- `tinggi_badan_lahir` `required|numeric|min:30|max:70`; `jenis_kelamin_bayi` `required|in:L,P`.
- `antenatal_care = 1` ⇒ `lokasi_antenatal_care` wajib; `= 2` ⇒ field dikosongkan & dibuang di `filteredData()`.
- `imunisasi_hamil` `required|in:Lengkap,Tidak Lengkap`.
- `filteredData()` membuang baris `usg_ibu_N` kosong dan checkbox yang tidak tercentang (jangan simpan "tidak ada").

### Cetak

`.../PengkajianPerina/print.blade.php` — A4 dua kolom (Identitas Ibu | Identitas Bayi), tabel SAP neonatal per organ, paraf perawat & dokter.

---

## 5. Form 61 — Pengkajian Awal Keperawatan Non-Perina

### Rasional

Legacy `non_perina.php` adalah pengkajian awal keperawatan **umum**: accordion 15 sub-form (Informasi Pasien, Riwayat Penyakit, Pemeriksaan Fisik, Pengkajian Nyeri, Riwayat Obat, Skrining Nutrisi, Eliminasi, Riwayat Keluarga, Risiko Jatuh, Restraint, Status Fungsional, Reproduksi, Respon Emosi, Perencanaan Pulang, Masalah Keperawatan) dengan instrumen risiko jatuh dipilih berdasarkan **usia** (HDS < 14, Morse 14–59, TUG ≥ 60) dan prefill dari triage IGD.

Struktur ini **sudah ada** di NusaMedika sebagai form 3 + partial `moduls/EMR/PartialForm/*`. Form 61 tidak mengulang field, melainkan menjadi entry point pengkajian awal non-perina, memakai partial yang sama, dan menambah satu objek penanda jenis pengkajian.

### Struktur Field

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `jenis_pengkajian` | 562 | Jenis Pengkajian Awal | hidden | ya | diisi server `Non-Perina` |

Seluruh field lain reuse mapping form 3 (`Agama` 21 … `tug_detik` 113, `total_ews` 142) lewat partial `informasi_pasien`, `riwayat_penyakit`, `pemeriksaan_fisik`, `pengkajian_nyeri`, `pengkajian_risiko_jatuh`.

### Dashboard & Form Master

```php
['form_id' => 61, 'nama_form' => 'Pengkajian Awal Keperawatan Non-Perina', 'slug' => 'pengkajian_non_perina', 'id_dash_menu' => '13.255', 'ri' => 1, 'rj' => 0, 'igd' => 0, 'mcu' => 0],
```

### Mapping

```php
61 => [
    'jenis_pengkajian' => 562,
    'agama' => 21, 'pekerjaan' => 24, 'tingkat_pendidikan' => 23, 'suku_bangsa' => 25, 'kebangsaan' => 26,
    'diagnosa_medis' => 40, 'keluhan' => 13,
    'riwayat_penyakit_sebelumnya' => 41, 'riwayat_penyakit_sekarang' => 42,
    'kesadaran' => 51, 'gcs_e' => 54, 'gcs_m' => 55, 'gcs_v' => 56, 'gcs_jumlah' => 57,
    'td_sistolik' => 6, 'nadi' => 10, 'suhu' => 11, 'pernapasan' => 12,
    'berat_badan' => 8, 'tinggi_badan' => 9, 'bmi' => 58,
    'pemberian_o2' => 18, 'cara_pemberian_o2' => 19, 'ett' => 20, 'saturasi' => 15,
    'total_ews' => 142, 'kategori_ews' => 143, 'ews_tidak_diukur' => 144,
    'nyeri' => 14, 'skor_nyeri' => 140, 'alergi' => 17,
    'penanggung_jawab_pasien' => 38, 'hubungan_pasien' => 39,
    'risiko_jatuh_instrumen' => 89, 'risiko_jatuh_skor' => 90, 'risiko_jatuh_label' => 91,
    'risiko_jatuh_intervensi' => 92, 'risiko_jatuh_ringkasan' => 93,
    'hds_1' => 94, 'hds_2' => 95, 'hds_3' => 96, 'hds_4' => 97,
    'hds_5' => 98, 'hds_6' => 99, 'hds_7' => 100,
    'mfs_1' => 101, 'mfs_2' => 102, 'mfs_3' => 103, 'mfs_4' => 104, 'mfs_5' => 105, 'mfs_6' => 106,
    'syd_ts_bed' => 107, 'syd_ts_bangku' => 108, 'syd_ts_bantuan' => 109,
    'syd_ms_bantuan' => 110, 'syd_ms_kursi_roda' => 111, 'syd_ms_imobil' => 112, 'tug_detik' => 113,
],
```

### Akses EHR

```php
['profesi_id' => 1, 'form_id' => 61, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 2, 'form_id' => 61, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
```

> Perawat **read-only** untuk form 61: pengkajian awal harus diisi petugas yang melakukan asesmen; perawat tetap boleh membaca. (Bila kebijakan RS berbeda, ubah flag di `Akses EHR` menu.)

### Validasi

- Sama dengan form 3; `jenis_pengkajian` di-set server (`Non-Perina`), nilai browser dibuang.
- `filteredData()` memakai `RisikoJatuhHelper::hitung()` persis seperti form 3 — skor dari browser diabaikan.

### Cetak

Memakai `print.blade.php` form 3 yang sama.

---

## 6. Form 62 — Kala I–IV

### Rasional

Partogram/handling kala persalinan. Legacy: `persalinan_spontan.php` menyimpan `kala` (objek 1053), me-load `kala.php` sesuai kala, dan `kala.php` me-load `kebidanan_kala.php`. **Satu baris kala = satu record `emr`** → di NusaMedika tiap kala adalah satu pengisian form terpisah dengan riwayatnya sendiri.

| Kala | Isi (dari `kala.php`) |
|---|---|
| I | Masalah (Tidak/Ya + detail), Penatalaksanaan, Pemeriksaan Dalam (VT), HIS, DJJ |
| II | Episiotomi (+ indikasi), Gawat Janin (+ tindakan), Distosia Bahu (+ tindakan), BB, PB, Jenis Kelamin, APGAR menit 1 & 5 |
| III | IMD (+ alasan), Oksitosin (+ alasan), Peregangan Tali Pusat Terkendali, Massage Fundus, Plasenta Lahir Lengkap |
| IV | Laserasi/Episiotomi, Penjahitan, Atonia Uteri, Jumlah Pendarahan (cc) |

> **Pembagian tanggung jawab:** butir *ringkasan persalinan* (plasenta, atonia, pendarahan, BB/PB bayi) dipindah ke **form 63** agar tidak dobel. Form 62 fokus pada observasi per kala.

### Struktur Field

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `kala` | 563 | Kala Persalinan | select | ya | 1/2/3/4 — penentu isi form |
| `tanggal_kala` | 564 | Tanggal Kala | date | ya | |
| `jam_kala` | 565 | Jam Kala | time | ya | |
| `masalah_kala_1..4` | 566 | Masalah Kala | radio | ya | Tidak / Ya |
| `masalah_kala_detail_1..4` | 566 | Masalah Kala | text | ya bila Ya | |
| `penatalaksanaan_kala_1..4` | 567 | Penatalaksanaan Kala | text | tidak | |
| `pemeriksaan_dalam` | 568 | Pemeriksaan Dalam (VT) | textarea | ya (kala I) | |
| `his` | 569 | HIS | textarea | tidak | |
| `djj` | 570 | DJJ | text | tidak | denyut jantung janin |

Panel APGAR menit 1 & 5 ditampilkan **read-only** dari form 66 via `EmrHelper::latestValuesByVariabel()` — tidak disimpan ganda.

### Dashboard & Form Master

```php
['form_id' => 62, 'nama_form' => 'Kala I-IV', 'slug' => 'kala_persalinan', 'id_dash_menu' => '13.256', 'ri' => 1, 'rj' => 0, 'igd' => 0, 'mcu' => 0],
```

### Mapping

```php
62 => [
    'kala' => 563, 'tanggal_kala' => 564, 'jam_kala' => 565,
    'pemeriksaan_dalam' => 568, 'his' => 569, 'djj' => 570,
],
for ($k = 1; $k <= 4; $k++) {
    $mapping[62]['masalah_kala_'.$k] = 566;
    $mapping[62]['masalah_kala_detail_'.$k] = 566;
    $mapping[62]['penatalaksanaan_kala_'.$k] = 567;
}
```

### Akses EHR

```php
['profesi_id' => 1, 'form_id' => 62, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 2, 'form_id' => 62, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
```

### Validasi

- `kala` `required|in:1,2,3,4`; `tanggal_kala` `required|date|before_or_equal:today`; `jam_kala` `required|date_format:H:i`.
- `withValidator`: `masalah_kala_detail_k` wajib bila `masalah_kala_k = 'Ya'`.
- Server menolak `kala` sama terisi 2× pada `registrasi_detail_id` yang sama kecuali saat `update`.
- `tanggal_kala` tidak boleh lebih tua dari `tanggal_pengkajian` form 60.

### Cetak

`.../KalaPersalinan/print.blade.php` — landscape, kolom per kala bergaris waktu.

---

## 7. Form 63 — Riwayat Persalinan Sekarang

### Rasional

Rekap episode persalinan berjalan, diisi bidan setelah kala II–III: penolong persalinan, durasi tiap kala, kondisi ketuban, data bayi, plasenta, keadaan ibu, kehilangan darah.

### Struktur Field

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `penolong_persalinan` | 571 | Penolong Persalinan | radio | ya | Dokter Obgyn / Bidan / Lainnya |
| `penolong_persalinan_lain` | 571 | Penolong Persalinan | text | ya bila Lainnya | |
| `durasi_kala_1` | 572 | Durasi Kala I | text | ya | format `HH:MM` |
| `durasi_kala_2` | 573 | Durasi Kala II | text | ya | format `HH:MM` |
| `durasi_kala_3` | 574 | Durasi Kala III | text | ya | format `HH:MM` |
| `ketuban` | 575 | Ketuban | radio | ya | Pecah / Dipecahkan |
| `warna_ketuban` | 575 | Ketuban | radio | ya | Jernih / Keruh / Hijau |
| `ketuban_lain` | 575 | Ketuban | text | tidak | |
| `jam_persalinan_kala_2` | 576 | Kelahiran Kala II | time | ya | |
| `tanggal_lahir_bayi` | 576 | Kelahiran Kala II | date | ya | |
| `jenis_kelamin_kala_2` | 576 | Kelahiran Kala II | radio | ya | L / P |
| `as_bayi` | 576 | Kelahiran Kala II | text | ya | A/S |
| `berat_badan_kala_2` | 8 | Berat Badan (reuse) | number | ya | kg |
| `tinggi_badan_kala_2` | 9 | Tinggi Badan (reuse) | number | ya | cm |
| `lingkar_kepala_kala_2` | 576 | Kelahiran Kala II | number | ya | cm |
| `kelainan_bayi` | 576 | Kelahiran Kala II | text | ya | |
| `plasenta_lahir` | 577 | Plasenta Lahir | text | ya | lengkap / kotiledon tertinggal |
| `infark_plasenta` | 577 | Plasenta Lahir | text | ya | |
| `keadaan_umum_ibu` | 578 | Kehilangan Darah & Keadaan Ibu | text | ya | |
| `kehilangan_darah` | 578 | Kehilangan Darah & Keadaan Ibu | number | ya | cc |

### Dashboard & Form Master

```php
['form_id' => 63, 'nama_form' => 'Riwayat Persalinan Sekarang', 'slug' => 'riwayat_persalinan', 'id_dash_menu' => '13.257', 'ri' => 1, 'rj' => 0, 'igd' => 0, 'mcu' => 0],
```

### Mapping

```php
63 => [
    'penolong_persalinan' => 571, 'penolong_persalinan_lain' => 571,
    'durasi_kala_1' => 572, 'durasi_kala_2' => 573, 'durasi_kala_3' => 574,
    'ketuban' => 575, 'warna_ketuban' => 575, 'ketuban_lain' => 575,
    'jam_persalinan_kala_2' => 576, 'tanggal_lahir_bayi' => 576, 'jenis_kelamin_kala_2' => 576,
    'as_bayi' => 576, 'berat_badan_kala_2' => 8, 'tinggi_badan_kala_2' => 9,
    'lingkar_kepala_kala_2' => 576, 'kelainan_bayi' => 576,
    'plasenta_lahir' => 577, 'infark_plasenta' => 577,
    'keadaan_umum_ibu' => 578, 'kehilangan_darah' => 578,
],
```

### Akses EHR

```php
['profesi_id' => 1, 'form_id' => 63, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 2, 'form_id' => 63, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
```

### Validasi

- `durasi_kala_*` `required|regex:/^\d{1,2}:\d{2}$/` + cek menit `< 60` di `withValidator`.
- `warna_ketuban = 'Hijau'` ⇒ wajib isi `ketuban_lain` (meconium) — alarm IUGR/aspiksia.
- `kehilangan_darah` `required|numeric|min:0`; `> 500` cc memunculkan peringatan (bukan blocking) "Pendarahan perlu perhatian."
- `jenis_kelamin_kala_2` harus sama dengan `jenis_kelamin_bayi` form 60 bila keduanya terisi (validasi lintas-form, di-check saat `store`).

### Cetak

`.../RiwayatPersalinan/print.blade.php` — lembar persalinan (partogram ringkas + data plasenta + Bryce score).

---

## 8. Form 64 — Pelaksanaan Bayi Baru Lahir

### Rasional

Dokumentasi langkahPublications immediate newborn care: penilaian awal (baik/ada penyulit), waktu skin-to-skin, dan daftar tindakan yang dilakukan pada bayi normal maupun bayi dengan penyulit.

### Struktur Field

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `penilaian_bayi_baru_lahir` | 579 | Penilaian Bayi Baru Lahir | radio | ya | Baik / Ada penyulit |
| `penilaian_bayi_baru_lahir_ya` | 580 | Pelaksanaan Penilaian Bayi Baru Lahir | radio | ya | Ya (Ya waktu … setelah lahir) / Tidak diberikan (alasan) |
| `waktu_penilaian_bayi` | 580 | Pelaksanaan Penilaian Bayi Baru Lahir | text | ya bila Ya | "… setelah lahir" |
| `alasan_tidak_penilaian` | 580 | Pelaksanaan Penilaian Bayi Baru Lahir | text | ya bila Tidak | |
| `tindakan_bayi_normal_1..4` | 581 | Tindakan Bayi Lahir Normal | checkbox | ya | Mengeringkan · Menghangatkan · Membungkus bayi · Menempatkan di sisi Ibu |
| `tindakan_bayi_abnormal_1..9` | 582 | Tindakan Bayi Lahir Tidak Normal | checkbox | tidak | Mengeringkan · Bebaskan jalan napas · Menghangatkan · Rangsang taktil · Bungkus bayi · Pemberian O2 · Penempatan posisi kepala bayi · VTP · Pemberian obat |

`penilaian_bayi_baru_lahir = 'Ada penyulit'` ⇒ `tindakan_bayi_abnormal_*` wajib minimal satu.

### Dashboard & Form Master

```php
['form_id' => 64, 'nama_form' => 'Pelaksanaan Bayi Baru Lahir', 'slug' => 'bayi_baru_lahir', 'id_dash_menu' => '13.258', 'ri' => 1, 'rj' => 0, 'igd' => 0, 'mcu' => 0],
```

### Mapping

```php
64 => [
    'penilaian_bayi_baru_lahir' => 579,
    'penilaian_bayi_baru_lahir_ya' => 580,
    'waktu_penilaian_bayi' => 580,
    'alasan_tidak_penilaian' => 580,
],
for ($i = 1; $i <= 4; $i++) { $mapping[64]['tindakan_bayi_normal_'.$i] = 581; }
for ($i = 1; $i <= 9; $i++) { $mapping[64]['tindakan_bayi_abnormal_'.$i] = 582; }
```

### Akses EHR

```php
['profesi_id' => 1, 'form_id' => 64, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 2, 'form_id' => 64, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
```

### Validasi

- `penilaian_bayi_baru_lahir` `required|in:Baik,Ada penyulit`.
- `withValidator`: minimal satu `tindakan_bayi_normal_*` tercentang; bila `Ada penyulit` minimal satu `tindakan_bayi_abnormal_*`.
- Checkbox = "0"/"1" (bukan string label) supaya bisa dijumlahkan server.
- Otomatis menautkan form 65 (resusitasi) dan 66 (APGAR) sebagai banner di panel atas.

### Cetak

`.../BayiBaruLahir/print.blade.php` — lembar estilos newborn, tanda tangan witnessing.

---

## 9. Form 65 — Riwayat Resusitasi Bayi

### Rasional

Dokumentasi tindakan resusitasi neonatal beserta parameter CPAP bila diberikan.

### Struktur Field

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `riwayat_resusitasi` | 583 | Riwayat Resusitasi | radio | ya | Ya / Tidak |
| `tindakan_resusitasi_1..6` | 584 | Tindakan Resusitasi | checkbox | ya bila Ya | Pembersihan jalan napas · Perangsangan · Pemberian O2 dengan CPAP · Pemberian O2 dengan ventilasi tekanan positif · Intubasi · Kompresi dada |
| `peep_cpap` | 585 | Parameter Ventilasi CPAP | number | tidak | cmH₂O |
| `flow_cpap` | 585 | Parameter Ventilasi CPAP | number | tidak | L/menit |
| `fio2_cpap` | 585 | Parameter Ventilasi CPAP | text | tidak | % |
| `durasi_cpap` | 585 | Parameter Ventilasi CPAP | text | tidak | |
| `catatan_resusitasi` | 586 | Catatan Resusitasi | textarea | tidak | |

`tindakan_resusitasi_3` tercentang ⇒ tiga parameter CPAP aktif. `tindakan_resusitasi_4` tercentang ⇒ `catatan_resusitasi` wajib.

### Dashboard & Form Master

```php
['form_id' => 65, 'nama_form' => 'Riwayat Resusitasi Bayi', 'slug' => 'riwayat_resusitasi_bayi', 'id_dash_menu' => '13.259', 'ri' => 1, 'rj' => 0, 'igd' => 0, 'mcu' => 0],
```

### Mapping

```php
65 => [
    'riwayat_resusitasi' => 583,
    'peep_cpap' => 585, 'flow_cpap' => 585, 'fio2_cpap' => 585, 'durasi_cpap' => 585,
    'catatan_resusitasi' => 586,
],
for ($i = 1; $i <= 6; $i++) { $mapping[65]['tindakan_resusitasi_'.$i] = 584; }
```

### Akses EHR

```php
['profesi_id' => 1, 'form_id' => 65, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 2, 'form_id' => 65, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
```

### Validasi

- `riwayat_resusitasi` `required|in:Ya,Tidak`.
- `withValidator`: `= Tidak` ⇒ semua `tindakan_resusitasi_*` kosong & dibuang; `= Ya` ⇒ minimal 1 tindakan.
- `peep_cpap` `nullable|numeric|between:0,30`; `flow_cpap` `nullable|numeric|between:0,30`; `fio2_cpap` `nullable|regex:/^\d{1,3}%?$/`.
- Server: `= Ya` **wajib** mengisi minimal satu `tindakan_resusitasi_*` (browser tidak dipercaya).

### Cetak

`.../RiwayatResusitasiBayi/print.blade.php`.

---

## 10. Form 66 — Penilaian Bayi dengan APGAR Score

### Rasional

Instrumen evaluasiimits neonatal pada menit ke-1, 5, 10, 15, dan 20. Struktur legacy persis: 5 tanda (Frekuensi Jantung, Usaha Napas, Tonus Otot, Refleks, Warna) × 5 titik waktu, dengan total per waktu.

### Tabel Penilaian APGAR (nilai 0/1/2 — skor = indeks kolom)

| Tanda | 0 | 1 | 2 |
|---|---|---|---|
| Frekuensi Jantung | Tidak ada | < 100 | > 100 |
| Usaha Napas | Tidak ada | Lambat | Menangis kuat |
| Tonus Otot | Lumpuh | Ekstremitas fleksi sedikit | Gerak aktif |
| Refleks | Tidak bereaksi | Gerak sedikit | Reaksi melawan |
| Warna | Biru/Pucat | Tubuh kemerahan, tangan & kaki biru | Kemerahan |

**Interpretasi (acuan service):** 7–10 Baik · 4–6 Perlu perhatian/tindakan segera · ≤ 3 Resusitasi.

### Struktur Field

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `apgar_frekuensi_jantung_1..20` | 587 | APGAR - Frekuensi Jantung | select 0/1/2 | ya | suffix menit 1, 5, 10, 15, 20 |
| `apgar_usaha_napas_1..20` | 588 | APGAR - Usaha Napas | select 0/1/2 | ya | idem |
| `apgar_tonus_otot_1..20` | 589 | APGAR - Tonus Otot | select 0/1/2 | ya | idem |
| `apgar_refleks_1..20` | 590 | APGAR - Refleks | select 0/1/2 | ya | idem |
| `apgar_warna_1..20` | 591 | APGAR - Warna | select 0/1/2 | ya | idem |
| `apgar_total_1..20` | 592 | APGAR - Total Skor | readonly number | ya | **TURUNAN, dihitung server** |

> Suffiks memakai menit (`_1`, `_5`, `_10`, `_15`, `_20`) — bukan indeks 1..5 — supaya variabel tetap terbaca saat form dibuka lagi.

### Dashboard & Form Master

```php
['form_id' => 66, 'nama_form' => 'Penilaian Bayi dengan APGAR Score', 'slug' => 'penilaian_bayi_dengan_apgar_score', 'id_dash_menu' => '13.260', 'ri' => 1, 'rj' => 0, 'igd' => 0, 'mcu' => 0],
```

### Mapping

```php
66 => [],
$menitApgar = [1, 5, 10, 15, 20];
$objekApgar = ['frekuensi_jantung' => 587, 'usaha_napas' => 588, 'tonus_otot' => 589, 'refleks' => 590, 'warna' => 591];
foreach ($objekApgar as $tanda => $objekId) {
    foreach ($menitApgar as $menit) {
        $mapping[66]['apgar_'.$tanda.'_'.$menit] = $objekId;
    }
}
foreach ($menitApgar as $menit) {
    $mapping[66]['apgar_total_'.$menit] = 592;
}
```

### Akses EHR

```php
['profesi_id' => 1, 'form_id' => 66, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 2, 'form_id' => 66, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
```

### Validasi & komputasi server

- Kelima tanda **wajib** terisi untuk menit 1 dan 5; menit 10/15/20 opsional.
- `ApgarHelper::hitung($data)` (helper baru, pola `EwsHelper`/`RisikoJatuhHelper`) menghitung ulang:
  ```php
  $total[$menit] = array_sum([
      (int) ($data['apgar_frekuensi_jantung_'.$menit] ?? 0),
      (int) ($data['apgar_usaha_napas_'.$menit] ?? 0),
      (int) ($data['apgar_tonus_otot_'.$menit] ?? 0),
      (int) ($data['apgar_refleks_'.$menit] ?? 0),
      (int) ($data['apgar_warna_'.$menit] ?? 0),
  ]);
  ```
- Nilai `apgar_total_*` dari browser **selalu dibuang** lalu ditulis ulang hasil hitungan server (PANDUAN §2.4).
- Tabel visual: kolom 0/1/2 sebagai header tetap, kolom waktu 1/5/10/15/20, baris TOTAL di bawah.
- Badge interpretasi: hijau (7–10), kuning (4–6), merah (≤ 3) — warna diambil dari `ApgarHelper::WARNA` via `@json` agar server & browser sama.

### Cetak

`.../PenilaianBayiDenganApgarScore/print.blade.php` — tabel APGAR lengkap dengan tanda tangan dokter.

---

## 11. Form 67 — Down Score (Neonatal)

### Rasional
Instrumen Down Score untuk menilai distress pernapasan neonatal. Struktur legacy persis: 5 item × 3 nilai (0/1/2), total, dan tabel interpretasi.

### Tabel Penilaian Down Score

| Item | 0 | 1 | 2 |
|---|---|---|---|
| Frekuensi Nafas | < 60 x/menit | 60–80 x/menit | > 80 x/menit |
| Retraksi | Tidak ada | Retraksi ringan | Retraksi berat |
| Sianosis | Tidak ada | Hilang dengan O₂ | Menetap dengan O₂ |
| Air entry (udara masuk) | Ada | Menurun | Tidak terdengar |
| Merintih | Tidak ada | Terdengar dengan stetoskop | Terdengar tanpa alat bantu |

**Interpretasi (dari legacy, verbatim):** Skor < 4 Gangguan pernapasan ringan · Skor 4–5 Gangguan pernapasan sedang · Skor > 6 Gangguan pernapasan berat (AGD harus dilakukan).

### Struktur Field

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `down_frekuensi_nafas` | 593 | Down Score - Frekuensi Nafas | radio 0/1/2 | ya | |
| `down_retraksi` | 594 | Down Score - Retraksi | radio 0/1/2 | ya | |
| `down_sianosis` | 595 | Down Score - Sianosis | radio 0/1/2 | ya | |
| `down_air_entry` | 596 | Down Score - Air Entry | radio 0/1/2 | ya | |
| `down_merintih` | 597 | Down Score - Merintih | radio 0/1/2 | ya | |
| `down_total` | 598 | Total Down Score | readonly number | ya | **TURUNAN, dihitung server** |
| `down_interpretasi` | 598 | Total Down Score | readonly text | ya | **TURUNAN** |

### Dashboard & Form Master

```php
['form_id' => 67, 'nama_form' => 'Down Score (Neonatal)', 'slug' => 'down_score', 'id_dash_menu' => '13.261', 'ri' => 1, 'rj' => 0, 'igd' => 0, 'mcu' => 0],
```

### Mapping

```php
67 => [
    'down_frekuensi_nafas' => 593,
    'down_retraksi' => 594,
    'down_sianosis' => 595,
    'down_air_entry' => 596,
    'down_merintih' => 597,
    'down_total' => 598,
    'down_interpretasi' => 598,
],
```

### Akses EHR

```php
['profesi_id' => 1, 'form_id' => 67, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 2, 'form_id' => 67, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
```

### Validasi & komputasi server

- Kelima item `required|in:0,1,2`.
- `DownScoreHelper::hitung($data)`:
  ```php
  $total = (int) $data['down_frekuensi_nafas'] + (int) $data['down_retraksi']
         + (int) $data['down_sianosis'] + (int) $data['down_air_entry']
         + (int) $data['down_merintih'];
  $interpretasi = $total < 4 ? 'Gangguan pernapasan ringan'
      : ($total <= 6 ? 'Gangguan pernapasan sedang' : 'Gangguan pernapasan berat (AGD harus dilakukan)');
  ```
  > **Catatan inconsistency:** tabel legacy tertulis *"Skor 4–5 sedang"* dan *"Skor > 6 berat"* — sehingga skor **6** tidak tercakup. Legacy `calculateTotal()` benar menjumlahkan, tetapi interpretasinya bolong. Desain ini menutup celah: `< 4` ringan, `4–6` sedang, `> 6` berat. **Putuskan bersama klinis sebelum implementasi** — bila tetap mengikuti legacy persis, skor 6 tampil tanpa label.
- `down_total` & `down_interpretasi` dari browser **dibuang**, ditulis ulang server.
- Badge warna: hijau (< 4), kuning (4–6), merah (> 6).

### Cetak

`.../DownScore/print.blade.php` — tabel + tabel interpretasi seperti legacy.

---

## 12. Form 68 — Inisiasi Menyusui Dini

### Rasional

Dokumentasi IMD (skin-to-skin 1 jam pertama). Legacy hanya radio Ya/Tidak; desain ini menambah waktu & alasan bila tidak dilakukan (kepatuhan IMD rumah sakit).

### Struktur Field

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `inisisasi_menyususui_dini` | 599 | Inisiasi Menyususui Dini | radio | ya | Ya / Tidak |
| `waktu_menyusui_dini` | 599 | Inisiasi Menyususui Dini | text | ya bila Ya | "… menit setelah lahir" |
| `durasi_menyusui_dini` | 599 | Inisiasi Menyususui Dini | text | tidak | lama IMD |
| `alasan_tidak_menyusui_dini` | 599 | Inisiasi Menyususui Dini | text | ya bila Tidak | |

### Dashboard & Form Master

```php
['form_id' => 68, 'nama_form' => 'Inisiasi Menyusui Dini', 'slug' => 'inisiasi_menyusui_dini', 'id_dash_menu' => '13.262', 'ri' => 1, 'rj' => 0, 'igd' => 0, 'mcu' => 0],
```

### Mapping

```php
68 => [
    'inisisasi_menyususui_dini' => 599,
    'waktu_menyususui_dini' => 599,
    'durasi_menyususui_dini' => 599,
    'alasan_tidak_menyususui_dini' => 599,
],
```

### Akses EHR

```php
['profesi_id' => 1, 'form_id' => 68, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 2, 'form_id' => 68, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
```

### Validasi

- `inisisasi_menyususui_dini` `required|in:Ya,Tidak`.
- `withValidator`: `= Ya` ⇒ `waktu_menyususui_dini` wajib & harus ≤ 60 menit; `= Tidak` ⇒ `alasan_tidak_menyususui_dini` wajib.
- Nilai di luar 60 menit tidak ditolak, hanya diberi badge peringatan (IMD > 1 jam tetap mungkin).

### Cetak

`.../InisiasiMenyusuiDini/print.blade.php`.

---

## 13. Form 69 — Skrining MDPK/MPP

### Rasional

Multidisiplin Pathways of Care: identifikasi risiko tinggi, asesmen per aspek, dan implementasi MPP beserta evaluasi. Legacy punya 3 panel:

1. **FORM SKRINING MPP** — 5 kategori risiko, masing-masing dengan problem pelayanan dan paraf MPP.
2. **FORM A (ASESMEN MPP)** — 6 aspek + plan + paraf.
3. **FORM B (IMPLEMENTASI MPP)** — baris berulang: waktu, implementasi, evaluasi, paraf.

### Struktur Field

**Panel 1 — Skrining (600–605)**

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `risiko_tinggi_1..4` | 600 | Risiko Tinggi MPP | checkbox | ya | Pelayanan inadekuat · Risiko readmisi · Risiko complain · Lainnya |
| `risiko_pembiayaan_1..4` | 601 | Risiko Pembiayaan MPP | checkbox | ya | Pasien tanpa asuransi dengan kebutuhan sumber daya tinggi · Dengan asuransi minimal coverage · Pembayar belum jelas · Lainnya |
| `kasus_kompleks_1..4` | 602 | Kasus Kompleks MPP | checkbox | ya | Kasus multidiagnosis · Kasus multiprovider · Mendapatkan banyak tindakan medis · Lainnya |
| `risiko_lama_rawat_1..2` | 603 | Risiko Lama Rawat MPP | checkbox | ya | Length of Stay lebih dari rencana rawat dalam clinical pathway · Waktu lebih (… jam/hari) |
| `risiko_psikososial_1..4` | 604 | Risiko Psikososial MPP | checkbox | ya | Riwayat dan risiko penelantaran · Pasien tanpa identitas · Support rendah dari keluarga · Lainnya |
| `*_lainnya` (5 grup) | 605 | Keterangan Lainnya Risiko MPP | text | ya bila Lainnya tercentang | 5 variabel: `risiko_tinggi_lainnya`, `risiko_pembiayaan_lainnya`, `kasus_kompleks_lainnya`, `risiko_lama_rawat_waktu_lebih`, `risiko_psikososial_lainnya` |
| `paraf_skrining_1..5` | 606 | Paraf Skrining MPP | text | ya | nama & paraf MPP per kategori risiko |

**Panel 2 — Form A Asesmen (607–608)**

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `forma_plan_1..6` | 607 | Plan Asesmen MPP (Form A) | textarea | ya | 1 Kondisi Klinis · 2 Aspek Psikososial dan spiritual · 3 Aspek Ekonomi dan Pembiayaan · 4 Kebutuhan Pemulangan pasien · 5 Asesmen caregiver · 6 Asesmen utilitas |
| `forma_paraf_1..6` | 608 | Paraf Asesmen MPP (Form A) | text | ya | |

**Panel 3 — Form B Implementasi (609–610)**

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `formb_waktu_1..10` | 609 | Implementasi MPP (Form B) | datetime-local | ya | minimal 1 baris |
| `formb_implementasi_1..10` | 609 | Implementasi MPP (Form B) | textarea | ya | idem |
| `formb_evaluasi_1..10` | 609 | Implementasi MPP (Form B) | textarea | ya | idem |
| `formb_paraf_1..10` | 610 | Paraf Implementasi MPP (Form B) | text | ya | idem |

> Baris Form B **maksimal 10** dengan suffix angka — `variabel` wajib unik (PANDUAN §2.2). Baris kosong dibuang di `filteredData()`.

Tanggal skrining & asesmen memakai objek **reuse** `155` Tanggal Observasi dan `156` Waktu Observasi.

### Dashboard & Form Master

```php
['form_id' => 69, 'nama_form' => 'Skrining MDPK/MPP', 'slug' => 'skrining_mpp', 'id_dash_menu' => '13.263', 'ri' => 1, 'rj' => 1, 'igd' => 0, 'mcu' => 0],
```

### Mapping

```php
69 => [
    'tanggal_skrining' => 155,
    'jam_skrining' => 156,
    'risiko_tinggi_1' => 600, 'risiko_tinggi_2' => 600, 'risiko_tinggi_3' => 600, 'risiko_tinggi_4' => 600,
    'risiko_pembiayaan_1' => 601, 'risiko_pembiayaan_2' => 601, 'risiko_pembiayaan_3' => 601, 'risiko_pembiayaan_4' => 601,
    'kasus_kompleks_1' => 602, 'kasus_kompleks_2' => 602, 'kasus_kompleks_3' => 602, 'kasus_kompleks_4' => 602,
    'risiko_lama_rawat_1' => 603, 'risiko_lama_rawat_2' => 603,
    'risiko_psikososial_1' => 604, 'risiko_psikososial_2' => 604, 'risiko_psikososial_3' => 604, 'risiko_psikososial_4' => 604,
    'risiko_tinggi_lainnya' => 605, 'risiko_pembiayaan_lainnya' => 605, 'kasus_kompleks_lainnya' => 605,
    'risiko_lama_rawat_waktu_lebih' => 605, 'risiko_psikososial_lainnya' => 605,
],
for ($i = 1; $i <= 5; $i++) { $mapping[69]['paraf_skrining_'.$i] = 606; }
for ($i = 1; $i <= 6; $i++) { $mapping[69]['forma_plan_'.$i] = 607; $mapping[69]['forma_paraf_'.$i] = 608; }
for ($i = 1; $i <= 10; $i++) {
    $mapping[69]['formb_waktu_'.$i] = 609;
    $mapping[69]['formb_implementasi_'.$i] = 609;
    $mapping[69]['formb_evaluasi_'.$i] = 609;
    $mapping[69]['formb_paraf_'.$i] = 610;
}
```

### Akses EHR

```php
// Perawat (profesi 2) adalah perfis MPP utama — create/read/update penuh
['profesi_id' => 1, 'form_id' => 69, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 2, 'form_id' => 69, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
```

### Validasi

- Minimal **satu** kategori risiko tercentang di Panel 1.
- `*_lainnya` wajib bila checkbox "Lainnya" grup terkait tercentang.
- `paraf_skrining_N` wajib bila kategori risiko ke-N tercentang.
- Panel 2: keenam `forma_plan_N` & `forma_paraf_N` wajib.
- Panel 3: minimal satu baris Form B lengkap (waktu + implementasi + evaluasi + paraf).
- `filteredData()` membuang baris Form B yang keempat fieldnya kosong.

### Cetak

`.../SkriningMpp/print.blade.php` — **3 halaman** seperti legacy: (1) Form Skrining MPP, (2) Form A, (3) Form B. Kop surat berisi logo, nama pasien, No RM, ruang, tanggal.

---

## 14. Implementasi

Berkas yang harus dibuat:

| # | Path | Keterangan |
|---|---|---|
| 1 | `app/Helpers/ApgarHelper.php` | `hitung()`, `interpretasi()`, `WARNA` — pola `EwsHelper` |
| 2 | `app/Helpers/DownScoreHelper.php` | `hitung()`, `interpretasi()`, `WARNA` |
| 3 | `app/Http/Controllers/EMR/AsesmenKebidanan/AsesmenKebidananController.php` | form 59 |
| 4 | `.../PengkajianPerina/PengkajianPerinaController.php` | form 60 |
| 5 | `.../PengkajianNonPerina/PengkajianNonPerinaController.php` | form 61 |
| 6 | `.../KalaPersalinan/KalaPersalinanController.php` | form 62 |
| 7 | `.../RiwayatPersalinan/RiwayatPersalinanController.php` | form 63 |
| 8 | `.../BayiBaruLahir/BayiBaruLahirController.php` | form 64 |
| 9 | `.../RiwayatResusitasiBayi/RiwayatResusitasiBayiController.php` | form 65 |
| 10 | `.../PenilaianBayiDenganApgarScore/PenilaianBayiDenganApgarScoreController.php` | form 66 |
| 11 | `.../DownScore/DownScoreController.php` | form 67 |
| 12 | `.../InisiasiMenyusuiDini/InisiasiMenyusuiDiniController.php` | form 68 |
| 13 | `.../SkriningMpp/SkriningMppController.php` | form 69 (Form B = baris dinamis) |
| 14 | `resources/views/moduls/EMR/<Folder>/index.blade.php` | 13 view |
| 15 | `resources/views/moduls/EMR/<Folder>/print.blade.php` | 13 view cetak |
| 16 | `resources/views/moduls/EMR/PartialForm/sap_bayi.blade.php` | partial tabel SAP neonatal (dipakai form 60) |
| 17 | `app/Helpers/SelectOption.php` | tambah key `penolong_persalinan`, `warna_ketuban`, `imunisasi_hamil`, `derajat_derajat` |
| 18 | `database/seeders/EmrMasterSeeder.php` | `$menus`, `$subMenus`, `$forms`, `$objeks`, `$mapping`, `$akses` |
| 19 | `AGENTS.md` | entri form 59–69 |

**Migration / master baru:** **tidak ada**. Semua dropdown berasal dari `pasien`, `pegawai`, `SelectOption`, dan objek grup. Jika rumah sakit ingin master **Persalinan** (riwayat persalinan terakhir ibu yang dapat dipilih ulang) atau **Master ANC** (jadwal kunjungan kehamilan), tambahkan sebagai iterasi lanjutan — form 59/60 saat ini menyimpan riwayat sebagai EMR, bukan master.

Eksekusi seeder:

```bash
docker compose exec app php artisan migrate:fresh --seed   # ⚠️ DROP semua tabel
# atau, lebih aman:
docker compose exec app php artisan db:seed --class=EmrMasterSeeder
```

Tambahkan `EmrHelper::backfillObjekId(59..69)` di akhir `run()`.

---

## 15. Catatan & Risiko

| # | Risiko | Mitigasi |
|---|---|---|
| 1 | **Objek grup form 60 (561) & form 69 (600–610) berisi banyak variabel berbeda.** Laporan berbasis objek (`emr_detail.objek_id`) tidak bisa membedakan butir; harus berbasis `variabel`. | Rename objek 561 saat seeding menjadi **`Butir Pengkajian Perina`** (generik). Laporan butir wajib query `emr_detail.variabel`, bukan `objek_id`. Bila jumlah objek boleh lebih banyak di iterasi berikutnya, pecah per organ. |
| 2 | Suffix angka (VAP/`usg_ibu_1..5`, `riwayat_obst_*_1..5`, `formb_*_1..10`, `apgar_*_<menit>`) wajib konsisten antara seeder, blade, dan controller. | Mapping digenerate dengan loop di seeder (lihat kode di atas) — **jangan** menulis daftar manual yang bisa berbeda. |
| 3 | APGAR/Down Score diisi browser bisa dimanipulasi. | `ApgarHelper`/`DownScoreHelper::hitung()` menghitung ulang di `filteredData()`; nilai dari browser dibuang (PANDUAN §2.4). |
| 4 | Down Score interpretasi legacy tidak mencakup skor 6. | Sudah dijelaskan di §11 — **keputusan klinis masih perlu diambil**. Implementasi default memakai `< 4` / `4–6` / `> 6`. |
| 5 | `id_dash_menu` `'13.253'` s.d. `'13.263'` — substring perbandingan numeric string (`"13.253" == "13.262"` true). | Semua query pembanding `id_dash_menu` WAJIB `===` atau `where` di SQL, bukan `firstWhere()` dengan `==` (PANDUAN §5). |
| 6 | Akses perawat form 61 read-only tidak konsisten dengan form 3 (full CRUD). | Dapat diubah di menu *Akses EHR*; tabel di atas adalah default yang disarankan. |
| 7 | Perbandingan `id_dash_menu` sebagai numeric string (`"13.253" == "13.262"` → true). | Sudah diperbaiki di `AksesEhrController` menjadi closure `===`; jangan ditambahkan pola serupa. |
| 8 | ~1.200 baris `objek_form_control` baru; halaman Manajemen EMR → Form bisa melambat. | Filter per menu sudah ada; tambahkan pencarian objek bila perlu (PANDUAN §2.3 catatan). |
| 9 | Data legacy `emr_detail` Tenriawaru tidak dimigrasikan (objek_id lama 164, 695, 697, dst. tidak sama). | Dokumen ini hanya rancangan; migrasi data legacy adalah proyek terpisah. |
| 10 | `Switch` `x-select_dokter` untuk `dokter_pemeriksa_id` — form 59 juga boleh diisi bidan. | Bila perlu, tambahkan `x-select_pegawai` dengan `profesiId` opsional sehingga bisa memilih bidan (AGENTS.md: `x-select_pegawai` menerima prop `profesiId`). |