# TASK FORM EMR — NusaMedika

**Status dokumen:** sumber kebenaran progress implementasi form EMR.
**Terakhir diperbarui:** 2026-10-11
**Acuan:** `docs/ALOKASI_ID_GLOBAL.md` (ID), `docs/PANDUAN_IMPLEMENTASI_FORM_EMR.md` (cara), `docs/KONSEP_*.md` (spesifikasi per form).

> **WAJIB baca file ini sebelum menambah form EMR baru.** Satu form = satu baris task.
> Status `⬜` → `🔄` → `✅` diperbarui manual di sini, **bukan** dari kode.

---

## 1. Cara Pakai (untuk Agent)

### 1.1 Alur klaim

```
1. Baca docs/ALOKASI_ID_GLOBAL.md      → form_id, slug, id_dash_menu, objek_id (WAJIB dari sini)
2. Baca docs/PANDUAN_IMPLEMENTASI_FORM_EMR.md  → pola seeder/controller/blade
3. Baca docs/<KONSEP domain>.md §form  → struktur field, mapping, akses, validasi
4. Baca §3 Definisi Selesai di bawah ini
5. Cek baris task: kalau sudah 🔄 oleh agent lain, pilih form lain
6. Ubah Status → 🔄 dan isi kolom Owner (nama agent/sesi)
7. Kerjakan (seeder → controller → blade → partial/print)
8. Uji manual: create → edit → delete → view
9. docker compose exec app ./vendor/bin/pint --dirty
10. Ubah Status → ✅, isi Owner, tambahkan entri di AGENTS.md
```

### 1.2 Aturan klaim

- **Selalu baca ulang file ini tepat sebelum klaim** — beberapa agent bisa jalan bersamaan dan menimpa baris yang sama.
- Satu agent idealnya mengerjakan **1 form per sesi** (setiap form = 1 seeder block + 1 controller + 1 blade). Kalau lebih dari 3 form, pilih satu domain saja.
- **Jangan mengubah ID yang sudah dialokasikan.** Semua `form_id`, `objek_id`, `dashboard_menu_sub_id`, `dashboard_menu_sub_extra_id`, `profesi_id`, dan `slug` dikunci di `ALOKASI_ID_GLOBAL.md`.
- Kalau menemukan bentrok ID atau form yang salah design, **jangan diam-diam menomori ulang** — laporkan dan perbarui ledger lebih dulu.
- Setelah 2–3 form selesai, perbarui tabel ringkasan di §4.

### 1.3 Task prasyarat (`PRE-*`)

Sebagian form tidak bisa dikerjakan sebelum prasyaratnya ada (tabel order, master, helper). Kerjakan `PRE-*` lebih dulu bila form yang diklaim butuh.

---

## 2. Legenda Status

| Status | Arti | Tindakan
|---|---|---|
| ⬜ | Belum dikerjakan | Klaim: ubah ke 🔄 + isi Owner |
| 🔄 | Sedang dikerjakan | Sudah di-claim; jangan diambil agent lain |
| ✅ | Selesai | Semua butir §3 terpenuhi + entri AGENTS.md |
| ⛔ | Tertunda | Butuh keputusan klinis / persetujuan manusia — lihat §13 |
| ⚠️ | Terceceng | Terdaftar di DB tapi tidak berfungsi — lihat §6 |

---

## 3. Definisi Selesai (DoD)

Sebuah task berstatus ✅ bila **SEMUA** butir berikut terpenuhi:

- [ ] `form` row baru di `EmrMasterSeeder::$forms` sesuai ledger (slug, `id_dash_menu`, flag ri/rj/igd/mcu)
- [ ] `dashboard_menu_sub` (+ `sub_extra` bila perlu) ditambahkan dan `id_dash_menu` **dihitung ulang** dari PK yang benar
- [ ] `objek` baru ditambahkan pada band objek dokumen ini (bukan objek ≤ 177 yang di-deklarasikan ulang)
- [ ] `$mapping[form_id]` ditulis lengkap — semua `variabel` **unik** dalam form (field berulang wajib sufiks `_1`, `_2`, …)
- [ ] `akses_ehr` ada untuk tiap profesi yang boleh mengisi — **tanpa ini form tidak muncul di dashboard & selalu 403**
- [ ] `EmrHelper::backfillObjekId(N)` dipanggil
- [ ] Folder controller & view == `Str::studly($slug)`
- [ ] Controller punya `index/store/update/destroy` + gate `AksesEhr::can()` di keempatnya
- [ ] `filteredData()` memakai `array_intersect_key` — tidak ada data gateway lolos
- [ ] Field computed dihitung ulang di server; `*_nama` snapshot diisi dari master bukan dari browser
- [ ] View memakai `old('x', $emr_data['x'] ?? '')`, ada `@error` tiap field, dan `<fieldset {{ $isView ? 'disabled' : '' }}>`
- [ ] Uji manual lulus: create → edit → delete → view
- [ ] `./vendor/bin/pint --dirty` dijalankan
- [ ] Entri baru di `AGENTS.md`
- [ ] Baris task di file ini diubah ke ✅

---

## 4. Ringkasan Progress

| Fase | Lingkup | Total | ⬜ | 🔄 | ✅ | ⛔ | Progres |
|---|---|---|---|---|---|---|---|
| **P1** | Catatan Medis Lanjutan | 7 | 0 | 0 | 7 | 0 | **100%** |
| **P2** | Asuhan Keperawatan | 23 | 0 | 0 | 23 | 0 | **100%** |
| **P3** | Gizi, Farmasi & Penunjang | 13 | 0 | 0 | 12 | 1 | **92%** |
| **P4** | Kebidanan & MCU | 26 | 0 | 0 | 25 | 1 | **96%** |
| **P5** | Bedah, Anestesi & ICU | 23 | 0 | 0 | 20 | 3 | **87%** |
| **P6** | Onkologi, Psikososial & Formulir | 22 | 22 | 0 | 0 | 0 | 0% |
| **Total** | 6 fase | **114** | **22** | 0 | **87** | **5** | **76%** |



> Angka di tabel ini **dipertahankan manual**. Saat agent menutup task, perbarui baris fase yang sesuai.

---

## 5. Task Prasyarat (`T00`)

| Task | Prasyarat | Dibutuhkan oleh | Status | Owner |
|---|---|---|---|---|
| `PRE-01` | Seed profesi baru di `MasterPegawaiSeeder`: 14 Kerohanian, 15 Patolog Anatomi, 16 Dokter Intensivis, 17 Dokter Anestesi, 18 Perawat Hemodialisa | form 50–53, 85–90, 99, 102–104, 112–115 | ✅ |  |
| `PRE-02` | Migration `order_patologi` + `order_patologi_detail` (dual-driver) | form 49 | ✅ |  |
| `PRE-03` | Migration `order_mikrobiologi` + `order_mikrobiologi_detail` (dual-driver) | form 50 | ✅ |  |
| `PRE-04` | Migration `order_poct` + `order_poct_detail` (dual-driver) | form 51 | ✅ |  |
| `PRE-05` | Tabel + CRUD + seeder `master_diet` dan `master_diagnosa_diet` | form 43 | ✅ |  |
| `PRE-06` | Migration `form_blanko`, `form_blanko_field`, `form_blanko_pengisian` + model | form 129 | ⬜ |  |
| `PRE-07` | `EmrHelper`: isi kolom `emr_detail.flag_abnormal` saat simpan + helper baca ronde (`emrFlags()`, `rondeMonitoring()`) | semua form time-series | ⬜ |  |
| `PRE-08` | Helper skoring (pola `RisikoJatuhHelper`): `NyeriHelper`, `DekubitusHelper`, `ApgarHelper`, `DownScoreHelper`, `EwsHelper` | form 27, 32, 33, 36, 66, 67 | ⬜ |  |
| `PRE-09` | Helper domain: `CairanHelper`, `IcuHelper`, `HemodialisaHelper`, `GiziHelper`, `FarmasiHelper`, `McuHelper`, `McuSpesialistikHelper`, `VisumHelper`, `SurveilansHelper`, `RekonsiliasiHelper`, `ChecklistKeselamatanHelper`, `SkorPemulihanHelper`, `AsesmenKeperawatanHelper`, `KesimpulanHelper`, `DiagnosisHelper`, `SuratHelper` | 30+ form | ⬜ |  |
| `PRE-10` | Tambah key enum baru di `App\Helpers\SelectOption::all()` (kriteria ICU, skala, alasan, jenis kerohanian, dll) | semua form | ⬜ |  |
| `PRE-11` | Partial reusable di `resources/views/moduls/EMR/PartialForm/` (harian, triase, Assesmen Keperawatan, dll) | 15+ form | ⬜ |  |
| `PRE-12` | Bersihkan sisa `env('FORM_ID_*')` / `env('OBJEK_ID_*')` di `ListPasienRanapController` → `EmrHelper` | konsistensi global | ⬜ |  |

---

## 6. Form yang Sudah Ada (form 1–15)

| form_id | Nama Form | `slug` | Status | Catatan |
|---|---|---|---|---|
| 1 | Catatan Awal Medis | `catatan_awal_medis` | ⚠️ | **Terceceng** — `id_dash_menu = NULL`, tanpa controller & view. Tidak pernah muncul di dashboard; URL langsung render `Unsupported.blade.php`. inherited ~15 mapping + `akses_ehr` ada tapi tak berguna. **Putuskan: selesaikan atau soft-delete.** |
| 2 | SOAP / CPPT | `soap` | ✅ | + `print.blade.php`, `CetakSoap.blade.php` |
| 3 | Pengkajian Awal Keperawatan | `pengkajian_awal_keperawatan` | ✅ | + partial risiko jatuh (HDS/MFS/Sydney/TUG) |
| 4 | Pengkajian Harian Keperawatan | `pengkajian_harian_keperawatan` | ✅ |  |
| 5 | Order Resep | `order_resep` | ✅ |  |
| 6 | Order Laboratorium | `laboratorium` | ✅ | pola acuan untuk PRE-02/03/04 |
| 7 | Order Radiologi | `radiologi` | ✅ | pola acuan untuk PRE-02/03/04 |
| 8 | Konsultasi | `konsultasi` | ✅ | multi-guna: Rehab Medik / Konsul / Rencana Kontrol |
| 9 | Implementasi Keperawatan | `implementasi_keperawatan` | ✅ | butuh Master Implementasi (sudah ada) |
| 10 | Tindakan Medis | `tindakan_medis` | ✅ | + `partials/opsiObat.blade.php`, `opsiBatch.blade.php` |
| 11 | Assesmen Awal Medis Rawat Jalan | `assesmen_awal_medis_rawat_jalan` | ✅ | KHUSUS rawat jalan |
| 12 | SBAR | `sbar` | ✅ |  |
| 13 | Tanda Vital | `tanda_vital` | ✅ | + 3 partial observasi harian |
| 14 | Bundle VAP | `bundle_vap` | ✅ | pola checklist `vap_1..10` |
| 15 | Alat Invasif | `alat_invasif` | ✅ | pola baris dinamis `alat_1..N` |

> Form 13–15 **belum** punya entri di `AGENTS.md` .. Source of truth untuk ketiganya tetap `EmrMasterSeeder`.

---

## 7. Fase P1 — Catatan Medis Lanjutan

| Task | Nama Form | `slug` | `id_dash_menu` | ri/rj/igd/mcu | Status | Owner | Dokumen Konsep | Folder Controller/View | Catatan / Prasyarat |
|---|---|---|---|---|---|---|---|---|---|
| `EMR-016` | Resume Medis | `resume_medis` | `1.13` | 1/0/0/0 | ✅ | P1 · selesai 2026-10-10 | KONSEP_CATATAN_MEDIS_LANJUTAN.md | `EMR/ResumeMedis/` · `moduls/EMR/ResumeMedis/` | — |
| `EMR-017` | Discharge Planning | `discharge_planning` | `6.33` | 1/1/1/0 | ✅ | P1 · selesai 2026-10-10 | KONSEP_DISCHARGE_PLANNING.md | `EMR/DischargePlanning/` · `moduls/EMR/DischargePlanning/` | — |
| `EMR-018` | Pemulangan Pasien | `pemulangan_pasien` | `6.34` | 1/0/0/0 | ✅ | P1 · selesai 2026-10-10 | KONSEP_DISCHARGE_PLANNING.md | `EMR/PemulanganPasien/` · `moduls/EMR/PemulanganPasien/` | — |
| `EMR-019` | Triage IGD | `triage_igd` | `7.53` | 0/0/1/0 | ✅ | P1 · selesai 2026-10-10 | KONSEP_TRIASE_IGD.md | `EMR/TriageIgd/` · `moduls/EMR/TriageIgd/` | — |
| `EMR-020` | Care Plan | `care_plan` | `1.14` | 1/1/1/0 | ✅ | P1 · selesai 2026-10-10 | KONSEP_CATATAN_MEDIS_LANJUTAN.md | `EMR/CarePlan/` · `moduls/EMR/CarePlan/` | — |
| `EMR-021` | DAR | `dar` | `1.15.6` | 1/0/1/0 | ✅ | P1 · selesai 2026-10-10 | KONSEP_CATATAN_MEDIS_LANJUTAN.md | `EMR/Dar/` · `moduls/EMR/Dar/` | — |
| `EMR-057` | Catatan Medis Visum | `catatan_medis_visum` | `1.15` | 0/0/1/0 | ✅ | P1 · selesai 2026-10-10 | KONSEP_CATATAN_MEDIS_LANJUTAN.md | `EMR/CatatanMedisVisum/` · `moduls/EMR/CatatanMedisVisum/` | — |

## 8. Fase P2 — Asuhan Keperawatan

| Task | Nama Form | `slug` | `id_dash_menu` | ri/rj/igd/mcu | Status | Owner | Dokumen Konsep | Folder Controller/View | Catatan / Prasyarat |
|---|---|---|---|---|---|---|---|---|---|
| `EMR-022` | Rencana Asuhan Keperawatan | `rencana_asuhan_keperawatan` | `2.73.26` | 1/0/0/0 | ✅ | P2 · selesai 2026-10-11 | KONSEP_ASUHAN_KEPERAWATAN_LANJUTAN.md | `EMR/RencanaAsuhanKeperawatan/` · `moduls/EMR/RencanaAsuhanKeperawatan/` | — |
| `EMR-023` | Issues Keperawatan | `issues_keperawatan` | `2.73.27` | 1/0/0/0 | ✅ | P2 · selesai 2026-10-11 | KONSEP_ASUHAN_KEPERAWATAN_LANJUTAN.md | `EMR/IssuesKeperawatan/` · `moduls/EMR/IssuesKeperawatan/` | — |
| `EMR-024` | Intake | `intake` | `2.93.46` | 1/0/0/0 | ✅ | P2 · selesai 2026-10-11 | KONSEP_CAIRAN_BALANCE.md | `EMR/Intake/` · `moduls/EMR/Intake/` | — |
| `EMR-025` | Output | `output` | `2.93.47` | 1/0/0/0 | ✅ | P2 · selesai 2026-10-11 | KONSEP_CAIRAN_BALANCE.md | `EMR/Output/` · `moduls/EMR/Output/` | — |
| `EMR-026` | Monitoring Cairan | `monitoring_cairan` | `9.94` | 1/0/0/0 | ✅ | P2 · selesai 2026-10-11 | KONSEP_CAIRAN_BALANCE.md | `EMR/MonitoringCairan/` · `moduls/EMR/MonitoringCairan/` | — |
| `EMR-027` | Penilaian Nyeri | `penilaian_nyeri` | `9.113.66` | 1/1/1/0 | ✅ | P2 · selesai 2026-10-11 | KONSEP_NYERI.md | `EMR/PenilaianNyeri/` · `moduls/EMR/PenilaianNyeri/` | — |
| `EMR-028` | Kesadaran & Pemberian O2 | `kesadaran_oksigen` | `9.95` | 1/0/0/0 | ✅ | P2 · selesai 2026-10-11 | KONSEP_CAIRAN_BALANCE.md | `EMR/KesadaranOksigen/` · `moduls/EMR/KesadaranOksigen/` | — |
| `EMR-029` | Handover / Serah Terima | `handover` | `2.74.28` | 1/0/0/0 | ✅ | P2 · selesai 2026-10-11 | KONSEP_ASUHAN_KEPERAWATAN_LANJUTAN.md | `EMR/Handover/` · `moduls/EMR/Handover/` | — |
| `EMR-030` | Monitoring & Evaluasi Risiko Jatuh | `monitoring_risiko_jatuh` | `8.133.86` | 1/0/0/0 | ✅ | P2 · selesai 2026-10-11 | KONSEP_RISIKO_JATUH_LANJUTAN.md | `EMR/MonitoringRisikoJatuh/` · `moduls/EMR/MonitoringRisikoJatuh/` | — |
| `EMR-031` | Asesmen Kebutuhan Edukasi | `asesmen_kebutuhan_edukasi` | `2.75.29` | 1/1/0/0 | ✅ | P2 · selesai 2026-10-11 | KONSEP_ASUHAN_KEPERAWATAN_LANJUTAN.md | `EMR/AsesmenKebutuhanEdukasi/` · `moduls/EMR/AsesmenKebutuhanEdukasi/` | — |
| `EMR-032` | Penilaian Risiko Dekubitus (Norton) | `penilaian_risiko_dekubitus` | `8.134.87` | 1/0/0/0 | ✅ | P2 · selesai 2026-10-11 | KONSEP_RISIKO_JATUH_LANJUTAN.md | `EMR/PenilaianRisikoDekubitus/` · `moduls/EMR/PenilaianRisikoDekubitus/` | — |
| `EMR-033` | Barthel Index | `barthel_index` | `8.134.88` | 1/0/0/0 | ✅ | P2 · selesai 2026-10-11 | KONSEP_RISIKO_JATUH_LANJUTAN.md | `EMR/BarthelIndex/` · `moduls/EMR/BarthelIndex/` | — |
| `EMR-034` | Asesmen Status Fungsional | `asesmen_status_fungsional` | `8.76.31` | 1/1/0/0 | ✅ | P2 · selesai 2026-10-11 | KONSEP_ASUHAN_KEPERAWATAN_LANJUTAN.md | `EMR/AsesmenStatusFungsional/` · `moduls/EMR/AsesmenStatusFungsional/` | — |
| `EMR-035` | Pengkajian Keperawatan Kritis | `pengkajian_keperawatan_kritis` | `2.75.30` | 1/0/1/0 | ✅ | P2 · selesai 2026-10-11 | KONSEP_ASUHAN_KEPERAWATAN_LANJUTAN.md | `EMR/PengkajianKeperawatanKritis/` · `moduls/EMR/PengkajianKeperawatanKritis/` | — |
| `EMR-036` | Metode Nyeri | `metode_nyeri` | `9.113.67` | 1/1/1/0 | ✅ | P2 · selesai 2026-10-11 | KONSEP_NYERI.md | `EMR/MetodeNyeri/` · `moduls/EMR/MetodeNyeri/` | — |
| `EMR-037` | Body Map Nyeri | `body_map_nyeri` | `9.114` | 1/1/1/0 | ✅ | P2 · selesai 2026-10-11 | KONSEP_NYERI.md | `EMR/BodyMapNyeri/` · `moduls/EMR/BodyMapNyeri/` | — |
| `EMR-038` | Skrining Nyeri Perina | `skrining_nyeri_perina` | `9.113.68` | 1/0/0/0 | ✅ | P2 · selesai 2026-10-11 | KONSEP_NYERI.md | `EMR/SkriningNyeriPerina/` · `moduls/EMR/SkriningNyeriPerina/` | — |
| `EMR-054` | Catatan Keperawatan Rawat Jalan | `catatan_keperawatan_rajal` | `8.77.37` | 0/1/0/0 | ✅ | P2 · selesai 2026-10-11 | KONSEP_ASUHAN_KEPERAWATAN_LANJUTAN.md | `EMR/CatatanKeperawatanRajal/` · `moduls/EMR/CatatanKeperawatanRajal/` | — |
| `EMR-055` | Riwayat Penyakit | `riwayat_penyakit` | `8.77.35` | 1/1/1/0 | ✅ | P2 · selesai 2026-10-11 | KONSEP_ASUHAN_KEPERAWATAN_LANJUTAN.md | `EMR/RiwayatPenyakit/` · `moduls/EMR/RiwayatPenyakit/` | — |
| `EMR-056` | Riwayat Keluarga | `riwayat_keluarga` | `8.77.36` | 1/1/1/0 | ✅ | P2 · selesai 2026-10-11 | KONSEP_ASUHAN_KEPERAWATAN_LANJUTAN.md | `EMR/RiwayatKeluarga/` · `moduls/EMR/RiwayatKeluarga/` | — |
| `EMR-123` | Asesmen Akhir Kehidupan | `asesmen_akhir_kehidupan` | `8.76.33` | 1/0/1/0 | ✅ | P2 · selesai 2026-10-11 | KONSEP_ASUHAN_KEPERAWATAN_LANJUTAN.md | `EMR/AsesmenAkhirKehidupan/` · `moduls/EMR/AsesmenAkhirKehidupan/` | — |
| `EMR-124` | Uji Fungsi & Asesment | `uji_fungsi_asesment` | `8.76.34` | 0/1/0/1 | ✅ | P2 · selesai 2026-10-11 | KONSEP_ASUHAN_KEPERAWATAN_LANJUTAN.md | `EMR/UjiFungsiAsesment/` · `moduls/EMR/UjiFungsiAsesment/` | — |
| `EMR-125` | Respon Emosi & Status Mental | `respon_emosi` | `8.76.32` | 1/1/1/0 | ✅ | P2 · selesai 2026-10-11 | KONSEP_ASUHAN_KEPERAWATAN_LANJUTAN.md | `EMR/ResponEmosi/` · `moduls/EMR/ResponEmosi/` | — |

## 9. Fase P3 — Gizi, Farmasi & Penunjang

| Task | Nama Form | `slug` | `id_dash_menu` | ri/rj/igd/mcu | Status | Owner | Dokumen Konsep | Folder Controller/View | Catatan / Prasyarat |
|---|---|---|---|---|---|---|---|---|---|
| `EMR-039` | Asesmen Gizi | `asesmen_gizi` | `10.153` | 1/1/1/0 | ✅ |  | P3 · selesai 2026-10-11 | KONSEP_ASESMEN_GIZI.md | `EMR/AsesmenGizi/` · `moduls/EMR/AsesmenGizi/` | — |
| `EMR-040` | Detail Nutrisi | `detail_nutrisi` | `10.154` | 1/0/0/0 | ✅ |  | P3 · selesai 2026-10-11 | KONSEP_ASESMEN_GIZI.md | `EMR/DetailNutrisi/` · `moduls/EMR/DetailNutrisi/` | — |
| `EMR-041` | Rekap Asesmen Nutrisi | `rekap_asesmen_nutrisi` | `10.155` | 1/0/0/0 | ⛔ |  | KONSEP_ASESMEN_GIZI.md | `EMR/RekapAsesmenNutrisi/` · `moduls/EMR/RekapAsesmenNutrisi/` | Legacy tidak punya tabel konversi ambang SGA — `kesimpulan` tak bisa diisi |
| `EMR-042` | Pesan Makanan & Monitoring Asupan | `pesan_makanan` | `10.156` | 1/0/0/0 | ✅ |  | P3 · selesai 2026-10-11 | KONSEP_ASESMEN_GIZI.md | `EMR/PesanMakanan/` · `moduls/EMR/PesanMakanan/` | — |
| `EMR-043` | Diet Pasien | `diet_pasien` | `10.157` | 1/1/1/0 | ✅ |  | P3 · selesai 2026-10-11 | KONSEP_ASESMEN_GIZI.md | `EMR/DietPasien/` · `moduls/EMR/DietPasien/` | Butuh **PRE-05** — `master_diet` + `master_diagnosa_diet` (selesai 2026-10-11) |
| `EMR-044` | ADIME | `adime` | `10.158` | 1/0/0/0 | ✅ |  | P3 · selesai 2026-10-11 | KONSEP_ASESMEN_GIZI.md | `EMR/Adime/` · `moduls/EMR/Adime/` | — |
| `EMR-045` | Daftar Terapi Obat | `daftar_terapi_obat` | `11.173` | 1/1/1/0 | ✅ |  | P3 · selesai 2026-10-11 | KONSEP_FARMAKASI.md | `EMR/DaftarTerapiObat/` · `moduls/EMR/DaftarTerapiObat/` | 184 baris bersuffix (obat_1..20), maks 20 obat |
| `EMR-046` | Rekonsiliasi Obat | `rekonsiliasi_obat` | `11.174` | 1/1/1/0 | ✅ |  | P3 · selesai 2026-10-11 | KONSEP_FARMAKASI.md | `EMR/RekonsiliasiObat/` · `moduls/EMR/RekonsiliasiObat/` | 3 grup x 10 baris = 246 baris bersuffix |
| `EMR-047` | Telaah Resep | `telaah_resep` | `11.175` | 0/1/1/0 | ✅ |  | P3 · selesai 2026-10-11 | KONSEP_FARMAKASI.md | `EMR/TelaahResep/` · `moduls/EMR/TelaahResep/` | — |
| `EMR-048` | Telaah Obat | `telaah_obat` | `11.176` | 1/0/0/0 | ✅ |  | P3 · selesai 2026-10-11 | KONSEP_FARMAKASI.md | `EMR/TelaahObat/` · `moduls/EMR/TelaahObat/` | — |
| `EMR-049` | Order Patologi | `order_patologi` | `4.193` | 1/1/1/0 | ✅ |  | P3 · selesai 2026-10-11 | KONSEP_PENUNJANG_MEDIS_LANJUTAN.md | `EMR/OrderPatologi/` · `moduls/EMR/OrderPatologi/` | Data di tabel `order_patologi` (bukan emr_detail); Butuh **PRE-02** ✅ |
| `EMR-050` | Order Mikrobiologi | `order_mikrobiologi` | `4.194` | 1/1/1/0 | ✅ |  | P3 · selesai 2026-10-11 | KONSEP_PENUNJANG_MEDIS_LANJUTAN.md | `EMR/OrderMikrobiologi/` · `moduls/EMR/OrderMikrobiologi/` | Data di tabel `order_mikrobiologi`; Butuh **PRE-03** ✅ |
| `EMR-051` | Order POCT | `order_poct` | `4.195` | 1/1/1/0 | ✅ |  | P3 · selesai 2026-10-11 | KONSEP_PENUNJANG_MEDIS_LANJUTAN.md | `EMR/OrderPoct/` · `moduls/EMR/OrderPoct/` | Data di tabel `order_poct`; Butuh **PRE-04** ✅ |

## 10. Fase P4 — Kebidanan & MCU

| Task | Nama Form | `slug` | `id_dash_menu` | ri/rj/igd/mcu | Status | Owner | Dokumen Konsep | Folder Controller/View | Catatan / Prasyarat |
|---|---|---|---|---|---|---|---|---|---|
| `EMR-059` | Asesmen Khusus Kebidanan & Penyakit Kandungan | `asesmen_kebidanan` | `13.253` | 1/1/0/0 | ✅ |  | KONSEP_OBSTETRI_NEONATAL.md | `EMR/AsesmenKebidanan/` · `moduls/EMR/AsesmenKebidanan/` | — |
| `EMR-060` | Pengkajian Awal Keperawatan Perina | `pengkajian_perina` | `13.254` | 1/0/0/0 | ✅ |  | KONSEP_OBSTETRI_NEONATAL.md | `EMR/PengkajianPerina/` · `moduls/EMR/PengkajianPerina/` | — |
| `EMR-061` | Pengkajian Awal Keperawatan Non-Perina | `pengkajian_non_perina` | `13.255` | 1/0/0/0 | ✅ |  | KONSEP_OBSTETRI_NEONATAL.md | `EMR/PengkajianNonPerina/` · `moduls/EMR/PengkajianNonPerina/` | — |
| `EMR-062` | Kala I-IV | `kala_persalinan` | `13.256` | 1/0/0/0 | ✅ |  | KONSEP_OBSTETRI_NEONATAL.md | `EMR/KalaPersalinan/` · `moduls/EMR/KalaPersalinan/` | — |
| `EMR-063` | Riwayat Persalinan Sekarang | `riwayat_persalinan` | `13.257` | 1/0/0/0 | ✅ |  | KONSEP_OBSTETRI_NEONATAL.md | `EMR/RiwayatPersalinan/` · `moduls/EMR/RiwayatPersalinan/` | — |
| `EMR-064` | Pelaksanaan Bayi Baru Lahir | `bayi_baru_lahir` | `13.258` | 1/0/0/0 | ✅ |  | KONSEP_OBSTETRI_NEONATAL.md | `EMR/BayiBaruLahir/` · `moduls/EMR/BayiBaruLahir/` | — |
| `EMR-065` | Riwayat Resusitasi Bayi | `riwayat_resusitasi_bayi` | `13.259` | 1/0/0/0 | ✅ |  | KONSEP_OBSTETRI_NEONATAL.md | `EMR/RiwayatResusitasiBayi/` · `moduls/EMR/RiwayatResusitasiBayi/` | — |
| `EMR-066` | Penilaian Bayi dengan APGAR Score | `penilaian_bayi_dengan_apgar_score` | `13.260` | 1/0/0/0 | ✅ |  | KONSEP_OBSTETRI_NEONATAL.md | `EMR/PenilaianBayiDenganApgarScore/` · `moduls/EMR/PenilaianBayiDenganApgarScore/` | — |
| `EMR-067` | Down Score (Neonatal) | `down_score` | `13.261` | 1/0/0/0 | ⛔ |  | KONSEP_OBSTETRI_NEONATAL.md | `EMR/DownScore/` · `moduls/EMR/DownScore/` | Tabel interpretasi Down Score legacy bolong (skor 6 tak tercakup) |
| `EMR-068` | Inisiasi Menyusui Dini | `inisiasi_menyusui_dini` | `13.262` | 1/0/0/0 | ✅ |  | KONSEP_OBSTETRI_NEONATAL.md | `EMR/InisiasiMenyusuiDini/` · `moduls/EMR/InisiasiMenyusuiDini/` | — |
| `EMR-069` | Skrining MDPK/MPP | `skrining_mpp` | `13.263` | 1/1/0/0 | ✅ |  | KONSEP_OBSTETRI_NEONATAL.md | `EMR/SkriningMpp/` · `moduls/EMR/SkriningMpp/` | — |
| `EMR-070` | Kesimpulan MCU | `kesimpulan_mcu` | `14.273` | 0/0/0/1 | ✅ |  | KONSEP_FORM_MCU.md | `EMR/KesimpulanMcu/` · `moduls/EMR/KesimpulanMcu/` | — |
| `EMR-071` | Catatan Keperawatan / TTV MCU | `ttv_mcu` | `14.274` | 0/0/0/1 | ✅ |  | KONSEP_FORM_MCU.md | `EMR/TtvMcu/` · `moduls/EMR/TtvMcu/` | — |
| `EMR-072` | Visus Mata MCU | `visus_mata_mcu` | `14.275` | 0/0/0/1 | ✅ |  | KONSEP_FORM_MCU.md | `EMR/VisusMataMcu/` · `moduls/EMR/VisusMataMcu/` | — |
| `EMR-073` | Obstetri MCU | `obstetri_mcu` | `14.276` | 0/0/0/1 | ✅ |  | KONSEP_FORM_MCU.md | `EMR/ObstetriMcu/` · `moduls/EMR/ObstetriMcu/` | — |
| `EMR-074` | Penyakit Dalam MCU | `penyakit_dalam_mcu` | `14.277` | 0/0/0/1 | ✅ |  | KONSEP_FORM_MCU.md | `EMR/PenyakitDalamMcu/` · `moduls/EMR/PenyakitDalamMcu/` | — |
| `EMR-075` | Jantung MCU | `jantung_mcu` | `14.278` | 0/0/0/1 | ✅ |  | KONSEP_FORM_MCU.md | `EMR/JantungMcu/` · `moduls/EMR/JantungMcu/` | — |
| `EMR-076` | THT MCU | `tht_mcu` | `14.279` | 0/0/0/1 | ✅ |  | KONSEP_FORM_MCU.md | `EMR/ThtMcu/` · `moduls/EMR/ThtMcu/` | — |
| `EMR-077` | Mata MCU | `mata_mcu` | `14.280` | 0/0/0/1 | ✅ |  | KONSEP_FORM_MCU.md | `EMR/MataMcu/` · `moduls/EMR/MataMcu/` | — |
| `EMR-078` | Gigi MCU | `gigi_mcu` | `14.281` | 0/0/0/1 | ✅ |  | KONSEP_FORM_MCU.md | `EMR/GigiMcu/` · `moduls/EMR/GigiMcu/` | — |
| `EMR-079` | OBGYN MCU | `obgyn_mcu` | `14.282` | 0/0/0/1 | ✅ |  | KONSEP_FORM_MCU.md | `EMR/ObgynMcu/` · `moduls/EMR/ObgynMcu/` | — |
| `EMR-080` | Bedah MCU | `bedah_mcu` | `14.283` | 0/0/0/1 | ✅ |  | KONSEP_FORM_MCU.md | `EMR/BedahMcu/` · `moduls/EMR/BedahMcu/` | — |
| `EMR-081` | Neurologi MCU | `neurologi_mcu` | `14.284` | 0/0/0/1 | ✅ |  | KONSEP_FORM_MCU.md | `EMR/NeurologiMcu/` · `moduls/EMR/NeurologiMcu/` | — |
| `EMR-082` | Gizi MCU | `gizi_mcu` | `14.285` | 0/0/0/1 | ✅ |  | KONSEP_FORM_MCU.md | `EMR/GiziMcu/` · `moduls/EMR/GiziMcu/` | — |
| `EMR-083` | Urologi MCU | `urologi_mcu` | `14.286` | 0/0/0/1 | ✅ |  | KONSEP_FORM_MCU.md | `EMR/UrologiMcu/` · `moduls/EMR/UrologiMcu/` | — |
| `EMR-084` | Psikologi MCU | `psikologi_mcu` | `14.287` | 0/0/0/1 | ✅ |  | KONSEP_FORM_MCU.md | `EMR/PsikologiMcu/` · `moduls/EMR/PsikologiMcu/` | — |

## 11. Fase P5 — Bedah, Anestesi & ICU

| Task | Nama Form | `slug` | `id_dash_menu` | ri/rj/igd/mcu | Status | Owner | Dokumen Konsep | Folder Controller/View | Catatan / Prasyarat |
|---|---|---|---|---|---|---|---|---|---|
| `EMR-052` | Kriteria Masuk ICU | `kriteria_masuk_icu` | `12.213` | 1/0/1/0 | ✅ |  | KONSEP_ICU_HEMODIALISA.md | `EMR/KriteriaMasukIcu/` · `moduls/EMR/KriteriaMasukIcu/` | Profesi 16 baru (PRE-01) |
| `EMR-053` | Kriteria Keluar ICU | `kriteria_keluar_icu` | `12.214` | 1/0/0/0 | ✅ |  | KONSEP_ICU_HEMODIALISA.md | `EMR/KriteriaKeluarIcu/` · `moduls/EMR/KriteriaKeluarIcu/` | Profesi 16 baru (PRE-01) |
| `EMR-085` | Asesmen Pra Bedah | `asesmen_pra_bedah` | `15.293` | 1/1/1/0 | ✅ | opencode | KONSEP_BEDAH_ANESTESI.md | `EMR/AsesmenPraBedah/` · `moduls/EMR/AsesmenPraBedah/` | selesai 2026-10-14 · 25 variabel E2E ALL OK |
| `EMR-086` | Evaluasi Pre-Anestesi & Sedasi | `evaluasi_pre_anestesi_sedasi` | `15.294` | 1/0/1/0 | ✅ | opencode | KONSEP_BEDAH_ANESTESI.md | `EMR/EvaluasiPreAnestesiSedasi/` · `moduls/EMR/EvaluasiPreAnestesiSedasi/` | selesai 2026-10-14 · 62 variabel E2E ALL OK |
| `EMR-087` | Asesmen Post-Anestesi & Sedasi | `asesmen_post_anestesi_sedasi` | `15.295` | 1/0/0/0 | ✅ | opencode | KONSEP_BEDAH_ANESTESI.md | `EMR/AsesmenPostAnestesiSedasi/` · `moduls/EMR/AsesmenPostAnestesiSedasi/` | selesai 2026-10-14 · 46 variabel E2E ALL OK |
| `EMR-088` | Monitoring Anestesi & Sedasi | `monitoring_anestesi_sedasi` | `15.296` | 1/0/1/0 | ✅ | opencode | KONSEP_BEDAH_ANESTESI.md | `EMR/MonitoringAnestesiSedasi/` · `moduls/EMR/MonitoringAnestesiSedasi/` | selesai 2026-10-14 · 106 variabel E2E ALL OK |
| `EMR-089` | Check List Keamanan Pasien Operasi | `checklist_keamanan_operasi` | `15.297` | 1/0/1/0 | ⛔ |  | KONSEP_BEDAH_ANESTESI.md | `EMR/ChecklistKeamananOperasi/` · `moduls/EMR/ChecklistKeamananOperasi/` | Desain memakai hard gate — perlu persetujuan tim klinis |
| `EMR-090` | Check List Kesiapan Anestesi | `checklist_kesiapan_anestesi` | `15.298` | 1/0/1/0 | ✅ | opencode | KONSEP_BEDAH_ANESTESI.md | `EMR/ChecklistKesiapanAnestesi/` · `moduls/EMR/ChecklistKesiapanAnestesi/` | selesai 2026-10-14 · 9 variabel E2E ALL OK |
| `EMR-091` | Pengkajian Pre/Intra/Post Operasi | `pengkajian_operasi` | `15.299` | 1/0/1/0 | ✅ | opencode | KONSEP_BEDAH_ANESTESI.md | `EMR/PengkajianOperasi/` · `moduls/EMR/PengkajianOperasi/` | selesai 2026-10-14 · 25 variabel E2E ALL OK |
| `EMR-092` | Laporan Operasi | `laporan_operasi` | `15.300` | 1/0/0/0 | ✅ | opencode | KONSEP_BEDAH_ANESTESI.md | `EMR/LaporanOperasi/` · `moduls/EMR/LaporanOperasi/` | selesai 2026-10-14 · 31 variabel E2E ALL OK |
| `EMR-093` | Catatan Bedah | `catatan_bedah` | `15.301` | 1/0/0/0 | ✅ | opencode | KONSEP_BEDAH_ANESTESI.md | `EMR/CatatanBedah/` · `moduls/EMR/CatatanBedah/` | selesai 2026-10-14 · 28 variabel E2E ALL OK |
| `EMR-094` | Transfer Pasien Antar Ruangan | `transfer_pasien` | `15.302` | 1/0/1/0 | ✅ | opencode | KONSEP_BEDAH_ANESTESI.md | `EMR/TransferPasien/` · `moduls/EMR/TransferPasien/` | selesai 2026-10-14 · 46 variabel E2E ALL OK |
| `EMR-095` | Penandaan Lokasi Operasi | `penandaan_lokasi_operasi` | `15.303` | 1/0/1/0 | ✅ | opencode | KONSEP_BEDAH_ANESTESI.md | `EMR/PenandaanLokasiOperasi/` · `moduls/EMR/PenandaanLokasiOperasi/` | selesai 2026-10-14 · 10 variabel E2E ALL OK |
| `EMR-096` | Penandaan Pasien | `penandaan_pasien` | `15.304` | 1/0/1/0 | ✅ | opencode | KONSEP_BEDAH_ANESTESI.md | `EMR/PenandaanPasien/` · `moduls/EMR/PenandaanPasien/` | selesai 2026-10-14 · 14 variabel E2E ALL OK |
| `EMR-097` | Ceklis Pre & Post Tindakan Invasif | `ceklis_tindakan_invasif` | `15.305` | 1/0/1/0 | ⛔ |  | KONSEP_BEDAH_ANESTESI.md | `EMR/CeklisTindakanInvasif/` · `moduls/EMR/CeklisTindakanInvasif/` | Hard gate checklist tindakan invasif — perlu persetujuan tim klinis |
| `EMR-098` | Surveilans Luka Insisi | `surveilans_luka_insisi` | `15.306` | 1/0/0/0 | ✅ | P5 · selesai 2026-10-14 | KONSEP_BEDAH_ANESTESI.md | `EMR/SurveilansLukaInsisi/` · `moduls/EMR/SurveilansLukaInsisi/` | — |
| `EMR-099` | Catatan Anestesia | `catatan_anestesia` | `15.307` | 1/0/1/0 | ✅ | P5 · selesai 2026-10-14 | KONSEP_BEDAH_ANESTESI.md | `EMR/CatatanAnestesia/` · `moduls/EMR/CatatanAnestesia/` | Profesi 17 baru (PRE-01) |
| `EMR-100` | Ceklist Keamanan Angiografi | `ceklist_keamanan_angiografi` | `15.308` | 1/0/1/0 | ⛔ |  | KONSEP_BEDAH_ANESTESI.md | `EMR/CeklistKeamananAngiografi/` · `moduls/EMR/CeklistKeamananAngiografi/` | Hard gate checklist angiografi — perlu persetujuan tim klinis |
| `EMR-101` | Asesmen Katarak | `asesmen_katarak` | `15.309` | 0/1/0/0 | ✅ | P5 · selesai 2026-10-14 | KONSEP_BEDAH_ANESTESI.md | `EMR/AsesmenKatarak/` · `moduls/EMR/AsesmenKatarak/` | — |
| `EMR-102` | Pre Hemodialisa | `pre_hemodialisa` | `12.215` | 1/0/1/0 | ✅ | opencode | KONSEP_ICU_HEMODIALISA.md | `EMR/PreHemodialisa/` · `moduls/EMR/PreHemodialisa/` | Profesi 18 baru (PRE-01) |
| `EMR-103` | Intra Hemodialisa | `intra_hemodialisa` | `12.216` | 1/0/1/0 | ✅ | opencode | KONSEP_ICU_HEMODIALISA.md | `EMR/IntraHemodialisa/` · `moduls/EMR/IntraHemodialisa/` | Profesi 18 baru (PRE-01) |
| `EMR-104` | Post Hemodialisa | `post_hemodialisa` | `12.217` | 1/0/0/0 | ✅ | opencode | KONSEP_ICU_HEMODIALISA.md | `EMR/PostHemodialisa/` · `moduls/EMR/PostHemodialisa/` | Profesi 18 baru (PRE-01) |
| `EMR-105` | Triage Hemodialisa | `triage_hemodialisa` | `12.218` | 0/1/0/0 | ✅ | opencode | KONSEP_ICU_HEMODIALISA.md | `EMR/TriageHemodialisa/` · `moduls/EMR/TriageHemodialisa/` | — |

## 12. Fase P6 — Onkologi, Psikososial & Formulir

| Task | Nama Form | `slug` | `id_dash_menu` | ri/rj/igd/mcu | Status | Owner | Dokumen Konsep | Folder Controller/View | Catatan / Prasyarat |
|---|---|---|---|---|---|---|---|---|---|
| `EMR-058` | Rekam Medis Rujukan | `rekam_medis_rujukan` | `5.233` | 1/1/1/0 | ⬜ |  | KONSEP_FORMULIR_RUJUKAN.md | `EMR/RekamMedisRujukan/` · `moduls/EMR/RekamMedisRujukan/` | Menempel objek 186 (`Dirujuk Ke`) milik form 16 |
| `EMR-106` | Asesmen Kemoterapi | `asesmen_kemoterapi` | `18.353` | 1/1/0/0 | ⬜ |  | KONSEP_ONKOLOGI_TRANSFUSI.md | `EMR/AsesmenKemoterapi/` · `moduls/EMR/AsesmenKemoterapi/` | — |
| `EMR-107` | Pemantauan Kemoterapi | `pemantauan_kemoterapi` | `18.354` | 1/0/0/0 | ⬜ |  | KONSEP_ONKOLOGI_TRANSFUSI.md | `EMR/PemantauanKemoterapi/` · `moduls/EMR/PemantauanKemoterapi/` | — |
| `EMR-108` | Asesmen Pasien dengan Darah | `asesmen_pemberian_darah` | `18.355` | 1/1/1/0 | ⬜ |  | KONSEP_ONKOLOGI_TRANSFUSI.md | `EMR/AsesmenPemberianDarah/` · `moduls/EMR/AsesmenPemberianDarah/` | — |
| `EMR-109` | Asesmen Transfusi | `assesmen_transfusi` | `18.356` | 1/0/0/0 | ⬜ |  | KONSEP_ONKOLOGI_TRANSFUSI.md | `EMR/AssesmenTransfusi/` · `moduls/EMR/AssesmenTransfusi/` | — |
| `EMR-110` | Monitoring Transfusi Darah | `monitoring_transfusi` | `18.357` | 1/0/0/0 | ⬜ |  | KONSEP_ONKOLOGI_TRANSFUSI.md | `EMR/MonitoringTransfusi/` · `moduls/EMR/MonitoringTransfusi/` | — |
| `EMR-111` | Double Check Obat High Alert | `double_check_high_alert` | `18.358` | 1/0/1/0 | ⬜ |  | KONSEP_ONKOLOGI_TRANSFUSI.md | `EMR/DoubleCheckHighAlert/` · `moduls/EMR/DoubleCheckHighAlert/` | — |
| `EMR-112` | Asesmen Psikologi Pasien & Keluarga | `assesmen_psikologi` | `16.313` | 1/1/1/0 | ⬜ |  | KONSEP_PSIKOSOSIAL_KEROHANIAN.md | `EMR/AssesmenPsikologi/` · `moduls/EMR/AssesmenPsikologi/` | Profesi 14 baru (PRE-01) |
| `EMR-113` | Asesmen Spiritual | `assesmen_spiritual` | `16.314` | 1/1/1/0 | ⬜ |  | KONSEP_PSIKOSOSIAL_KEROHANIAN.md | `EMR/AssesmenSpiritual/` · `moduls/EMR/AssesmenSpiritual/` | Profesi 14 baru (PRE-01) |
| `EMR-114` | Permintaan Pelayanan Kerohanian | `permintaan_kerohanian` | `16.315` | 1/1/1/0 | ⬜ |  | KONSEP_PSIKOSOSIAL_KEROHANIAN.md | `EMR/PermintaanKerohanian/` · `moduls/EMR/PermintaanKerohanian/` | Profesi 14 baru (PRE-01) |
| `EMR-115` | Bukti Pelayanan Kerohanian | `bukti_kerohanian` | `16.316` | 1/1/1/0 | ⬜ |  | KONSEP_PSIKOSOSIAL_KEROHANIAN.md | `EMR/BuktiKerohanian/` · `moduls/EMR/BuktiKerohanian/` | Profesi 14 baru (PRE-01) |
| `EMR-116` | Surveilans Pasien Terpasang CVC/PICC | `surveilans_cvc_picc` | `17.333` | 1/0/1/0 | ⬜ |  | KONSEP_SURVEILANS_INFEKSI.md | `EMR/SurveilansCvcPicc/` · `moduls/EMR/SurveilansCvcPicc/` | — |
| `EMR-117` | Surveilans Pasien Terpasang IV Catheter Perifer | `surveilans_iv_catheter` | `17.334` | 1/0/1/0 | ⬜ |  | KONSEP_SURVEILANS_INFEKSI.md | `EMR/SurveilansIvCatheter/` · `moduls/EMR/SurveilansIvCatheter/` | — |
| `EMR-118` | Surveilans Pasien Terpasang Kateter Urine | `surveilans_kateter_urine` | `17.335` | 1/0/1/0 | ⬜ |  | KONSEP_SURVEILANS_INFEKSI.md | `EMR/SurveilansKateterUrine/` · `moduls/EMR/SurveilansKateterUrine/` | — |
| `EMR-119` | Surveilans Pasien Tirah Baring Total | `surveilans_tirah_baring` | `17.336` | 1/0/0/0 | ⬜ |  | KONSEP_SURVEILANS_INFEKSI.md | `EMR/SurveilansTirahBaring/` · `moduls/EMR/SurveilansTirahBaring/` | — |
| `EMR-120` | Screening Akses Vaskular | `screening_akses` | `17.337` | 1/0/1/0 | ⬜ |  | KONSEP_SURVEILANS_INFEKSI.md | `EMR/ScreeningAkses/` · `moduls/EMR/ScreeningAkses/` | — |
| `EMR-121` | Asesmen Pasien Restraint | `asesmen_restraint` | `17.338` | 1/0/0/0 | ⬜ |  | KONSEP_SURVEILANS_INFEKSI.md | `EMR/AsesmenRestraint/` · `moduls/EMR/AsesmenRestraint/` | — |
| `EMR-122` | Observasi Restraint | `observasi_restraint` | `17.339` | 1/0/0/0 | ⬜ |  | KONSEP_SURVEILANS_INFEKSI.md | `EMR/ObservasiRestraint/` · `moduls/EMR/ObservasiRestraint/` | — |
| `EMR-126` | Surat Keterangan Sehat | `surat_keterangan_sehat` | `5.234` | 0/1/0/0 | ⬜ |  | KONSEP_FORMULIR_RUJUKAN.md | `EMR/SuratKeteranganSehat/` · `moduls/EMR/SuratKeteranganSehat/` | — |
| `EMR-127` | Pesan Bedah / Cathlab / Endoscopy / ESWL | `pesan_penunjang` | `5.235` | 1/1/1/0 | ⬜ |  | KONSEP_FORMULIR_RUJUKAN.md | `EMR/PesanPenunjang/` · `moduls/EMR/PesanPenunjang/` | — |
| `EMR-128` | SPRI & Program Rujukan Balik | `spri_rujukan_balik` | `5.236` | 1/1/0/0 | ⬜ |  | KONSEP_FORMULIR_RUJUKAN.md | `EMR/SpriRujukanBalik/` · `moduls/EMR/SpriRujukanBalik/` | — |
| `EMR-129` | Katalog Form Blanko | `katalog_form_blanko` | `5.373` | 1/1/1/1 | ⬜ |  | KONSEP_KATALOG_FORM_BLANKO.md | `EMR/KatalogFormBlanko/` · `moduls/EMR/KatalogFormBlanko/` | Butuh **PRE-06** |

---

## 13. Keputusan Klinis Tertunda (`⛔`)

Task berikut **tidak bisa diselesaikan** tanpa keputusan manusia. Jangan dikerjakan sampai diputuskan.

| Task | Form | Keputusan yang dibutuhkan |
|---|---|---|
| `EMR-041` | 41 | **Ambang SGA.** Legacy tidak punya tabel konversi ke status Gizi Ringan / Sedang / Berat. Field `kesimpulan` sengaja tidak disimpan sampai tim klinis menetapkan ambang. |
| `EMR-067` | 67 | **Celah tabel interpretasi Down Score.** Legacy tertulis "Skor 4–5 sedang" dan "Skor > 6 berat" sehingga **skor 6 tidak tercakup**. Desain menutupnya `< 4` / `4–6` / `> 6` — perlu konfirmasi klinis. |
| `EMR-089` | 89 | **WHO Surgical Safety Checklist — hard gate.** Desain menolak `store()` bila butir kritis (gelang identitas, informed consent, kelengkapan alat) tidak lengkap. Ini keputusan keselamatan yang harus disetujui & diuji sebelum go-live. |
| `EMR-097` | 97 | **Ceklis Tindakan Invasif — hard gate.** Sama seperti EMR-089. |
| `EMR-100` | 100 | **Ceklist Keamanan Angiografi — hard gate.** Sama seperti EMR-089. |

### Keputusan lain yang masih terbuka

- **DNR / WLSBT** sengaja tidak dianggarkan di dokumen bedah — perlu diputuskan apakah ditambahkan ke form 85/86.
- **Form 1** — selesaikan atau soft-delete (lihat §6).
- **Skor Aldrete / Bromage / Steward / PADSS** di legacy diisi `<select>0/1/2` tanpa label kriteria. Desain mengisi tabel kriteria lengkap dari standar — perlu konfirmasi angkanya.
- **Profesi 16 (Intensivis) vs 17 (Anestesi)** — perlu dipastikan pembagian tanggung jawab klinis sebelum `akses_ehr` di-seed (PRE-01).

---

## 14. Referensi

| Dokumen | Isi |
|---|---|
| `docs/ALOKASI_ID_GLOBAL.md` | ID final semua form/objek/menu/profesi — **ledger** |
| `docs/PANDUAN_IMPLEMENTASI_FORM_EMR.md` | Cara menambah form (checklist 11 langkah) |
| `docs/ANALISIS_KEKURANGAN_FORM.md` | Analisis gap vs SIMRS Tenriawaru |
| `docs/KONSEP_*.md` (19 file) | Spesifikasi tiap domain: field, mapping, akses, validasi, cetak |
| `AGENTS.md` | Aturan proyek; section *Status Implementasi Form EMR* menunjuk ke file ini |
| `tools/e2e.php` + `tools/lib_pay.php` | Harness verifikasi E2E (payload builder introspektif) |

## 15. Riwayat

| Tanggal | Perubahan |
|---|---|
| 2026-10-10 | File dibuat. 114 form (16–129) + 12 prasyarat + 15 form eksisting tercatat. 5 task `⛔`. |
| 2026-10-11 | **Fase P2 selesai**: 23 form (22–38, 54–56, 123–125) → ✅. Tambah objek 236–361, menu 8 "Penilaian Klinis" & 9 "Monitoring", sub 73–77/93–95/113–114/133–134, extra 26–37/46–47/66–68/86–88. 6 helper baru. Semua terverifikasi E2E (363 variabel).
| 2026-10-11 | Tambah harness E2E permanen `tools/e2e.php` + `tools/lib_pay.php` (payload builder introspektif dari aturan `validated()`).
| 2026-10-11 | **Fase P3 berjalan (6/12 form)** — seeder P3 lengkap dimuat (objek 362-442, menu 10 "Gizi & Nutrisi" + 11 "Farmasi", sub 153-158/173-176/193-195, form 39-51 kecuali 41 `⛔`, mapping + `akses_ehr`). Helper baru `GiziHelper` (IMT/status gizi/4 instrumen skrining/penurunan BB) & `FarmasiHelper` (7 kriteria telaah resep, 5 telaah obat, 7 kode status pemberian). Selesai & terverifikasi E2E: **39, 40, 42, 44, 47, 48**. Seeder kini soft-delete mapping aktif yang variabelnya sudah hilang dari `$mapping` (perbaiki form 48 yang punya `prioritas` hantu). Regresi P1+P2 nihil: **36 form, 700 variabel, ALL OK**. Sisa P3: 45, 46 (Farmasi baris bersuffix), 43 (butuh PRE-05 `master_diet`), 49-51 (butuh PRE-01..04). |
| 2026-10-11 | **Fase P3 Farmasi selesai + form 43** — form 45 (Daftar Terapi Obat, 184 baris suffix, maks 20 obat) & form 46 (Rekonsiliasi Obat, 3 grup x 10 baris = 246 baris suffix) ✅. Harness E2E kini bisa membaca aturan validasi yang ditulis di dalam loop (`expandRuleLoops()`: ekspansi `for ($b=1; $b<=self::MAKS_BARIS; $b++)`, konstanta kelas, dan variabel aturan lokal). `GiziHelper::kesimpulan()` untuk SGA sengaja mengembalikan **null** — belum ada ambang konversi klinis tertulis (KONSEP §11 risiko #3), jadi hanya skor yang ditampilkan. **PRE-05** partly: migration `master_diet` + `master_diagnosa_diet`, model, `MasterDietSeeder` (20 diet + 16 faktor penyerta); halaman CRUD admin belum. **PRE-01** ✅: profesi 14 Kerohanian, 15 Patolog Anatomi, 16 Dokter Intensivis, 17 Dokter Anestesi, 18 Perawat Hemodialisa. Verifikasi: **39 form, 1133 variabel, ALL OK**, pint bersih. Sisa P3: form 49, 50, 51 (butuh PRE-02/03/04 — tabel `order_patologi`, `order_mikrobiologi`, `order_poct` + detail). |
| 2026-10-11 | **Fase P3 selesai 12/12 form** (kecuali 41 `⛔`) — form 49 Order Patologi, 50 Order Mikrobiologi, 51 Order POCT ✅. Ketiganya **TIDAK** memakai `emr_detail`: data order disimpan di tabel `order_patologi`, `order_mikrobiologi`, `order_poct` (+ `_detail`) seperti Order Laboratorium (form 6). **PRE-02/03/04 ✅**: satu migration `2026_10_11_000002` membuat keenam tabel (dual-driver), 6 model baru. `PenunjangHelper` kini generic total — menambah jenis order cukup menambah 1 baris di `PenunjangHelper::TABEL`, `::PREFIX`, dan `::MODEL`; `findOrder()`/`baseQuery()` tidak lagi bercabang `if ($jenis === 'lab')`. **Harness baru `tools/e2e_order.php`** untuk order: `tools/e2e.php` tidak bisa mengujinya karena butuh item baris dari Master Group Tindakan. Verifikasi akhir: **39 form, 1133 variabel, ALL OK** + **3 jenis order ALL OK**, pint bersih. |
| 2026-10-11 | **PRE-05 ✅** — CRUD admin `Master Diet` (sub_menu 63) & `Master Diagnosa Diet` (sub_menu 64) di `Administrator → Manajemen Master`, mengikuti pola `MasterImplementasi` persis. 2 controller + 8 view (index/create/edit/_form per master), `MasterDietMenuSeeder` (idempotent, sekaligus memberi `user_akses` admin). 10 route auto-registered oleh `SubMenuRouteServiceProvider`; 6 halaman render 200; CRUD diuji end-to-end (create, tolak kode duplikat, update, soft delete). **Peringatan di form edit**: `nama_diet` adalah nilai yang TERSIMPAN di `emr_detail` (form 43 memvalidasi `exists:master_diet,nama_diet`) — mengubahnya memutus riwayat lama, jadi deskripsi diubah lewat kolom Keterangan. Regresi nihil: **39 form, 1133 variabel, ALL OK** + 3 jenis order. |
| 2026-10-11 | **Verifikasi akhir P1+P2**: 30 form (16-38, 54-57, 123-125) lulus E2E penuh - **559 variabel**, `ALL OK`. Harness diperkuat: `min`/`max` hanya untuk kolom numerik, `phpListValues()` (pecah literal PHP hormati kutip/kurung), `Rule::exists()` objek + rantai `->where()`, blok rules `$var = [...]` yang di-*validate* via variabel, deteksi `array` tanpa kutip. Harness kini auto-bersihkan data uji di akhir run (sebelumnya meninggalkan 35 baris `emr` + 442 nilai "Data Uji"). `pint --test` bersih, 0 CJK/garbled. Integritas: objek 361, form 45, mapping 923, akses 98, menu 9, sub 31, extra 26, 0 data uji tersisa. |
| 2026-10-14 | **Fase P4 Kebidanan selesai (kecuali 67 `⛔`)** — form 59–66 & 68 → ✅ (9 form, 401 variabel, ALL OK). Form 59 `AsesmenKebidanan` (77 var, cross-field `p+a<=g` ditangani di harness `lib_pay`), 60 `PengkajianPerina` (135 var, SAP head-to-toe + checkbox pola legacy), 62 `KalaPersalinan` (18 var, validasi duplikat kala per kunjungan + panel APGAR read-only). **Gotcha harness**: komentar `//` berkutip di dalam array literal `$rules` merusak parse key `ruleMap` (key berikutnya tertelan jadi bagian nilai entry sebelumnya) — hindari komentar di dalam array rules. `ruleMap` missing=0 untuk form 59/60. Regresi: jalankan `e2e.php 59,60,61,62,63,64,65,66,68`. |
| 2026-10-14 | **Fase P5 dimulai — form 52 & 53 ICU ✅** (2 form, 75 variabel, ALL OK). Seeder P5: menu 12 "ICU & Hemodialisa", sub 213/214, form 52/53, objek 447–471 (472 reserved), mapping 51+25 baris, akses_ehr (Dokter full; Perawat & Dokter Intensivis 16 READ; Dokter Anestesi 17 full untuk 53). Helper baru `IcuHelper` (`gcsJumlah`, `jumlahKriteriaTerpenuhi` hanya saat semua status terisi, `kr1Status` turunan YA bila semua butir TIDAK, `kesimpulanKeluar` LAYAK/TIDAK LAYAK). **`dpjp` (objek 453) tidak di-mapping** — readonly dari penanggung_rawat, tidak disimpan. Cross-field 53: kr2/kr4 YA → catatan wajib min. 5 karakter. Regresi P4 nihil. Sisa P5: 85–88, 90–96, 98–99, 101–105 (⛔: 89, 97, 100). |
| 2026-10-14 | **Form 85 Asesmen Pra Bedah ✅** (25 variabel, E2E ALL OK; band bedah 86–101 belum dikerjakan). Seeder band bedah kini ter-seed penuh (menu 15, sub 293–309 minus 297/305/308, form 85–101 minus 89/97/100, objek 762–979, mapping 85 + akses 85 Dokter full / Perawat READ). **Variabel checkbox edukasi pakai snake_case** (`kebutuhan_edukasi_persiapan_mentas` dst., 5 var → objek 768) — label item masih provisional (KONSEP §3). Validasi: `in:Efektif,Darurat` (literal KONSEP; opsi blade difilter dari `SelectOption::jenis_operasi`), `in:Dubia ad Bonam,Dubia ad Malam`, `after_or_equal:today` pada `tanggal_rencana_operasi` (aplikasikan selalu — memblokir edit record lama dgn rencana lampau, sesuai KONSEP), guard `back_up_plan` bila komplikasi terisi, guard min-1 edukasi (hanya bila grup checkbox terkirim; harness skip `nullable in:0,1` jadi guard inert). **Harness `tools/lib_pay.php`**: date branch kini memberi `date('Y-m-d')` bila rule mengandung `after*:today` (default hardcoded `2026-10-10` sudah lewat). Regresi penuh: **FAILURES: 3** (baseline pre-existing order_resep/patologi/mikrobiologi → `e2e_order.php` ALL OK). |
| 2026-10-14 | **Form 86 Evaluasi Pre-Anestesi & Sedasi ✅** (62 variabel, E2E ALL OK, lulus pertama kali). Seeder: mapping 86 = 62 baris + akses 86 (Dokter full / Perawat READ). `pernafasan_2` **tidak dimapping** (hanya catatan "reassign" di tabel field KONSEP; blok Mapping §4 tidak memuatnya). Safety gate di `after()` validator: `persetujuan_anestesi ≠ Sudah` ⇒ **simpan ditolak** (bukan sekadar pesan), dan `jenis_kelamin=P` usia < 60 (dihitung dari `tanggal_lahir` submitted) ⇒ `hamil_bulan` wajib; `hamil_bulan` di-null-kan selain P. Literal KONSEP: `asa => in:1,2,3,4,5,6` (opsi `SelectOption::asa_kelas` value '1'..'6'), `tipe_anestesia => in:Umum,Regional,Lokal,Sedasi` (opsi blade difilter dari `jenis_anestesia` 6 → 4), alergi `in:Ada,Tidak`, `required_if` gangguan_pernafasan/alergi_obat_timbul/perlu_alat_bantu. Prefill create: nama/tgl lahir/jk dari pasien (readonly). Badge klinis (TD >180/110, trombosit <100k, gula >200) murni tampilan JS. `mobilitas` legacy ternyata radio Ya/Tidak ("masalah mobilitas leher") — KONSEP sebut "select" tanpa opsi → dipakai radio Ya/Tidak. |
| 2026-10-14 | **Form 87 Asesmen Post-Anestesi & Sedasi ✅** (46 variabel, E2E ALL OK, lulus pertama kali). Seeder: mapping 87 = 46 baris + akses 87 (Dokter & Perawat sama-sama full CRUD — form band bedah dengan Perawat CRUD penuh). Keempat **total skor (Aldrete/Bromage/Steward/PADSS) dan `kriteria_pemulihan` dihitung ulang di server** via `SkorPemulihanHelper::hitung()` — nilai browser dibuang; kriteria = `Terpenuhi` bila Aldrete > 8, selain `Belum`. **PADSS hanya 2 item** (`vas`+`aktivitas`, range 0–3 sesuai tabel field + helper) — catatan komputasi KONSEP menyebut 5 item tapi blok Mapping §5 hanya 2 var; ikuti mapping. Checkbox grup: `komplikasi_*` = `nullable\|in:0,1`; `keselamatan_*` = **`required\|in:0,1`** (rule mengandung `required` → TIDAK dilewati harness, dikirim nilai pertama `'1'` via `optsFromRule`; jangan pasang guard server mewajibkan nilai lain). Pola hidden `0` + checkbox `1`. Field kondisional (`skala_nyeri`, `intake_oral_catatan`, `mobilisasi_catatan`) di-set `null`, bukan di-unset. Panel kriteria = hidden input + badge warna live (JS cerminan ambang helper; bukan radio pilihan user — KONSEP: dihitung server). `ruangan_tujuan` pakai komponen `x-select-ruang-perawatan` (value `bagian_id`, rule `exists:bagian,bagian_id`). |
| 2026-10-14 | **Form 88 Monitoring Anestesi & Sedasi ✅** (106 variabel, E2E ALL OK, lulus pertama kali). Seeder: mapping 88 = 6 var utama + loop `for i=1..20` × 5 kolom (objek 810–819) + akses 88 (Dokter & Perawat full CRUD). **Rules 106 entri DITULIS LITERAL penuh (bukan loop)** — `ruleMap()` harness (`tools/lib_pay.php`) hanya mem-parse `'key' => 'rule'` string literal dari sumber; `$rules['x_'.$i] = ...` di loop TIDAK terdeteksi → payload harness kosong utk baris monitoring. Baris 1..4 (menit 0/5/15/30) `required`, baris 5..20 `nullable`. Opsi kesadaran **AVPU literal** `in:Alert,Voice,Pain,Unresponsive` (tidak ada key SelectOption-nya). Guard `after()`: `jam_selesai >= jam_mulai` (harness kirim `'10:00'` utk semua `H:i` → == lolos). `kejadian_kritis` = `required_if:status_monitoring,Ongoing`. `lampiran_monitoring` = `nullable\|string\|max:60000` + normalisasi hanya `data:image/...` (input file tanpa `name` → JS FileReader → hidden input; preview + tombol hapus). Tabel 20 baris label menit statis (UI saja, tidak ada kolom waktu di mapping). |
| 2026-10-14 | **Form 90 Check List Kesiapan Anestesi ✅** (9 variabel, E2E ALL OK). Seeder: mapping 90 = 9 baris (objek 834–841) + akses 90 (Dokter & Perawat full CRUD). Penilaian 5 butir `required\|in:Ok,Tidak Ok` (radio Ok/Tidak Ok, bukan checkbox — legacy upload scan, desain native baru). **Dua guard `after()`**: (1) hard gate `consent_dokumentasi_ok = 'Tidak Ok'` ⇒ store/update **ditolak** (KONSEP §8 — keputusan ini berbeda dgn 89/97 yang `⛔` total; di sini hanya satu butir + warna peringatan UI); (2) `catatan_kesiapan` min. 5 karakter bila ada butir `Tidak Ok` (rule literal `required\|string` utk tabel field "wajib", guard menambah syarat bila ada ketidaksapan). `mesin_anestesi_keterangan` = `required_if:mesin_anestesi_ok,Tidak Ok` — **parameter rule mengandung SPASI** (`Tidak Ok`) — Laravel & harness menanganinya (kutipan string utuh; `optsFromRule` kirim `'Ok'` utk field `in:`). **BUG pertama kali ditemukan**: closure `$validator->after(function ($validator) {...})` memanggil `$request->all()` TANPA `use ($request)` → `ErrorException: Undefined variable $request` + `VALIDASI 0` — closure wajib `use ($request)` atau pakai helper global `request()` (dipakai form 87/88 aman krn `$this->input()` → `request()`). |
| 2026-10-14 | **Form 91 Pengkajian Pre/Intra/Post Operasi ✅** (25 variabel, E2E ALL OK). Seeder: mapping 91 = 25 baris (objek 842–851 + reuse 13, 17) + akses 91 (Dokter & Perawat full CRUD). Satu form dgn `fase` radio `in:Pre,Intra,Post` — field lintas-fase pakai `required_if:fase,...` (Pre 9 var, Intra 5 var, Post 4 var, 4 textarea perencanaan selalu wajib) dan **field di luar fase terpilih di-set `null`** (KONSEP: "dibuang di `filteredData()`" — null, bukan unset, supaya baris `emr_detail` tetap ada utk `simpan` penuh). Opsi select literal-parsial: `jenis_operasi`/`status_emosi`/`posisi_operasi`/`keadaan_umum_intra`(→`keadaan_umum_pulih`)/`asa_intra`(→`asa_kelas` '1'..'6')/`mobilisasi_post`(→`mobilisasi_operasi`) pakai **array rule `['required_if:...', Rule::in(array_column(SelectOption::get(...)))]`** — splitEntries harness paren-aware menangani koma internal; `ruangan_pulih` pakai `x-select-ruang-perawatan` + `exists:bagian,bagian_id`. `skala_cemas` `integer\|between:0,4` (Hamilton — legacy memakai 6 label kategorikal, ikut KONSEP). `status_emosi`: legacy `pengkajian_perioperatif.php` 6 nilai vs key `SelectOption::status_emosi` 4 nilai → pakai key SelectOption. **E2E gagal 1×**: `index=500 View [moduls.EMR.PengkajianOperasi.index] not found` — controller+seeder selesai sebelum view dibuat; **buat view sebelum jalankan `e2e.php`**. |
| 2026-10-14 | **Form 92 Laporan Operasi ✅** (31 variabel, E2E ALL OK, lulus pertama kali — urutan: seeder → controller → view → e2e). Seeder: mapping 92 = 25 baris + `for` loop 6 `komplikasi_operasi_*` (objek 852–866 + **983/984 sudah di-seed terpisah** utk jam selesai & lama operasi); akses 92 (Dokter full, **Perawat 0/1/0/0 read-only**). `lama_operasi` **TURUNAN dihitung server** (selisih jam → `'N menit'`, input user dibuang; preview JS readonly). Guard `after()` `jam_selesai >= jam_mulai` (pakai `request()` global — aman tanpa `use`). Literal: `jenis_operasi => in:Efektif,Darurat,Semi Darurat`, `jumlah_transfusi => required_if:transfusi_darah,Ya\|integer\|min:1` (**`gte:` TIDAK didukung harness → pakai `min:`** — branch min+integer di `lib_pay` memberi angka valid), `jenis_implan`/`nama_specimen` `required_if`. Kondisional (jumlah/jenis_implan/nama_specimen) di-null-kan selain status `Ya`; komplikasi ternormalisasi `'0'`. Radio `jenis_anas_umum`/`jenis_anas_lokal` = `Ya/Tidak` (legacy tidak punya field ini — desain native baru). Badge "Pendarahan signifikan" (`kehilangan_darah > 500`) = tampilan JS. Select utk `ruangan_pasca_operasi` = `x-select-ruang-perawatan`, dokter = `x-select_dokter` + `exists:pegawai,pegawai_id`. |
| 2026-10-14 | **Form 93 Catatan Bedah ✅** (28 variabel, E2E ALL OK, lulus pertama kali). Seeder: mapping 93 = **28** baris (objek 867–882; komentar seeder sempat salah tulis "31" — sudah dikoreksi) + akses 93 (Dokter full, Perawat 0/1/0/0). Catatan bedah tunggal tanpa guard after() — hanya validasi literal: `petugas_pendamping` = `x-select_pegawai` + `exists:pegawai,pegawai_id` (**komponen sudah ada** — dipakai SBAR/CatatanMedisVisum; filter `profesiId` opsional, dibiarkan tanpa filter), `keadaan_umum` → `keadaan_umum_pulih`, `posisi_operasi` → `posisi_operasi`, `jumlah_cairan` `required\|numeric\|min:0` (harness branch min+numeric → kirim 1), **`jenis_operasi => in:Efektif,Darurat` (hanya 2 nilai — jangan pakai `SelectOption::jenis_operasi` utk opsi blade, difilter jadi 2)**. Semua field wajib → tanpa kondisional/normalisasi. |
| 2026-10-14 | **Form 94 Transfer Pasien Antar Ruangan ✅** (46 variabel, E2E ALL OK, lulus pertama kali — urutan: seeder → controller → view → e2e). Seeder: mapping 94 = 41 baris literal + `for` loop 5 `dokter_rawat_1..5` (objek 883–897 + reuse 17, 6, 7, 10, 12, 11, 51, 54–57); akses 94 (Dokter & Perawat **sama-sama full CRUD**). **Nama variabel ikut mapping `peralatan` (bukan `peralatan_transfer` — kolom KONSEP berbeda; preseden form 87)**. Guard `after()`: `ruangan_tujuan ≠ ruangan_asal` — **harness `tools/lib_pay.php` diberi special-case khusus** `ruangan_tujuan` (default kirim id sama utk keduanya → guard selalu gagal; kini pilih id berbeda dari `ruangan_asal`). `dokumen_pendukung => required\|array\|min:1` (6 checkbox legacy `Pengantar IGD, General Consent, Hasil Laboratorium, Hasil Radiologi, Konvensional, CT Scan`) → server `implode(', ', ...)` jadi string scalar SATU baris (kompatibel `emr_detail`; edit pecah dgn `explode(', ')`). `kategori_transfer => in:Rawat Inap,Rawat Jalan,IGD`. 6 field `exists:pegawai,pegawai_id` (dokter_pengantar + dokter_rawat_1..5 + nama_penerima — harness kirim id sama, tanpa guard lintas). Tanda vital (sistolik/diastolik/nadi/pernapasan) `integer\|min:0` **TANPA between** (KONSEP: di luar rentang → badge JS non-blocking; `min:0` wajib utk harness — `integer` murni tanpa min/max → fallback `'Data Uji'` → gagal validasi); `suhu numeric\|min:0`; GCS `between` klinis baku (eye 1–4, verbal 1–5, motorik 1–6, score 3–15 — mid harness lolos). `kesadaran` = `Rule::in(array_column(SelectOption::get('kesadaran')))` (array rule, aman `splitEntries`). Select: `x-select-ruang-perawatan` ×2, `x-select_dokter` ×6, `x-select_pegawai` (nama_penerima). |
| 2026-10-14 | **Form 95 Penandaan Lokasi Operasi ✅** (10 variabel, E2E ALL OK, lulus pertama kali — urutan: seeder → controller → view → e2e). Seeder: mapping 95 = 9 var KONSEP + **`alasan_penandaan` (objek_id NULL)** — var tanpa objek utk safety gate KONSEP §13 ("`konfirmasi_pasien = Tidak` ⇒ wajib alasan") yang terlewat dari tabel field & blok Mapping §13 (objek 898–902 habis utk 9 var; preseden objek_id nullable — var tanpa objek, form 27); akses 95 **hanya Dokter (profesi 1) full CRUD**. Literal: `sisi_operasi => in:Kanan,Kiri,Bilateral,Tanpa Sisi` (KONSEP "Nao sides" → pakai `Tanpa Sisi`), `konfirmasi_pasien => in:Ya,Tidak` (typo KONSEP "pasieneniumsetujui" = pasien menyetujui), `alasan_penandaan => required_if:konfirmasi_pasien,Tidak\|string\|max:2000` (harness kirim `konfirmasi_pasien='Ya'` — nilai pertama `in:` → cabang Tidak inert utk E2E, preseden guard inert). **`jenis_penandaan` opsi DESAIN baru** — KONSEP hanya menulis "radio" tanpa daftar, legacy (`penandaan_lokasi_operasi.php`) cuma upload gambar: `in:Spidol Penanda Kulit,Plester Penanda,Sayatan Kulit`. `petugas_penanda` = `x-select_dokter` + `exists:pegawai,pegawai_id`. `lampiran_penandaan` `nullable\|string\|max:60000` dinormalisasi hanya `data:image/...` (input file tanpa name → JS FileReader → hidden + preview + tombol hapus — pola form 88); UI alasan hanya tampil saat konfirmasi = Tidak (mirror `required_if`). |
| 2026-10-14 | **Form 96 Penandaan Pasien ✅** (14 variabel, E2E ALL OK, lulus pertama kali — urutan: seeder → controller → view → e2e). Seeder: mapping 96 = 9 var KONSEP + loop `alergi_1..5` (objek 903–910); akses 96 (Dokter & Perawat full CRUD). Checkbox pola legacy **hidden `0` + checkbox `1`** → rules `penanda_* => required\|in:0,1` (rule mengandung `required` → TIDAK dilewati harness, `optsFromRule` kirim `'1'` utk keempat penanda → **guards diuji E2E**, bukan inert). Kondisional: `jenis_hepatitis => required_if:penanda_hepatitis,1\|in:B,C`, `alasan_geriatri => required_if:penanda_geriatri,1\|string\|max:2000`, `alergi_1..5 => nullable\|string\|max:2000` + **guard `after()`**: `penanda_alergi='1'` ⇒ minimal satu `alergi_N` non-kosong (KONSEP §14: "minimal satu" — bukan per-field required; guard lolos E2E krn harness isi 'Data Uji' di semua alergi). Field kondisional di-`null`-kan bila penanda terkait ≠ '1' (pola P5: alergi_1..5, jenis_hepatitis, alasan_geriatri). View: 4 panel penanda, tiap panel toggle blok detail via `style.display` + checkbox tersembunyi. |
| 2026-10-14 | **Form 98 Surveilans Luka Insisi ✅** (23 variabel, E2E ALL OK, lulus pertama kali — urutan: seeder → controller → view → e2e). Seeder: mapping 98 = 12 var literal + loop `profilaksis_1..5` & `tanda_infeksi_1..5` + **`keterangan_trauma` (objek_id NULL)** — var tanpa objek utk safety gate KONSEP §16 ("`trauma_pada` = trauma ⇒ wajib keterangan") yang terlewat dari tabel field (objek 924–936 = 13 objek habis utk 12 var; preseden form 95 `alasan_penandaan`); akses 98 (Dokter & Perawat full CRUD, flags 1/0/0/0 = RI only). **3 guard `after()`**: (1) `tgl_operasi >= tgl_mrs` (harness kirim `date` sama → == lolos); (2) `trauma_pada ∈ {Trauma Tertutup, Trauma Terbuka}` ⇒ `keterangan_trauma` wajib — **KONSEP menulis `= 'Ada'` (nilai legacy) sedangkan opsi dipakai SelectOption `trauma_pada` 3 nilai** → guard diadaptasi bila ≠ `Elektif` (harness kirim 'Trauma Tertutup' → guard teruji E2E); (3) `klasifikasi_luka >= II` (roman I–IV) ⇒ minimal satu `tanda_infeksi_* = '1'` (harness kirim 'I' → inert utk E2E). **`klasifikasi_luka` memakai literal grade SSI KONSEP §16** (`in:I,II,III,IV`, label kriteria Grade I–IV) — key SelectOption `klasifikasi_luka` berisi wound class I–IV (values sama, label beda) TIDAK dipakai (belum dipakai form lain). **Item 2 grup checkbox = desain** (KONSEP hanya menulis nama grup): profilaksis = 5 ukuran pencegahan SSI (antibiotik profilaksis, persiapan kulit, pencukuran clipper, normotermia, kontrol glikemi; label provisional); tanda_infeksi = 5 tanda dari legacy `post_op_insisi.php` (nyeri/tekan, demam, kemerahan, bengkak, drainase purulen). `kamar_operasi` = **nama_bed** dari tabel `bed` bagian `INSTALASI BEDAH SENTRAL` (lookup per nama — legacy memakai bedah+endoscopy+cathlab, dua unit terakhir tidak ada di DB lokal) + `exists:bed,nama_bed`. `drainase_luka => in:Ada,Tidak Ada` (desain baru — legacy tanpa radio). Radio `dokter_bedah` = `kualifikasi_bedah` (Spesialis/Subspesialis/Dokter Umum), `trauma_pada` = SelectOption 3 nilai. Harness tanpa perubahan. pint PASS. |
| 2026-10-14 | **Form 99 Catatan Anestesia ✅** (32 variabel, E2E ALL OK, lulus pertama kali — urutan: seeder → controller → view → e2e). Seeder: mapping 99 = 32 var ikut blok Mapping KONSEP §17 (objek 937–954 + reuse 6, 7, 10, 11, 12, 15, 51); akses 99 (Dokter & Perawat full CRUD, flags 1/0/1/0 RI+IGD). **Variabel `ruang_tujuan` (bukan `pesakit_ruang` yang tertulis di tabel field — preseden form 87/94, kolom KONSEP beda → ikut Mapping)**. `lama_anestesia` **TURUNAN** — server hitung selisih jam → `'N menit'` (pola form 92; input user dibuang, preview JS readonly + hidden). **3 guard `after()`**: (1) `jam_selesai >= jam_mulai` (harness kirim '10:00' keduanya → lolos); (2) `caudel` wajib bila `jenis_anestesi ∈ {Epidural, Spinal}` — `caudel` rule `nullable` + guard (tabel field seragam tulis "wajib", validasi §17 menentukan conditional — preseden form 91) + **di-null-kan selain Epidural/Spinal** (harness kirim 'Umum' → inert); (3) chain `kejadian_tidak_terduga` non-kosong ⇒ `penanganan_intraop` wajib (`penanganan_intraop` `nullable` + guard; kejadian sendiri `required` per tabel). Literal: `jenis_anestesi => in:Umum,Regional,Epidural,Spinal,Sedasi` (5 nilai KONSEP — key SelectOption `jenis_anestesia` punya 6 dgn Lokal, TIDAK dipakai utk rule/opsi), `airway => in:Ada,Tidak_ada` (**Difficult airway — label & nilai dari legacy `catatan_anestesia.php`**), `ventilasi => in:Spontan,Assisted,Controlled` (KONSEP 3 nilai; legacy checkbox Spontan/Manual/Controlled → redesain radio), `posisi` select = SelectOption `posisi_operasi` (6 nilai; KONSEP tulis "select" tanpa opsi), `asa => in:1,2,3,4,5,6` (SelectOption `asa_kelas`), `sirkuit` text bebas (legacy checkbox Closed circuit/Jackson-Rees → ikuti KONSEP text). Tanda vital `integer|min:0` tanpa between, `suhu numeric|min:0`, `kesadaran` array rule SelectOption (pola form 94). Select: `x-select_dokter` + `x-select_pegawai` + `x-select-ruang-perawatan`. pint PASS. |
| 2026-10-14 | **Form 101 Asesmen Katarak ✅** (62 variabel, E2E ALL OK — urutan: seeder → controller → view → e2e; E2E pertama `index=500` karena typo blade, kedua lulus). Seeder: mapping 101 = 12 var KONSEP §19 + loop 5×10 baris (objek 968–979 + reuse 13, 51, 6, 7, 10, 11, 12); akses 101 (Dokter & Perawat full CRUD, flags 0/1/0/0 RJ only). **1 guard `after()`**: `resiko_jatuh = 'Risiko Tinggi'` ⇒ `intervensi_pre_operasi` min. 5 karakter (pola form 90 — rule field tetap `required` per tabel §19, guard menambah syarat ketat; harness kirim nilai pertama `in:` = 'Risiko Tinggi' + 'Data Uji' (8 char) → guard teruji E2E). Literal: `resiko_jatuh => in:Risiko Tinggi,Tidak Berisiko` (**opsi KONSEP §19 — legacy `asesmen_katarak.php` memakai 'Risiko Jatuh Tinggi'/'Tidak Berisiko Jatuh', beda generasi; Validasi §19 sendiri merujuk 'Risiko Tinggi' → ikuti KONSEP**), `skala_nyeri` & `skala_nyeri_1..5 => integer\|between:0,10` (**VAS 0–10 dari legacy helper `skala_nyeri_vas_simple()`**), `kesadaran` array rule SelectOption, `dpjp_utama` = `x-select_dokter` + `exists:pegawai,pegawai_id`. Baris obat (`jam_pemberian_obat_1..5`, `nama_obat_1..5`) **`nullable`** (tabel §19 "wajib=tidak"); baris TTV+visus 1..5 semua `required` per tabel (`integer\|min:0` utk angka — aturan harness; `suhu numeric\|min:0`; visus text bebas `required\|string`). `diagnosa_keperawatan required` (Validasi §19 "minimal 1 baris" = non-kosong). **BUG pelajaran — typo `?>` vs `}}` di blade**: `value="{{ $v('visus_kiri_'.$i) ?>"` (tutup echo salah ketik `?>`) membuat regex `compileEscapedEchos` (`/\{\{\s*(.+?)\s*\}\}/s`) menelan lintas baris sampai `{{ $message }}` berikutnya → `syntax error, unexpected ";"` saat `index` padahal `simpan=62/62` sukses; diagnosis via `Blade::compileString()` per potongan (bisect) — kalau E2E gagal `index=500` dgn syntax error view, periksa dulu kecocokan `{{ ... }}`. pint PASS. |
| 2026-10-14 | **Fase P5 (form wajib) selesai** — seluruh 20 form P5 kecuali 3 `⛔` (89, 97, 100) terverifikasi E2E: **52, 53, 85–88, 90–96, 98, 99, 101, 102–105**. Regresi penuh `tools/e2e.php`: **FAILURES: 3 = baseline pre-existing** (`order_resep`, `order_patologi`, `order_mikrobiologi` — dibuktikan via `e2e_order.php` **ALL OK**); seluruh form lain `simpan` penuh + `index/upd/del` OK. `tools/e2e_order.php` ALL OK. pint PASS. |
| 2026-10-10 | **Fase P1 selesai**: form 16, 17, 18, 19, 20, 21, 57 → ✅. Seeder (objek 178–235, menu 6–7, sub 33/34/53/54) + controller + blade. Semua terverifikasi E2E: store/mapping/index/update/delete + gate akses. 2 bug nyata ditemukan & diperbaiki (`Rule::requiredIf` wrong-arity di TriageIgd; `$metaTriase` undefined). |

