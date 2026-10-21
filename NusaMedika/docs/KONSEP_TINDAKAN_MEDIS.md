# Konsep Form EMR: Tindakan Medis

Rancangan & catatan implementasi form **Tindakan Medis** (form EMR 10) di bawah dashboard
**Catatan Medis**. Form ini mencatat permintaan tindakan medis atas persetujuan dokter,
hasil/kondisi pasca tindakan, serta pemakaian obat/BMHP yang diberikan.

## 1. Letak & registrasi form

| Item | Nilai |
|---|---|
| `form.form_id` | `10` |
| `form.nama_form` | `Tindakan Medis` |
| `form.slug` | `tindakan_medis` |
| `form.id_dash_menu` | `1.9` |
| Flag rawat | `ri=1`, `rj=1`, `igd=1`, `mcu=1` |
| Sub-menu | `dashboard_menu_sub_id = 9`, `dashboard_menu_id = 1` (Catatan Medis), **tanpa** extra |
| Controller | `App\Http\Controllers\EMR\TindakanMedis\TindakanMedisController` |
| View | `resources/views/moduls/EMR/TindakanMedis/index.blade.php` |
| Route | generik EMR — `GET /emr/form/tindakan_medis/{registrasi_detail_id}/{emr_id?}` |

Semua baris di atas di-seed di `database/seeders/EmrMasterSeeder.php`. **Gotcha `id_dash_menu`**:
`header_ehr` membangun `id_dash_menu` dari PK aktual baris (`dashboard_menu_id.dashboard_menu_sub_id.dashboard_menu_sub_extra_id`),
jadi harus `1.9` (menu 1 + sub 9, extra NULL di-skip `CONCAT_WS`). Id yang salah membuat form tidak
muncul di dashboard pasien maupun checkbox Akses EHR.

`Str::slug('Tindakan Medis', '_')` **wajib** sama dengan `form.slug` (`tindakan_medis`), karena
sub-menu tanpa extra memakai `nama_sub_menu` sebagai slug URL di dashboard pasien.

## 2. Field & mapping objek

| Variabel | Objek | Wajib | Tipe / sumber |
|---|---|---|---|
| `dokter_persetujuan_id` | 127 (Dokter Penyetuju) | ya | `pegawai_id` — dropdown `x-select_dokter` |
| `tindakan_id` | 75 (Tindakan, reuse) | ya | Master Tindakan (`Tindakan::aktif()`) |
| `jenis_permintaan` | 128 (Jenis Permintaan) | ya | `CITO` / `BIASA` |
| `tanggal_tindakan` | 129 (Tanggal Tindakan) | ya | `date`, **tanpa default** |
| `waktu_tindakan` | 130 (Waktu Tindakan) | ya | `H:i` (24 jam), **tanpa default** |
| `hasil_kondisi` | 131 (Hasil/Kondisi Pasca Tindakan) | tidak | free text |
| `keterangan` | 77 (Keterangan, reuse) | tidak | free text |
| `pemakaian_obat_bmhp` | 132 (Pemakaian Obat/BMHP) | ya | `Ya` / `Tidak` |
| `jenis_barang_1` … `_20` | 133 (Jenis Barang) | ya* | `Obat` / `BMHP` (dari `barang_jenis`) |
| `obat_1` … `obat_20` | 69 (Barang, reuse) | ya* | `barang_id` dari Master Barang |
| `batch_1` … `batch_20` | 134 (Nomor Batch) | ya* | nomor batch dari tabel `stock` |
| `jumlah_1` … `jumlah_20` | 70 (Jumlah, reuse) | ya* | angka > 0 dan ≤ sisa stok batch |

`*` wajib bila `pemakaian_obat_bmhp = Ya`.

### Prinsip: simpan ID saja

User **tidak** mengirim nama dokter / nama tindakan / nama barang — hanya `id`. Nama selalu
di-resolve saat tampilan dari master (`Pegawai`, `Tindakan`, `Barang`). Konsekuensinya:

- Master boleh berubah nama tanpa mengubah data EMR.
- Master di-soft-delete → baris EMR tetap utuh, tapi nama tidak ter-resolve (dropdown memakai
  `aktif()`, jadi record lama tidak muncul sebagai pilihan baru).

## 3. Baris obat/BMHP — kenapa variabel BERSUFFIX

Baris obat/BMHP **wajib** disimpan sebagai pasangan variabel per baris:

```
obat_1   -> barang_id baris 1     jumlah_1  -> jumlah baris 1
obat_2   -> barang_id baris 2     jumlah_2  -> jumlah baris 2
...  s/d 20
```

**Alasannya:** `EmrHelper::emrDetailByVariabel()` melakukan
`emr_detail` → `pluck('value', 'variabel')`. Kalau N baris memakai nama variabel yang sama
(mis. `obat` untuk semua baris), baris kedua dan seterusnya **tertimpa** dan hilang saat form
dibuka lagi. Dengan suffix, seluruh key unik sehingga semua baris terbaca utuh, dan
`array_intersect_key()` di controller tetap menyisakannya.

**Aturan tambahan:**suffix diambil dari atribut `data-item` baris (`tr`), **bukan** dari urutan
array. Menghapus baris di tengah **tidak boleh** menggeser indeks — kalau indeks dihitung ulang
berdasarkan posisi (`obat[]` + `jumlah[]`), pasangan `obat_N` dengan `jumlah_N` bisa menempel ke
baris yang salah (bug yang sama pernah terjadi di Order Laboratorium). Counter di view hanya
naik, tidak pernah dipakai ulang.

Pembacaan sisi server (`TindakanMedisController::kumpulkanBarisObat()`) melakukan loop
`obat_1..obat_20`, mengambil pasangan yang terisi, lalu melempar sisanya ke `emr_detail` sebagai
`obat_N`/`jumlah_N`.

## 4. Validasi

- Semua field utama wajib; `tanggal_tindakan` `date`, `waktu_tindakan` `date_format:H:i`.
- `tindakan_id` harus `exists:tindakan,tindakan_id` **dan** lolos `Tindakan::aktif()`.
- `dokter_persetujuan_id` harus `exists:pegawai,pegawai_id`.
- `pemakaian_obat_bmhp = Ya` → minimal satu baris dengan `jumlah > 0`.
- Bila `Tidak`, baris obat/BMHP di-`disabled` di UI sehingga tidak terkirim sama sekali.

Semua error muncul lewat blok `$errors->any()` di `layouts/iframe.blade.php` plus pesan per-field
(`@error`).

## 5. Akses EHR

`akses_ehr` untuk `form_id = 10` di-seed **full CRUD** (create/read/update/delete = 1) bagi
profesi **1 (Dokter)** dan **2 (Perawat)**. Tanpa baris ini form tidak muncul di dashboard dan
selalu 403 (`AksesEhr::can()` dipakai di `index`/`store`/`update`/`destroy`).

## 6. Master pendukung

- **Barang** (tabel `barang`) — sumber pilihan obat/BMHP. `JenisBarangSeeder` mengisi
  `barang_jenis` (`Obat`, `BMHP`); `BarangSeeder` mengisi 18 item contoh (8 Obat + 10 BMHP).
- **Dokter** — `x-select_dokter` membaca tabel `pegawai` (`profesi_id = 1`), **bukan** `users`.
  `MasterPegawaiSeeder` menyertakan 5 dokter (`pegawai_id` 3, 14, 15, 16, 17).

## 7. Gotcha Blade

Opsi `<option>` untuk baris obat/BMHP yang ditambahkan lewat JS harus dihitung di blok `@php`
lalu diteruskan sebagai **variabel tunggal** ke `@json()`. `@json()` dipecah dengan
`explode(',')`, sehingga ekspresi kompleks (mis. `$barangs->map(...)->implode()`) di dalam
argumen `@json()` akan terpotong dan menghasilkan `ParseError` / error runtime.

## 8. Pemakaian obat/BMHP: cascade + batch + stok

### Alur isian: baris lahir dari modal

Baris obat/BMHP **tidak pernah dibuat otomatis**. Saat `pemakaian_obat_bmhp = Ya`
dan tabel masih kosong, panel menampilkan empty state; baris baru hanya lahir dari
tombol **"Cari & Tambah Obat/BMHP"** yang membuka modal 3 langkah:

```
1. Jenis Barang (Obat / BMHP)      -> radio, memblokir langkah 2; pilihan terakhir
                                        dibawa ke pembukaan modal berikutnya
2. Obat / BMHP                     -> Select2 AJAX, difilter per jenis
3. Nomor Batch                     -> TABEL (No Batch | Tgl Expired | Sisa Stok | Jumlah Ambil)
        pilih SATU batch (radio)  -> isi Jumlah  -> klik "Simpan ke Form"
                                    = menambah SATU baris di form
```

Kolom **Jumlah Ambil** tersedia per baris tabel, dan input jumlah utama di bawah
tabel ikut terisi saat batch dipilih. Nilai yang dipakai saat menyimpan adalah
input jumlah utama; bila masih kosong, dipakai nilai kolom tabel. Baris yang
dibentuk otomatis terisi `jenis_barang_N`, `obat_N`, `batch_N`, **dan `jumlah_N`**
— user tetap bisa menyunting jumlah di baris form.

Tombol "Cari & Tambah Obat/BMHP" **hanya aktif saat `Pemakaian Obat/BMHP = Ya`** (disembunyikan sekaligus dinonaktifkan; `bukaModal()` juga menolak dengan peringatan bila dipaksa dibuka).

**Satu barang + satu batch = satu baris.** Kalau batch yang sama dipilih lagi,
jumlahnya **digabung** ke baris yang sudah ada (bukan menambah baris baru), disertai
peringatan "Barang Sudah Ditambahkan".

Server juga menjadi penjaga akhir (klien bisa saja mengirim POST langsung):

| Kasus | Hasil |
|---|---|
| `(barang_id, no_batch)` sama muncul 2× dalam satu payload | **ditolak** — "sudah ditambahkan pada baris #N" |
| nomor batch sama dipakai untuk **barang berbeda** | **ditolak** — nomor batch unik per fisik barang |

Kolom jumlah di tabel terkunci: hanya baris yang batch-nya sedang dipilih yang
boleh diisi, dan input jumlah utama baru aktif setelah satu batch dipilih.

Peringatan memakai komponen **`confirm-alert`** (`window.nusaConfirm`), bukan
`alert()` bawaan browser:

| Situasi | Judul dialog |
|---|---|
| Jenis / barang / batch belum dipilih | "…Belum Dipilih" |
| Jumlah kosong | "Jumlah Belum Diisi" |
| Jumlah bukan angka / <= 0 | "Jumlah Tidak Valid" (merah) |
| Jumlah melebihi sisa stok | "Jumlah Melebihi Stok" (merah, menyebut sisa) |
| Batch sudah dipakai baris lain | "Jumlah Digabung" (jumlah digabung ke baris itu) |
| Batch habis | "Stok Batch Tidak Tersedia" (merah) |
| Batas 20 baris | "Batas Baris Tercapai" |

Fungsi `peringatan()` membungkus `nusaConfirm` dengan fallback ke `alert()` bila
komponen belum termuat.

### Nomor batch: `<select>` biasa, bukan Select2

Pada **aksi Lihat** sisa stok tidak ditampilkan sama sekali: badge hijau di bawah
dropdown dihilangkan dan label option diringkas menjadi
`OBT-003-2610-1 · exp 01/10/2027` (flag `$tampilkanSisa` pada partial
`opsiBatch`).

Dropdown nomor batch di baris form memakai **`<select>` biasa** — satu batch per
baris sehingga daftarnya pendek dan tidak perlu pencarian. Labelnya:

```
OBT-001-2610-1 — sisa 59 · exp 01/10/2027
OBT-001-2401-1 — sisa 40 · exp 31/01/2024 (Kedaluwarsa)
```

Batch **kedaluwarsa tetap ditampilkan** (agar riwayat stoknya terlihat) tapi diberi
tanda `(Kedaluwarsa)` dan atribut `disabled` sehingga tidak bisa dipilih.

### Urutan batch: FEFO

`StockHelper::sisaPerBatch()` mengurutkan batch berdasarkan `tgl_expired` **paling
dahulu** (First Expired, First Out) supaya batch yang hampir habis dipakai lebih
langsung. Batch tanpa `tgl_expired` diletakkan di akhir.

Bentuk balik map-nya diperkaya agar bisa menampilkan tanggal kedaluwarsa:

```php
[barang_id => [
    'BATCH-001' => ['jumlah' => 50.0, 'tgl_expired' => '2027-10-01', 'kedaluwarsa' => false],
]]
```

Bila satu batch punya beberapa baris `stock` dengan tanggal berbeda, yang dipakai
tanggal **paling awal** agar batch tidak pernah terpakai melewati tanggal.

### Select2 hanya untuk dropdown barang

| Dropdown | Widget | Alasan |
|---|---|---|
| Jenis Barang | radio | hanya 2 pilihan |
| Obat / BMHP | **Select2 AJAX** | master barang banyak, perlu mengetik mencari |
| No. Batch | `<select>` biasa | satu batch per baris, daftar pendek |

**Dua aturan Select2 pada baris dinamis (WAJIB)** — hasil pelajaran dari bug
"batch tidak bisa dipilih" dan "input dobel":

1. **Selalu `select2('destroy')` sebelum init ulang.** Select2 yang sudah terpasang
   mengabaikan pemanggilan `select2()` berikutnya sehingga muncul container dropdown
   kedua di samping elemen asli (terlihat seperti input dobel).
2. **Jangan `disabled` untuk menonaktifkan baris.** Select2 mengunci status disabled
   saat di-init, jadi mengaktifkan kembali elemen asli tidak berefek dan dropdown
   tetap tak bisa diklik. Gunakan **lepas atribut `name`**.

Dropdown batch memang memakai `disabled` — itu aman karena `<select>` biasa, bukan
Select2.

### Endpoint pencarian barang

`GET /api/barang/search` (`api.barang.search` → `ApiBarangController@searchBarang`):

| Param | Fungsi |
|---|---|
| `q` | kata kunci, dicocokkan ke `nama_barang` / `kode_barang` |
| `jenis_barang_id` | filter jenis barang (menerapkan cascade di langkah 1 -> 2) |
| `limit` | jumlah hasil, default 50, dibatasi maksimal 200 |

Balikan `{ "results": [{ "id", "text", "kode", "satuan" }] }` dengan `text` =
`KODE - Nama (satuan)`, sehingga yang diketik user cocok dengan yang tampil.

> Semula `ApiBarangController` memakai `ILIKE` yang hanya ada di PostgreSQL. Sudah
> diganti `like` supaya jalan di MySQL (kolasi `utf8mb4_unicode_ci` sudah
> case-insensitive).

### Catatan Tailwind v4

Tampil/sembunyi memakai helper `tampilkan()`, yang **melepas/pasang class `hidden`
sekalian** lalu sets inline `style.display`. Dua hal ini wajib beriringan:

- Tailwind v4 menaruh `.hidden` sebelum `.inline-flex` di hasil CSS, sehingga elemen
  yang punya kedua class itu tetap terlihat walau `hidden` ditambahkan.
- Sebaliknya, hanya sets `style.display = ''` juga tidak cukup: bila markup masih
  memuat class `hidden`, elemen tetap `display:none` dari aturan class tersebut.

### Gotcha Blade: jangan bersarang `@php`

Blok `@php` **tidak boleh** diletakkan di dalam blok `@php` atau di dalam
`@foreach` yang juga memakai `@php`. Regex `@php` milik Blade bersifat greedy
sehingga blok luar menelan blok di dalamnya; hasil kompilasi terpotong dan
melempar `ParseError: unexpected token endforeach`. Di `opsiBatch.blade.php` ini
karena itu dipakai tag `<?php ... ?>` biasa di dalam loop.

### Partial opsi

| Partial | Isi |
|---|---|
| `partials/opsiObat.blade.php` | **hanya** opsi yang sedang terpilih, agar Select2 menampilkan nilai lama saat mode edit |
| `partials/opsiBatch.blade.php` | `<option>` batch (sisa + exp + tanda kedaluwarsa) untuk satu barang |

### Sumber stok = bagian tempat pasien dirawat

Sisa stok **bukan** penjumlahan ulang dari `penerimaan_detail` minus `mutasi_barang_detail`,
tetapi dibaca dari tabel `stock` (`running balance` per `barang_id` + `no_batch` + `bagian_id`),
difilter `bagian_id = registrasi_detail.bagian_id` — lokasi tempat form EMR dibuka.

Helper di `app/Helpers/StockHelper.php`:

| Method | Kegunaan |
|---|---|
| `sisaPerBatch(int $bagianId): array` | map `barang_id => [no_batch => jumlah]`, hanya saldo > 0; dikirim ke view |
| `sisaBatch(int $bagianId, int $barangId, ?string $noBatch): float` | cek satu batch di server |
| `catatPemakaian(...)` | `tambahKeluar` + `kartu_stock.jenis_mutasi = 4` (Pemakaian) |
| `kembalikanPemakaian(...)` | `tambahMasuk` untuk koreksi/pembatalan |

`KartuStock` punya konstanta `JENIS_SALDO_AWAL`, `JENIS_PENERIMAAN`, `JENIS_MUTASI_MASUK`,
`JENIS_MUTASI_KELUAR`, `JENIS_PEMAKAIAN`, `JENIS_DISPENSE_OBAT` yang dipakai `JENIS_LABEL`.

### Pengurangan & pengembalian stok

- **Simpan**: `store()` membungkus `EmrHelper::insert()` + `catatPemakaian()` dalam satu
  `DB::transaction()`, jadi EMR dan kartu stok bersifat atomik.
- **Ubah**: `update()` mengembalikan stok pemakaian **lama** dulu
  (`barisObatTersimpan()` membaca `emr_detail` sebelum detail lama di-soft-delete),
  lalu mencatat stok pemakaian baru.
- **Hapus**: `destroy()` mengembalikan stok pemakaian lalu soft-delete EMR.
- `pemakaian_obat_bmhp = Tidak` → tidak ada pergerakan stok, dan seluruh baris
  obat/BMHP dibuang dari payload (`filteredData()`), termasuk bila browser tetap
  mengirimnya, supaya tidak ada baris yatim di `emr_detail`.

### Validasi stok di server (tidak percaya browser)

- `jenis_barang_N` harus sama dengan `barang.jenis_barang_id`.
- `batch_N` wajib diisi dan harus punya saldo `> 0` di bagian pasien.
- `jumlah_N` tidak boleh melebihi sisa stok batch tersebut (pesan error menyebutkan sisa stok).
- Sisa stok dibaca ulang dari tabel `stock` setiap kali validasi berjalan.

### Seeder stok awal

`database/seeders/StokAwalSeeder.php` mengisi `stock` + `kartu_stock`
(`jenis_mutasi = 0` Saldo Awal) untuk 18 barang × 2 batch × 3 bagian (Poli RJ,
Ruang Perawatan, IGD) = 108 baris stok. Lokasi diambil dari `bagian_id` yang benar-benar
dipakai `registrasi_detail` (bukan id hardcode), mencakup `harga_jual = NULL` supaya
`StockHelper` saat mengurangi stok menyasar baris stok yang sama.

Tanpa seeder ini tabel `stock` kosong, sehingga **tidak ada satu pun batch yang bisa dipilih**
dan baris obat/BMHP selalu ditolak. Idempotent: batch yang sudah ada dilewati.
