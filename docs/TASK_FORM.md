# TASK FORM EMR — NusaMedika

**Status dokumen:** sumber kebenaran progress implementasi form EMR.
**Terakhir diperbarui:** 2026-10-10
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
| **P2** | Asuhan Keperawatan | 23 | 23 | 0 | 0 | 0 | 0% |
| **P3** | Gizi, Farmasi & Penunjang | 13 | 12 | 0 | 0 | 1 | 0% |
| **P4** | Kebidanan & MCU | 26 | 25 | 0 | 0 | 1 | 0% |
| **P5** | Bedah, Anestesi & ICU | 23 | 20 | 0 | 0 | 3 | 0% |
| **P6** | Onkologi, Psikososial & Formulir | 22 | 22 | 0 | 0 | 0 | 0% |
| **Total** | 6 fase | **114** | **109** | 0 | 0 | **5** | **0%** |



> Angka di tabel ini **dipertahankan manual**. Saat agent menutup task, perbarui baris fase yang sesuai.

---

## 5. Task Prasyarat (`T00`)

| Task | Prasyarat | Dibutuhkan oleh | Status | Owner |
|---|---|---|---|---|
| `PRE-01` | Seed profesi baru di `MasterPegawaiSeeder`: 14 Kerohanian, 15 Patolog Anatomi, 16 Dokter Intensivis, 17 Dokter Anestesi, 18 Perawat Hemodialisa | form 50–53, 85–90, 99, 102–104, 112–115 | ⬜ |  |
| `PRE-02` | Migration `order_patologi` + `order_patologi_detail` (dual-driver) | form 49 | ⬜ |  |
| `PRE-03` | Migration `order_mikrobiologi` + `order_mikrobiologi_detail` (dual-driver) | form 50 | ⬜ |  |
| `PRE-04` | Migration `order_poct` + `order_poct_detail` (dual-driver) | form 51 | ⬜ |  |
| `PRE-05` | Tabel + CRUD + seeder `master_diet` dan `master_diagnosa_diet` | form 43 | ⬜ |  |
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
| `EMR-022` | Rencana Asuhan Keperawatan | `rencana_asuhan_keperawatan` | `2.73.26` | 1/0/0/0 | ⬜ |  | KONSEP_ASUHAN_KEPERAWATAN_LANJUTAN.md | `EMR/RencanaAsuhanKeperawatan/` · `moduls/EMR/RencanaAsuhanKeperawatan/` | — |
| `EMR-023` | Issues Keperawatan | `issues_keperawatan` | `2.73.27` | 1/0/0/0 | ⬜ |  | KONSEP_ASUHAN_KEPERAWATAN_LANJUTAN.md | `EMR/IssuesKeperawatan/` · `moduls/EMR/IssuesKeperawatan/` | — |
| `EMR-024` | Intake | `intake` | `2.93.46` | 1/0/0/0 | ⬜ |  | KONSEP_CAIRAN_BALANCE.md | `EMR/Intake/` · `moduls/EMR/Intake/` | — |
| `EMR-025` | Output | `output` | `2.93.47` | 1/0/0/0 | ⬜ |  | KONSEP_CAIRAN_BALANCE.md | `EMR/Output/` · `moduls/EMR/Output/` | — |
| `EMR-026` | Monitoring Cairan | `monitoring_cairan` | `9.94` | 1/0/0/0 | ⬜ |  | KONSEP_CAIRAN_BALANCE.md | `EMR/MonitoringCairan/` · `moduls/EMR/MonitoringCairan/` | — |
| `EMR-027` | Penilaian Nyeri | `penilaian_nyeri` | `9.113.66` | 1/1/1/0 | ⬜ |  | KONSEP_NYERI.md | `EMR/PenilaianNyeri/` · `moduls/EMR/PenilaianNyeri/` | — |
| `EMR-028` | Kesadaran & Pemberian O2 | `kesadaran_oksigen` | `9.95` | 1/0/0/0 | ⬜ |  | KONSEP_CAIRAN_BALANCE.md | `EMR/KesadaranOksigen/` · `moduls/EMR/KesadaranOksigen/` | — |
| `EMR-029` | Handover / Serah Terima | `handover` | `2.74.28` | 1/0/0/0 | ⬜ |  | KONSEP_ASUHAN_KEPERAWATAN_LANJUTAN.md | `EMR/Handover/` · `moduls/EMR/Handover/` | — |
| `EMR-030` | Monitoring & Evaluasi Risiko Jatuh | `monitoring_risiko_jatuh` | `8.133.86` | 1/0/0/0 | ⬜ |  | KONSEP_RISIKO_JATUH_LANJUTAN.md | `EMR/MonitoringRisikoJatuh/` · `moduls/EMR/MonitoringRisikoJatuh/` | — |
| `EMR-031` | Asesmen Kebutuhan Edukasi | `asesmen_kebutuhan_edukasi` | `2.75.29` | 1/1/0/0 | ⬜ |  | KONSEP_ASUHAN_KEPERAWATAN_LANJUTAN.md | `EMR/AsesmenKebutuhanEdukasi/` · `moduls/EMR/AsesmenKebutuhanEdukasi/` | — |
| `EMR-032` | Penilaian Risiko Dekubitus (Norton) | `penilaian_risiko_dekubitus` | `8.134.87` | 1/0/0/0 | ⬜ |  | KONSEP_RISIKO_JATUH_LANJUTAN.md | `EMR/PenilaianRisikoDekubitus/` · `moduls/EMR/PenilaianRisikoDekubitus/` | — |
| `EMR-033` | Barthel Index | `barthel_index` | `8.134.88` | 1/0/0/0 | ⬜ |  | KONSEP_RISIKO_JATUH_LANJUTAN.md | `EMR/BarthelIndex/` · `moduls/EMR/BarthelIndex/` | — |
| `EMR-034` | Asesmen Status Fungsional | `asesmen_status_fungsional` | `8.76.31` | 1/1/0/0 | ⬜ |  | KONSEP_ASUHAN_KEPERAWATAN_LANJUTAN.md | `EMR/AsesmenStatusFungsional/` · `moduls/EMR/AsesmenStatusFungsional/` | — |
| `EMR-035` | Pengkajian Keperawatan Kritis | `pengkajian_keperawatan_kritis` | `2.75.30` | 1/0/1/0 | ⬜ |  | KONSEP_ASUHAN_KEPERAWATAN_LANJUTAN.md | `EMR/PengkajianKeperawatanKritis/` · `moduls/EMR/PengkajianKeperawatanKritis/` | — |
| `EMR-036` | Metode Nyeri | `metode_nyeri` | `9.113.67` | 1/1/1/0 | ⬜ |  | KONSEP_NYERI.md | `EMR/MetodeNyeri/` · `moduls/EMR/MetodeNyeri/` | — |
| `EMR-037` | Body Map Nyeri | `body_map_nyeri` | `9.114` | 1/1/1/0 | ⬜ |  | KONSEP_NYERI.md | `EMR/BodyMapNyeri/` · `moduls/EMR/BodyMapNyeri/` | — |
| `EMR-038` | Skrining Nyeri Perina | `skrining_nyeri_perina` | `9.113.68` | 1/0/0/0 | ⬜ |  | KONSEP_NYERI.md | `EMR/SkriningNyeriPerina/` · `moduls/EMR/SkriningNyeriPerina/` | — |
| `EMR-054` | Catatan Keperawatan Rawat Jalan | `catatan_keperawatan_rajal` | `8.77.37` | 0/1/0/0 | ⬜ |  | KONSEP_ASUHAN_KEPERAWATAN_LANJUTAN.md | `EMR/CatatanKeperawatanRajal/` · `moduls/EMR/CatatanKeperawatanRajal/` | — |
| `EMR-055` | Riwayat Penyakit | `riwayat_penyakit` | `8.77.35` | 1/1/1/0 | ⬜ |  | KONSEP_ASUHAN_KEPERAWATAN_LANJUTAN.md | `EMR/RiwayatPenyakit/` · `moduls/EMR/RiwayatPenyakit/` | — |
| `EMR-056` | Riwayat Keluarga | `riwayat_keluarga` | `8.77.36` | 1/1/1/0 | ⬜ |  | KONSEP_ASUHAN_KEPERAWATAN_LANJUTAN.md | `EMR/RiwayatKeluarga/` · `moduls/EMR/RiwayatKeluarga/` | — |
| `EMR-123` | Asesmen Akhir Kehidupan | `asesmen_akhir_kehidupan` | `8.76.33` | 1/0/1/0 | ⬜ |  | KONSEP_ASUHAN_KEPERAWATAN_LANJUTAN.md | `EMR/AsesmenAkhirKehidupan/` · `moduls/EMR/AsesmenAkhirKehidupan/` | — |
| `EMR-124` | Uji Fungsi & Asesment | `uji_fungsi_asesment` | `8.76.34` | 0/1/0/1 | ⬜ |  | KONSEP_ASUHAN_KEPERAWATAN_LANJUTAN.md | `EMR/UjiFungsiAsesment/` · `moduls/EMR/UjiFungsiAsesment/` | — |
| `EMR-125` | Respon Emosi & Status Mental | `respon_emosi` | `8.76.32` | 1/1/1/0 | ⬜ |  | KONSEP_ASUHAN_KEPERAWATAN_LANJUTAN.md | `EMR/ResponEmosi/` · `moduls/EMR/ResponEmosi/` | — |

## 9. Fase P3 — Gizi, Farmasi & Penunjang

| Task | Nama Form | `slug` | `id_dash_menu` | ri/rj/igd/mcu | Status | Owner | Dokumen Konsep | Folder Controller/View | Catatan / Prasyarat |
|---|---|---|---|---|---|---|---|---|---|
| `EMR-039` | Asesmen Gizi | `asesmen_gizi` | `10.153` | 1/1/1/0 | ⬜ |  | KONSEP_ASESMEN_GIZI.md | `EMR/AsesmenGizi/` · `moduls/EMR/AsesmenGizi/` | — |
| `EMR-040` | Detail Nutrisi | `detail_nutrisi` | `10.154` | 1/0/0/0 | ⬜ |  | KONSEP_ASESMEN_GIZI.md | `EMR/DetailNutrisi/` · `moduls/EMR/DetailNutrisi/` | — |
| `EMR-041` | Rekap Asesmen Nutrisi | `rekap_asesmen_nutrisi` | `10.155` | 1/0/0/0 | ⛔ |  | KONSEP_ASESMEN_GIZI.md | `EMR/RekapAsesmenNutrisi/` · `moduls/EMR/RekapAsesmenNutrisi/` | Legacy tidak punya tabel konversi ambang SGA — `kesimpulan` tak bisa diisi |
| `EMR-042` | Pesan Makanan & Monitoring Asupan | `pesan_makanan` | `10.156` | 1/0/0/0 | ⬜ |  | KONSEP_ASESMEN_GIZI.md | `EMR/PesanMakanan/` · `moduls/EMR/PesanMakanan/` | — |
| `EMR-043` | Diet Pasien | `diet_pasien` | `10.157` | 1/1/1/0 | ⬜ |  | KONSEP_ASESMEN_GIZI.md | `EMR/DietPasien/` · `moduls/EMR/DietPasien/` | Butuh **PRE-05** |
| `EMR-044` | ADIME | `adime` | `10.158` | 1/0/0/0 | ⬜ |  | KONSEP_ASESMEN_GIZI.md | `EMR/Adime/` · `moduls/EMR/Adime/` | — |
| `EMR-045` | Daftar Terapi Obat | `daftar_terapi_obat` | `11.173` | 1/1/1/0 | ⬜ |  | KONSEP_FARMAKASI.md | `EMR/DaftarTerapiObat/` · `moduls/EMR/DaftarTerapiObat/` | — |
| `EMR-046` | Rekonsiliasi Obat | `rekonsiliasi_obat` | `11.174` | 1/1/1/0 | ⬜ |  | KONSEP_FARMAKASI.md | `EMR/RekonsiliasiObat/` · `moduls/EMR/RekonsiliasiObat/` | — |
| `EMR-047` | Telaah Resep | `telaah_resep` | `11.175` | 0/1/1/0 | ⬜ |  | KONSEP_FARMAKASI.md | `EMR/TelaahResep/` · `moduls/EMR/TelaahResep/` | — |
| `EMR-048` | Telaah Obat | `telaah_obat` | `11.176` | 1/0/0/0 | ⬜ |  | KONSEP_FARMAKASI.md | `EMR/TelaahObat/` · `moduls/EMR/TelaahObat/` | — |
| `EMR-049` | Order Patologi | `order_patologi` | `4.193` | 1/1/1/0 | ⬜ |  | KONSEP_PENUNJANG_MEDIS_LANJUTAN.md | `EMR/OrderPatologi/` · `moduls/EMR/OrderPatologi/` | Butuh **PRE-02** |
| `EMR-050` | Order Mikrobiologi | `order_mikrobiologi` | `4.194` | 1/1/1/0 | ⬜ |  | KONSEP_PENUNJANG_MEDIS_LANJUTAN.md | `EMR/OrderMikrobiologi/` · `moduls/EMR/OrderMikrobiologi/` | Profesi 15 baru (PRE-01) · **PRE-03** |
| `EMR-051` | Order POCT | `order_poct` | `4.195` | 1/1/1/0 | ⬜ |  | KONSEP_PENUNJANG_MEDIS_LANJUTAN.md | `EMR/OrderPoct/` · `moduls/EMR/OrderPoct/` | Butuh **PRE-04** |

## 10. Fase P4 — Kebidanan & MCU

| Task | Nama Form | `slug` | `id_dash_menu` | ri/rj/igd/mcu | Status | Owner | Dokumen Konsep | Folder Controller/View | Catatan / Prasyarat |
|---|---|---|---|---|---|---|---|---|---|
| `EMR-059` | Asesmen Khusus Kebidanan & Penyakit Kandungan | `asesmen_kebidanan` | `13.253` | 1/1/0/0 | ⬜ |  | KONSEP_OBSTETRI_NEONATAL.md | `EMR/AsesmenKebidanan/` · `moduls/EMR/AsesmenKebidanan/` | — |
| `EMR-060` | Pengkajian Awal Keperawatan Perina | `pengkajian_perina` | `13.254` | 1/0/0/0 | ⬜ |  | KONSEP_OBSTETRI_NEONATAL.md | `EMR/PengkajianPerina/` · `moduls/EMR/PengkajianPerina/` | — |
| `EMR-061` | Pengkajian Awal Keperawatan Non-Perina | `pengkajian_non_perina` | `13.255` | 1/0/0/0 | ⬜ |  | KONSEP_OBSTETRI_NEONATAL.md | `EMR/PengkajianNonPerina/` · `moduls/EMR/PengkajianNonPerina/` | — |
| `EMR-062` | Kala I-IV | `kala_persalinan` | `13.256` | 1/0/0/0 | ⬜ |  | KONSEP_OBSTETRI_NEONATAL.md | `EMR/KalaPersalinan/` · `moduls/EMR/KalaPersalinan/` | — |
| `EMR-063` | Riwayat Persalinan Sekarang | `riwayat_persalinan` | `13.257` | 1/0/0/0 | ⬜ |  | KONSEP_OBSTETRI_NEONATAL.md | `EMR/RiwayatPersalinan/` · `moduls/EMR/RiwayatPersalinan/` | — |
| `EMR-064` | Pelaksanaan Bayi Baru Lahir | `bayi_baru_lahir` | `13.258` | 1/0/0/0 | ⬜ |  | KONSEP_OBSTETRI_NEONATAL.md | `EMR/BayiBaruLahir/` · `moduls/EMR/BayiBaruLahir/` | — |
| `EMR-065` | Riwayat Resusitasi Bayi | `riwayat_resusitasi_bayi` | `13.259` | 1/0/0/0 | ⬜ |  | KONSEP_OBSTETRI_NEONATAL.md | `EMR/RiwayatResusitasiBayi/` · `moduls/EMR/RiwayatResusitasiBayi/` | — |
| `EMR-066` | Penilaian Bayi dengan APGAR Score | `penilaian_bayi_dengan_apgar_score` | `13.260` | 1/0/0/0 | ⬜ |  | KONSEP_OBSTETRI_NEONATAL.md | `EMR/PenilaianBayiDenganApgarScore/` · `moduls/EMR/PenilaianBayiDenganApgarScore/` | — |
| `EMR-067` | Down Score (Neonatal) | `down_score` | `13.261` | 1/0/0/0 | ⛔ |  | KONSEP_OBSTETRI_NEONATAL.md | `EMR/DownScore/` · `moduls/EMR/DownScore/` | Tabel interpretasi Down Score legacy bolong (skor 6 tak tercakup) |
| `EMR-068` | Inisiasi Menyusui Dini | `inisiasi_menyusui_dini` | `13.262` | 1/0/0/0 | ⬜ |  | KONSEP_OBSTETRI_NEONATAL.md | `EMR/InisiasiMenyusuiDini/` · `moduls/EMR/InisiasiMenyusuiDini/` | — |
| `EMR-069` | Skrining MDPK/MPP | `skrining_mpp` | `13.263` | 1/1/0/0 | ⬜ |  | KONSEP_OBSTETRI_NEONATAL.md | `EMR/SkriningMpp/` · `moduls/EMR/SkriningMpp/` | — |
| `EMR-070` | Kesimpulan MCU | `kesimpulan_mcu` | `14.273` | 0/0/0/1 | ⬜ |  | KONSEP_FORM_MCU.md | `EMR/KesimpulanMcu/` · `moduls/EMR/KesimpulanMcu/` | — |
| `EMR-071` | Catatan Keperawatan / TTV MCU | `ttv_mcu` | `14.274` | 0/0/0/1 | ⬜ |  | KONSEP_FORM_MCU.md | `EMR/TtvMcu/` · `moduls/EMR/TtvMcu/` | — |
| `EMR-072` | Visus Mata MCU | `visus_mata_mcu` | `14.275` | 0/0/0/1 | ⬜ |  | KONSEP_FORM_MCU.md | `EMR/VisusMataMcu/` · `moduls/EMR/VisusMataMcu/` | — |
| `EMR-073` | Obstetri MCU | `obstetri_mcu` | `14.276` | 0/0/0/1 | ⬜ |  | KONSEP_FORM_MCU.md | `EMR/ObstetriMcu/` · `moduls/EMR/ObstetriMcu/` | — |
| `EMR-074` | Penyakit Dalam MCU | `penyakit_dalam_mcu` | `14.277` | 0/0/0/1 | ⬜ |  | KONSEP_FORM_MCU.md | `EMR/PenyakitDalamMcu/` · `moduls/EMR/PenyakitDalamMcu/` | — |
| `EMR-075` | Jantung MCU | `jantung_mcu` | `14.278` | 0/0/0/1 | ⬜ |  | KONSEP_FORM_MCU.md | `EMR/JantungMcu/` · `moduls/EMR/JantungMcu/` | — |
| `EMR-076` | THT MCU | `tht_mcu` | `14.279` | 0/0/0/1 | ⬜ |  | KONSEP_FORM_MCU.md | `EMR/ThtMcu/` · `moduls/EMR/ThtMcu/` | — |
| `EMR-077` | Mata MCU | `mata_mcu` | `14.280` | 0/0/0/1 | ⬜ |  | KONSEP_FORM_MCU.md | `EMR/MataMcu/` · `moduls/EMR/MataMcu/` | — |
| `EMR-078` | Gigi MCU | `gigi_mcu` | `14.281` | 0/0/0/1 | ⬜ |  | KONSEP_FORM_MCU.md | `EMR/GigiMcu/` · `moduls/EMR/GigiMcu/` | — |
| `EMR-079` | OBGYN MCU | `obgyn_mcu` | `14.282` | 0/0/0/1 | ⬜ |  | KONSEP_FORM_MCU.md | `EMR/ObgynMcu/` · `moduls/EMR/ObgynMcu/` | — |
| `EMR-080` | Bedah MCU | `bedah_mcu` | `14.283` | 0/0/0/1 | ⬜ |  | KONSEP_FORM_MCU.md | `EMR/BedahMcu/` · `moduls/EMR/BedahMcu/` | — |
| `EMR-081` | Neurologi MCU | `neurologi_mcu` | `14.284` | 0/0/0/1 | ⬜ |  | KONSEP_FORM_MCU.md | `EMR/NeurologiMcu/` · `moduls/EMR/NeurologiMcu/` | — |
| `EMR-082` | Gizi MCU | `gizi_mcu` | `14.285` | 0/0/0/1 | ⬜ |  | KONSEP_FORM_MCU.md | `EMR/GiziMcu/` · `moduls/EMR/GiziMcu/` | — |
| `EMR-083` | Urologi MCU | `urologi_mcu` | `14.286` | 0/0/0/1 | ⬜ |  | KONSEP_FORM_MCU.md | `EMR/UrologiMcu/` · `moduls/EMR/UrologiMcu/` | — |
| `EMR-084` | Psikologi MCU | `psikologi_mcu` | `14.287` | 0/0/0/1 | ⬜ |  | KONSEP_FORM_MCU.md | `EMR/PsikologiMcu/` · `moduls/EMR/PsikologiMcu/` | — |

## 11. Fase P5 — Bedah, Anestesi & ICU

| Task | Nama Form | `slug` | `id_dash_menu` | ri/rj/igd/mcu | Status | Owner | Dokumen Konsep | Folder Controller/View | Catatan / Prasyarat |
|---|---|---|---|---|---|---|---|---|---|
| `EMR-052` | Kriteria Masuk ICU | `kriteria_masuk_icu` | `12.213` | 1/0/1/0 | ⬜ |  | KONSEP_ICU_HEMODIALISA.md | `EMR/KriteriaMasukIcu/` · `moduls/EMR/KriteriaMasukIcu/` | Profesi 16 baru (PRE-01) |
| `EMR-053` | Kriteria Keluar ICU | `kriteria_keluar_icu` | `12.214` | 1/0/0/0 | ⬜ |  | KONSEP_ICU_HEMODIALISA.md | `EMR/KriteriaKeluarIcu/` · `moduls/EMR/KriteriaKeluarIcu/` | Profesi 16 baru (PRE-01) |
| `EMR-085` | Asesmen Pra Bedah | `asesmen_pra_bedah` | `15.293` | 1/1/1/0 | ⬜ |  | KONSEP_BEDAH_ANESTESI.md | `EMR/AsesmenPraBedah/` · `moduls/EMR/AsesmenPraBedah/` | Profesi 17 baru (PRE-01) |
| `EMR-086` | Evaluasi Pre-Anestesi & Sedasi | `evaluasi_pre_anestesi_sedasi` | `15.294` | 1/0/1/0 | ⬜ |  | KONSEP_BEDAH_ANESTESI.md | `EMR/EvaluasiPreAnestesiSedasi/` · `moduls/EMR/EvaluasiPreAnestesiSedasi/` | Profesi 17 baru (PRE-01) |
| `EMR-087` | Asesmen Post-Anestesi & Sedasi | `asesmen_post_anestesi_sedasi` | `15.295` | 1/0/0/0 | ⬜ |  | KONSEP_BEDAH_ANESTESI.md | `EMR/AsesmenPostAnestesiSedasi/` · `moduls/EMR/AsesmenPostAnestesiSedasi/` | Profesi 17 baru (PRE-01) |
| `EMR-088` | Monitoring Anestesi & Sedasi | `monitoring_anestesi_sedasi` | `15.296` | 1/0/1/0 | ⬜ |  | KONSEP_BEDAH_ANESTESI.md | `EMR/MonitoringAnestesiSedasi/` · `moduls/EMR/MonitoringAnestesiSedasi/` | Profesi 17 baru (PRE-01) |
| `EMR-089` | Check List Keamanan Pasien Operasi | `checklist_keamanan_operasi` | `15.297` | 1/0/1/0 | ⛔ |  | KONSEP_BEDAH_ANESTESI.md | `EMR/ChecklistKeamananOperasi/` · `moduls/EMR/ChecklistKeamananOperasi/` | Desain memakai hard gate — perlu persetujuan tim klinis |
| `EMR-090` | Check List Kesiapan Anestesi | `checklist_kesiapan_anestesi` | `15.298` | 1/0/1/0 | ⬜ |  | KONSEP_BEDAH_ANESTESI.md | `EMR/ChecklistKesiapanAnestesi/` · `moduls/EMR/ChecklistKesiapanAnestesi/` | Profesi 17 baru (PRE-01) |
| `EMR-091` | Pengkajian Pre/Intra/Post Operasi | `pengkajian_operasi` | `15.299` | 1/0/1/0 | ⬜ |  | KONSEP_BEDAH_ANESTESI.md | `EMR/PengkajianOperasi/` · `moduls/EMR/PengkajianOperasi/` | — |
| `EMR-092` | Laporan Operasi | `laporan_operasi` | `15.300` | 1/0/0/0 | ⬜ |  | KONSEP_BEDAH_ANESTESI.md | `EMR/LaporanOperasi/` · `moduls/EMR/LaporanOperasi/` | — |
| `EMR-093` | Catatan Bedah | `catatan_bedah` | `15.301` | 1/0/0/0 | ⬜ |  | KONSEP_BEDAH_ANESTESI.md | `EMR/CatatanBedah/` · `moduls/EMR/CatatanBedah/` | — |
| `EMR-094` | Transfer Pasien Antar Ruangan | `transfer_pasien` | `15.302` | 1/0/1/0 | ⬜ |  | KONSEP_BEDAH_ANESTESI.md | `EMR/TransferPasien/` · `moduls/EMR/TransferPasien/` | — |
| `EMR-095` | Penandaan Lokasi Operasi | `penandaan_lokasi_operasi` | `15.303` | 1/0/1/0 | ⬜ |  | KONSEP_BEDAH_ANESTESI.md | `EMR/PenandaanLokasiOperasi/` · `moduls/EMR/PenandaanLokasiOperasi/` | — |
| `EMR-096` | Penandaan Pasien | `penandaan_pasien` | `15.304` | 1/0/1/0 | ⬜ |  | KONSEP_BEDAH_ANESTESI.md | `EMR/PenandaanPasien/` · `moduls/EMR/PenandaanPasien/` | — |
| `EMR-097` | Ceklis Pre & Post Tindakan Invasif | `ceklis_tindakan_invasif` | `15.305` | 1/0/1/0 | ⛔ |  | KONSEP_BEDAH_ANESTESI.md | `EMR/CeklisTindakanInvasif/` · `moduls/EMR/CeklisTindakanInvasif/` | Hard gate checklist tindakan invasif — perlu persetujuan tim klinis |
| `EMR-098` | Surveilans Luka Insisi | `surveilans_luka_insisi` | `15.306` | 1/0/0/0 | ⬜ |  | KONSEP_BEDAH_ANESTESI.md | `EMR/SurveilansLukaInsisi/` · `moduls/EMR/SurveilansLukaInsisi/` | — |
| `EMR-099` | Catatan Anestesia | `catatan_anestesia` | `15.307` | 1/0/1/0 | ⬜ |  | KONSEP_BEDAH_ANESTESI.md | `EMR/CatatanAnestesia/` · `moduls/EMR/CatatanAnestesia/` | Profesi 17 baru (PRE-01) |
| `EMR-100` | Ceklist Keamanan Angiografi | `ceklist_keamanan_angiografi` | `15.308` | 1/0/1/0 | ⛔ |  | KONSEP_BEDAH_ANESTESI.md | `EMR/CeklistKeamananAngiografi/` · `moduls/EMR/CeklistKeamananAngiografi/` | Hard gate checklist angiografi — perlu persetujuan tim klinis |
| `EMR-101` | Asesmen Katarak | `asesmen_katarak` | `15.309` | 0/1/0/0 | ⬜ |  | KONSEP_BEDAH_ANESTESI.md | `EMR/AsesmenKatarak/` · `moduls/EMR/AsesmenKatarak/` | — |
| `EMR-102` | Pre Hemodialisa | `pre_hemodialisa` | `12.215` | 1/0/1/0 | ⬜ |  | KONSEP_ICU_HEMODIALISA.md | `EMR/PreHemodialisa/` · `moduls/EMR/PreHemodialisa/` | Profesi 18 baru (PRE-01) |
| `EMR-103` | Intra Hemodialisa | `intra_hemodialisa` | `12.216` | 1/0/1/0 | ⬜ |  | KONSEP_ICU_HEMODIALISA.md | `EMR/IntraHemodialisa/` · `moduls/EMR/IntraHemodialisa/` | Profesi 18 baru (PRE-01) |
| `EMR-104` | Post Hemodialisa | `post_hemodialisa` | `12.217` | 1/0/0/0 | ⬜ |  | KONSEP_ICU_HEMODIALISA.md | `EMR/PostHemodialisa/` · `moduls/EMR/PostHemodialisa/` | Profesi 18 baru (PRE-01) |
| `EMR-105` | Triage Hemodialisa | `triage_hemodialisa` | `12.218` | 0/1/0/0 | ⬜ |  | KONSEP_ICU_HEMODIALISA.md | `EMR/TriageHemodialisa/` · `moduls/EMR/TriageHemodialisa/` | — |

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

## 15. Riwayat

| Tanggal | Perubahan |
|---|---|
| 2026-10-10 | File dibuat. 114 form (16–129) + 12 prasyarat + 15 form eksisting tercatat. 5 task `⛔`. |
| 2026-10-10 | **Fase P1 selesai**: form 16, 17, 18, 19, 20, 21, 57 → ✅. Seeder (objek 178–235, menu 6–7, sub 33/34/53/54) + controller + blade. Semua terverifikasi E2E: store/mapping/index/update/delete + gate akses. 2 bug nyata ditemukan & diperbaiki (`Rule::requiredIf` wrong-arity di TriageIgd; `$metaTriase` undefined). |

