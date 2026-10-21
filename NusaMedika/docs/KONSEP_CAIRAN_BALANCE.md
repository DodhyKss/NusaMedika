# Konsep Cairan & Balance Cairan (EMR)

**Status:** Rancangan / Konsep — belum diimplementasikan
**Tanggal:** 2026-10-10
**Acuan:** [`PANDUAN_IMPLEMENTASI_FORM_EMR.md`](PANDUAN_IMPLEMENTASI_FORM_EMR.md),
[`ANALISIS_KEKURANGAN_FORM.md`](ANALISIS_KEKURANGAN_FORM.md)
**Sumber legacy:** `/simrs_tenriawaru/FE/lib/modul/`

---

## 1. Ringkasan

| form_id | nama_form | slug | id_dash_menu | ri | rj | igd | mcu |
|---|---|---|---|---|---|---|---|
| 24 | Intake | `intake` | `2.93.46` | 1 | 0 | 0 | 0 |
| 25 | Output | `output` | `2.93.47` | 1 | 0 | 0 | 0 |
| 26 | Monitoring Cairan | `monitoring_cairan` | `9.94` | 1 | 0 | 0 | 0 |
| 28 | Kesadaran & Pemberian O2 | `kesadaran_oksigen` | `9.95` | 1 | 0 | 0 | 0 |

Objek baru yang dialokasikan: **291 – 312** (22 objek, sesuai band
`ALOKASI_ID_GLOBAL.md` §1). Objek lama yang
di-*reuse*: 8 (Berat Badan), 15 (Saturasi), 18/19/20 (Oksigen/Cara/ETT),
51 (Kesadaran), 54–57 (GCS), 59 (DPO), 77 (Keterangan), 155/156 (Tanggal/Waktu).

### 1.1 Struktur menu dashboard

```
dashboard_menu 2  "Catatan Keperawatan"
└── sub 93  "Balance Cairan"
    ├── extra 46  "Intake"    → form 24 (2.93.46)
    └── extra 47  "Output"    → form 25 (2.93.47)

dashboard_menu 9  "Monitoring"   (MENU BARU)
├── sub 94  "Monitoring Cairan"          → form 26 (9.94)
├── sub 95  "Kesadaran Oksigen"           → form 28 (9.95)
├── sub 113 "Nyeri"                       → dipakai KONSEP_NYERI.md
└── sub 114 "Body Map Nyeri"              → dipakai KONSEP_NYERI.md
```

> `dashboard_menu_sub_extra` 46–47 milik dokumen ini (26–45 dipakai
> `KONSEP_ASUHAN_KEPERAWATAN_LANJUTAN.md`, 66–68 milik `KONSEP_NYERI.md`,
> 86–88 milik `KONSEP_RISIKO_JATUH_LANJUTAN.md`).
> Band sub dokumen ini = **93–112**; sub 113–132 milik `KONSEP_NYERI.md`
> (`ALOKASI_ID_GLOBAL.md` §3). `dashboard_menu_sub_id` bersifat **global** —
> sub 1–12 milik menu 1–5 dan tidak boleh dipakai ulang.

---

## 2. Sumber Referensi Legacy

| Form | Berkas legacy | Peran |
|---|---|---|
| 24 Intake | `FE/lib/modul/intake.php` | Infus 3 cabang, Spoel/Oral/Transfusi, AB, Syringe Pump 6 slot |
| 25 Output | `FE/lib/modul/output.php` | Urine, NGT, WSD, Drain, Output Lain |
| 26 Monitoring Cairan | `FE/lib/modul/monitoring_cairan.php`, `monitoring_cairan_act.php` | Time-series intake/output + IWL |
| 26 (eliminasi) | `FE/lib/modul/Eliminasi_cak.php` | Frekuensi & karakter BAB/BAK |
| 28 Kesadaran & O2 | `FE/lib/modul/oksigen.php` | GCS lengkap, DPO, flow O2, cara pemberian, ETT, saturasi |

---

## 3. Mekanisme Inti: Flag Abnormal & Indeks Ronde

Bagian ini adalah fondasi seluruh form cairan, jadi dibahas sekali di sini.

### 3.1 Yang benar-benar ada di skema

```php
// database/migrations/2026_08_28_000030_create_emr_detail_table.php
Schema::create('emr_detail', function (Blueprint $table) {
    $table->increments('emr_detail_id');
    $table->timestamp('input_time', 6)->nullable();
    $table->integer('input_user_id')->nullable();
    $table->timestamp('mod_time', 6)->nullable();
    $table->integer('mod_user_id')->nullable();
    $table->smallInteger('status_batal')->nullable();
    $table->integer('emr_id')->nullable();
    $table->integer('objek_id')->nullable();
    $table->string('variabel', 250)->nullable();
    $table->text('value')->nullable();
    $table->smallInteger('flag_abnormal')->nullable();   // <-- KOLOM INI
    $table->uuid('id_satu_sehat')->nullable();
    $table->index('emr_id');
    $table->index('objek_id');
});
```

`emr_detail.flag_abnormal` (`smallInteger`, nullable) **sudah ada** dan saat ini
dipakai oleh modul Order Laboratorium / Radiologi dengan konvensi:

| Nilai | Arti |
|---|---|
| `NULL` | belum ada penilaian / tidak relevan |
| `0` | normal / dalam batas |
| `1` | **ABNORMAL** |

Lihat `resources/views/moduls/EMR/Laboratorium/index.blade.php:87,202,206` dan
`resources/views/moduls/PenunjangMedis/Radiologi/DaftarPesananRadiologi/detail.blade.php:103–117`.

### 3.2 Yang BELUM ada, dan harus ditambahkan

**`EmrHelper::storeDetails()` saat ini tidak pernah mengisi `flag_abnormal`:**

```php
// app/Helpers/EmrHelper.php:424-442 — kolom flag_abnormal TIDAK ada di insert
DB::table('emr_detail')->insert([
    'emr_id'      => $emrId,
    'objek_id'    => static::objekId($formId, $variabel),
    'variabel'    => $variabel,
    'value'       => is_array($value) ? json_encode($value) : $value,
    'input_time'  => $now,
    'input_user_id' => $user->user_id ?? null,
]);
```

Karena itu diperlukan penambahan **backward-compatible** (tidak mengubah
perilaku form 1–15):

```php
// app/Helpers/EmrHelper.php — tambahan
public static function insert(int $formId, array $data, int $registrasiDetailId, array $flags = []): int
{
    // ... signature ditambah parameter $flags default []
    static::storeDetails($emrId, $formId, $data, $now, $flags);
}

public static function update(int $emrId, int $formId, array $data, array $flags = []): void
{
    // ... sama
}

private static function storeDetails(int $emrId, int $formId, array $data, $now, array $flags = []): void
{
    foreach ($data as $variabel => $value) {
        DB::table('emr_detail')->insert([
            'emr_id'        => $emrId,
            'objek_id'      => static::objekId($formId, $variabel),
            'variabel'      => $variabel,
            'value'         => is_array($value) ? json_encode($value) : $value,
            'flag_abnormal' => $flags[$variabel] ?? null,   // BARU
            'input_time'    => $now,
            'input_user_id' => $user->user_id ?? null,
        ]);
    }
}
```

`$flags` adalah peta `[variabel => 0|1]`. Variabel yang tidak disebut menyimpan
`NULL` — sehingga data form lama tidak berubah sama sekali.

### 3.3 Apa yang ditandai abnormal di form cairan

Konteks: output harian di bawah kebutuhan cairan dan volume masuk yang melebihi
kebutuhan adalah temuan klinis. Keduanya harus **tampak di panel riwayat** tanpa
menghitung ulang tiap kali.

| variabel | objek | Flag `1` bila |
|---|---|---|
| `total_intake` | 291 | `total_intake > kebutuhan_cairan` ( Indonesian: intake melebihi kebutuhan ) |
| `total_output` | 306 | `total_output < 0.5 × kebutuhan_cairan` (oliguria) |
| `balance_cairan` | 311 | `balance_cairan` di luar rentang ±500 cc/hari, **atau** kumulation > ±2000 cc |
| `sisa_infus` | 294 | `sisa_infus < 0` (infus terlampaui) |

```php
// MonitoringCairanController::filteredData()
$kebutuhan = $this->kebutuhanCairan($data['berat_badan'] ?? null);   // BB*40 s/d BB*50
$intake    = (float) ($data['total_intake'] ?? 0);
$output    = (float) ($data['total_output'] ?? 0);
$balance   = $intake - $output;

$data['total_intake']   = $intake;
$data['total_output']   = $output;
$data['balance_cairan'] = $balance;

$flags = [
    'total_intake'   => $kebutuhan ? ($intake > $kebutuhan['maks'] ? 1 : 0) : null,
    'total_output'   => $kebutuhan ? ($output < $kebutuhan['min'] * 0.5 ? 1 : 0) : null,
    'balance_cairan' => (abs($balance) > 500 ? 1 : 0),
    'sisa_infus'     => isset($data['sisa_infus']) ? (((float) $data['sisa_infus']) < 0 ? 1 : 0) : null,
];

EmrHelper::insert((int) $form_id, $data, (int) $registrasi_detail_id, $flags);
```

> **Nilai dari browser untuk `flag_abnormal` selalu dibuang.** Column flag hanya
> boleh ditulis server. Ini mengikuti PANDUAN §2.4 (field computed tidak
> dipercaya dari browser).

### 3.4 Membaca flag di riwayat

`x-emr-history-table` saat ini hanya menampilkan nilai. Diperluas agar bisa
menampilkan badge:

```php
// app/Helpers/EmrHelper.php — helper baru
public static function emrFlags(int $emrId): array
{
    return DB::table('emr_detail')
        ->where('emr_id', $emrId)
        ->where(function ($q) { $q->whereNull('status_batal')->orWhere('status_batal', 0); })
        ->where('flag_abnormal', 1)
        ->pluck('variabel', 'variabel')
        ->all();
}
```

Di blade:

```blade
@php($flag = EmrHelper::emrFlags($emr_id))
@if (!empty($flag))
    <span class="inline-block w-1.5 h-1.5 rounded-full bg-red-500"></span>
    <span class="text-[10px] font-bold text-red-700">ABNORMAL</span>
@endif
```

### 3.5 Indeks ronde (round indexing)

**Tidak ada kolom `round` di `emr_detail`.** Indeks ronde pada NusaMedika
didefinisikan sebagai berikut:

```
SATU RONDE OBSERVASI = SATU BARIS `emr`
```

Artinya:
- Setiap kali perawat menyimpan monitoring cairan pada jam 08:00, 14:00, dan 22:00,
  terbentuk **tiga baris `emr`** dengan `form_id = 26` pada `registrasi_detail`
  yang sama.
- Nomor ronde dihitung saat **baca**, bukan disimpan:

```php
/**
 * Daftar ronde monitoring cairan untuk satu registrasi_detail,
 * Bernomor dari 1 (terlama) dan diurutkan naik per waktu.
 *
 * @return array<int, object>  [{ ronde => 1, emr_id => 901, tgl_jam => ... }, ...]
 */
public static function rondeMonitoring(int $formId, int $registrasiDetailId): array
{
    $rows = DB::table('emr')
        ->where('form_id', $formId)
        ->where('registrasi_detail_id', $registrasiDetailId)
        ->where(function ($q) { $q->whereNull('status_batal')->orWhere('status_batal', 0); })
        ->orderBy('tgl_jam')
        ->orderBy('emr_id')
        ->get();

    return $rows->values()->map(fn ($r, $i) => (object) [
        'ronde'  => $i + 1,
        'emr_id' => $r->emr_id,
        'tgl_jam'=> $r->tgl_jam,
        'pegawai_id' => $r->pegawai_id,
    ])->all();
}
```

Mengapa `emr_id` = ronde, bukan kolom khusus:

1. **Tidak perlu migration baru** — PANDUAN §1 melarang migration kecuali butuh
   tabel master baru.
2. Konsisten dengan seluruh form EMR lain di project ini: satu `emr` = satu
   pengisian form (sudah berlaku di form 13 Tanda Vital, form 9 Implementasi).
3. `EmrHelper::update()` melakukan soft-delete detail lama lalu insert ulang, jadi
   indeks ronde tetap stabil selama baris `emr` tidak dihapus.
4. Ada indeks `emr.form_id` dan `emr.registrasi_detail_id` sehingga query di atas
   inexpensive.

> **Kalau nanti dibutuhkan** kolom ronde eksplisit (mis. untuk integrasi SATU SEHAT
> yang butuh `id_satu_sehat` per episode), tambahkan
> `emr_detail.round_index smallint nullable` melalui migration terpisah dan isi di
> `storeDetails()`. Untuk versi sekarang, indeks turunan di atas sudah cukup.

### 3.6 Baris dalam satu ronde

**Posisi baris/kolom dalam satu ronde** (mis. "Cabang Infus ke-2") memakai
**sufiks angka pada variabel**, bukan indeks ronde:

```
nama_infus_1  volume_infus_1  sisa_infus_1  ivfd_infus_1
nama_infus_2  volume_infus_2  sisa_infus_2  ivfd_infus_2
...
nama_infus_6  volume_infus_6  sisa_infus_6  ivfd_infus_6
```

Maks 6 cabang (sesuai legacy: 3 cabang infus + 3 tambahan; legacy `intake.php`
menggunakan 3, `monitoring_cairan.php` tidak membatasi). **Indeks diambil dari
atribut `data-item` baris, bukan urutan array** — menghapus baris di tengah tidak
menggeser pasangan kolom (bug yang sama pernah terjadi di Order Laboratorium
dan Tindakan Medis, lihat `AGENTS.md`).

---

## 4. Intake (form 24)

### Rasional

Mencatat seluruh cairan yang masuk: infus, syringe pump, oral, spoel, transfusi,
dan obat. Legacy `intake.php` punya:
- **INFUS**: 3 cabang (dropdown 47 jenis cairan: RL, Asering, NaCl 0,9 %, NaCl 3 %,
  Dext 5 %, Dext 10 %, Aminofusin L 600, Kaen 1 B, Clinimix, NUTRIFLEX LIPID PERI
  1250 ML INFUS, dst.), tiap cabang punya `dosis` (cc), `tetes/menit`, `masuk` (cc).
- **SPOEL / ORAL / TRANSFUSI**: no. kantong + volume masuk.
- **AB**: volume masuk (cc).
- **SYRINGE PUMP**: 6 slot (nama obat + jumlah cc).

NusaMedika menyederhanakan: jenis cairan diambil dari **master `barang`**
(tabel sudah ada — dipakai form 10 Tindakan Medis), bukan array 47 elemen statis.
Kolom `nama_barang` disimpan sebagai snapshot terpisah.

### Struktur Field

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `nama_infus_1..6` | 292 | Nama Cairan Infus | select (master `barang`) | ya (baris dipakai) | Legacy `barang_id_infusC{n}` |
| `volume_infus_1..6` | 293 | Volume Masukkan Infus (cc) | number | ya | |
| `sisa_infus_1..6` | 294 | Sisa Infus (cc) | number | tidak | Turunan = dosis − volume masuk |
| `ivfd_1..6` | 309 | IVFD (cc/menit) | number | tidak | Legacy `ivfd_infusC{n}` |
| `oral` | 295 | Intake Oral (cc) | number | tidak | Legacy `jenis_Oral` + `masuk_Oral` |
| `spoel` | 296 | Intake Spoel (cc) | number | tidak | |
| `transfusi` | 297 | Intake Transfusi (cc) | number | tidak | Legacy no. kantong disimpan di `catatan` |
| `syringe_pump_1..6` | 298 | Intake Syringe Pump (cc) | number | tidak | |
| `intake_lainnya` | 299 | Intake Lainnya (cc) | number | tidak | Antibiotik/Spoel/Drips |
| `total_intake` | 291 | Total Intake Cairan (cc) | readonly (server) | ya | **Turunan** |
| `nama_barang_snapshot` | 77 | Keterangan | text | tidak | Reuse objek 77 — nama cairan saat simpan |
| `catatan` | 77 | Keterangan | textarea | tidak | Reuse objek 77 |

### Mapping

```php
24 => [
    'total_intake'    => 291,
    'nama_infus_1'    => 292, 'volume_infus_1' => 293, 'sisa_infus_1' => 294,
    'nama_infus_2'    => 292, 'volume_infus_2' => 293, 'sisa_infus_2' => 294,
    'nama_infus_3'    => 292, 'volume_infus_3' => 293, 'sisa_infus_3' => 294,
    'nama_infus_4'    => 292, 'volume_infus_4' => 293, 'sisa_infus_4' => 294,
    'nama_infus_5'    => 292, 'volume_infus_5' => 293, 'sisa_infus_5' => 294,
    'nama_infus_6'    => 292, 'volume_infus_6' => 293, 'sisa_infus_6' => 294,
    'oral'            => 295,
    'spoel'           => 296,
    'transfusi'       => 297,
    'syringe_pump_1'  => 298, 'syringe_pump_2' => 298, 'syringe_pump_3' => 298,
    'syringe_pump_4'  => 298, 'syringe_pump_5' => 298, 'syringe_pump_6' => 298,
    'intake_lainnya'  => 299,
    'ivfd_1'          => 309, 'ivfd_2' => 309, 'ivfd_3' => 309,
    'ivfd_4'          => 309, 'ivfd_5' => 309, 'ivfd_6' => 309,
    'catatan'         => 77,
],
```

> `total_intake` (291) di-*share* dengan form 26 — sengaja, agar laporan balance
> bisa menjumlahkan intake dari kedua sumber tanpa penerjemahan variabel.
> `ivfd` (309) di-*share* dengan form 26.

### Dashboard & Form Master

```php
['dashboard_menu_sub_id' => 93, 'dashboard_menu_id' => 2, 'nama_sub_menu' => 'Balance Cairan'],
['dashboard_menu_sub_extra_id' => 46, 'dashboard_menu_sub_id' => 93, 'nama_sub_menu_extra' => 'Intake'],
['form_id' => 24, 'nama_form' => 'Intake', 'slug' => 'intake', 'id_dash_menu' => '2.93.46', 'ri' => 1, 'rj' => 0, 'igd' => 0, 'mcu' => 0],
```

### Akses EHR

```php
['profesi_id' => 1, 'form_id' => 24, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 2, 'form_id' => 24, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
```

### Validasi

```php
'volume_infus_1'   => 'required_if:nama_infus_1,!=,|nullable|numeric|min:0',
'sisa_infus_1'     => 'nullable|numeric',
'oral'             => 'nullable|numeric|min:0',
'spoel'            => 'nullable|numeric|min:0',
'transfusi'        => 'nullable|numeric|min:0',
'syringe_pump_1'   => 'nullable|numeric|min:0',
'intake_lainnya'   => 'nullable|numeric|min:0',
'ivfd_1'           => 'nullable|numeric|min:0',
'catatan'          => 'nullable|string|max:1000',
// kolom 2..6 nullable dengan aturan yang sama
```

Validasi tambahan di `filteredData()`:

```php
// Buang baris yatim: nama_infus_N kosong -> volume/sisa/ivfd_N ikut dibuang.
for ($i = 1; $i <= 6; $i++) {
    if (blank($data['nama_infus_'.$i])) {
        unset($data['volume_infus_'.$i], $data['sisa_infus_'.$i], $data['ivfd_'.$i]);
    }
    if (blank($data['syringe_pump_'.$i])) {
        unset($data['syringe_pump_'.$i]);
    }
}
```

Snapshot nama cairan diisi dari master `barang`:

```php
$barangMap = Barang::aktif()->whereIn('barang_id', array_filter(array_map(
    fn ($i) => (int) ($data['nama_infus_'.$i] ?? 0), range(1, 6)
)))->pluck('nama_barang', 'barang_id');
// disimpan sebagai "id1=RL;id2=Dext 5%" pada variabel `catatan` (objek 77)
```

### Cetak

Tidak wajib (form entry harian, bukan dokumen Patient Chart).

---

## 5. Output (form 25)

### Rasional

Mencatat cairan keluar. Legacy `output.php` sangat ringkas: lima kolom angka
(`Urine`, `NGT`, `WSD`, `Drain`, `Output_Lain`) dalam cc. `monitoring_cairan.php`
mengelompokkan menjadi dua tabel:

- Kelompok 1 (sudah otomatis dihitung di balance legacy): `Urine`, `BAB`, `WSD`, `NGT`
- Kelompok 2: `Drain`, `Colostomy`, `UFG`, `Lainnya`

Plus **IWL** (Insensible Water Loss) yang dihitung otomatis dari berat badan & suhu.
NusaMedika menambahkan IWL sebagai kolom **read-only hasil turunan** agar bisa
dilaporkan (legacy tidak menyimpannya).

### Struktur Field

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `urine` | 300 | Output Urine (cc) | number | ya | |
| `drain` | 301 | Output Drain (cc) | number | tidak | |
| `ngt` | 302 | Output NGT (cc) | number | tidak | Gastrik suction |
| `wsd` | 303 | Output WSD (cc) | number | tidak | Watershed drainage |
| `iwl` | 304 | Output IWL (cc) | readonly (server) | ya | **Turunan** — lihat §5.1 |
| `output_lainnya` | 305 | Output Lainnya (cc) | number | tidak | Colostomy / UFG / lainnya |
| `total_output` | 306 | Total Output Cairan (cc) | readonly (server) | ya | **Turunan** |
| `frekuensi_bab` | 116 | Eliminasi (Bj) | number | tidak | Reuse objek 116 |
| `karakter_bab` | 116 | Eliminasi (Bj) | checkbox group | tidak | Reuse objek 116; opsi `Eliminasi_cak.php` |
| `frekuensi_bak` | 116 | Eliminasi (Bj) | number | tidak | Reuse objek 116 |
| `karakter_bak` | 116 | Eliminasi (Bj) | checkbox group | tidak | Reuse objek 116 |
| `catatan` | 77 | Keterangan | textarea | tidak | Reuse objek 77 |

Opsi `karakter_bab` (dari `Eliminasi_cak.php`): Normal, Diare, Konstipasi,
Colostomy, Melena, Inkontinensia, Lainnya.

Opsi `karakter_bak`: Normal, Disuria, Retensio, Inkontinensia, Hematuri, Lainnya.

### 5.1 Perhitungan IWL (server-side)

Formula Hudson:

```
IWL (cc/24 jam) = (25 + berat_badan_kg) × jumlah_tanda_vital
```

`jumlah_tanda_vital` = 1 (suhu normal) · 1,5 (suhu 38,0–39,0) · 2 (suhu > 39,0).
Bila `suhu` tidak diisi, default 1.

```php
// OutputController::filteredData()
$bb   = (float) ($data['berat_badan'] ?? 0);
$suhu = (float) ($data['suhu'] ?? 0);
$tr   = $suhu <= 0 ? 1.0 : ($suhu > 39.0 ? 2.0 : ($suhu >= 38.0 ? 1.5 : 1.0));
$data['iwl'] = $bb > 0 ? round((25 + $bb) * $tr, 0) : 0;
```

Nilai dari browser untuk `iwl` **diabaikan**.

### 5.2 Total Output

```php
$total = 0.0;
foreach (['urine','drain','ngt','wsd','iwl','output_lainnya'] as $k) {
    $total += (float) ($data[$k] ?? 0);
}
$data['total_output'] = round($total, 0);
```

### Mapping

```php
25 => [
    'urine'         => 300,
    'drain'         => 301,
    'ngt'           => 302,
    'wsd'           => 303,
    'iwl'           => 304,
    'output_lainnya'=> 305,
    'total_output'  => 306,
    'frekuensi_bab' => 116,  // reuse
    'karakter_bab'  => 116,  // reuse
    'frekuensi_bak' => 116,  // reuse
    'karakter_bak'  => 116,  // reuse
    'catatan'       => 77,   // reuse
],
```

> Empat variabel berbagi objek 116. Aman untuk `emrDetailByVariabel()`;
> **tidak aman** untuk `emrDetailByObjek()`. Lihat catatan R3.

### Dashboard & Form Master

```php
['dashboard_menu_sub_extra_id' => 47, 'dashboard_menu_sub_id' => 93, 'nama_sub_menu_extra' => 'Output'],
['form_id' => 25, 'nama_form' => 'Output', 'slug' => 'output', 'id_dash_menu' => '2.93.47', 'ri' => 1, 'rj' => 0, 'igd' => 0, 'mcu' => 0],
```

### Akses EHR

```php
['profesi_id' => 1, 'form_id' => 25, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 2, 'form_id' => 25, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
```

### Validasi

```php
'urine'          => 'required|numeric|min:0',
'drain'          => 'nullable|numeric|min:0',
'ngt'            => 'nullable|numeric|min:0',
'wsd'            => 'nullable|numeric|min:0',
'output_lainnya' => 'nullable|numeric|min:0',
'frekuensi_bab'  => 'nullable|integer|min:0|max:20',
'frekuensi_bak'  => 'nullable|integer|min:0|max:30',
'karakter_bab'   => 'nullable|array',
'karakter_bak'   => 'nullable|array',
'catatan'        => 'nullable|string|max:1000',
```

### Cetak

Tidak wajib.

---

## 6. Monitoring Cairan (form 26)

### Rasional

Form **time-series**: satu baris `emr` per ronde observasi (lihat §3.5). Legacy
`monitoring_cairan.php` menghitung kebutuhan cairan dari berat badan:

```php
// monitoring_cairan.php:174-186
$bb  = $resbb2['value'];          // berat badan dari EMR terakhir form 6/30 (objek 10)
$bb1 = $bb * 40;                  // maintenance minimum
$bb2 = $bb * 50;                  // maintenance maksimum
// ditampilkan: "BB : 70 Kg  |  Kebutuhan Cairan : 2800 - 3500 cc"
```

Lalu menampilkan tabel intake (infus / syringe pump / lainnya) dan output, dengan
validasi "volume masuk tidak boleh melebihi sisa infus" via JS `hitung()`.

NusaMedika: tabung moniker tersebut, ditambah kolom **balance** dan **flag
abnormal** (§3.3).

### Struktur Field

Satu baris = satu ronde. Jam diambil dari `waktu_observasi` (objek 156).

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `tanggal_monitoring` | 155 | Tanggal Observasi | date | ya | Reuse objek 155 |
| `waktu_monitoring` | 156 | Waktu Observasi | time (`H:i`) | ya | Reuse objek 156 — jam ronde |
| `berat_badan` | 8 | Berat Badan | number | ya | Reuse objek 8 — sumber kebutuhan cairan |
| `suhu` | 11 | Suhu | number | tidak | Reuse objek 11 — untuk IWL |
| `nama_infus_1..6` | 292 | Nama Cairan Infus | select (`barang`) | tidak | **Share dengan form 24** |
| `dosis_infus_1..6` | 308 | Dosis Infus (cc) | number | ya (baris dipakai) | Legacy `dosis_infusC{n}` |
| `volume_infus_1..6` | 293 | Volume Masukkan Infus (cc) | number | tidak | Share dengan form 24 |
| `sisa_infus_1..6` | 294 | Sisa Infus (cc) | number | tidak | **Turunan** = dosis − volume |
| `ivfd_1..6` | 309 | IVFD (cc/menit) | number | tidak | Share dengan form 24 |
| `intake_oral` | 295 | Intake Oral (cc) | number | tidak | Share dengan form 24 |
| `intake_spoel` | 296 | Intake Spoel (cc) | number | tidak | Share dengan form 24 |
| `intake_transfusi` | 297 | Intake Transfusi (cc) | number | tidak | Share dengan form 24 |
| `intake_lainnya` | 299 | Intake Lainnya (cc) | number | tidak | Share dengan form 24 |
| `total_intake` | 291 | Total Intake Cairan (cc) | readonly (server) | ya | **Turunan + flag abnormal** |
| `output_urine` | 300 | Output Urine (cc) | number | tidak | Share dengan form 25 |
| `output_drain` | 301 | Output Drain (cc) | number | tidak | Share |
| `output_ngt` | 302 | Output NGT (cc) | number | tidak | Share |
| `output_wsd` | 303 | Output WSD (cc) | number | tidak | Share |
| `output_iwl` | 304 | Output IWL (cc) | readonly (server) | ya | **Turunan** |
| `output_lainnya` | 305 | Output Lainnya (cc) | number | tidak | Share |
| `total_output` | 306 | Total Output Cairan (cc) | readonly (server) | ya | **Turunan + flag abnormal** |
| `kebutuhan_cairan` | 310 | Kebutuhan Cairan Harian (cc) | readonly (server) | ya | **Turunan** = BB × 40 s/d BB × 50 |
| `balance_cairan` | 311 | Balance Cairan (cc) | readonly (server) | ya | **Turunan + flag abnormal** |
| `total_intake_24jam` | 307 | Total Intake Cairan 24 Jam (cc) | readonly (server) | ya | **Turunan kumulatif** — jumlah seluruh ronde pada tanggal yang sama |
| `keterangan_monitoring` | 312 | Keterangan Monitoring Cairan | textarea | tidak | |

### Mapping

```php
26 => [
    'tanggal_monitoring'    => 155,
    'waktu_monitoring'      => 156,
    'berat_badan'           => 8,
    'suhu'                  => 11,

    'nama_infus_1'   => 292, 'dosis_infus_1' => 308, 'volume_infus_1' => 293,
    'nama_infus_2'   => 292, 'dosis_infus_2' => 308, 'volume_infus_2' => 293,
    'nama_infus_3'   => 292, 'dosis_infus_3' => 308, 'volume_infus_3' => 293,
    'nama_infus_4'   => 292, 'dosis_infus_4' => 308, 'volume_infus_4' => 293,
    'nama_infus_5'   => 292, 'dosis_infus_5' => 308, 'volume_infus_5' => 293,
    'nama_infus_6'   => 292, 'dosis_infus_6' => 308, 'volume_infus_6' => 293,
    'sisa_infus_1'   => 294, 'sisa_infus_2' => 294, 'sisa_infus_3' => 294,
    'sisa_infus_4'   => 294, 'sisa_infus_5' => 294, 'sisa_infus_6' => 294,
    'ivfd_1'         => 309, 'ivfd_2' => 309, 'ivfd_3' => 309,
    'ivfd_4'         => 309, 'ivfd_5' => 309, 'ivfd_6' => 309,

    'intake_oral'      => 295,
    'intake_spoel'     => 296,
    'intake_transfusi' => 297,
    'intake_lainnya'   => 299,
    'total_intake'     => 291,

    'output_urine'    => 300,
    'output_drain'    => 301,
    'output_ngt'      => 302,
    'output_wsd'      => 303,
    'output_iwl'      => 304,
    'output_lainnya'  => 305,
    'total_output'    => 306,

    'kebutuhan_cairan'    => 310,
    'balance_cairan'       => 311,
    'total_intake_24jam'   => 307,
    'keterangan_monitoring'=> 312,
],
```

> Object reuse lintas-form disengaja agar laporan balance dapat menjumlahkan dari
> sumber mana pun. `EmrHelper::objekVariabels()` sudah berbasis `form_id`, jadi
> tidak ada kebocoran antar form.

### Perhitungan Server-Side

Semua nilai `readonly` diabaikan dari browser:

```php
// MonitoringCairanController::filteredData()
$kebutuhan = $this->kebutuhanCairan($data['berat_badan'] ?? null);
// ['min' => bb*40, 'maks' => bb*50, 'teks' => '2800 - 3500 cc'] atau null

$totalIntake = 0.0;
for ($i = 1; $i <= 6; $i++) {
    $dosis  = (float) ($data['dosis_infus_'.$i]  ?? 0);
    $volume = (float) ($data['volume_infus_'.$i] ?? 0);

    // Volume tidak boleh melebihi sisa infus.
    $volume = min($volume, $dosis > 0 ? $dosis : $volume);
    $data['volume_infus_'.$i] = $volume;
    $data['sisa_infus_'.$i]   = $dosis > 0 ? round($dosis - $volume, 0) : null;

    $totalIntake += $volume;
}
foreach (['intake_oral','intake_spoel','intake_transfusi','intake_lainnya'] as $k) {
    $totalIntake += (float) ($data[$k] ?? 0);
}
$data['total_intake'] = round($totalIntake, 0);

$bb  = (float) ($data['berat_badan'] ?? 0);
$suhu = (float) ($data['suhu'] ?? 0);
$tr   = $suhu <= 0 ? 1.0 : ($suhu > 39.0 ? 2.0 : ($suhu >= 38.0 ? 1.5 : 1.0));
$data['output_iwl'] = $bb > 0 ? round((25 + $bb) * $tr, 0) : 0;

$totalOutput = 0.0;
foreach (['output_urine','output_drain','output_ngt','output_wsd','output_lainnya'] as $k) {
    $totalOutput += (float) ($data[$k] ?? 0);
}
$totalOutput += (float) $data['output_iwl'];
$data['total_output'] = round($totalOutput, 0);

$data['balance_cairan'] = round($data['total_intake'] - $data['total_output'], 0);
$data['kebutuhan_cairan'] = $kebutuhan['teks'] ?? '';   // disimpan sebagai teks "2800 - 3500 cc"

// Kumulatif 24 jam: jumlah seluruh ronde form 26 pada tanggal yang sama,
// di luar emr yang sedang disimpan (untuk kasus update).
$data['total_intake_24jam'] = $this->akumulasiIntakeHariIni(
    (int) $registrasi_detail_id,
    $data['tanggal_monitoring'],
    $data['total_intake']
);
```

`kebutuhan_cairan` disimpan sebagai **teks**, bukan angka, karena rentang
(min–maks) tidak bisa direpresentasikan sebagai satu angka tanpa kehilangan
informasi. Ambang flag abnormal tetap memakai angka (`$kebutuhan['maks']`).

### Validasi

```php
'tanggal_monitoring'  => 'required|date',
'waktu_monitoring'    => 'required|date_format:H:i',
'berat_badan'         => 'required|numeric|between:1,400',
'suhu'                => 'nullable|numeric|between:30,45',
'dosis_infus_1'       => 'nullable|required_with:volume_infus_1|numeric|min:0',
'volume_infus_1'      => 'nullable|numeric|min:0',
'ivfd_1'              => 'nullable|numeric|min:0',
'intake_oral'         => 'nullable|numeric|min:0',
'intake_spoel'        => 'nullable|numeric|min:0',
'intake_transfusi'    => 'nullable|numeric|min:0',
'intake_lainnya'      => 'nullable|numeric|min:0',
'output_urine'        => 'nullable|numeric|min:0',
'output_drain'        => 'nullable|numeric|min:0',
'output_ngt'          => 'nullable|numeric|min:0',
'output_wsd'          => 'nullable|numeric|min:0',
'output_lainnya'      => 'nullable|numeric|min:0',
'keterangan_monitoring' => 'nullable|string|max:1000',
// kolom 2..6 nullable dengan aturan identik
```

`total_intake_24jam` **tidak divalidasi dari browser** — ia adalah turunan
akumulatif dan selalu ditulis ulang server.

Validasi tambahan `filteredData()`:

```php
// Buang baris infus yatim (nama_infus_N kosong -> semua kolom N ikut dibuang).
for ($i = 1; $i <= 6; $i++) {
    if (blank($data['nama_infus_'.$i])) {
        unset($data['dosis_infus_'.$i], $data['volume_infus_'.$i], $data['sisa_infus_'.$i], $data['ivfd_'.$i]);
    }
}
```

### Dashboard & Form Master

```php
['dashboard_menu_id' => 9, 'nama_menu' => 'Monitoring'],
['dashboard_menu_sub_id' => 94, 'dashboard_menu_id' => 9, 'nama_sub_menu' => 'Monitoring Cairan'],
['form_id' => 26, 'nama_form' => 'Monitoring Cairan', 'slug' => 'monitoring_cairan', 'id_dash_menu' => '9.94', 'ri' => 1, 'rj' => 0, 'igd' => 0, 'mcu' => 0],
```

### Akses EHR

```php
['profesi_id' => 1, 'form_id' => 26, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 2, 'form_id' => 26, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
```

### Tampilan Indeks Ronde di Blade

```blade
@php
    $rondeList = EmrHelper::rondeMonitoring((int) $form_id, (int) $registrasi_detail->registrasi_detail_id);
@endphp

<table class="w-full text-sm">
    <thead>
        <tr>
            <th>Ronde</th><th>Jam</th><th>Berat Badan</th>
            <th>Intake</th><th>Output</th><th>Balance</th><th>Flag</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($rondeList as $r)
            @php($v = EmrHelper::emrDetailByVariabel($r->emr_id))
            <tr>
                <td>{{ $r->ronde }}</td>
                <td>{{ \Carbon\Carbon::parse($r->tgl_jam)->format('H:i') }}</td>
                <td>{{ $v['berat_badan'] }} Kg</td>
                <td>{{ $v['total_intake'] }} cc</td>
                <td>{{ $v['total_output'] }} cc</td>
                <td class="{{ ($v['balance_cairan'] ?? 0) < 0 ? 'text-red-600 font-bold' : '' }}">
                    {{ $v['balance_cairan'] }} cc
                </td>
                <td>
                    @if (! empty(EmrHelper::emrFlags($r->emr_id)))
                        <span class="text-[10px] font-bold text-red-700">ABNORMAL</span>
                    @else
                        <span class="text-[10px] text-emerald-700">Normal</span>
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
```

### Cetak

`print.blade.php` **wajib** — lembar balance harian ditandatangani DPJP saat
kontrol harian.

---

## 7. Kesadaran & Pemberian Oksigen (form 28)

### Rasional

Legacy `oksigen.php` memuat **Glasgow Coma Scale lengkap + AVPU + DPO + pemberian
O2**. Objek legacy-nya: `gcs_eye` (238), `gcs_motorik` (239), `gcs_verbal` (240),
`gcs_score` (241), `dalam_pengaruh_obat` (1287), `beri` (157), `cara` (158),
`ett` (159), `saturasi` (28).

NusaMedika **sudah punya objek GCS** (54 Eye, 55 Motorik, 56 Verbal, 57 Score) dan
objek oksigen (18 Pemberian Oksigen, 19 Cara Pemberian, 20 ETT, 15 Saturasi,
157 Flow Rate) yang dipakai form 3, 4, dan 13. Karena itu **form 28 tidak
menambah objek baru** — seluruhnya reuse.

AVPU yang dihitung dari GCS total (logika legacy `oksigen.php:49-71`) disimpan pada
objek 51 (Kesadaran) sebagai nilai turunan.

### Struktur Field

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `tanggal_kesadaran` | 155 | Tanggal Observasi | date | ya | Reuse |
| `waktu_kesadaran` | 156 | Waktu Observasi | time | ya | Reuse |
| `gcs_e` | 54 | GCS Eye | radio 1–4 | ya | Reuse |
| `gcs_m` | 55 | GCS Motorik | radio 1–6 | ya | Reuse |
| `gcs_v` | 56 | GCS Verbal | radio 1–5 | ya | Reuse |
| `gcs_jumlah` | 57 | GCS Score | readonly (server) | ya | **Turunan**, reuse objek 57 |
| `kesadaran` | 51 | Kesadaran | readonly (server) | ya | **Turunan** dari `gcs_jumlah` |
| `dpo` | 59 | DPO | checkbox | tidak | Reuse — Dalam Pengaruh Obat |
| `pemberian_o2` | 18 | Pemberian Oksigen | select (Air/Oksigen) | ya | Reuse — parameter EWS |
| `flow_rate` | 157 | Flow Rate Oksigen | number | ya bila Oksigen | L/menit; neonatal: `< 2` / `> 2` |
| `cara_oksigen` | 19 | Cara Pemberian Oksigen | select | ya bila Oksigen | Reuse |
| `ett` | 20 | ETT | radio (Ya/Tidak) | ya | Reuse |
| `persentase_oksigen` | 20 | ETT | number | tidak | Legacy: label "Oksigen Persen" (%), reuse objek 20 |
| `saturasi` | 15 | Saturasi Oksigen | number | ya | Reuse |
| `keterangan` | 77 | Keterangan | textarea | tidak | Reuse |

### Kriteria Penilaian GCS (rekap dari legacy `oksigen.php`)

**Eye (E)** — objek 54:

| Skor | Deskripsi |
|---|---|
| 4 | Buka mata spontan |
| 3 | Buka mata bila dirangsang suara |
| 2 | Buka mata bila dirangsang nyeri |
| 1 | Tidak bisa buka mata dengan dirangsang apa pun |

**Motorik (M)** — objek 55:

| Skor | Deskripsi |
|---|---|
| 6 | Mengikuti perintah |
| 5 | Mengetahui tempat rangsang nyeri (localisasi nyeri) |
| 4 | Hanya menarik bagian tubuh bila dirangsang |
| 3 | Timbul fleksi abnormal bila diberi rangsang nyeri |
| 2 | Timbul ekstensi abnormal bila diberi rangsang nyeri |
| 1 | Tidak ada gerakan dengan rangsang apa pun |

**Verbal (V)** — objek 56:

| Skor | Deskripsi |
|---|---|
| 5 | Komunikasi verbal baik, jawaban baik |
| 4 | Bingung, disorientasi waktu, orang, dan tempat |
| 3 | Dengan rangsangan hanya ada kata-kata |
| 2 | Dengan rangsangan hanya keluar suara |
| 1 | Tidak ada respons verbal |

### Pemetaan GCS → AVPU (logika legacy verbatim)

| Total GCS | Kesadaran | AVPU |
|---|---|---|
| 3 | Coma | Unresponsive |
| 4–6 | Soporus | Pain |
| 7–9 | Somnolen | Pain |
| 10–11 | Delirium | Verbal |
| 12–14 | Apatis | Verbal |
| 15 | Compos Mentis | Alert |

```php
// KesadaranOksigenController::filteredData()
$e = (int) ($data['gcs_e'] ?? 0); $m = (int) ($data['gcs_m'] ?? 0); $v = (int) ($data['gcs_v'] ?? 0);
$total = ($e && $m && $v) ? $e + $m + $v : null;

$data['gcs_jumlah'] = $total;
$data['kesadaran']   = match (true) {
    $total === null           => null,
    $total === 3              => 'Coma',
    $total >= 4 && $total <= 6 => 'Soporus',
    $total >= 7 && $total <= 9 => 'Somnolen',
    $total >= 10 && $total <= 11 => 'Delirium',
    $total >= 12 && $total <= 14 => 'Apatis',
    $total === 15             => 'Compos Mentis',
    default                   => null,
};
```

`SelectOption` key `kesadaran` **harus** memuat seluruh nilai di atas. Saat ini
key tersebut sudah ada (dipakai form 3/4/13) — konfirmasi ulang daftarnya saat
implementasi dan tambahkan `Delirium` bila belum ada.

### Cara Pemberian O2 (opsi legacy `oksigen.php:13`)

```php
'cara_pemberian_o2' => [
    'Nasal Canul', 'Non Rebreathing Mask', 'Rebreathing Mask',
    'Ventury', 'CPAP', 'Voltran', 'HFNC', 'Ventilator',
    // 'Room Air' ditambahkan bila klien tidak memakai oksigen sentral
],
```

### Dashboard & Form Master

```php
['dashboard_menu_sub_id' => 95, 'dashboard_menu_id' => 9, 'nama_sub_menu' => 'Kesadaran Oksigen'],
['form_id' => 28, 'nama_form' => 'Kesadaran & Pemberian O2', 'slug' => 'kesadaran_oksigen', 'id_dash_menu' => '9.95', 'ri' => 1, 'rj' => 0, 'igd' => 0, 'mcu' => 0],
```

### Akses EHR

```php
['profesi_id' => 1, 'form_id' => 28, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 2, 'form_id' => 28, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
```

### Validasi

```php
'tanggal_kesadaran' => 'required|date',
'waktu_kesadaran'   => 'required|date_format:H:i',
'gcs_e'  => 'required|integer|between:1,4',
'gcs_m'  => 'required|integer|between:1,6',
'gcs_v'  => 'required|integer|between:1,5',
'pemberian_o2' => 'required|in:Air,Oksigen',
'flow_rate'     => 'nullable|required_if:pemberian_o2,Oksigen|numeric|min:0|max:60',
'cara_oksigen'  => 'nullable|required_if:pemberian_o2,Oksigen|string|max:50',
'ett'           => 'required|in:Ya,Tidak',
'persentase_oksigen' => 'nullable|numeric|between:21,100',
'saturasi'      => 'required|integer|between:50,100',
'keterangan'    => 'nullable|string|max:1000',
```

### Cetak

Tidak wajib (data sudah tercetak di Tanda Vital form 13).

---

## 8. Implementasi

### Seeder (`database/seeders/EmrMasterSeeder.php`)

- [ ] `$menus` — tambah `['dashboard_menu_id' => 9, 'nama_menu' => 'Monitoring']`
- [ ] `$subMenus` — tambah `93 Balance Cairan (menu 2)`, `94 Monitoring Cairan (menu 9)`,
      `95 Kesadaran Oksigen (menu 9)`
- [ ] `$extras` — tambah `46 Intake`, `47 Output`
- [ ] `$forms` — tambah 4 baris (24, 25, 26, 28)
- [ ] `$objeks` — tambah 22 baris (291–312)
- [ ] `$mapping` — tambah 4 blok; blok 24 dan 26 **dihasilkan loop** 1..6
- [ ] `EmrHelper::backfillObjekId(24..28)`
- [ ] `$akses` — tambah 8 baris

### Helper

- [ ] `app/Helpers/CairanHelper.php` — `kebutuhanCairan(?float $bb)`, `hitungIWL($bb,$suhu)`,
      `totalIntake(array $d)`, `totalOutput(array $d)`, `balance(array $d)`,
      `flags(array $d): array`

### Perubahan `EmrHelper` (wajib, backward compatible)

- [ ] `insert()` & `update()` tambah parameter `array $flags = []`
- [ ] `storeDetails()` isi kolom `flag_abnormal` dari `$flags[$variabel] ?? null`
- [ ] Helper baru `emrFlags(int $emrId): array` dan `rondeMonitoring(int $formId, int $regDetId): array`
- [ ] `getHistoryForForm()` dikembalikan `flags` agar `x-emr-history-table` bisa
      menampilkan badge abnormal
- [ ] Tambah test unit: form 1–15 tetap menyimpan `flag_abnormal = NULL`

### Controller

- [ ] `app/Http/Controllers/EMR/Intake/IntakeController.php`
- [ ] `app/Http/Controllers/EMR/Output/OutputController.php`
- [ ] `app/Http/Controllers/EMR/MonitoringCairan/MonitoringCairanController.php`
- [ ] `app/Http/Controllers/EMR/KesadaranOksigen/KesadaranOksigenController.php`

> ⚠️ `Str::studly('intake')` = `Intake`, `Str::studly('output')` = `Output`,
> `Str::studly('monitoring_cairan')` = `MonitoringCairan`,
> `Str::studly('kesadaran_oksigen')` = `KesadaranOksigen`.

### View

- [ ] `resources/views/moduls/EMR/{Intake,Output,MonitoringCairan,KesadaranOksigen}/index.blade.php`
- [ ] `resources/views/moduls/EMR/MonitoringCairan/print.blade.php` (lembar balance)
- [ ] Partial baru `PartialForm/_monitoring_cairan_ronde.blade.php` (tabel ronde)
- [ ] Partial baru `PartialForm/_baris_infus.blade.php` (baris infus dinamis)

### SelectOption

- [ ] Tambah/verifikasi key: `cara_pemberian_o2`, `karakter_bab`, `karakter_bak`

### Komponen

- [ ] `x-emr-history-table` — tambah prop `showFlag` (default false) supaya tidak
      mengubah tampilan form 1–15

### Dokumentasi

- [ ] `AGENTS.md` — entri form 24, 25, 26, 28 + catatan `flag_abnormal` & ronde
- [ ] `docs/ANALISIS_KEKURANGAN_FORM.md` §6

### Verifikasi

```bash
docker compose up -d db
docker compose exec app php artisan migrate:fresh --seed
docker compose exec app ./vendor/bin/pint --dirty
docker compose exec app php artisan test
```

Uji khusus: simpan form 26 tiga kali dalam satu hari → pastikan 3 baris `emr`,
3 badge ronde, dan `flag_abnormal` pada baris yang benar.

---

## 9. Catatan & Risiko

| # | Risiko | Dampak | Mitigasi |
|---|---|---|---|
| R1 | **`emr_detail` tidak punya kolom ronde.** | Rapikan grid monitoring cystic. | Ronde = satu baris `emr`. Indeks dihitung saat baca lewat `EmrHelper::rondeMonitoring()`. Tidak perlu migration. Bila nanti butuh kolom eksplisit (mis. integrasi SATU SEHAT), tambahkan `round_index` lewat migration terpisah. |
| R2 | **`EmrHelper::storeDetails()` belum mengisi `flag_abnormal`.** | Mekanisme abnormal tidak berjalan tanpa perubahan helper. | Tambahkan parameter `$flags = []` dengan default kosong — perilaku form 1–15 tidak berubah. Wajib ada test regresi. |
| R3 | **Banyak variabel berbagi objek** (form 25: `frekuensi_bab`/`karakter_bab`/`frekuensi_bak`/`karakter_bak` → objek 116). `emrDetailByObjek()` memakai `pluck('value','objek_id')` → **salah satu tertimpa**. | Laporan berbasis objek kehilangan 3 dari 4 nilai. | Jangan pakai `emrDetailByObjek()` untuk form 25. Bila laporan butuh, tambah objek baru setelah rentang 312 (di luar alokasi dokumen ini) atau gunakan `emrDetailByVariabel()` + map manual. |
| R4 | **Kolom baris infus harus bersuffix** (`nama_infus_1..6`, dst). | Baris kedua dst. tertimpa saat form dibuka lagi. | mapping digenerate loop; blade memakai `data-item` untuk indeks, bukan urutan array. |
| R5 | **Objek `berat_badan` (8) juga dipakai form 3, 4, 13, 26.** Bila form 26 diisi baris yang bukan weight-loss monitoring (mis. strik uri IWL per 24 jam), angka akan kacau. | Kebutuhan cairan salah hitung. | Pastikan form 26 hanya dipakai pada pasien rawat inap dengan pencatatan stripped harian (bukan per-shift). Untuk IWL per shift, buat form terpisah. |
| R6 | **Sharing objek antar form** (291–306 dipakai form 24/25 dan 26). | Laporan menjumlahkan dobel bila tidak difilter `emr_id`. | Query laporan **wajib** difilter `form_id` + `registrasi_detail_id` + tanggal. Jangan menjumlahkan seluruh `emr_detail` dengan `objek_id IN (291..312)`. |
| R7 | **Volume infus melebihi sisa.** Legacy hanya dicek di JS (`hitung()`). | Over-infus. | Validasi server-side `min($volume, $dosis)` di `filteredData()` (lihat §6) + `flag_abnormal` pada `sisa_infus` negatif. |
| R8 | **`kebutuhan_cairan` disimpan sebagai teks** ("2800 - 3500 cc"). | Tidak bisa dijumlahkan aritmetis. | Ambil batas angka dari `$kebutuhan['maks']` saat menghitung flag; parse teks hanya untuk tampilan. Bila laporan butuh angka, tambahkan objek terpisah `kebutuhan_cairan_maks` (numeric). |
| R9 | **Warna badge dari `flag_abnormal` konsisten dengan Lab/Rad.** | Two different meanings for same column. | Dokumentasikan konvensi global di `AGENTS.md`: `1 = ABNORMAL`, `0 = Normal`, `NULL = tidak dinilai`. Form lain yang punya makna berbeda **wajib** memakai kolom sendiri. |
| R10 | **`input_time`/`tgl_jam` menulis dari PHP.** | Selisih 8 jam dengan DB container (UTC). | `EmrHelper::insert()` sudah memakai `now()`. Jangan pernah `NOW()` di query manual. |
| R11 | **Form 28 tidak menambah objek — semua reuse (15, 18, 19, 20, 51, 54–57, 59, 157).** | Tingkat reuse objek tinggi di form ini. | `emrDetailByObjek()` pada form 28 juga bermasalah bila dipakai; andalkan `emrDetailByVariabel()`. |
| R12 | **`flow_rate` untuk neonatal.** Legacy memakai opsi `< 2` / `> 2`, sedangkan kolom ini `numeric`. | Tidak bisa menyimpan simbol `< 2`. | Validasi server mengizinkan string `< 2` / `> 2` bila usia < 1 tahun, atau tambahkan kolom terpisah `flow_rate_nekatal`. Rekomendasi: field `flow_rate` numerik + checkbox `flow_rate_kategori` (objek NULL). |
| R13 | **Halaman riwayat `x-emr-history-table` perlu query tambahan per baris** untuk `emrFlags()`. | N+1 query, lambat bila riwayat panjang. | Ambil flag **bulk**: `EmrHelper::emrFlagsMulti(array $emrIds)`. Sudah tersedia pola di `getHistoryForForm()` yang sudah mengambil detail sekaligus. |
| R14 | **Menu 9 "Monitoring" dipakai bersama `KONSEP_NYERI.md` (sub 113, 114).** | Id bentrok bila salah seed. | Sudah dikunci: sub 94–95 milik dokumen ini, sub 113–114 milik `KONSEP_NYERI.md` (`ALOKASI_ID_GLOBAL.md` §3). Jangan menambah sub baru di luar band 93–112 tanpa verifikasi. |