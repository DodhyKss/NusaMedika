# Konsep Form EMR: Psikososial & Kerohanian

Rancangan & catatan implementasi 4 form EMR domain **Psikososial dan Kerohanian**
(form 112–115). Dokumen ini adalah konsep — belum ada kode yang ditulis.
Semua aturan wajib mengikuti [`PANDUAN_IMPLEMENTASI_FORM_EMR.md`](PANDUAN_IMPLEMENTASI_FORM_EMR.md).

**Status:** Rancangan / Konsep — belum diimplementasikan
**Tanggal:** 2026-10-10
**Sumber legacy:** `/simrs_tenriawaru/FE/lib/modul/`
**Alokasi:** objek_id **671–690**, `dashboard_menu_id = 16`, sub-menu **313–316**
(band objek §1 & band sub §3 `ALOKASI_ID_GLOBAL.md`; tidak ada `sub_extra`)

---

## 1. Ringkasan

| form_id | nama_form | slug | id_dash_menu | ri | rj | igd | mcu |
|---|---|---|---|---|---|---|---|
| 112 | Asesmen Psikologi Pasien & Keluarga | `assesmen_psikologi` | `16.313` | 1 | 1 | 1 | 0 |
| 113 | Asesmen Spiritual | `assesmen_spiritual` | `16.314` | 1 | 1 | 1 | 0 |
| 114 | Permintaan Pelayanan Kerohanian | `permintaan_kerohanian` | `16.315` | 1 | 1 | 1 | 0 |
| 115 | Bukti Pelayanan Kerohanian | `bukti_kerohanian` | `16.316` | 1 | 1 | 1 | 0 |

Menu **16 "Psikososial & Kerohanian"** adalah menu dashboard EMR baru (menu yang
sudah ada: 1 Catatan Medis, 2 Catatan Keperawatan, 3 Resep (soft-delete), 4 Order,
5 Formulir). `dashboard_menu_sub_id` bersifat **global**, jadi sub-menu baru
memakai **313, 314, 315, 316** — band sub dokumen ini dimulai dari 313 sesuai
`ALOKASI_ID_GLOBAL.md` §3 (angka 1–12 milik menu 1–5 dan tidak boleh dipakai ulang).

> **Rantai `slug` (PANDUAN §2.1).** Sub-menu tanpa extra memakai
> `nama_sub_menu` sebagai slug URL, jadi `Str::slug(nama_sub_menu, '_')`
> **wajib sama persis** dengan `form.slug`:
>
> | sub | `nama_sub_menu` | `Str::slug(...)` **harus** | `form.slug` (dikunci) |
> |---|---|---|---|
> | 313 | `Asesmen Psikologi` | `asesmen_psikologi` | **`assesmen_psikologi`** |
> | 314 | `Asesmen Spiritual` | `asesmen_spiritual` | **`assesmen_spiritual`** |
> | 315 | `Permintaan Kerohanian` | `permintaan_kerohanian` | `permintaan_kerohanian` |
> | 316 | `Bukti Kerohanian` | `bukti_kerohanian` | `bukti_kerohanian` |
>
> `nama_form` boleh lebih lengkap (mis. "Asesmen Psikologi Pasien & Keluarga")
> karena hanya `nama_sub_menu` yang jadi `form_name` di dashboard.
>
> ### ⚠️ Penyimpangan yang disengaja: ejaan `assesmen` (dua s)
>
> Kolom "harus" di atas sengaja **berbeda** dari `form.slug` untuk form 112 & 113.
> `Str::slug()` bersifat literal: `Str::slug('Asesmen Psikologi','_')` →
> `asesmen_psikologi` (**satu** s), sedangkan `form.slug` memakai
> `assesmen_psikologi` (**dua** s).
>
> Penyimpangan ini **disengaja dan terkunci** oleh
> `ALOKASI_ID_GLOBAL.md` §7: ejaan `assesmen` sudah menjadi **konvensi proyek**
> — lihat form 11 yang sudah berjalan dengan slug
> `assesmen_awal_medis_rawat_jalan`. Menukarnya menjadi `asesmen_*` akan
> membuat slug form baru tidak konsisten dengan kode yang sudah berjalan.
>
> Konsekuensi yang harus disadari: nama sub menu **tetap ditulis**
> `Asesmen Psikologi` (satu s) supaya label di dashboard terbaca benar; yang
> di-override manual di seeder hanya nilai `form.slug`.
> Konvensi sama berlaku pada `KONSEP_SURVEILANS_INFEKSI.md` (form 121
> `asesmen_restraint`) dan `KONSEP_ONKOLOGI_TRANSFUSI.md` (form 109
> `assesmen_transfusi`).

---

## 2. Sumber Referensi Legacy

| Berkas legacy | Dipakai untuk |
|---|---|
| `/simrs_tenriawaru/FE/lib/modul/assesmen_psikologi_pasien.php` | Struktur 3 section asesmen psikologi (442 baris) |
| `/simrs_tenriawaru/FE/lib/modul/assesmen_spiritual.php` | Manajemen gejala + orientasi & kebutuhan spiritual (244 baris) |
| `/simrs_tenriawaru/FE/lib/modul/permintaan_pelayanan_kerohanian.php` | Formulir permintaan (265 baris) |
| `/simrs_tenriawaru/FE/lib/modul/bukti_pelayanan_kerohanian.php` | Formulir bukti pelayanan (370 baris) |
| `/simrs_tenriawaru/FE/lib/modul/restraint_cak.php` | Referensi struktur pengkajian restraint (dipakai form 121 di `KONSEP_SURVEILANS_INFEKSI.md`) |

Struktur yang benar-benar diambil dari legacy, bukan generik:

- `assesmen_psikologi_pasien.php` → tiga section:
  **"Status psikologis pasien dan keluarga"** (orang yang bisa dihubungi /
  rencana perawatan selanjutnya / lingkungan rumah), **"Reaksi pasien atas
  penyakitnya"** (asesmen informasi 8 item + masalah kebutuhan pribadi 2 item),
  **"Reaksi keluarga atas penyakit pasien"** (asesmen informasi 10 item +
  masalah keperawatan 3 item).
- `assesmen_spiritual.php` → **"Managemen gejala saat ini dan respon pasien"**
  (8 masalah keperawatan), **"Orientasi spiritual pasien dan keluarga"** (perlu
  pelayanan spiritual Ya/Tidak + petugas), **"Urusan dan kebutuhan spiritual"**
  (doa / bimbingan rohani / pendamping rohani, masing-masing Ya/Tidak + petugas).
- `permintaan_pelayanan_kerohanian.php` → blok **"Saya Yang Bertanggung Jawab
  Dibawah Ini"**: Nama, Umur, Jenis Kelamin, Agama. Sisanya (Nama/Umur/JK/Agama/
  No. RM/Ruangannya) di-render read-only dari master `pasien` + `registrasi_detail`
  + `bagian`.
- `bukti_pelayanan_kerohanian.php` → blok **"Saya Anggota Kerohanian"** (Nama,
  Umur, JK, Agama), blok **"Pada"** (Hari / Tanggal / Jam / Tempat), dan blok
  identitas pasien read-only yang sama.

---

## 3. Asesmen Psikologi Pasien & Keluarga (form 112)

### Rasional / tujuan klinis

Asesmen psikososial dilakukan sejak pasien masuk. Tujuannya memetakan reaksi
emosional pasien terhadap penyakitnya (menyangkal, menangis, marah, takut, rasa
bersalah), masalah kebutuhan pribadi (cemas terhadap kematian, distres
spiritual), serta reaksi keluarga — yang sering menjadi hambatan utama dalam
perencanaan asuhan keperawatan. Hasilnya dipakai untuk menentukan intervensi
psikososial dan kebutuhan informasi/edukasi.

### Struktur Field

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `hubungan_keluarga` | 671 | Ketersediaan Orang yang Dapat Dihubungi | radio `Tidak` / `Ya` | ya | Legacy: "Apakah ada orang yang bisa dihubungi saat ini?" |
| `nama_keluarga` | 672 | Nama Keluarga yang Dihubungi | text | ya* | Wajib bila `hubungan_keluarga = Ya` |
| `status_keluarga` | 673 | Status/Hubungan Keluarga | text | ya* | "Suami", "Ibu", "Anak", … |
| `alamat_keluarga` | 674 | Alamat Keluarga | text | tidak | |
| `hp_keluarga` | 675 | No. HP/Telp. Keluarga | text (maks 13) | ya* | |
| `rencana_rawat` | 676 | Rencana Perawatan Selanjutnya | select | ya | `Tetap Dirawat di RS` / `Dirawat di Rumah` |
| `lingkungan` | 677 | Persiapan Lingkungan Rumah | radio `Tidak` / `Ya` | ya | Legacy: "Apakah lingkungan rumah sudah disiapkan?" |
| `perawat_rumah` | 678 | Nama Perawat di Rumah | text | ya* | Wajib bila `lingkungan = Ya` |
| `fasilitasi_rs` | 679 | Perlu Fasilitasi Rumah Sakit | radio `Tidak` / `Ya` | ya | Tampil hanya bila `lingkungan = Tidak` |
| `psi_info_1` | 680 | Reaksi Pasien atas Penyakitnya | checkbox | tidak | Menyangkal |
| `psi_info_2` | 680 | Reaksi Pasien atas Penyakitnya | checkbox | tidak | Sedih / Menangis |
| `psi_info_3` | 680 | Reaksi Pasien atas Penyakitnya | checkbox | tidak | Marah |
| `psi_info_4` | 680 | Reaksi Pasien atas Penyakitnya | checkbox | tidak | Rasa Bersalah |
| `psi_info_5` | 680 | Reaksi Pasien atas Penyakitnya | checkbox | tidak | Nafas Melalui Mulut |
| `psi_info_6` | 680 | Reaksi Pasien atas Penyakitnya | checkbox | tidak | Takut |
| `psi_info_7` | 680 | Reaksi Pasien atas Penyakitnya | checkbox | tidak | Ketidakberdayaan |
| `psi_info_8` | 680 | Reaksi Pasien atas Penyakitnya | checkbox | tidak | Tidak Dapat Dinilai |
| `psi_masalah_1` | 681 | Kebutuhan Pribadi & Reaksi Keluarga | checkbox | tidak | Ansietas terhadap kematian |
| `psi_masalah_2` | 681 | Kebutuhan Pribadi & Reaksi Keluarga | checkbox | tidak | Distres Spiritual |
| `psi_keluarga_1` | 681 | Kebutuhan Pribadi & Reaksi Keluarga | checkbox | tidak | Problem kebiasaan pola komunikasi |
| `psi_keluarga_2` | 681 | Kebutuhan Pribadi & Reaksi Keluarga | checkbox | tidak | Rasa Bersalah |
| `psi_keluarga_3` | 681 | Kebutuhan Pribadi & Reaksi Keluarga | checkbox | tidak | Penurunan Konsentrasi |
| `psi_keluarga_4` | 681 | Kebutuhan Pribadi & Reaksi Keluarga | checkbox | tidak | Letih / Lelah |
| `psi_keluarga_5` | 681 | Kebutuhan Pribadi & Reaksi Keluarga | checkbox | tidak | Ketidakmampuan memenuhi peran yang diharapkan |
| `psi_keluarga_6` | 681 | Kebutuhan Pribadi & Reaksi Keluarga | checkbox | tidak | Gangguan Tidur |
| `psi_keluarga_7` | 681 | Kebutuhan Pribadi & Reaksi Keluarga | checkbox | tidak | Keluarga kurang berkomunikasi dengan pasien |
| `psi_keluarga_8` | 681 | Kebutuhan Pribadi & Reaksi Keluarga | checkbox | tidak | Sedih / Menangis |
| `psi_keluarga_9` | 681 | Kebutuhan Pribadi & Reaksi Keluarga | checkbox | tidak | Keluarga kurang berpartisipasi dalam keputusan perawatan |
| `psi_keluarga_10` | 681 | Kebutuhan Pribadi & Reaksi Keluarga | checkbox | tidak | Marah |
| `psi_mk_keluarga_1` | 681 | Kebutuhan Pribadi & Reaksi Keluarga | checkbox | tidak | Koping individu tidak efektif |
| `psi_mk_keluarga_2` | 681 | Kebutuhan Pribadi & Reaksi Keluarga | checkbox | tidak | Perubahan proses keluarga |
| `psi_mk_keluarga_3` | 681 | Kebutuhan Pribadi & Reaksi Keluarga | checkbox | tidak | Distres Spiritual |
| `tanggal_asesmen` | 155 (reuse) | Tanggal Observasi | date | ya | Reuse objek lintas form |
| `waktu_asesmen` | 156 (reuse) | Waktu Observasi | time | ya | Reuse objek lintas form |

`*` = wajib **kondisional** (`required_if`).

> **Mengapa 8 item asesmen informasi dipetakan ke SATU objek (680)?** Legacy
> memetakan seluruh item checkbox ke `objek_id[assesmen_informasi] = 1095` —
> satu objek untuk 8 item. Pola yang sama disalin. `objek_form_control`
> mengizinkan banyak variabel → satu objek; yang **wajib unik** adalah
> `variabel` (PANDUAN §2.2), bukan `objek_id`. Bandingkan form 3:
> `'vaksin_covid' => 62, 'tanggal_covid_1' => 62, 'tanggal_covid_2' => 62`.
>
> **Yang dilarang tetap sama:** satu `variabel` dipakai untuk banyak item —
> itu yang membuat `emrDetailByVariabel()` menimpa sendiri.

### Dashboard & Form Master

```php
// $menus di EmrMasterSeeder
['dashboard_menu_id' => 16, 'nama_menu' => 'Psikososial & Kerohanian'],

// $subMenus
['dashboard_menu_sub_id' => 313, 'dashboard_menu_id' => 16, 'nama_sub_menu' => 'Asesmen Psikologi'],
['dashboard_menu_sub_id' => 314, 'dashboard_menu_id' => 16, 'nama_sub_menu' => 'Asesmen Spiritual'],
['dashboard_menu_sub_id' => 315, 'dashboard_menu_id' => 16, 'nama_sub_menu' => 'Permintaan Kerohanian'],
['dashboard_menu_sub_id' => 316, 'dashboard_menu_id' => 16, 'nama_sub_menu' => 'Bukti Kerohanian'],

// $forms
['form_id' => 112, 'nama_form' => 'Asesmen Psikologi Pasien & Keluarga', 'slug' => 'assesmen_psikologi', 'id_dash_menu' => '16.313', 'ri' => 1, 'rj' => 1, 'igd' => 1, 'mcu' => 0],
['form_id' => 113, 'nama_form' => 'Asesmen Spiritual', 'slug' => 'assesmen_spiritual', 'id_dash_menu' => '16.314', 'ri' => 1, 'rj' => 1, 'igd' => 1, 'mcu' => 0],
['form_id' => 114, 'nama_form' => 'Permintaan Pelayanan Kerohanian', 'slug' => 'permintaan_kerohanian', 'id_dash_menu' => '16.315', 'ri' => 1, 'rj' => 1, 'igd' => 1, 'mcu' => 0],
['form_id' => 115, 'nama_form' => 'Bukti Pelayanan Kerohanian', 'slug' => 'bukti_kerohanian', 'id_dash_menu' => '16.316', 'ri' => 1, 'rj' => 1, 'igd' => 1, 'mcu' => 0],

// $objeks (671-681)
671 => 'Ketersediaan Orang yang Dapat Dihubungi',
672 => 'Nama Keluarga yang Dihubungi',
673 => 'Status/Hubungan Keluarga',
674 => 'Alamat Keluarga',
675 => 'No. HP/Telp. Keluarga',
676 => 'Rencana Perawatan Selanjutnya',
677 => 'Persiapan Lingkungan Rumah',
678 => 'Nama Perawat di Rumah',
679 => 'Perlu Fasilitasi Rumah Sakit',
680 => 'Reaksi Pasien atas Penyakitnya',
681 => 'Kebutuhan Pribadi & Reaksi Keluarga',
```

### Mapping

```php
112 => [
    'hubungan_keluarga' => 671,   // Tidak / Ya
    'nama_keluarga'     => 672,   // text
    'status_keluarga'   => 673,   // text
    'alamat_keluarga'   => 674,   // text
    'hp_keluarga'       => 675,   // text, maks 13 karakter
    'rencana_rawat'     => 676,   // Tetap Dirawat di RS / Dirawat di Rumah
    'lingkungan'        => 677,   // Tidak / Ya
    'perawat_rumah'     => 678,   // text
    'fasilitasi_rs'     => 679,   // Tidak / Ya

    // Reaksi pasien atas penyakitnya — 8 item asesmen informasi.
    // WAJIB bersuffix: EmrHelper::emrDetailByVariabel() melakukan
    // pluck('value','variabel'), sehingga 8 baris dengan variabel sama akan
    // saling menimpa dan hanya butir terakhir yang terbaca
    // (pola butir Bundle VAP, form 14).
    'psi_info_1' => 680,  // Menyangkal
    'psi_info_2' => 680,  // Sedih / Menangis
    'psi_info_3' => 680,  // Marah
    'psi_info_4' => 680,  // Rasa Bersalah
    'psi_info_5' => 680,  // Nafas Melalui Mulut
    'psi_info_6' => 680,  // Takut
    'psi_info_7' => 680,  // Ketidakberdayaan
    'psi_info_8' => 680,  // Tidak Dapat Dinilai

    // Masalah kebutuhan pribadi + reaksi keluarga + masalah keperawatan keluarga
    'psi_masalah_1'     => 681,  // Ansietas terhadap kematian
    'psi_masalah_2'     => 681,  // Distres spiritual
    'psi_keluarga_1'    => 681,  // Problem komunikasi keluarga
    'psi_keluarga_2'    => 681,  // Rasa bersalah
    'psi_keluarga_3'    => 681,  // Penurunan konsentrasi
    'psi_keluarga_4'    => 681,  // Letih/lelah
    'psi_keluarga_5'    => 681,  // Ketidakmampuan memenuhi peran
    'psi_keluarga_6'    => 681,  // Gangguan tidur
    'psi_keluarga_7'    => 681,  // Kurang berkomunikasi
    'psi_keluarga_8'    => 681,  // Sedih/menangis
    'psi_keluarga_9'    => 681,  // Kurang berpartisipasi mengambil keputusan
    'psi_keluarga_10'   => 681,  // Marah
    'psi_mk_keluarga_1' => 681,  // Koping individu tidak efektif
    'psi_mk_keluarga_2' => 681,  // Perubahan proses keluarga
    'psi_mk_keluarga_3' => 681,  // Distres spiritual keluarga

    'tanggal_asesmen' => 155,     // reuse objek "Tanggal Observasi"
    'waktu_asesmen'   => 156,     // reuse objek "Waktu Observasi"
],
```

### Akses EHR

```php
// Dokter (1)
['profesi_id' => 1, 'form_id' => 112, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
// Perawat (2)
['profesi_id' => 2, 'form_id' => 112, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
// Perekam Medis (7) — hanya baca
['profesi_id' => 7, 'form_id' => 112, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
```

### Validasi

```php
private function validated(Request $request): array
{
    return array_merge([
        'nama_keluarga'   => null,
        'status_keluarga' => null,
        'alamat_keluarga' => null,
        'hp_keluarga'     => null,
        'perawat_rumah'  => null,
        'fasilitasi_rs'   => null,
    ], $request->validate([
        'hubungan_keluarga' => 'required|in:Tidak,Ya',
        'nama_keluarga'     => 'required_if:hubungan_keluarga,Ya|nullable|string|max:150',
        'status_keluarga'   => 'required_if:hubungan_keluarga,Ya|nullable|string|max:100',
        'alamat_keluarga'   => 'nullable|string|max:500',
        'hp_keluarga'       => 'required_if:hubungan_keluarga,Ya|nullable|string|max:13',
        'rencana_rawat'     => 'required|in:Tetap Dirawat di RS,Dirawat di Rumah',
        'lingkungan'        => 'required|in:Tidak,Ya',
        'perawat_rumah'     => 'required_if:lingkungan,Ya|nullable|string|max:150',
        'fasilitasi_rs'     => 'required_if:lingkungan,Tidak|nullable|in:Tidak,Ya',
        'tanggal_asesmen'   => 'required|date',
        'waktu_asesmen'     => 'required|date_format:H:i',
    ]));
}
```

Aturan tambahan:

- Checkbox `psi_*` **tidak** wajib minimal satu — pasien boleh tidak menunjukkan
  gejala psikologis apa pun.
- Tiap checkbox wajib berpasangan dengan `<input type="hidden" name="…"
  value="0">` supaya key tetap terkirim saat tidak dicentang. Tanpa itu,
  `filteredData()` membuang key dan baris `emr_detail` lama tidak pernah
  ter-update saat edit.
- Field conditional (`perawat_rumah`, `fasilitasi_rs`, `nama_keluarga`, …)
  **di-`disabled`** lewat JS bila radio = `Tidak`, dan tetap dibuang dari
  payload di `filteredData()` agar tidak ada baris yatim.
- `waktu_asesmen` dari DB bisa `H:i:00` → potong `substr(…, 0, 5)` di blade.
- Tampil/sembunyi wajib lewat inline `style.display` **dan** lepas/pasang
  class `hidden` (lihat AGENTS.md — gotcha Tailwind v4 `.hidden` vs
  `inline-flex`).

### Cetak

`resources/views/moduls/EMR/AsesmenPsikologi/print.blade.php`, route manual
`GET /emr/assesmen_psikologi/print/{emr_id}` (pola `Soap` / `Konsultasi`).
Isi: kop logo, identitas pasien, tiga section asesmen, nama & tanda tangan
asesor.

---

## 4. Asesmen Spiritual (form 113)

### Rasional / tujuan klinis

Form ini menjawab dua hal yang sering terlewat di pengkajian keperawatan rutin:
(1) masalah/gejala yang sedang aktif dan perlu complaining care, dan (2) apakah
pasien membutuhkan layanan spiritual, lalu bentuk kerohanian apa yang dibutuhkan
(doa, bimbingan rohani, pendamping rohani). Hasilnya menjadi dasar permintaan
layanan kerohanian (form 114).

### Struktur Field

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `masalah_1` | 682 | Masalah Keperawatan (Manajemen Gejala) | checkbox | tidak | Bersihan Jalan Tidak Efektif |
| `masalah_2` | 682 | Masalah Keperawatan (Manajemen Gejala) | checkbox | tidak | Pola Nafas Tidak Efektif |
| `masalah_3` | 682 | Masalah Keperawatan (Manajemen Gejala) | checkbox | tidak | Mual |
| `masalah_4` | 682 | Masalah Keperawatan (Manajemen Gejala) | checkbox | tidak | Perubahan Persepsi Sensori |
| `masalah_5` | 682 | Masalah Keperawatan (Manajemen Gejala) | checkbox | tidak | Konstipation |
| `masalah_6` | 682 | Masalah Keperawatan (Manajemen Gejala) | checkbox | tidak | Defisit Perawatan Diri |
| `masalah_7` | 682 | Masalah Keperawatan (Manajemen Gejala) | checkbox | tidak | Nyeri Akut |
| `masalah_8` | 682 | Masalah Keperawatan (Manajemen Gejala) | checkbox | tidak | Nyeri Kronis |
| `spiritual` | 683 | Perlu Pelayanan & Kebutuhan Kerohanian | radio `Tidak` / `Ya` | ya | "Apakah perlu pelayanan spiritual?" |
| `rohani_1` | 683 | Perlu Pelayanan & Kebutuhan Kerohanian | radio `Tidak` / `Ya` | ya | Perlu didoakan |
| `rohani_2` | 683 | Perlu Pelayanan & Kebutuhan Kerohanian | radio `Tidak` / `Ya` | ya | Perlu bimbingan rohani |
| `rohani_3` | 683 | Perlu Pelayanan & Kebutuhan Kerohanian | radio `Tidak` / `Ya` | ya | Perlu pendamping rohani |
| `petugas_spiritual` | 684 | Petugas Kerohanian | text | ya* | Wajib bila `spiritual = Ya` |
| `petugas_rohani_1` | 684 | Petugas Kerohanian | text | ya* | Wajib bila `rohani_1 = Yes` |
| `petugas_rohani_2` | 684 | Petugas Kerohanian | text | ya* | Wajib bila `rohani_2 = Yes` |
| `petugas_rohani_3` | 684 | Petugas Kerohanian | text | ya* | Wajib bila `rohani_3 = Yes` |

`ya*` = wajib kondisional.

### Dashboard & Form Master

```php
// $objeks (682-684)
682 => 'Masalah Keperawatan (Manajemen Gejala)',
683 => 'Perlu Pelayanan & Kebutuhan Kerohanian',
684 => 'Petugas Kerohanian',
```

### Mapping

```php
113 => [
    'masalah_1' => 682,  // Bersihan jalan tidak efektif
    'masalah_2' => 682,  // Pola nafas tidak efektif
    'masalah_3' => 682,  // Mual
    'masalah_4' => 682,  // Perubahan persepsi sensori
    'masalah_5' => 682,  // Konstipasi (ejaan legacy)
    'masalah_6' => 682,  // Defisit perawatan diri
    'masalah_7' => 682,  // Nyeri akut
    'masalah_8' => 682,  // Nyeri kronis

    'spiritual' => 683,  // Tidak / Ya
    'rohani_1'  => 683,  // Tidak / Ya  (doa)
    'rohani_2'  => 683,  // Tidak / Ya  (bimbingan rohani)
    'rohani_3'  => 683,  // Tidak / Ya  (pendamping rohani)

    'petugas_spiritual' => 684,
    'petugas_rohani_1'  => 684,
    'petugas_rohani_2'  => 684,
    'petugas_rohani_3'  => 684,
],
```

Istilah legacy adalah **"Konstipasi"** (`value="konstipasi"`) — itu salah ketik
dari "Constipation" / "Konstribusi" pada penulisan medically yang benar. **Tetap
gunakan ejaan legacy `Konstipasi`** supaya migrasi data lama tidak ambigu, dan
catat koreksinya di `AGENTS.md`.

### Akses EHR

```php
['profesi_id' => 1,  'form_id' => 113, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 2,  'form_id' => 113, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 14, 'form_id' => 113, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
```

> **`profesi_id = 14` (Kerohanian) belum ada.** `MasterPegawaiSeeder` saat ini
> mengisi 1–13. Perlu tambahan
> `['profesi_id' => 14, 'nama_profesi' => 'Kerohanian']` **sebelum** form 113–115
> dipakai petugas rohani. Tanpa itu, `AksesEhr::profesiId()` jatuh ke fallback
> `1` (Dokter) dan akses rohani tidak berlaku.
>
> Sesuai `ALOKASI_ID_GLOBAL.md` §5, **14 memang ID Kerohanian yang baru** —
> dipakai oleh dokumen ini dan tidak oleh dokumen lain. Jangan tertukar dengan
> **Apoteker = 4** (sudah ada). ID 3 = Bidan, **bukan** Ahli Gizi (Ahli Gizi
> = 10).

### Validasi

```php
'spiritual' => 'required|in:Tidak,Ya',
'rohani_1'  => 'required|in:Tidak,Ya',
'rohani_2'  => 'required|in:Tidak,Ya',
'rohani_3'  => 'required|in:Tidak,Ya',
'petugas_spiritual' => 'required_if:spiritual,Ya|nullable|string|max:150',
'petugas_rohani_1'  => 'required_if:rohani_1,Ya|nullable|string|max:150',
'petugas_rohani_2'  => 'required_if:rohani_2,Ya|nullable|string|max:150',
'petugas_rohani_3'  => 'required_if:rohani_3,Ya|nullable|string|max:150',
```

Checkbox `masalah_*` memakai pasangan hidden `value="0"` seperti §3.

### Cetak

`print.blade.php` ukuran A4: daftar masalah terpilih, status layanan spiritual
(ya/tidak + petugas), kebutuhan kerohanian (doa / bimbingan / pendamping),
kolom paraf petugas kerohanian.

---

## 5. Permintaan Pelayanan Kerohanian (form 114)

### Rasional / tujuan klinis

Formulir pengajuan yang diisi tenaga klinik di unit pelayanan untuk meminta
petugas kerohanian datang ke pasien. Data identitas pemohon (nama, umur, JK,
agama) diambil dari legacy `permintaan_pelayanan_kerohanian.php`; identitas
pasien **tidak** disimpan karena sudah ada di master — cukup read-only.

### Struktur Field

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `nama_rohani` | 685 | Nama Petugas Kerohanian | text | ya | Legacy: blok "Saya Yang Bertanggung Jawab" |
| `umur_rohani` | 686 | Umur Petugas Kerohanian | text | ya | Legacy mengirim string bebas (mis. "35 th"), bukan angka |
| `jk_rohani` | 687 | Jenis Kelamin Petugas Kerohanian | select | ya | `L` / `P` |
| `agama_rohani` | 688 | Agama Petugas Kerohanian | select | ya | Dari `SelectOption::agama` |
| — | — | Nama / Umur / JK / Agama / No. RM / Ruangan pasien | **read-only** | — | Di-render dari `pasien` + `registrasi_detail` + `bagian`, **tidak** disimpan |

### Dashboard & Form Master

```php
// $objeks (685-690)
685 => 'Nama Petugas Kerohanian',
686 => 'Umur Petugas Kerohanian',
687 => 'Jenis Kelamin Petugas Kerohanian',
688 => 'Agama Petugas Kerohanian',
689 => 'Tanggal & Waktu Pelayanan Kerohanian',
690 => 'Hari & Tempat Pelayanan Kerohanian',
```

Objek 689 & 690 dipakai bersama oleh form 115 (§6).

### Mapping

```php
114 => [
    'nama_rohani'  => 685,
    'umur_rohani'  => 686,
    'jk_rohani'    => 687,   // SelectOption::render('jenis_kelamin')
    'agama_rohani' => 688,   // SelectOption::render('agama')
],
```

### Akses EHR

```php
['profesi_id' => 1,  'form_id' => 114, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 2,  'form_id' => 114, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
['profesi_id' => 14, 'form_id' => 114, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
```

### Validasi

```php
'nama_rohani'  => 'required|string|max:150',
'umur_rohani'  => 'required|string|max:30',
'jk_rohani'    => 'required|in:L,P',
'agama_rohani' => 'required|in:'.implode(',', array_column(SelectOption::get('agama'), 'value')),
```

> **Jangan hardcode `Laki-Laki`/`Perempuan`.** Legacy memakai label tersebut,
> sedangkan `SelectOption::jenis_kelamin` memakai value `L`/`P`. Form 114 memakai
> `L`/`P` agar konsisten dengan master `pasien`, dan label panjang
> ("Laki-laki", "Perempuan") dirender oleh helper.

### Cetak

`print.blade.php` A5, kop resmi, blok pemohon, blok data pasien read-only,
kalimat penutup *"Demikian Permohonan Ini Kami Ajukan"*, kolom tanda tangan
pemohon + tanggal cetak — mengikuti layout legacy.

---

## 6. Bukti Pelayanan Kerohanian (form 115)

### Rasional / tujuan klinis

Dokumen bukti bahwa petugas kerohanian **sudah** memberikan pelayanan, lengkap
dengan kapan (hari/tanggal/jam) dan di mana. Inilah dokumen yang ditunjukkan
saat audit akreditasi. Diisi setelah pelayanan, biasanya oleh petugas
kerohanian sendiri.

### Struktur Field

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `nama_anggota_rohani` | 685 | Nama Petugas Kerohanian | text | ya | Reuse objek 685 |
| `umur_anggota_rohani` | 686 | Umur Petugas Kerohanian | text | ya | Reuse objek 686 |
| `jk_anggota_rohani` | 687 | Jenis Kelamin Petugas Kerohanian | select | ya | Reuse objek 687 |
| `agama_anggota_rohani` | 688 | Agama Petugas Kerohanian | select | ya | Reuse objek 688 |
| `tanggal_rohani` | 689 | Tanggal & Waktu Pelayanan Kerohanian | date | ya | Legacy `tanggal_rohani` |
| `jam_rohani` | 689 | Tanggal & Waktu Pelayanan Kerohanian | time | ya | Legacy `jam_rohani` |
| `hari_rohani` | 690 | Hari & Tempat Pelayanan Kerohanian | select | ya | Legacy: `SENIN`…`MINGGU` (kapital) |
| `tempat_rohani` | 690 | Hari & Tempat Pelayanan Kerohanian | text | ya | Legacy `tempat_rohani` |

> `hari_rohani` sebenarnya bisa dihitung dari `tanggal_rohani`, tetapi legacy
> menyimpannya eksplisit (nilai kapital) karena dipakai langsung di formulir
> cetak. **Tetap simpan** — field ini bukan computed field.

### Dashboard & Form Master

Objek 685–690 sudah didefinisikan di §5.

### Mapping

```php
115 => [
    'nama_anggota_rohani'  => 685,
    'umur_anggota_rohani'  => 686,
    'jk_anggota_rohani'    => 687,
    'agama_anggota_rohani' => 688,
    'tanggal_rohani'       => 689,
    'jam_rohani'           => 689,
    'hari_rohani'          => 690,   // SENIN..MINGGU
    'tempat_rohani'        => 690,
],
```

### Akses EHR

```php
['profesi_id' => 1,  'form_id' => 115, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 14, 'form_id' => 115, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 2,  'form_id' => 115, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
```

> Perhatikan ejaan key: `akses_update`, **bukan** `acess_update`. Tabel
> `akses_ehr` tidak punya kolom `acess_update` — salah ejaan akan membuat
> `insert()` gagal dengan "Unknown column".

### Validasi

```php
'nama_anggota_rohani'  => 'required|string|max:150',
'umur_anggota_rohani'  => 'required|string|max:30',
'jk_anggota_rohani'    => 'required|in:L,P',
'agama_anggota_rohani' => 'required|in:'.implode(',', array_column(SelectOption::get('agama'), 'value')),
'tanggal_rohani'       => 'required|date',
'jam_rohani'           => 'required|date_format:H:i',
'hari_rohani'          => 'required|in:SENIN,SELASA,RABU,KAMIS,JUMAT,SABTU,MINGGU',
'tempat_rohani'        => 'required|string|max:150',
// tambahan (server-side): hari harus cocok dengan tanggal
```

Tambahan validasi lintas-field (di `filteredData()` atau `withValidator()`):

```php
$hariIndonesia = strtoupper(Carbon::parse($data['tanggal_rohani'])->translatedFormat('l'));
abort_if($hariIndonesia !== $data['hari_rohani'], 422, 'Hari tidak sesuai dengan tanggal pelayanan.');
```

### Cetak

`print.blade.php` A5: kalimat *"Dengan Ini Telah Memberikan Pelayanan
Kerohanian kepada"*, blok identitas pasien read-only, blok "Pada, …", kolom
tanda tangan petugas kerohanian, nama petugas/unit.

---

## Implementasi

- [ ] `database/seeders/EmrMasterSeeder.php` — menu 16, sub 313–316, form 112–115,
      objek 671–690, mapping, `akses_ehr`, `EmrHelper::backfillObjekId(112..115)`
- [ ] `app/Http/Controllers/EMR/AsesmenPsikologi/AsesmenPsikologiController.php`
- [ ] `resources/views/moduls/EMR/AsesmenPsikologi/index.blade.php`
- [ ] `resources/views/moduls/EMR/AsesmenPsikologi/print.blade.php`
- [ ] `app/Http/Controllers/EMR/AsesmenSpiritual/AsesmenSpiritualController.php`
- [ ] `resources/views/moduls/EMR/AsesmenSpiritual/index.blade.php`
- [ ] `resources/views/moduls/EMR/AsesmenSpiritual/print.blade.php`
- [ ] `app/Http/Controllers/EMR/PermintaanKerohanian/PermintaanKerohanianController.php`
- [ ] `resources/views/moduls/EMR/PermintaanKerohanian/index.blade.php`
- [ ] `resources/views/moduls/EMR/PermintaanKerohanian/print.blade.php`
- [ ] `app/Http/Controllers/EMR/BuktiKerohanian/BuktiKerohanianController.php`
- [ ] `resources/views/moduls/EMR/BuktiKerohanian/index.blade.php`
- [ ] `resources/views/moduls/EMR/BuktiKerohanian/print.blade.php`
- [ ] `resources/views/moduls/EMR/PartialForm/kerohanian_identitas_pasien.blade.php`
      (blok read-only nama/umur/JK/agama/MR/ruangan, dipakai form 114 & 115)
- [ ] `database/seeders/MasterPegawaiSeeder.php` — tambah
      `['profesi_id' => 14, 'nama_profesi' => 'Kerohanian']`
- [ ] `app/Helpers/SelectOption.php` — tambah key `hari_kerohanian`
      (SENIN…MINGGU) dan `layanan_kerohanian`
- [ ] `routes/web.php` — route print manual untuk 4 form (`->whereNumber('emr_id')`)
- [ ] `AGENTS.md` — entri form 112–115

Tidak ada migration baru. `profesi_id = 14` hanya menambah data seeder.

---

## Catatan & Risiko

| Risiko | Mitigasi |
|---|---|
| `Str::slug('Asesmen Psikologi Pasien & Keluarga','_')` = `assesmen_psikologi_pasien_keluarga` ≠ `form.slug` → form yatim dari dashboard | Sub-menu **wajib** named `Asesmen Psikologi`; verifikasi `Str::slug()` sebelum merge |
| `Str::slug('Asesmen Psikologi','_')` = `asesmen_psikologi` ≠ `form.slug` `assesmen_psikologi` | **Penyimpangan yang disengaja** (konvensi proyek ejaan `assesmen`, lihat §1). `nama_sub_menu` tetap ditulis `Asesmen …`; nilai `form.slug` di-override manual di seeder |
| `id_dash_menu` salah (mis. `16.1`) → form tidak muncul di dashboard & checkbox Akses EHR kosong | Pakai PK aktual: `16.313`…`16.316`. `header_ehr` memakai `CONCAT_WS` sehingga extra NULL dilewati |
| `"16.1" == "16.313"` bernilai **true** di PHP (numeric string `==`) | `AksesEhrController` sudah pakai closure `===`; jangan menambah perbandingan `==` baru |
| Satu `variabel` untuk banyak item checkbox | **Terlarang.** Semua item bersuffix (`psi_info_1..8`, `psi_keluarga_1..10`), persis pola butir Bundle VAP form 14 |
| `profesi_id = 14` (Kerohanian) belum ada di tabel `profesi` | Seed dulu di `MasterPegawaiSeeder`; tanpa itu akses rohani jatuh ke fallback `profesi_id = 1` |
| `_emr_data['x']` scalar di partial | Selalu `$emr_data['x'] ?? ''` — `EmrDataWrapper::__toString()` mengembalikan string kosong sehingga semua field tampil sama |
| Checkbox tanpa hidden `value="0"` tidak terkirim saat tidak dicentang | Tambahkan hidden per checkbox agar baris `emr_detail` selalu ter-update saat edit |
| Tombol hide/show memakai class `hidden` saja | Tailwind v4: `.hidden` bisa kalah spesifisitas dari `inline-flex`/`flex`. Gunakan helper yang **melepas class `hidden` DAN** set `style.display` |
| Tag komponen blade ditulis di dalam `<script>` | Blade mengompilasinya → markup ikut ter-embed, script mati dengan `SyntaxError`. Tulis "komponen confirm-alert", bukan `<x-confirm-alert />` |
| Permintaan (114) ↔ Bukti (115) tidak terkunci relasinya | Tidak ada kolom FK pada desain ini. Bila butuh relasi eksplisit, tambahkan `permintaan_id` (objek baru di luar alokasi 671–690) atau andalkan pencarian by `registrasi_detail_id` + rentang tanggal |
| `hari_rohani` tidak sinkron dengan `tanggal_rohani` | Validasi server lintas-field (lihat §6) |
| Istilah "Konstipasi" dari legacy merupakan salah ketik | Dipertahankan persis agar identik dengan data legacy; koreksi dicatat di `AGENTS.md` |
| Label opsi dari legacy (kapital `SENIN`) tidak konsisten dengan `SelectOption` | Opsi `hari_kerohanian` tetap memakai value kapital; **jangan** reuse key SelectOption yang valued lowercase |
| Beban objek pada menu Manajemen EMR → Form | Alokasi ini hanya 20 objek baru untuk 4 form (reuse agresif), sehingga total objek tetap terkendali |
| Nama menu 16 bentrok dengan menu lain bila digabung branch | `dashboard_menu_id = 16` & `dashboard_menu_sub_id = 313–316` dikoordinasikan dengan dokumen domain lain (band sub §3 ledger); verifikasi `DB::table('dashboard_menu')->where('dashboard_menu_id',16)->exists()` sebelum seeding |