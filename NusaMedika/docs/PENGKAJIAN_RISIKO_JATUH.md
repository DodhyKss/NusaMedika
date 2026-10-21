# PENGKAJIAN RISIKO JATUH (EMR) — instrumen berbasis usia

> Status: **Sudah diimplementasikan** — dipakai pada form *Pengkajian Awal Keperawatan* (form 3) dan *Pengkajian Harian Keperawatan* (form 4).
> Pusat logika: `app/Helpers/RisikoJatuhHelper.php`.

---

## 1. Masalah yang diselesaikan

Form Pengkajian Awal Keperawatan punya bagian "Pengkajian Up & Go" dengan tiga parameter (sempoyongan/pincang, alat bantu, menopang saat duduk). Instrumen tersebut:

1. **Tidak membedakan usia** — instrumen yang sama untuk anak, dewasa, dan lanjut usia, padahal validitas tiap instrumen berbeda.
2. **Tidak punya skor numerik** — hanya tabel "Tidak Berisiko / Risiko Rendah / Risiko Tinggi" subjektif.
3. **Checkbox-nya rusak** — partial tersebut memakai `$emr_data` sebagai **scalar**, sehingga semua checkbox membaca nilai yang sama (lihat section 6).

Kini diganti pengkajian risiko jatuh yang **memilih instrumen otomatis sesuai usia**, menghitung skor, dan menyimpan angkanya.

---

## 2. Pemilihan instrumen

| Kelompok usia | Instrumen | Kode |
|---|---|---|
| Anak `< 14` tahun | Humpty Dumpty Falls Scale | `HDS` |
| Dewasa `14–59` tahun | Morse Fall Scale | `MFS` |
| Lansia `>= 60` tahun | Timed "Up and Go" | `TUG` |
| Semua usia (manual) | Sydney Scoring (STRATIFY modifikasi) | `SYDNEY` |

```php
RisikoJatuhHelper::instrumenUntukUsia(8);   // HDS
RisikoJatuhHelper::instrumenUntukUsia(36);  // MFS
RisikoJatuhHelper::instrumenUntukUsia(76);  // TUG
```

Batas usia ada di konstanta `USIA_ANAK_MAKS` (13) dan `USIA_LANSIA_MIN` (60). Perawat tetap dapat mengganti instrumen secara manual lewat dropdown.

---

## 3. Skor & ambang batas

### 3.1 Morse Fall Scale (MFS) — dewasa, skor 0–125

Enam item (Morse 1989):

| Item | Jawaban | Skor |
|---|---|---|
| Riwayat jatuh (3 bulan / selama dirawat) | Tidak / Ya | 0 / 25 |
| Diagnosa sekunder (> 1 diagnose) | Tidak / Ya | 0 / 15 |
| Alat bantu ambulasi | Tanpa alat / tirah / kruk-tongkat-walker / menyandar perabot | 0 / 15 / 30 |
| Terpasang infus atau heparin lock | Tidak / Ya | 0 / 20 |
| Gait dan transfer | Normal–tirah–kursi roda / lemah / terganggu | 0 / 10 / 20 |
| Status mental | Sesuai kemampuan / overestimate–lupa keterbatasan | 0 / 15 |

**Rendah 0–24 · Sedang 25–50 · Tinggi >= 51** (LWW dan VA Falls Toolkit).

### 3.2 Humpty Dumpty Falls Scale (HDS) — anak, skor 7–23

Tujuh item, jawaban Ya/Tidak dengan bobot **1, 2, 2, 2, 3, 4, 4**. HDS asli memberi minimal 1 poin per item (total minimal 7), jadi implementasi ini memakai:

```
skor = min(23, 7 + jumlah bobot)
```

Dengan demikian rentang publikasi **7–23** dan ambangnya (**7–11 rendah, 12–23 tinggi**) tetap terpakai: tidak ada risiko → 7 (rendah); semua risiko → 23 (tinggi).

### 3.3 Timed "Up and Go" (TUG) —lansia, skor = detik

Kategori TUG **tidak** memakai angka statis, melainkan dibandingkan dengan batas atas 95% CI dari meta-analisis Bohannon (2006, *J Geriatr Phys Ther*, 21 studi):

| Kelompok usia | Rata-rata | Batas atas (dipakai) |
|---|---|---|
| 60–69 tahun | 8,1 dtk | 9,0 dtk |
| 70–79 tahun | 9,2 dtk | 10,2 dtk |
| 80–99 tahun | 11,3 dtk | 12,7 dtk |

Aturan kategori: `<= batas atas` → Sesuai Norma · `> batas atas` → Risiko Meningkat · `>= 30 dtk` → Risiko Tinggi.

> **Gotcha:** perbandingan memakai nilai **mentah** (`10.2`), bukan nilai yang sudah di-`ceil()` ke `11`, agar 11 detik pada usia 70–79 tetap terbaca "Risiko Meningkat". Nilai bulat hanya dipakai untuk ditampilkan.

### 3.4 Sydney Scoring (STRATIFY modifikasi)

Dua sub-skor dari 3 subitem masing-masing: **Transfer Score** (`syd_ts_*`) dan **Mobility Score** (`syd_ms_*`), tiap subitem 0–3. Aturan penjumlahan (NSW Clinical Excellence Commission):

```
TS + MS = 0–2  -> skor 0
TS + MS = 3–6  -> skor 7
TS + MS >= 7   -> skor = TS + MS
Total >= 9  -> risiko tinggi
```

> **Catatan sumber:** yang dipakai adalah versi **Ontario Modified STRATIFY (Sydney Scoring)** yang skornya terdokumentasi lengkap, bukan "Sydney Falls Risk Screening Tool" versi rehabilitasi otak (McKechnie 2018, ambang 33). Nama sumber dicetak di bawah dropdown agar perawat tidak tertukar.

---

## 4. Yang disimpan ke `emr_detail`

`emr_detail` bersifat generik (satu baris per variabel), jadi skor disimpan sebagai variabel biasa:

| variabel | isi |
|---|---|
| `risiko_jatuh_instrumen` | kode instrumen (`HDS`/`MFS`/`TUG`/`SYDNEY`) |
| `risiko_jatuh_skor` | angka skor |
| `risiko_jatuh_label` | tingkat risiko |
| `risiko_jatuh_intervensi` | intervensi yang sesuai |
| `risiko_jatuh_ringkasan` | teks ringkas, mis. `85 - Risiko Tinggi` atau `14.5 - Risiko Meningkat (norma 70-79 tahun: maksimal 11 detik)` |

Jawaban tiap item (`hds_1..7`, `mfs_1..6`, `syd_*`, `tug_detik`) juga disimpan sendiri agar bisa diaudit. Objek 89–113.

### Skor dihitung ulang di server

Frontend mengirim skor lewat input tersembunyi supaya layar responsif, tetapi **`filteredData()` pada controller mengabaikan nilai itu dan menghitung ulang dari jawaban mentah**:

```php
$kode = RisikoJatuhHelper::normalisasiKode($request->input('risiko_jatuh_instrumen'))
    ?: RisikoJatuhHelper::deteksiInstrumen($data);   // fallback bila field tidak terkirim

$hasil = RisikoJatuhHelper::hitung($kode, $data, $usia);
```

Diverifikasi: POST `risiko_jatuh_skor=999&risiko_jatuh_label=PALSU` tersimpan sebagai `85` / `Risiko Tinggi`.

`deteksiInstrumen()` memindai item mana yang terisi, sehingga jawaban tidak hilang bila field instrumen tidak ikut terkirim (form dirender ulang, integrasi lama, atau proses klik simulator).

---

## 5. Partial & view

| Berkas | Isi |
|---|---|
| `PartialForm/pengkajian_risiko_jatuh.blade.php` | Panel utama: kartu usia, dropdown instrumen, 4 panel item, kotak hasil, input tersembunyi, JS penghitung |
| `PartialForm/_risiko_jatuh_instrumen.blade.php` | Tabel item untuk satu instrumen (dipakai `@include` per instrumen) |
| `PartialForm/harian_keluhan.blade.php` | Bagian A form harian: keluhan utama + catatan keperawatan |
| `PartialForm/harian_balance.blade.php` | Bagian D form harian: intake cairan, eliminasi, intake makanan, tidur + notifikasi balance otomatis |

Panel instrumen yang tidak aktif diberi `disabled()` oleh JS agar tidak ikut terkirim.

> **Gotcha `layouts.iframe`:** layout ini **tidak** merender `@stack('scripts')`, jadi JS di dalam partial ditulis sebagai `<script>` inline, bukan `@push('scripts')`.

---

## 5b. Bug: skor tidak berubah saat radio diganti

Gejala: perawat mencentang pilihan pada instrumen risiko jatuh, tetapi **skor dan
tingkat risiko di layar tidak bergerak**.

Penyebabnya adalah cara membaca nilai radio group di JS:

```js
// SALAH — hanya menyimpan radio PERTAMA sebagai referensi
var radios = panel.querySelectorAll('input[type=radio][name="' + nama + '"]');
item.el = radios[0];
...
var nilai = el.checked ? el.value : '';   // el = radio pertama
```

Ketika pengguna memilih **opsi kedua** (mis. "Ya" pada `mfs_1`), browser
otomatis men-*uncheck* radio pertama — sehingga `radios[0].checked` bernilai
`false`, jawaban terbaca sebagai `""`, dan item itu tidak dihitung. Akibatnya
hanya opsi **pertama** ("Tidak"/skor 0) yang bisa terbaca, jadi skor praktis
**tidak pernah berubah** kecuali semua item dijawab dengan opsi pertama.

Perbaikan: simpan **seluruh grup** dan cari elemen yang sedang `checked`.

```js
item.els = Array.prototype.slice.call(panel.querySelectorAll('[name="' + item.var + '"]'));

function nilaiItem(item) {
    if (!item.els || !item.els.length) return '';
    for (var i = 0; i < item.els.length; i++) {
        var el = item.els[i];
        if (el.type === 'radio') { if (el.checked) return el.value; }
        else { return el.value; }
    }
    return '';
}
```

Pola yang sama berlaku untuk GCS. Field `gcs_jumlah` (readonly) **tidak punya
perhitungan sama sekali** sebelumnya, kini dihitung otomatis
`gcs_e + gcs_v + gcs_m`. Sekalian diperbaiki atribut `value` yang tertulis dua
kali pada input `gcs_jumlah` (`value="{{ ... }}" readonly value=""`) — atribut
kedua menimpa nilai yang tersimpan sehingga total selalu kosong.

> **Cara verifikasi tanpa browser:** logika ini bisa diuji terpisah dengan
> mengekstrak `<script>` dari HTML hasil render lalu menjalankannya di Node dengan
> DOM shim. Assertion: memilih opsi kedua harus mengubah skor (mis. `mfs_1=Ya`
> → 25; seluruh MFS terisi → 125).

## 6. Bug lama: `$emr_data` dibaca sebagai scalar

Semua partial EMR lama menulis nilai seperti ini:

```blade
<input type="text" name="agama" value="{{ $emr_data ?? '' }}">      {{-- SALAH --}}
<input type="radio" name="nyeri" value="ya" {{ ($emr_data ?? '') == 'ya' ? 'checked' : '' }}>
```

`$emr_data` adalah `EmrDataWrapper` yang implements `ArrayAccess` dan **keyed by variabel**. Membacanya sebagai scalar membuat:

- setiap field menampilkan **nilai yang sama** (yaitu `''`, karena `__toString()` mengembalikan string kosong);
- **tidak ada satu pun** radio/checkbox yang tercentang;
- checkbox membandingkan objek wrapper dengan `'on'` sehingga praktis tidak pernah tampil centang.

Pola benar:

```blade
{{ $emr_data['agama'] ?? '' }}
{{ ($emr_data['nyeri'] ?? '') === 'ya' ? 'checked' : '' }}
```

Sudah diperbaiki di enam partial: `informasi_pasien`, `riwayat_penyakit`, `pemeriksaan_fisik`, `pengkajian_nyeri`, `riwayat_alergi`.

Radio `_radio` (mis. `aktifitas_sebelum_makan_radio`) adalah kontrol bantu yang tidak ada di mapping `emr_detail`, jadi statusnya diturunkan dari field teksnya: kosong atau `'off'` → Tidak, selain itu → Ya.

---

## 7. Blocker skema yang ditemukan: `pasien.tgl_lahir`

Column `pasien.tgl_lahir` semula `timestamp(6)`. MySQL hanya menerima **1970-01-01 s.d. 2038-01-19** untuk `TIMESTAMP`, sehingga:

```
UPDATE pasien SET tgl_lahir = '1960-03-10';
ERROR 1292 (22007): Incorrect datetime value for column 'tgl_lahir'
```

Artinya **pasien lahir sebelum 1970 tidak bisa didaftarkan sama sekali** — justru kelompok usia yang paling membutuhkan TUG. Migration `2026_10_01_000006_change_pasien_tgl_lahir_to_date.php` mengubahnya menjadi `date` (rentang 1000–9999, bebas batas 2038). Konversi `timestamp -> date` aman, bagian jam dipotong, tanggal lama tidak berubah.

---

## 8. Referensi

- Morse Fall Scale — Morse, J.M. (1989); LWW Docucare & US Dept of Veterans Affairs Falls Toolkit.
- Timed Up and Go — Podsiadlo, D. & Richardson, S. (1991); norma usia: Bohannon, R.W. (2006). *Reference Values for the Timed Up and Go Test: A Descriptive Meta-Analysis.* J Geriatr Phys Ther 29(2):64–68.
- Humpty Dumpty Falls Scale — Cupo, T.L. et al. (2007); ringkasan pada Strini, V. et al. (2021) *Fall Risk Assessment Scales: A Systematic Literature Review*, Medicina 57(2):41.
- Ontario Modified STRATIFY (Sydney Scoring) — NSW Clinical Excellence Commission, *Risk Screening*.