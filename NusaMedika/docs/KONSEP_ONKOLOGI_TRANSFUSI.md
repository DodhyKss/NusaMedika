# Konsep Form EMR: Onkologi & Darah

Rancangan & catatan implementasi 6 form EMR domain **Onkologi dan Transfusi**
(form 106–111): asesmen & pemantauan kemoterapi, asesmen pasien dengan darah,
assesmen transfusi, monitoring transfusi darah, serta double check obat high
alert. Dokumen ini adalah konsep — belum ada kode yang ditulis.

**Status:** Rancangan / Konsep — belum diimplementasikan
**Tanggal:** 2026-10-10
**Sumber legacy:** `/simrs_tenriawaru/FE/lib/modul/`
**Alokasi:** objek_id **993–1032**, `dashboard_menu_id = 18`, sub-menu **353–358**
(band objek §1 & band sub §3 `ALOKASI_ID_GLOBAL.md`; tidak ada `sub_extra`)

> ### ✅ Band objek 993–1032 — 40 objek, sudah sesuai ledger
>
> `ALOKASI_ID_GLOBAL.md` §1 mengalokasikan band **993–1032 (40 objek)** untuk
> dokumen ini. Dokumen ini mendeklarasikan **40 objek baru** — seluruhnya di
> dalam band, tanpa objek ekstra.
>
> Penyesuaian yang sudah diterapkan agar muat di band (varian "gabungkan"
> yang diizinkan PANDUAN §2.6):
>
> | objek | treatment |
> |---|---|
> | `cek_identitas_p1/p2`, `cek_jumlah_p1/p2`, `cek_jenis_p1/p2` | digabung ke objek **1032** (`objek_form_control` mengizinkan banyak variabel → satu objek; yang wajib unik adalah `variabel`, bukan `objek_id`) |
> | `jumlah_baris` | **tidak punya objek** — field turunan, dipetakan dengan `objek_id = NULL` sesuai PANDUAN §2.6 |
>
> `nama_objek` 1032 diperluas menjadi `Pengecekan 5 Benar & Verifikasi Ganda`
> supaya labelnya menutupi kedua kelompok field tersebut. Tidak ada variabel,
> nama field, atau struktur field yang berubah.

---

## 1. Ringkasan

| form_id | nama_form | slug | id_dash_menu | ri | rj | igd | mcu |
|---|---|---|---|---|---|---|---|
| 106 | Asesmen Kemoterapi | `asesmen_kemoterapi` | `18.353` | 1 | 1 | 0 | 0 |
| 107 | Pemantauan Kemoterapi | `pemantauan_kemoterapi` | `18.354` | 1 | 0 | 0 | 0 |
| 108 | Asesmen Pasien dengan Darah | `asesmen_pemberian_darah` | `18.355` | 1 | 1 | 1 | 0 |
| 109 | Assesmen Transfusi | `assesmen_transfusi` | `18.356` | 1 | 0 | 0 | 0 |
| 110 | Monitoring Transfusi Darah | `monitoring_transfusi` | `18.357` | 1 | 0 | 0 | 0 |
| 111 | Double Check Obat High Alert | `double_check_high_alert` | `18.358` | 1 | 0 | 1 | 0 |

Menu **18 "Onkologi & Darah"** adalah menu dashboard EMR baru.
`dashboard_menu_sub_id` bersifat **global**, jadi sub-menu baru memakai
**353–358** — band sub dokumen ini dimulai dari 353 sesuai
`ALOKASI_ID_GLOBAL.md` §3 (angka 1–12 milik menu 1–5; band sebelumnya 313–316
psikososial & kerohanian dan 333–339 surveilans infeksi).

> **Rantai `slug` (PANDUAN §2.1).**
>
> | sub | `nama_sub_menu` | `Str::slug(...)` | `form.slug` |
> |---|---|---|---|
> | 353 | `Asesmen Kemoterapi` | `asesmen_kemoterapi` | `asesmen_kemoterapi` |
> | 354 | `Pemantauan Kemoterapi` | `pemantauan_kemoterapi` | `pemantauan_kemoterapi` |
> | 355 | `Asesmen Pemberian Darah` | `asesmen_pemberian_darah` | `asesmen_pemberian_darah` |
> | 356 | `Asesmen Transfusi` | `asesmen_transfusi` | **`assesmen_transfusi`** |
> | 357 | `Monitoring Transfusi` | `monitoring_transfusi` | `monitoring_transfusi` |
> | 358 | `Double Check High Alert` | `double_check_high_alert` | `double_check_high_alert` |
>
> ### ⚠️ Sub 356: penyimpangan `assesmen` (dua s) yang disengaja
>
> `Str::slug()` bersifat literal: `Str::slug('Asesmen Transfusi','_')` =
> `asesmen_transfusi` (**satu** s), sedangkan `form.slug` terkunci
> `assesmen_transfusi` (**dua** s). Penyimpangan ini **disengaja** dan
> dikunci `ALOKASI_ID_GLOBAL.md` §7: ejaan `assesmen` sudah menjadi **konvensi
> proyek** — lihat form 11 yang sudah berjalan dengan slug
> `assesmen_awal_medis_rawat_jalan`. Menukarnya menjadi `asesmen_*` akan
> membuat slug form baru tidak konsisten dengan kode yang sudah berjalan.
> Konsekuensinya `nama_sub_menu` tetap ditulis `Asesmen Transfusi` supaya
> label dashboard terbaca benar; hanya nilai `form.slug` yang di-override
> manual di seeder.
>
> ### ⚠️ Sub 355 & 358: `nama_form` ≠ `nama_sub_menu`
>
> `nama_form` boleh lebih lengkap karena **hanya** `nama_sub_menu` yang
> menjadi sumber slug. Karena itu:
>
> - Sub **355** **tidak** boleh bernama "Asesmen Pasien dengan Darah"
>   (`Str::slug(...)` = `asesmen_pasien_dengan_darah`, tidak cocok dengan
>   ledger §6). `nama_form` form 108 tetap "Asesmen Pasien dengan Darah".
> - Sub **358** **tidak** boleh bernama "Double Check Obat High Alert"
>   (`Str::slug(...)` = `double_check_obat_high_alert`). `nama_form` form 111
>   tetap "Double Check Obat High Alert".
>
> `/` dan `&` juga tidak boleh muncul di `nama_sub_menu` — `Str::slug()`
> membuang keduanya, bukan menggantinya dengan separator.

---

## 2. Sumber Referensi Legacy

| Berkas legacy | Dipakai untuk |
|---|---|
| `asesmen_kemoterapi.php` | Kerangka form: tab asesmen / persiapan / rencana, `arr_penunjang`, `arr_riwayat`, `arr_alat` |
| `ases_kemo.php` | Riwayat operasi / kemoterapi / radioterapi, penilaian assesmen nyeri (skala, sifat, kualitas, intervensi, lokasi), `reaksi_kemoterapi`, `kondisi_rahim` |
| `persiapan_kemo.php` | `pasang_alat` (Infus / Catheter / Lain), `protokol_kemoterapi`, panel Double Check |
| `rencana_kemo.php` | Lima masalah keperawatan kemoterapi: nyeri, resiko kemoterapi, cemas, nutrisi, infeksi — masing-masing dengan daftar indikator & intervensi |
| `pemantauan_kemo.php` | Tabel baris pemantauan: Jam, Cairan + Obat Kemo (Masuk cc), TD, Nadi, Suhu, RR, Skala Nyeri, Keluhan, Efek Samping Obat, Pencatat |
| `pemantauan_kemoterapi.php` | Wrapper `Lihat`/`Observasi` dari `pemantauan_kemo.php` |
| `protokol_kemoterapi.php` | Daftar regimen: TC, Gemsitabine-Docetaxel, HERCEPTIN, Docetaxel-Carboplatin, dengan premedikasi dan jadwal infus per jam |
| `assement_darah.php` | Asesmen pasien dengan pemberian darah: golongan darah, Rh, dasar penentuan, jenis & jumlah darah, alasan, riwayat transfusi, reaksi alergi, pantangan, tanggal permintaan |
| `persiapan_darah.php` | Persiapan pemberian darah: tanggal, jenis darah, labu darah, keadaan umum (E/M/V), keluhan, TTV, panel Double Check 4 butir x 3 penanda |
| `assement_transfusi.php` | Wrapper tab: assesmen darah / persiapan darah / transfusi / pemantauan (sumber `flag_abnormal` untuk ronde) |
| `form_pemantauan_transfusi.php` | Baris pemantauan transfusi: Tanggal & Jam, TTV, skala nyeri, No Labu Darah, Darah Masuk, gejala, tanda vital, tindakan, komponen darah |
| `monitoring_transfusi_darah.php` | Unggah foto (&lt; 150px) sebagai bukti monitoring |
| `double_check_obat_high_alert.php` | Form 158: tabel obat high alert dengan kolom Perawat 1 & 2 (IDENTITAS/OBAT/DOSIS/CARA/WAKTU), Verifikasi Ganda (SESUAI/TIDAK) 3 butir, daftar obat dari `peresepan_obat_dispense` dengan `barang.flag_alert = 1` |

Struktur yang benar-benar diambil dari legacy:

- **Asesmen kemoterapi** — riwayat (operasi / kemoterapi / radioterapi),
  penunjang terjadwal (`PA`, `IHK`, `Laboratorium`, `Radiologi`),
  alat akses (`Infus`, `Cateter`, `Lain`), kondisi rahim
  (untuk terapi dengan kontrasepsi bila pasien usia subur), tanggal pemberian obat,
  regimen/siklus, perawat & keluarga pendamping.
- **Rencana keperawatan kemoterapi** (`rencana_kemo.php`) — lima masalah
  (nyeri, risiko, cemas, nutrisi, infeksi), masing-masing disimpan sebagai dua
  field: status (`Ya`/`Tidak`) dan isi intervensi.
- **Pemantauan kemoterapi** (`pemantauan_kemo.php`) — tabel baris dengan kolom
  Jam / Cairan + Obat (cc) / TD / Nadi / Suhu / RR / Skala Nyeri / Keluhan /
  Efek Samping / Pencatat, beserta "Pemberian Premedikasi" terpisah.
- **Asesmen dengan darah** — `goldar`, `rh`, `hasil_lab` (dasar penentuan),
  `jenis_darah`, `keb_darah` (jumlah cc), `riwayat_transfusi` (alasan),
  `masuk_transfusi` (pernah Ya/Tidak), `reaksi_alergi_transfusi` (Ya/Tidak),
  `pantangan`, `tgl_perm`.
- **Persiapan & monitoring transfusi** — `no_labu_darah`, `masuk_transfusi`
  (cc), `gejala_fisik[]`, `tanda_tanda_vital[]`, `tindakan`, `komponen_darah`.
- **Double check obat high alert** — baris obat dengan lima kolom perawat
  (`p1_identitas/p1_obat/p1_dosis/p1_cara/p1_waktu` dan `p2_*`), plus tabel
  verifikasi ganda dengan tiga baris (identitas / jumlah / jenis) dan dua
  penanda perawat (SESUAI / TIDAK).

---

## 3. Asesmen Kemoterapi (form 106)

### Rasional / tujuan klinis

Asesmen awal sebelum siklus kemoterapi. Menetapkan kelayakan pasien, riwayat
kemoterapi/radioterapi/operasi sebelumnya, rencana penunjang, alat akses
infus, dan lima masalah keperawatan khas kemoterapi (nyeri, risiko, cemas,
nutrisi, infeksi). Hasilnya menjadi dasar persiapan pemberian obat (form 107).

### Struktur Field

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `tanggal_asesmen` | 155 (reuse) | Tanggal Observasi | date | ya | |
| `waktu_asesmen` | 156 (reuse) | Waktu Observasi | time | ya | |
| `riwayat_operasi` | 61 (reuse) | Riwayat Operasi Kemo | checkbox | ya | Legacy `arr_riwayat` |
| `riwayat_kemoterapi` | 52 (reuse) | Riwayat Kemoterapi | checkbox | ya | |
| `riwayat_radioterapi` | 53 (reuse) | Riwayat Radioterapi | checkbox | ya | |
| `siklus` | 993 | Siklus & Regimen Kemoterapi | text | ya | Contoh: "Siklus 3" |
| `protokol` | 993 | Siklus & Regimen Kemoterapi | select | ya | `PROTOKOL KEMOTERAPI TC`, `GEMSITABINE DAN DOCETAXEL`, `HERCEPTIN`, `DOCETAXEL CARBOPLATIN GINEKOLOGI ONKOLOGI`, `Lainnya` |
| `dpjp_utama` | 994 | DPJP Utama | select (x-select_dokter) | ya | |
| `diagnosis` | 40 (reuse) | Diagnosa Medis | textarea | ya | |
| `penunjang_1` | 995 | Hasil Penunjang Terjadwal | checkbox | ya | `PA` |
| `penunjang_2` | 995 | Hasil Penunjang Terjadwal | checkbox | ya | `IHK` |
| `penunjang_3` | 995 | Hasil Penunjang Terjadwal | checkbox | ya | `Laboratorium` |
| `penunjang_4` | 995 | Hasil Penunjang Terjadwal | checkbox | ya | `Radiologi` |
| `tanggal_pemberian_obat` | 996 | Tanggal Pemberian Obat Kemoterapi | date | ya | |
| `pasang_alat` | 997 | Pemasangan Alat Akses | select | ya | `Infus` / `Cateter` / `Lainnya` |
| `jenis_obat` | 998 | Jenis Obat Kemoterapi | textarea | ya | |
| `reaksi_kemoterapi` | 999 | Reaksi Kemoterapi | select | ya | `Tidak ada` / `Ada` |
| `kondisi_rahim` | 1000 | Kondisi Rahim | textarea | ya* | Wajib bila pasien perempuan usia subur & ada override |
| `perawat_kemoterapi` | 1001 | Perawat & Keluarga Pendamping | text | ya | |
| `keluarga_kemoterapi` | 1001 | Perawat & Keluarga Pendamping | text | ya | |
| `nyeri_kemoterapi` | 1002 | Masalah Keperawatan Kemoterapi | select | ya | Aspek **Nyeri** · `Ya`/`Tidak` |
| `isi_nyeri_kemoterapi` | 1002 | Masalah Keperawatan Kemoterapi | textarea | ya* | Aspek **Nyeri** — wajib bila `nyeri_kemoterapi = Ya` |
| `resiko_kemoterapi` | 1002 | Masalah Keperawatan Kemoterapi | select | ya | Aspek **Risiko** |
| `isi_resiko_kemoterapi` | 1002 | Masalah Keperawatan Kemoterapi | textarea | ya* | Aspek **Risiko** |
| `cemas_kemoterapi` | 1002 | Masalah Keperawatan Kemoterapi | select | ya | Aspek **Cemas** |
| `isi_cemas_kemoterapi` | 1002 | Masalah Keperawatan Kemoterapi | textarea | ya* | Aspek **Cemas** |
| `nutrisi_kemoterapi` | 1002 | Masalah Keperawatan Kemoterapi | select | ya | Aspek **Nutrisi** |
| `isi_nutrisi_kemoterapi` | 1002 | Masalah Keperawatan Kemoterapi | textarea | ya* | Aspek **Nutrisi** |
| `infeksi_kemoterapi` | 1002 | Masalah Keperawatan Kemoterapi | select | ya | Aspek **Infeksi** |
| `isi_infeksi_kemoterapi` | 1002 | Masalah Keperawatan Kemoterapi | textarea | ya* | Aspek **Infeksi** |
| `skala_nyeri` | 140 (reuse) | Skor Nyeri | number 0–10 | tidak | |

### Dashboard & Form Master

```php
// $menus
['dashboard_menu_id' => 18, 'nama_menu' => 'Onkologi & Darah'],

// $subMenus
['dashboard_menu_sub_id' => 353, 'dashboard_menu_id' => 18, 'nama_sub_menu' => 'Asesmen Kemoterapi'],
['dashboard_menu_sub_id' => 354, 'dashboard_menu_id' => 18, 'nama_sub_menu' => 'Pemantauan Kemoterapi'],
['dashboard_menu_sub_id' => 355, 'dashboard_menu_id' => 18, 'nama_sub_menu' => 'Asesmen Pemberian Darah'],
['dashboard_menu_sub_id' => 356, 'dashboard_menu_id' => 18, 'nama_sub_menu' => 'Asesmen Transfusi'],
['dashboard_menu_sub_id' => 357, 'dashboard_menu_id' => 18, 'nama_sub_menu' => 'Monitoring Transfusi'],
['dashboard_menu_sub_id' => 358, 'dashboard_menu_id' => 18, 'nama_sub_menu' => 'Double Check High Alert'],

// $forms
['form_id' => 106, 'nama_form' => 'Asesmen Kemoterapi', 'slug' => 'asesmen_kemoterapi', 'id_dash_menu' => '18.353', 'ri' => 1, 'rj' => 1, 'igd' => 0, 'mcu' => 0],
['form_id' => 107, 'nama_form' => 'Pemantauan Kemoterapi', 'slug' => 'pemantauan_kemoterapi', 'id_dash_menu' => '18.354', 'ri' => 1, 'rj' => 0, 'igd' => 0, 'mcu' => 0],
['form_id' => 108, 'nama_form' => 'Asesmen Pasien dengan Darah', 'slug' => 'asesmen_pemberian_darah', 'id_dash_menu' => '18.355', 'ri' => 1, 'rj' => 1, 'igd' => 1, 'mcu' => 0],
['form_id' => 109, 'nama_form' => 'Asesmen Transfusi', 'slug' => 'assesmen_transfusi', 'id_dash_menu' => '18.356', 'ri' => 1, 'rj' => 0, 'igd' => 0, 'mcu' => 0],
['form_id' => 110, 'nama_form' => 'Monitoring Transfusi Darah', 'slug' => 'monitoring_transfusi', 'id_dash_menu' => '18.357', 'ri' => 1, 'rj' => 0, 'igd' => 0, 'mcu' => 0],
['form_id' => 111, 'nama_form' => 'Double Check Obat High Alert', 'slug' => 'double_check_high_alert', 'id_dash_menu' => '18.358', 'ri' => 1, 'rj' => 0, 'igd' => 1, 'mcu' => 0],

// $objeks (993-1002)
993 => 'Siklus & Regimen Kemoterapi',
994 => 'DPJP Utama',
995 => 'Hasil Penunjang Terjadwal',
996 => 'Tanggal Pemberian Obat Kemoterapi',
997 => 'Pemasangan Alat Akses',
998 => 'Jenis Obat Kemoterapi',
999 => 'Reaksi Kemoterapi',
1000 => 'Kondisi Rahim',
1001 => 'Perawat & Keluarga Pendamping',
1002 => 'Masalah Keperawatan Kemoterapi',
```

### Mapping

```php
106 => [
    'tanggal_asesmen' => 155,   // reuse
    'waktu_asesmen'   => 156,   // reuse

    'riwayat_operasi'    => 61,  // reuse objek Riwayat Operasi Kemo
    'riwayat_kemoterapi' => 52,  // reuse objek Riwayat Kemoterapi
    'riwayat_radioterapi'=> 53,  // reuse objek Riwayat Radioterapi

    'siklus'   => 993,
    'protokol' => 993,
    'dpjp_utama' => 994,
    'diagnosis' => 40,   // reuse objek Diagnosa Medis

    'penunjang_1' => 995,  // PA
    'penunjang_2' => 995,  // IHK
    'penunjang_3' => 995,  // Laboratorium
    'penunjang_4' => 995,  // Radiologi

    'tanggal_pemberian_obat' => 996,
    'pasang_alat'            => 997,
    'jenis_obat'             => 998,
    'reaksi_kemoterapi'      => 999,
    'kondisi_rahim'          => 1000,
    'perawat_kemoterapi'     => 1001,
    'keluarga_kemoterapi'    => 1001,

    // Lima masalah keperawatan kemoterapi (rencana_kemo.php) — satu objek 1002,
    // 10 variabel; aspek dibedakan lewat awalan variabel (lihat §2.1 tabel objek)
    'nyeri_kemoterapi'          => 1002,
    'isi_nyeri_kemoterapi'      => 1002,
    'resiko_kemoterapi'         => 1002,
    'isi_resiko_kemoterapi'     => 1002,
    'cemas_kemoterapi'          => 1002,
    'isi_cemas_kemoterapi'      => 1002,
    'nutrisi_kemoterapi'        => 1002,
    'isi_nutrisi_kemoterapi'    => 1002,
    'infeksi_kemoterapi'        => 1002,
    'isi_infeksi_kemoterapi'    => 1002,

    'skala_nyeri' => 140,  // reuse objek Skor Nyeri
],
```

### Akses EHR

```php
['profesi_id' => 1, 'form_id' => 106, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 2, 'form_id' => 106, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
```

### Validasi

```php
'siklus'   => 'required|string|max:50',
'protokol' => 'required|string|max:100',
'dpjp_utama' => 'required|integer|exists:pegawai,pegawai_id',
'diagnosis' => 'required|string|max:2000',
'tanggal_pemberian_obat' => 'required|date',
'pasang_alat'       => 'required|in:Infus,Catheter,Lainnya',
'jenis_obat'        => 'required|string|max:1000',
'reaksi_kemoterapi' => 'required|in:Tidak ada,Ada',
'perawat_kemoterapi'=> 'required|string|max:150',
'keluarga_kemoterapi' => 'required|string|max:150',
'nyeri_kemoterapi'  => 'required|in:Ya,Tidak',
'resiko_kemoterapi' => 'required|in:Ya,Tidak',
'cemas_kemoterapi'  => 'required|in:Ya,Tidak',
'nutrisi_kemoterapi'=> 'required|in:Ya,Tidak',
'infeksi_kemoterapi' => 'required|in:Ya,Tidak',
'isi_infeksi_kemoterapi' => 'required_if:infeksi_kemoterapi,Ya|nullable|string|max:2000',
```

Tambahan di `filteredData()`:

- Bila `reaksi_kemoterapi = Ada`, wajib ada `keterangan_reaksi` — field tambahan
  dalam objek 999 (variabel `ket_reaksi`).
- `kondisi_rahim`: pada legacy, field ini hanya relevan untuk perempuan usia
  subur; tidak ada validasi kondisional di sistem lama. Di sini dibiarkan opsional
  (`nullable|string|max:1000`) dengan catatan bahwa kebijakan pencegahan
tes fertil harus ditetapkan rumah sakit.
- Seluruh `isi_*` dibuang bila statusnya `Tidak`.

### Cetak

`print.blade.php` — lima panel (Riwayat, Rencana Penunjang, Persiapan
Pemberian Obat, Rencana Keperawatan, Tanda Tangan), beserta tabel regimen
kemoterapi sesuai `protokol` terpilih.

---

## 4. Pemantauan Kemoterapi (form 107)

### Rasional / tujuan klinis

Pemantauan **per jam** selama pemberian kemoterapi: berapa cc cairan masuk, obat
apa dan dosisnya, tanda vital, skala nyeri, keluhan, dan efek samping. Ini form
**repeated-row** dengan jumlah baris tidak tetap — memakai pola baris
`obat_N`/`jumlah_N` (maks 20 baris), bukan `flag_abnormal`, karena baris
dibatasi secara praktis oleh satu siklus perawatan.

### Struktur Field

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `tanggal_pemantauan` | 1003 | Tanggal & Jam Pemantauan Kemoterapi | date | ya | |
| `jam_pemantauan` | 1003 | Tanggal & Jam Pemantauan Kemoterapi | time | ya | |
| `premed_1` | 1004 | Pemberian Premedikasi | text | tidak | Baris 1..5 |
| `premed_dosis_1` | 1004 | Pemberian Premedikasi | text | tidak | |
| `jam_1` | 1005 | Jam Monitoring Kemoterapi | time | ya* | Baris 1..20 |
| `cairan_1` | 1006 | Cairan & Obat Kemoterapi | number (cc) | ya* | |
| `obat_1` | 1006 | Cairan & Obat Kemoterapi | text | ya* | Nama obat |
| `dosis_1` | 1006 | Cairan & Obat Kemoterapi | text | ya* | |
| `sistolik` | 6 (reuse) | Tekanan Darah Sistolik | number | ya* | Diulang per baris |
| `diastolik` | 7 (reuse) | Tekanan Darah Diastolik | number | ya* | |
| `nadi` | 10 (reuse) | Nadi | number | ya* | |
| `suhu` | 11 (reuse) | Suhu | number | ya* | |
| `pernapasan` | 12 (reuse) | Pernapasan | number | ya* | |
| `skala_nyeri` | 140 (reuse) | Skor Nyeri | number 0–10 | ya* | |
| `keluhan_1` | 1007 | Keluhan & Efek Samping Obat | text | ya* | |
| `efek_samping_1` | 1007 | Keluhan & Efek Samping Obat | text | ya* | |
| `total_cairan` | 1008 | Total Cairan Kemoterapi (cc) | number | tidak | **TURUNAN**, dihitung server |
| `perawat_ruangan` | 82 (reuse) | Petugas Pelaksana | text | ya | Nama perawat pemantau (read-only dari `emr.pegawai_id`) |

`ya*` = wajib **per baris** yang terisi.

### Dashboard & Form Master

```php
// $objeks (1003-1008)
1003 => 'Tanggal & Jam Pemantauan Kemoterapi',
1004 => 'Pemberian Premedikasi',
1005 => 'Jam Monitoring Kemoterapi',
1006 => 'Cairan & Obat Kemoterapi',
1007 => 'Keluhan & Efek Samping Obat',
1008 => 'Total Cairan Kemoterapi (cc)',
```

### Mapping

```php
107 => [
    'tanggal_pemantauan' => 1003,
    'jam_pemantauan'     => 1003,

    'premed_1'        => 1004,  // nama premedikasi baris 1
    'premed_dosis_1'  => 1004,  // dosis baris 1

    'jam_1'    => 1005,
    'cairan_1' => 1006,
    'obat_1'   => 1006,
    'dosis_1'  => 1006,

    'sistolik'    => 6,   // reuse, diulang per baris lewat suffix
    'diastolik'   => 7,   // reuse
    'nadi'        => 10,  // reuse
    'pernapasan'  => 12,  // reuse
    'suhu'        => 11,  // reuse
    'skala_nyeri' => 140, // reuse

    'keluhan_1'     => 1007,
    'efek_samping_1'=> 1007,

    'total_cairan' => 1008,   // TURUNAN
    'perawat_ruangan' => 82, // reuse objek Petugas Pelaksana
],
```

Baris monitoring **maks 20** dibangkitkan di loop (identik pola `$mappingBarisObat`
pada form 10 Tindakan Medis):

```php
$mappingBarisKemo = [];
for ($baris = 1; $baris <= 20; $baris++) {
    $mappingBarisKemo['jam_'.$baris]            = 1005;
    $mappingBarisKemo['cairan_'.$baris]         = 1006;
    $mappingBarisKemo['obat_'.$baris]           = 1006;
    $mappingBarisKemo['dosis_'.$baris]          = 1006;
    $mappingBarisKemo['sistolik_'.$baris]       = 6;    // reuse
    $mappingBarisKemo['diastolik_'.$baris]      = 7;    // reuse
    $mappingBarisKemo['nadi_'.$baris]           = 10;   // reuse
    $mappingBarisKemo['pernapasan_'.$baris]     = 12;   // reuse
    $mappingBarisKemo['suhu_'.$baris]           = 11;   // reuse
    $mappingBarisKemo['skala_nyeri_'.$baris]    = 140;  // reuse
    $mappingBarisKemo['keluhan_'.$baris]        = 1007;
    $mappingBarisKemo['efek_samping_'.$baris]   = 1007;
}
$mapping[107] = array_merge($mapping[107], $mappingBarisKemo);

$mappingPremed = [];
for ($baris = 1; $baris <= 5; $baris++) {
    $mappingPremed['premed_'.$baris]       = 1004;
    $mappingPremed['premed_dosis_'.$baris] = 1004;
}
$mapping[107] = array_merge($mapping[107], $mappingPremed);
```

> **Suffix wajib** — `EmrHelper::emrDetailByVariabel()` melakukan
> `pluck('value','variabel')`, sehingga 20 baris dengan variabel sama akan
> saling menimpa dan hanya baris terakhir yang terbaca saat form dibuka lagi.
> Prefiks `sistolik_1`, `sistolik_2`, … boleh dipetakan ke objek yang sama
  (reuse objek 6) karena yang harus unik adalah `variabel`.

**Field turunan `total_cairan`** (PANDUAN §2.4 — nilai browser dibuang):

```php
private function filteredData(array $data, int $formId): array
{
    $data = array_intersect_key($data, array_flip(EmrHelper::objekVariabels($formId)));

    $total = 0;
    for ($baris = 1; $baris <= 20; $baris++) {
        $total += (float) ($data['cairan_'.$baris] ?? 0);
    }
    $data['total_cairan'] = $total;

    return $data;
}
```

### Akses EHR

```php
['profesi_id' => 1, 'form_id' => 107, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 2, 'form_id' => 107, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
```

### Validasi

```php
for ($baris = 1; $baris <= 20; $baris++) {
    $rules = [
        "jam_$baris"         => "nullable|date_format:H:i",
        "cairan_$baris"      => "nullable|numeric|min:0|max:10000",
        "obat_$baris"        => "nullable|string|max:150",
        "dosis_$baris"       => "nullable|string|max:100",
        "sistolik_$baris"    => "nullable|integer|min:50|max:300",
        "diastolik_$baris"   => "nullable|integer|min:20|max:200",
        "nadi_$baris"        => "nullable|integer|min:20|max:250",
        "pernapasan_$baris"  => "nullable|integer|min:5|max:80",
        "suhu_$baris"        => "nullable|numeric|min:30|max:45",
        "skala_nyeri_$baris" => "nullable|integer|min:0|max:10",
        "keluhan_$baris"     => "nullable|string|max:500",
        "efek_samping_$baris"=> "nullable|string|max:500",
    ];
}
```

- Bila satu baris terisi (`jam_N` ada), seluruh kolom baris itu **wajib** terisi
  (dicek di `withValidator()`).
- Minimal satu baris wajib terisi.
- Suffix taken dari atribut `data-item` baris `<tr>`, **bukan** dari urutan array
  — menghapus baris di tengah tidak boleh menggeser indeks.

### Cetak

`print.blade.php` — tabel pemantauan per jam dengan kolom Jam / Cairan / Obat /
Dosis / TD / Nadi / RR / Suhu / Skala Nyeri / Keluhan / Efek Samping / Pencatat,
ditutup baris total cairan.

---

## 5. Asesmen Pasien dengan Darah (form 108)

### Rasional / tujuan klinis

Pra-transfusi: memastikan golongan darah, jenis komponen, jumlah, alasan
transfusi, serta riwayat transfusi dan reaksi alergi sebelumnya. Ini form yang
menjadi dasar permintaan darah ke Bank Darah.

### Struktur Field

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `golongan_darah` | 1009 | Golongan Darah & Rh | select | ya | Dari `SelectOption::golongan_darah` |
| `rhesus` | 1009 | Golongan Darah & Rh | select | ya | `Positif` / `Negatif` |
| `dasar_penentuan` | 1010 | Dasar Penentuan Golongan Darah | select | ya | `Sesuai anamnesis pasien` / `Sesuai hasil lab` |
| `jenis_darah` | 1011 | Jenis Darah yang Diminta | select | ya | `Whole Blood`, `Packed Red Cell`, `Plasma`, `Platelet`, `Cryoprecipitate`, `Lainnya` |
| `jumlah_darah` | 1012 | Jumlah Darah yang Diminta | number (cc) | ya | Legacy `keb_darah` |
| `alasan_transfusi` | 1013 | Alasan Transfusi | textarea | ya | Legacy `riwayat_transfusi` |
| `pernah_transfusi` | 1014 | Riwayat Transfusi & Reaksi Alergi | select | ya | `Ya`/`Tidak` |
| `reaksi_alergi` | 1014 | Riwayat Transfusi & Reaksi Alergi | select | ya | `Ya`/`Tidak` |
| `pantangan_transfusi` | 29 (reuse) | Pantangan Transfusi Darah | text | ya* | Wajib bila `reaksi_alergi = Ya` |
| `tanggal_permintaan` | 1015 | Tanggal Permintaan Darah | date | ya | |
| `dokter_pemohon_id` | 1016 | Dokter Pemohon Darah | select (x-select_dokter) | ya | |

### Dashboard & Form Master

```php
// $objeks (1009-1016)
1009 => 'Golongan Darah & Rh',
1010 => 'Dasar Penentuan Golongan Darah',
1011 => 'Jenis Darah yang Diminta',
1012 => 'Jumlah Darah yang Diminta',
1013 => 'Alasan Transfusi',
1014 => 'Riwayat Transfusi & Reaksi Alergi',
1015 => 'Tanggal Permintaan Darah',
1016 => 'Dokter Pemohon Darah',
```

### Mapping

```php
108 => [
    'golongan_darah'  => 1009,
    'rhesus'          => 1009,
    'dasar_penentuan' => 1010,
    'jenis_darah'     => 1011,
    'jumlah_darah'    => 1012,
    'alasan_transfusi'=> 1013,
    'pernah_transfusi'=> 1014,
    'reaksi_alergi'   => 1014,
    'pantangan_transfusi' => 29,  // reuse objek Pantangan Transfusi Darah
    'tanggal_permintaan'  => 1015,
    'dokter_pemohon_id'   => 1016,
],
```

### Akses EHR

```php
['profesi_id' => 1,  'form_id' => 108, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 2,  'form_id' => 108, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 13, 'form_id' => 108, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
```

Profesi **13 = Analis Laboratorium** untuk verifikasi hasil golongan darah.

### Validasi

```php
'golongan_darah'   => 'required|in:'.implode(',', array_column(SelectOption::get('golongan_darah'), 'value')),
'rhesus'           => 'required|in:Positif,Negatif',
'dasar_penentuan'  => 'required|in:Sesuai anamnesis pasien,Sesuai hasil lab',
'jenis_darah'      => 'required|in:Whole Blood,Packed Red Cell,Plasma,Platelet,Cryoprecipitate,Lainnya',
'jumlah_darah'     => 'required|integer|min:1|max:10000',
'alasan_transfusi' => 'required|string|max:1000',
'pernah_transfusi' => 'required|in:Ya,Tidak',
'reaksi_alergi'    => 'required_if:pernah_transfusi,Ya|nullable|in:Ya,Tidak',
'pantangan_transfusi' => 'required_if:reaksi_alergi,Ya|nullable|string|max:500',
'tanggal_permintaan'  => 'required|date',
'dokter_pemohon_id'   => 'required|integer|exists:pegawai,pegawai_id',
```

Tambahan di `filteredData()`:

- Bila `dasar_penentuan = Sesuai hasil lab`, wajib ada
  `nomor_hasil_lab` (variabel tambahan pada objek 1010) yang menunjuk order
  laboratorium aktif — opsional bila tidak ada linkage LIS.
- Bila `pernah_transfusi = Tidak`, `reaksi_alergi` dan `pantangan_transfusi`
  dibuang dari payload.

### Cetak

`print.blade.php` — formulir permintaan darah ke Bank Darah: identitas pasien,
golongan darah & Rh, dasar penentuan, jenis & jumlah darah, alasan, riwayat
transfusi, tanda tangan dokter pemohon.

---

## 6. Assesmen Transfusi (form 109)

### Rasional / tujuan klinis

Tahap **persiapan** sebelum komponen darah mulai.flows: verifikasi identitas
pasien (gelang, golongan darah, kartu), kecocokan jumlah & jenis darah dengan
instruksi DPJP, dan **verifikasi ganda dua perawat**. Ini implementasi form
`persiapan_darah.php` yang di legacy berada di tab kedua.

### Struktur Field

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `tanggal_persiapan` | 1017 | Tanggal & Waktu Persiapan Transfusi | date | ya | |
| `waktu_persiapan` | 1017 | Tanggal & Waktu Persiapan Transfusi | time | ya | |
| `no_labu_darah` | 1018 | Identitas Labu Darah | text | ya | Nomor labu |
| `jumlah_labu_darah` | 1018 | Identitas Labu Darah | number (cc) | ya | |
| `jenis_labu_darah` | 1018 | Identitas Labu Darah | select | ya | Sama dengan opsi `jenis_darah` form 108 |
| `keadaan_umum` | 1019 | Keadaan Umum & Keluhan | select | ya | Legacy `E : / M : / V :` (Emergency/Muda/Vital) — disederhanakan `Baik`/`Cukup`/`Buruk` |
| `keluhan` | 1019 | Keadaan Umum & Keluhan | text | ya | |
| `dc1_identitas` | 1020 | Verifikasi Ganda Darah - Perawat 1 | select | ya | `SESUAI` / `TIDAK SESUAI` |
| `dc1_jumlah` | 1020 | Verifikasi Ganda Darah - Perawat 1 | select | ya | |
| `dc1_jenis` | 1020 | Verifikasi Ganda Darah - Perawat 1 | select | ya | |
| `dc1_golongan` | 1020 | Verifikasi Ganda Darah - Perawat 1 | select | ya | |
| `dc2_identitas` | 1021 | Verifikasi Ganda Darah - Perawat 2 | select | ya | |
| `dc2_jumlah` | 1021 | Verifikasi Ganda Darah - Perawat 2 | select | ya | |
| `dc2_jenis` | 1021 | Verifikasi Ganda Darah - Perawat 2 | select | ya | |
| `dc2_golongan` | 1021 | Verifikasi Ganda Darah - Perawat 2 | select | ya | |
| `perawat_1_id` | 1022 | Petugas Verifikasi Transfusi | select (x-select_pegawai) | ya | Perawat 1 |
| `perawat_2_id` | 1022 | Petugas Verifikasi Transfusi | select (x-select_pegawai) | ya | Perawat 2 / verifikator |
| `sistolik` | 6 (reuse) | Tekanan Darah Sistolik | number | ya | |
| `diastolik` | 7 (reuse) | Tekanan Darah Diastolik | number | ya | |
| `nadi` | 10 (reuse) | Nadi | number | ya | |
| `pernapasan` | 12 (reuse) | Pernapasan | number | ya | |
| `suhu` | 11 (reuse) | Suhu | number | ya | |

### Dashboard & Form Master

```php
// $objeks (1017-1022)
1017 => 'Tanggal & Waktu Persiapan Transfusi',
1018 => 'Identitas Labu Darah',
1019 => 'Keadaan Umum & Keluhan',
1020 => 'Verifikasi Ganda Darah - Perawat 1',
1021 => 'Verifikasi Ganda Darah - Perawat 2',
1022 => 'Petugas Verifikasi Transfusi',
```

### Mapping

```php
109 => [
    'tanggal_persiapan' => 1017,
    'waktu_persiapan'   => 1017,
    'no_labu_darah'     => 1018,
    'jumlah_labu_darah' => 1018,
    'jenis_labu_darah'  => 1018,
    'keadaan_umum'      => 1019,
    'keluhan'           => 1019,
    'dc1_identitas' => 1020,
    'dc1_jumlah'    => 1020,
    'dc1_jenis'     => 1020,
    'dc1_golongan' => 1020,
    'dc2_identitas' => 1021,
    'dc2_jumlah'    => 1021,
    'dc2_jenis'     => 1021,
    'dc2_golongan' => 1021,
    'perawat_1_id'  => 1022,
    'perawat_2_id'  => 1022,
    'sistolik'    => 6,   // reuse
    'diastolik'   => 7,   // reuse
    'nadi'        => 10,  // reuse
    'pernapasan'  => 12,  // reuse
    'suhu'        => 11,  // reuse
],
```

### Akses EHR

```php
['profesi_id' => 1,  'form_id' => 109, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 2,  'form_id' => 109, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 13, 'form_id' => 109, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
```

### Validasi

```php
'tanggal_persiapan'   => 'required|date',
'waktu_persiapan'     => 'required|date_format:H:i',
'no_labu_darah'       => 'required|string|max:50',
'jumlah_labu_darah'   => 'required|integer|min:1|max:10000',
'jenis_labu_darah'    => 'required|in:Whole Blood,Packed Red Cell,Plasma,Platelet,Cryoprecipitate',
'keadaan_umum'        => 'required|in:Baik,Cukup,Buruk',
'keluhan'             => 'required|string|max:500',
'dc1_identitas' => 'required|in:SESUAI,TIDAK SESUAI',
'dc1_jumlah'    => 'required|in:SESUAI,TIDAK SESUAI',
'dc1_jenis'     => 'required|in:SESUAI,TIDAK SESUAI',
'dc1_golongan' => 'required|in:SESUAI,TIDAK SESUAI',
'dc2_identitas' => 'required|in:SESUAI,TIDAK SESUAI',
'dc2_jumlah'    => 'required|in:SESUAI,TIDAK SESUAI',
'dc2_jenis'     => 'required|in:SESUAI,TIDAK SESUAI',
'dc2_golongan' => 'required|in:SESUAI,TIDAK SESUAI',
'perawat_1_id'  => 'required|integer|exists:pegawai,pegawai_id',
'perawat_2_id'  => 'required|integer|exists:pegawai,pegawai_id|different:perawat_1_id',
'sistolik'  => 'required|integer|min:50|max:300',
'diastolik' => 'required|integer|min:20|max:200',
'nadi'      => 'required|integer|min:20|max:250',
'pernapasan'=> 'required|integer|min:5|max:80',
'suhu'      => 'required|numeric|min:30|max:45',
```

Tambahan di `filteredData()`:

- `perawat_2_id` **wajib berbeda** dari `perawat_1_id` (validasi `different`) —
  ini inti "verifikasi ganda".
- Bila ada satu saja dari 8 butir verifikasi bernilai `TIDAK SESUAI`, simpan tetap
  boleh tetapi tampilkan badge merah "Verifikasi tidak sesuai - jangan mulai
  transfusi".
- `jumlah_labu_darah` harus **sama** dengan `jumlah_darah` pada asesmen form 108
  yang terakhir — dicek lewat `EmrHelper::latestValuesByVariabel(108, …)`;
  bila beda, tampilkan peringatan.

### Cetak

`print.blade.php` — formulir persiapan & verifikasi transfusi dengan tabel
4 butir × 3 kolom penanda (Perawat 1 / Perawat 2 / Pasien-keluarga),
seperti layout legacy `persiapan_darah.php`.

---

## 7. Monitoring Transfusi Darah (form 110)

### Rasional / tujuan klinis

Pemantauan **reaksi transfusi** selama dan setelah komponen darah masuk.
Dilakukan per 15 menit pada fase awal, lalu per jam. Reaksi transfusi ringan
(masih bisa tertangani) sampai berat (bibrosis, dispnea, syok) harus
terdeteksi dan tercatat. Form ini memakai **time-series berbasis
`flag_abnormal`**, sama seperti Observasi Restraint (form 122).

### Struktur Field

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `tanggal_evaluasi` | 155 (reuse) | Tanggal Observasi | date | ya | Legacy `tgl_evaluasi` |
| `jam` | 156 (reuse) | Waktu Observasi | time | ya | |
| `no_labu_darah` | 1023 | No. Labu Darah (Monitoring) | text | ya | Legacy `labu_darah` |
| `darah_masuk` | 1024 | Darah Masuk per Ronde (cc) | number | ya | Legacy `masuk_transfusi` |
| `komponen_darah` | 1025 | Komponen Darah | select | ya | Legacy `komponen_darah` |
| `gejala_1` | 1026 | Gejala Fisik Transfusi | checkbox | ya | 10 gejala |
| `gejala_2` | 1026 | Gejala Fisik Transfusi | checkbox | ya | |
| `gejala_3` | 1026 | Gejala Fisik Transfusi | checkbox | ya | |
| `gejala_4` | 1026 | Gejala Fisik Transfusi | checkbox | ya | |
| `gejala_5` | 1026 | Gejala Fisik Transfusi | checkbox | ya | |
| `gejala_6` | 1026 | Gejala Fisik Transfusi | checkbox | ya | |
| `gejala_7` | 1026 | Gejala Fisik Transfusi | checkbox | ya | |
| `gejala_8` | 1026 | Gejala Fisik Transfusi | checkbox | ya | |
| `gejala_9` | 1026 | Gejala Fisik Transfusi | checkbox | ya | |
| `gejala_10` | 1026 | Gejala Fisik Transfusi | checkbox | ya | |
| `tanda_1` | 1027 | Tanda Tanda Vital during Transfusi | checkbox | ya | 10 tanda |
| `tanda_2` | 1027 | Tanda Tanda Vital during Transfusi | checkbox | ya | |
| `tanda_3` | 1027 | Tanda Tanda Vital during Transfusi | checkbox | ya | |
| `tanda_4` | 1027 | Tanda Tanda Vital during Transfusi | checkbox | ya | |
| `tanda_5` | 1027 | Tanda Tanda Vital during Transfusi | checkbox | ya | |
| `tanda_6` | 1027 | Tanda Tanda Vital during Transfusi | checkbox | ya | |
| `tanda_7` | 1027 | Tanda Tanda Vital during Transfusi | checkbox | ya | |
| `tanda_8` | 1027 | Tanda Tanda Vital during Transfusi | checkbox | ya | |
| `tanda_9` | 1027 | Tanda Tanda Vital during Transfusi | checkbox | ya | |
| `tanda_10` | 1027 | Tanda Tanda Vital during Transfusi | checkbox | ya | |
| `tindakan` | 1028 | Tindakan & Catatan Transfusi | textarea | ya | Legacy `tindakan` |
| `keluhan` | 1029 | Keluhan Pasien | text | ya | |
| `skala_nyeri` | 140 (reuse) | Skor Nyeri | number 0–10 | tidak | |
| `sistolik` | 6 (reuse) | Tekanan Darah Sistolik | number | ya | Per ronde |
| `diastolik` | 7 (reuse) | Tekanan Darah Diastolik | number | ya | Per ronde |
| `nadi` | 10 (reuse) | Nadi | number | ya | Per ronde |
| `pernapasan` | 12 (reuse) | Pernapasan | number | ya | Per ronde |
| `suhu` | 11 (reuse) | Suhu | number | ya | Per ronde |

### Dashboard & Form Master

```php
// $objeks (1023-1029)
1023 => 'No. Labu Darah (Monitoring)',
1024 => 'Darah Masuk per Ronde (cc)',
1025 => 'Komponen Darah',
1026 => 'Gejala Fisik Transfusi',
1027 => 'Tanda Tanda Vital during Transfusi',
1028 => 'Tindakan & Catatan Transfusi',
1029 => 'Keluhan Pasien',
```

### Mapping — dan mekanisme `flag_abnormal`

Sama seperti form 122: indeks ronde disimpan di `emr_detail.flag_abnormal`.

```php
110 => [
    'tanggal_evaluasi' => 155,
    'jam'              => 156,
    'no_labu_darah'    => 1023,
    'darah_masuk'      => 1024,
    'komponen_darah'   => 1025,
    'gejala_1'  => 1026,
    'gejala_2'  => 1026,
    // ... gejala_10 => 1026
    'tanda_1'   => 1027,
    'tanda_2'   => 1027,
    // ... tanda_10 => 1027
    'tindakan'   => 1028,
    'keluhan'   => 1029,
    'skala_nyeri' => 140,
    'sistolik'   => 6,
    'diastolik'  => 7,
    'nadi'       => 10,
    'pernapasan' => 12,
    'suhu'       => 11,
],
```

Variabel `gejala_1..10` dan `tanda_1..10` **wajib ditulis eksplisit di mapping**
(generate di loop bila perlu):

```php
for ($i = 1; $i <= 10; $i++) {
    $mapping[110]['gejala_'.$i] = 1026;
    $mapping[110]['tanda_'.$i]  = 1027;
}
```

Pembacaan & penyimpanan identik dengan §"Observasi Restraint":

```php
// Penyimpanan per ronde — index ronde = flag_abnormal
$rondaBerikut = ((int) DB::table('emr_detail')
        ->where('emr_id', $emrId)
        ->where(function ($q) { $q->whereNull('status_batal')->orWhere('status_batal', 0); })
        ->max('flag_abnormal')) + 1;

// Pembacaan — WAJIB grouping, jangan emrDetailByVariabel()
$rondellan = DB::table('emr_detail')
    ->where('emr_id', $emrId)
    ->where(function ($q) { $q->whereNull('status_batal')->orWhere('status_batal', 0); })
    ->whereNotNull('flag_abnormal')
    ->orderBy('flag_abnormal')
    ->orderBy('objek_id')
    ->get()
    ->groupBy('flag_abnormal')
    ->map(fn ($rows) => $rows->pluck('value', 'variabel'))
    ->all();
```

`darah_masuk` **kumulatif** (bukan delta): Lebih mudah dipakai sebagai titik grafik
lonjakan dan sebagai pembanding dengan total labu saat selesai.

### Akses EHR

```php
['profesi_id' => 1, 'form_id' => 110, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 2, 'form_id' => 110, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
```

### Validasi

Per ronde (`ronde.N.*`):

```php
"ronde.$ronde.tanggal_evaluasi" => 'required|date',
"ronde.$ronde.jam"              => 'required|date_format:H:i',
"ronde.$ronde.no_labu_darah"     => 'required|string|max:50',
"ronde.$ronde.darah_masuk"       => 'required|integer|min:0|max:10000',
"ronde.$ronde.komponen_darah"    => 'required|in:Whole Blood,Packed Red Cell,Plasma,Platelet,Cryoprecipitate',
"ronde.$ronde.sistolik"          => 'required|integer|min:50|max:300',
"ronde.$ronde.diastolik"         => 'required|integer|min:20|max:200',
"ronde.$ronde.nadi"              => 'required|integer|min:20|max:250',
"ronde.$ronde.pernapasan"        => 'required|integer|min:5|max:80',
"ronde.$ronde.suhu"              => 'required|numeric|min:30|max:45',
"ronde.$ronde.gejala_1"          => 'nullable|boolean',
// ... gejala_2..10
"ronde.$ronde.tindakan"          => 'required|string|max:1000',
"ronde.$ronde.keluhan"           => 'required|string|max:500',
```

Tambahan:

- `darah_masuk` ronde berikutnya **harus >=** ronde sebelumnya (kumulatif). Validasi
  di `withValidator()`.
- `jumlah_ronde` `required|integer|min:1|max:32` (32 baris/hari).
- Bila pada ronde mana pun terdapat gejala berat (`demam`, `urtikaria`,
  `sesak napas`, `dispnea`) → tampilkan badge **"Reaksi Transfusi Ringan–Berat"**
  beserta nomor telepon Bank Darah.

### Cetak

`print.blade.php` — tabel pemantauan berkala dengan kolom Jam / Labu Darah /
Darah Masuk / Komponen / TD / Nadi / RR / Suhu / Gejala / Tanda / Tindakan /
Pencatat, ditutup Signature pemantau.

> Legacy juga menyediakan unggah foto (`monitoring_transfusi_darah.php` storing
> `data_image` base64, objek 161). Di NusaMedika, binary base64 **tidak**
> disimpan di `emr_detail` (kolom `value` bertipe text). Bila fitur foto
> dibutuhkan, buat tabel `dokumen_emr` terpisah — di luar cakupan dokumen ini.

---

## 8. Double Check Obat High Alert (form 111)

### Rasional / tujuan klinis

Keselamatan pasien untuk obat **high alert** (antikoagulan, insulin, opioid,
elektrolit pekat, chemoterapi, dll). Dua lapis: **5 benar** oleh **dua perawat**
(identitas, obat, dosis, cara, waktu) per baris obat, lalu **verifikasi ganda**
atas identitas/jumlah/jenis obat terhadap instruksi DPJP. Daftar obat diambil
otomatis dari resep yang sudah ter-dispense dan master barang ber
`flag_alert = 1`.

### Struktur Field

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `tanggal_pemberian` | 1030 | Tanggal & Jam Pemberian High Alert | date | ya | |
| `waktu_pemberian` | 1030 | Tanggal & Jam Pemberian High Alert | time | ya | |
| `ha_obat_1` | 69 (reuse) | Obat | text (datalist) | ya | Nama obat high alert baris 1..20 (prefill dari resep dispense) |
| `ha_p1_identitas_1` | 1031 | Pengecekan 5 Benar - Perawat 1 | checkbox | ya* | |
| `ha_p1_obat_1` | 1031 | Pengecekan 5 Benar - Perawat 1 | checkbox | ya* | |
| `ha_p1_dosis_1` | 1031 | Pengecekan 5 Benar - Perawat 1 | checkbox | ya* | |
| `ha_p1_cara_1` | 1031 | Pengecekan 5 Benar - Perawat 1 | checkbox | ya* | |
| `ha_p1_waktu_1` | 1031 | Pengecekan 5 Benar - Perawat 1 | checkbox | ya* | |
| `ha_p2_identitas_1` | 1032 | Pengecekan 5 Benar & Verifikasi Ganda | checkbox | ya* | |
| `ha_p2_obat_1` | 1032 | Pengecekan 5 Benar & Verifikasi Ganda | checkbox | ya* | |
| `ha_p2_dosis_1` | 1032 | Pengecekan 5 Benar & Verifikasi Ganda | checkbox | ya* | |
| `ha_p2_cara_1` | 1032 | Pengecekan 5 Benar & Verifikasi Ganda | checkbox | ya* | |
| `ha_p2_waktu_1` | 1032 | Pengecekan 5 Benar & Verifikasi Ganda | checkbox | ya* | |
| `cek_identitas_p1` | 1032 | Pengecekan 5 Benar & Verifikasi Ganda | select | ya | `SESUAI` / `TIDAK` |
| `cek_identitas_p2` | 1032 | Pengecekan 5 Benar & Verifikasi Ganda | select | ya | |
| `cek_jumlah_p1` | 1032 | Pengecekan 5 Benar & Verifikasi Ganda | select | ya | |
| `cek_jumlah_p2` | 1032 | Pengecekan 5 Benar & Verifikasi Ganda | select | ya | |
| `cek_jenis_p1` | 1032 | Pengecekan 5 Benar & Verifikasi Ganda | select | ya | |
| `cek_jenis_p2` | 1032 | Pengecekan 5 Benar & Verifikasi Ganda | select | ya | |
| `perawat_1_id` | 82 (reuse) | Petugas Pelaksana | select | ya | Perawat 1 |
| `perawat_2_id` | 82 (reuse) | Petugas Pelaksana | select | ya | Perawat 2 (verifikator) |
| `jumlah_baris` | NULL | — | number | tidak | **TURUNAN**, dihitung server; `objek_id = NULL` (PANDUAN §2.6) |

### Dashboard & Form Master

```php
// $objeks (1030-1032)
1030 => 'Tanggal & Jam Pemberian High Alert',
1031 => 'Pengecekan 5 Benar - Perawat 1',
1032 => 'Pengecekan 5 Benar & Verifikasi Ganda',
```

### Mapping

```php
111 => [
    'tanggal_pemberian' => 1030,
    'waktu_pemberian'   => 1030,
    'cek_identitas_p1'  => 1032,   // objek 1032 mencakup verifikasi ganda
    'cek_identitas_p2'  => 1032,
    'cek_jumlah_p1'     => 1032,
    'cek_jumlah_p2'     => 1032,
    'cek_jenis_p1'      => 1032,
    'cek_jenis_p2'      => 1032,
    'perawat_1_id'      => 82,   // reuse objek Petugas Pelaksana
    'perawat_2_id'      => 82,   // reuse objek Petugas Pelaksana
    'jumlah_baris'      => null, // TURUNAN — objek_id NULL (PANDUAN §2.6)
],
```

Baris obat **maks 20** dibangkitkan di loop:

```php
$mappingBarisHa = [];
for ($baris = 1; $baris <= 20; $baris++) {
    $mappingBarisHa['ha_obat_'.$baris]        = 69;  // reuse objek Obat
    $mappingBarisHa['ha_p1_identitas_'.$baris] = 1031;
    $mappingBarisHa['ha_p1_obat_'.$baris]       = 1031;
    $mappingBarisHa['ha_p1_dosis_'.$baris]      = 1031;
    $mappingBarisHa['ha_p1_cara_'.$baris]       = 1031;
    $mappingBarisHa['ha_p1_waktu_'.$baris]      = 1031;
    $mappingBarisHa['ha_p2_identitas_'.$baris] = 1032;
    $mappingBarisHa['ha_p2_obat_'.$baris]       = 1032;
    $mappingBarisHa['ha_p2_dosis_'.$baris]      = 1032;
    $mappingBarisHa['ha_p2_cara_'.$baris]       = 1032;
    $mappingBarisHa['ha_p2_waktu_'.$baris]      = 1032;
}
$mapping[111] = array_merge($mapping[111], $mappingBarisHa);
```

**Prefill dari resep yang sudah dispense:**

```php
$listObatHighAlert = Barang::aktif()
    ->where('flag_alert', 1)
    ->when($resepId, fn ($q) => $q->whereIn('barang_id', function ($sub) use ($resepId) {
        $sub->select('barang_id')
            ->from('peresepan_obat_dispense')
            ->where('peresepan_obat_id', $resepId)
            ->where(function ($q) { $q->whereNull('status_batal')->orWhere('status_batal', 0); });
    }))
    ->pluck('nama_barang', 'barang_id');
```

Nilai ini dikirim ke view sebagai `$opsiObatHa` (array id → nama) untuk `<datalist>`.

**`jumlah_baris` dihitung server (PANDUAN §2.4):**

```php
private function filteredData(array $data, int $formId): array
{
    $data = array_intersect_key($data, array_flip(EmrHelper::objekVariabels($formId)));

    $jumlah = 0;
    for ($baris = 1; $baris <= 20; $baris++) {
        if (trim((string) ($data['ha_obat_'.$baris] ?? '')) !== '') {
            $jumlah = $baris;
        }
    }
    $data['jumlah_baris'] = $jumlah;

    return $data;
}
```

### Akses EHR

```php
['profesi_id' => 1, 'form_id' => 111, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
['profesi_id' => 2, 'form_id' => 111, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 4, 'form_id' => 111, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
```

Profesi **4 = Apoteker** boleh menutupi penilaian farmasi; **Dokter hanya
read** karena filled oleh perawat.

> **`profesi_id = 4` (Apoteker) SUDAH ADA** di `MasterPegawaiSeeder` — jangan
> didefinisikan ulang. Sesuai `ALOKASI_ID_GLOBAL.md` §5, id 4 = Apoteker
> (bukan 14); id 14 = **Kerohanian** dan dipakai
> `KONSEP_PSIKOSOSIAL_KEROHANIAN.md`, id 3 = Bidan (bukan Ahli Gizi — Ahli Gizi
> = 10). Dokumen ini tidak memakai profesi baru sama sekali; seluruh
> `profesi_id` yang dipakai (1, 2, 4, 13) sudah ada di tabel `profesi`.

### Validasi

```php
'tanggal_pemberian'  => 'required|date',
'waktu_pemberian'    => 'required|date_format:H:i',
'cek_identitas_p1'   => 'required|in:SESUAI,TIDAK',
'cek_identitas_p2'   => 'required|in:SESUAI,TIDAK',
'cek_jumlah_p1'      => 'required|in:SESUAI,TIDAK',
'cek_jumlah_p2'      => 'required|in:SESUAI,TIDAK',
'cek_jenis_p1'       => 'required|in:SESUAI,TIDAK',
'cek_jenis_p2'       => 'required|in:SESUAI,TIDAK',
'perawat_1_id'       => 'required|integer|exists:pegawai,pegawai_id',
'perawat_2_id'       => 'required|integer|exists:pegawai,pegawai_id|different:perawat_1_id',
```

Per baris (hanya bila `ha_obat_N` terisi):

```php
for ($baris = 1; $baris <= 20; $baris++) {
    foreach ([
        "ha_p1_identitas_$baris", "ha_p1_obat_$baris", "ha_p1_dosis_$baris",
        "ha_p1_cara_$baris", "ha_p1_waktu_$baris",
        "ha_p2_identitas_$baris", "ha_p2_obat_$baris", "ha_p2_dosis_$baris",
        "ha_p2_cara_$baris", "ha_p2_waktu_$baris",
    ] as $k) {
        $rules[$k] = "required_if:ha_obat_$baris,!=,|nullable|boolean";
    }
}
```

Tambahan:

- `perawat_2_id` wajib **berbeda** dari `perawat_1_id` (`different`) — inti
  "double check".
- Bila ada satu saja `cek_*` bernilai `TIDAK`, tampilkan peringatan keras dan
  **jangan** izinkan simpan bila `akses_create` perawat tanpa apoteker. Legacy
  hanya menampilkan tanda; di sini tetap dipakai peringatan, bukan blokir agar
  tidak menghambat alur penanganan pasien.

### Cetak

`print.blade.php` — tabel 5 benar per baris obat dengan kolom Perawat 1 & 2,
tabel verifikasi ganda 3 baris (Identitas / Jumlah / Jenis), serta kolom
tanda tangan kedua perawat.

---

## Implementasi

- [ ] `database/seeders/EmrMasterSeeder.php` — menu 18, sub 353–358, form 106–111,
      objek 993–1032, mapping (loop untuk baris `ha_*`, `premed_*`, baris
      pemantauan `jam_N`), `akses_ehr`, `EmrHelper::backfillObjekId(106..111)`
- [ ] `app/Http/Controllers/EMR/AsesmenKemoterapi/AsesmenKemoterapiController.php`
- [ ] `resources/views/moduls/EMR/AsesmenKemoterapi/{index,print}.blade.php`
- [ ] `app/Http/Controllers/EMR/PemantauanKemoterapi/PemantauanKemoterapiController.php`
- [ ] `resources/views/moduls/EMR/PemantauanKemoterapi/{index,print}.blade.php`
- [ ] `app/Http/Controllers/EMR/AsesmenPemberianDarah/AsesmenPemberianDarahController.php`
- [ ] `resources/views/moduls/EMR/AsesmenPemberianDarah/{index,print}.blade.php`
- [ ] `app/Http/Controllers/EMR/AssesmenTransfusi/AssesmenTransfusiController.php`
- [ ] `resources/views/moduls/EMR/AssesmenTransfusi/{index,print}.blade.php`
- [ ] `app/Http/Controllers/EMR/MonitoringTransfusi/MonitoringTransfusiController.php`
- [ ] `resources/views/moduls/EMR/MonitoringTransfusi/{index,print}.blade.php`
- [ ] `app/Http/Controllers/EMR/DoubleCheckHighAlert/DoubleCheckHighAlertController.php`
- [ ] `resources/views/moduls/EMR/DoubleCheckHighAlert/{index,print}.blade.php`
- [ ] `app/Helpers/EmrHelper.php` — `emrDetailRonde()` dipakai bersama oleh form
      110 & 122 (time-series `flag_abnormal`)
- [ ] `database/migrations/*_add_flag_alert_to_barang_table.php` — kolom
      `barang.flag_alert` untuk prefill daftar obat high alert (bila belum ada)
- [ ] `app/Helpers/SelectOption.php` — key `jenis_komponen_darah`,
      `gejala_reaksi_transfusi`, `tanda_reaksi_transfusi`,
      `protokol_kemoterapi`, `jenis_alat_akses_kemo`, `rhesus`,
      `keadaan_umum_transfusi`
- [ ] `routes/web.php` — route print manual untuk 6 form (`->whereNumber('emr_id')`)
- [ ] `AGENTS.md` — entri form 106–111

Migration baru **hanya** bila `barang.flag_alert` belum ada.

---

## Catatan & Risiko

| Risiko | Mitigasi |
|---|---|
| `nama_sub_menu` sub 355/358 ikut `nama_form` ("Asesmen Pasien dengan Darah" / "Double Check Obat High Alert") | Tulis persis `Asesmen Pemberian Darah` dan `Double Check High Alert`; hanya `nama_sub_menu` yang jadi sumber slug |
| Band objek melebihi ledger (pernah 993–1034) | Objek `Verifikasi Ganda Kesesuaian` digabung ke 1032; `jumlah_baris` dipetakan `objek_id = NULL`. Band final **993–1032** sesuai `ALOKASI_ID_GLOBAL.md` §1 |
| Form 107 & 110 punya banyak baris (20 / 32) | Wajib suffix; `emrDetailByVariabel()` akan menimpa tanpa suffix |
| Form 110 & 122 memakai `flag_abnormal` sebagai indeks ronde | Jelaskan di doc AGENTS; kode Order Lab/Rad memakai kolom sama di tabel **berbeda**, tidak terpengaruh |
| Pembacaan time-series memakai `emrDetailByVariabel()` | **Terlarang.** Wajib grouping manual per `flag_abnormal` |
| `total_cairan` & `jumlah_baris` diambil dari browser | Hitung ulang di server pada `filteredData()` |
| `perawat_2_id` sama dengan `perawat_1_id` | Validasi `different` pada form 109 & 111 |
| `nama_barang` obat high alert bisa diubah bebas | Gunakan `<datalist>` + validasi `exists:barang,nama_barang` ATAU simpan `barang_id` (objek baru). Field 69 dipakai ulang agar tidak menambah alokasi objek |
| `barang.flag_alert` belum ada | Migration kecil; bila tabel `barang` belum punya kolom tersebut, tambahkan di migration terpisah |
| Suffix baris dinamis diambil dari urutan array | Ambil dari atribut `data-item` baris `<tr>` (pola Tindakan Medis) |
| `daerah waktu container db = UTC` | Semua timestamp ditulis dari PHP `now()` |
| Nama variabel pada contoh kode harus konsisten | Gunakan `nyeri_kemoterapi` dan `infeksi_kemoterapi` (bukan singkatan/typo) di seluruh file controller, view, dan seeder |
| 40 objek baru pada satu menu (band 993–1032) | Grouping per objek menjaga jumlah total tetap; total objek repo masih terkendali |
| `x-select_dokter` tidak punya `@error` | Saat menambah select baru, selalu sertakan blok `@error` (bug yang sudah pernah terjadi) |