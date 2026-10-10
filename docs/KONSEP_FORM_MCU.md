# Konsep Form Medical Checkup (MCU) — NusaMedika

**Status:** Rancangan / Konsep — belum diimplementasikan
**Tanggal:** 2026-10-10
**Cakupan:** `form_id` **70–84** (15 form)
**Acuan wajib:** [`PANDUAN_IMPLEMENTASI_FORM_EMR.md`](PANDUAN_IMPLEMENTASI_FORM_EMR.md), `AGENTS.md`, [`KONSEP_MCU.md`](KONSEP_MCU.md) (modul & pendaftaran), [`ANALISIS_KEKURANGAN_FORM.md`](ANALISIS_KEKURANGAN_FORM.md)
**Alokasi `objek_id`:** **611–670** (60 id band; **49 objek baru terpakai**); objek 1–177 dipakai ulang.

> Semua form pada dokumen ini **`mcu = 1`, `ri = rj = igd = 0`** — hanya muncul di dashboard pasien dengan `registrasi.jenis_rawat = MCU`.

---

## 1. Ringkasan

| form_id | nama_form | slug | id_dash_menu | ri | rj | igd | mcu | objek baru |
|---|---|---|---|---|---|---|---|---|
| 70 | Kesimpulan MCU | `kesimpulan_mcu` | `14.273` | 0 | 0 | 0 | 1 | 611–616 (6) |
| 71 | Catatan Keperawatan / TTV MCU | `ttv_mcu` | `14.274` | 0 | 0 | 0 | 1 | 617–624 (8) |
| 72 | Visus Mata MCU | `visus_mata_mcu` | `14.275` | 0 | 0 | 0 | 1 | 625–628 (4) |
| 73 | Obstetri MCU | `obstetri_mcu` | `14.276` | 0 | 0 | 0 | 1 | 629–632 (4) |
| 74 | Penyakit Dalam MCU | `penyakit_dalam_mcu` | `14.277` | 0 | 0 | 0 | 1 | 633–636 (4) |
| 75 | Jantung MCU | `jantung_mcu` | `14.278` | 0 | 0 | 0 | 1 | 637–640 (4) |
| 76 | THT MCU | `tht_mcu` | `14.279` | 0 | 0 | 0 | 1 | 641–644 (4) |
| 77 | Mata MCU | `mata_mcu` | `14.280` | 0 | 0 | 0 | 1 | 645–647 (3) |
| 78 | Gigi MCU | `gigi_mcu` | `14.281` | 0 | 0 | 0 | 1 | 649–650 (2) |
| 79 | OBGYN MCU | `obgyn_mcu` | `14.282` | 0 | 0 | 0 | 1 | 652 (1) |
| 80 | Bedah MCU | `bedah_mcu` | `14.283` | 0 | 0 | 0 | 1 | 655 (1) |
| 81 | Neurologi MCU | `neurologi_mcu` | `14.284` | 0 | 0 | 0 | 1 | 658 (1) |
| 82 | Gizi MCU | `gizi_mcu` | `14.285` | 0 | 0 | 0 | 1 | 661–662 (2) |
| 83 | Urologi MCU | `urologi_mcu` | `14.286` | 0 | 0 | 0 | 1 | 664 (1) |
| 84 | Psikologi MCU | `psikologi_mcu` | `14.287` | 0 | 0 | 0 | 1 | 667–670 (4) |

> **Id band yang tidak terpakai:** 648, 651, 653, 654, 656, 657, 659, 660, 663,
> 665, 666 (11 id dicadangkan, tidak dideklarasikan di `$objeks`). Akses `id_dash_menu`
> tetap `14.273`–`14.287` sesuai sub-menu 273–287.

### 1.1 Struktur menu

```php
// $menus
['dashboard_menu_id' => 14, 'nama_menu' => 'MCU'],

// $subMenus — tanpa extra, id_dash_menu = "14.N"
['dashboard_menu_sub_id' => 273,  'dashboard_menu_id' => 14, 'nama_sub_menu' => 'Kesimpulan MCU'],
['dashboard_menu_sub_id' => 274,  'dashboard_menu_id' => 14, 'nama_sub_menu' => 'TTV MCU'],
['dashboard_menu_sub_id' => 275,  'dashboard_menu_id' => 14, 'nama_sub_menu' => 'Visus Mata MCU'],
['dashboard_menu_sub_id' => 276,  'dashboard_menu_id' => 14, 'nama_sub_menu' => 'Obstetri MCU'],
['dashboard_menu_sub_id' => 277,  'dashboard_menu_id' => 14, 'nama_sub_menu' => 'Penyakit Dalam MCU'],
['dashboard_menu_sub_id' => 278,  'dashboard_menu_id' => 14, 'nama_sub_menu' => 'Jantung MCU'],
['dashboard_menu_sub_id' => 279,  'dashboard_menu_id' => 14, 'nama_sub_menu' => 'THT MCU'],
['dashboard_menu_sub_id' => 280,  'dashboard_menu_id' => 14, 'nama_sub_menu' => 'Mata MCU'],
['dashboard_menu_sub_id' => 281,  'dashboard_menu_id' => 14, 'nama_sub_menu' => 'Gigi MCU'],
['dashboard_menu_sub_id' => 282, 'dashboard_menu_id' => 14, 'nama_sub_menu' => 'OBGYN MCU'],
['dashboard_menu_sub_id' => 283, 'dashboard_menu_id' => 14, 'nama_sub_menu' => 'Bedah MCU'],
['dashboard_menu_sub_id' => 284, 'dashboard_menu_id' => 14, 'nama_sub_menu' => 'Neurologi MCU'],
['dashboard_menu_sub_id' => 285, 'dashboard_menu_id' => 14, 'nama_sub_menu' => 'Gizi MCU'],
['dashboard_menu_sub_id' => 286, 'dashboard_menu_id' => 14, 'nama_sub_menu' => 'Urologi MCU'],
['dashboard_menu_sub_id' => 287, 'dashboard_menu_id' => 14, 'nama_sub_menu' => 'Psikologi MCU'],
```

> Sub 2 memakai nama **`TTV MCU`** (bukan "Catatan Keperawatan MCU") agar `Str::slug('TTV MCU','_') = 'ttv_mcu'` sama dengan `form.slug`. Nama panjang yang tidak cocok slug akan membuat form tampil sebagai *Unsupported* (PANDUAN §2.1).

---

## 2. Sumber Referensi Legacy

Path relatif terhadap `/simrs_tenriawaru/FE/lib/modul/`.

| Form | File legacy | Isi utama |
|---|---|---|
| 70 | `kesimpulan_mcu.php` | Interpretasi, Kesimpulan (4 kategori), Keterangan, Rekomendasi + tombol hasil penunjang |
| 71 | `catatan_keperawatan_mcu.php` (+ `_act`) | Anamnesis + TTV (TD, nadi, RR, suhu, TB, BB, BMI, lingkar perut, jenis pekerjaan), riwayat keluarga, gaya hidup |
| 72 | `catatan_keperawatan_mcu_visus.php` | Visus AVOD/AVOS/C/ADD/PD/TOD/TOS |
| 73 | `catatan_keperawatan_mcu_obstetri.php` | Menarche, haid, siklus, KB, kehamilan, keguguran, coitus, pap smear |
| 74 | `pemeriksaan_mcu_ipd.php` | Kepala, kulit, marry, mata, mulut, hidung, tenggorokan, telinga, thorax, jantung, paru, abdomen, ekstremitas, lain |
| 75 | `pemeriksaan_mcu_jantung.php` | Jantung, EKG (Sinus Rhythm, HR, kelainan, kesimpulan), Treadmill, Echocardiography |
| 76 | `pemeriksaan_mcu_tht.php` | Telinga (kanan/kiri), hidung, tenggorokan |
| 77 | `pemeriksaan_mcu_mata.php` | Visus + konfrontasi + buta warna |
| 78 | `pemeriksaan_mcu_gigi.php` | Pemeriksaan gigi + kebutuhan tindakan (expertise gigi) |
| 79 | `pemeriksaan_mcu_obgyn.php` | Pemeriksaan lokalis + diagnosis + rekomendasi |
| 80 | `pemeriksaan_mcu_bedah.php` | Pemeriksaan lokalis + diagnosis + rekomendasi |
| 81 | `pemeriksaan_mcu_neurologi.php` | Pemeriksaan neurologi + diagnosis + rekomendasi |
| 82 | `pemeriksaan_mcu_gizi.php` | Objektif (TB/BB), status gizi, pola diet |
| 83 | `pemeriksaan_mcu_urology.php` | Pemeriksaan fisik + diagnosis + rekomendasi |
| 84 | `pemeriksaan_mcu_psikologi.php` | Gangguan kehidupan, saran, status psikologis |
| — | `dashboard_mcu_pasien.php` | Alur sequential per organ — dasar desain "read-only display panel" |
| — | `cetak_resume_mcu.php`, `cetak_resume_mcu_cover.php` | Cetak hasil MCU |

> Semua form legacy MCU memakai pola identik: include `informasi_umum_tanpa_spesifik.php`, baca `emr.data` JSON, tombol **"Ada / Tidak Ada"** per item (item "Tidak Ada" menyembunyikan kolom uraian), lalu `Diagnosa` + `Rekomendasi` di bawah. Pola ini dipertahankan.

---

## 3. Arsitektur bersama 14 form spesialistik

### 3.1 Anatomi yang sama

Keempat belas form (72–84) memiliki **struktur identik**:

```
┌──────────────────────────────────────────────────────────────┐
│ HEADER IDENTITAS PASIEN  (dari form 71, read-only)          │
│  Nama · No MR · Umur · Jenis Kelamin · No. MCU · Tanggal     │
├──────────────────────────────────────────────────────────────┤
│ KELUHAN SAAT INI : Tidak Ada | Ada → [textarea uraian]       │
├──────────────────────────────────────────────────────────────┤
│ PARAMETER PER ORGAN   (2–12 baris)                           │
│  ┌──────────────┬────────────┬───────────┬──────────────────┐ │
│  │ Parameter    │ Tidak Ada  │ Ada       │ Uraian/Temuan    │ │
│  ├──────────────┼────────────┼───────────┼──────────────────┤ │
│  │ Telinga      │ ○          │ ○         │ [textarea]        │ │
│  └──────────────┴────────────┴───────────┴──────────────────┘ │
├──────────────────────────────────────────────────────────────┤
│ RENTANG NORMAL  (helper, tampil sebagai teks kecil)           │
├──────────────────────────────────────────────────────────────┤
│ DIAGNOSA  [textarea]   ·   REKOMENDASI  [textarea]           │
├──────────────────────────────────────────────────────────────┤
│ PEMERIKSA PENUNJANG (tombol ke order Lab/Rad) + PARAF DOKTER │
└──────────────────────────────────────────────────────────────┘
```

Konsekuensi: **satu partial Blade + satu trait/controller base** cukup untuk 14 form.

### 3.2 Partial Blade reusable

`resources/views/moduls/EMR/PartialForm/mcu_spesialistik.blade.php`

```blade
@props([
    'judulForm'       => '',       // "PEMERIKSAAN THT"
    'organ'           => [],       // ['kunci' => 'telinga_kanan', 'label' => 'Telinga Kanan', 'normal' => 'Normal']
    'emr_data'        => [],
    'isView'          => false,
    'headerPasien'    => [],       // dari form 71
    'emrForm'         => null,     // form object (untuk simpan snapshot nama dokter)
])

@foreach ($organ as $o)
    @php $ada = ($emr_data[$o['kunci'] . '_ada'] ?? '0') === '1'; @endphp
    <div class="row">
        <div class="col-5">{{ $o['label'] }} <span class="text-red-500">*</span></div>
        <div class="col-3">
            <label><input type="radio" name="{{ $o['kunci'] }}_ada" value="0" @checked(! $ada) {{ $isView ? 'disabled' : '' }}> Tidak Ada</label>
            <label><input type="radio" name="{{ $o['kunci'] }}_ada" value="1" @checked($ada) {{ $isView ? 'disabled' : '' }}> Ada</label>
        </div>
        <div class="col-4">
            <input type="text" name="{{ $o['kunci'] }}" value="{{ old($o['kunci'], $emr_data[$o['kunci']] ?? '') }}"
                   @disabled(! $ada) placeholder="{{ $o['normal'] ?? '' }}">
            @error($o['kunci'])<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
        </div>
    </div>
@endforeach
```

> **Gotcha wajib:** `$emr_data` adalah `EmrDataWrapper` yang **hanya mengimplementasikan `ArrayAccess`**; `{{ $emr_data ?? '' }}` selalu menghasilkan string kosong. Selalu tulis `$emr_data['variabel'] ?? ''` (AGENTS.md — Pengkajian Risiko Jatuh).
>
> **Gotcha wajib:** `aria-hidden`/`<input type="file">` tidak relevan, tetapi **tampil/sembunyi WAJIB lewat inline `style.display`, bukan class `hidden`** bila elemen punya utility display lain (AGENTS.md — Tindakan Medis).

### 3.3 Base controller reusable

`app/Http/Controllers/EMR/McuSpesialistikController.php`

```php
abstract class McuSpesialistikController extends Controller
{
    protected const SLUG = '';       // dioverride tiap form
    protected array $organ = [];     // daftar parameter organ

    public function index($registrasi_detail_id, $emr_id = null) { /* sama ImplementasiKeperawatanController */ }

    /** Panel read-only hasil organ lain untuk form ini. */
    protected function ringkasanSpesialistik(): array
    {
        $formId = EmrHelper::formIdBySlug('jantung_mcu');   // per form
        return EmrHelper::latestValuesByVariabel((int) $formId, $this->registrasi_detail_id, $variabels);
    }
}
```

14 subclass hanyaoverride: `SLUG`, `$organ`, `$diagnosisKey`, `$rekomendasiKey`, `validated()`.

### 3.4 Pola "Tidak Ada / Ada" ala legacy

Legacy menyembunyikan kolom uraian bila carbox "Tidak Ada" dipilih. Di Laravel+Tailwind:

- `Tidak Ada` = `*_ada = '0'`, field uraian kosong & **dibuang di `filteredData()`**.
- `Ada` = `*_ada = '1'`, field uraian **wajib**.
- Radio tidak boleh `disabled` dipaksa (lihat gotcha Select2 di AGENTS.md) — untuk Show/hide cukup `el.style.display` + lepas `name` pada input yang disembunyikan.

---

## 4. Form 70 — Kesimpulan MCU

### Rasional

Form penutup episode MCU. Menggabungkan temuan seluruh organ (72–84) menjadi **interpretasi**, **kategori kesimpulan**, **catatan**, dan **rekomendasi**. Ini dokumen yang dibaca pasien dan menjadi dasar surat keterangan.

Kategori kesimpulan (verbatim legacy, **tidak boleh diubah** karena menjadi isi surat):

| Nilai | Label |
|---|---|
| 1 | SEHAT |
| 2 | SEHAT Dengan Catatan Medis |
| 3 | TIDAK SEHAT Sementara Waktu Hingga Pemeriksaan Berikutnya |
| 4 | TIDAK SEHAT |

### Struktur Field

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `tanggal_kesimpulan` | 611 | Tanggal Kesimpulan MCU | date | ya | default hari ini |
| `jam_kesimpulan` | 612 | Jam Kesimpulan MCU | time | ya | default now |
| `interpretasi` | 614 | Interpretasi Hasil MCU | textarea | ya | default: *"Hasil pemeriksaan fisik, pemeriksaan penunjang dan laboratorium dalam batas normal, kecuali:"* |
| `kesimpulan_kategori` | 613 | Kesimpulan MCU | radio | ya | 4 kategori di atas |
| `kesimpulan_catatan` | 615 | Catatan Kesimpulan MCU | textarea | ya | wajib bila kategori 2/3/4 |
| `rekomendasi` | 616 | Rekomendasi MCU | textarea | ya | |
| `dokter_pemeriksa_id` | 82 | Petugas Pelaksana (reuse) | select | ya | snapshot dari `emr.pegawai_id` bila kosong |

> `kesimpulan_kategori` memakai objek **613** (bukan 615) agar urutan objek tetap monoton dengan urutan field tabel.

### Dashboard & Form Master

```php
['form_id' => 70, 'nama_form' => 'Kesimpulan MCU', 'slug' => 'kesimpulan_mcu', 'id_dash_menu' => '14.273', 'ri' => 0, 'rj' => 0, 'igd' => 0, 'mcu' => 1],
```

### Mapping

```php
70 => [
    'tanggal_kesimpulan' => 611,
    'jam_kesimpulan' => 612,
    'kesimpulan_kategori' => 613,
    'interpretasi' => 614,
    'kesimpulan_catatan' => 615,
    'rekomendasi' => 616,
    'dokter_pemeriksa_id' => 82,
],
```

### Akses EHR

```php
['profesi_id' => 1, 'form_id' => 70, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
// Perawat boleh membaca hasil MCU (edukasi, EURO rujukan) tetapi tidak boleh mengisi.
['profesi_id' => 2, 'form_id' => 70, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
```

### Validasi

- `tanggal_kesimpulan` `required|date`; `jam_kesimpulan` `required|date_format:H:i`.
- `kesimpulan_kategori` `required|in:1,2,3,4`.
- `withValidator`: `kesimpulan_catatan` wajib bila kategori ∈ {2,3,4}.
- `interpretasi` & `rekomendasi` `required|string|max:5000`.
- **Cross-form gate (soft):** `KesimpulanMCUController::index()` menampilkan banner *"8 dari 14 organ belum diperiksa"* — tidak memblokir, tapi `KesimpulanHelper::organBelumDiperiksa($registrasi_detail_id)` mengembalikan daftar slug form yang belum punya EMR untukhammertslregistrasi yang sama.

### Cetak

`resources/views/moduls/EMR/KesimpulanMcu/print.blade.php` — **resume MCU 2 halaman** (mengikuti `cetak_resume_mcu.php` legacy):
- Halaman 1 (cover): kop, identitas, daftar tes yang dijalankan, **ringkasan temuan per organ** (dari `McuHelper::ringkasan()`), interpretasi, kesimpulan.
- Halaman 2: rekomendasi, catatan, paraf dokter pemeriksa & ttd.

---

## 5. Form 71 — Catatan Keperawatan / TTV MCU

### Rasional

Form **pembuka** episode MCU. Dikfilled perawat MCU sebelum pasieniboldperiksa dokter spesialis. Berisi anamnesis, vital sign, anthropometri, dan riwayat keluarga/gaya hidup. Semua 14 form spesialis memakai header identifikasi dari form ini secara read-only.

### Struktur Field

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `tanggal_ttv` | 617 | Tanggal Pemeriksaan TTV MCU | date | ya | |
| `jam_ttv` | 618 | Jam Pemeriksaan TTV MCU | time | ya | |
| `keluhan_saat_ini` | 619 | Keluhan Saat Ini MCU | textarea | ya | |
| `riwayat_penyakit` | 620 | Riwayat Penyakit MCU | textarea | ya | |
| `riwayat_keluarga_ayah` | 621 | Riwayat Keluarga MCU | text | ya | |
| `riwayat_keluarga_ibu` | 621 | Riwayat Keluarga MCU | text | ya | |
| `riwayat_keluarga_lain` | 621 | Riwayat Keluarga MCU | text | tidak | |
| `riwayat_merokok` | 622 | Riwayat Kebiasaan MCU | radio | ya | Tidak / Ya → jumlah & cara |
| `riwayat_merokok_detail` | 622 | Riwayat Kebiasaan MCU | text | ya bila Ya | |
| `riwayat_alkohol` | 622 | Riwayat Kebiasaan MCU | radio | ya | Tidak / Ya |
| `riwayat_alkohol_detail` | 622 | Riwayat Kebiasaan MCU | text | ya bila Ya | |
| `riwayat_olahraga` | 622 | Riwayat Kebiasaan MCU | radio | ya | Tidak / Ya |
| `riwayat_olahraga_detail` | 622 | Riwayat Kebiasaan MCU | text | ya bila Ya | |
| `riwayat_alergi` | 623 | Riwayat Alergi MCU | textarea | ya | *"Tidak ada"* bila tidak ada |
| `jenis_pekerjaan` | 624 | Data Diri MCU | select | ya | `SelectOption::pekerjaan` |
| `sistolik` | 6 | TD Sistolik (reuse) | number | ya | |
| `diastolik` | 7 | TD Diastolik (reuse) | number | ya | |
| `nadi` | 10 | Nadi (reuse) | number | ya | |
| `pernapasan` | 12 | Pernapasan (reuse) | number | ya | |
| `suhu` | 11 | Suhu (reuse) | number | ya | |
| `berat_badan` | 8 | Berat Badan (reuse) | number | ya | kg |
| `tinggi_badan` | 9 | Tinggi Badan (reuse) | number | ya | cm |
| `bmi` | 58 | BMI (reuse) | number | ya | **TURUNAN, dihitung server** |
| `lingkar_perut` | 624 | Data Diri MCU | number | ya | cm |
| `kategori_bmi` | 624 | Data Diri MCU | text | ya | **TURUNAN** (Kurus/Normal/Overweight/Obesitas) |

### Dashboard & Form Master

```php
['form_id' => 71, 'nama_form' => 'Catatan Keperawatan / TTV MCU', 'slug' => 'ttv_mcu', 'id_dash_menu' => '14.274', 'ri' => 0, 'rj' => 0, 'igd' => 0, 'mcu' => 1],
```

### Mapping

```php
71 => [
    'tanggal_ttv' => 617, 'jam_ttv' => 618,
    'keluhan_saat_ini' => 619,
    'riwayat_penyakit' => 620,
    'riwayat_keluarga_ayah' => 621, 'riwayat_keluarga_ibu' => 621, 'riwayat_keluarga_lain' => 621,
    'riwayat_merokok' => 622, 'riwayat_merokok_detail' => 622,
    'riwayat_alkohol' => 622, 'riwayat_alkohol_detail' => 622,
    'riwayat_olahraga' => 622, 'riwayat_olahraga_detail' => 622,
    'riwayat_alergi' => 623,
    'jenis_pekerjaan' => 624, 'lingkar_perut' => 624, 'kategori_bmi' => 624,
    'sistolik' => 6, 'diastolik' => 7, 'nadi' => 10, 'pernapasan' => 12, 'suhu' => 11,
    'berat_badan' => 8, 'tinggi_badan' => 9, 'bmi' => 58,
],
```

> Kedua field riwayat olahraga (`riwayat_olahraga`, `riwayat_olahraga_detail`) dipetakan ke objek 622 yang sama dengan field gaya hidup lainnya.

### Akses EHR

```php
['profesi_id' => 1, 'form_id' => 71, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
// Perawat MCU mengisi TTV; akses penuh.
['profesi_id' => 2, 'form_id' => 71, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
```

> **Konfirmasi klinis:** bila kebijakan RS hanya mengizinkan dokter yang mengisi TTV (bila perawat MCU bukan tenaga invasif), ubah baris profesi 2 menjadi `create=0, read=1`. Flag ini dapat dikelola dari menu *Akses EHR*.

### Validasi

- `tanggal_ttv` `required|date`; `jam_ttv` `required|date_format:H:i`.
- `sistolik` `required|numeric|between:60,260`; `diastolik` `required|numeric|between:30,160`.
- `nadi` `required|numeric|between:30,220`; `pernapasan` `required|numeric|between:8,60`; `suhu` `required|numeric|between:33,43`.
- `berat_badan` `required|numeric|between:2,300`; `tinggi_badan` `required|numeric|between:50,250`.
- `withValidator`: `*_detail` wajib bila `* = 'Ya'`.
- `filteredData()` menghitung ulang `bmi` dan `kategori_bmi` dari `berat_badan`/`tinggi_badan` — nilai browser **dibuang** (PANDUAN §2.4).
- Tanda vital di luar rentang displaysbadge peringatan (bukan blocking), mengikuti pola EWS.

### Cetak

`.../TtvMcu/print.blade.php` — lembar TTV & anamnesis, menjadi halaman pembuka resume MCU.

---

## 6. Form 72 — Visus Mata MCU

### Rasional

Pengukuran visus menggunakan Snellen card di poli mata, diisi olehindle refraksi/optometris. Nilai AKG = visus dasar yang dipakai untuksurat keterangan.

### Struktur Field

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `visus_od_avod` | 625 | Visus Mata Kanan MCU | text | ya | mata kanan (OD), visus jauh |
| `visus_od_c` | 625 | Visus Mata Kanan MCU | text | ya | visus dekat mata kanan |
| `visus_od_add` | 625 | Visus Mata Kanan MCU | text | ya | Addition (daya tambah) mata kanan |
| `visus_od_tod` | 625 | Visus Mata Kanan MCU | text | tidak | terminologi optometris (lihat catatan) |
| `visus_od_tos` | 625 | Visus Mata Kanan MCU | text | tidak | terminologi optometris (lihat catatan) |
| `visus_os_avos` | 626 | Visus Mata Kiri MCU | text | ya | mata kiri (OS), visus jauh |
| `visus_os_c` | 626 | Visus Mata Kiri MCU | text | ya | visus dekat mata kiri |
| `visus_os_add` | 626 | Visus Mata Kiri MCU | text | ya | Addition mata kiri |
| `visus_os_pd` | 626 | Visus Mata Kiri MCU | text | tidak | Phoria distance |
| `tolak` | 627 | Keterangan Visus MCU | text | ya | hanya bila visus < 6/60 |
| `tes_konfrontasi` | 628 | Konfrontasi & Buta Warna MCU | text | ya | Normal / Tidak |
| `buta_warna` | 628 | Konfrontasi & Buta Warna MCU | radio | ya | Tidak / Ishihara Test |

> **CATATAN terminologi:** legacy memuat kolom `tod` dan `tos` pada blok mata **kanan**, dan `pd` pada blok mata **kiri** — penamaan tersebut tidak konsisten secara optometris dan tampaknya warisan kolom lama. Desain ini mempertahankan nama variabel tersebut apa adanya agar data lama tetap terbaca, namun menetapkannya **opsional**. Wajib dikonfirmasi ke tim optometris sebelum implementasi; bila ternyata tidak dipakai, cukup keluarkan dari mapping.

### Dashboard & Form Master

```php
['form_id' => 72, 'nama_form' => 'Visus Mata MCU', 'slug' => 'visus_mata_mcu', 'id_dash_menu' => '14.275', 'ri' => 0, 'rj' => 0, 'igd' => 0, 'mcu' => 1],
```

### Mapping

```php
72 => [
    'visus_od_avod' => 625, 'visus_od_c' => 625, 'visus_od_add' => 625, 'visus_od_tod' => 625, 'visus_od_tos' => 625,
    'visus_os_avos' => 626, 'visus_os_c' => 626, 'visus_os_add' => 626, 'visus_os_pd' => 626,
    'tolak' => 627,
    'tes_konfrontasi' => 628, 'buta_warna' => 628,
],
```

### Akses EHR

```php
['profesi_id' => 1, 'form_id' => 72, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 2, 'form_id' => 72, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
```

### Validasi

- Lima kolom mata kanan & empat kolom mata kiri `required|string|max:10`.
- Format tervalidasi: `regex:/^\d+\/\d+$/` **atau** `in:NPL,HD,BB,FD,ED,ND,CD,VK,PL` (buta) atau `-` (tidak diuji).
- `visus` < 6/60 ⇒ `tolak` wajib diisi.
- `buta_warna = 'Ishihara Test'` ⇒ wajib menyertakan hasil uji pada `tes_konfrontasi`.

### Cetak

`.../VisusMataMcu/print.blade.php` — tabel visus bilateral.

---

## 7. Form 73 — Obstetri MCU

### Rasional
Anamnesis obstetri untuk pasien perempuan (pria cukup memilih "L" pada jenis kelamin; sisa field disembunyikan). Mengisi data haid, kontrasepsi, riwayat kehamilan, dan pemeriksaan preventif (pap smear).

### Struktur Field

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `jenis_kelamin_pasien` | 629 | Kehamilan MCU | radio | ya | L / P — P membuka sisa field |
| `menarche` | 629 | Kehamilan MCU | text | ya (bila P) | umur menarche |
| `haid_terakhir` | 629 | Kehamilan MCU | date | ya (bila P) | |
| `siklus_haid` | 629 | Kehamilan MCU | text | ya (bila P) | |
| `nyeri_haid` | 629 | Kehamilan MCU | radio | ya (bila P) | Ya / Tidak |
| `kb_metode` | 630 | Riwayat Kehamilan MCU | select | ya (bila P) | |
| `kb_aktif` | 630 | Riwayat Kehamilan MCU | radio | ya (bila P) | Ya/Tidak |
| `kehamilan_g` | 630 | Riwayat Kehamilan MCU | number | ya (bila P) | |
| `kehamilan_p` | 630 | Riwayat Kehamilan MCU | number | ya (bila P) | |
| `kehamilan_a` | 630 | Riwayat Kehamilan MCU | number | ya (bila P) | abortus/keguguran |
| `keguguran_terakhir` | 630 | Riwayat Kehamilan MCU | date | tidak | |
| `keguguran_keterangan` | 631 | Riwayat Kehamilan MCU | text | tidak | |
| `hamil_terakhir` | 631 | Riwayat Kehamilan MCU | date | tidak | tanggal kehamilan terakhir |
| `hamil_terakhir_lama` | 631 | Riwayat Kehamilan MCU | text | tidak | lama kehamilan |
| `jml_anak_lahir` | 631 | Riwayat Kehamilan MCU | number | ya | |
| `jml_anak_hidup` | 631 | Riwayat Kehamilan MCU | number | ya | |
| `waktu_paps` | 632 | Riwayat Kehamilan MCU | date | tidak | |
| `sc` | 632 | Riwayat Kehamilan MCU | text | tidak | |
| `coitus` | 632 | Riwayat Kehamilan MCU | text | tidak | |
| `coitus_terakhir` | 632 | Riwayat Kehamilan MCU | date | tidak | |
| `discharge` | 632 | Riwayat Kehamilan MCU | text | tidak | |
| `gatal` | 632 | Riwayat Kehamilan MCU | text | tidak | |
| `hari` | 632 | Riwayat Kehamilan MCU | text | tidak | |
| `hormon` | 632 | Riwayat Kehamilan MCU | text | tidak | |
| `normal` | 632 | Riwayat Kehamilan MCU | text | tidak | |

### Dashboard & Form Master

```php
['form_id' => 73, 'nama_form' => 'Obstetri MCU', 'slug' => 'obstetri_mcu', 'id_dash_menu' => '14.276', 'ri' => 0, 'rj' => 0, 'igd' => 0, 'mcu' => 1],
```

### Mapping

```php
73 => [
    'jenis_kelamin_pasien' => 629,
    'menarche' => 629, 'haid_terakhir' => 629, 'siklus_haid' => 629, 'nyeri_haid' => 629,
    'kb_metode' => 630, 'kb_aktif' => 630,
    'kehamilan_g' => 630, 'kehamilan_p' => 630, 'kehamilan_a' => 630, 'keguguran_terakhir' => 630,
    'keguguran_keterangan' => 631,
    'hamil_terakhir' => 631, 'hamil_terakhir_lama' => 631, 'jml_anak_lahir' => 631, 'jml_anak_hidup' => 631,
    'waktu_paps' => 632, 'sc' => 632, 'coitus' => 632, 'coitus_terakhir' => 632,
    'discharge' => 632, 'gatal' => 632, 'hari' => 632, 'hormon' => 632, 'normal' => 632,
],
```

### Akses EHR

```php
['profesi_id' => 1, 'form_id' => 73, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 2, 'form_id' => 73, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
```

### Validasi

- `jenis_kelamin_pasien` `required|in:L,P`.
- Bila `= P`: seluruh field obstetri **wajib**; `kehamilan_g >= kehamilan_p + kehamilan_a` (divalidasi).
- Bila `= L`: semua field obstetri dikosongkan & dibuang dari payload.
- `waktu_paps` tidak boleh di masa depan.

### Cetak

`.../ObstetriMcu/print.blade.php`.

---

## 8. Form 74 — Penyakit Dalam MCU

### Rasional

Pemeriksaan fisik oleh dokter spesialis penyakit dalam, mencakup 12 region tubuh. Dikelompokkan menjadi 3 objek grup agar hemat objek: **inspasial**, **toraks**, dan **abdominal/ekstremitas**.

### Struktur Field

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `keluhan_saat_ini` | 13 | Keluhan Utama (reuse) | textarea | ya | |
| `keluhan_uraian` | 633 | Riwayat Penyakit Dalam MCU | textarea | ya bila keluhan ada | |
| `riwayat_penyakit` | 633 | Riwayat Penyakit Dalam MCU | textarea | ya | |
| `kepala_ada` / `kepala` | 634 | Kelainan Inspasial MCU | radio + text | ya | Normal / Kelainan |
| `kulit_ada` / `kulit` | 634 | Kelainan Inspasial MCU | radio + text | ya | |
| `mata_ada` / `mata` | 634 | Kelainan Inspasial MCU | radio + text | ya | |
| `mulut_ada` / `mulut` | 634 | Kelainan Inspasial MCU | radio + text | ya | |
| `hidung_ada` / `hidung` | 634 | Kelainan Inspasial MCU | radio + text | ya | |
| `tenggorokan_ada` / `tenggorokan` | 634 | Kelainan Inspasial MCU | radio + text | ya | |
| `telinga_ada` / `telinga` | 634 | Kelainan Inspasial MCU | radio + text | ya | |
| `leher_ada` / `leher` | 634 | Kelainan Inspasial MCU | radio + text | ya | |
| `mamae_ada` / `mamae` | 634 | Kelainan Inspasial MCU | radio + text | ya | |
| `thorax_ada` / `thorax` | 635 | Kelainan Toraks MCU | radio + text | ya | |
| `jantung_ada` / `jantung` | 635 | Kelainan Toraks MCU | radio + text | ya | |
| `paru_ada` / `paru` | 635 | Kelainan Toraks MCU | radio + text | ya | |
| `abdomen_ada` / `abdomen` | 636 | Kelainan Abdominal MCU | radio + text | ya | |
| `ekstremitas_ada` / `ekstremitas` | 636 | Kelainan Abdominal MCU | radio + text | ya | |
| `lain_ada` / `lain` | 636 | Kelainan Abdominal MCU | radio + text | ya | |
| `diagnosa` | 40 | Diagnosa Medis (reuse) | textarea | ya | |
| `rekomendasi` | 77 | Keterangan (reuse) | textarea | ya | |

### Dashboard & Form Master

```php
['form_id' => 74, 'nama_form' => 'Penyakit Dalam MCU', 'slug' => 'penyakit_dalam_mcu', 'id_dash_menu' => '14.277', 'ri' => 0, 'rj' => 0, 'igd' => 0, 'mcu' => 1],
```

### Mapping

```php
74 => [
    'keluhan_saat_ini' => 13, 'keluhan_uraian' => 633, 'riwayat_penyakit' => 633,
    'diagnosa' => 40, 'rekomendasi' => 77,
],
$regionInspasial = ['kepala', 'kulit', 'mata', 'mulut', 'hidung', 'tenggorokan', 'telinga', 'leher', 'mamae'];
foreach ($regionInspasial as $r) { $mapping[74][$r.'_ada'] = 634; $mapping[74][$r] = 634; }
$regionToraks = ['thorax', 'jantung', 'paru'];
foreach ($regionToraks as $r) { $mapping[74][$r.'_ada'] = 635; $mapping[74][$r] = 635; }
$regionAbdomen = ['abdomen', 'ekstremitas', 'lain'];
foreach ($regionAbdomen as $r) { $mapping[74][$r.'_ada'] = 636; $mapping[74][$r] = 636; }
```

### Akses EHR

```php
['profesi_id' => 1, 'form_id' => 74, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 2, 'form_id' => 74, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
```

### Validasi

- Semua `*_ada` `required|in:0,1`.
- `withValidator`: `{r}` wajib bila `{r}_ada = '1'`.
- `diagnosa` `required|string|max:2000`; `rekomendasi` `required|string|max:2000`.
- Bila seluruh 12 region `= 0` (Normal), tampilkan badge "Pemeriksaan fisik normal".

### Cetak

`.../PenyakitDalamMcu/print.blade.php`.

---

## 9. Form 75 — Jantung MCU

### Rasional
Pemeriksaan jantung + EKG + Treadmill + Echo. Legacy menyediakan 4 sub-panel.

### Struktur Field

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `keluhan_saat_ini` | 13 | Keluhan Utama (reuse) | textarea | ya | |
| `jantung_ada` | 637 | Pemeriksaan Jantung MCU | radio | ya | Tidak Ada / Ada |
| `jantung` | 637 | Pemeriksaan Jantung MCU | textarea | ya bila Ada | |
| `ekg_hr` | 638 | EKG MCU | number | ya | denyut jantung (x/menit) |
| `ekg_rhythm` | 638 | EKG MCU | select | ya | Sinus Rhythm / lainnya (teks) |
| `ekg_kelainan` | 638 | EKG MCU | text | ya | |
| `ekg_kesimpulan` | 638 | EKG MCU | text | ya | |
| `threadmill_case` | 639 | Treadmill MCU | radio | ya | Tidak / Ya |
| `threadmill_kesimpulan` | 639 | Treadmill MCU | text | ya | |
| `echocardiography` | 640 | Echocardiography MCU | radio | ya | Tidak / Ya |
| `echocardiography_kesimpulan` | 640 | Echocardiography MCU | text | ya | |
| `diagnosa` | 40 | Diagnosa Medis (reuse) | textarea | ya | |
| `rekomendasi` | 77 | Keterangan (reuse) | textarea | ya | |

> `ekg_rhythm` mewarisi typo legacy `SinusRhytm` — **perbaiki** menjadi `Sinus Rhythm`.

### Dashboard & Form Master

```php
['form_id' => 75, 'nama_form' => 'Jantung MCU', 'slug' => 'jantung_mcu', 'id_dash_menu' => '14.278', 'ri' => 0, 'rj' => 0, 'igd' => 0, 'mcu' => 1],
```

### Mapping

```php
75 => [
    'keluhan_saat_ini' => 13,
    'jantung_ada' => 637, 'jantung' => 637,
    'ekg_hr' => 638, 'ekg_rhythm' => 638, 'ekg_kelainan' => 638, 'ekg_kesimpulan' => 638,
    'threadmill_case' => 639, 'threadmill_kesimpulan' => 639,
    'echocardiography' => 640, 'echocardiography_kesimpulan' => 640,
    'diagnosa' => 40, 'rekomendasi' => 77,
],
```

### Akses EHR

```php
['profesi_id' => 1, 'form_id' => 75, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 2, 'form_id' => 75, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
```

### Validasi

- `ekg_hr` `required|numeric|between:30,220`.
- `withValidator`: `jantung` wajib bila `jantung_ada = '1'`; `*_kesimpulan` wajib bila `* = 'Ya'`.
- `ekg_rhythm` `required|string|max:50`.
- Rekomendasi: `jantung_ada = '1'` atau kelainan EKG ⇒ `rekomendasi` **wajib memuat** kata kunci kontrol/tatalaksana (saran perokok, rujukan kardiologi, dll.) — validasi soft (badge peringatan, bukan blocking).

### Cetak

`.../JantungMcu/print.blade.php` — memuat **grafik EKG** (gambar PNG dari master `tindakan`/lampiran) bila ada.

---

## 10. Form 76 — THT MCU

### Rasional
Pemeriksaan Telinga, Hidung, Tenggorokan.

### Struktur Field

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `keluhan_saat_ini` | 13 | Keluhan Utama (reuse) | textarea | ya | |
| `telinga_kanan_ada` | 641 | Telinga MCU | radio | ya | Tidak Ada / Ada |
| `telinga_kanan` | 641 | Telinga MCU | textarea | ya bila Ada | |
| `telinga_kiri_ada` | 641 | Telinga MCU | radio | ya | |
| `telinga_kiri` | 641 | Telinga MCU | textarea | ya bila Ada | |
| `hidung_ada` | 642 | Hidung MCU | radio | ya | |
| `hidung` | 642 | Hidung MCU | textarea | ya bila Ada | |
| `tenggorokan_ada` | 643 | Tenggorokan MCU | radio | ya | |
| `tenggorokan` | 643 | Tenggorokan MCU | textarea | ya bila Ada | |
| `tht_lainnya_ada` | 644 | Kelainan THT Lainnya MCU | radio | ya | |
| `tht_lainnya` | 644 | Kelainan THT Lainnya MCU | textarea | ya bila Ada | |
| `diagnosa` | 40 | Diagnosa Medis (reuse) | textarea | ya | |
| `rekomendasi` | 77 | Keterangan (reuse) | textarea | ya | |

> Telinga kanan & kiri **berbagi objek 641** dengan awalan berbeda (`telinga_kanan_ada` vs `telinga_kiri_ada`) — hemat 1 objek, laporan tetap via `variabel`.

### Dashboard & Form Master

```php
['form_id' => 76, 'nama_form' => 'THT MCU', 'slug' => 'tht_mcu', 'id_dash_menu' => '14.279', 'ri' => 0, 'rj' => 0, 'igd' => 0, 'mcu' => 1],
```

### Mapping

```php
76 => [
    'keluhan_saat_ini' => 13,
    'telinga_kanan_ada' => 641, 'telinga_kanan' => 641,
    'telinga_kiri_ada' => 641, 'telinga_kiri' => 641,
    'hidung_ada' => 642, 'hidung' => 642,
    'tenggorokan_ada' => 643, 'tenggorokan' => 643,
    'tht_lainnya_ada' => 644, 'tht_lainnya' => 644,
    'diagnosa' => 40, 'rekomendasi' => 77,
],
```

### Akses EHR

```php
['profesi_id' => 1, 'form_id' => 76, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 2, 'form_id' => 76, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
```

### Validasi

Pola standar: semua `*_ada` `required|in:0,1`; `{r}` wajib bila `{r}_ada = '1'`; `diagnosa` & `rekomendasi` wajib.

### Cetak

`.../ThtMcu/print.blade.php`.

---

## 11. Form 77 — Mata MCU

### Rasional
Pemeriksaan organ mata oleh dokter Spesialis mata (di luar pengukuran visus form 72). Berisi pemeriksaan mata kanan/kiri, kelainan, diagnosis, rekomendasi.

### Struktur Field

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `keluhan_saat_ini` | 13 | Keluhan Utama (reuse) | textarea | ya | |
| `visus_od` | 645 | Pemeriksaan Mata Kanan MCU | text | ya | read-only dari form 72 bila terisi |
| `mata_kanan_ada` | 645 | Pemeriksaan Mata Kanan MCU | radio | ya | Tidak Ada / Ada |
| `mata_kanan` | 645 | Pemeriksaan Mata Kanan MCU | textarea | ya bila Ada | |
| `visus_os` | 646 | Pemeriksaan Mata Kiri MCU | text | ya | read-only dari form 72 |
| `mata_kiri_ada` | 646 | Pemeriksaan Mata Kiri MCU | radio | ya | |
| `mata_kiri` | 646 | Pemeriksaan Mata Kiri MCU | textarea | ya bila Ada | |
| `kelainan_mata_lainnya_ada` | 647 | Kelainan Mata MCU | radio | ya | |
| `kelainan_mata_lainnya` | 647 | Kelainan Mata MCU | textarea | ya bila Ada | |
| `diagnosa` | 40 | Diagnosa Medis (reuse) | textarea | ya | |
| `rekomendasi` | 77 | Keterangan (reuse) | textarea | ya | |

> `visus_od`/`visus_os` di-prefill dari `EmrHelper::latestValuesByVariabel(formIdBySlug('visus_mata_mcu'), ...)` — read-only, tidak disimpan ulang. Bila tidak diisi manual, jangan ada.

### Dashboard & Form Master

```php
['form_id' => 77, 'nama_form' => 'Mata MCU', 'slug' => 'mata_mcu', 'id_dash_menu' => '14.280', 'ri' => 0, 'rj' => 0, 'igd' => 0, 'mcu' => 1],
```

### Mapping

```php
77 => [
    'keluhan_saat_ini' => 13,
    'visus_od' => 645, 'mata_kanan_ada' => 645, 'mata_kanan' => 645,
    'visus_os' => 646, 'mata_kiri_ada' => 646, 'mata_kiri' => 646,
    'kelainan_mata_lainnya_ada' => 647, 'kelainan_mata_lainnya' => 647,
    'diagnosa' => 40, 'rekomendasi' => 77,
],
```

### Akses EHR

```php
['profesi_id' => 1, 'form_id' => 77, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 2, 'form_id' => 77, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
```

### Validasi

Pola standar form organ + `visus_od`/`visus_os` `nullable|string|max:10`.

### Cetak

`.../MataMcu/print.blade.php`.

---

## 12. Form 78 — Gigi MCU

### Rasional
Pemeriksaan gigi & kebutuhan tindakan (tambal, cabut, Scaling, akar, prostesis).

### Struktur Field

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `keluhan_saat_ini` | 13 | Keluhan Utama (reuse) | textarea | ya | |
| `gigi_ada` | 649 | Pemeriksaan Gigi MCU | radio | ya | Tidak Ada / Ada |
| `gigi` | 649 | Pemeriksaan Gigi MCU | textarea | ya bila Ada | |
| `kebutuhan_perawatan_ada` | 650 | Kebutuhan Perawatan Gigi MCU | radio | ya | Tidak / Ya |
| `kebutuhan_perawatan` | 650 | Kebutuhan Perawatan Gigi MCU | textarea | ya bila Ya | |
| `diagnosa` | 40 | Diagnosa Medis (reuse) | textarea | ya | |
| `rekomendasi` | 77 | Keterangan (reuse) | textarea | ya | |

> Legacy memakai `expertise_gigi` — disederhanakan menjadi `kebutuhan_perawatan` + `rekomendasi`.

### Dashboard & Form Master

```php
['form_id' => 78, 'nama_form' => 'Gigi MCU', 'slug' => 'gigi_mcu', 'id_dash_menu' => '14.281', 'ri' => 0, 'rj' => 0, 'igd' => 0, 'mcu' => 1],
```

### Mapping

```php
78 => [
    'keluhan_saat_ini' => 13,
    'gigi_ada' => 649, 'gigi' => 649,
    'kebutuhan_perawatan_ada' => 650, 'kebutuhan_perawatan' => 650,
    'diagnosa' => 40, 'rekomendasi' => 77,
],
```

### Akses EHR

```php
['profesi_id' => 1, 'form_id' => 78, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 2, 'form_id' => 78, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
```

### Validasi

Pola standar. `kebutuhan_perawatan = 'Ya'` ⇒ `rekomendasi` wajib spesifik (nama tindakan).

### Cetak

`.../GigiMcu/print.blade.php`.

---

## 13. Form 79 — OBGYN MCU

### Rasional
Pemeriksaan lokalis obstetri-ginekologi oleh dokter SpOG.

### Struktur Field

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `keluhan_saat_ini` | 13 | Keluhan Utama (reuse) | textarea | ya | |
| `lokalis_ada` | 652 | Pemeriksaan Lokalis OBGYN MCU | radio | ya | Tidak Ada / Ada |
| `lokalis` | 652 | Pemeriksaan Lokalis OBGYN MCU | textarea | ya bila Ada | uterus, adnexa, serviks |
| `diagnosa` | 40 | Diagnosa Medis (reuse) | textarea | ya | |
| `rekomendasi` | 77 | Keterangan (reuse) | textarea | ya | |

### Dashboard & Form Master

```php
['form_id' => 79, 'nama_form' => 'OBGYN MCU', 'slug' => 'obgyn_mcu', 'id_dash_menu' => '14.282', 'ri' => 0, 'rj' => 0, 'igd' => 0, 'mcu' => 1],
```

### Mapping

```php
79 => [
    'keluhan_saat_ini' => 13,
    'lokalis_ada' => 652, 'lokalis' => 652,
    'diagnosa' => 40, 'rekomendasi' => 77,
],
```

### Akses EHR

```php
['profesi_id' => 1, 'form_id' => 79, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 2, 'form_id' => 79, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
```

### Validasi

Pola standar. `lokalis_ada = 'Ada'` ⇒ `diagnosa` **wajib** (bukan opsional).

### Cetak

`.../ObgynMcu/print.blade.php`.

---

## 14. Form 80 — Bedah MCU

### Rasional
Pemeriksaan bedah (montase, gerak sendi, benjolan, hernia, varises, dan kelainan bedah lainnya).

### Struktur Field

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `keluhan_saat_ini` | 13 | Keluhan Utama (reuse) | textarea | ya | |
| `lokalis_ada` | 655 | Pemeriksaan Localis Bedah MCU | radio | ya | Tidak Ada / Ada |
| `lokalis` | 655 | Pemeriksaan Localis Bedah MCU | textarea | ya bila Ada | |
| `diagnosa` | 40 | Diagnosa Medis (reuse) | textarea | ya | |
| `rekomendasi` | 77 | Keterangan (reuse) | textarea | ya | |

### Dashboard & Form Master

```php
['form_id' => 80, 'nama_form' => 'Bedah MCU', 'slug' => 'bedah_mcu', 'id_dash_menu' => '14.283', 'ri' => 0, 'rj' => 0, 'igd' => 0, 'mcu' => 1],
```

### Mapping

```php
80 => [
    'keluhan_saat_ini' => 13,
    'lokalis_ada' => 655, 'lokalis' => 655,
    'diagnosa' => 40, 'rekomendasi' => 77,
],
```

### Akses EHR

```php
['profesi_id' => 1, 'form_id' => 80, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 2, 'form_id' => 80, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
```

### Validasi

Pola standar.

### Cetak

`.../BedahMcu/print.blade.php`.

---

## 15. Form 81 — Neurologi MCU

### Rasional
Pemeriksaan neurologi (kesadaran, motorik, sensorik, refleks, koordinasi).

### Struktur Field

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `keluhan_saat_ini` | 13 | Keluhan Utama (reuse) | textarea | ya | |
| `neurology_ada` | 658 | Pemeriksaan Neurologi MCU | radio | ya | Tidak Ada / Ada |
| `neurology` | 658 | Pemeriksaan Neurologi MCU | textarea | ya bila Ada | |
| `diagnosa` | 40 | Diagnosa Medis (reuse) | textarea | ya | |
| `rekomendasi` | 77 | Keterangan (reuse) | textarea | ya | |

### Dashboard & Form Master

```php
['form_id' => 81, 'nama_form' => 'Neurologi MCU', 'slug' => 'neurologi_mcu', 'id_dash_menu' => '14.284', 'ri' => 0, 'rj' => 0, 'igd' => 0, 'mcu' => 1],
```

### Mapping

```php
81 => [
    'keluhan_saat_ini' => 13,
    'neurology_ada' => 658, 'neurology' => 658,
    'diagnosa' => 40, 'rekomendasi' => 77,
],
```

### Akses EHR

```php
['profesi_id' => 1, 'form_id' => 81, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 2, 'form_id' => 81, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
```

### Validasi

Pola standar.

### Cetak

`.../NeurologiMcu/print.blade.php`.

---

## 16. Form 82 — Gizi MCU

### Rasional
Penilaian status gizi (BMI, lingkar lengan atas, pola diet) oleh dokter SpGizi.

### Struktur Field

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `tinggi_badan` | 9 | Tinggi Badan (reuse) | number | ya | cm — prefilled dari form 71 |
| `berat_badan` | 8 | Berat Badan (reuse) | number | ya | kg — prefilled dari form 71 |
| `bmi` | 58 | BMI (reuse) | number | ya | **TURUNAN** |
| `kategori_bmi` | 58 | BMI (reuse) | text | ya | **TURUNAN** |
| `lingkar_lengan_atas` | 661 | Objektif Gizi MCU | number | ya | cm |
| `status_gizi` | 661 | Objektif Gizi MCU | select | ya | Kurus · Normal · Overweight · Obesitas · Malnutrisi |
| `pola_diet` | 662 | Status Gizi & Pola Diet MCU | text | ya | |
| `risiko_gizi` | 662 | Status Gizi & Pola Diet MCU | text | ya | |
| `diagnosa` | 40 | Diagnosa Medis (reuse) | textarea | ya | |
| `rekomendasi` | 77 | Keterangan (reuse) | textarea | ya | |

### Dashboard & Form Master

```php
['form_id' => 82, 'nama_form' => 'Gizi MCU', 'slug' => 'gizi_mcu', 'id_dash_menu' => '14.285', 'ri' => 0, 'rj' => 0, 'igd' => 0, 'mcu' => 1],
```

### Mapping

```php
82 => [
    'tinggi_badan' => 9, 'berat_badan' => 8, 'bmi' => 58, 'kategori_bmi' => 58,
    'lingkar_lengan_atas' => 661, 'status_gizi' => 661,
    'pola_diet' => 662, 'risiko_gizi' => 662,
    'diagnosa' => 40, 'rekomendasi' => 77,
],
```

### Akses EHR

```php
['profesi_id' => 1, 'form_id' => 82, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 2, 'form_id' => 82, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
```

### Validasi

- `lingkar_lengan_atas` `required|numeric|between:10,60`.
- `bmi` & `kategori_bmi` **dihitung ulang server** dari TB/BB; nilai browser dibuang.
- `withValidator`: bila BMI > 30 atau < 18,5 ⇒ `rekomendasi` wajib menyebut rencana tindak lanjut (kontrol diet / penambahan berat).
- `status_gizi` `required|in:Kurus,Normal,Overweight,Obesitas,Malnutrisi`.

### Cetak

`.../GiziMcu/print.blade.php` — grafik lingkung lengan atas & BMI.

---

## 17. Form 83 — Urologi MCU

### Rasional
Pemeriksaan fisik sistem urinarius (ginjal, kandung kemih, uretra).

### Struktur Field

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `keluhan_saat_ini` | 13 | Keluhan Utama (reuse) | textarea | ya | |
| `fisik_ada` | 664 | Pemeriksaan Fisik Urologi MCU | radio | ya | Tidak Ada / Ada |
| `fisik` | 664 | Pemeriksaan Fisik Urologi MCU | textarea | ya bila Ada | |
| `diagnosa` | 40 | Diagnosa Medis (reuse) | textarea | ya | |
| `rekomendasi` | 77 | Keterangan (reuse) | textarea | ya | |

### Dashboard & Form Master

```php
['form_id' => 83, 'nama_form' => 'Urologi MCU', 'slug' => 'urologi_mcu', 'id_dash_menu' => '14.286', 'ri' => 0, 'rj' => 0, 'igd' => 0, 'mcu' => 1],
```

### Mapping

```php
83 => [
    'keluhan_saat_ini' => 13,
    'fisik_ada' => 664, 'fisik' => 664,
    'diagnosa' => 40, 'rekomendasi' => 77,
],
```

### Akses EHR

```php
['profesi_id' => 1, 'form_id' => 83, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 2, 'form_id' => 83, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
```

### Validasi

Pola standar.

### Cetak

`.../UrologiMcu/print.blade.php`.

---

## 18. Form 84 — Psikologi MCU

### Rasional
Asesmen psikologis oleh psikolog klinis (bukan dokter), meliputi gangguan kehidupan, kesesuaian, dan saran.

### Struktur Field

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `gangguan_kehidupan_ada` | 667 | Status Psikologis MCU | radio | ya | Tidak Ada / Ada |
| `gangguan_kehidupan` | 667 | Status Psikologis MCU | textarea | ya bila Ada | |
| `skala_cemas` | 668 | Skala Cemas & Depresi MCU | number | ya | 0–4 (GAD-2 ringkas) |
| `skala_depresi` | 668 | Skala Cemas & Depresi MCU | number | ya | 0–4 |
| `interpretasi` | 669 | Interpretasi Psikologi MCU | text | ya | **TURUNAN** |
| `saran` | 670 | Saran Psikologi MCU | textarea | ya | |

> Legacy `pemeriksaan_mcu_psikologi.php` hanya punya `gangguan_kehidupan` & `saran`. Field `skala_cemas`/`skala_depresi`/`interpretasi` **ditambahkan** sebagai instrumen screening standar (GAD-2 & PHQ-2) — **opsional**, dapat dihapus bila psikologis memilih memakai instrumen lain. Bila dihapus, objek 668–669 dilepas dan `interpretasi` diisi manual.

### Dashboard & Form Master

```php
['form_id' => 84, 'nama_form' => 'Psikologi MCU', 'slug' => 'psikologi_mcu', 'id_dash_menu' => '14.287', 'ri' => 0, 'rj' => 0, 'igd' => 0, 'mcu' => 1],
```

### Mapping

```php
84 => [
    'gangguan_kehidupan_ada' => 667, 'gangguan_kehidupan' => 667,
    'skala_cemas' => 668, 'skala_depresi' => 668,
    'interpretasi' => 669,
    'saran' => 670,
],
```

### Akses EHR

```php
['profesi_id' => 1, 'form_id' => 84, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
// Psikolog klinis belum punya profesi_id di tabel `profesi` — bila tersedia, tambahkan baris:
// ['profesi_id' => X, 'form_id' => 84, ... create/read/update 1, delete 0]
['profesi_id' => 2, 'form_id' => 84, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
```

### Validasi

- `skala_cemas` & `skala_depresi` `required|integer|between:0,4`.
- `filteredData()` menghitung `interpretasi` (server-side): `total = cemas + depresi`; `0–2 = Normal`, `3–4 = Keluhan ringan`, `≥ 5 = Rujukan ke psikolog/klinisi`. Nilai browser dibuang.
- `gangguan_kehidupan = 'Ya'` ⇒ `saran` wajib memuat **minimal satu** rujukan (psikolog, psikiater, konseling).
- Bila `total ≥ 5`, tampilkan badge "Perlu rujukan".

### Cetak

`.../PsikologiMcu/print.blade.php`.

---

## 19. Agregasi Kesimpulan MCU (form 70)

### 19.1 Sumber agregasi

`KesimpulanMcuController::index()` memuat, untuk `registrasi_detail_id` yang sama, EMR terakhir tiap form organ:

```php
$formsMcu = [
    'ttv_mcu'              => ['label' => 'Tanda Vital & Anamnesis',    'ringkas' => ['sistolik', 'diastolik', 'nadi', 'suhu', 'bmi', 'kategori_bmi']],
    'visus_mata_mcu'       => ['label' => 'Visus Mata',                'ringkas' => ['visus_od_avod', 'visus_os_avos']],
    'obstetri_mcu'         => ['label' => 'Obstetri',                  'ringkas' => ['kehamilan_g', 'kehamilan_p', 'kehamilan_a', 'kb_metode']],
    'penyakit_dalam_mcu'   => ['label' => 'Penyakit Dalam',            'ringkas' => ['diagnosa']],
    'jantung_mcu'          => ['label' => 'Jantung & EKG',             'ringkas' => ['ekg_hr', 'ekg_kesimpulan']],
    'tht_mcu'              => ['label' => 'THT',                       'ringkas' => ['diagnosa']],
    'mata_mcu'             => ['label' => 'Mata',                      'ringkas' => ['diagnosa']],
    'gigi_mcu'             => ['label' => 'Gigi',                      'ringkas' => ['diagnosa', 'kebutuhan_perawatan']],
    'obgyn_mcu'            => ['label' => 'OBGYN',                     'ringkas' => ['diagnosa']],
    'bedah_mcu'            => ['label' => 'Bedah',                     'ringkas' => ['diagnosa']],
    'neurologi_mcu'        => ['label' => 'Neurologi',                 'ringkas' => ['diagnosa']],
    'gizi_mcu'             => ['label' => 'Gizi',                      'ringkas' => ['status_gizi', 'bmi']],
    'urologi_mcu'          => ['label' => 'Urologi',                   'ringkas' => ['diagnosa']],
    'psikologi_mcu'        => ['label' => 'Psikologi',                 'ringkas' => ['interpretasi', 'saran']],
];
```

### 19.2 Panel agregat di form 70

Read-only, berisi 3 bagian:

1. **Tabel Ringkasan Temuan** — 14 baris, kolom `Organ | Temuan | Keterangan`; baris yang belum diperiksa diberi badge abu "Belum diperiksa".
2. **Tombol Order Penunjang** — `route('laboratorium.create', ...)` / `route('radiologi.create', ...)` dengan `registrasi_detail_id` yang sama (menggantikan query `queryPenunjang` di legacy).
3. **Panel editable** — interpretasi, kategori kesimpulan, catatan, rekomendasi (auto-prefill `interpretasi` dari daftar temuan yang tidak normal).

### 19.3 Auto-prefill interpretasi

```php
// App\Helpers\McuHelper::interpretasiOtomatis(array $ringkasan): string
$normal = array_filter($ringkasan, fn ($baris) => trim((string) ($baris['temuan'] ?? '')) === '' || stripos((string) $baris['temuan'], 'normal') !== false);
$lainnya = array_diff_key($ringkasan, $normal);

if ($lainnya === []) {
    return 'Hasil pemeriksaan fisik, pemeriksaan penunjang dan laboratorium dalam batas normal.';
}
return "Hasil pemeriksaan fisik, pemeriksaan penunjang dan laboratorium dalam batas normal, kecuali:\n"
    . implode("\n", array_map(fn ($b) => '- '.$b['organ'].': '.$b['temuan'], $lainnya));
```

> Prefill **hanya teks** — Similar `SbarController`. Nilai `interpretasi` dari EMR yang sudah tersimpan selalu menang; `old()` menang atas keduanya.

### 19.4 Validasi agregat

- `kesimpulan_kategori` **wajib**; bila ada ≥ 3 organ dengan temuan tidak normal, sistem menampilkan peringatan (bukan blocking): *"Terdapat N temuan tidak normal — pertimbangkan kategori TIDAK SEHAT."*
- Bila **belum ada satu pun** form organ yang terisi saat `store`, server menolak dengan pesan: *"Isi minimal satu pemeriksaan spesialis sebelum menyimpan Kesimpulan MCU."*

### 19.5 Cetak agregat

`cetak_resume_mcu.php` (legacy) dijadikan acuan:

- **Cover**: logo, nama RS, "HASIL MEDICAL CHECKUP", identitas (nama, No MR, umur, jenis kelamin, No. MCU, tanggal, "--resume--"), interpretasi, kesimpulan + kategori, ttd dokter.
- **Lampiran**: tabel temuan per organ, daftar tes penunjang yang dijalankan, rekomendasi.

---

## 20. Implementasi

| # | Path | Keterangan |
|---|---|---|
| 1 | `app/Http/Controllers/EMR/McuSpesialistikController.php` | abstract base untuk 14 form organ |
| 2 | `app/Http/Controllers/EMR/McuSpesialistik/<Form>Controller.php` × 13 | subclass tipis |
| 3 | `app/Http/Controllers/EMR/KesimpulanMcu/KesimpulanMcuController.php` | form 70 + agregasi |
| 4 | `app/Http/Controllers/EMR/TtvMcu/TtvMcuController.php` | form 71 (BMI hitung ulang) |
| 5 | `app/Helpers/McuHelper.php` | `formIdsOrgan()`, `ringkasan()`, `interpretasiOtomatis()`, `organBelumDiperiksa()` |
| 6 | `app/Helpers/McuSpesialistikHelper.php` | `hitungInterpretasiPsikologi()`, label & normal range tiap parameter |
| 7 | `resources/views/moduls/EMR/PartialForm/mcu_spesialistik.blade.php` | partial 14 form |
| 8 | `resources/views/moduls/EMR/PartialForm/mcu_header_pasien.blade.php` | header identitas (read-only dari form 71) |
| 9 | `resources/views/moduls/EMR/PartialForm/mcu_ringkasan_organ.blade.php` | panel read-only form 70 |
| 10 | `resources/views/moduls/EMR/<Folder>/index.blade.php` × 15 | 15 view |
| 11 | `resources/views/moduls/EMR/KesimpulanMcu/print.blade.php` | resume MCU 2 halaman |
| 12 | `resources/views/moduls/EMR/<Folder>/print.blade.php` × 14 | cetak per organ |
| 13 | `app/Helpers/SelectOption.php` | tambah key `mcu_status_gizi`, `mcu_rhythm_ekg`, `mcu_kb_metode` |
| 14 | `database/seeders/EmrMasterSeeder.php` | `$menus`, `$subMenus`, `$forms`, `$objeks`, `$mapping`, `$akses` |
| 15 | `AGENTS.md` | entri form 70–84 |

**Migration / master baru:** **tidak ada migration**. Tabel yang dibutuhkan sudah ada: `registrasi` (`jenis_rawat = MCU`), `suket_mcu`, `order_laboratorium*`, `order_radiologi*` (dari `KONSEP_MCU.md`).

Eksekusi:

```bash
docker compose exec app php artisan db:seed --class=EmrMasterSeeder
# tambah EmrHelper::backfillObjekId(70..84) di akhir run()
```

---

## 21. Catatan & Risiko

| # | Risiko | Mitigasi |
|---|---|---|
| 1 | `id_dash_menu` `'14.273'`..`'14.287'` — `"14.273" == "14.282"` true bila dibandingkan dengan `==`. | Semua query WAJIB `===` atau `where` di SQL. `AksesEhrController` sudah memakai closure `===`. |
| 2 | Sub 2 diberi nama `TTV MCU` agar slug cocok; bila diubah menjadi "Catatan Keperawatan MCU", form 71 menjadi yatim. | Jangan ubah `nama_sub_menu` tanpa menyesuaikan `form.slug` (PANDUAN §2.1). |
| 3 | Field turunan (`bmi`, `kategori_bmi`, `interpretasi` psikologi, `interpretasi` kesimpulan) bisa dimanipulasi dari browser. | Selalu hitung ulang di `filteredData()`; nilai browser dibuang (PANDUAN §2.4). |
| 4 | Field turunan **berbagi objek dengan field manual** (`kategori_bmi` → objek 58, `diagnosa` → objek 40, `rekomendasi` → objek 77). Laporan berbasis objek menggabungkan keduanya. | Dokumentasikan di tabel objek; bila laporan perlu memisahkan, buat objek khusus. |
| 5 | Prefix variabel `visus_od_*` (form 72) dan `mata_kanan_*` (form 77) berbeda nama meski maksudnya sama → mudah tertukar. | Konfirmasi penamaan dengan tim optometris/spesialis mata sebelum implementasi. |
| 6 | `tod`/`tos` pada blok visus mata kanan memiliki arti yang belum terkonfirmasi (legacy tidak jelas). | Dikonfirmasi ke tim optometris; sementara itu field bersifat opsional (`nullable`). |
| 7 | 14 form memakai pola identik → **duplikasi blade 14×** kalau partial gagal dipakai. | Wajib pakai `mcu_spesialistik.blade.php`; review checklist memverifikasi tidak ada copy-paste markup per organ. |
| 8 | `$emr_data` wrapper hanya `ArrayAccess` — `{{ $emr_data ?? '' }}` selalu kosong sehingga semua field menampilkan nilai yang sama. | Partial WAJIB pakai `$emr_data['x'] ?? ''` (AGENTS.md — Risiko Jatuh). |
| 9 | Hidden `input` (untuk show/hide "Tidak Ada") tidak terkirim bila `disabled` → validasi gagal diam-diam. | Gunakan `name` yang tetap ada; hide/show lewat `style.display` **dan** lepas `name` (PANDUAN §2.1 & AGENTS.md — Tindakan Medis). |
| 10 | Akses perawat form 71 default full CRUD; bila kebijakan RS berbeda, ubah di menu *Akses EHR*. | Default sudah didokumentasikan §5; mudah diubah tanpa kode. |
| 11 | Agregasi form 70 bergantung pada `registrasi_detail_id` yang sama — MCU dengan banyak episode dapat salah ringkasan. | `McuHelper::ringkasan()` selalu memakai `registrasi_detail_id` **yang sedang dibuka**, bukan seluruh `registrasi_id`. |
| 12 | ~250 baris `objek_form_control` baru; total objek mendekati 900. | Filter per menu di Manajemen EMR → Form sudah tersedia; tambahkan pencarian bila perlu. |