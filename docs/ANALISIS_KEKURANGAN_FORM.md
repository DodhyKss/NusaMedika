# Analisis Kekurangan Form EMR — NusaMedika vs SIMRS Tenriawaru

**Status:** Rancangan / Konsep — belum diimplementasikan
**Tanggal:** 2026-10-10
**Sumber pembanding:** `/simrs_tenriawaru/` (legacy PHP, ~90 form_id)
**Target:** `/NusaMedika/` (Laravel 13, 15 form_id)

---

## 1. Ringkasan Eksekutif

| Metrik | Tenriawaru (legacy) | NusaMedika (sekarang) | Kekurangan |
|---|---|---|---|
| Jumlah form_id terdaftar | ± 90 | 15 | **± 78** |
| Domain klinis ter-cover | 18 | 5 | 13 domain kosong |
| Controller + Blade dedicated | ± 85 | 14 | — |
| Form "mati" (seed tanpa implementasi) | 0 | 1 (form 1) | — |

**NusaMedika sudah kuat di 5 area:** SOAP/CPPT, pengkajian keperawatan awal & harian,
order penunjang medis (lab/rad/resep), implementasi keperawatan, tindakan medis,
SBAR, tanda vital, bundle VAP, alat invasif.

**Yang hilang besar-besaran:** seluruh domain rawat inap (resume, discharge planning,
transfer), obstetri–neonatal, bedah–anestesi, hemodialisa, kemoterapi, farmsi klinik,
gizi, dan 15 form MCU.

---

## 2. Peta Domain

| # | Domain | Form Tenriawaru | Form NusaMedika | Status |
|---|---|---|---|---|
| 1 | Catatan medis inti | SOAP, SBAR, DAR, Care Plan, Data Subjektif/Objektif/Kala, Visum | SOAP, SBAR | ⚠️ sebagian |
| 2 | Pengkajian keperawatan | Awal, Harian, Kritis, Status Fungsional, Perioperatif, Primer/Sekunder | Awal, Harian | ⚠️ sebagian |
| 3 | Asuhan keperawatan | Rencana Asuhan, Issues, Intake, Output, Eliminasi, Oksigen, Restraint, Riwayat penyakit/keluarga, Reproduksi, Uji Fungsi | Implementasi | ❌ kosong |
| 4 | Serah terima | Handover, Handover Rawat Inap, Waktu Asesmen | — | ❌ kosong |
| 5 | Vital & tanda hidup | Tanda Vital, Status Respirasi | Tanda Vital | ✅ |
| 6 | Cairan & balance | Monitoring Cairan | — | ❌ kosong |
| 7 | Nyeri | Penilaian Nyeri, metode (numerik/wafers/verbal), body map, kolom vertebra, perina | Objek `nyeri` di pengkajian | ⚠️ minimal |
| 8 | Skala risiko | Risiko jatuh (asesmen ulang, monitoring, Morse/HD/Sydney/Up&Go, TUG), Norton, Barthel, dekubitus/tirah baring | Risiko jatuh HDS/MFS/Sydney/TUG | ⚠️ sebagian |
| 9 | Gizi & diet | Asesmen Gizi, SGA, MNA/MST, Detail Nutrisi, Rekap Gizi, Diet, ADIME, Pesan Makanan | — | ❌ kosong |
| 10 | Obstetri & neonatal | Asesmen Kebidanan, Perina/Non-perina (9 sub-form), Kala I–IV, APGAR, Down Score, IMD, Riwayat Persalinan, Bayi Baru Lahir, Resusitasi, Medikasi Bayi, Skrining MPP | — | ❌ kosong |
| 11 | Bedah & anestesi | Pra Bedah, Pre/Post Anestesi, Monitoring Anestesi, 6 checklist, Laporan Operasi, Catatan Bedah, Pre/Intra/Post Operasi, Transfer, Penandaan, Luka Insisi, Alkes Bedah, Anestesi, Angiografi, Katarak | — | ❌ kosong |
| 12 | ICU | Kriteria Masuk ICU, Kriteria Keluar ICU, Bundle VAP | Bundle VAP | ❌ kosong |
| 13 | Hemodialisa | Pre/Post/Intra HD, Triage HD, Evaluasi Post HD | — | ❌ kosong |
| 14 | Onkologi & darah | Asesmen Kemo, Pemantauan Kemo, Persiapan Darah, Assesmen Transfusi, Monitoring Transfusi, Double Check High Alert | — | ❌ kosong |
| 15 | Farmasi | Daftar Terapi Obat, Rekonsiliasi Obat, Telaah Resep, Telaah Obat | Order Resep | ❌ kosong |
| 16 | Penunjang medis | Lab, Radiologi, Patologi Anatomi, Mikrobiologi, POCT | Lab, Radiologi | ⚠️ sebagian |
| 17 | MCU | 15 form spesialistik | — (docs/KONSEP_MCU.md sudah ada, tapi **belum ada form MCU**) | ❌ kosong |
| 18 | IGD & triase | Triage IGD, Entry Triage, Konfirmasi IGD, Registrasi IGD OBGYN | — (docs/KONSEP_GAWAT_DARURAT.md sudah ada) | ❌ kosong |
| 19 | Discharge & pulang | Discharge Planning, Pemulangan, Kondisi/Edukasi/Diagnosa/Nyeri/Anjuran/Kontrol Pulang, Home Care, Perencanaan Pulang | — | ❌ kosong |
| 20 | Resume medis | Resume Medis (+7 sub-form), Resume Keperawatan | — | ❌ kosong |
| 21 | Formulir & rujukan | SK Sehat, Pengantar Rawat, Pesan Bedah/Cathlab/Endoscopy/ESWL, SPRI, Program Rujuk Balik | Konsultasi | ⚠️ sebagian |
| 22 | Psikososial & kerohanian | Asesmen Psikologi, Asesmen Spiritual, Permintaan Kerohanian, Bukti Kerohanian | — | ❌ kosong |
| 23 | Surveilans infeksi | Screening Akses, CVC/PICC, IV Catheter, Kateter Urine, Tirah Baring | Alat Invasif | ❌ kosong |
| 24 | Kustom | Katalog Form Blanko (form generator) | — | ❌ kosong |

---

## 3. Daftar Form yang Belum Ada (76 form)

Setiap baris = satu `form_id` baru. Detail field/objek ada di file KONSEP per domain
(rujuk kolom **Dokumen Konsep**).

### P1 — Inti, wajib ada untuk rawat inap & penjaminan

| Form ID baru | Nama Form | Slug | Ref. Tenriawaru | Dokumen Konsep |
|---|---|---|---|---|
| 16 | Resume Medis | `resume_medis` | form 33 `resume_medis.php` | KONSEP_CATATAN_MEDIS_LANJUTAN.md |
| 17 | Discharge Planning | `discharge_planning` | form 9 `discharge_planning.php` | KONSEP_DISCHARGE_PLANNING.md |
| 18 | Pemulangan Pasien | `pemulangan_pasien` | form 32 `pemulangan_pasien.php` | KONSEP_DISCHARGE_PLANNING.md |
| 19 | Triage IGD | `triage_igd` | form 30 `triage_igd_cak.php` | KONSEP_TRIASE_IGD.md |
| 20 | Care Plan | `care_plan` | form 2 `care_plan.php` | KONSEP_CATATAN_MEDIS_LANJUTAN.md |
| 21 | DAR (D-Rekognisi) | `dar` | form 5 `dar_entry.php` | KONSEP_CATATAN_MEDIS_LANJUTAN.md |
| 22 | Rencana Asuhan Keperawatan | `rencana_asuhan_keperawatan` | form 8 | KONSEP_ASUHAN_KEPERAWATAN_LANJUTAN.md |
| 23 | Issues Keperawatan | `issues_keperawatan` | `Masalah_cak.php` | KONSEP_ASUHAN_KEPERAWATAN_LANJUTAN.md |
| 24 | Intake | `intake` | `intake.php` | KONSEP_CAIRAN_BALANCE.md |
| 25 | Output | `output` | `output.php` | KONSEP_CAIRAN_BALANCE.md |
| 26 | Monitoring Cairan | `monitoring_cairan` | form 11 | KONSEP_CAIRAN_BALANCE.md |
| 27 | Penilaian Nyeri | `penilaian_nyeri` | `nyeri_cak.php` | KONSEP_NYERI.md |
| 28 | Kesadaran & Pemberian O2 | `kesadaran_oksigen` | `oksigen.php` | KONSEP_CAIRAN_BALANCE.md |
| 29 | Handover / Serah Terima | `handover` | `handover.php` | KONSEP_ASUHAN_KEPERAWATAN_LANJUTAN.md |
| 30 | Monitoring & Evaluasi Risiko Jatuh | `monitoring_risiko_jatuh` | form 156 | KONSEP_RISIKO_JATUH_LANJUTAN.md |
| 31 | Asesmen Kebutuhan Edukasi | `asesmen_kebutuhan_edukasi` | form 7 | KONSEP_ASUHAN_KEPERAWATAN_LANJUTAN.md |

### P2 — Klinis inti Cameron/rumah sakit

| Form ID baru | Nama Form | Slug | Ref. Tenriawaru | Dokumen Konsep |
|---|---|---|---|---|
| 32 | Penilaian Risiko Dekubitus | `penilaian_risiko_dekubitus` | `score_norton.php` | KONSEP_RISIKO_JATUH_LANJUTAN.md |
| 33 | Barthel Index | `barthel_index` | `barthel_index.php` | KONSEP_RISIKO_JATUH_LANJUTAN.md |
| 34 | Asesmen Status Fungsional | `asesmen_status_fungsional` | form `pengkajian_awal_status_fungsional_pasien.php` | KONSEP_ASUHAN_KEPERAWATAN_LANJUTAN.md |
| 35 | Pengkajian Keperawatan Kritis | `pengkajian_keperawatan_kritis` | form 88 | KONSEP_ASUHAN_KEPERAWATAN_LANJUTAN.md |
| 36 | Metode Nyeri | `metode_nyeri` | `metode_nyeri.php` | KONSEP_NYERI.md |
| 37 | Body Map Nyeri | `body_map_nyeri` | `gambar_nyeri.php` | KONSEP_NYERI.md |
| 38 | Skrining Nyeri Perina | `skrining_nyeri_perina` | `skrining_nyeri_perina.php` | KONSEP_NYERI.md |
| 39 | Asesmen Gizi | `asesmen_gizi` | form 39 | KONSEP_ASESMEN_GIZI.md |
| 40 | Detail Nutrisi | `detail_nutrisi` | `detail_nutrisi.php` | KONSEP_ASESMEN_GIZI.md |
| 41 | Rekap Asesmen Nutrisi | `rekap_asesmen_nutrisi` | `rekap_assesment_nutrisi.php` | KONSEP_ASESMEN_GIZI.md |
| 42 | Pesan Makanan & Monitoring Asupan | `pesan_makanan` | `pesan_makanan_monitoring_asupan.php` | KONSEP_ASESMEN_GIZI.md |
| 43 | Diet Pasien | `diet_pasien` | `diit.php` | KONSEP_ASESMEN_GIZI.md |
| 44 | ADIME | `adime` | `adime.php` | KONSEP_ASESMEN_GIZI.md |
| 45 | Daftar Terapi Obat | `daftar_terapi_obat` | form 12 | KONSEP_FARMAKASI.md |
| 46 | Rekonsiliasi Obat | `rekonsiliasi_obat` | form 22 | KONSEP_FARMAKASI.md |
| 47 | Telaah Resep | `telaah_resep` | form 34 | KONSEP_FARMAKASI.md |
| 48 | Telaah Obat | `telaah_obat` | form 35 | KONSEP_FARMAKASI.md |
| 49 | Order Patologi Anatomi | `order_patologi` | form 16 | KONSEP_PENUNJANG_MEDIS_LANJUTAN.md |
| 50 | Order Mikrobiologi | `order_mikrobiologi` | form 17 | KONSEP_PENUNJANG_MEDIS_LANJUTAN.md |
| 51 | Order POCT | `order_poct` | form 18 | KONSEP_PENUNJANG_MEDIS_LANJUTAN.md |
| 52 | Kriteria Masuk ICU | `kriteria_masuk_icu` | form 151 | KONSEP_ICU_HEMODIALISA.md |
| 53 | Kriteria Keluar ICU | `kriteria_keluar_icu` | form 152 | KONSEP_ICU_HEMODIALISA.md |
| 54 | Catatan Keperawatan Rawat Jalan | `catatan_keperawatan_rajal` | `catatan_keperawatan_rajal.php` | KONSEP_ASUHAN_KEPERAWATAN_LANJUTAN.md |
| 55 | Riwayat Penyakit | `riwayat_penyakit` | `riw_cak.php` | KONSEP_ASUHAN_KEPERAWATAN_LANJUTAN.md |
| 56 | Riwayat Keluarga | `riwayat_keluarga` | `riwayat_keluarga_cak.php` | KONSEP_ASUHAN_KEPERAWATAN_LANJUTAN.md |
| 57 | Catatan Medis Visum | `catatan_medis_visum` | form 44 | KONSEP_CATATAN_MEDIS_LANJUTAN.md |
| 58 | Rekam Medis Rujukan | `rekam_medis_rujukan` | `pengantar_rawat.php` | KONSEP_FORMULIR_RUJUKAN.md |

### P3 — Kebidanan, neonatal & MCU

| Form ID baru | Nama Form | Slug | Ref. Tenriawaru | Dokumen Konsep |
|---|---|---|---|---|
| 59 | Asesmen Khusus Kebidanan & Penyakit Kandungan | `asesmen_kebidanan` | `asesmen_khusus_kebidinan_dan_penyakit_kandungan.php` | KONSEP_OBSTETRI_NEONATAL.md |
| 60 | Pengkajian Awal Keperawatan Perina | `pengkajian_perina` | `perina.php` | KONSEP_OBSTETRI_NEONATAL.md |
| 61 | Pengkajian Awal Keperawatan Non-Perina | `pengkajian_non_perina` | `non_perina.php` | KONSEP_OBSTETRI_NEONATAL.md |
| 62 | Kala I-IV | `kala_persalinan` | `kala.php` | KONSEP_OBSTETRI_NEONATAL.md |
| 63 | Riwayat Persalinan Sekarang | `riwayat_persalinan` | `riwayat_persalinan_sekarang_perina.php` | KONSEP_OBSTETRI_NEONATAL.md |
| 64 | Pelaksanaan Bayi Baru Lahir | `bayi_baru_lahir` | `pelaksanaan_bayi_baru_lahir_perina.php` | KONSEP_OBSTETRI_NEONATAL.md |
| 65 | Riwayat Resusitasi Bayi | `riwayat_resusitasi_bayi` | `riwayat_resusitasi_perina.php` | KONSEP_OBSTETRI_NEONATAL.md |
| 66 | Penilaian Bayi dengan APGAR Score | `penilaian_bayi_dengan_apgar_score` | `penilaian_bayi_dengan_apgar_score_perina.php` | KONSEP_OBSTETRI_NEONATAL.md |
| 67 | Down Score (Neonatal) | `down_score` | `down_score_perina.php` | KONSEP_OBSTETRI_NEONATAL.md |
| 68 | Inisiasi Menyusui Dini | `inisiasi_menyusui_dini` | `inisisiasi_menyusu_dini_perina.php` | KONSEP_OBSTETRI_NEONATAL.md |
| 69 | Skrining MDPK/MPP | `skrining_mpp` | `skrining_mpp.php` | KONSEP_OBSTETRI_NEONATAL.md |
| 70 | Kesimpulan MCU | `kesimpulan_mcu` | form 79 | KONSEP_FORM_MCU.md |
| 71 | Catatan Keperawatan / TTV MCU | `ttv_mcu` | form 65 | KONSEP_FORM_MCU.md |
| 72 | Visus Mata MCU | `visus_mata_mcu` | form 66 | KONSEP_FORM_MCU.md |
| 73 | Obstetri MCU | `obstetri_mcu` | form 67 | KONSEP_FORM_MCU.md |
| 74 | Penyakit Dalam MCU | `penyakit_dalam_mcu` | form 68 | KONSEP_FORM_MCU.md |
| 75 | Jantung MCU | `jantung_mcu` | form 69 | KONSEP_FORM_MCU.md |
| 76 | THT MCU | `tht_mcu` | form 70 | KONSEP_FORM_MCU.md |
| 77 | Mata MCU | `mata_mcu` | form 71 | KONSEP_FORM_MCU.md |
| 78 | Gigi MCU | `gigi_mcu` | form 72 | KONSEP_FORM_MCU.md |
| 79 | OBGYN MCU | `obgyn_mcu` | form 73 | KONSEP_FORM_MCU.md |
| 80 | Bedah MCU | `bedah_mcu` | form 74 | KONSEP_FORM_MCU.md |
| 81 | Neurologi MCU | `neurologi_mcu` | form 75 | KONSEP_FORM_MCU.md |
| 82 | Gizi MCU | `gizi_mcu` | form 76 | KONSEP_FORM_MCU.md |
| 83 | Urologi MCU | `urologi_mcu` | form 77 | KONSEP_FORM_MCU.md |
| 84 | Psikologi MCU | `psikologi_mcu` | form 78 | KONSEP_FORM_MCU.md |

### P4 — Bedah, anestesi, Marquee & suficial

| Form ID baru | Nama Form | Slug | Ref. Tenriawaru | Dokumen Konsep |
|---|---|---|---|---|
| 85 | Asesmen Pra Bedah | `asesmen_pra_bedah` | form 87 | KONSEP_BEDAH_ANESTESI.md |
| 86 | Evaluasi Pre-Anestesi & Sedasi | `evaluasi_pre_anestesi_sedasi` | form 41 | KONSEP_BEDAH_ANESTESI.md |
| 87 | Asesmen Post-Anestesi & Sedasi | `asesmen_post_anestesi_sedasi` | `assesmen_post-anestesi_dan_sedasi.php` | KONSEP_BEDAH_ANESTESI.md |
| 88 | Monitoring Anestesi & Sedasi | `monitoring_anestesi_sedasi` | form 42 | KONSEP_BEDAH_ANESTESI.md |
| 89 | Check List Keamanan Pasien Operasi | `checklist_keamanan_operasi` | form 40 | KONSEP_BEDAH_ANESTESI.md |
| 90 | Check List Kesiapan Anestesi | `checklist_kesiapan_anestesi` | form 43 | KONSEP_BEDAH_ANESTESI.md |
| 91 | Pengkajian Pre/Intra/Post Operasi | `pengkajian_operasi` | form 45 | KONSEP_BEDAH_ANESTESI.md |
| 92 | Laporan Operasi | `laporan_operasi` | form 27 | KONSEP_BEDAH_ANESTESI.md |
| 93 | Catatan Bedah | `catatan_bedah` | `catatan_bedah.php` | KONSEP_BEDAH_ANESTESI.md |
| 94 | Transfer Pasien Antar Ruangan | `transfer_pasien` | `transfer_pasien_antar_ruangan.php` | KONSEP_BEDAH_ANESTESI.md |
| 95 | Penandaan Lokasi Operasi | `penandaan_lokasi_operasi` | form 59 | KONSEP_BEDAH_ANESTESI.md |
| 96 | Penandaan Pasien | `penandaan_pasien` | form 150 | KONSEP_BEDAH_ANESTESI.md |
| 97 | Ceklis Pre & Post Tindakan Invasif | `ceklis_tindakan_invasif` | `ceklis_pre_dan_operatif_tindakan_infasif.php` | KONSEP_BEDAH_ANESTESI.md |
| 98 | Surveilans Luka Insisi | `surveilans_luka_insisi` | `surveilans_pasien_operasi_dengan_luka_insisi.php` | KONSEP_BEDAH_ANESTESI.md |
| 99 | Catatan Anestesia | `catatan_anestesia` | `catatan_anestesia.php` | KONSEP_BEDAH_ANESTESI.md |
| 100 | Ceklist Keamanan Angiografi | `ceklist_keamanan_angiografi` | `ceklist_keamanan_angiosgrafi.php` | KONSEP_BEDAH_ANESTESI.md |
| 101 | Asesmen Katarak | `asesmen_katarak` | form 133 | KONSEP_BEDAH_ANESTESI.md |

### P5 — Hemodialisa, kemo, transfusi & psikososial

| Form ID baru | Nama Form | Slug | Ref. Tenriawaru | Dokumen Konsep |
|---|---|---|---|---|
| 102 | Pre Hemodialisa | `pre_hemodialisa` | form 36 | KONSEP_ICU_HEMODIALISA.md |
| 103 | Intra Hemodialisa | `intra_hemodialisa` | `intra_hemodialisa.php` | KONSEP_ICU_HEMODIALISA.md |
| 104 | Post Hemodialisa | `post_hemodialisa` | `post_hemodialisa.php` | KONSEP_ICU_HEMODIALISA.md |
| 105 | Triage Hemodialisa | `triage_hemodialisa` | form 131 | KONSEP_ICU_HEMODIALISA.md |
| 106 | Asesmen Kemoterapi | `asesmen_kemoterapi` | `asesmen_kemoterapi.php` | KONSEP_ONKOLOGI_TRANSFUSI.md |
| 107 | Pemantauan Kemoterapi | `pemantauan_kemoterapi` | form 20 | KONSEP_ONKOLOGI_TRANSFUSI.md |
| 108 | Asesmen Pasien dengan Darah | `asesmen_pemberian_darah` | `assement_darah.php` | KONSEP_ONKOLOGI_TRANSFUSI.md |
| 109 | Asesmen Transfusi | `assesmen_transfusi` | `assement_transfusi.php` | KONSEP_ONKOLOGI_TRANSFUSI.md |
| 110 | Monitoring Transfusi Darah | `monitoring_transfusi` | `monitoring_transfusi_darah.php` | KONSEP_ONKOLOGI_TRANSFUSI.md |
| 111 | Double Check Obat High Alert | `double_check_high_alert` | form 158 | KONSEP_ONKOLOGI_TRANSFUSI.md |
| 112 | Asesmen Psikologi Pasien & Keluarga | `asesmen_psikologi` | `assesmen_psikologi_pasien.php` | KONSEP_PSIKOSOSIAL_KEROHANIAN.md |
| 113 | Asesmen Spiritual | `asesmen_spiritual` | `assesmen_spiritual.php` | KONSEP_PSIKOSOSIAL_KEROHANIAN.md |
| 114 | Permintaan Pelayanan Kerohanian | `permintaan_kerohanian` | `permintaan_pelayanan_kerohanian.php` | KONSEP_PSIKOSOSIAL_KEROHANIAN.md |
| 115 | Bukti Pelayanan Kerohanian | `bukti_kerohanian` | `bukti_pelayanan_kerohanian.php` | KONSEP_PSIKOSOSIAL_KEROHANIAN.md |
| 116 | Surveilans CVC PICC | `surveilans_cvc_picc` | `surveilans_pasien_terpasang_CVC_atau_PICC.php` | KONSEP_SURVEILANS_INFEKSI.md |
| 117 | Surveilans IV Catheter | `surveilans_iv_catheter` | `surveilans_pasien_terpasang_IV_catheter_perifer.php` | KONSEP_SURVEILANS_INFEKSI.md |
| 118 | Surveilans Kateter Urine | `surveilans_kateter_urine` | `surveilans_pasien_terpasang_kateter_urine.php` | KONSEP_SURVEILANS_INFEKSI.md |
| 119 | Surveilans Tirah Baring | `surveilans_tirah_baring` | `surveilans_pasien_tirah_baring_total.php` | KONSEP_SURVEILANS_INFEKSI.md |
| 120 | Screening Akses | `screening_akses` | `screening_akses.php` | KONSEP_SURVEILANS_INFEKSI.md |
| 121 | Asesmen Pasien Restraint | `asesmen_restraint` | `asesmen_pasien_restraint.php` | KONSEP_SURVEILANS_INFEKSI.md |
| 122 | Observasi Restraint | `observasi_restraint` | `asesmen_pasien_restraint_observasi.php` | KONSEP_SURVEILANS_INFEKSI.md |
| 123 | Asesmen Akhir Kehidupan | `asesmen_akhir_kehidupan` | `assesment_pasien_akhir_kehidupan.php` | KONSEP_ASUHAN_KEPERAWATAN_LANJUTAN.md |
| 124 | Uji Fungsi & Asesment | `uji_fungsi_asesment` | `uji_fungsi_dan_assesment.php` | KONSEP_ASUHAN_KEPERAWATAN_LANJUTAN.md |
| 125 | Respon Emosi & Status Mental | `respon_emosi` | `respon_emosi_cak.php` | KONSEP_ASUHAN_KEPERAWATAN_LANJUTAN.md |
| 126 | Surat Keterangan Sehat | `surat_keterangan_sehat` | form 129 | KONSEP_FORMULIR_RUJUKAN.md |
| 127 | Pesan Penunjang | `pesan_penunjang` | form 28/103/102/139 | KONSEP_FORMULIR_RUJUKAN.md |
| 128 | SPRI Rujukan Balik | `spri_rujukan_balik` | form 127 `program_rujuk_balik.php` | KONSEP_FORMULIR_RUJUKAN.md |
| 129 | Katalog Form Blanko | `katalog_form_blanko` | `katalog_form_blanko.php` | KONSEP_KATALOG_FORM_BLANKO.md |

---

## 4. Temuan Teknis yang Mempengaruhi Seluruh Rencana

### 4.1 Form 1 "Catatan Awal Medis" adalah stub mati
- Ter-seed di `form` + `objek_form_control` (~15 mapping) + `akses_ehr`
- `id_dash_menu = NULL` → tidak pernah muncul di dashboard pasien
- Tidak ada controller/view → buka `/emr/form/catatan_awal_medis/...` render `Unsupported.blade.php`
- **Aksi:** selesaikan (tirar dengan `id_dash_menu` + controller) atau soft-delete.
  Disarankan: **selesaikan** karena Tenriawaru punya form 1 yang equivalente.

### 4.2 Tidak ada skema deklaratif form
Tidak ada kolom tipe kontrol / label / urutan / opsi di DB. Artinya setiap form
baru = tulis controller + Blade secara manual. Aturan main ada di
[`PANDUAN_IMPLEMENTASI_FORM_EMR.md`](PANDUAN_IMPLEMENTASI_FORM_EMR.md).

### 4.3 Field berulang harus di-suffix
`variabel` harus unik per form (`EmrHelper::emrDetailByVariabel` melakukan
`pluck('value','variabel')`). Untuk field yang bisa diulang (terapi obat, convin,
checklist) gunakan sufiks angka: `obat_1..obat_N`. Ini menimbulkan banyak baris
`objek_form_control` — tetap ikuti polanya, jangan//////////////// devi asi.

### 4.4 `akses_ehr` wajib diisi
`EmrDashboardController` INNER JOIN ke `akses_ehr`. Tanpa baris `akses_ehr`,
form tidak tampil dan selalu 403.

### 4.5 Risiko `id_dash_menu`
`header_ehr` membangun string dari PK nyata `dashboard_menu*`. Kalau menu di-re-ID,
form yatim dari dashboard. Bandingkan dengan `===`, bukan `==` (pernah bug `"1.1" == "1.10"`).

---

## 5. Roadmap Prioritas

| Fase | Form | Estimasi | Prasyarat |
|---|---|---|---|
| **Fase 1** — Rawat inap inti | 16–31 (P1) | 3–4 minggu | Master Implementasi sudah ada |
| **Fase 2** — Skala & CNE | 32–58 (P2) | 3–4 minggu | Helper skala seperti `RisikoJatuhHelper` |
| **Fase 3** — Kebidanan | 59–69 | 2–3 minggu | Master kebidanan, master Persalinan |
| **Fase 4** — Bedah & anestesi | 85–101 (P4) | 3–4 minggu | Master tindakan bedah, anestesi |
| **Fase 5** — HD, onkologi,pj lahir | 102–128 | 2–3 minggu | Master obat kemo, Master Bank Darah |
| **Fase 6** — MCU | 70–84 | 2–3 minggu | `docs/KONSEP_MCU.md` (sudah ada) |

Fase 1 adalah fondasi rawat inap — **mulai dari situ** untuk lanjutan bisa dititipkan
pada episode rawat inap.

---

## 6. Dokumen Konsep yang Tersedia

> ⚠️ **Baca [`ALOKASI_ID_GLOBAL.md`](ALOKASI_ID_GLOBAL.md) lebih dulu.** Dokumen
> konsep ditulis paralel sehingga Initially bentrok ID. Ledger tersebut mengunci
> `objek_id`, `dashboard_menu_sub_id`, `dashboard_menu_sub_extra_id`, `profesi_id`,
> dan slug final untuk seluruh form — **ledger yang mengikat**, bukan tabel di
> dokumen ini.

Semua di folder `docs/`:

| File | Cakupan |
|---|---|
| `ALOKASI_ID_GLOBAL.md` | **Rujukan tunggal alokasi ID** |
| `PANDUAN_IMPLEMENTASI_FORM_EMR.md` | **Aturan umum menambah form** |
| `KONSEP_CATATAN_MEDIS_LANJUTAN.md` | 16, 20, 21, 57 |
| `KONSEP_DISCHARGE_PLANNING.md` | 17, 18 |
| `KONSEP_TRIASE_IGD.md` | 19 |
| `KONSEP_ASUHAN_KEPERAWATAN_LANJUTAN.md` | 22, 23, 29, 31, 34, 35, 54, 55, 56, 123, 124, 125 |
| `KONSEP_CAIRAN_BALANCE.md` | 24, 25, 26, 28 |
| `KONSEP_NYERI.md` | 27, 36, 37, 38 |
| `KONSEP_RISIKO_JATUH_LANJUTAN.md` | 30, 32, 33 |
| `KONSEP_ASESMEN_GIZI.md` | 39–44 |
| `KONSEP_FARMAKASI.md` | 45–48 |
| `KONSEP_PENUNJANG_MEDIS_LANJUTAN.md` | 49–51 |
| `KONSEP_ICU_HEMODIALISA.md` | 52, 53, 102–105 |
| `KONSEP_FORMULIR_RUJUKAN.md` | 58, 126, 127, 128 |
| `KONSEP_OBSTETRI_NEONATAL.md` | 59–69 |
| `KONSEP_FORM_MCU.md` | 70–84 |
| `KONSEP_BEDAH_ANESTESI.md` | 85–101 |
| `KONSEP_ONKOLOGI_TRANSFUSI.md` | 106–111 |
| `KONSEP_PSIKOSOSIAL_KEROHANIAN.md` | 112–115 |
| `KONSEP_SURVEILANS_INFEKSI.md` | 116–122 |
| `KONSEP_KATALOG_FORM_BLANKO.md` | 129 |

Dokumen yang sudah ada sebelumnya dan **tidak diubah** oleh analisis ini:
`KONSEP_GAWAT_DARURAT.md`, `KONSEP_IMPLEMENTASI_KEPERAWATAN.md`,
`KONSEP_MCU.md`, `KONSEP_PENUNJANG_MEDIS.md`, `KONSEP_RAWAT_INAP.md`,
`KONSEP_REHABILITASI_MEDIK.md`, `KONSEP_TINDAKAN_MEDIS.md`,
`PENGKAJIAN_RISIKO_JATUH.md`, `ROMBAK_MASTER_TINDAKAN.md`.

---

## 7. Catatan Teknis

- Dokumen ini adalah **konsep** — belum ada kode yang ditulis.
- Saat implementasi, wajib ikuti
  [`PANDUAN_IMPLEMENTASI_FORM_EMR.md`](PANDUAN_IMPLEMENTASI_FORM_EMR.md) dan
  `AGENTS.md`.
- Jumlah objek akan bertambah dari 177 → ± 500-an. Pertimbangkan filtering objek
  per menu di `Administrator/ManajemenEMR/Form` agar tetap terkendali.