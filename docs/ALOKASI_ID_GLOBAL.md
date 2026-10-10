# Alokasi ID Global — Form EMR NusaMedika

**Status:** Rujukan tunggal (single source of truth) — WAJIB dipakai semua dokumen `KONSEP_*.md`
**Tanggal:** 2026-10-10
**Alasan:** dokumen konsep ditulis paralel sehingga bentrok ID. Dokumen ini mengunci alokasi.

---

## 0. Baseline (sudah ada di `EmrMasterSeeder.php`)

| Tabel | ID terpakai | ID berikutnya yang boleh dipakai |
|---|---|---|
| `dashboard_menu` | 1–5 | **6** |
| `dashboard_menu_sub` | 1, 2, 3, 8, 9, 10, 11, 12 | **13** |
| `dashboard_menu_sub_extra` | 1–5 | **6** |
| `objek` | 1–177 | **178** |
| `form` | 1–15 | **16** |

> `dashboard_menu_sub_id` bersifat **global**, bukan per-menu. Menu baru TIDAK
> boleh memakai sub id 1–12 — itu milik menu 1–5.

---

## 1. Objek Master

Setiap dokumen memakai **rentang tertutup eksklusif**. Objek 1–177 boleh di-reuse
_cross-document_, tapi objek baru TIDAK boleh di-deklarasikan dua kali.

| # | Dokumen | `objek_id` (rentang) | Jumlah | Subsequent ID |
|---|---|---|---|---|
| 1 | `KONSEP_CATATAN_MEDIS_LANJUTAN.md` | **178–199** | 22 | 200 |
| 2 | `KONSEP_DISCHARGE_PLANNING.md` | **200–224** | 25 | 225 |
| 3 | `KONSEP_TRIASE_IGD.md` | **225–235** | 11 | 236 |
| 4 | `KONSEP_ASUHAN_KEPERAWATAN_LANJUTAN.md` | **236–290** | 55 | 291 |
| 5 | `KONSEP_CAIRAN_BALANCE.md` | **291–312** | 22 | 313 |
| 6 | `KONSEP_NYERI.md` | **313–335** | 23 | 336 |
| 7 | `KONSEP_RISIKO_JATUH_LANJUTAN.md` | **336–361** | 26 | 362 |
| 8 | `KONSEP_ASESMEN_GIZI.md` | **362–396** | 35 | 397 |
| 9 | `KONSEP_FARMAKASI.md` | **397–431** | 35 | 432 |
| 10 | `KONSEP_PENUNJANG_MEDIS_LANJUTAN.md` | **432–446** | 15 | 447 |
| 11 | `KONSEP_ICU_HEMODIALISA.md` | **447–535** | 89 | 536 |
| 12 | `KONSEP_OBSTETRI_NEONATAL.md` | **536–610** | 75 | 611 |
| 13 | `KONSEP_FORM_MCU.md` | **611–670** | 60 | 671 |
| 14 | `KONSEP_PSIKOSOSIAL_KEROHANIAN.md` | **671–690** | 20 | 691 |
| 15 | `KONSEP_SURVEILANS_INFEKSI.md` | **691–729** | 39 | 730 |
| 16 | `KONSEP_FORMULIR_RUJUKAN.md` | **730–761** | 32 | 762 |
| 17 | `KONSEP_BEDAH_ANESTESI.md` | **762–992** | 231 | 993 |
| 18 | `KONSEP_ONKOLOGI_TRANSFUSI.md` | **993–1032** | 40 | 1033 |
| 19 | `KONSEP_KATALOG_FORM_BLANKO.md` | — (tabel sendiri) | 0 | 1033 |

**Objek lintas dokumen yang sah (dideklarasikan sekali, di-reuse):**

| objek | Nama | Deklarasi | Dipakai oleh |
|---|---|---|---|
| 186 | `Dirujuk Ke` | `KONSEP_CATATAN_MEDIS_LANJUTAN.md` | form 16 (Resume) & form 18 (Pemulangan) |

> Selain 186, **tidak ada** objek baru yang boleh dipakai lintas dokumen.

---

## 2. `dashboard_menu` (menu baru)

| ID | Nama Menu | Dokumen pemilik |
|---|---|---|
| 6 | Resume & Discharge | `KONSEP_DISCHARGE_PLANNING.md` |
| 7 | Gawat Darurat | `KONSEP_TRIASE_IGD.md` |
| 8 | Penilaian Klinis | `KONSEP_ASUHAN_KEPERAWATAN_LANJUTAN.md` |
| 9 | Monitoring | `KONSEP_CAIRAN_BALANCE.md` |
| 10 | Gizi & Nutrisi | `KONSEP_ASESMEN_GIZI.md` |
| 11 | Farmasi | `KONSEP_FARMAKASI.md` |
| 12 | ICU & Hemodialisa | `KONSEP_ICU_HEMODIALISA.md` |
| 13 | Kebidanan & Neonatal | `KONSEP_OBSTETRI_NEONATAL.md` |
| 14 | MCU | `KONSEP_FORM_MCU.md` |
| 15 | Bedah & Anestesi | `KONSEP_BEDAH_ANESTESI.md` |
| 16 | Psikososial & Kerohanian | `KONSEP_PSIKOSOSIAL_KEROHANIAN.md` |
| 17 | Surveilans Infeksi | `KONSEP_SURVEILANS_INFEKSI.md` |
| 18 | Onkologi & Darah | `KONSEP_ONKOLOGI_TRANSFUSI.md` |

Menu 1–5 sudah ada dan **tidak** boleh dipakai ulang.

---

## 3. `dashboard_menu_sub` — alokasi berbasis *band*

`dashboard_menu_sub_id` bersifat **global**. Setiap dokumen mendapat *band* 20 ID
yang eksklusif, mulai dari ID pertama yang dipakai dan berurutan ke atas.

| Dokumen | Band sub (mulai dari) | Sisa |
|---|---|---|
| `KONSEP_CATATAN_MEDIS_LANJUTAN.md` | **13** | 13–32 |
| `KONSEP_DISCHARGE_PLANNING.md` | **33** | 33–52 |
| `KONSEP_TRIASE_IGD.md` | **53** | 53–72 |
| `KONSEP_ASUHAN_KEPERAWATAN_LANJUTAN.md` | **73** | 73–92 |
| `KONSEP_CAIRAN_BALANCE.md` | **93** | 93–112 |
| `KONSEP_NYERI.md` | **113** | 113–132 |
| `KONSEP_RISIKO_JATUH_LANJUTAN.md` | **133** | 133–152 |
| `KONSEP_ASESMEN_GIZI.md` | **153** | 153–172 |
| `KONSEP_FARMAKASI.md` | **173** | 173–192 |
| `KONSEP_PENUNJANG_MEDIS_LANJUTAN.md` | **193** | 193–212 |
| `KONSEP_ICU_HEMODIALISA.md` | **213** | 213–232 |
| `KONSEP_FORMULIR_RUJUKAN.md` | **233** | 233–252 |
| `KONSEP_OBSTETRI_NEONATAL.md` | **253** | 253–272 |
| `KONSEP_FORM_MCU.md` | **273** | 273–292 |
| `KONSEP_BEDAH_ANESTESI.md` | **293** | 293–312 |
| `KONSEP_PSIKOSOSIAL_KEROHANIAN.md` | **313** | 313–332 |
| `KONSEP_SURVEILANS_INFEKSI.md` | **333** | 333–352 |
| `KONSEP_ONKOLOGI_TRANSFUSI.md` | **353** | 353–372 |
| `KONSEP_KATALOG_FORM_BLANKO.md` | **373** | 373–392 |

> Cara pakai: dalam dokumen, sub pertama yang ditulis harus `13` (atau nilai
> mulai dokumen ini), sub berikutnya `14`, lalu `15`, dan seterusnya — gasps
> sampai band habis. **Setiap dokumen harus mengulang alokasi sub-nya sendiri
> dari angka di tabel ini.** Setelah selesai, semua `id_dash_menu` **wajib**
> dihitung ulang dari sub/extra yang baru.

---

## 4. `dashboard_menu_sub_extra` — alokasi berbasis *band*

Sama seperti sub: global, mulai dari 6.

| Dokumen | Band extra (mulai dari) | Sisa |
|---|---|---|
| `KONSEP_CATATAN_MEDIS_LANJUTAN.md` | **6** | 6–25 |
| `KONSEP_ASUHAN_KEPERAWATAN_LANJUTAN.md` | **26** | 26–45 |
| `KONSEP_CAIRAN_BALANCE.md` | **46** | 46–65 |
| `KONSEP_NYERI.md` | **66** | 66–85 |
| `KONSEP_RISIKO_JATUH_LANJUTAN.md` | **86** | 86–105 |

> Hanya 5 dokumen yang memakai level extra. Dokumen lain tidak boleh membuat baris extra.

---

## 5. Profesi

Sudah ada di `MasterPegawaiSeeder.php` — **jangan didefinisikan ulang**:

| ID | Profesi | Status |
|---|---|---|
| 1 | Dokter | ada |
| 2 | Perawat | ada |
| 3 | Bidan | ada |
| 4 | Apoteker | ada |
| 5 | Radiografer | ada |
| 6 | IT Support | ada |
| 7 | Perekam Medis | ada |
| 8 | Fisioterapis | ada |
| 9 | Sanitarian | ada |
| **10** | **Ahli Gizi** | ada |
| 11 | Security | ada |
| 12 | Teknisi | ada |
| 13 | Analis Laboratorium | ada |

Profesi baru yang perlu di-seed sebelum `akses_ehr` dipakai:

| ID | Profesi | Dibutuhkan oleh |
|---|---|---|
| **14** | Kerohanian | `KONSEP_PSIKOSOSIAL_KEROHANIAN.md` |
| 15 | Patolog Anatomi | `KONSEP_PENUNJANG_MEDIS_LANJUTAN.md` |
| 16 | Dokter Intensivis | `KONSEP_ICU_HEMODIALISA.md` |
| 17 | Dokter Anestesi | `KONSEP_ICU_HEMODIALISA.md` |
| 18 | Perawat Hemodialisa | `KONSEP_ICU_HEMODIALISA.md` |

> ⚠️ **Ahli Gizi = 10, bukan 3.** ID 3 sudah dipakai Bidan.
> ⚠️ **Apoteker = 4, bukan 14.** ID 14 = Kerohanian.
> Menyebut ID 3 sebagai "Ahli Gizi baru" atau 14 sebagai "Apoteker" akan
> menimpa profesi yang sudah ada.

---

## 6. Form

| form_id | Nama Form | slug | id_dash_menu | ri | rj | igd | mcu |
|---|---|---|---|---|---|---|---|
| 16 | Resume Medis | `resume_medis` | 1.13 | 1 | 0 | 0 | 0 |
| 17 | Discharge Planning | `discharge_planning` | 6.33 | 1 | 1 | 1 | 0 |
| 18 | Pemulangan Pasien | `pemulangan_pasien` | 6.34 | 1 | 0 | 0 | 0 |
| 19 | Triage IGD | `triage_igd` | 7.53 | 0 | 0 | 1 | 0 |
| 20 | Care Plan | `care_plan` | 1.14 | 1 | 1 | 1 | 0 |
| 21 | DAR | `dar` | 1.15.6 | 1 | 0 | 1 | 0 |
| 22 | Rencana Asuhan Keperawatan | `rencana_asuhan_keperawatan` | 2.73.26 | 1 | 0 | 0 | 0 |
| 23 | Issues Keperawatan | `issues_keperawatan` | 2.73.27 | 1 | 0 | 0 | 0 |
| 24 | Intake | `intake` | 2.93.46 | 1 | 0 | 0 | 0 |
| 25 | Output | `output` | 2.93.47 | 1 | 0 | 0 | 0 |
| 26 | Monitoring Cairan | `monitoring_cairan` | 9.94 | 1 | 0 | 0 | 0 |
| 27 | Penilaian Nyeri | `penilaian_nyeri` | 9.113.66 | 1 | 1 | 1 | 0 |
| 28 | Kesadaran & Pemberian O2 | `kesadaran_oksigen` | 9.95 | 1 | 0 | 0 | 0 |
| 29 | Handover / Serah Terima | `handover` | 2.74.28 | 1 | 0 | 0 | 0 |
| 30 | Monitoring & Evaluasi Risiko Jatuh | `monitoring_risiko_jatuh` | 8.133.86 | 1 | 0 | 0 | 0 |
| 31 | Asesmen Kebutuhan Edukasi | `asesmen_kebutuhan_edukasi` | 2.75.29 | 1 | 1 | 0 | 0 |
| 32 | Penilaian Risiko Dekubitus (Norton) | `penilaian_risiko_dekubitus` | 8.134.87 | 1 | 0 | 0 | 0 |
| 33 | Barthel Index | `barthel_index` | 8.134.88 | 1 | 0 | 0 | 0 |
| 34 | Asesmen Status Fungsional | `asesmen_status_fungsional` | 8.76.31 | 1 | 1 | 0 | 0 |
| 35 | Pengkajian Keperawatan Kritis | `pengkajian_keperawatan_kritis` | 2.75.30 | 1 | 0 | 1 | 0 |
| 36 | Metode Nyeri | `metode_nyeri` | 9.113.67 | 1 | 1 | 1 | 0 |
| 37 | Body Map Nyeri | `body_map_nyeri` | 9.114 | 1 | 1 | 1 | 0 |
| 38 | Skrining Nyeri Perina | `skrining_nyeri_perina` | 9.113.68 | 1 | 0 | 0 | 0 |
| 39 | Asesmen Gizi | `asesmen_gizi` | 10.153 | 1 | 1 | 1 | 0 |
| 40 | Detail Nutrisi | `detail_nutrisi` | 10.154 | 1 | 0 | 0 | 0 |
| 41 | Rekap Asesmen Nutrisi | `rekap_asesmen_nutrisi` | 10.155 | 1 | 0 | 0 | 0 |
| 42 | Pesan Makanan & Monitoring Asupan | `pesan_makanan` | 10.156 | 1 | 0 | 0 | 0 |
| 43 | Diet Pasien | `diet_pasien` | 10.157 | 1 | 1 | 1 | 0 |
| 44 | ADIME | `adime` | 10.158 | 1 | 0 | 0 | 0 |
| 45 | Daftar Terapi Obat | `daftar_terapi_obat` | 11.173 | 1 | 1 | 1 | 0 |
| 46 | Rekonsiliasi Obat | `rekonsiliasi_obat` | 11.174 | 1 | 1 | 1 | 0 |
| 47 | Telaah Resep | `telaah_resep` | 11.175 | 0 | 1 | 1 | 0 |
| 48 | Telaah Obat | `telaah_obat` | 11.176 | 1 | 0 | 0 | 0 |
| 49 | Order Patologi | `order_patologi` | 4.193 | 1 | 1 | 1 | 0 |
| 50 | Order Mikrobiologi | `order_mikrobiologi` | 4.194 | 1 | 1 | 1 | 0 |
| 51 | Order POCT | `order_poct` | 4.195 | 1 | 1 | 1 | 0 |
| 52 | Kriteria Masuk ICU | `kriteria_masuk_icu` | 12.213 | 1 | 0 | 1 | 0 |
| 53 | Kriteria Keluar ICU | `kriteria_keluar_icu` | 12.214 | 1 | 0 | 0 | 0 |
| 54 | Catatan Keperawatan Rawat Jalan | `catatan_keperawatan_rajal` | 8.77.37 | 0 | 1 | 0 | 0 |
| 55 | Riwayat Penyakit | `riwayat_penyakit` | 8.77.35 | 1 | 1 | 1 | 0 |
| 56 | Riwayat Keluarga | `riwayat_keluarga` | 8.77.36 | 1 | 1 | 1 | 0 |
| 57 | Catatan Medis Visum | `catatan_medis_visum` | 1.15 | 0 | 0 | 1 | 0 |
| 58 | Rekam Medis Rujukan | `rekam_medis_rujukan` | 5.233 | 1 | 1 | 1 | 0 |
| 59 | Asesmen Khusus Kebidanan & Penyakit Kandungan | `asesmen_kebidanan` | 13.253 | 1 | 1 | 0 | 0 |
| 60 | Pengkajian Awal Keperawatan Perina | `pengkajian_perina` | 13.254 | 1 | 0 | 0 | 0 |
| 61 | Pengkajian Awal Keperawatan Non-Perina | `pengkajian_non_perina` | 13.255 | 1 | 0 | 0 | 0 |
| 62 | Kala I-IV | `kala_persalinan` | 13.256 | 1 | 0 | 0 | 0 |
| 63 | Riwayat Persalinan Sekarang | `riwayat_persalinan` | 13.257 | 1 | 0 | 0 | 0 |
| 64 | Pelaksanaan Bayi Baru Lahir | `bayi_baru_lahir` | 13.258 | 1 | 0 | 0 | 0 |
| 65 | Riwayat Resusitasi Bayi | `riwayat_resusitasi_bayi` | 13.259 | 1 | 0 | 0 | 0 |
| 66 | Penilaian Bayi dengan APGAR Score | `penilaian_bayi_dengan_apgar_score` | 13.260 | 1 | 0 | 0 | 0 |
| 67 | Down Score (Neonatal) | `down_score` | 13.261 | 1 | 0 | 0 | 0 |
| 68 | Inisiasi Menyusui Dini | `inisiasi_menyusui_dini` | 13.262 | 1 | 0 | 0 | 0 |
| 69 | Skrining MDPK/MPP | `skrining_mpp` | 13.263 | 1 | 1 | 0 | 0 |
| 70 | Kesimpulan MCU | `kesimpulan_mcu` | 14.273 | 0 | 0 | 0 | 1 |
| 71 | Catatan Keperawatan / TTV MCU | `ttv_mcu` | 14.274 | 0 | 0 | 0 | 1 |
| 72 | Visus Mata MCU | `visus_mata_mcu` | 14.275 | 0 | 0 | 0 | 1 |
| 73 | Obstetri MCU | `obstetri_mcu` | 14.276 | 0 | 0 | 0 | 1 |
| 74 | Penyakit Dalam MCU | `penyakit_dalam_mcu` | 14.277 | 0 | 0 | 0 | 1 |
| 75 | Jantung MCU | `jantung_mcu` | 14.278 | 0 | 0 | 0 | 1 |
| 76 | THT MCU | `tht_mcu` | 14.279 | 0 | 0 | 0 | 1 |
| 77 | Mata MCU | `mata_mcu` | 14.280 | 0 | 0 | 0 | 1 |
| 78 | Gigi MCU | `gigi_mcu` | 14.281 | 0 | 0 | 0 | 1 |
| 79 | OBGYN MCU | `obgyn_mcu` | 14.282 | 0 | 0 | 0 | 1 |
| 80 | Bedah MCU | `bedah_mcu` | 14.283 | 0 | 0 | 0 | 1 |
| 81 | Neurologi MCU | `neurologi_mcu` | 14.284 | 0 | 0 | 0 | 1 |
| 82 | Gizi MCU | `gizi_mcu` | 14.285 | 0 | 0 | 0 | 1 |
| 83 | Urologi MCU | `urologi_mcu` | 14.286 | 0 | 0 | 0 | 1 |
| 84 | Psikologi MCU | `psikologi_mcu` | 14.287 | 0 | 0 | 0 | 1 |
| 85 | Asesmen Pra Bedah | `asesmen_pra_bedah` | 15.293 | 1 | 1 | 1 | 0 |
| 86 | Evaluasi Pre-Anestesi & Sedasi | `evaluasi_pre_anestesi_sedasi` | 15.294 | 1 | 0 | 1 | 0 |
| 87 | Asesmen Post-Anestesi & Sedasi | `asesmen_post_anestesi_sedasi` | 15.295 | 1 | 0 | 0 | 0 |
| 88 | Monitoring Anestesi & Sedasi | `monitoring_anestesi_sedasi` | 15.296 | 1 | 0 | 1 | 0 |
| 89 | Check List Keamanan Pasien Operasi | `checklist_keamanan_operasi` | 15.297 | 1 | 0 | 1 | 0 |
| 90 | Check List Kesiapan Anestesi | `checklist_kesiapan_anestesi` | 15.298 | 1 | 0 | 1 | 0 |
| 91 | Pengkajian Pre/Intra/Post Operasi | `pengkajian_operasi` | 15.299 | 1 | 0 | 1 | 0 |
| 92 | Laporan Operasi | `laporan_operasi` | 15.300 | 1 | 0 | 0 | 0 |
| 93 | Catatan Bedah | `catatan_bedah` | 15.301 | 1 | 0 | 0 | 0 |
| 94 | Transfer Pasien Antar Ruangan | `transfer_pasien` | 15.302 | 1 | 0 | 1 | 0 |
| 95 | Penandaan Lokasi Operasi | `penandaan_lokasi_operasi` | 15.303 | 1 | 0 | 1 | 0 |
| 96 | Penandaan Pasien | `penandaan_pasien` | 15.304 | 1 | 0 | 1 | 0 |
| 97 | Ceklis Pre & Post Tindakan Invasif | `ceklis_tindakan_invasif` | 15.305 | 1 | 0 | 1 | 0 |
| 98 | Surveilans Luka Insisi | `surveilans_luka_insisi` | 15.306 | 1 | 0 | 0 | 0 |
| 99 | Catatan Anestesia | `catatan_anestesia` | 15.307 | 1 | 0 | 1 | 0 |
| 100 | Ceklist Keamanan Angiografi | `ceklist_keamanan_angiografi` | 15.308 | 1 | 0 | 1 | 0 |
| 101 | Asesmen Katarak | `asesmen_katarak` | 15.309 | 0 | 1 | 0 | 0 |
| 102 | Pre Hemodialisa | `pre_hemodialisa` | 12.215 | 1 | 0 | 1 | 0 |
| 103 | Intra Hemodialisa | `intra_hemodialisa` | 12.216 | 1 | 0 | 1 | 0 |
| 104 | Post Hemodialisa | `post_hemodialisa` | 12.217 | 1 | 0 | 0 | 0 |
| 105 | Triage Hemodialisa | `triage_hemodialisa` | 12.218 | 0 | 1 | 0 | 0 |
| 106 | Asesmen Kemoterapi | `asesmen_kemoterapi` | 18.353 | 1 | 1 | 0 | 0 |
| 107 | Pemantauan Kemoterapi | `pemantauan_kemoterapi` | 18.354 | 1 | 0 | 0 | 0 |
| 108 | Asesmen Pasien dengan Darah | `asesmen_pemberian_darah` | 18.355 | 1 | 1 | 1 | 0 |
| 109 | Asesmen Transfusi | `assesmen_transfusi` | 18.356 | 1 | 0 | 0 | 0 |
| 110 | Monitoring Transfusi Darah | `monitoring_transfusi` | 18.357 | 1 | 0 | 0 | 0 |
| 111 | Double Check Obat High Alert | `double_check_high_alert` | 18.358 | 1 | 0 | 1 | 0 |
| 112 | Asesmen Psikologi Pasien & Keluarga | `assesmen_psikologi` | 16.313 | 1 | 1 | 1 | 0 |
| 113 | Asesmen Spiritual | `assesmen_spiritual` | 16.314 | 1 | 1 | 1 | 0 |
| 114 | Permintaan Pelayanan Kerohanian | `permintaan_kerohanian` | 16.315 | 1 | 1 | 1 | 0 |
| 115 | Bukti Pelayanan Kerohanian | `bukti_kerohanian` | 16.316 | 1 | 1 | 1 | 0 |
| 116 | Surveilans Pasien Terpasang CVC/PICC | `surveilans_cvc_picc` | 17.333 | 1 | 0 | 1 | 0 |
| 117 | Surveilans Pasien Terpasang IV Catheter Perifer | `surveilans_iv_catheter` | 17.334 | 1 | 0 | 1 | 0 |
| 118 | Surveilans Pasien Terpasang Kateter Urine | `surveilans_kateter_urine` | 17.335 | 1 | 0 | 1 | 0 |
| 119 | Surveilans Pasien Tirah Baring Total | `surveilans_tirah_baring` | 17.336 | 1 | 0 | 0 | 0 |
| 120 | Screening Akses Vaskular | `screening_akses` | 17.337 | 1 | 0 | 1 | 0 |
| 121 | Asesmen Pasien Restraint | `asesmen_restraint` | 17.338 | 1 | 0 | 0 | 0 |
| 122 | Observasi Restraint | `observasi_restraint` | 17.339 | 1 | 0 | 0 | 0 |
| 123 | Asesmen Akhir Kehidupan | `asesmen_akhir_kehidupan` | 8.76.33 | 1 | 0 | 1 | 0 |
| 124 | Uji Fungsi & Asesment | `uji_fungsi_asesment` | 8.76.34 | 0 | 1 | 0 | 1 |
| 125 | Respon Emosi & Status Mental | `respon_emosi` | 8.76.32 | 1 | 1 | 1 | 0 |
| 126 | Surat Keterangan Sehat | `surat_keterangan_sehat` | 5.234 | 0 | 1 | 0 | 0 |
| 127 | Pesan Bedah / Cathlab / Endoscopy / ESWL | `pesan_penunjang` | 5.235 | 1 | 1 | 1 | 0 |
| 128 | SPRI & Program Rujukan Balik | `spri_rujukan_balik` | 5.236 | 1 | 1 | 0 | 0 |
| 129 | Katalog Form Blanko | `katalog_form_blanko` | 5.373 | 1 | 1 | 1 | 1 |

> Kolom `id_dash_menu` di atas bersifat **indikatif**. Yang mengikat adalah
> `menu.sub(.extra)` yang konsisten dengan baris `$subMenus`/`$extras` di
> dokumen masing-masing. Kalau dokumen memakai susunan sub berbeda, **hitung
> ulang `id_dash_menu` dari sub/extra yang benar-benar ditulis di dokumen itu.**

---

## 7. Aturan slug yang terkunci

`form.slug` **WAJIB** = `Str::slug(nama_sub_menu_atau_extra, '_')`.

Preservasi ejaan "`assesmen`" (dua s) sudah menjadi konvensi proyek — lihat form 11
yang sudah ada (`assesmen_awal_medis_rawat_jalan`). **Tetap pakai `assesmen`**
agar konsisten dengan kode yang sudah berjalan.

Perbaikan yang wajib diterapkan:

| Form | Sub/extra name | Slug yang benar |
|---|---|---|
| 21 DAR | harus `DAR` (bukan `DAR (D-Rekognisi)`) | `dar` |
| 54 | `Catatan Keperawatan Rawat Jalan` | `catatan_keperawatan_rajal` → **ganti sub name** menjadi `Catatan Keperawatan Rajal` |
| 68 | `Inisiasi Menyusui Dini` | `inisiasi_menyusui_dini` (bukan `inisisasi_menyususui_dini` — huruf `s` yang liar berasal dari nama lama, bukan dari `Str::slug`) |
| 109 | `Asesmen Transfusi` | `assesmen_transfusi` |
| 112 | `Asesmen Psikologi` | `assesmen_psikologi` |
| 113 | `Asesmen Spiritual` | `assesmen_spiritual` |
| 66 | `Penilaian Bayi dengan APGAR Score` | `penilaian_bayi_dengan_apgar_score` |

### 7.1 Penyimpangan yang disengaja

Konvensi proyek **mempertahankan ejaan `assesmen` (dua s)**, mengikuti form 11 yang
sudah berjalan (`assesmen_awal_medis_rawat_jalan`). Jadi:

| Form | Slug | Catatan |
|---|---|---|
| 11 | `assesmen_awal_medis_rawat_jalan` | sudah ada di produksi |
| 112 | `assesmen_psikologi` | `Str::slug('Asesmen Psikologi','_')` = `asesmen_psikologi` — **disengaja** |
| 113 | `assesmen_spiritual` | idem |
| 109 | `assesmen_transfusi` | idem |
| 121 | `asesmen_restraint` | idem |

Form 21–23, 31, 34, 35, 43, 54, 85, 86, 87, 121 dst. yang memakai `Asesmen`/`Assesmen`
harus memakai `assesmen`/`assesmen` sesuai ejaan yang sudah dipakai di dokumennya.
Tiap dokumen wajib memuat catatan penyimpangan ini di bagian slug-nya.

### 7.2 Karakter yang dihapus `Str::slug`

`Str::slug` **menghapus** (bukan mengganti) karakter non-alphanumeric:

| Masukan | Hasil |
|---|---|
| `Surveilans CVC/PICC` | `surveilans_cvc_picc` |
| `SPRI & Program Rujukan Balik` | `spri_program_rujukan_balik` |
| `DAR (D-Rekognisi)` | `dar_d_rekognisi` |

Karena itu karakter `/`, `&`, `(`, `)`, `+` **tidak boleh** dipakai di
`nama_sub_menu` / `nama_sub_menu_extra` apabila kata yang hilang dari slug itu
bermakna. Karakter tersebut boleh muncul di `form.nama_form` (bukan sumber
slug) — contoh: `SPRI & Program Rujukan Balik` sebagai `nama_form`, dengan
`nama_sub_menu = 'SPRI Rujukan Balik'`.

### 7.3 `id_dash_menu` dan flag rawat pada tabel Section 6

Kolom `id_dash_menu` **indikatif**. Yang mengikat adalah `menu.sub(.extra)` yang
konsisten dengan baris `$subMenus`/`$extras` di dokumen masing-masing.
Kolom `ri`/`rj`/`igd`/`mcu` bersifat **normatif** — bila dokumen berbeda,
**perbaiki dokumennya** mengikuti tabel ini.

---

## 8. Checklist sebelum menulis kode

- [ ] Semua objek baru berada di dalam band dokumen ini (Section 1)
- [ ] Semua sub id berada di dalam band sub dokumen ini (Section 3)
- [ ] Semua extra id berada di dalam band extra dokumen ini (Section 4)
- [ ] `id_dash_menu` dihitung dari sub/extra yang benar-benar ditulis
- [ ] `slug` == `Str::slug(nama_sub_menu/extra, '_')`
- [ ] ID profesi sesuai Section 5 (Ahli Gizi = 10, Apoteker = 4, Kerohanian = 14 baru)
- [ ] Objek 1–177 hanya di-*reuse*, tidak pernah di-deklarasikan ulang
- [ ] Tidak ada objek > 177 yang dipakai lintas dokumen kecuali objek 186