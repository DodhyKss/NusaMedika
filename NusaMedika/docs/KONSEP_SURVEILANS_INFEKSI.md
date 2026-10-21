# Konsep Form EMR: Surveilans Infeksi & Restraint

Rancangan & catatan implementasi 7 form EMR domain **Surveilans Infeksi dan
Peng-controlled** (form 116–122): 4 formulir surveilans bundle pencegahan
infeksi nosokomial, screening akses vaskular, asesmen restraint, dan observasi
restraint. Dokumen ini adalah konsep — belum ada kode yang ditulis.

**Status:** Rancangan / Konsep — belum diimplementasikan
**Tanggal:** 2026-10-10
**Sumber legacy:** `/simrs_tenriawaru/FE/lib/modul/`
**Alokasi:** objek_id **691–729**, `dashboard_menu_id = 17`, sub-menu **333–339**
(band objek §1 & band sub §3 `ALOKASI_ID_GLOBAL.md`; tidak ada `sub_extra`)

---

## 1. Ringkasan

| form_id | nama_form | slug | id_dash_menu | ri | rj | igd | mcu |
|---|---|---|---|---|---|---|---|
| 116 | Surveilans Pasien Terpasang CVC/PICC | `surveilans_cvc_picc` | `17.333` | 1 | 0 | 1 | 0 |
| 117 | Surveilans Pasien Terpasang IV Catheter Perifer | `surveilans_iv_catheter` | `17.334` | 1 | 0 | 1 | 0 |
| 118 | Surveilans Pasien Terpasang Kateter Urine | `surveilans_kateter_urine` | `17.335` | 1 | 0 | 1 | 0 |
| 119 | Surveilans Pasien Tirah Baring Total | `surveilans_tirah_baring` | `17.336` | 1 | 0 | 0 | 0 |
| 120 | Screening Akses Vaskular | `screening_akses` | `17.337` | 1 | 0 | 1 | 0 |
| 121 | Asesmen Pasien Restraint | `asesmen_restraint` | `17.338` | 1 | 0 | 0 | 0 |
| 122 | Observasi Restraint | `observasi_restraint` | `17.339` | 1 | 0 | 0 | 0 |

Menu **17 "Surveilans Infeksi"** adalah menu dashboard EMR baru.
`dashboard_menu_sub_id` bersifat **global**, jadi sub-menu baru memakai
**333–339** — band sub dokumen ini dimulai dari 333 sesuai
`ALOKASI_ID_GLOBAL.md` §3 (angka 1–12 milik menu 1–5; band sebelumnya
313–316 dipakai `KONSEP_PSIKOSOSIAL_KEROHANIAN.md`).

> **Rantai `slug` (PANDUAN §2.1).** Sub-menu tanpa extra memakai
> `nama_sub_menu` sebagai `form_name` di dashboard, jadi
> `Str::slug(nama_sub_menu, '_')` wajib sama dengan `form.slug`:
>
> | sub | `nama_sub_menu` | `Str::slug(...)` **harus** | `form.slug` (dikunci) |
> |---|---|---|---|
> | 333 | `Surveilans CVC PICC` | `surveilans_cvc_picc` | `surveilans_cvc_picc` |
> | 334 | `Surveilans IV Catheter` | `surveilans_iv_catheter` | `surveilans_iv_catheter` |
> | 335 | `Surveilans Kateter Urine` | `surveilans_kateter_urine` | `surveilans_kateter_urine` |
> | 336 | `Surveilans Tirah Baring` | `surveilans_tirah_baring` | `surveilans_tirah_baring` |
> | 337 | `Screening Akses` | `screening_akses` | `screening_akses` |
> | 338 | `Asesmen Restraint` | `asesmen_restraint` | **`asesmen_restraint`** |
> | 339 | `Observasi Restraint` | `observasi_restraint` | `observasi_restraint` |
>
> ### ✅ Sub 333: garis miring `/` **dihapus**, bukan diganti separator
>
> Klaim lama di dokumen ini ("`Surveilans CVC/PICC` menghasilkan
> `surveilans_cvc-picc`") **salah**. Sudah diverifikasi terhadap
> `Illuminate\Support\Str::slug()` di `vendor/laravel/framework`:
>
> ```
> Str::slug('Surveilans CVC/PICC','_') === 'surveilans_cvcpicc'   // "/" dihapus, kata menempel
> Str::slug('Surveilans CVC PICC','_') === 'surveilans_cvc_picc'  // spasi -> separator
> ```
>
> `Str::slug()` membuang karakter yang bukan separator/huruf/angka/spasi
> (`preg_replace('![^_\pL\pN\s]+!u', '', ...)`) — **tidak** pernah menyisipkan
> separator. Jadi bentuk `CVC/PICC` **tidak boleh** dipakai sebagai
> `nama_sub_menu`: slug-nya menjadi `surveilans_cvcpicc` dan tidak akan pernah
> sama dengan `surveilans_cvc_picc` yang tercatat di ledger.
>
> Sub menu **tetap ditulis "Surveilans CVC PICC"** (tanpa `/`) — bentuk inilah
> yang benar-benar menghasilkan slug `surveilans_cvc_picc`. Nama alternate
> `Surveilans CVC dan PICC` juga valid secara slug
> (`surveilans_cvc_dan_picc`), tetapi **tidak boleh** dipakai karena tidak cocok
> dengan ledger §6.
>
> Aturan yang sama berlaku untuk seluruh sub menu dokumen ini: **`/` dan `&`
> tidak boleh muncul di `nama_sub_menu`**. Keduanya dibuang oleh `Str::slug()`
> (bukan diganti separator), sehingga bentuk `Surveilans IV Catheter`
> (`surveilans_iv_catheter`) dan `Surveilans Kateter Urine`
> (`surveilans_kateter_urine`) — keduanya tanpa `/` — tetap cocok dengan
> ledger §6. `/` dan `&` hanya boleh muncul di `nama_form`, karena
> `nama_form` tidak pernah menjadi sumber slug.
>
> ### ⚠️ Sub 338: penyimpangan `assesmen` yang disengaja
>
> `Str::slug('Asesmen Restraint','_')` = `asesmen_restraint` (satu s), sedangkan
> `form.slug` terkunci `asesmen_restraint` (dua s). Ini **penyimpangan yang
> disengaja** sesuai `ALOKASI_ID_GLOBAL.md` §7: ejaan `assesmen` sudah menjadi
> konvensi proyek (form 11 `assesmen_awal_medis_rawat_jalan`). `nama_sub_menu`
> tetap ditulis `Asesmen Restraint`; hanya nilai `form.slug` yang di-override
> manual di seeder.

---

## 2. Sumber Referensi Legacy

| Berkas legacy | Dipakai untuk |
|---|---|
| `surveilans_pasien_terpasang_CVC_atau_PICC.php` (565 baris) | Lokasi / No. Cath / Jenis CVC + 15 baris item pencegahan BSI + rumus CLABSI |
| `surveilans_pasien_terpasang_IV_catheter_perifer.php` (710 baris) | 7 item pencegahan Peripheral Intravenous + skala PIVAS 0–4 |
| `surveilans_pasien_terpasang_kateter_urine.php` (720 baris) | 11 item pencegahan ISK + gejala ISK (klinis & laboratorium) + rumus CA-UTI |
| `surveilans_pasien_tirah_baring_total.php` (521 baris) | HAP bundle checklist + gejala HAP (suhu, leukosit, antibiotik, sekresi trakea, kultur sputum) |
| `screening_akses.php` (752 baris) | Akses vaskuler AV Shunt/Cimino & CDL, tabel A–B dan 1–5 |
| `assesmen_pasien_restraint.php` (34 KB) | Pertimbangan klinis, kebutuhan restraint, kriteria penghentian, pengkajian fisik & mental, informasi & edukasi |
| `assesmen_pasien_restraint_observasi.php` | Form baris observasi (jam, TTV, kognitif, luka, asupan, posisi fiksasi, PANSS-EC) |
| `restraint_cak.php` | Riwayat restraint & jenis restraint sebagai konteks |

Struktur yang benar-benar diambil dari legacy:

- **CVC/PICC** — kolom `Lokasi` (Subclavia / Jugularis / Femoralis + lainnya),
  `No.Cath` (12 / 7 / 5 / 4 / 3 + lainnya), `Jenis CVC` (4/3/2/1 lumen), lalu
  tabel **"Item Pencegahan BSI (Blood Stream Infection)"** dengan baris: Pasang,
  Lepas, Cuci tangan sesuai 5 moment, Pemasangan dengan teknik aseptik,
  (1) gunakan masker/penutup kepala/gown steril/duk steril saat insersi,
  (2) bersihkan area insersi dengan antiseptik, Teknik aseptik saat
  injeksi/sambung tubing, kondisi dressing baik (bersih, fiksasi baik), ganti
  dressing setiap hari/kotor, lalu kriteria BSI: (a) kuman pada kultur darah*,
  (b) demam > 38 °C, hipotermi, hipotensi, apneu, bradikardi.
  Rumus legacy: *(Jumlah pasien CLABSI / jumlah hari pemasangan CVC/PICC) x 1000*.
- **IV Catheter** — item: pemasangan dengan teknik aseptik, nomor IV catheter
  sesuai lokasi, jenis cairan sesuai lokasi pemasangan, metode fiksasi benar,
  kebersihan tangan sebelum & sesudah kontak, penutup dengan transparan
  dressing, dokumentasi tertulis tanggal dan jam; lalu **PIVAS Score 0–4**
  beserta deskripsi gejalanya.
- **Kateter Urine** — `Jenis Cath` (Silikon / Folly / lainnya), `No. Cath`
  (6 / 8 / 10 / 12 / 14), 11 item pencegahan ISK (cuci tangan 5 moment,
  pemasangan aseptik, fiksasi, urin bag di bawah bladder, posisi kateter tidak
  tertekuk/terlipat, urin bag tidak menyentuh lantai, tidak bladder training
  dengan klem, tidak membuka sambungan, perineal higiene 2x/hari, gelas ukur
  terpisah, ada indikasi pemakaian), lalu gejala ISK (demam >= 38 °C, nyeri
  supra-pubic, urgency/anyang-anyangan, frequency, dysuria; kuman biakan urine
  >= 10^5; pyuria >= 10 leukosit/ul).
- **Tirah Baring** — `HAP Bundle Prevention Cheklist` (cuci tangan 5 moment,
  oral hygiene 2x/day, posisi kepala 30–45°, ganti/bersihkan peralatan bila
  dipakai bersama) dan `GEJALA HAP` (suhu 36–38 / > 38 / < 36; leukosit
  4000–11.999 / >= 12.000 / <= 4.000; antibiotik 1–3; sekresi trakea; hasil
  kultur sputum dengan 3 tingkat). Rumus legacy: *(jumlah pasien dengan HAP /
  jumlah hari tirah baring total) x 1000*.
- **Screening Akses** — `jenis_spesimen` = `SCREENING AKSES AV SHUNT/CIMINO` /
  `SCREENING AKSES VASKULER CDL`; tabel A (Kondisi Akses), B (Steal syndrome),
  dan 1–5 (CDL, Kondisi pasien, Exit Site, Tanda CDL disfungsi/malfungsi,
  terkait pasien nyeri).
- **Restraint** — Penilaian Gaduh Gelisah (Perawat) 5 item + Penilaian dan Order
  Dokter, Kebutuhan Restraint Non Farmakologis 5 item + Farmakologis, kriteria
  penghentian 6 item + Lainnya, Pengkajian Fisik dan Mental (kesadaran, TTV,
  status mental), Informasi dan Edukasi (tanggal/jam, petugas, keluarga,
  petugas yang melepas).
- **Observasi Restraint** — tabel baris: No, Jam, TTV (TD/Nadi/Napas/Suhu),
  Fungsi Kognitif, Tanda Cedera/Luka, Asupan Makanan & Minuman, Posisi
  Fiksasi, Pencatat, PANSS-EC, Aksi.

---

## 3. Surveilans Pasien Terpasang CVC/PICC (form 116)

### Rasional / tujuan klinis

Bundle pencegahan CLABSI (Central Line-Associated Blood Stream Infection)
dimonitor **per hari**. Dua tujuan: (1) memastikan seluruh butir pencegahan
terimplementasi, (2) deteksi dini kriteria BSI agar hari ke berapa infeksi
terjadi dan berapa lama kateter terpasang dapat dihitung — jumlah hari
pemasangan inilah yang menjadi penyebut (denominator) rumus CLABSI IPCN.

### Struktur Field

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `tanggal_evaluasi` | 155 (reuse) | Tanggal Observasi | date | ya | Legacy `tgl_tindakan` |
| `waktu_evaluasi` | 156 (reuse) | Waktu Observasi | time | ya | |
| `lokasi_subclavia` | 691 | Lokasi Pemasangan CVC/PICC | select `Ya`/`Tidak`/`N/A` | ya | |
| `lokasi_jugularis` | 691 | Lokasi Pemasangan CVC/PICC | select | ya | |
| `lokasi_femoralis` | 691 | Lokasi Pemasangan CVC/PICC | select | ya | |
| `lokasi_lainnya` | 691 | Lokasi Pemasangan CVC/PICC | text | ya* | Wajib bila satu lokasi `Tidak` semua |
| `cath_12` | 692 | Nomor Kateter CVC/PICC | select | ya | Fr 12 |
| `cath_7` | 692 | Nomor Kateter CVC/PICC | select | ya | Fr 7 |
| `cath_5` | 692 | Nomor Kateter CVC/PICC | select | ya | Fr 5 |
| `cath_4` | 692 | Nomor Kateter CVC/PICC | select | ya | Fr 4 |
| `cath_3` | 692 | Nomor Kateter CVC/PICC | select | ya | Fr 3 |
| `cath_lainnya` | 692 | Nomor Kateter CVC/PICC | text | ya* | |
| `cvc_4lumen` | 693 | Jenis CVC/PICC (Lumen) | select | ya | 4 lumen |
| `cvc_3lumen` | 693 | Jenis CVC/PICC (Lumen) | select | ya | 3 lumen |
| `cvc_2lumen` | 693 | Jenis CVC/PICC (Lumen) | select | ya | 2 lumen |
| `cvc_1lumen` | 693 | Jenis CVC/PICC (Lumen) | select | ya | 1 lumen |
| `bsi_pasang` | 694 | Status Pemasangan & Pelepasan CVC/PICC | select | ya | Baris "Pasang" pada tanggal ini |
| `bsi_lepas` | 694 | Status Pemasangan & Pelepasan CVC/PICC | select | ya | Baris "Lepas" pada tanggal ini |
| `tanggal_pasang` | 695 | Tanggal Pemasangan & Pelepasan CVC/PICC | date | tidak | Kosong bila alat sudah terpasang sebelum masuk |
| `tanggal_lepas` | 695 | Tanggal Pemasangan & Pelepasan CVC/PICC | date | tidak | Kosong = masih terpasang |
| `bsi_1` | 696 | Item Pencegahan BSI - Kebersihan Tangan & Aseptik | select | ya | Cuci tangan sesuai 5 moment |
| `bsi_2` | 696 | Item Pencegahan BSI - Kebersihan Tangan & Aseptik | select | ya | Pemasangan dengan teknik aseptik |
| `bsi_3` | 697 | Item Pencegahan BSI - Alat Pelindung, Antiseptik & Dressing | select | ya | Masker, penutup kepala, gown steril, duk steril saat insersi |
| `bsi_4` | 697 | Item Pencegahan BSI - Alat Pelindung, Antiseptik & Dressing | select | ya | Bersihkan area insersi dengan antiseptik |
| `bsi_5` | 697 | Item Pencegahan BSI - Alat Pelindung, Antiseptik & Dressing | select | ya | Teknik aseptik saat injeksi/sambung tubing |
| `bsi_6` | 697 | Item Pencegahan BSI - Alat Pelindung, Antiseptik & Dressing | select | ya | Kondisi dressing baik (bersih, fiksasi baik) |
| `bsi_7` | 697 | Item Pencegahan BSI - Alat Pelindung, Antiseptik & Dressing | select | ya | Ganti dressing setiap hari/kotor |
| `bsi_8` | 698 | Gejala & Tanda BSI (Kriteria CLABSI) | select | ya | a. Kuman pada kultur darah* |
| `bsi_9` | 698 | Gejala & Tanda BSI (Kriteria CLABSI) | select | ya | b. Demam > 38 C |
| `bsi_10` | 698 | Gejala & Tanda BSI (Kriteria CLABSI) | select | ya | Hipotermi |
| `bsi_11` | 698 | Gejala & Tanda BSI (Kriteria CLABSI) | select | ya | Hipotensi |
| `bsi_12` | 698 | Gejala & Tanda BSI (Kriteria CLABSI) | select | ya | Apneu |
| `bsi_13` | 698 | Gejala & Tanda BSI (Kriteria CLABSI) | select | ya | Bradikardi |
| `keterangan` | 77 (reuse) | Keterangan | textarea | tidak | Nama kuman hasil kultur & sensitivitasnya |

`ya*` = wajib kondisional.

### Dashboard & Form Master

```php
// $menus
['dashboard_menu_id' => 17, 'nama_menu' => 'Surveilans Infeksi'],

// $subMenus
['dashboard_menu_sub_id' => 333, 'dashboard_menu_id' => 17, 'nama_sub_menu' => 'Surveilans CVC PICC'],
['dashboard_menu_sub_id' => 334, 'dashboard_menu_id' => 17, 'nama_sub_menu' => 'Surveilans IV Catheter'],
['dashboard_menu_sub_id' => 335, 'dashboard_menu_id' => 17, 'nama_sub_menu' => 'Surveilans Kateter Urine'],
['dashboard_menu_sub_id' => 336, 'dashboard_menu_id' => 17, 'nama_sub_menu' => 'Surveilans Tirah Baring'],
['dashboard_menu_sub_id' => 337, 'dashboard_menu_id' => 17, 'nama_sub_menu' => 'Screening Akses'],
['dashboard_menu_sub_id' => 338, 'dashboard_menu_id' => 17, 'nama_sub_menu' => 'Asesmen Restraint'],
['dashboard_menu_sub_id' => 339, 'dashboard_menu_id' => 17, 'nama_sub_menu' => 'Observasi Restraint'],

// $forms
['form_id' => 116, 'nama_form' => 'Surveilans Pasien Terpasang CVC/PICC', 'slug' => 'surveilans_cvc_picc', 'id_dash_menu' => '17.333', 'ri' => 1, 'rj' => 0, 'igd' => 1, 'mcu' => 0],
['form_id' => 117, 'nama_form' => 'Surveilans Pasien Terpasang IV Catheter Perifer', 'slug' => 'surveilans_iv_catheter', 'id_dash_menu' => '17.334', 'ri' => 1, 'rj' => 0, 'igd' => 1, 'mcu' => 0],
['form_id' => 118, 'nama_form' => 'Surveilans Pasien Terpasang Kateter Urine', 'slug' => 'surveilans_kateter_urine', 'id_dash_menu' => '17.335', 'ri' => 1, 'rj' => 0, 'igd' => 1, 'mcu' => 0],
['form_id' => 119, 'nama_form' => 'Surveilans Pasien Tirah Baring Total', 'slug' => 'surveilans_tirah_baring', 'id_dash_menu' => '17.336', 'ri' => 1, 'rj' => 0, 'igd' => 0, 'mcu' => 0],
['form_id' => 120, 'nama_form' => 'Screening Akses Vaskular', 'slug' => 'screening_akses', 'id_dash_menu' => '17.337', 'ri' => 1, 'rj' => 0, 'igd' => 1, 'mcu' => 0],
['form_id' => 121, 'nama_form' => 'Asesmen Pasien Restraint', 'slug' => 'asesmen_restraint', 'id_dash_menu' => '17.338', 'ri' => 1, 'rj' => 0, 'igd' => 0, 'mcu' => 0],
['form_id' => 122, 'nama_form' => 'Observasi Restraint', 'slug' => 'observasi_restraint', 'id_dash_menu' => '17.339', 'ri' => 1, 'rj' => 0, 'igd' => 0, 'mcu' => 0],

// $objeks (691-698)
691 => 'Lokasi Pemasangan CVC/PICC',
692 => 'Nomor Kateter CVC/PICC',
693 => 'Jenis CVC/PICC (Lumen)',
694 => 'Status Pemasangan & Pelepasan CVC/PICC',
695 => 'Tanggal Pemasangan & Pelepasan CVC/PICC',
696 => 'Item Pencegahan BSI - Kebersihan Tangan & Aseptik',
697 => 'Item Pencegahan BSI - Alat Pelindung, Antiseptik & Dressing',
698 => 'Gejala & Tanda BSI (Kriteria CLABSI)',
```

### Mapping

```php
116 => [
    'tanggal_evaluasi' => 155,  // reuse
    'waktu_evaluasi'   => 156,  // reuse

    'lokasi_subclavia' => 691,
    'lokasi_jugularis' => 691,
    'lokasi_femoralis' => 691,
    'lokasi_lainnya'   => 691,

    'cath_12'      => 692,
    'cath_7'       => 692,
    'cath_5'       => 692,
    'cath_4'       => 692,
    'cath_3'       => 692,
    'cath_lainnya' => 692,

    'cvc_4lumen' => 693,
    'cvc_3lumen' => 693,
    'cvc_2lumen' => 693,
    'cvc_1lumen' => 693,

    'bsi_pasang'     => 694,   // Ya / Tidak (kenaikan "Pasang" hari ini)
    'bsi_lepas'      => 694,   // Ya / Tidak (kenaikan "Lepas" hari ini)
    'tanggal_pasang' => 695,
    'tanggal_lepas'  => 695,

    // Butir pencegahan BSI - WAJIB bersuffix (pola Bundle VAP form 14).
    'bsi_1' => 696,  // Cuci tangan sesuai 5 moment
    'bsi_2' => 696,  // Pemasangan dengan teknik aseptik
    'bsi_3' => 697,  // Masker/penutup kepala/gown steril/duk steril saat insersi
    'bsi_4' => 697,  // Bersihkan area insersi dengan antiseptik
    'bsi_5' => 697,  // Teknik aseptik saat injeksi/sambung tubing
    'bsi_6' => 697,  // Kondisi dressing baik
    'bsi_7' => 697,  // Ganti dressing setiap hari/kotor

    // Kriteria BSI (CLABSI)
    'bsi_8'  => 698,  // a. Kuman pada kultur darah*
    'bsi_9'  => 698,  // b. Demam > 38 C
    'bsi_10' => 698,  // Hipotermi
    'bsi_11' => 698,  // Hipotensi
    'bsi_12' => 698,  // Apneu
    'bsi_13' => 698,  // Bradikardi

    'keterangan' => 77, // reuse objek Keterangan
],
```

> **Kenapa satu objek untuk beberapa butir?** Legacy memetakan seluruh butir ke
> objek yang sama (`objek_id[tgl_evaluasi] = 54` untuk 15 baris CVC). Pola yang
> sama disalin: `objek_form_control` mengizinkan banyak variabel menuju satu
> objek. Yang **wajib unik** adalah `variabel` (PANDUAN §2.2), bukan `objek_id`.
> quantifiable laporan per butir tetap tersedia karena tiap butir punya variabel
> sendiri.

### Akses EHR

```php
['profesi_id' => 1, 'form_id' => 116, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 2, 'form_id' => 116, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 9, 'form_id' => 116, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 0],
```

> Profesi **9 = Sanitarian** berperan sebagai IPCN (Infection Prevention & Control
> Nurse). Ia boleh membuat dan mengubah baris surveilans tetapi **tidak** boleh
> menghapus — penghapusan hanya oleh perawat/dokter.

### Validasi

```php
private const JAWABAN = ['Ya', 'Tidak', 'N/A'];

private function validated(Request $request): array
{
    $rules = [
        'tanggal_evaluasi' => 'required|date',
        'waktu_evaluasi'   => 'required|date_format:H:i',
        'keterangan'       => 'nullable|string|max:2000',
        'tanggal_pasang'   => 'nullable|date',
        'tanggal_lepas'    => 'nullable|date|after_or_equal:tanggal_pasang',
    ];

    $defaults = [
        'keterangan'     => null,
        'tanggal_pasang' => null,
        'tanggal_lepas'  => null,
        'lokasi_lainnya' => null,
        'cath_lainnya'   => null,
    ];

    foreach ([
        'lokasi_subclavia', 'lokasi_jugularis', 'lokasi_femoralis',
        'cath_12', 'cath_7', 'cath_5', 'cath_4', 'cath_3',
        'cvc_4lumen', 'cvc_3lumen', 'cvc_2lumen', 'cvc_1lumen',
        'bsi_pasang', 'bsi_lepas',
    ] as $key) {
        $rules[$key] = 'required|in:'.implode(',', self::JAWABAN);
        $defaults[$key] = null;
    }

    for ($butir = 1; $butir <= 13; $butir++) {
        $key = 'bsi_'.$butir;
        $rules[$key] = 'required|in:'.implode(',', self::JAWABAN);
        $defaults[$key] = null;
    }

    return array_merge($defaults, $request->validate($rules));
}
```

Tambahan di `filteredData()`:

- **Exactly-one** untuk lokasi: hanya satu dari `lokasi_subclavia`,
  `lokasi_jugularis`, `lokasi_femoralis` boleh bernilai `Ya`. Bila ketiganya
  `Tidak`, `lokasi_lainnya` wajib diisi. Divalidasi di level form (bukan hanya UI).
- Exactly-one untuk nomor kateter & jenis lumen (pola yang sama).
- `tanggal_lepas` hanya boleh diisi bila `bsi_lepas = Ya`.
- Nilai `N/A` **dikeluarkan** dari perhitungan kepatuhan — butir yang memang
  tidak berlaku tidak boleh menurunkan skor (pola
  `BundleVapController::hitungKepatuhan()`).

### Cetak

`print.blade.php` — tabel surveillance harian dengan kolom Tanggal dan seluruh
baris item pencegahan, plus baris gejala. Kolom penutup berisi rumus CLABSI
yang dihitung dari agregat seluruh EMR form 116 pada `registrasi_id` tersebut
(butuh helper baru `SurveilansHelper::hitungClabsi($registrasiId)`).

---

## 4. Surveilans Pasien Terpasang IV Catheter Perifer (form 117)

### Rasional / tujuan klinis

Pencegahan infeksi jalur vena perifer (phlebitis). Skala **PIVAS** (Peripheral
Intravenous Catheter Assessment Scale) memberi skor 0-4; skor >= 2 menjadi sinyal
untuk tindakan lanjutan (ganti lokasi / planned removal).

### Struktur Field

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `tanggal_evaluasi` | 155 | Tanggal Observasi | date | ya | |
| `waktu_evaluasi` | 156 | Waktu Observasi | time | ya | |
| `lokasi_iv` | 699 | Lokasi IV Catheter | select | ya | Dari `SelectOption::lokasi_alat_invasif` |
| `nomor_iv` | 700 | Nomor IV Catheter | text | ya | |
| `jenis_cairan` | 701 | Jenis Cairan/Terapi | text | ya | Legacy: jenis cairan yang dipasang |
| `iv_1` | 702 | Item Pencegahan IV - Pemasangan, Nomor & Cairan | select | ya | Pemasangan dengan teknik aseptik |
| `iv_2` | 702 | Item Pencegahan IV - Pemasangan, Nomor & Cairan | select | ya | Nomor IV catheter sesuai lokasi |
| `iv_3` | 702 | Item Pencegahan IV - Pemasangan, Nomor & Cairan | select | ya | Jenis cairan sesuai lokasi pemasangan |
| `iv_4` | 703 | Item Pencegahan IV - Fiksasi, Tangan, Dressing & Dokumentasi | select | ya | Metode fiksasi benar |
| `iv_5` | 703 | Item Pencegahan IV - Fiksasi, Tangan, Dressing & Dokumentasi | select | ya | Kebersihan tangan sebelum & sesudah kontak dengan IV cath/infus |
| `iv_6` | 703 | Item Pencegahan IV - Fiksasi, Tangan, Dressing & Dokumentasi | select | ya | Penutup IV catheter dengan transparan dressing |
| `iv_7` | 703 | Item Pencegahan IV - Fiksasi, Tangan, Dressing & Dokumentasi | select | ya | Dokumentasi tertulis tanggal dan jam |
| `pivas_skor` | 704 | Skor & Gejala PIVAS | select 0–4 | ya | 0 = tidak ada gejala |
| `pivas_gejala_1` | 704 | Skor & Gejala PIVAS | checkbox | ya | Skor 0: tidak ada rasa sakit / tanda phlebitis |
| `pivas_gejala_2` | 704 | Skor & Gejala PIVAS | checkbox | ya | Skor 1: nyeri / kemerahan di sekitar penusukan |
| `pivas_gejala_3` | 704 | Skor & Gejala PIVAS | checkbox | ya | Skor 2: nyeri, pembengkakan, kemerahan, vena teraba |
| `pivas_gejala_4` | 704 | Skor & Gejala PIVAS | checkbox | ya | Skor 3–4: nyeri sepanjang kanula, edema/indurasi, drainase purulen, demam |
| `tanggal_pasang` | 705 | Tanggal Pemasangan & Pelepasan IV Catheter | date | tidak | |
| `tanggal_lepas` | 705 | Tanggal Pemasangan & Pelepasan IV Catheter | date | tidak | |

### Dashboard & Form Master

```php
// $objeks (699-705)
699 => 'Lokasi IV Catheter',
700 => 'Nomor IV Catheter',
701 => 'Jenis Cairan/Terapi',
702 => 'Item Pencegahan IV - Pemasangan, Nomor & Cairan',
703 => 'Item Pencegahan IV - Fiksasi, Tangan, Dressing & Dokumentasi',
704 => 'Skor & Gejala PIVAS',
705 => 'Tanggal Pemasangan & Pelepasan IV Catheter',
```

### Mapping

```php
117 => [
    'tanggal_evaluasi' => 155,
    'waktu_evaluasi'   => 156,
    'lokasi_iv'        => 699,
    'nomor_iv'         => 700,
    'jenis_cairan'     => 701,
    'iv_1' => 702,  // Pemasangan dengan teknik aseptik
    'iv_2' => 702,  // Nomor IV catheter sesuai lokasi
    'iv_3' => 702,  // Jenis cairan sesuai lokasi pemasangan
    'iv_4' => 703,  // Metode fiksasi benar
    'iv_5' => 703,  // Kebersihan tangan sebelum & sesudah kontak
    'iv_6' => 703,  // Penutup transparan dressing
    'iv_7' => 703,  // Dokumentasi tertulis tanggal dan jam
    'pivas_skor'     => 704,  // 0..4
    'pivas_gejala_1' => 704,  // Skor 0
    'pivas_gejala_2' => 704,  // Skor 1
    'pivas_gejala_3' => 704,  // Skor 2
    'pivas_gejala_4' => 704,  // Skor 3-4
    'tanggal_pasang' => 705,
    'tanggal_lepas'  => 705,
],
```

### Akses EHR

```php
['profesi_id' => 1, 'form_id' => 117, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 2, 'form_id' => 117, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 9, 'form_id' => 117, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 0],
```

> Periksa ejaan key saat menyalin baris `akses_ehr`: selalu `akses_read`,
> `akses_create`, `akses_update`, `akses_delete`.

### Validasi

- `pivas_skor` `required|in:0,1,2,3,4`.
- Konsistensi skor ↔ gejala (server-side): `pivas_skor = 0` wajib_centang
  `pivas_gejala_1`; `pivas_skor >= 3` wajib centang `pivas_gejala_4`.
- `iv_1..iv_7` wajib dijawab `Ya`/`Tidak`/`N/A`.
- `lokasi_iv` dari `SelectOption::lokasi_alat_invasif`; bila diisi, harus salah
  satu dari nilai vena perifer (`Vena Mediana Cubiti`, `Vena Cephalica`,
  `Vena Basilica`, `Tangan`, `Kaki`).

### Cetak

`print.blade.php` — tabel harian + kolom PIVAS score, dirangkum per episode
IV catheter (satu baris `tanggal_pasang` sampai `tanggal_lepas`).

---

## 5. Surveilans Pasien Terpasang Kateter Urine (form 118)

### Rasional / tujuan klinis

Bundle pencegahan CA-UTI (Catheter-Associated Urinary Tract Infection). Untuk
CA-UTI, penyebut rumus adalah **jumlah hari kateter urine terpasang**, sehingga
kolom tanggal pemasangan dan pelepasan harus selalu terisi lengkap.

### Struktur Field

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `tanggal_evaluasi` | 155 | Tanggal Observasi | date | ya | |
| `waktu_evaluasi` | 156 | Waktu Observasi | time | ya | |
| `jenis_kath_silikon` | 706 | Jenis Kateter Urine | select | ya | |
| `jenis_kath_folly` | 706 | Jenis Kateter Urine | select | ya | |
| `jenis_kath_lainnya` | 706 | Jenis Kateter Urine | text | ya* | Wajib bila keduanya `Tidak` |
| `no_kath_6` | 707 | Nomor Kateter Urine | select | ya | Fr 6 |
| `no_kath_8` | 707 | Nomor Kateter Urine | select | ya | Fr 8 |
| `no_kath_10` | 707 | Nomor Kateter Urine | select | ya | Fr 10 |
| `no_kath_12` | 707 | Nomor Kateter Urine | select | ya | Fr 12 |
| `no_kath_14` | 707 | Nomor Kateter Urine | select | ya | Fr 14 |
| `isk_1` | 708 | Item Pencegahan ISK - Aseptik, Posisi Kateter & Perawatan Urin Bag | select | ya | Cuci tangan sesuai 5 moment |
| `isk_2` | 708 | Item Pencegahan ISK - Aseptik, Posisi Kateter & Perawatan Urin Bag | select | ya | Pemasangan dengan teknik aseptik |
| `isk_3` | 708 | Item Pencegahan ISK - Aseptik, Posisi Kateter & Perawatan Urin Bag | select | ya | Fiksasi dengan baik |
| `isk_4` | 708 | Item Pencegahan ISK - Aseptik, Posisi Kateter & Perawatan Urin Bag | select | ya | Urin bag di bawah bladder |
| `isk_5` | 708 | Item Pencegahan ISK - Aseptik, Posisi Kateter & Perawatan Urin Bag | select | ya | Posisi kateter baik/tidak tertekuk, terlipat |
| `isk_6` | 708 | Item Pencegahan ISK - Aseptik, Posisi Kateter & Perawatan Urin Bag | select | ya | Urin bag tidak menyentuh lantai |
| `isk_7` | 708 | Item Pencegahan ISK - Aseptik, Posisi Kateter & Perawatan Urin Bag | select | ya | Tidak bladder training dengan klem |
| `isk_8` | 708 | Item Pencegahan ISK - Aseptik, Posisi Kateter & Perawatan Urin Bag | select | ya | Tidak membuka sambungan antar cath dan selang |
| `isk_9` | 708 | Item Pencegahan ISK - Aseptik, Posisi Kateter & Perawatan Urin Bag | select | ya | Perineal hygiene dengan air dan sabun 2x/hari |
| `isk_10` | 709 | Item Pencegahan ISK - Gelas Ukur & Indikasi | select | ya | Gelas ukur terpisah antar pasien |
| `isk_11` | 709 | Item Pencegahan ISK - Gelas Ukur & Indikasi | select | ya | Ada indikasi pemakaian kateter urin |
| `isk_gejala_1` | 710 | Gejala & Tanda ISK | select | ya | a. Demam >= 38 C |
| `isk_gejala_2` | 710 | Gejala & Tanda ISK | select | ya | Nyeri supra - public |
| `isk_gejala_3` | 710 | Gejala & Tanda ISK | select | ya | Urgency / anyang-anyangan |
| `isk_gejala_4` | 710 | Gejala & Tanda ISK | select | ya | Frequency / sering kencing |
| `isk_gejala_5` | 710 | Gejala & Tanda ISK | select | ya | Dysuria / nyeri saat kencing |
| `isk_gejala_6` | 710 | Gejala & Tanda ISK | select | ya | b. Kuman biakan urine >= 10^5 |
| `isk_gejala_7` | 710 | Gejala & Tanda ISK | select | ya | c. Pyuria (>= 10 leukosit urin) |
| `tanggal_pasang` | 711 | Tanggal Pemasangan & Pelepasan Kateter Urine | date | ya | |
| `tanggal_lepas` | 711 | Tanggal Pemasangan & Pelepasan Kateter Urine | date | tidak | |

### Dashboard & Form Master

```php
// $objeks (706-711)
706 => 'Jenis Kateter Urine',
707 => 'Nomor Kateter Urine',
708 => 'Item Pencegahan ISK - Aseptik, Posisi Kateter & Perawatan Urin Bag',
709 => 'Item Pencegahan ISK - Gelas Ukur & Indikasi',
710 => 'Gejala & Tanda ISK',
711 => 'Tanggal Pemasangan & Pelepasan Kateter Urine',
```

### Mapping

```php
118 => [
    'tanggal_evaluasi' => 155,
    'waktu_evaluasi'   => 156,
    'jenis_kath_silikon' => 706,
    'jenis_kath_folly'   => 706,
    'jenis_kath_lainnya' => 706,
    'no_kath_6'  => 707,
    'no_kath_8'  => 707,
    'no_kath_10' => 707,
    'no_kath_12' => 707,
    'no_kath_14' => 707,
    'isk_1'  => 708,  // Cuci tangan sesuai 5 moment
    'isk_2'  => 708,  // Pemasangan dengan teknik aseptik
    'isk_3'  => 708,  // Fiksasi dengan baik
    'isk_4'  => 708,  // Urin bag di bawah bladder
    'isk_5'  => 708,  // Posisi kateter baik/tidak tertekuk, terlipat
    'isk_6'  => 708,  // Urin bag tidak menyentuh lantai
    'isk_7'  => 708,  // Tidak bladder training dengan klem
    'isk_8'  => 708,  // Tidak membuka sambungan antar cath dan selang
    'isk_9'  => 708,  // Perineal hygiene dengan air dan sabun 2x/hari
    'isk_10' => 709,  // Gelas ukur terpisah antar pasien
    'isk_11' => 709,  // Ada indikasi pemakaian kateter urin
    'isk_gejala_1' => 710,  // a. Demam >= 38 C
    'isk_gejala_2' => 710,  // Nyeri supra - public
    'isk_gejala_3' => 710,  // Urgency/anyang-anyangan
    'isk_gejala_4' => 710,  // Frequency
    'isk_gejala_5' => 710,  // Dysuria
    'isk_gejala_6' => 710,  // b. Kuman biakan urine >= 10^5
    'isk_gejala_7' => 710,  // c. Pyuria
    'tanggal_pasang' => 711,
    'tanggal_lepas'  => 711,
],
```

### Akses EHR

```php
['profesi_id' => 1, 'form_id' => 118, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 2, 'form_id' => 118, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 9, 'form_id' => 118, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 0],
```

### Validasi

- Semua item `isk_1..isk_11` dan gejala wajib dijawab `Ya`/`Tidak`/`N/A`.
- **Kriteria CA-UTI (server-side, informasional):** bila minimal salah satu dari
  `isk_gejala_1..5` dan salah satu dari `isk_gejala_6` atau `isk_gejala_7`
  bernilai `Ya`, tampilkan badge "Kriteria CA-UTI terpenuhi - hubungi IPCN" di
  ringkasan. Tidak memblokir penyimpanan.
- `tanggal_pasang` wajib; `tanggal_lepas` opsional (kosong = masih terpasang).

### Cetak

`print.blade.php` + kolom penutup berisi rumus CA-UTI
`(jumlah pasien CA-UTI / jumlah hari pemasangan kateter urine) x 1000`.

---

## 6. Surveilans Pasien Tirah Baring Total (form 119)

### Rasional / tujuan klinis

Bundle pencegahan HAP (Hospital Acquired Pneumonia) untuk pasien yang benar-benar
terbaring total, dilengkapi gejala HAP: suhu, leukosit, antibiotik yang diberikan,
sekresi trakea, dan hasil kultur sputum. Rumus legacy memakai jumlah hari
tirah baring sebagai denominator.

### Struktur Field

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `tanggal_evaluasi` | 155 | Tanggal Observasi | date | ya | |
| `waktu_evaluasi` | 156 | Waktu Observasi | time | ya | |
| `hap_1` | 712 | Bundle Pencegahan HAP | select | ya | Cuci tangan sesuai 5 moment |
| `hap_2` | 712 | Bundle Pencegahan HAP | select | ya | Oral hygiene 2x/day |
| `hap_3` | 712 | Bundle Pencegahan HAP | select | ya | Posisi kepala 30-45 derajat |
| `hap_4` | 712 | Bundle Pencegahan HAP | select | ya | Ganti/bersihkan peralatan bila dipakai bersama |
| `hap_suhu_1` | 713 | Suhu Tubuh & Leukosit pada Gejala HAP | select | ya | 36 c - 38 c |
| `hap_suhu_2` | 713 | Suhu Tubuh & Leukosit pada Gejala HAP | select | ya | > 38 c |
| `hap_suhu_3` | 713 | Suhu Tubuh & Leukosit pada Gejala HAP | select | ya | < 36 c |
| `hap_leukosit_1` | 713 | Suhu Tubuh & Leukosit pada Gejala HAP | select | ya | 4000 - 11.999/mm3 |
| `hap_leukosit_2` | 713 | Suhu Tubuh & Leukosit pada Gejala HAP | select | ya | >= 12000/mm3 |
| `hap_leukosit_3` | 713 | Suhu Tubuh & Leukosit pada Gejala HAP | select | ya | <= 4000/mm3 |
| `hap_ab_1` | 714 | Antibiotik yang Diberikan | text | ya* | Nama Ab 1 |
| `hap_ab_2` | 714 | Antibiotik yang Diberikan | text | tidak | Nama Ab 2 |
| `hap_ab_3` | 714 | Antibiotik yang Diberikan | text | tidak | Nama Ab 3 |
| `hap_sekresi_1` | 715 | Sekresi Trakea & Hasil Kultur Sputum | select | ya | Tidak ada / ada non-purulent |
| `hap_sekresi_2` | 715 | Sekresi Trakea & Hasil Kultur Sputum | select | ya | Ada purulent |
| `hap_kultur_1` | 715 | Sekresi Trakea & Hasil Kultur Sputum | select | ya | Tidak ada pertumbuhan kuman (tumbuh yeast/candida) |
| `hap_kultur_2` | 715 | Sekresi Trakea & Hasil Kultur Sputum | select | ya | Ada pertumbuhan kuman selain yeast/candida |
| `hap_kultur_3` | 715 | Sekresi Trakea & Hasil Kultur Sputum | select | ya | Tumbuh kuman selain yeast/candida dengan jumlah >= 10 CFU/ml |
| `keterangan` | 77 | Keterangan | textarea | tidak | Nama kuman hasil kultur & sensitivitasnya |

### Dashboard & Form Master

```php
// $objeks (712-715)
712 => 'Bundle Pencegahan HAP',
713 => 'Suhu Tubuh & Leukosit pada Gejala HAP',
714 => 'Antibiotik yang Diberikan',
715 => 'Sekresi Trakea & Hasil Kultur Sputum',
```

### Mapping

```php
119 => [
    'tanggal_evaluasi' => 155,
    'waktu_evaluasi'   => 156,
    'hap_1' => 712,  // Cuci tangan sesuai 5 moment
    'hap_2' => 712,  // Oral hygiene 2x/day
    'hap_3' => 712,  // Posisi kepala 30-45 derajat
    'hap_4' => 712,  // Ganti/bersihkan peralatan bila dipakai bersama
    'hap_suhu_1'     => 713,  // 36 c - 38 c
    'hap_suhu_2'     => 713,  // > 38 c
    'hap_suhu_3'     => 713,  // < 36 c
    'hap_leukosit_1' => 713,  // 4000 - 11.999/mm3
    'hap_leukosit_2' => 713,  // >= 12000/mm3
    'hap_leukosit_3' => 713,  // <= 4000/mm3
    'hap_ab_1' => 714,  // Nama Ab 1
    'hap_ab_2' => 714,  // Nama Ab 2
    'hap_ab_3' => 714,  // Nama Ab 3
    'hap_sekresi_1' => 715,  // Tidak ada / ada non-purulent
    'hap_sekresi_2' => 715,  // Ada purulent
    'hap_kultur_1'  => 715,  // Tidak ada pertumbuhan (tumbuh yeast/candida)
    'hap_kultur_2'  => 715,  // Ada pertumbuhan selain yeast/candida
    'hap_kultur_3'  => 715,  // Tumbuh kuman >= 10 CFU/ml
    'keterangan' => 77,
],
```

### Akses EHR

```php
['profesi_id' => 1, 'form_id' => 119, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 2, 'form_id' => 119, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 9, 'form_id' => 119, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 0],
```

### Validasi

- Semua item `hap_*` (selain `hap_ab_*`) wajib dijawab `Ya`/`Tidak`/`N/A`;
  `hap_ab_*` bebas teks, `hap_ab_1` wajib diisi.
- Deteksi gejala HAP (informasional): bila `hap_suhu_2 = Ya` **atau**
  `hap_leukosit_2 = Ya` **atau** `hap_sekresi_2 = Ya` **atau** `hap_kultur_3 = Ya`,
  tampilkan badge "Gejala HAP ditemukan" (peringatan, bukan blokir).
- Auto-prefill opsional: `hap_leukosit_*` dapat diisi dari hasil lab terakhir via
  `EmrHelper::latestValuesByVariabel()` bila lab sudah punya leukosit.

### Cetak

`print.blade.php` + kolom penutup berisi rumus HAP
`(jumlah pasien dengan HAP / jumlah hari tirah baring total) x 1000`.

---

## 7. Screening Akses Vaskular (form 120)

### Rasional / tujuan klinis

Sebelum memasang atau mengganti akses vaskuler (AV Shunt/Cimino untuk
hemodialisis, atau CDL untuk kemoterapi), perawat melakukan screening
kelayakan. Adanya steal syndrome, tanda radang/infeksi exit site, atau gejala
sistemik menjadi alasan menunda. Kontrol `jenis_spesimen` menentukan bagian mana
yang ditampilkan.

### Struktur Field

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `jenis_spesimen` | 716 | Jenis Screening Akses | select | ya | `SCREENING AKSES AV SHUNT/CIMINO` / `SCREENING AKSES VASKULER CDL` |
| `aks_area_1` | 717 | Kondisi Akses & Steal Syndrome | select `Ya`/`Tidak` | ya | Area AV shunt |
| `aks_area_2` | 717 | Kondisi Akses & Steal Syndrome | select | ya | Fundus teraba |
| `aks_area_3` | 717 | Kondisi Akses & Steal Syndrome | select | ya | Hematoma |
| `aks_steal_1` | 717 | Kondisi Akses & Steal Syndrome | select | ya | Jari tangan |
| `aks_steal_2` | 717 | Kondisi Akses & Steal Syndrome | select | ya | Nyeri |
| `aks_steal_3` | 717 | Kondisi Akses & Steal Syndrome | select | ya | Baal |
| `aks_steal_4` | 717 | Kondisi Akses & Steal Syndrome | select | ya | Tangan keriput |
| `aks_steal_5` | 717 | Kondisi Akses & Steal Syndrome | select | ya | Aneurisma |
| `aks_steal_6` | 717 | Kondisi Akses & Steal Syndrome | select | ya | Ulserasi |
| `aks_steal_7` | 717 | Kondisi Akses & Steal Syndrome | select | ya | Frekuensi nyeri |
| `aks_steal_8` | 717 | Kondisi Akses & Steal Syndrome | select | ya | Screening test |
| `aks_steal_9` | 717 | Kondisi Akses & Steal Syndrome | select | ya | Oedema tangan |
| `aks_cdl_1` | 718 | Kondisi CDL, Exit Site & Kondisi Pasien | select | ya | Kepatenan |
| `aks_cdl_2` | 718 | Kondisi CDL, Exit Site & Kondisi Pasien | select | ya | Tutup |
| `aks_cdl_3` | 718 | Kondisi CDL, Exit Site & Kondisi Pasien | select | ya | Keretakan |
| `aks_cdl_4` | 718 | Kondisi CDL, Exit Site & Kondisi Pasien | select | ya | Lokasi |
| `aks_exit_1` | 718 | Kondisi CDL, Exit Site & Kondisi Pasien | select | ya | Tanda bahaya |
| `aks_exit_2` | 718 | Kondisi CDL, Exit Site & Kondisi Pasien | select | ya | Pembengkakan |
| `aks_exit_3` | 718 | Kondisi CDL, Exit Site & Kondisi Pasien | select | ya | Perdarahan |
| `aks_exit_4` | 718 | Kondisi CDL, Exit Site & Kondisi Pasien | select | ya | Posisi |
| `aks_exit_5` | 718 | Kondisi CDL, Exit Site & Kondisi Pasien | select | ya | Pemicu nyeri |
| `aks_exit_6` | 718 | Kondisi CDL, Exit Site & Kondisi Pasien | select | ya | Memar |
| `aks_disfungsi_1` | 719 | Tanda CDL Disfungsi/Malfungsi & Terkait Pasien | select | ya | Tekanan arteri |
| `aks_disfungsi_2` | 719 | Tanda CDL Disfungsi/Malfungsi & Terkait Pasien | select | ya | Tekanan vena |
| `aks_disfungsi_3` | 719 | Tanda CDL Disfungsi/Malfungsi & Terkait Pasien | select | ya | Tekanan psikologis |
| `aks_pasien_1` | 718 | Kondisi CDL, Exit Site & Kondisi Pasien | select | ya | Benjolan |
| `aks_nyeri_1` | 719 | Tanda CDL Disfungsi/Malfungsi & Terkait Pasien | select | ya | Risiko perdarahan |
| `aks_nyeri_2` | 719 | Tanda CDL Disfungsi/Malfungsi & Terkait Pasien | select | ya | Pernapasan |
| `aks_nyeri_3` | 719 | Tanda CDL Disfungsi/Malfungsi & Terkait Pasien | select | ya | Jenis kateter |
| `aks_nyeri_4` | 719 | Tanda CDL Disfungsi/Malfungsi & Terkait Pasien | select | ya | Antisipasi perdarahan |

### Dashboard & Form Master

```php
// $objeks (716-719)
716 => 'Jenis Screening Akses',
717 => 'Kondisi Akses & Steal Syndrome',
718 => 'Kondisi CDL, Exit Site & Kondisi Pasien',
719 => 'Tanda CDL Disfungsi/Malfungsi & Terkait Pasien',
```

### Mapping

```php
120 => [
    'jenis_spesimen' => 716,
    'aks_area_1'   => 717,  // Area AV shunt
    'aks_area_2'   => 717,  // Fundus teraba
    'aks_area_3'   => 717,  // Hematoma
    'aks_steal_1'  => 717,  // Jari tangan
    'aks_steal_2'  => 717,  // Nyeri
    'aks_steal_3'  => 717,  // Baal
    'aks_steal_4'  => 717,  // Tangan keriput
    'aks_steal_5'  => 717,  // Aneurisma
    'aks_steal_6'  => 717,  // Ulserasi
    'aks_steal_7'  => 717,  // Frekuensi nyeri
    'aks_steal_8'  => 717,  // Screening test
    'aks_steal_9'  => 717,  // Oedema tangan
    'aks_cdl_1'    => 718,  // Kepatenan
    'aks_cdl_2'    => 718,  // Tutup
    'aks_cdl_3'    => 718,  // Keretakan
    'aks_cdl_4'    => 718,  // Lokasi
    'aks_exit_1'   => 718,  // Tanda bahaya
    'aks_exit_2'   => 718,  // Pembengkakan
    'aks_exit_3'   => 718,  // Perdarahan
    'aks_exit_4'   => 718,  // Posisi
    'aks_exit_5'   => 718,  // Pemicu nyeri
    'aks_exit_6'   => 718,  // Memar
    'aks_disfungsi_1' => 719, // Tekanan arteri
    'aks_disfungsi_2' => 719, // Tekanan vena
    'aks_disfungsi_3' => 719, // Tekanan psikologis
    'aks_pasien_1'    => 718, // Benjolan
    'aks_nyeri_1'     => 719, // Risiko perdarahan
    'aks_nyeri_2'     => 719, // Pernapasan
    'aks_nyeri_3'     => 719, // Jenis kateter
    'aks_nyeri_4'     => 719, // Antisipasi perdarahan
],
```

### Akses EHR

```php
['profesi_id' => 1, 'form_id' => 120, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 2, 'form_id' => 120, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
```

### Validasi

- `jenis_spesimen` `required` dengan dua nilai legacy (huruf kapital + spasi).
- Semua `aks_*` wajib dijawab `Ya`/`Tidak`.
- Bila `jenis_spesimen = SCREENING AKSES AV SHUNT/CIMINO`, blok
  `aks_cdl_*`/`aks_exit_*`/`aks_disfungsi_*`/`aks_pasien_*` dibuang di
  `filteredData()`; bila `SCREENING AKSES VASKULER CDL`, blok
  `aks_area_*`/`aks_steal_*` dibuang. Jangan hanya mengandalkan `disabled` di UI.
- Bila ada salah satu `aks_steal_* = Ya` atau `aks_exit_1/2/3 = Ya`, tampilkan
  peringatan "Terdapat kontraindikasi - konsultasikan ke dokter" (bukan blokir).

### Cetak

`print.blade.php` — dua tabel (A/B dan 1–5) sesuai `jenis_spesimen`, dengan
kolom signature perawat.

---

## 8. Asesmen Pasien Restraint (form 121)

### Rasional / tujuan klinis

Restraint (fiksasi) adalah tindakan **terakhir**. Asesmen ini mendokumentasikan
(1) indikasi klinis berupa perilaku gaduh/gelisah yang berbahaya, (2) order
dokter, (3) kebutuhan restraint non-farmakologis dan farmakologis yang diberikan,
dan (4) kriteria penghentian restraint. Mengikuti legacy, catatan
"apakah salah satu item di atas terpenuhi -> indikasi untuk dilakukan fiksasi"
ditampilkan sebagai teks permanen di bawah panel penilaian.

### Struktur Field

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `tanggal_asesmen` | 155 | Tanggal Observasi | date | ya | |
| `waktu_asesmen` | 156 | Waktu Observasi | time | ya | |
| `gaduh_1` | 720 | Penilaian Gaduh Gelisah & Order Dokter | checkbox | tidak | Melakukan kekerasan pada diri sendiri |
| `gaduh_2` | 720 | Penilaian Gaduh Gelisah & Order Dokter | checkbox | tidak | Melakukan kekerasan pada orang lain |
| `gaduh_3` | 720 | Penilaian Gaduh Gelisah & Order Dokter | checkbox | tidak | Impulsif / menyerang |
| `gaduh_4` | 720 | Penilaian Gaduh Gelisah & Order Dokter | checkbox | tidak | Perilaku tidak kooperatif |
| `gaduh_5` | 720 | Penilaian Gaduh Gelisah & Order Dokter | checkbox | tidak | Gaduh gelisah / amuk |
| `order_dokter` | 720 | Penilaian Gaduh Gelisah & Order Dokter | textarea | ya | Penilaian dan order dokter |
| `nonfarm_1` | 721 | Kebutuhan Restraint Non Farmakologis | checkbox | ya* | Restraint tempat tidur / bedrails |
| `nonfarm_2` | 721 | Kebutuhan Restraint Non Farmakologis | checkbox | ya* | Restraint tangan dan kaki |
| `nonfarm_3` | 721 | Kebutuhan Restraint Non Farmakologis | checkbox | ya* | Restraint tangan |
| `nonfarm_4` | 721 | Kebutuhan Restraint Non Farmakologis | checkbox | ya* | Isolasi |
| `nonfarm_5` | 721 | Kebutuhan Restraint Non Farmakologis | checkbox | ya* | Jaket pengikat |
| `farmak_1` | 722 | Restraint Farmakologis | text | tidak | Nama obat baris dinamis 1..10 |
| `farmak_dosis_1` | 722 | Restraint Farmakologis | text | tidak | Dosis baris dinamis 1..10 |
| `stop_1` | 723 | Kriteria Penghentian Restraint | checkbox | tidak | Orientasi pada orang, tempat, lingkungan, waktu |
| `stop_2` | 723 | Kriteria Penghentian Restraint | checkbox | tidak | Mampu secara verbal melakukan kesepakatan untuk keamanan |
| `stop_3` | 723 | Kriteria Penghentian Restraint | checkbox | tidak | Mampu mengenali/mendiskusikan alternatif pertahanan |
| `stop_4` | 723 | Kriteria Penghentian Restraint | checkbox | tidak | Pasien sadar penuh (GCS 15) |
| `stop_5` | 723 | Kriteria Penghentian Restraint | checkbox | tidak | Perilaku awal tidak ditemukan |
| `stop_6` | 723 | Kriteria Penghentian Restraint | checkbox | tidak | Lainnya (wajib isi `stop_lainnya`) |
| `stop_lainnya` | 723 | Kriteria Penghentian Restraint | textarea | ya* | |
| `status_mental` | 724 | Status Mental | radio | ya | `Kooperatif`/`Gelisah`/`Menyerang`/`Tidak ada respon`/`Lainnya` |
| `status_mental_lainnya` | 724 | Status Mental | text | ya* | Wajib bila `Lainnya` |
| `kesadaran` | 51 | Kesadaran (reuse) | radio | ya | `CM`/`Somnolen`/`Apatis`/`Soporus`/`Koma` |
| `sistolik` | 6 | Tekanan Darah Sistolik (reuse) | number | ya | |
| `diastolik` | 7 | Tekanan Darah Diastolik (reuse) | number | ya | |
| `nadi` | 10 | Nadi (reuse) | number | ya | |
| `pernapasan` | 12 | Pernapasan (reuse) | number | ya | |
| `suhu` | 11 | Suhu (reuse) | number | ya | |
| `tgl_edukasi` | 725 | Informasi & Edukasi Pasien/Keluarga | date | ya | |
| `jam_edukasi` | 725 | Informasi & Edukasi Pasien/Keluarga | time | ya | |
| `nama_petugas` | 725 | Informasi & Edukasi Pasien/Keluarga | text | ya | |
| `nama_keluarga` | 725 | Informasi & Edukasi Pasien/Keluarga | text | ya | |
| `nama_petugas_melepas` | 725 | Informasi & Edukasi Pasien/Keluarga | text | tidak | Wajib bila `tgl_melepas` terisi |
| `tgl_melepas` | 725 | Informasi & Edukasi Pasien/Keluarga | date | tidak | |
| `jam_melepas` | 725 | Informasi & Edukasi Pasien/Keluarga | time | tidak | |

`ya*` = wajib kondisional / minimal satu baris.

### Dashboard & Form Master

```php
// $objeks (720-725)
720 => 'Penilaian Gaduh Gelisah & Order Dokter',
721 => 'Kebutuhan Restraint Non Farmakologis',
722 => 'Restraint Farmakologis',
723 => 'Kriteria Penghentian Restraint',
724 => 'Status Mental',
725 => 'Informasi & Edukasi Pasien/Keluarga',
```

### Mapping

```php
121 => [
    'tanggal_asesmen' => 155,
    'waktu_asesmen'   => 156,

    'gaduh_1'      => 720,  // Melakukan kekerasan pada diri sendiri
    'gaduh_2'      => 720,  // Melakukan kekerasan pada orang lain
    'gaduh_3'      => 720,  // Impulsif / menyerang
    'gaduh_4'      => 720,  // Perilaku tidak kooperatif
    'gaduh_5'      => 720,  // Gaduh gelisah / amuk
    'order_dokter' => 720,  // Penilaian dan order dokter (textarea)

    'nonfarm_1' => 721,  // Restraint tempat tidur / bedrails
    'nonfarm_2' => 721,  // Restraint tangan dan kaki
    'nonfarm_3' => 721,  // Restraint tangan
    'nonfarm_4' => 721,  // Isolasi
    'nonfarm_5' => 721,  // Jaket pengikat

    'farmak_1'       => 722,  // Nama obat baris 1
    'farmak_dosis_1' => 722,  // Dosis baris 1
    // farmak_2..farmak_10 + farmak_dosis_2..10 dibangkitkan di loop (lihat bawah)

    'stop_1'       => 723,  // Orientasi pada orang/tempat/lingkungan/waktu
    'stop_2'       => 723,  // Mampu melakukan kesepakatan keamanan
    'stop_3'       => 723,  // Mampu mengenali alternatif pertahanan
    'stop_4'       => 723,  // Pasien sadar penuh (GCS 15)
    'stop_5'       => 723,  // Perilaku awal tidak ditemukan
    'stop_6'       => 723,  // Lainnya
    'stop_lainnya' => 723,

    'status_mental'        => 724,
    'status_mental_lainnya' => 724,

    // reuse objek tanda vital & kesadaran lintas form
    'kesadaran'  => 51,
    'sistolik'   => 6,
    'diastolik'  => 7,
    'nadi'       => 10,
    'pernapasan' => 12,
    'suhu'       => 11,

    'tgl_edikasi'           => 725,
    'jam_edukasi'           => 725,
    'nama_petugas'         => 725,
    'nama_keluarga'        => 725,
    'nama_petugas_melepas' => 725,
    'tgl_melepas'          => 725,
    'jam_melepas'          => 725,
],
```

Baris farmakologis dibangkitkan di loop, sama seperti baris obat/BMHP form 10:

```php
$mappingBarisFarmak = [];
for ($baris = 1; $baris <= 10; $baris++) {
    $mappingBarisFarmak['farmak_'.$baris]       = 722;
    $mappingBarisFarmak['farmak_dosis_'.$baris] = 722;
}
$mapping[121] = array_merge($mapping[121], $mappingBarisFarmak);
```

### Akses EHR

```php
['profesi_id' => 1, 'form_id' => 121, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 2, 'form_id' => 121, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
```

> `order_dokter` diisi dokter; perawat boleh mengisi sisanya. Legacy juga
> memperlakukan kolom ini sebagai teks bebas, tanpa gating peran di sisi server.

### Validasi

```php
'order_dokter'   => 'required|string|max:2000',
'nonfarm_1'      => 'nullable|boolean',
'nonfarm_2'      => 'nullable|boolean',
'nonfarm_3'      => 'nullable|boolean',
'nonfarm_4'      => 'nullable|boolean',
'nonfarm_5'      => 'nullable|boolean',
'status_mental'  => 'required|in:Kooperatif,Gelisah,Menyerang,Tidak ada respon,Lainnya',
'status_mental_lainnya' => 'required_if:status_mental,Lainnya|nullable|string|max:150',
'kesadaran'      => 'required|in:CM,Somnolen,Apatis,Soporus,Koma',
'sistolik'       => 'required|integer|min:50|max:300',
'diastolik'      => 'required|integer|min:20|max:200',
'nadi'           => 'required|integer|min:20|max:250',
'pernapasan'     => 'required|integer|min:5|max:80',
'suhu'           => 'required|numeric|min:30|max:45',
'stop_lainnya'   => 'required_if:stop_6,1|nullable|string|max:500',
'tgl_edukasi'    => 'required|date',
'jam_edukasi'    => 'required|date_format:H:i',
'nama_petugas'   => 'required|string|max:150',
'nama_keluarga'  => 'required|string|max:150',
'nama_petugas_melepas' => 'required_if:tgl_melepas,!=null|nullable|string|max:150',
'tgl_melepas'    => 'nullable|date',
'jam_melepas'    => 'nullable|date_format:H:i',
```

Tambahan:

- Minimal satu dari `nonfarm_1..nonfarm_5` atau `farmak_*` wajib terisi (ada
  kebutuhan restraint). Dicek di `withValidator()` atau setelah `validate()`.
- Bila `tgl_melepas` terisi maka `nama_petugas_melepas` wajib, dan sebaliknya.
- Checkbox `gaduh_*` / `stop_*` memakai hidden `value="0"` agar tidak dicentang
  tetap terkirim.

### Cetak

`print.blade.php` — empat panel (Pertimbangan Klinis, Kebutuhan Restraint,
Pengkajian Penghentian Restraint, Informasi & Edukasi) plus tabel observasi
(form 122) bila dokumen dirangkai.

---

## 9. Observasi Restraint (form 122)

### Rasional / tujuan klinis

Observasi berkala (per jam, minimal tiap 2 jam sesuai kebijakan) selama restraint
diberikan, untuk (1) memantau kondisi fisik, (2) mendeteksi cedera/luka akibat
fiksasi, dan (3) mendokumentasikan pelepasan restraint. Ini form **time-series**:
satu baris `emr` berisi banyak baris observasi, masing-masing dengan
`flag_abnormal` sebagai indeks ronde.

### Struktur Field

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `waktu_observasi` | 156 (reuse) | Waktu Observasi | time | ya | Legacy `jam` |
| `sistolik` | 6 (reuse) | Tekanan Darah Sistolik | number | ya | |
| `diastolik` | 7 (reuse) | Tekanan Darah Diastolik | number | ya | |
| `nadi` | 10 (reuse) | Nadi | number | ya | |
| `pernapasan` | 12 (reuse) | Pernapasan | number | ya | |
| `suhu` | 11 (reuse) | Suhu | number | ya | |
| `kognitif` | 726 | Fungsi Kognitif | text | ya | |
| `luka` | 727 | Tanda Cedera / Luka | text | ya | |
| `asupan_makanan` | 728 | Asupan Makanan, Minuman & Posisi Fiksasi | text | ya | |
| `posisi_fiksasi` | 728 | Asupan Makanan, Minuman & Posisi Fiksasi | text | ya | |
| `panses_ec` | 729 | PANSS-EC (Dokter) | textarea | tidak | Diisi dokter |

### Dashboard & Form Master

```php
// $objeks (726-729)
726 => 'Fungsi Kognitif',
727 => 'Tanda Cedera / Luka',
728 => 'Asupan Makanan, Minuman & Posisi Fiksasi',
729 => 'PANSS-EC (Dokter)',
```

### Mapping — dan mekanisme `flag_abnormal` untuk time-series

Time-series tidak bisa memakai pola suffiks `vak_1`, `vak_2`, ... tanpa batas
jumlah baris (dalam legacy bisa 48 baris per hari selama beberapa hari). Legacy
memakai kolom `emr_detail.flag_abnormal` sebagai **indeks ronde**: setiap baris
observasi adalah satu baris `emr_detail` dengan `variabel` yang sama, tetapi
`flag_abnormal` berbeda (1, 2, 3, ...).

Bentuk datanya:

```
emr_id | variabel      | value        | flag_abnormal
-------+---------------+--------------+--------------
  9001 | kognitif      | Tenang       | 1
  9001 | kognitif      | Bingung      | 2
  9001 | kognitif      | Tenang       | 3
  9001 | luka          | -            | 1
  9001 | luka          | Luka lecet   | 2
  9001 | luka          | -            | 3
```

Kolom `flag_abnormal` **sudah ada** di `emr_detail`
(migration `2026_08_28_000030_create_emr_detail_table.php`, tipe
`smallInteger nullable`). Tidak ada kolom baru — kita hanya memakai ulang kolom
itu dengan semantik berbeda dari tabel Order Lab/Rad (yang memakai `flag_abnormal`
sebagai penanda abnormal 0/1 di tabel **yang lain**, bukan di `emr_detail`).

Mapping statis (satu baris di `objek_form_control` per variabel; indeks ronde
tidak ada di mapping karena dibuat saat penyimpanan):

```php
122 => [
    'waktu_observasi' => 156,  // reuse, diulang per ronde lewat flag_abnormal
    'sistolik'        => 6,    // reuse
    'diastolik'       => 7,    // reuse
    'nadi'            => 10,   // reuse
    'pernapasan'      => 12,   // reuse
    'suhu'            => 11,   // reuse
    'kognitif'        => 726,
    'luka'            => 727,
    'asupan_makanan'  => 728,
    'posisi_fiksasi'  => 728,
    'panses_ec'       => 729,
],
```

Penyimpanan tiap ronde memakai `DB::table('emr_detail')->insert()` langsung
(keluar dari `EmrHelper::insert()` yang hanya menerima array datar):

```php
$variabelForm = array_keys(EmrHelper::objekMap($formId));

$rondaBerikut = ((int) DB::table('emr_detail')
        ->where('emr_id', $emrId)
        ->where(function ($q) { $q->whereNull('status_batal')->orWhere('status_batal', 0); })
        ->max('flag_abnormal')) + 1;

$baris = [];
for ($ronde = 1; $ronde <= $jumlahRonde; $ronde++) {
    foreach ($variabelForm as $variabel) {
        $nilai = $data['ronde'][$ronde][$variabel] ?? null;
        if ($nilai === null || $nilai === '') {
            continue;
        }
        $baris[] = [
            'emr_id'        => $emrId,
            'objek_id'      => EmrHelper::objekId($formId, $variabel),
            'variabel'      => $variabel,
            'value'         => $nilai,
            'flag_abnormal' => $rondaBerikut + $ronde - 1,
            'input_time'    => now(),
            'input_user_id' => Auth::id(),
            'status_batal'  => 0,
        ];
    }
}
DB::table('emr_detail')->insert($baris);
```

Pembacaan wajib pakai grouping. **Jangan** pakai `EmrHelper::emrDetailByVariabel()`
karena `pluck('value','variabel')` akan menimpa semua ronde kecuali ronde terakhir:

```php
$detail = DB::table('emr_detail')
    ->where('emr_id', $emrId)
    ->where(function ($q) { $q->whereNull('status_batal')->orWhere('status_batal', 0); })
    ->whereNotNull('flag_abnormal')
    ->orderBy('flag_abnormal')
    ->orderBy('objek_id')
    ->get();

$rondellan = $detail->groupBy('flag_abnormal')->map(function ($rows) {
    return $rows->pluck('value', 'variabel');
})->all();

// $rondellan[1] = ['waktu_observasi' => '08:00', 'kognitif' => 'Tenang', ...]
// $rondellan[2] = ['waktu_observasi' => '10:00', 'kognitif' => 'Bingung', ...]
//
// Kolom "Pencatat" diambil dari emr_detail.input_user_id tiap baris.
```


Perilaku update: `update()` melakukan soft-delete seluruh `emr_detail` form 122
lalu menulis ulang semua ronde dari payload (`ronde[1][waktu_observasi]`,
`ronde[1][kognitif]`, ...). `destroy()` memakai `EmrHelper::delete()` yang sudah
menangani soft-delete massal.

Tampilan: tabel dengan kolom
`No | Jam | TD | Nadi | RR | Suhu | Fungsi Kognitif | Tanda Cedera/Luka |
Asupan | Asupan Makanan, Minuman & Posisi Fiksasi | Pencatat | PANSS-EC | Aksi`.
Kolom **Pencatat** diambil dari `emr_detail.input_user_id` tiap baris (join
`users`) — meniru kolom `Pencatat` di legacy.

### Akses EHR

```php
['profesi_id' => 1, 'form_id' => 122, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 2, 'form_id' => 122, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
```

### Validasi

Validasi dilakukan **per ronde** di dalam loop:

```php
for ($ronde = 1; $ronde <= $jumlah; $ronde++) {
    $v = Validator::make($request->all(), [
        "ronde.$ronde.waktu_observasi" => 'required|date_format:H:i',
        "ronde.$ronde.sistolik"         => 'required|integer|min:50|max:300',
        "ronde.$ronde.diastolik"        => 'required|integer|min:20|max:200',
        "ronde.$ronde.nadi"             => 'required|integer|min:20|max:250',
        "ronde.$ronde.pernapasan"       => 'required|integer|min:5|max:80',
        "ronde.$ronde.suhu"             => 'required|numeric|min:30|max:45',
        "ronde.$ronde.kognitif"         => 'required|string|max:500',
        "ronde.$ronde.luka"             => 'required|string|max:500',
        "ronde.$ronde.asupan_makanan"   => 'required|string|max:500',
        "ronde.$ronde.posisi_fiksasi"   => 'required|string|max:500',
        "ronde.$ronde.panses_ec"        => 'nullable|string|max:1000',
    ]);

    if ($v->fails()) {
        return back()->withErrors($v)->withInput();
    }
}
```

- `jumlah_ronde` `required|integer|min:1|max:48`.
- Ronde bersifat **monoton**: jangan menimpa ronde lama saat update — soft-delete
  dulu, baru tulis ulang (lihat catatan risiko).

### Cetak

`print.blade.php` — tabel observasi lengkap dengan kolom Pencatat, diurutkan
`flag_abnormal`.

---

## Implementasi

- [ ] `database/seeders/EmrMasterSeeder.php` — menu 17, sub 333–339, form 116–122,
      objek 691–729, mapping (termasuk loop untuk baris `farmak_*`),
      `akses_ehr`, `EmrHelper::backfillObjekId(116..122)`
- [ ] `app/Http/Controllers/EMR/SurveilansCvcPicc/SurveilansCvcPiccController.php`
- [ ] `resources/views/moduls/EMR/SurveilansCvcPicc/{index,print}.blade.php`
- [ ] `app/Http/Controllers/EMR/SurveilansIvCatheter/SurveilansIvCatheterController.php`
- [ ] `resources/views/moduls/EMR/SurveilansIvCatheter/{index,print}.blade.php`
- [ ] `app/Http/Controllers/EMR/SurveilansKateterUrine/SurveilansKateterUrineController.php`
- [ ] `resources/views/moduls/EMR/SurveilansKateterUrine/{index,print}.blade.php`
- [ ] `app/Http/Controllers/EMR/SurveilansTirahBaring/SurveilansTirahBaringController.php`
- [ ] `resources/views/moduls/EMR/SurveilansTirahBaring/{index,print}.blade.php`
- [ ] `app/Http/Controllers/EMR/ScreeningAkses/ScreeningAksesController.php`
- [ ] `resources/views/moduls/EMR/ScreeningAkses/{index,print}.blade.php`
- [ ] `app/Http/Controllers/EMR/AsesmenRestraint/AsesmenRestraintController.php`
- [ ] `resources/views/moduls/EMR/AsesmenRestraint/{index,print}.blade.php`
- [ ] `app/Http/Controllers/EMR/ObservasiRestraint/ObservasiRestraintController.php`
- [ ] `resources/views/moduls/EMR/ObservasiRestraint/{index,print}.blade.php`
- [ ] `app/Helpers/EmrHelper.php` — tambahkan `emrDetailRonde(int $emrId)`
      (grouping per `flag_abnormal`) dan `rondeBerikut(int $emrId)`
- [ ] `app/Helpers/SurveilansHelper.php` — agregator CLABSI / CA-UTI / HAP
      (untuk cetak & laporan IPCN)
- [ ] `app/Helpers/SelectOption.php` — key `jenis_screening_akses`, `opsi_pivas`,
      `status_kateter_urine`, `ukuran_kateter_urine`
- [ ] `routes/web.php` — route print manual untuk 7 form (`->whereNumber('emr_id')`)
- [ ] `AGENTS.md` — entri form 116–122

Tidak ada migration baru: kolom `flag_abnormal` sudah ada di `emr_detail`.

---

## Catatan & Risiko

| Risiko | Mitigasi |
|---|---|
| `Str::slug('Surveilans CVC/PICC','_')` = `surveilans_cvcpicc` (bukan `surveilans_cvc-picc` — `/` dihapus, bukan jadi separator) | Sub-menu ditulis "Surveilans CVC PICC" (tanpa `/`) → `surveilans_cvc_picc` |
| `Str::slug('Asesmen Restraint','_')` = `asesmen_restraint` ≠ `form.slug` `asesmen_restraint` | **Penyimpangan disengaja** (konvensi proyek ejaan `assesmen`, ledger §7). `nama_sub_menu` tetap `Asesmen Restraint`; `form.slug` di-override manual di seeder |
| Satu `variabel` untuk banyak butir checklist (`bsi_*`, `isk_*`, `hap_*`) | **Terlarang.** WAJIB suffiks, persis pola butir Bundle VAP form 14 |
| `flag_abnormal` disalahpahami sebagai "flag abnormal" | Dokumen ini mendefinisikannya sebagai indeks ronde untuk time-series form 122. Kode Order Lab/Rad memakai kolom bernama sama di tabel **berbeda** (`order_laboratorium_detail`, `order_radiologi_detail`), jadi tidak saling memengaruhi |
| `emrDetailByVariabel()` menimpa semua ronde observasi | Untuk form 122 **wajib** grouping manual per `flag_abnormal` |
| `max(flag_abnormal)` dihitung dari data yang sudah di-soft-delete | Soft-delete dulu, baru `max()` dari baris **aktif**; kalau tidak, indeks ronde melompat/duplikat |
| Update form 122 membaca indeks ronde lama | Tulis ulang dari `rondeBerikut = max(flag_abnormal aktif) + 1` setelah soft-delete total |
| Exactly-one untuk lokasi / jenis kateter / nomor kateter | Validasi di level form (`withValidator()`), jangan hanya radio UI |
| Opsi "Lainnya" pada checkbox tidak punya isian | `lokasi_lainnya`, `cath_lainnya`, `jenis_kath_lainnya`, `stop_lainnya` — wajib diisi bila "Lainnya" dicentang |
| Sanitarian (IPCN) dapat menghapus data surveilans | `akses_delete = 0` untuk profesi 9 pada form 116–119 |
| `Str::studly('screening_akses')` = `ScreeningAkses` | Folder controller/view WAJIB `ScreeningAkses` |
| Jumlah baris `objek_form_control` membludak | Grouping per objek (seperti desain di atas) menjaga jumlah objek tetap terkendali |
| Cetak tabel CVC sangat lebar (21 kolom) | Gunakan `@page { size: landscape; }` pada CSS print |
| Salah ejaan key pada baris `akses_ehr` saat copy-paste | Selalu gunakan `akses_create/read/update/delete`; kolom lain tidak ada di tabel |
| Ronde observasi restraint banyak (48/hari x beberapa hari) | Pertahankan soft-delete + re-insert dalam transaksi `EmrHelper` |