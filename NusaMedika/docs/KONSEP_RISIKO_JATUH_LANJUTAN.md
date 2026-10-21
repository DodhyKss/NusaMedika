# Konsep Monitoring & Penilaian Risiko Jatuh Lanjutan (EMR)

**Status:** Rancangan / Konsep — belum diimplementasikan
**Tanggal:** 2026-10-10
**Acuan:** [`PANDUAN_IMPLEMENTASI_FORM_EMR.md`](PANDUAN_IMPLEMENTASI_FORM_EMR.md),
[`PENGKAJIAN_RISIKO_JATUH.md`](PENGKAJIAN_RISIKO_JATUH.md),
[`ANALISIS_KEKURANGAN_FORM.md`](ANALISIS_KEKURANGAN_FORM.md)
**Sumber legacy:** `/simrs_tenriawaru/FE/lib/modul/`

---

## 1. Ringkasan

| form_id | nama_form | slug | id_dash_menu | ri | rj | igd | mcu |
|---|---|---|---|---|---|---|---|
| 30 | Monitoring & Evaluasi Risiko Jatuh | `monitoring_risiko_jatuh` | `8.133.86` | 1 | 0 | 0 | 0 |
| 32 | Penilaian Risiko Dekubitus (Norton) | `penilaian_risiko_dekubitus` | `8.134.87` | 1 | 0 | 0 | 0 |
| 33 | Barthel Index | `barthel_index` | `8.134.88` | 1 | 0 | 0 | 0 |

Objek baru yang dialokasikan: **336 – 361** (26 objek). Objek lama yang
di-*reuse*: 3 (Assessment), 5 (Instruksi), 6/7/8/9 (TD, BB, TB), 10/11/12,
15 (Saturasi), 51 (Kesadaran), 54–57 (GCS), 77 (Keterangan), 89–93 (ringkasan
risiko jatuh), 94–100 (HDS), 101–106 (MFS), 107–112 (Sydney), 113 (TUG),
155/156 (Tanggal/Waktu Observasi).

> Objek 89–113 **sudah ada** dan dipakai form 3 (Pengkajian Awal) serta form 4
> (Pengkajian Harian). Form 30 mem-*reuse* seluruhnya agar laporan risiko jatuh
> dapat menjumlahkan dan membandingkan antar waktu.

### 1.1 Struktur menu dashboard

```
dashboard_menu 8  "Penilaian Klinis"   (menu baru, dibuat di KONSEP_ASUHAN_KEPERAWATAN_LANJUTAN.md)
├── sub 76  "Asesmen Pasien"      → form 34, 125, 123, 124
├── sub 77  "Anamnesis"           → form 55, 56, 54
├── sub 133 "Risiko Jatuh"
│   └── extra 86 "Monitoring Risiko Jatuh"  → form 30 (8.133.86)
└── sub 134 "Penilaian Dekubitus"
    ├── extra 87 "Penilaian Risiko Dekubitus" → form 32 (8.134.87)
    └── extra 88 "Barthel Index"              → form 33 (8.134.88)
```

> `dashboard_menu_sub_extra` 86–88 milik dokumen ini (26–45 milik
> `KONSEP_ASUHAN_KEPERAWATAN_LANJUTAN.md`, 46–47 milik Cairan,
> 66–68 milik Nyeri). Band sub dokumen ini = **133–152**
> (`ALOKASI_ID_GLOBAL.md` §3). `dashboard_menu_sub_id` bersifat **global** —
> sub 1–12 milik menu 1–5 dan tidak boleh dipakai ulang.

---

## 2. Sumber Referensi Legacy

| Form | Berkas legacy | Isi |
|---|---|---|
| 30 Monitoring & Evaluasi Risiko Jatuh | `FE/lib/modul/monitoring_dan_evaluasi_risiko_jatuh.php`, `evaluasi_risiko_jatuh_humpty_dumpty.php`, `evaluasi_risiko_jatuh_morse.php`, `evaluasi_risiko_jatuh_sydney.php`, `penatalaksanaan_pencegahan_risiko_jatuh.php` | Assesmen ulang berbasis usia + checklist intervensi 6 butir |
| 32 Penilaian Risiko Dekubitus | `FE/lib/modul/score_norton.php` (199 baris) | Norton Scale 5 item × 4 opsi |
| 33 Barthel Index | `FE/lib/modul/barthel_index.php` (683 baris) | Barthel Index 10 item + interpretasi |
| (pendukung) | `FE/lib/modul/asesmen_ulang_risiko_jatuh.php`, `asesmen_ulang_risiko_jatuh_HD.php` | Riwayat penilaian |

---

## 3. Monitoring & Evaluasi Risiko Jatuh (form 30)

### Rasional

NusaMedika **sudah** punya pengkajian risiko jatuh berbasis usia di form 3 dan 4
(`App\Helpers\RisikoJatuhHelper`: HDS < 14 tahun, MFS 14–59, TUG ≥ 60, Sydney
sebagai alternatif). Yang belum ada adalah **monitoring lanjutan**: apakah
risikonya berubah, apakah ada kejadian jatuh, dan apakah intervensi benar-benar
dijalankan.

Legacy `monitoring_dan_evaluasi_risiko_jatuh.php` sudah melakukan pemisahan
instrumen berdasarkan usia:

```php
// monitoring_dan_evaluasi_risiko_jatuh.php:28-34
if ($umur_pasien >= 0  && $umur_pasien <= 17)      include 'evaluasi_risiko_jatuh_humpty_dumpty.php';
else if ($umur_pasien >= 18 && $umur_pasien <= 59) include 'evaluasi_risiko_jatuh_morse.php';
else if ($umur_pasien >= 60)                      include 'evaluasi_risiko_jatuh_sydney.php';
```

Perbedaannya dengan `RisikoJatuhHelper`: batas usia legacy **17 / 18–59 / ≥ 60**,
sedangkan helper NusaMedika **≤ 13 / 14–59 / ≥ 60**. NusaMedika memakai versi
helper (sudah diuji dan didokumentasikan di `PENGKAJIAN_RISIKO_JATUH.md`), dan
form 30 cukup memanggil `RisikoJatuhHelper::instrumenUntukUsia()` agar konsisten.

`penatalaksanaan_pencegahan_risiko_jatuh.php` memuat **6 butir intervensi** Ya/Tidak:

1. Pastikan Stiker Kuning Terpasang pada Gelang
2. Informasi Pasa Pasien dan Keluarga
3. Pastikan Terpasang Hand Rail
4. Kondisi Lantai Tidak Licin
5. Melakukan Penilaian Ulang Risiko Jatuh Tiap Shift
6. Kondisi Kamar Mandi Tidak Terkunci

### Struktur Field

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `tanggal_monitoring_jatuh` | 336 | Tanggal Monitoring Risiko Jatuh | date | ya | Objek baru |
| `waktu_monitoring_jatuh` | 337 | Waktu Monitoring Risiko Jatuh | time | ya | Objek baru |
| `risiko_jatuh_instrumen` | 89 | Risiko Jatuh - Instrumen | select | ya | **Reuse** (HDS/MFS/TUG/SYDNEY) |
| `risiko_jatuh_skor` | 90 | Risiko Jatuh - Skor | readonly (server) | ya | **Reuse**, turunan `RisikoJatuhHelper::hitung()` |
| `risiko_jatuh_label` | 91 | Risiko Jatuh - Tingkat Risiko | readonly (server) | ya | **Reuse** |
| `risiko_jatuh_intervensi` | 92 | Risiko Jatuh - Intervensi | readonly (server) | ya | **Reuse** |
| `risiko_jatuh_ringkasan` | 93 | Risiko Jatuh - Ringkasan | readonly (server) | ya | **Reuse** |
| `hds_1..7` | 94–100 | HDS 1–7 | radio | sesuai instrumen | **Reuse** |
| `mfs_1..6` | 101–106 | MFS 1–6 | radio | sesuai instrumen | **Reuse** |
| `syd_*` | 107–112 | Sydney TS/MS | radio | sesuai instrumen | **Reuse** |
| `tug_detik` | 113 | Durasi Timed Up and Go | number | sesuai instrumen | **Reuse** |
| `status_monitoring` | 338 | Status Monitoring Risiko Jatuh | radio | ya | Objek baru |
| `kejadian_jatuh` | 339 | Kejadian Jatuh | radio Ya/Tidak | ya | Objek baru |
| `waktu_kejadian_jatuh` | 340 | Waktu Kejadian Jatuh | date + time | ya bila Ya | Objek baru |
| `keterangan_kejadian_jatuh` | 341 | Keterangan Kejadian Jatuh | textarea | ya bila Ya | Objek baru |
| `tindak_lanjut_jatuh` | 342 | Tindak Lanjut Kejadian Jatuh | textarea | ya bila Ya | Objek baru |
| `intervensi_pencegahan` | 92 | Risiko Jatuh - Intervensi | checkbox group | ya | **Reuse objek 92** — JSON 6 butir |
| `berat_badan` | 8 | Berat Badan | number | tidak | **Reuse** — untuk ringkasan |
| `kesadaran` | 51 | Kesadaran | radio | tidak | **Reuse** |
| `catatan` | 77 | Keterangan | textarea | tidak | **Reuse** |

### Opsi

**Status Monitoring** (objek 338):

| Nilai | Arti |
|---|---|
| `Membaik` | skor turun dibanding penilaian sebelumnya |
| `Tetap` | skor sama |
| `Memburuk` | skor naik dibanding penilaian sebelumnya |

**Intervensi Pencegahan** (objek 92, JSON array — 6 butir legacy):

1. Pastikan Stiker Kuning Terpasang pada Gelang
2. Informasi Pasa Pasien dan Keluarga
3. Pastikan Terpasang Hand Rail
4. Kondisi Lantai Tidak Licin
5. Melakukan Penilaian Ulang Risiko Jatuh Tiap Shift
6. Kondisi Kamar Mandi Tidak Terkunci

> Objek 92 dipakai dua variabel: `risiko_jatuh_intervensi` (teks intervensi dari
> `RisikoJatuhHelper`) dan `intervensi_pencegahan` (JSON checklist). Aman untuk
> `emrDetailByVariabel()`, **berbahaya** untuk `emrDetailByObjek()`.

### Perhitungan Server-Side

```php
// MonitoringRisikoJatuhController::filteredData()
$usia = RisikoJatuhHelper::hitungUsia($registrasi_detail->registrasi->pasien->tgl_lahir);

// Instrumen yang dihitung server — nilai `risiko_jatuh_instrumen` dari browser
// dipakai HANYA bila `deteksiInstrumen()` tidak menemukan jawaban item.
$kode = $data['risiko_jatuh_instrumen'] ?: RisikoJatuhHelper::deteksiInstrumen($data);

$hasil = RisikoJatuhHelper::hitung($kode, $data, $usia);

$data['risiko_jatuh_instrumen'] = $hasil['instrumen'];
$data['risiko_jatuh_skor']      = $hasil['skor'];
$data['risiko_jatuh_label']     = $hasil['label'];
$data['risiko_jatuh_intervensi'] = $hasil['intervensi'];
$data['risiko_jatuh_ringkasan']  = RisikoJatuhHelper::ringkasan($hasil);
```

`status_monitoring` **tidak** dihitung server karena memerlukan perbandingan
dengan penilaian sebelumnya; dihitung di server dengan query:

```php
$sebelum = EmrHelper::latestValuesByVariabel(
    $form_id,
    $registrasi_detail_id,
    ['risiko_jatuh_skor', 'risiko_jatuh_label']
);
// bandingkan dengan skor emr yang sedang disimpan
$data['status_monitoring'] = match (true) {
    $sebelum === [] || $data['risiko_jatuh_skor'] === null => null,
    $data['risiko_jatuh_skor'] <  (float) $sebelum['risiko_jatuh_skor'] => 'Membaik',
    $data['risiko_jatuh_skor'] >  (float) $sebelum['risiko_jatuh_skor'] => 'Memburuk',
    default => 'Tetap',
};
```

### Mapping

```php
30 => [
    'tanggal_monitoring_jatuh' => 336,
    'waktu_monitoring_jatuh'   => 337,

    'risiko_jatuh_instrumen' => 89,
    'risiko_jatuh_skor'      => 90,
    'risiko_jatuh_label'     => 91,
    'risiko_jatuh_intervensi'=> 92,
    'risiko_jatuh_ringkasan' => 93,

    'hds_1' => 94, 'hds_2' => 95, 'hds_3' => 96, 'hds_4' => 97,
    'hds_5' => 98, 'hds_6' => 99, 'hds_7' => 100,
    'mfs_1' => 101, 'mfs_2' => 102, 'mfs_3' => 103,
    'mfs_4' => 104, 'mfs_5' => 105, 'mfs_6' => 106,
    'syd_ts_bed' => 107, 'syd_ts_bangku' => 108, 'syd_ts_bantuan' => 109,
    'syd_ms_bantuan' => 110, 'syd_ms_kursi_roda' => 111, 'syd_ms_imobil' => 112,
    'tug_detik' => 113,

    'status_monitoring'        => 338,
    'kejadian_jatuh'           => 339,
    'waktu_kejadian_jatuh'     => 340,
    'keterangan_kejadian_jatuh'=> 341,
    'tindak_lanjut_jatuh'      => 342,
    'intervensi_pencegahan'    => 92,   // share dgn risiko_jatuh_intervensi
    'berat_badan'              => 8,    // reuse
    'kesadaran'                => 51,   // reuse
    'catatan'                  => 77,   // reuse
],
```

### Dashboard & Form Master

```php
['dashboard_menu_sub_id' => 133, 'dashboard_menu_id' => 8, 'nama_sub_menu' => 'Risiko Jatuh'],
['dashboard_menu_sub_extra_id' => 86, 'dashboard_menu_sub_id' => 133, 'nama_sub_menu_extra' => 'Monitoring Risiko Jatuh'],
['form_id' => 30, 'nama_form' => 'Monitoring & Evaluasi Risiko Jatuh', 'slug' => 'monitoring_risiko_jatuh', 'id_dash_menu' => '8.133.86', 'ri' => 1, 'rj' => 0, 'igd' => 0, 'mcu' => 0],
```

### Akses EHR

```php
['profesi_id' => 1, 'form_id' => 30, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 0],
['profesi_id' => 2, 'form_id' => 30, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 0],
```

> `akses_delete = 0` — monitoring risiko jatuh bagian dari rekam medis yang tidak
> boleh dihapus (persyaratan akreditasi).

### Validasi

```php
'tanggal_monitoring_jatuh'    => 'required|date',
'waktu_monitoring_jatuh'      => 'required|date_format:H:i',
'risiko_jatuh_instrumen'      => 'nullable|in:HDS,MFS,TUG,SYDNEY',
'status_monitoring'           => 'nullable|in:Membaik,Tetap,Memburuk',
'kejadian_jatuh'              => 'required|in:Ya,Tidak',
'waktu_kejadian_jatuh'        => 'nullable|required_if:kejadian_jatuh,Ya|date',
'keterangan_kejadian_jatuh'   => 'nullable|required_if:kejadian_jatuh,Ya|string|max:1000',
'tindak_lanjut_jatuh'         => 'nullable|required_if:kejadian_jatuh,Ya|string|max:1000',
'intervensi_pencegahan'       => 'required|array|min:1',
'intervensi_pencegahan.*'     => 'string',
'berat_badan'                 => 'nullable|numeric|between:1,400',
'catatan'                     => 'nullable|string|max:1000',
// jawaban item instrumen divalidasi per instrumen di filteredData(),
// bukan lewat $request->validate() — instrumen ditentukan server.
```

Validasi per instrumen di `filteredData()`:

```php
$def = RisikoJatuhHelper::definisi($kode);
if ($def) {
    foreach ($def['item'] as $item) {
        $v = $data[$item['var']] ?? null;
        if ($v === null || $v === '') {
            throw ValidationException::withMessages([
                $item['var'] => 'Item '.strtolower($item['label']).' wajib diisi untuk instrumen '.$def['kode'].'.',
            ]);
        }
    }
    // SYDNEY menyaring item yang tidak relevan
    $keep = [];
    foreach ($def['item'] as $item) { $keep[] = $item['var']; }
    foreach (array_diff(array_keys($data), $keep) as $buang) {
        if (str_starts_with($buang, 'hds_') || str_starts_with($buang, 'mfs_')
            || str_starts_with($buang, 'syd_') || $buang === 'tug_detik') {
            unset($data[$buang]);
        }
    }
}
```

> **Gotcha `radio` grup:** jangan hanya membaca radio **pertama** sebuah grup
> (`querySelectorAll(...)[0]`) lalu membaca lewat `.checked` — memilih opsi kedua
> membuat radio pertama ter-*uncheck* sehingga jawaban terbaca kosong. Ini bug
> yang sudah pernah terjadi (lihat `AGENTS.md`, section *Pengkajian Risiko Jatuh*).
> Simpan **seluruh grup** (`item.els = Array.prototype.slice.call(panel.querySelectorAll('[name="X"]'))`)
> lalu cari elemen yang `checked`.

### Cetak

`print.blade.php` **wajib** — form monitoring risiko jatuh yang ditandatangani
per shift.

---

## 4. Penilaian Risiko Dekubitus / Norton (form 32)

### Rasional

Skala **Norton** untuk risiko dekubitus, verbatim dari `score_norton.php`.
Lima item, tiap item 4 opsi dengan skor **menurun dari 4 ke 1** (`$skor =
count($val_norton); ... $skor--`), total **5–20**.

### Kriteria Penilaian Norton

| Item | 4 | 3 | 2 | 1 |
|---|---|---|---|---|
| **1. Kondisi Fisik Umum** | Baik | Lumayan | Buruk | Sangat Buruk |
| **2. Kesadaran** | Compos Mentis | Apatis | Konfus/Sopor | Stupor/Koma |
| **3. Aktivitas** | Ambulansi Normal | Ambulansi dengan Bantuan | Hanya Bisa Duduk | Tiduran |
| **4. Mobilitas** | Bergerak Bebas | Sedikit Terbatas | Sangat Terbatas | Tidak Bisa Bergerak |
| **5. Inkontinensia** | Tidak Ada | Kadang-kadang | Sering Inkontinensia Urine | Inkontinensia Alvi dan Urine |

### Ambang Kategori (verbatim `score_norton.php:132-155`)

| Total Skor | Kategori | Warna |
|---|---|---|
| < 12 | **Risiko Tinggi** Terjadi Dekubitus | cokelat |
| 12–15 | **Risiko Sedang** Terjadi Dekubitus | oranye |
| 16–20 | **Tidak Berisiko / Risiko Rendah** Terjadi Dekubitus | hijau lime |

> Perhatikan arahnya: **skor rendah = risiko tinggi** (sama dengan Barthel, tapi
> berkebalikan dari MFS).

### Struktur Field

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `tanggal_norton` | 155 | Tanggal Observasi | date | ya | Reuse objek 155 |
| `waktu_norton` | 156 | Waktu Observasi | time | ya | Reuse objek 156 |
| `norton_kondisi_fisik_umum` | 343 | Norton Kondisi Fisik Umum | radio 1–4 | ya | Objek baru |
| `norton_kesadaran` | 344 | Norton Kesadaran | radio 1–4 | ya | Objek baru |
| `norton_aktivitas` | 345 | Norton Aktivitas | radio 1–4 | ya | Objek baru |
| `norton_mobilitas` | 346 | Norton Mobilitas | radio 1–4 | ya | Objek baru |
| `norton_inkontinensia` | 347 | Norton Inkontinensia | radio 1–4 | ya | Objek baru |
| `skor_norton` | 348 | Total Skor Norton | readonly (server) | ya | **Turunan**, objek baru |
| `kategori_dekubitus` | 349 | Kategori Risiko Dekubitus | readonly (server) | ya | **Turunan**, objek baru |
| `kesadaran` | 51 | Kesadaran | radio | tidak | Reuse — konsistensi dengan form 13 |
| `catatan` | 77 | Keterangan | textarea | tidak | Reuse |

### Perhitungan Server-Side

```php
// PenilaianRisikoDekubitusController::filteredData()
$item = [
    'norton_kondisi_fisik_umum',
    'norton_kesadaran',
    'norton_aktivitas',
    'norton_mobilitas',
    'norton_inkontinensia',
];

$total = 0; $terisi = 0;
foreach ($item as $v) {
    $n = $data[$v] ?? null;
    if ($n === null || $n === '') { continue; }
    $total += (int) $n; $terisi++;
}

$data['skor_norton'] = $terisi === 5 ? $total : null;
$data['kategori_dekubitus'] = match (true) {
    $terisi < 5     => null,
    $total < 12     => 'Risiko Tinggi Terjadi Dekubitus',
    $total <= 15    => 'Risiko Sedang Terjadi Dekubitus',
    default         => 'Tidak Berisiko / Risiko Rendah Terjadi Dekubitus',
};
```

> Skor parsial **tidak disimpan** — 3/5 item bisa menghasilkan skor yang terlihat
> aman padahal belum lengkap. Pola sama dengan `EwsHelper` dan `NyeriHelper`.

### Mapping

```php
32 => [
    'tanggal_norton'            => 155,
    'waktu_norton'              => 156,
    'norton_kondisi_fisik_umum' => 343,
    'norton_kesadaran'          => 344,
    'norton_aktivitas'          => 345,
    'norton_mobilitas'          => 346,
    'norton_inkontinensia'      => 347,
    'skor_norton'               => 348,
    'kategori_dekubitus'        => 349,
    'kesadaran'                 => 51,  // reuse
    'catatan'                   => 77,  // reuse
],
```

### Dashboard & Form Master

```php
['dashboard_menu_sub_id' => 134, 'dashboard_menu_id' => 8, 'nama_sub_menu' => 'Penilaian Dekubitus'],
['dashboard_menu_sub_extra_id' => 87, 'dashboard_menu_sub_id' => 134, 'nama_sub_menu_extra' => 'Penilaian Risiko Dekubitus'],
['form_id' => 32, 'nama_form' => 'Penilaian Risiko Dekubitus (Norton)', 'slug' => 'penilaian_risiko_dekubitus', 'id_dash_menu' => '8.134.87', 'ri' => 1, 'rj' => 0, 'igd' => 0, 'mcu' => 0],
```

### Akses EHR

```php
['profesi_id' => 1, 'form_id' => 32, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 0],
['profesi_id' => 2, 'form_id' => 32, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 0],
```

### Validasi

```php
'tanggal_norton'            => 'required|date',
'waktu_norton'              => 'required|date_format:H:i',
'norton_kondisi_fisik_umum' => 'required|integer|between:1,4',
'norton_kesadaran'          => 'required|integer|between:1,4',
'norton_aktivitas'          => 'required|integer|between:1,4',
'norton_mobilitas'          => 'required|integer|between:1,4',
'norton_inkontinensia'      => 'required|integer|between:1,4',
'kesadaran'                 => 'nullable',
'catatan'                   => 'nullable|string|max:1000',
```

### Cetak

`print.blade.php` **wajib** — hasil Norton dipakai menentukan frekuensi reposisi
dan terapi Matlabrium.

---

## 5. Barthel Index (form 33)

### Rasional

Barthel Index baku (Mahoney & Barthel 1965) untuk mengukur **kemandirian ADL**.
Legacy `barthel_index.php` memakai 10 item dengan skor **menurun** dari nilai
tertinggi tiap item (`$no = count($arr) - 1; ... $no--`), total maksimum **20**.

Legacy juga menyimpan waktu penilaian (`tgl_pertemuan` objek 44, `jam_pertemuan`
objek 45) — dipakai sebagai referensi golden yang sudah ada.

> **Perbedaan penting dengan form 34 (Asesmen Status Fungsional):** wording dan
> ambang Barthel legacy berbeda dari tabel status fungsional. Lihat §5.1.

### Struktur Field

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `tanggal_barthel` | 155 | Tanggal Observasi | date | ya | Reuse objek 155 |
| `waktu_barthel` | 156 | Waktu Observasi | time | ya | Reuse objek 156 |
| `barthel_transfer` | 350 | Barthel Transfer | radio 0–3 | ya | Objek baru |
| `barthel_mobilisasi` | 351 | Barthel Mobilisasi | radio 0–3 | ya | Objek baru |
| `barthel_toilet` | 352 | Barthel Toilet | radio 0–1 | ya | Objek baru |
| `barthel_bersih` | 353 | Barthel Bersih | radio 0–1 | ya | Objek baru |
| `barthel_bab` | 354 | Barthel Buang Air Besar | radio 0–2 | ya | Objek baru |
| `barthel_bak` | 355 | Barthel Buang Air Kecil | radio 0–2 | ya | Objek baru |
| `barthel_mandi` | 356 | Barthel Mandi | radio 0–2 | ya | Objek baru |
| `barthel_berpakaian` | 357 | Barthel Berpakaian | radio 0–2 | ya | Objek baru |
| `barthel_makan` | 358 | Barthel Makan | radio 0–2 | ya | Objek baru |
| `barthel_tangga` | 359 | Barthel Naik Turun Tangga | radio 0–2 | ya | Objek baru |
| `barthel_skor` | 360 | Total Skor Barthel | readonly (server) | ya | **Turunan**, objek baru |
| `tingkat_ketergantungan` | 361 | Tingkat Ketergantungan Barthel | readonly (server) | ya | **Turunan**, objek baru |
| `catatan` | 77 | Keterangan | textarea | tidak | Reuse |

### Kriteria Penilaian Barthel (verbatim dari `barthel_index.php:29-38`)

| Item | Skor 3 | Skor 2 | Skor 1 | Skor 0 |
|---|---|---|---|---|
| **Transfer** (tidur → duduk) | Mandiri | Dibantu Satu Orang | Dibantu Dua Orang | Tidak Mampu |
| **Mobilisasi** | Mandiri | Dibantu Satu Orang / Walker | Dengan Kursi Roda | Tergantung Orang Lain |
| **Toilet** | Mandiri | — | Perlu pertolongan orang lain | — |
| **Bersih** | Mandiri | — | Perlu pertolongan orang lain | — |
| **BAB** | Kontinen teratur | Kadang-kadang inkontinen | Inkontinen | — |
| **BAK** | Kontinen teratur | Kadang-kadang inkontinen | Inkontinen | — |
| **Mandi** | Mandiri | Perlu pertolongan | — | Tergantung pertolongan orang |
| **Berpakaian** | Mandiri | Sebagian dibantu | — | Tergantung orang lain |
| **Makan** | Mandiri | Perlu pertolongan | — | Tergantung orang lain |
| **Naik turun tangga** | Mandiri | Perlu pertolongan | — | Tidak Mampu |

> Perhatikan inkontinensia pada Barthel: **skor 1 = kadang inkontinen,
> skor 0 = inkontinen**. Pada tabel status fungsional (form 34) arahnya
> dibalik (skor 2 = kontinen, skor 0 = inkontinen). Jangan menyamakan keduanya.

**Skor maksimum = 20** (3+3+1+1+2+2+2+2+2+2).

### Ambang Tingkat Ketergantungan

| Total Skor | Tingkat | Warna |
|---|---|---|
| 0–4 | Ketergantungan Total | merah |
| 5–8 | Ketergantungan Berat | kuning |
| 9–11 | Ketergantungan Sedang | abu-abu |
| 12–19 | Tingkat Ketergantungan Ringan | biru |
| 20 | Mandiri | hijau |

### 5.1 Perbandingan Barthel vs Status Fungsional

| Aspek | Barthel (form 33) | Status Fungsional (form 34) |
|---|---|---|
| Jumlah item | 10 | 10 |
| Skor maks | 20 | 20 |
| Opsi Toilet | 2 (Mandiri / Perlu pertolongan) | 3 (Tergantung / Sebagian mandiri / Mandiri) |
| Opsi Mandi | 3 | 2 |
| Inkontinensia | 1 = kadang, 0 = total | 2 = kontinen, 0 = inkontinen |
| Ambang | 0–4 / 5–8 / 9–11 / 12–19 / 20 | identik |
| Timing | Berulang (pemulangan & rehabilitasi) | Sekali saat admisi |
| Sumber legacy | `barthel_index.php` | `pengkajian_awal_status_fungsional_pasien.php` |

Keduanya sengaja dipisah; label menu sudah dibuat berbeda supaya petugas tidak
tertukar.

### Perhitungan Server-Side

```php
// BarthelIndexController::filteredData()
$item = ['barthel_transfer','barthel_mobilisasi','barthel_toilet','barthel_bersih',
         'barthel_bab','barthel_bak','barthel_mandi','barthel_berpakaian',
         'barthel_makan','barthel_tangga'];

$total = 0; $terisi = 0;
foreach ($item as $v) {
    $n = $data[$v] ?? null;
    if ($n === null || $n === '') { continue; }
    $total += (int) $n; $terisi++;
}

$data['barthel_skor'] = $terisi === 10 ? $total : null;
$data['tingkat_ketergantungan'] = match (true) {
    $terisi < 10     => null,
    $total < 5       => 'Ketergantungan Total',
    $total < 9       => 'Ketergantungan Berat',
    $total < 12      => 'Ketergantungan Sedang',
    $total < 20      => 'Tingkat Ketergantungan Ringan',
    default          => 'Mandiri',
};
```

### Mapping

```php
33 => [
    'tanggal_barthel'         => 155,
    'waktu_barthel'           => 156,
    'barthel_transfer'        => 350,
    'barthel_mobilisasi'      => 351,
    'barthel_toilet'          => 352,
    'barthel_bersih'          => 353,
    'barthel_bab'             => 354,
    'barthel_bak'             => 355,
    'barthel_mandi'           => 356,
    'barthel_berpakaian'      => 357,
    'barthel_makan'           => 358,
    'barthel_tangga'          => 359,
    'barthel_skor'            => 360,
    'tingkat_ketergantungan'  => 361,
    'catatan'                 => 77,  // reuse
],
```

### Dashboard & Form Master

```php
['dashboard_menu_sub_extra_id' => 88, 'dashboard_menu_sub_id' => 134, 'nama_sub_menu_extra' => 'Barthel Index'],
['form_id' => 33, 'nama_form' => 'Barthel Index', 'slug' => 'barthel_index', 'id_dash_menu' => '8.134.88', 'ri' => 1, 'rj' => 0, 'igd' => 0, 'mcu' => 0],
```

### Akses EHR

```php
['profesi_id' => 1, 'form_id' => 33, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 0],
['profesi_id' => 2, 'form_id' => 33, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 0],
```

### Validasi

```php
'tanggal_barthel'    => 'required|date',
'waktu_barthel'      => 'required|date_format:H:i',
'barthel_transfer'   => 'required|integer|between:0,3',
'barthel_mobilisasi' => 'required|integer|between:0,3',
'barthel_toilet'     => 'required|integer|between:0,1',
'barthel_bersih'     => 'required|integer|between:0,1',
'barthel_bab'        => 'required|integer|between:0,2',
'barthel_bak'        => 'required|integer|between:0,2',
'barthel_mandi'      => 'required|integer|between:0,2',
'barthel_berpakaian' => 'required|integer|between:0,2',
'barthel_makan'      => 'required|integer|between:0,2',
'barthel_tangga'     => 'required|integer|between:0,2',
'catatan'            => 'nullable|string|max:1000',
```

### Cetak

`print.blade.php` **wajib** — lampiran discharge summary dan rujukan rehabilitasi.

---

## 6. Implementasi

### Seeder (`database/seeders/EmrMasterSeeder.php`)

- [ ] `$subMenus` — tambah `133 Risiko Jatuh (menu 8)`, `134 Penilaian Dekubitus (menu 8)`
      (menu 8, sub 76, sub 77 sudah dibuat di `KONSEP_ASUHAN_KEPERAWATAN_LANJUTAN.md`)
- [ ] `$extras` — tambah `86 Monitoring Risiko Jatuh`, `87 Penilaian Risiko Dekubitus`,
      `88 Barthel Index`
- [ ] `$forms` — tambah 3 baris (30, 32, 33)
- [ ] `$objeks` — tambah 26 baris (336–361)
- [ ] `$mapping` — tambah 3 blok
- [ ] `EmrHelper::backfillObjekId(30)`, `(32)`, `(33)`
- [ ] `$akses` — tambah 6 baris

### Helper

- [ ] `app/Helpers/DekubitusHelper.php`
      - `norton(): array` — definisi 5 item + opsi + kategori
      - `barthel(): array` — definisi 10 item + opsi + kategori
      - `hitungNorton(array $a): array`, `hitungBarthel(array $a): array`
      - `SKOR_WARNA`, `KATEGORI_WARNA` — konstanta warna untuk badge di blade
- [ ] `RisikoJatuhHelper` **tidak diubah** — form 30 memakainya apa adanya

### Controller

- [ ] `app/Http/Controllers/EMR/MonitoringRisikoJatuh/MonitoringRisikoJatuhController.php`
- [ ] `app/Http/Controllers/EMR/PenilaianRisikoDekubitus/PenilaianRisikoDekubitusController.php`
- [ ] `app/Http/Controllers/EMR/BarthelIndex/BarthelIndexController.php`

### View

- [ ] `resources/views/moduls/EMR/{MonitoringRisikoJatuh,PenilaianRisikoDekubitus,BarthelIndex}/index.blade.php`
- [ ] `print.blade.php` untuk ketiga form
- [ ] Partial **sudah ada** dan dipakai ulang:
      - `PartialForm/pengkajian_risiko_jatuh.blade.php`
      - `PartialForm/_risiko_jatuh_instrumen.blade.php`
      Ketiganya perlu menerima parameter `$emrDataKey` agar bisa dipakai form 3, 4, dan 30.
- [ ] Partial baru `PartialForm/_norton_scale.blade.php`
- [ ] Partial baru `PartialForm/_barthel_index.blade.php`

### SelectOption

- [ ] Tambah key: `status_monitoring_jatuh`, `intervensi_pencegahan_jatuh`,
      `norton_item` (atau simpan sebagai konstanta di `DekubitusHelper`)

### Dokumentasi

- [ ] `AGENTS.md` — entri form 30, 32, 33 + `DekubitusHelper`
- [ ] `docs/PENGKAJIAN_RISIKO_JATUH.md` — tambahkan rujukan silang ke form 30
- [ ] `docs/ANALISIS_KEKURANGAN_FORM.md` §6

### Verifikasi

```bash
docker compose up -d db
docker compose exec app php artisan migrate:fresh --seed
docker compose exec app ./vendor/bin/pint --dirty
docker compose exec app php artisan test
```

Uji khusus:
- Norton: 4/4/4/4/4 → skor 20 → "Tidak Berisiko"; 1/1/1/1/1 → skor 5 → "Risiko Tinggi"
- Barthel: semua maksimum → 20 → "Mandiri"; semua nol → 0 → "Ketergantungan Total"
- Form 30: ubah radio dari opsi pertama ke kedua pada tiap item → skor harus berubah
  (regresi bug "radio pertama saja")

---

## 7. Catatan & Risiko

| # | Risiko | Dampak | Mitigasi |
|---|---|---|---|
| R1 | **Norton: skor rendah = risiko tinggi.** Ini berlawanan dengan intuisi dan dengan MFS. | Salah baca kategori, salah intervensi. | Label kategori ditulis penuh ("Risiko Tinggi Terjadi Dekubitus"), bukan "Tinggi"/"Sedang" saja. Warna di server (`DekubitusHelper::KATEGORI_WARNA`). |
| R2 | **Barthel vs Status Fungsional tumpang tindih** dengan arah inkontinensia berlawanan. | Data tertukar antar form, kesimpulan rehabilitasi salah. | Label menu dibedakan; tabel perbandingan ada di §5.1; form 33 tidak diberi badge "ADL Admission" dan sebaliknya. |
| R3 | **Objek 92 dipakai dua variabel pada form 30** (`risiko_jatuh_intervensi` teks, `intervensi_pencegahan` JSON). | `emrDetailByObjek()` menimpa salah satu. | Jangan pakai `emrDetailByObjek()` untuk form 30. Bila laporan butuh keduanya, alokasikan objek baru di rentang setelah 361 (di luar alokasi dokumen ini). |
| R4 | **Nilai browser untuk skor/kategori diabaikan.** | Angka palsu bila validasi JS dilewati. | Semua `skor_*`, `label`, `ringkasan` dihitung ulang di `filteredData()` (`RisikoJatuhHelper::hitung()`, `DekubitusHelper::hitungNorton()`). |
| R5 | **Skor parsial tersimpan sebagai nol.** | Pasien tampak "aman". | Skor hanya disimpan bila seluruh item terisi; selain itu `NULL`. Warna badge "Belum Dinilai" untuk `NULL`. |
| R6 | **Radio grup hanya dibaca yang pertama.** | Skor tidak berubah saat opsi diganti — bug historis. | Simpan seluruh elemen grup dan cari yang `checked`. Sudah didokumentasikan di `AGENTS.md`; tambahkan regression test untuk form 30, 32, 33. |
| R7 | **Batas usia instrumen berbeda antara legacy (17/18) dan NusaMedika (13/14).** | Skor tidak konsisten antar form 3, 4, 30. | Form 30 wajib memanggil `RisikoJatuhHelper::instrumenUntukUsia()`. Jangan menyalin angka batas dari legacy. |
| R8 | **TUG tidak bisa dipakai pada pasien yang tidak dapat berjalan.** | Data terisi-ngawur. | Bila `tug_detik` diisi tetapi pasien tidak/impossible, server menolak — tambahkan validasi: bila instrumen TUG dan `tug_detik` NULL, skor NULL. |
| R9 | **`deteksiInstrumen()` bisa salah bila jawaban item dari instrumen lain ikut terkirim.** | Skor terhitung dari instrumen yang keliru. | `filteredData()` membuang jawaban instrumen yang tidak terpilih **setelah** `hitung()` dipanggil; atau lebih aman: panggil `deteksiInstrumen()` lebih dulu bila `risiko_jatuh_instrumen` tidak dikirim. |
| R10 | **`access_delete = 0` tapi route `emr.form.destroy` tetap ada.** | 403 yang membingungkan bila user menekan Hapus. | View sudah.disable tombol Hapus via `$aksesCrud['delete']`. Pastikan `x-emr-split-layout` meneruskan `canDelete`. |
| R11 | **`status_monitoring` bergantung pada `latestValuesByVariabel()`** yang membaca emr **terakhir** pada `registrasi_detail`. | Bila baris terakhir dihapus, perbandingan melompat ke baris lebih lama. | `akses_delete = 0` mencegah penghapusan. Kalau suatu saat diizinkan, simpan `emr_id` pembanding sebagai kolom snapshot. |
| R12 | **Nomor objek legacy tidak boleh dipakai** (22, 25, 44, 45, 1062, 1070, 1086, 1087, 1287, 429, 252, 1044–1049, 155, 254, 578, 809–811, 1332, 1333). | Data tercampur antar form. | Selalu pakai objek NusaMedika. Nomor legacy hanya dirujuk di kolom keterangan. |
| R13 | **Nilai TUG dibandingkan dengan batas atas 95% CI, bukan nilai bulat hasil `ceil()`.** | Salah klasifikasi (mis. 11 dtk pada usia 70–79). | Sudah ditangani `RisikoJatuhHelper::kategoriTug()`; **jangan** menulis ulang logikanya di controller form 30. |
| R14 | **Menu 8 dipakai bersama `KONSEP_ASUHAN_KEPERAWATAN_LANJUTAN.md` (sub 76, 77).** | Id bentrok bila salah seed. | Sudah dikunci: sub 76–77 milik dokumen itu, sub 133–134 milik dokumen ini (`ALOKASI_ID_GLOBAL.md` §3). Jangan menambah sub baru di luar band 133–152 tanpa verifikasi. |
| R15 | **Partial `pengkajian_risiko_jatuh.blade.php` saat ini meng-hardcode form 3/4.** | Form 30 tidak bisa memakainya. | Refactor partial menerima `$prefix` atau `$formId` agar generik; uji form 3, 4, 30 sekaligus. |
| R16 | **Tambah 3 form + 26 objek.** Total objek ± 500. | Halaman Manajemen EMR Form makin lambat. | Filter per `form_id` sudah tersedia; tambahkan pencarian objek berdasarkan `nama_objek`. |