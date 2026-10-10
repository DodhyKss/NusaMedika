# Konsep Penilaian Nyeri (EMR)

**Status:** Rancangan / Konsep — belum diimplementasikan
**Tanggal:** 2026-10-10
**Acuan:** [`PANDUAN_IMPLEMENTASI_FORM_EMR.md`](PANDUAN_IMPLEMENTASI_FORM_EMR.md),
[`ANALISIS_KEKURANGAN_FORM.md`](ANALISIS_KEKURANGAN_FORM.md)
**Sumber legacy:** `/simrs_tenriawaru/FE/lib/modul/`

---

## 1. Ringkasan

| form_id | nama_form | slug | id_dash_menu | ri | rj | igd | mcu |
|---|---|---|---|---|---|---|---|
| 27 | Penilaian Nyeri | `penilaian_nyeri` | `9.113.66` | 1 | 1 | 1 | 0 |
| 36 | Metode Nyeri | `metode_nyeri` | `9.113.67` | 1 | 1 | 1 | 0 |
| 37 | Body Map Nyeri | `body_map_nyeri` | `9.114` | 1 | 1 | 1 | 0 |
| 38 | Skrining Nyeri Perina | `skrining_nyeri_perina` | `9.113.68` | 1 | 0 | 0 | 0 |

Objek baru yang dialokasikan: **313 – 335** (23 objek). Objek lama yang
di-*reuse*: 14 (Nyeri), 77 (Keterangan), 140 (Skor Nyeri), 161 & 18 (tidak dipakai
— nomor legacy sudah bentrok), 251 (Masalah Keperawatan).

### 1.1 Struktur menu dashboard

```
dashboard_menu 9  "Monitoring"        (menu baru, dibuat di KONSEP_CAIRAN_BALANCE.md)
├── sub 94  "Monitoring Cairan"   → form 26   (9.94)
├── sub 95  "Kesadaran Oksigen"   → form 28   (9.95)
├── sub 113 "Nyeri"
│   ├── extra 66 "Penilaian Nyeri"           → form 27 (9.113.66)
│   ├── extra 67 "Metode Nyeri"             → form 36 (9.113.67)
│   └── extra 68 "Skrining Nyeri Perina"    → form 38 (9.113.68)
└── sub 114 "Body Map Nyeri"    → form 37 (9.114)
```

> `dashboard_menu_sub_extra` 66–68 milik dokumen ini (46–47 milik
> `KONSEP_CAIRAN_BALANCE.md`, 86–88 milik `KONSEP_RISIKO_JATUH_LANJUTAN.md`).
> Band sub dokumen ini = **113–132**; sub 93–112 milik
> `KONSEP_CAIRAN_BALANCE.md` (`ALOKASI_ID_GLOBAL.md` §3).
> `dashboard_menu_sub_id` bersifat **global** — sub 1–12 milik menu 1–5 dan
> tidak boleh dipakai ulang.

---

## 2. Sumber Referensi Legacy

| Form | Berkas legacy | Isi |
|---|---|---|
| 27 Penilaian Nyeri | `FE/lib/modul/nyeri_cak.php` (590 baris) | Flag nyeri, metode VAS/FLACC, frekuensi, karakteristik, body map (canvas), pemicu & penющим nyeri, intervensi |
| 36 Metode Nyeri | `FE/lib/modul/metode_nyeri.php` (440 baris) | Pemilih metode VAS/FLACC + tabel FLACC lengkap (5 item × 3 opsi) + total score |
| 37 Body Map Nyeri | `FE/lib/modul/gambar_nyeri.php`, `nyeri_cak.php` bagian Lokasi | Canvas `createjs` di atas `assets/vendor/nyeri_tubuh/tubuh.png` |
| 38 Skrining Nyeri Perina | `FE/lib/modul/skrining_nyeri_perina.php` (153 baris) | NIPS — 6 parameter neonatus + interpretasi |
| (pendukung) | `FE/lib/modul/nyeri.php`, `FE/lib/modul/PengkajianNyeriAwal/` | Partial pengkajian nyeri awal |

---

## 3. Landasan Ilmiah & Skala yang Digunakan

| Skala | Populasi | Rentang | Kategori |
|---|---|---|---|
| **VAS / NRS** (Visual Analog Scale / Numeric Rating Scale) | Dewasa & anakverbal | 0–10 | 1–3 ringan · 4–6 sedang · 7–10 berat |
| **FLACC** (Face, Legs, Activity, Cry, Consolability) | Bayi & anak tidak dapat Eld communicate | 0–10 | 0 tidak nyeri · 1–3 ringan · 4–6 sedang · 7–10 berat |
| **NIPS** (Neonatal Infant Pain Scale) | Neonatus | 0–7 | 0–2 tidak nyeri–ringan · 3–4 ringan–sedang · >4 berat |
| **Body Map** | Semua | bukan skor | Deskriptif lokasi |

> FLACC dipakai di tiga tempat: form 36 (metode), form 27 (bila metode = FLACC),
> dan form 35 (kritis). Karena itu **definisi FLACC harus hidup di satu helper**
> (`NyeriHelper`), bukan diduplikasi di tiga controller.

---

## 4. Penilaian Nyeri (form 27)

### Rasional

Form utama pengkajian nyeri. Legacy `nyeri_cak.php` memuat blok:

| Blok | Field legacy (objek) | Opsi |
|---|---|---|
| Flag | `nyeri` (17) | Ya / Tidak |
| Metode | `metode_skala_nyeri` (1101) | VAS / FLACC |
| VAS | `skala_nyeri` (18) | 1..10 (radio) |
| FLACC | `wajah` (498), `kaki` (499), `activity` (809), `menangis` (503), `dihibur` (810) | 0/1/2 tiap item |
| FLACC total | `fc_score` (811) | 0..10 |
| Frekuensi | `frekuensi_nyeri` (19) | Tidak Ada / Sering / Kadang / Jarang |
| Karakteristik | `karakteristik_nyeri` (20) | Terbakar, Tertindih, Menyebar, Tajam, Tumpul, Berdenyut, Lainnya |
| Lokasi | `data_image` (161) | canvas body map |
| Pemicu | `pemicu_nyeri` (248) | free text |
| Faktor pengurang | `meringankan_nyeri` (249) | free text |
| Intervensi | `intervensi` | free text (`nyeri.php`) |

Nomor objek legacy **tidak boleh dipakai** — bentrok dengan objek NusaMedika
(17 = Alergi, 18 = EVM/EWS, 19 = Cara Pemberian O2). Semua variabel unik berikut.

### Struktur Field

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `tanggal_nyeri` | 155 | Tanggal Observasi | date | ya | Reuse |
| `waktu_nyeri` | 156 | Waktu Observasi | time | ya | Reuse |
| `nyeri` | 14 | Nyeri | radio Ya/Tidak | ya | Reuse objek 14 |
| `metode_skala_nyeri` | 319 | Metode Skala Nyeri | radio VAS/FLACC | ya bila nyeri | Objek baru |
| `skor_nyeri` | 140 | Skor Nyeri | number 0–10 | ya bila VAS | Reuse objek 140 |
| `skor_flacc` | 318 | Skor FLACC | readonly (server) | ya bila FLACC | **Turunan**, objek baru |
| `frekuensi_nyeri` | 313 | Frekuensi Nyeri | radio | ya | Objek baru |
| `karakteristik_nyeri` | 314 | Karakteristik Nyeri | checkbox group | ya | Objek baru; 6 opsi tetap + Lainnya |
| `pemicu_nyeri` | 315 | Faktor Pemicu yang Memperberat Nyeri | textarea | ya | Objek baru |
| `meringankan_nyeri` | 316 | Faktor yang Mengurangi Nyeri | textarea | ya | Objek baru |
| `lokasi_nyeri` | 317 | Lokasi Nyeri (Teks) | text | ya | Objek baru — deskripsi bila tanpa body map |
| `gambar_lokasi_nyeri` | 326 | Gambar Lokasi Nyeri | canvas | tidak | Objek baru (dipakai form 37) |
| `intervensi_nyeri` | 5 | Instruksi (I) | textarea | ya | **Reuse objek 5** |
| `catatan` | 77 | Keterangan | textarea | tidak | Reuse objek 77 |

### Opsi Detail

**Frekuensi** (objek 313): `Tidak Ada`, `Kadang`, `Sering`, `Jarang`.

**Karakteristik** (objek 314): `Terbakar`, `Tertindih`, `Menyebar`, `Tajam`,
`Tumpul`, `Berdenyut`, `Lainnya` (teks bebas bila dicentang).

### Perhitungan Server-Side (Skor FLACC)

```php
// PainController::filteredData()
if (($data['nyeri'] ?? '') === 'Ya' && ($data['metode_skala_nyeri'] ?? '') === 'FLACC') {
    $r = NyeriHelper::flacc([
        'wajah'      => $data['flacc_wajah']      ?? null,
        'kaki'       => $data['flacc_kaki']       ?? null,
        'aktivitas'  => $data['flacc_aktivitas']  ?? null,
        'menangis'   => $data['flacc_menangis']   ?? null,
        'dihibur'    => $data['flacc_dihibur']    ?? null,
    ]);
    $data['skor_flacc'] = $r['total'];   // null bila item belum lengkap
} else {
    $data['skor_flacc'] = null;
}
```

`NyeriHelper::flacc()` mengembalikan `['total' => int|null, 'kategori' => string]`:

```php
public static function flacc(array $a): array
{
    $total = 0; $terisi = 0;
    foreach (['wajah','kaki','aktivitas','menangis','dihibur'] as $k) {
        $v = $a[$k];
        if ($v === null || $v === '') { continue; }
        $total += (int) $v; $terisi++;
    }
    if ($terisi < 5) {
        return ['total' => null, 'kategori' => ''];
    }
    return [
        'total'    => $total,
        'kategori' => match (true) {
            $total === 0 => 'Tidak Nyeri',
            $total <= 3  => 'Nyeri Ringan',
            $total <= 6  => 'Nyeri Sedang',
            default      => 'Nyeri Berat',
        },
    ];
}
```

`flacc_wajah` … `flacc_dihibur` tidak dipetakan di `objek_form_control` (objek
`NULL`, PANDUAN §2.6) karena nilainya sudah terwakili `skor_flacc` (318).
**`filteredData()` wajib membaca field ini SEBELUM `array_intersect_key()`** —
pola yang sama dengan `ews_na` pada `EwsHelper` (lihat `AGENTS.md`, section
*EVM / Early Warning Score*).

### Mapping

```php
27 => [
    'tanggal_nyeri'      => 155,
    'waktu_nyeri'        => 156,
    'nyeri'              => 14,    // reuse
    'metode_skala_nyeri' => 319,
    'skor_nyeri'         => 140,   // reuse
    'skor_flacc'         => 318,
    'frekuensi_nyeri'    => 313,
    'karakteristik_nyeri'=> 314,
    'pemicu_nyeri'       => 315,
    'meringankan_nyeri'  => 316,
    'lokasi_nyeri'       => 317,
    'gambar_lokasi_nyeri'=> 326,   // share dgn form 37
    'intervensi_nyeri'   => 5,     // reuse Instruksi
    'catatan'            => 77,    // reuse
],
```

### Dashboard & Form Master

```php
['dashboard_menu_sub_id' => 113, 'dashboard_menu_id' => 9, 'nama_sub_menu' => 'Nyeri'],
['dashboard_menu_sub_extra_id' => 66, 'dashboard_menu_sub_id' => 113, 'nama_sub_menu_extra' => 'Penilaian Nyeri'],
['form_id' => 27, 'nama_form' => 'Penilaian Nyeri', 'slug' => 'penilaian_nyeri', 'id_dash_menu' => '9.113.66', 'ri' => 1, 'rj' => 1, 'igd' => 1, 'mcu' => 0],
```

### Akses EHR

```php
['profesi_id' => 1, 'form_id' => 27, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 2, 'form_id' => 27, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
```

### Validasi

```php
'tanggal_nyeri'       => 'required|date',
'waktu_nyeri'         => 'required|date_format:H:i',
'nyeri'               => 'required|in:Ya,Tidak',
'metode_skala_nyeri'  => 'nullable|required_if:nyeri,Ya|in:VAS,FLACC',
'skor_nyeri'          => 'nullable|required_if:metode_skala_nyeri,VAS|integer|between:0,10',
'frekuensi_nyeri'     => 'nullable|required_if:nyeri,Ya|in:Tidak Ada,Kadang,Sering,Jarang',
'karakteristik_nyeri' => 'nullable|required_if:nyeri,Ya|array|min:1',
'karakteristik_nyeri.*' => 'string',
'pemicu_nyeri'        => 'nullable|required_if:nyeri,Ya|string|max:1000',
'meringankan_nyeri'   => 'nullable|required_if:nyeri,Ya|string|max:1000',
'lokasi_nyeri'        => 'nullable|required_if:nyeri,Ya|string|max:200',
'intervensi_nyeri'    => 'nullable|required_if:nyeri,Ya|string|max:2000',
'catatan'             => 'nullable|string|max:1000',
```

Validasi tambahan `filteredData()`:

```php
// Catatan: JSON array; buang "Lainnya" bila teksnya kosong
if (($data['nyeri'] ?? '') !== 'Ya') {
    foreach (['frekuensi_nyeri','karakteristik_nyeri','pemicu_nyeri','meringankan_nyeri','lokasi_nyeri','skor_nyeri'] as $k) {
        unset($data[$k]);
    }
}
// Buang pasangan item FLACC bila metode bukan FLACC
if (($data['metode_skala_nyeri'] ?? '') !== 'FLACC') {
    unset($data['skor_flacc']);
}
```

### Cetak

`print.blade.php` **wajib** — pain scale adalah bagian dari pain chart yang
diaudit.

---

## 5. Metode Nyeri (form 36)

### Rasional

Form terpisah yang berfungsi sebagai **pemilih metode + penskalaan**. Legacy
`metode_nyeri.php` menampilkan VAS (radio 1..10 dengan gambar
`media/images/skala_nyeri.jpg`) atau FLACC (tabel 5 kategori × 3 opsi ber skor
0/1/2) sesuai radio metode. FLACC dihitung ulang di sisi klien lewat
`ceknilaiflacc()`.

NusaMedika memindahkan perhitungan ke server (PANDUAN §2.4) dan menjadikan form ini
**independen** — bisa diisi tanpaAssessment toggle form 27, berguna saat pasien
berpindah unit.

### Struktur Field

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `metode_skala_nyeri` | 319 | Metode Skala Nyeri | radio VAS/FLACC | ya | **Share dengan form 27** |
| `skor_nyeri` | 140 | Skor Nyeri | radio 0–10 | ya bila VAS | Reuse objek 140 |
| `flacc_wajah` | 320 | FLACC Wajah (Face) | radio 0/1/2 | ya bila FLACC | Objek baru |
| `flacc_kaki` | 321 | FLACC Kaki (Legs) | radio 0/1/2 | ya bila FLACC | Objek baru |
| `flacc_aktivitas` | 322 | FLACC Aktifitas (Activity) | radio 0/1/2 | ya bila FLACC | Objek baru |
| `flacc_menangis` | 323 | FLACC Menangis (Cry) | radio 0/1/2 | ya bila FLACC | Objek baru |
| `flacc_konsolabilitas` | 324 | FLACC Konsolabilitas | radio 0/1/2 | ya bila FLACC | Objek baru |
| `skor_flacc` | 325 | Total Skor FLACC | readonly (server) | ya bila FLACC | **Turunan**, objek baru |
| `interpretasi_nyeri` | 335 | Interpretasi Skor Nyeri | readonly (server) | ya | **Turunan**, objek baru |
| `keterangan` | 77 | Keterangan | textarea | tidak | Reuse objek 77 |

### Kriteria Penilaian FLACC (rekap dari `metode_nyeri.php:4-8`)

| Skor | Wajah (Face) | Kaki (Legs) | Aktifitas (Activity) | Menangis (Cry) | Konsolabilitas |
|---|---|---|---|---|---|
| 0 | Tersenyum / tidak ada ekspresi khusus | Gerakan normal / relaksasi | Tidur, posisi normal, mudah bergerak | Tidak menangis (bangun/tidur) | Rileks |
| 1 | Terkadang menangis / menarik diri | Tidak tenang / tegang | Gelongan menggeliat, berguling, kaku | Mengerang / merengek | Tenang bila dipeluk, digendong, atau diajak bicara |
| 2 | Sering menggetarkan dagu dan mengatupkan rahang | Kaki dibuat menendang / menarik diri | Melengkungkan punggung / kaku / menghentak | Menangis terus-menerus, terhisak, menjerit | Sulit untuk menenangkan |

### Interpretasi

| Total | Kategori |
|---|---|
| 0 | Tidak Nyeri |
| 1–3 | Nyeri Ringan |
| 4–6 | Nyeri Sedang |
| 7–10 | Nyeri Berat |

### Perhitungan Server-Side

```php
// MetodeNyeriController::filteredData()
$total = NyeriHelper::flacc([
    'wajah'     => $data['flacc_wajah']        ?? null,
    'kaki'      => $data['flacc_kaki']         ?? null,
    'aktivitas' => $data['flacc_aktivitas']    ?? null,
    'menangis'  => $data['flacc_menangis']     ?? null,
    'dihibur'   => $data['flacc_konsolabilitas'] ?? null,
]);
$data['skor_flacc']         = $total['total'];
$data['interpretasi_nyeri'] = $total['kategori'] ?: $this->kategoriVas((int) ($data['skor_nyeri'] ?? -1));
```

`kategoriVas()` mengikuti ambang yang sama (0 tidak nyeri, 1–3 ringan, 4–6 sedang,
7–10 berat) sehingga kedua metode punya label yang konsisten.

### Mapping

```php
36 => [
    'metode_skala_nyeri'    => 319,   // share dgn form 27
    'skor_nyeri'            => 140,   // reuse
    'flacc_wajah'           => 320,
    'flacc_kaki'            => 321,
    'flacc_aktivitas'       => 322,
    'flacc_menangis'        => 323,
    'flacc_konsolabilitas'  => 324,
    'skor_flacc'            => 325,
    'interpretasi_nyeri'    => 335,
    'keterangan'            => 77,    // reuse
],
```

### Dashboard & Form Master

```php
['dashboard_menu_sub_extra_id' => 67, 'dashboard_menu_sub_id' => 113, 'nama_sub_menu_extra' => 'Metode Nyeri'],
['form_id' => 36, 'nama_form' => 'Metode Nyeri', 'slug' => 'metode_nyeri', 'id_dash_menu' => '9.113.67', 'ri' => 1, 'rj' => 1, 'igd' => 1, 'mcu' => 0],
```

### Akses EHR

```php
['profesi_id' => 1, 'form_id' => 36, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 2, 'form_id' => 36, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
```

### Validasi

```php
'metode_skala_nyeri'   => 'required|in:VAS,FLACC',
'skor_nyeri'           => 'nullable|required_if:metode_skala_nyeri,VAS|integer|between:0,10',
'flacc_wajah'          => 'nullable|required_if:metode_skala_nyeri,FLACC|integer|between:0,2',
'flacc_kaki'           => 'nullable|required_if:metode_skala_nyeri,FLACC|integer|between:0,2',
'flacc_aktivitas'      => 'nullable|required_if:metode_skala_nyeri,FLACC|integer|between:0,2',
'flacc_menangis'       => 'nullable|required_if:metode_skala_nyeri,FLACC|integer|between:0,2',
'flacc_konsolabilitas' => 'nullable|required_if:metode_skala_nyeri,FLACC|integer|between:0,2',
'keterangan'           => 'nullable|string|max:1000',
```

### Cetak

`print.blade.php` **wajib** — VAS/FACE scale legend perlu tercetak agar pasien
dapat menunjuk angka.

---

## 6. Body Map Nyeri (form 37)

### Rasional

Pasien menandai lokasi nyeri pada gambar tubuh. Legacy memakai **EaselJS**
(`assets/vendor/nyeri_tubuh/easeljs-NEXT.combined.js`) dengan `<canvas
id="myCanvas" width="200" height="200">` di atas sprite `tubuh.png`, dan hasilnya
disimpan di ARSIP sebagai file teks (bukan di `emr_detail`) — lihat
`nyeri_cak.php:495-520` yang membaca
`STORED/{CLIENT}/ARSIP/PENGKAJIAN_NYERI_AWAL/{emr_id}.txt`.

NusaMedika **menyimpan gambar sebagai data URL base64 di dalam
`emr_detail.value`** (kolom `TEXT`,kapasitas 64 KB MySQL — data URL PNG dari
gambar 550×160 Fairuz *menggunakan* ~8–25 KB setelah dikompresi). Ini
menghilangkan kebutuhan folder ARSIP dan membuat data ikut tercetak serta ikut
ter-backup bersama `emr_detail`.

### Struktur Field

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `tanggal_body_map` | 155 | Tanggal Observasi | date | ya | Reuse |
| `waktu_body_map` | 156 | Waktu Observasi | time | ya | Reuse |
| `gambar_lokasi_nyeri` | 326 | Gambar Lokasi Nyeri | canvas → PNG data URL | ya | Objek baru; **share dengan form 27** |
| `keterangan_lokasi` | 327 | Keterangan Lokasi Nyeri | textarea | ya | Objek baru |
| `sisi_lokasi` | 327 | Keterangan Lokasi Nyeri | radio (Kiri/Kanan/Tengah/Bilateral) | ya | **Share objek dengan keterangan** |
| `catatan` | 77 | Keterangan | textarea | tidak | Reuse |

> Objek 327 dipakai dua variabel (`keterangan_lokasi`, `sisi_lokasi`) — aman
> untuk `emrDetailByVariabel()`, **berbahaya** untuk `emrDetailByObjek()`.

### Aturan Batas Ukuran

```php
'gambar_lokasi_nyeri' => 'nullable|string|max:60000',
```

Validasi tambahan: awali data URL dengan `data:image/png;base64,` bila ada isi;
hapus bila kosong. Kompresi sisi klien sebelum submit:

```js
const out = document.createElement('canvas');
out.width = canvas.width / 2;          // gambar tubuh 550x160 -> 275x80
out.height = canvas.height / 2;
out.getContext('2d').drawImage(canvas, 0, 0, out.width, out.height);
document.getElementById('gambar_lokasi_nyeri').value =
    out.toDataURL('image/png');        // ~8-15 KB
```

Aset yang perlu disalin ke `public/`:

- `public/img/nyeri/tubuh.png` (dari `assets/vendor/nyeri_tubuh/tubuh.png`)
- `public/img/nyeri/skala_nyeri.jpg` (dari `media/images/skala_nyeri.jpg`)

### Dashboard & Form Master

```php
['dashboard_menu_sub_id' => 114, 'dashboard_menu_id' => 9, 'nama_sub_menu' => 'Body Map Nyeri'],
['form_id' => 37, 'nama_form' => 'Body Map Nyeri', 'slug' => 'body_map_nyeri', 'id_dash_menu' => '9.114', 'ri' => 1, 'rj' => 1, 'igd' => 1, 'mcu' => 0],
```

### Akses EHR

```php
['profesi_id' => 1, 'form_id' => 37, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 2, 'form_id' => 37, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
```

### Validasi

```php
'tanggal_body_map'   => 'required|date',
'waktu_body_map'     => 'required|date_format:H:i',
'gambar_lokasi_nyeri'=> 'required|string|max:60000',
'keterangan_lokasi'  => 'required|string|max:500',
'sisi_lokasi'        => 'required|in:Kiri,Kanan,Tengah,Bilateral',
'catatan'            => 'nullable|string|max:1000',
```

### Cetak

`print.blade.php` **wajib** — gambar lokasisteil dari pain chart.

---

## 7. Skrining Nyeri Perina (form 38)

### Rasional

**NIPS — Neonatal Infant Pain Scale** untuk neonatus (legacy
`skrining_nyeri_perina.php`, 6 parameter). Skala ini dipakai pada prosedur
tidak nyaman seperti lekta Suntik, taking darah, +/- suction.

### Struktur Field

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `tanggal_nips` | 155 | Tanggal Observasi | date | ya | Reuse |
| `waktu_nips` | 156 | Waktu Observasi | time | ya | Reuse |
| `nips_ekspresi_wajah` | 328 | NIPS Ekspresi Wajah | radio 0/1 | ya | Objek baru |
| `nips_tangisan` | 329 | NIPS Tangisan | radio 0/1/2 | ya | Objek baru |
| `nips_pola_napas` | 330 | NIPS Pola Napas | radio 0/1 | ya | Objek baru |
| `nips_gerakan_lengan` | 331 | NIPS Gerakan Lengan | radio 0/1 | ya | Objek baru |
| `nips_gerakan_tungkai` | 332 | NIPS Gerakan Tungkai | radio 0/1 | ya | Objek baru |
| `nips_status_jaga` | 333 | NIPS Status Jaga | radio 0/1 | ya | Objek baru |
| `total_nips` | 334 | Total Skor NIPS | readonly (server) | ya | **Turunan**, objek baru |
| `interpretasi_nyeri` | 335 | Interpretasi Skor Nyeri | readonly (server) | ya | **Turunan**, share dengan form 36 |
| `catatan` | 77 | Keterangan | textarea | tidak | Reuse |

### Kriteria Penilaian NIPS (verbatim dari `skrining_nyeri_perina.php:2-15`)

| Parameter | Respon 0 | Respon 1 | Respon 2 |
|---|---|---|---|
| **Ekspresi Wajah** | Relaksasi — wajah tenang, ekspresi netral | Meringis — otot wajah tegang, alis berkerut | — |
| **Tangisan** | Tidak menangis (tenang) | Meringis (merengek lemah, intermiten) | Menangis (menjerit, menangis terus menerus) |
| **Pola Napas** | Relaksasi — bernapas biasa | Perubahan pola napas — tarikan irregular, lebih cepat dari biasa | — |
| **Gerakan Lengan** | Relaksasi — tidak ada kekuatan otot / gerakan | Fleksi/Ekstensi — tegang, kaku | — |
| **Gerakan Tungkai** | Relaksasi — tidak ada kekuatan otot / gerakan | Fleksi/Ekstensi — tegang, kaku | — |
| **Status Jaga** | Tidur/Bangun — tenang | Rewel — gelisah | — |

**Total skor maksimal = 7** (0+2+1+1+1+2 = 7; legacy menulis literal
"Total skor maksimal : 7" di tabel interpretasi).

### Interpretasi

| Total | Interpretasi | Tindakan |
|---|---|---|
| 0–2 | Tidak nyeri – nyeri ringan | Monitoring |
| 3–4 | Nyeri ringan – sedang | Manajemen nyeri non-farmakologi **dengan pengkajian ulang menit ke-30** |
| > 4 | Nyeri berat | Manajemen nyeri non-farmakologi, **dengan pengkajian ulang menit ke-30** |

### Perhitungan Server-Side

```php
// SkriningNyeriPerinaController::filteredData()
$item = ['nips_ekspresi_wajah','nips_tangisan','nips_pola_napas',
         'nips_gerakan_lengan','nips_gerakan_tungkai','nips_status_jaga'];

$total = 0; $terisi = 0;
foreach ($item as $v) {
    $n = $data[$v] ?? null;
    if ($n === null || $n === '') { continue; }
    $total += (int) $n; $terisi++;
}

// Hanya simpan bila 6/6 terisi — skor parsial menyesatkan (pola EWS).
$data['total_nips']          = $terisi === 6 ? $total : null;
$data['interpretasi_nyeri']  = match (true) {
    $terisi < 6         => null,
    $total <= 2         => 'Tidak Nyeri - Nyeri Ringan',
    $total <= 4         => 'Nyeri Ringan - Sedang',
    default             => 'Nyeri Berat',
};
```

> Field interpretasi **tidak** memuat jadwal pengkajian ulang menit ke-30 —
> jadwal itu masuk ke `catatan` (objek 77) oleh perawat, bukan dipaksa server.

### Mapping

```php
38 => [
    'tanggal_nips'        => 155,
    'waktu_nips'          => 156,
    'nips_ekspresi_wajah' => 328,
    'nips_tangisan'       => 329,
    'nips_pola_napas'     => 330,
    'nips_gerakan_lengan' => 331,
    'nips_gerakan_tungkai'=> 332,
    'nips_status_jaga'    => 333,
    'total_nips'          => 334,
    'interpretasi_nyeri'  => 335,
    'catatan'             => 77,
],
```

### Dashboard & Form Master

```php
['dashboard_menu_sub_extra_id' => 68, 'dashboard_menu_sub_id' => 113, 'nama_sub_menu_extra' => 'Skrining Nyeri Perina'],
['form_id' => 38, 'nama_form' => 'Skrining Nyeri Perina', 'slug' => 'skrining_nyeri_perina', 'id_dash_menu' => '9.113.68', 'ri' => 1, 'rj' => 0, 'igd' => 0, 'mcu' => 0],
```

### Akses EHR

```php
['profesi_id' => 1, 'form_id' => 38, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 2, 'form_id' => 38, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
```

### Validasi

```php
'tanggal_nips'        => 'required|date',
'waktu_nips'          => 'required|date_format:H:i',
'nips_ekspresi_wajah' => 'required|integer|between:0,1',
'nips_tangisan'       => 'required|integer|between:0,2',
'nips_pola_napas'     => 'required|integer|between:0,1',
'nips_gerakan_lengan' => 'required|integer|between:0,1',
'nips_gerakan_tungkai'=> 'required|integer|between:0,1',
'nips_status_jaga'    => 'required|integer|between:0,1',
'catatan'             => 'nullable|string|max:1000',
```

### Cetak

`print.blade.php` **wajib** — dipakai pada lembar prosedur neonatus.

---

## 8. Implementasi

### Seeder (`database/seeders/EmrMasterSeeder.php`)

- [ ] `$subMenus` — tambah `113 Nyeri (menu 9)`, `114 Body Map Nyeri (menu 9)`
- [ ] `$extras` — tambah `66 Penilaian Nyeri`, `67 Metode Nyeri`, `68 Skrining Nyeri Perina`
      (menu 9 dan sub 94–95 sudah dibuat di `KONSEP_CAIRAN_BALANCE.md`)
- [ ] `$forms` — tambah 4 baris (27, 36, 37, 38)
- [ ] `$objeks` — tambah 23 baris (313–335)
- [ ] `$mapping` — tambah 4 blok
- [ ] `EmrHelper::backfillObjekId(27)`, `(36)`, `(37)`, `(38)`
- [ ] `$akses` — tambah 8 baris

### Helper

- [ ] `app/Helpers/NyeriHelper.php`
      - `flacc(array $a): array` (total + kategori)
      - `kategoriVas(int $skor): string`
      - `nips(array $a): array` (total + interpretasi)
      - `kategori(int $skor): string` — dipakai juga oleh form 35 (kritis)

### Controller

- [ ] `app/Http/Controllers/EMR/PenilaianNyeri/PenilaianNyeriController.php`
- [ ] `app/Http/Controllers/EMR/MetodeNyeri/MetodeNyeriController.php`
- [ ] `app/Http/Controllers/EMR/BodyMapNyeri/BodyMapNyeriController.php`
- [ ] `app/Http/Controllers/EMR/SkriningNyeriPerina/SkriningNyeriPerinaController.php`

### View

- [ ] `resources/views/moduls/EMR/{PenilaianNyeri,MetodeNyeri,BodyMapNyeri,SkriningNyeriPerina}/index.blade.php`
- [ ] `print.blade.php` untuk keempat form
- [ ] Partial baru `PartialForm/_flacc_scale.blade.php` — dipakai form 27, 36, 35
- [ ] Partial baru `PartialForm/_vas_scale.blade.php` — dipakai form 27, 36
- [ ] Partial baru `PartialForm/_body_map_nyeri.blade.php` — dipakai form 27, 37
- [ ] Partial yang **sudah ada** tapi perlu diperluas: `PartialForm/_pengkajian_nyeri.blade.php`
      dan `PartialForm/_observasi_harian_penilaian_nyeri.blade.php` — keduanya
      saat ini hanya punya field `nyeri`; tambahkan tautan ke form 27.

### Aset

- [ ] `public/img/nyeri/tubuh.png`
- [ ] `public/img/nyeri/skala_nyeri.jpg`
- [ ] `public/vendor/nyeri_tubuh/easeljs-NEXT.combined.js` (atau ganti ke
      Canvas API murni — rekomendasi, tanpa dependensi third-party)

### SelectOption

- [ ] Tambah key: `frekuensi_nyeri`, `karakteristik_nyeri`, `metode_skala_nyeri`,
      `sisi_lokasi`

### Dokumentasi

- [ ] `AGENTS.md` — entri form 27, 36, 37, 38 + catatan `NyeriHelper`
- [ ] `docs/ANALISIS_KEKURANGAN_FORM.md` §6
- [ ] `docs/PENGKAJIAN_RISIKO_JATUH.md` — tidak berubah

### Verifikasi

```bash
docker compose up -d db
docker compose exec app php artisan migrate:fresh --seed
docker compose exec app ./vendor/bin/pint --dirty
docker compose exec app php artisan test
```

Uji khusus FLACC: isi 5 item → `skor_flacc` = jumlah; kosongkan 1 item →
`skor_flacc` harus `NULL` (bukan 0). Uji NIPS: total 0,3,5 menghasilkan tiga
label berbeda.

---

## 9. Catatan & Risiko

| # | Risiko | Dampak | Mitigasi |
|---|---|---|---|
| R1 | **Skor 0 vs "belum diisi".** Legacy memakai JS `ceknilaiflacc()` yang meng-default `0` bila radio belum dipilih — sehingga "belum dinilai" dan "skor 0" tercampur. | Skor 0 palsu pada pasien yang belum dinilai. | Server hanya menyimpan skor bila **5/5 (FLACC) atau 6/6 (NIPS) terisi**; selain itu `NULL`. Persis aturan yang dipakai `EwsHelper`. |
| R2 | **Field FLACC form 27 tidak ada di `objek_form_control`.** | `array_intersect_key()` di `filteredData()` membuangnya sebelum sempat dipakai. | Baca `flacc_*` **sebelum** `array_intersect_key()` (pola `ews_na`). |
| R3 | **Data URL di `emr_detail.value` bertipe `TEXT` (64 KB).** | Gambar besar gagal disimpan / terpotong. | Kompresi klien (setengah ukuran), validasi `max:60000`, dan tolak bila awalan bukan `data:image/png;base64,`. |
| R4 | **Legacy menyimpan body map di folder ARSIP filesystem, bukan DB.** | Data hilang bila folder tidak di-backup. | Disimpan di DB — lebih baik untuk integritas, tetapi `export`/backup harus menyertakan `emr_detail`. |
| R5 | **Objek 327 dipakai dua variabel (`keterangan_lokasi`, `sisi_lokasi`).** | `emrDetailByObjek()` akan menimpa. | Jangan pakai `emrDetailByObjek()` pada form 37. Bila laporan butuh, alokasikan objek baru di rentang setelah 335 (di luar alokasi dokumen ini). |
| R6 | **Objek 319, 326, 335 dipakai bersama antar form.** | Laporan harus memfilter `form_id` agar tidak menjumlahkan dobel. | `EmrHelper::objekVariabels()` sudah berbasis `form_id`; query laporan wajib menyertakan filter `form_id`. |
| R7 | **Nomor objek legacy (17, 18, 19, 20, 1101, 498, 499, 503, 809, 810, 811, 161, 248, 249, 160) tidak boleh dipakai.** | Data tercampur antar form. | Selalu pakai objek NusaMedika. Nomor legacy hanya dirujuk di kolom keterangan. |
| R8 | **`<canvas>` tidak ada di `layouts.iframe` `@stack('scripts')`.** | JS partial tidak jalan. | `layouts.iframe` **tidak** merender `@stack('scripts')` — JS partial harus `<script>` inline (lihat `AGENTS.md`, section *Pengkajian Risiko Jatuh*). |
| R9 | **Tailwind v4 `.hidden` kalah dari `inline-flex`.** | Panel VAS/FLACC tidak menyembunyi saat metode diganti. | Gunakan helper `tampilkan(el, tampil, display)` seperti di `TindakanMedis/index.blade.php`: set `style.display` **dan** lepas/pasang class `hidden`. |
| R10 | **Komponen blade di dalam `<script>`.** | Script rusak total tanpa error jelas. | Jangan menulis `<x-confirm-alert />` di dalam blok `<script>` maupun komentarnya — tulis "komponen confirm-alert". |
| R11 | **FLACC diduplikasi di 3 form.** | Ambang kategorikan bisa berbeda antar form. | Satu helper `NyeriHelper::flacc()` dipakai form 27, 36, dan 35. |
| R12 | **Form 38 tanpa akses untuk RJ.** | FLAG `rj=0` disengaja (NIPS hanya neonatus di RI). | Bila poli neonatal memiliki layanan RJ, ubah flag di seeder dan sekalian tambahkan akses. |
| R13 | **Label objek generik** (mis. "Interpretasi Skor Nyeri" dipakai VAS & NIPS). | Ambigu saat filter objek di `Administrator/ManajemenEMR/Form`. | Nama objek sudah cukup generik; tambahkan keterangan pada form mapping. Bila perlu, pisahkan objek interpretasi VAS vs NIPS pada rentang berikutnya. |
| R14 | **Body map memakai aset body proprietary.** | Lisensi gambar tubuh. | Pastikan lisensi aset `tubuh.png` bebas pakai komersial; bila tidak, ganti dengan SVG garis yang digambar sendiri. |