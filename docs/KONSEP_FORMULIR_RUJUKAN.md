# Konsep Form EMR: Formulir & Rujukan

Rancangan & catatan implementasi 4 form EMR domain **Formulir dan Rujukan**
(form 58, 126, 127, 128): rekam medis rujukan/pengantar rawat, surat keterangan
sehat, pesan penunjang (bedah / cathlab / endoscopy / ESWL), serta SPRI dan
program rujukan balik. Dokumen ini adalah konsep — belum ada kode yang ditulis.

**Status:** Rancangan / Konsep — belum diimplementasikan
**Tanggal:** 2026-10-10
**Sumber legacy:** `/simrs_tenriawaru/FE/lib/modul/`
**Alokasi:** objek_id **730–761**, menu **5 "Formulir"**, sub-menu **233–236**

---

## 1. Ringkasan

| form_id | nama_form | slug | id_dash_menu | ri | rj | igd | mcu |
|---|---|---|---|---|---|---|---|
| 58 | Rekam Medis Rujukan | `rekam_medis_rujukan` | `5.233` | 1 | 1 | 1 | 0 |
| 126 | Surat Keterangan Sehat | `surat_keterangan_sehat` | `5.234` | 0 | 1 | 0 | 0 |
| 127 | Pesan Bedah / Cathlab / Endoscopy / ESWL | `pesan_penunjang` | `5.235` | 1 | 1 | 1 | 0 |
| 128 | SPRI & Program Rujukan Balik | `spri_rujukan_balik` | `5.236` | 1 | 1 | 0 | 0 |

Menu **5 "Formulir"** sudah ada (sub 7 = Konsultasi). Sub-menu baru memakai
`dashboard_menu_sub_id` **233, 234, 235, 236** — `dashboard_menu_sub_id` bersifat
**GLOBAL**, dan band `KONSEP_FORMULIR_RUJUKAN.md` adalah **233–252**
(`ALOKASI_ID_GLOBAL.md` §3); file ini memakai **233–236**. Band yang lebih rendah
dipakai dokumen lain: `KONSEP_OBSTETRI_NEONATAL.md` 253–272,
`KONSEP_FORM_MCU.md` 273–292, `KONSEP_BEDAH_ANESTESI.md` 293–312,
`KONSEP_PSIKOSOSIAL_KEROHANIAN.md` 313–332, `KONSEP_SURVEILANS_INFEKSI.md`
333–352, `KONSEP_ONKOLOGI_TRANSFUSI.md` 353–372,
`KONSEP_KATALOG_FORM_BLANKO.md` 373–392.

> **Rantai `slug` (PANDUAN §2.1).**
>
> | sub | `nama_sub_menu` | `Str::slug(...)` | `form.slug` |
> |---|---|---|---|
> | 233 | `Rekam Medis Rujukan` | `rekam_medis_rujukan` | `rekam_medis_rujukan` |
> | 234 | `Surat Keterangan Sehat` | `surat_keterangan_sehat` | `surat_keterangan_sehat` |
> | 235 | `Pesan Penunjang` | `pesan_penunjang` | `pesan_penunjang` |
> | 236 | `SPRI Rujukan Balik` | `spri_rujukan_balik` | `spri_rujukan_balik` |
>
> Sub 236 **tidak** boleh memakai nama yang memuat `&` maupun kata penghubung —
> `Str::slug()` **membuang** karakter tersebut, bukan menggantinya:
> `Str::slug('SPRI & Program Rujukan Balik','_')` = `spri_program_rujukan_balik`
> dan `Str::slug('SPRI dan Program Rujukan Balik','_')` =
> `spri_dan_program_rujukan_balik`. Keduanya ≠ `form.slug` `spri_rujukan_balik`,
> sehingga form akan **yatim dari dashboard**. Nama yang benar tetap
> `SPRI Rujukan Balik`. `&` hanya boleh muncul di `form.nama_form`
> (`SPRI & Program Rujukan Balik`, sesuai `ALOKASI_ID_GLOBAL.md` §6) karena
> `nama_form` **tidak** menjadi sumber slug.

---

## 2. Sumber Referensi Legacy

| Berkas legacy | Dipakai untuk |
|---|---|
| `pengantar_rawat.php` | Rekam medis rujukan: diagnosa, tingkat urgensi, indikasi rawat inap, rencana pemeriksaan, terapi, dokter rawat, kebutuhan pelayanan/kamar, tanggal rencana rawat inap |
| `spri_casemix.php` | Block "FORM PENGANTAR RAWAT" milik SPRI: daftar lengkap opsi tingkat urgensi, indikasi, kebutuhan pelayanan, kebutuhan kamar |
| `program_rujuk_balik.php` | Program rujukan balik: anamnesa/pemeriksaan fisik dari SOAP, alasan default, obat yang tersisa, tanggal kontrol |
| `surat_keterangan_sehat.php` | Surat keterangan sehat: nomor surat berformat `urut / KDPM / SKS / BULAN / TAHUN`, hasil pemeriksaan fisik, pemeriksaan mata, catatan |
| `pesan_bedah.php` | Pesan bedah: asal ruangan, unit pelaksana, diagnosa pre operasi, jenis tindakan, jenis operasi, SC, emergency, tanggal/jam rencana, kelas nasabah, alat khusus, dokter operator |
| `pesan_cathlab.php` | Pesan cathlab: sama seperti bedah + jenis tindakan cathlab |
| `pesan_endoscopy.php` | Pesan endoscopy: + jenis anestesi |
| `pesan_eswl.php` | Pesan ESWL: + jenis anestesi |
| `integratednote_konformasi.php` | Referensi pola "konfirmasi" order olehDPJP — dipakai untuk prefill, bukan field |

Struktur yang benar-benar diambil dari legacy:

- **Rekam medis rujukan** (`pengantar_rawat.php`): `tingkat_urgensi` =
  *Rawat Tindakan Elektif* / *Rawat IGD / Tindakan Cito IGD*;
  `indikasi_rawat_inap` = *Pro Endoscopy, Pro Bronchoscopy, Pro CAG, Pro PCI,
  Pro Kemoterapi, Pro MOW, Pro Operasi Katarak, Pro Injeksi, Pro Transfusi,
  Sesak Nafas, Nyeri Dada, Nyeri Abdomen, Kedaruratan Psikiatri, Repair CDL,
  Lainnya*; `kebutuhan_pelayanan` = *Preventif / Paliatif / Kuratif /
  Rehabilitatif*; `kebutuhan_kamar` = *HCU, ICCU, Kebidanan, VK, Perina, Bayi,
  Ruang Biasa, Ruang Khusus B20, Ruang Isolasi*; plus `screening_covid`,
  `kriteria_covid`, `ews`, `oksigen`, `ventilator`, `monitor`, `syringe_pump`.
- **Surat keterangan sehat**: variabel `no_surat`, `usia_pasien`, `alamat`,
  `pemeriksaan_fisik` = *SEHAT / SEHAT DENGAN CATATAN MEDIS / TIDAK SEHAT*,
  `keterangan`, `tb`, `bb`, `sistolik`, `diastolik`, `mata` = *Ada / Tidak Ada*.
  Nomor surat dibentuk `{urut} / KDPM / SKS / {bulan romawi} / {tahun}`.
- **Pesan penunjang**: field yang sama pada keempat berkas — asal ruangan entry, unit
  pelaksana tindakan, diagnosa pre tindakan, jenis tindakan, jenis operasi,
  emergency, tanggal & jam rencana, jam keputusan SC, rencana masuk ruangan,
  kelas nasabah, no. HP + no. HP keluarga, alat khusus (C-Arm dengan kontras,
  C-Arm non kontras, USG), SC, SC Emergency, IC, pre-tindakan, dokter operator;
  endoscopy & ESWL menambah `jenis_anestesi`.
- **Rujukan balik** (`program_rujuk_balik.php`): alasan default *"Pasien telah
  stabil, mohon dapat dilanjutkan pengobatan di Faskes Tingkat I, Kontrol
  Kembali ke RS Setelah 3 Bulan"*, anamnesa (dari `subjective`), pemeriksaan
  fisik (dari `objective`), diagnosa (dari `diagnosisDokter()`), persiapan
  tindakan, rencana pemeriksaan penunjang, terapi perawatan, obat yang tersisa.

---

## 3. Rekam Medis Rujukan (form 58)

### Rasional / tujuan klinis

Dokumen rujukan keluar rumah sakit. Mengandung diagnosa, urgensi, indikasi,
rencana penunjang, terapi, kebutuhan tingkat pelayanan/kamar, serta data pendamping
pasien (screening COVID, EWS,/cgi noodles ventilator, syringe pump) yang
menjadi pertimbangan penerima rujukan. Legacy juga menarik diagnosa, obat
terakhir, hasil lab, dan hasil radiasi langsung dari master — bagian itu
dijadikan **prefill read-only**, bukan field yang disimpan.

### Struktur Field

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `tanggal_pengantar` | 730 | Tanggal Pengantar & Rencana Rawat | date | ya | Tanggal surat dibuat |
| `tgl_rencana_ranap` | 730 | Tanggal Pengantar & Rencana Rawat | date | ya* | Wajib bila indikasi rawat inap |
| `tingkat_urgensi` | 731 | Tingkat Urgensi Rujukan | select | ya | `Rawat Tindakan Elektif` / `Rawat IGD / Tindakan Cito IGD` |
| `indikasi_rawat_inap` | 732 | Indikasi Rawat Inap | select | ya | `Pro Endoscopy` … `Lainnya` (15 opsi legacy) |
| `rencana_pemeriksaan_penunjang` | 733 | Rencana Pemeriksaan Penunjang | textarea | ya | Legacy juga prefill dari order lab/rad aktif |
| `terapi_keperawatan` | 734 | Terapi Perawatan | textarea | ya | `Farmakologis` / `Non Farmakologis` + isi |
| `dokter_rawat` | 735 | Dokter Rawat Pengirim | select (x-select_dokter) | ya | `pegawai` profesi 1 |
| `kebutuhan_pelayanan` | 736 | Kebutuhan Pelayanan | select | ya | `Preventif`/`Paliatif`/`Kuratif`/`Rehabilitatif` |
| `kebutuhan_kamar` | 737 | Kebutuhan Kamar | select | ya | `HCU` … `Ruang Isolasi` |
| `screening_covid` | 738 | Data Pendamping Pasien | select | ya | Legacy:Ya/Tidak |
| `kriteria_covid` | 738 | Data Pendamping Pasien | textarea | ya* | Wajib bila `screening_covid = Ya` |
| `ews` | 738 | Data Pendamping Pasien | select | ya | `0`,`1`,`2`,`3`,`4-6`,`>=7` |
| `oksigen` | 738 | Data Pendamping Pasien | select | ya | `Ya`/`Tidak` |
| `ventilator` | 738 | Data Pendamping Pasien | select | ya | `Ya`/`Tidak` |
| `monitor` | 738 | Data Pendamping Pasien | select | ya | `Ya`/`Tidak` |
| `syringe_pump` | 738 | Data Pendamping Pasien | select | ya | `Ya`/`Tidak` |
| `diagnosis` | 40 (reuse) | Diagnosa Medis | textarea | ya | Di-prefill dari `diagnosisDokter($registrasiId)` bila ada |
| `handphone` | 60 (reuse) | Nomor Handphone | text (maks 13) | ya | Di-prefill dari `pasien.no_hp` |
| `alasan` | 77 (reuse) | Keterangan | textarea | tidak | Alasan/ket.opsional |

`ya*` = wajib kondisional.

### Dashboard & Form Master

```php
['dashboard_menu_sub_id' => 233, 'dashboard_menu_id' => 5, 'nama_sub_menu' => 'Rekam Medis Rujukan'],
['dashboard_menu_sub_id' => 234, 'dashboard_menu_id' => 5, 'nama_sub_menu' => 'Surat Keterangan Sehat'],
['dashboard_menu_sub_id' => 235, 'dashboard_menu_id' => 5, 'nama_sub_menu' => 'Pesan Penunjang'],
['dashboard_menu_sub_id' => 236, 'dashboard_menu_id' => 5, 'nama_sub_menu' => 'SPRI Rujukan Balik'],

['form_id' => 58,  'nama_form' => 'Rekam Medis Rujukan', 'slug' => 'rekam_medis_rujukan', 'id_dash_menu' => '5.233', 'ri' => 1, 'rj' => 1, 'igd' => 1, 'mcu' => 0],
['form_id' => 126, 'nama_form' => 'Surat Keterangan Sehat', 'slug' => 'surat_keterangan_sehat', 'id_dash_menu' => '5.234', 'ri' => 0, 'rj' => 1, 'igd' => 0, 'mcu' => 0],
['form_id' => 127, 'nama_form' => 'Pesan Bedah / Cathlab / Endoscopy / ESWL', 'slug' => 'pesan_penunjang', 'id_dash_menu' => '5.235', 'ri' => 1, 'rj' => 1, 'igd' => 1, 'mcu' => 0],
['form_id' => 128, 'nama_form' => 'SPRI & Program Rujukan Balik', 'slug' => 'spri_rujukan_balik', 'id_dash_menu' => '5.236', 'ri' => 1, 'rj' => 1, 'igd' => 0, 'mcu' => 0],

// $objeks (730-738)
730 => 'Tanggal Pengantar & Rencana Rawat',
731 => 'Tingkat Urgensi Rujukan',
732 => 'Indikasi Rawat Inap',
733 => 'Rencana Pemeriksaan Penunjang',
734 => 'Terapi Perawatan',
735 => 'Dokter Rawat Pengirim',
736 => 'Kebutuhan Pelayanan',
737 => 'Kebutuhan Kamar',
738 => 'Data Pendamping Pasien',

// $objeks (739-741)
739 => 'Nomor Surat Keterangan Sehat',
740 => 'Hasil Pemeriksaan Fisik',
741 => 'Pemeriksaan Mata',
```

### Mapping

```php
58 => [
    'tanggal_pengantar'     => 730,
    'tgl_rencana_ranap'      => 730,
    'tingkat_urgensi'        => 731,
    'indikasi_rawat_inap'    => 732,
    'rencana_pemeriksaan_penunjang' => 733,
    'terapi_keperawatan'     => 734,
    'dokter_rawat'           => 735,
    'kebutuhan_pelayanan'   => 736,
    'kebutuhan_kamar'        => 737,
    'screening_covid'        => 738,
    'kriteria_covid'         => 738,
    'ews'                    => 738,
    'oksigen'                => 738,
    'ventilator'             => 738,
    'monitor'                => 738,
    'syringe_pump'           => 738,
    'diagnosis'              => 40,  // reuse objek Diagnosa Medis
    'handphone'              => 60,  // reuse objek Nomor Handphone
    'alasan'                 => 77,  // reuse objek Keterangan
],
```

**Prefill read-only (disimpan ke `filteredData()` dari master, bukan dari browser):**

```php
// diagnoses dari order/DPJP
$diagnosa = \App\Helpers\DiagnosisHelper::terakhir($registrasiId) ?? '';
$data['diagnosis'] = $diagnosa;

// nama dokter TIDAK disimpan — selalu di-resolve dari pegawai saat tampil
```

### Akses EHR

```php
['profesi_id' => 1, 'form_id' => 58, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 2, 'form_id' => 58, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
```

### Validasi

```php
'tanggal_pengantar' => 'required|date',
'tingkat_urgensi'   => 'required|in:Rawat Tindakan Elektif,Rawat IGD / Tindakan Cito IGD',
'indikasi_rawat_inap'=> 'required|in:Pro Endoscopy,Pro Bronchoscopy,Pro CAG,Pro PCI,Pro Kemoterapi,Pro MOW,Pro Operasi Katarak,Pro Injeksi,Pro Transfusi,Sesak Nafas,Nyeri Dada,Nyeri Abdomen,Kedaruratan Psikiatri,Repair CDL,Lainnya',
'rencana_pemeriksaan_penunjang' => 'required|string|max:2000',
'terapi_keperawatan' => 'required|string|max:2000',
'dokter_rawat'      => 'required|integer|exists:pegawai,pegawai_id',
'kebutuhan_pelayanan'=> 'required|in:Preventif,Paliatif,Kuratif,Rehabilitatif',
'kebutuhan_kamar'   => 'required|in:HCU,ICCU,Kebidanan,VK,Perina,Bayi,Ruang Biasa,Ruang Khusus B20,Ruang Isolasi',
'screening_covid'   => 'required|in:Ya,Tidak',
'kriteria_covid'    => 'required_if:screening_covid,Ya|nullable|string|max:1000',
'ews'               => 'required|in:0,1,2,3,4-6,>=7',
'oksigen'           => 'required|in:Ya,Tidak',
'ventilator'        => 'required|in:Ya,Tidak',
'monitor'           => 'required|in:Ya,Tidak',
'syringe_pump'      => 'required|in:Ya,Tidak',
'diagnosis'         => 'required|string|max:2000',
'handphone'         => 'required|string|max:13',
```

Opsi panjang (indikasi rawat inap, kebutuhan pelayanan/kamar) **wajib** masuk ke
`App\Helpers\SelectOption` sebagai key baru — jangan `<option>` hardcode di blade.

### Cetak

`print.blade.php` — kop resmi, blok data pasien, blok " forecasted: rujukan" :
Tanggal, Diagnosa, Tingkat Urgensi, Indikasi Rawat, Rencana Pemeriksaan
Penunjang, Terapi, Dokter Rawat, Kebutuhan Pelayanan, Kebutuhan Kamar,
Rencana Tanggal Rawat Inap. Kolom tanda tangan dokter pengirim.

---

## 4. Surat Keterangan Sehat (form 126)

### Rasional / tujuan klinis

Surat keterangan sehat untuk keperluan pasien (izin kerja, sekolah, beasiswa,
pre-test, atau keperluan administrasi lainnya). Dalam legacy hasilnya juga
dipakai sebagai ringkasan MCU — header memakai `head.png` folder MCU. Nomor
surat berformat resmi `{urut} / KDPM / SKS / {BULAN} / {TAHUN}` dan dipakai
sebagai nomor dokumen saat cetak.

### Struktur Field

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `no_surat` | 739 | Nomor Surat Keterangan Sehat | text (readonly) | ya | Dibentuk server: `{urut} / KDPM / SKS / {bulan} / {tahun}` |
| `pemeriksaan_fisik` | 740 | Hasil Pemeriksaan Fisik | select | ya | `SEHAT` / `SEHAT DENGAN CATATAN MEDIS` / `TIDAK SEHAT` |
| `mata` | 741 | Pemeriksaan Mata | select | ya | `Ada` / `Tidak Ada` (legacy: apakah ada catatan pada mata) |
| `keterangan` | 77 (reuse) | Keterangan | textarea | ya* | Wajib bila `pemeriksaan_fisik != SEHAT` |
| `bb` | 8 (reuse) | Berat Badan | number | ya | |
| `tb` | 9 (reuse) | Tinggi Badan | number | ya | |
| `sistolik` | 6 (reuse) | Tekanan Darah Sistolik | number | ya | |
| `diastolik` | 7 (reuse) | Tekanan Darah Diastolik | number | ya | |
| — | — | Nama / No. MR / Jenis Kelamin / Usia / Alamat | **read-only** | — | Dari master `pasien`; **tidak** disimpan |

### Dashboard & Form Master

Objek 739–741 (form 126) didefinisikan di blok seeder §3.

### Mapping

```php
126 => [
    'no_surat'          => 739,
    'pemeriksaan_fisik' => 740,
    'mata'              => 741,
    'keterangan'        => 77,   // reuse objek Keterangan
    'bb'                => 8,    // reuse Berat Badan
    'tb'                => 9,    // reuse Tinggi Badan
    'sistolik'          => 6,    // reuse Tekanan Darah Sistolik
    'diastolik'         => 7,    // reuse Tekanan Darah Diastolik
],
```

**Nomor surat dihitung server-side saat `store()` (bukan dari browser):**

```php
private function nomorSurat(): string
{
    $urut = (int) DB::table('emr_detail')
        ->where('objek_id', 739)
        ->where(function ($q) { $q->whereNull('status_batal')->orWhere('status_batal', 0); })
        ->count() + 1;

    $bulan = now()->translatedFormat('M');           // contoh: "Okt"
    return $urut.' / KDPM / SKS / '.$bulan.' / '.now()->format('Y');
}
```

> Legacy memakai `urut` dari `COUNT` + `MAX`; pola `count()+1` sudah cukup untuk
> satu rumah sakit. Bila perlu concurrency-safe, pakai
> `DB::transaction()` + `lockForUpdate()` pada tabel penghitung.

### Akses EHR

```php
['profesi_id' => 1, 'form_id' => 126, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 7, 'form_id' => 126, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
```

Profesi **7 = Perekam Medis** — ini surat yang paling sering dibuatnya.

### Validasi

```php
'pemeriksaan_fisik' => 'required|in:SEHAT,SEHAT DENGAN CATATAN MEDIS,TIDAK SEHAT',
'mata'              => 'required|in:Ada,Tidak Ada',
'keterangan'        => 'required_if:pemeriksaan_fisik,"SEHAT DENGAN CATATAN MEDIS,TIDAK SEHAT"|nullable|string|max:1000',
'bb'                => 'required|numeric|min:1|max:400',
'tb'                => 'required|numeric|min:30|max:250',
'sistolik'          => 'required|integer|min:50|max:300',
'diastolik'         => 'required|integer|min:20|max:200',
```

- `no_surat` **tidak** divalidasi dari input — diisi server dan diabaikan bila
  browser mengirimnya.
- Auto-prefill `bb`, `tb`, `sistolik`, `diastolik` dari tanda vital terakhir pada
  `registrasi_detail` yang sama (legacy melakukan hal yang sama lewat query
  `json_agg`), tetapi tetap **boleh diubah** petugas sebelum disimpan.

### Cetak

`print.blade.php` — kop `head.png` MCU, nomor surat, blok
"Nama / No. RM / Jenis Kelamin / Usia / Alamat", tabel pemeriksaan fisik
(BB, TB, TD, mata), kalimat kesimpulan, kota + tanggal Indonesia,
tanda tangan dokter.

---

## 5. Pesan Bedah / Cathlab / Endoscopy / ESWL (form 127)

### Rasional / tujuan klinis

Permintaan penjadwalan tindakan penunjang invasif. Legacy memakai **empat
formulir terpisah** dengan ~95% field identik; desain di sini menyatukannya
menjadi satu form dengan selector `jenis_pesan`. Field yang **hanya** relevan
untuk endoscopy/ESWL (`jenis_anestesi`) ditampilkan kondisional.

### Struktur Field

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `jenis_pesan` | 742 | Jenis Pesan Penunjang | select | ya | `BEDAH` / `CATHLAB` / `ENDOSCOPY` / `ESWL` |
| `bagian_asal` | 743 | Asal Ruangan Entry & Unit Pelaksanaan | select (x-select_ruang_perawatan) | ya | |
| `unit_pelaksanaan_id` | 743 | Asal Ruangan Entry & Unit Pelaksanaan | select (dropdown `bagian`) | ya | Unit penunjang pelaksana tindakan |
| `diagnosa_medis` | 40 (reuse) | Diagnosa Medis | textarea | ya | Diagnosa pre tindakan |
| `jenis_tindakan` | 744 | Jenis Tindakan Penunjang | select | ya | `Tindakan` dari master (kategori sesuai jenis_pesan) |
| `tgl_rencana_tindakan` | 745 | Tanggal & Jam Rencana Tindakan | date | ya | |
| `jam_rencana_tindakan` | 745 | Tanggal & Jam Rencana Tindakan | time | ya | |
| `jam_keputusan_sc` | 746 | Jam Keputusan SC / EMERGENCY | time | ya* | Wajib bila `sc = Ya` atau `sc_emergency = Ya` |
| `jenis_rawat_rencana` | 747 | Rencana Masuk Ruangan | select | ya | `RI` / `RJ` |
| `tgl_rencana_ranap` | 747 | Rencana Masuk Ruangan | date | ya* | Wajib bila `jenis_rawat_rencana = RI` |
| `handphone` | 60 (reuse) | Nomor Handphone | text | ya | |
| `handphone_keluarga` | 748 | No. Handphone & Kontak Keluarga | text | ya | `max:13` |
| `pasang_alat` | 749 | Alat Khusus yang Diperlukan | select | ya | `Tidak Ada` / `Ada` |
| `alat_khusus` | 749 | Alat Khusus yang Diperlukan | checkbox | ya* | `C-Arm dengan kontras`, `C-Arm Non Kontras`, `USG`, `Lainnya` |
| `sc` | 750 | Status SC / SC Emergency / IC | select | ya | `Ya`/`Tidak` |
| `sc_emergency` | 750 | Status SC / SC Emergency / IC | select | ya | `Ya`/`Tidak` |
| `ic` | 750 | Status SC / SC Emergency / IC | select | ya | `Ya`/`Tidak` |
| `pra_tindakan` | 751 | Pre Tindakan, Anestesi & Dokter Operator | select | ya | Persiapan pra tindakan |
| `jenis_anestesi` | 751 | Pre Tindakan, Anestesi & Dokter Operator | select | ya* | **Khusus ENDOSCOPY/ESWL**: `Umum`, `Spinal`, `Epidural`, `Lokal`, `Sedasi` |
| `dokter_operator_id` | 751 | Pre Tindakan, Anestesi & Dokter Operator | select (x-select_dokter) | ya | Dokter operator tindakan |
| `dpjp_utama` | 751 | Pre Tindakan, Anestesi & Dokter Operator | text | ya | |
| `kelas_id` | — | Kelas Nasabah | select | ya | Di-resolve dari `pasien_nasabah.hak_kelas_id`; **tidak** disimpan |
| `bb` | 8 (reuse) | Berat Badan | number | tidak | |
| `tb` | 9 (reuse) | Tinggi Badan | number | tidak | |

`ya*` = wajib kondisional.

### Dashboard & Form Master

```php
// $objeks (742-751)
742 => 'Jenis Pesan Penunjang',
743 => 'Asal Ruangan Entry & Unit Pelaksanaan',
744 => 'Jenis Tindakan Penunjang',
745 => 'Tanggal & Jam Rencana Tindakan',
746 => 'Jam Keputusan SC / EMERGENCY',
747 => 'Rencana Masuk Ruangan',
748 => 'No. Handphone & Kontak Keluarga',
749 => 'Alat Khusus yang Diperlukan',
750 => 'Status SC / SC Emergency / IC',
751 => 'Pre Tindakan, Anestesi & Dokter Operator',
```

### Mapping

```php
127 => [
    'jenis_pesan'           => 742,
    'bagian_asal'           => 743,
    'unit_pelaksanaan_id'   => 743,
    'diagnosa_medis'        => 40,   // reuse objek Diagnosa Medis
    'jenis_tindakan'        => 744,
    'tgl_rencana_tindakan'  => 745,
    'jam_rencana_tindakan'  => 745,
    'jam_keputusan_sc'      => 746,
    'jenis_rawat_rencana'   => 747,
    'tgl_rencana_ranap'     => 747,
    'handphone'             => 60,   // reuse objek Nomor Handphone
    'handphone_keluarga'    => 748,
    'pasang_alat'           => 749,
    'alat_khusus'           => 749,
    'sc'                    => 750,
    'sc_emergency'          => 750,
    'ic'                    => 750,
    'pra_tindakan'          => 751,
    'jenis_anestesi'        => 751,
    'dokter_operator_id'    => 751,
    'dpjp_utama'            => 751,
    'bb'                    => 8,    // reuse Berat Badan
    'tb'                    => 9,    // reuse Tinggi Badan
],
```

**Penyaringan `jenis_tindakan` berdasarkan `jenis_pesan`** (dilakukan di server
dan di-JS dropdown):

```php
// Bedah   -> Tindakan dengan kategori termasuk "Bedah"
// Cathlab -> Tindakan dengan kategori termasuk "Cathlab"
// Endoscopy/ESWL -> kategori masing-masing
$tindakanIds = Tindakan::aktif()
    ->where(function ($q) use ($jenisPesan) {
        $q->where('kategori_tindakan_id', $kategoriMap[$jenisPesan]);
    })
    ->pluck('tindakan_id');
```

Nilai browser **tidak** dipercaya: filter ulang di `filteredData()`.

### Akses EHR

```php
['profesi_id' => 1, 'form_id' => 127, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 2, 'form_id' => 127, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
```

### Validasi

```php
'jenis_pesan'       => 'required|in:BEDAH,CATHLAB,ENDOSCOPY,ESWL',
'bagian_asal'       => 'required|integer|exists:bagian,bagian_id',
'unit_pelaksanaan_id'=> 'required|integer|exists:bagian,bagian_id',
'diagnosa_medis'    => 'required|string|max:2000',
'jenis_tindakan'    => 'required|integer|exists:tindakan,tindakan_id',
'tgl_rencana_tindakan' => 'required|date|after_or_equal:today',
'jam_rencana_tindakan' => 'required|date_format:H:i',
'sc'                => 'required|in:Ya,Tidak',
'sc_emergency'      => 'required|in:Ya,Tidak',
'ic'                => 'required|in:Ya,Tidak',
'jam_keputusan_sc'  => 'required_if:sc,Ya|nullable|date_format:H:i',
'jenis_rawat_rencana' => 'required|in:RI,RJ',
'tgl_rencana_ranap' => 'required_if:jenis_rawat_rencana,RI|nullable|date',
'handphone'         => 'required|string|max:13',
'handphone_keluarga'=> 'required|string|max:13',
'pasang_alat'       => 'required|in:Tidak Ada,Ada',
'alat_khusus'       => 'required_if:pasang_alat,Ada|nullable|array',
'pra_tindakan'      => 'required|in:'.implode(',', array_column(SelectOption::get('pra_tindakan_penunjang'), 'value')),
'dokter_operator_id'=> 'required|integer|exists:pegawai,pegawai_id',
'dpjp_utama'        => 'required|string|max:200',
'jenis_anestesi'    => 'required_if:jenis_pesan,ENDOSCOPY,ESWL|nullable|in:Umum,Spinal,Epidural,Lokal,Sedasi',
```

Tambahan di `filteredData()`:

- Bila `jenis_pesan` bukan `ENDOSCOPY`/`ESWL`, `jenis_anestesi` dibuang.
- Bila `pasang_alat = Tidak Ada`, `alat_khusus` dibuang.
- `jenis_tindakan` divalidasi ulang terhadap filter kategori `jenis_pesan`.

### Cetak

`print.blade.php` — judul otomatis mengikuti `jenis_pesan`
("PESAN BEDAH" / "PESAN CATHLAB" / "PESAN ENDOSCOPY" / "PESAN ESWL"),
blok pemohon, blok diagnosis & jenis tindakan, jadwal, kebutuhan kamar,
kontak, alat khusus, status SC/IC, dan kolom tanda tangan DPJP + dokter operator.

---

## 6. SPRI & Program Rujukan Balik (form 128)

### Rasional / tujuan klinis

Dua dokumen dalam satu form karena keduanya lahir dari episode rawat inap yang
sama dan memiliki isi identik:

1. **SPRI (Surat Pernyataan Rawat Inap)** — untuk pasien BPJS yang akan
  .dirujuk ke fasilitas lain atau dirawat inap kembali.
2. **Program Rujukan Balik (PRB)** — untuk pasien kondisi stabil yang
   dilanjutkan pengobatan di Faskes Tingkat I dengan kontrol ke rumah sakit.

Field anamnesis/pemeriksaan fisik/diagnosa **di-prefill dari SOAP terakhir**
seperti legacy, tetapi dapat diedit sebelum disimpan.

### Struktur Field

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `jenis_formulir` | 752 | Jenis Formulir | select | ya | `SPRI` / `PROGRAM RUJUKAN BALIK` |
| `no_sep` | 753 | Nomor SEP & No. Rujukan | text | ya* | Wajib bila `jenis_formulir = SPRI` |
| `no_rujukan` | 753 | Nomor SEP & No. Rujukan | text | ya* | Dari `rujukan_sep` bila tersedia |
| `alasan` | 754 | Alasan Rujukan Balik | textarea | ya | Default: "Pasien telah stabil, mohon dapat dilanjutkan pengobatan di Faskes Tingkat I, Kontrol Kembali ke RS Setelah 3 Bulan" |
| `persiapan_tindakan` | 755 | Persiapan Tindakan | textarea | ya | |
| `rencana_pemeriksaan_penunjang` | 756 | Rencana Pemeriksaan Penunjang | textarea | ya | |
| `terapi_obat` | 757 | Terapi & Obat yang Masih Diberikan | textarea | ya | Di-prefill dari resep terakhir yang sudah selesai |
| `anjuran` | 758 | Anjuran & Instruksi | textarea | ya | |
| `tanggal_kontrol` | 759 | Tanggal Kontrol Rujukan Balik | date | ya* | Wajib bila `jenis_formulir = PROGRAM RUJUKAN BALIK` |
| `dokter_pengarah_id` | 760 | Dokter Pengarah | select (x-select_dokter) | ya | |
| `faskes_tujuan` | 761 | Faskes Tujuan Rujukan Balik | text | ya | Namafkas tujuan |
| `diagnosis` | 40 (reuse) | Diagnosa Medis | textarea | ya | Prefill dari SOAP/DPJP terakhir |
| `anamnesis` | 1 (reuse) | Subjective | textarea | ya | Prefill `subjective` SOAP terakhir |
| `pemeriksaan_fisik` | 2 (reuse) | Objective | textarea | ya | Prefill `objective` SOAP terakhir |

### Dashboard & Form Master

```php
// $objeks (752-761)
752 => 'Jenis Formulir',
753 => 'Nomor SEP & No. Rujukan',
754 => 'Alasan Rujukan Balik',
755 => 'Persiapan Tindakan',
756 => 'Rencana Pemeriksaan Penunjang',
757 => 'Terapi & Obat yang Masih Diberikan',
758 => 'Anjuran & Instruksi',
759 => 'Tanggal Kontrol Rujukan Balik',
760 => 'Dokter Pengarah',
761 => 'Faskes Tujuan Rujukan Balik',
```

### Mapping

```php
128 => [
    'jenis_formulir'    => 752,
    'no_sep'            => 753,
    'no_rujukan'        => 753,
    'alasan'            => 754,
    'persiapan_tindakan'=> 755,
    'rencana_pemeriksaan_penunjang' => 756,
    'terapi_obat'       => 757,
    'anjuran'           => 758,
    'tanggal_kontrol'   => 759,
    'dokter_pengarah_id'=> 760,
    'faskes_tujuan'     => 761,
    'diagnosis'         => 40,   // reuse objek Diagnosa Medis
    'anamnesis'         => 1,    // reuse objek Subjective (S)
    'pemeriksaan_fisik' => 2,    // reuse objek Objective (O)
],
```

**Prefill dari EMR terakhir (pola `SbarController::konteksKlinis()`):**

```php
$terakhir = EmrHelper::latestEmr(2, $registrasi_detail_id);   // form 2 = SOAP
if ($terakhir) {
    $nilai = EmrHelper::latestValuesByVariabel(2, $registrasi_detail_id, [
        'subjective', 'objective', 'diagnosis',
    ]);
    // Prefill hanya bila form baru (bukan saat edit)
    $data['anamnesis']         = $nilai['subjective'] ?? '';
    $data['pemeriksaan_fisik'] = $nilai['objective']  ?? '';
}
```

Saat **edit**, nilai tersimpan yang dipakai (`$emr_data` > prefill, `old()`
selalu menang) — persis aturan pada form SBAR.

### Akses EHR

```php
['profesi_id' => 1, 'form_id' => 128, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 2, 'form_id' => 128, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
```

### Validasi

```php
'jenis_formulir' => 'required|in:SPRI,PROGRAM RUJUKAN BALIK',
'no_sep'         => 'required_if:jenis_formulir,SPRI|nullable|string|max:50',
'no_rujukan'     => 'nullable|string|max:50',
'alasan'         => 'required|string|max:1000',
'persiapan_tindakan' => 'required|string|max:1000',
'rencana_pemeriksaan_penunjang' => 'required|string|max:1000',
'terapi_obat'    => 'required|string|max:2000',
'anjuran'        => 'required|string|max:2000',
'tanggal_kontrol'=> 'required_if:jenis_formulir,PROGRAM RUJUKAN BALIK|nullable|date|after_or_equal:today',
'dokter_pengarah_id' => 'required|integer|exists:pegawai,pegawai_id',
'faskes_tujuan'  => 'required|string|max:150',
'diagnosis'      => 'required|string|max:2000',
'anamnesis'      => 'required|string|max:4000',
'pemeriksaan_fisik' => 'required|string|max:4000',
```

Tambahan di `filteredData()`:

- Bila `jenis_formulir = SPRI`, `tanggal_kontrol` dibuang.
- Bila `jenis_formulir = PROGRAM RUJUKAN BALIK`, `no_sep`/`no_rujukan` boleh
  tetap ada (pasien BPJS bisa keduanya) — **tidak** dibuang.

### Cetak

`print.blade.php` — dua tata letak berbeda dipilih dari `jenis_formulir`:
- **SPRI**: kop BPJS/SRS, blok identitas & nomor SEP, blok "INFORMASI KLINIS"
  (anamnesis, pemeriksaan fisik, diagnosa, persiapan tindakan, rencana
  penunjang, terapi), blok "SARAN/ANJURAN", signature dokter pengarah.
- **Program Rujukan Balik**: kop, blok identitas, blok "Alasan", blok
  Terapi Obat, blok Anjuran, blok Tanggal Kontul, nama & tanda tangan dokter
  pengarah +imestamps RS.

---

## Implementasi

- [ ] `database/seeders/EmrMasterSeeder.php` — sub 233–236 (menu 5), form
      58/126/127/128, objek 730–761, mapping, `akses_ehr`,
      `EmrHelper::backfillObjekId(58, 126, 127, 128)`
- [ ] `app/Http/Controllers/EMR/RekamMedisRujukan/RekamMedisRujukanController.php`
- [ ] `resources/views/moduls/EMR/RekamMedisRujukan/{index,print}.blade.php`
- [ ] `app/Http/Controllers/EMR/SuratKeteranganSehat/SuratKeteranganSehatController.php`
- [ ] `resources/views/moduls/EMR/SuratKeteranganSehat/{index,print}.blade.php`
- [ ] `app/Http/Controllers/EMR/PesanPenunjang/PesanPenunjangController.php`
- [ ] `resources/views/moduls/EMR/PesanPenunjang/{index,print}.blade.php`
- [ ] `app/Http/Controllers/EMR/SpriRujukanBalik/SpriRujukanBalikController.php`
- [ ] `resources/views/moduls/EMR/SpriRujukanBalik/{index,print}.blade.php`
- [ ] `app/Http/Controllers/EMR/PartialForm/pasien_identitas_rujukan.blade.php`
      (blok read-only nama/MR/JK/usia/alamat/ruangan, dipakai 58/126/127/128)
- [ ] `app/Helpers/SelectOption.php` — key baru: `indikasi_rawat_inap`,
      `kebutuhan_pelayanan`, `kebutuhan_kamar`, `tingkat_urgensi_rujukan`,
      `alat_khusus_penunjang`, `jenis_pesan_penunjang`,
      `jenis_anestesi`, `status_sc`
- [ ] `app/Helpers/DiagnosisHelper.php` (opsional) — prefill diagnosa terakhir
- [ ] `app/Helpers/SuratHelper.php` (opsional) — generator nomor surat SKS
- [ ] `routes/web.php` — route print manual untuk 4 form (`->whereNumber('emr_id')`)
- [ ] `AGENTS.md` — entri form 58, 126, 127, 128

Tidak ada migration baru.

---

## Catatan & Risiko

| Risiko | Mitigasi |
|---|---|
| Sub 236 bernama "SPRI dan Program Rujukan Balik" atau "SPRI & Program Rujukan Balik" → slug `spri_dan_program_rujukan_balik` / `spri_program_rujukan_balik` | Tulis persis `SPRI Rujukan Balik` — `Str::slug()` membuang `&` dan kata penghubung, tidak menggantinya |
| `id_dash_menu` salah (`5.7` vs `5.233`) | Pakai PK aktual sub-menu 233–236 |
| Opsi panjang (indikasi rawat inap, kebutuhan kamar) hardcode di blade | Semua masuk `SelectOption` |
| Field computed (`no_surat`) diambil dari browser | Hitung ulang di server saat `store()`; nilai browser diabaikan (PANDUAN §2.4) |
| `diagnosis`/`anamnesis` diisi user padahal bisa dari master | Snapshot dari master boleh diedit user (bukan computed) — tetap disimpan, bukan di-resolve saat tampil |
| `jenis_tindakan` tidak cocok dengan `jenis_pesan` (kebocoran filter) | Filter ulang **di server** pada `filteredData()`, jangan hanya andalkan dropdown JS |
| Checkbox `alat_khusus` hilang saat tidak dicentang | Gunakan `name="alat_khusus[]"` — sudah berupa array, tidak perlu hidden; validasi `array` menerima array kosong |
| `required_if:jenis_formulir,PROGRAM RUJUKAN BALIK` gagal karena nilai mengandung spasi | Gunakan nilai tanpa spasi atau `Rule::in()` di `withValidator()` |
| Konsistensi zona waktu nomor surat | Nomor surat dihitung dari `now()` PHP, bukan `NOW()` MySQL (zona waktu container db = UTC) |
| `mcu=0` pada form 127 & 128 | Diverifikasi: pesan penunjang tidak dipakai pada MCU; tidak muncul di dashboard pasien MCU |
| Prefill SOAP saat edit menimpa nilai tersimpan | Aturan: `$emr_data` > prefill, `old()` selalu menang (pola `SbarController`) |
| Tampilan `pemeriksaan_fisik` di form 126 bentrok nama dengan field lain | `pemeriksaan_fisik` form 126 = hasil pemeriksaan (string), sedangkan `pemeriksaan_fisik` form 128 = objektif (teks panjang). Keduanya `variabel` berbeda **pada form berbeda**, jadi aman |
| `Str::studly('pesan_penunjang')` = `PesanPenunjang` | Folder controller/view WAJIB `PesanPenunjang` |