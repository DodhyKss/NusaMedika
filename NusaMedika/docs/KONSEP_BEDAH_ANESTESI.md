# Konsep Form Bedah, Anestesi & Keamanan Operasi — NusaMedika

**Status:** Rancangan / Konsep — belum diimplementasikan
**Tanggal:** 2026-10-10
**Cakupan:** `form_id` **85–101** (17 form)
**Acuan wajib:** [`PANDUAN_IMPLEMENTASI_FORM_EMR.md`](PANDUAN_IMPLEMENTASI_FORM_EMR.md), `AGENTS.md`, [`ANALISIS_KEKURANGAN_FORM.md`](ANALISIS_KEKURANGAN_FORM.md)
**Alokasi `objek_id`:** **762–992** (231 id band; **223 objek baru terpakai**, 762–984); objek 1–177 dipakai ulang.

---

## 1. Ringkasan

| form_id | nama_form | slug | id_dash_menu | ri | rj | igd | mcu | objek baru |
|---|---|---|---|---|---|---|---|---|
| 85 | Asesmen Pra Bedah | `asesmen_pra_bedah` | `15.293` | 1 | 1 | 1 | 0 | 762–774 (13) |
| 86 | Evaluasi Pre-Anestesi & Sedasi | `evaluasi_pre_anestesi_sedasi` | `15.294` | 1 | 0 | 1 | 0 | 775–793 (19) |
| 87 | Asesmen Post-Anestesi & Sedasi | `asesmen_post_anestesi_sedasi` | `15.295` | 1 | 0 | 0 | 0 | 794–809 (16) |
| 88 | Monitoring Anestesi & Sedasi | `monitoring_anestesi_sedasi` | `15.296` | 1 | 0 | 1 | 0 | 810–819 (10) |
| 89 | Check List Keamanan Pasien Operasi | `checklist_keamanan_operasi` | `15.297` | 1 | 0 | 1 | 0 | 820–833, 980–982 (17) |
| 90 | Check List Kesiapan Anestesi | `checklist_kesiapan_anestesi` | `15.298` | 1 | 0 | 1 | 0 | 834–841 (8) |
| 91 | Pengkajian Pre/Intra/Post Operasi | `pengkajian_operasi` | `15.299` | 1 | 0 | 1 | 0 | 842–851 (10) |
| 92 | Laporan Operasi | `laporan_operasi` | `15.300` | 1 | 0 | 0 | 0 | 852–866, 983–984 (17) |
| 93 | Catatan Bedah | `catatan_bedah` | `15.301` | 1 | 0 | 0 | 0 | 867–882 (16) |
| 94 | Transfer Pasien Antar Ruangan | `transfer_pasien` | `15.302` | 1 | 0 | 1 | 0 | 883–897 (15) |
| 95 | Penandaan Lokasi Operasi | `penandaan_lokasi_operasi` | `15.303` | 1 | 0 | 1 | 0 | 898–902 (5) |
| 96 | Penandaan Pasien | `penandaan_pasien` | `15.304` | 1 | 0 | 1 | 0 | 903–910 (8) |
| 97 | Ceklis Pre & Post Tindakan Invasif | `ceklis_tindakan_invasif` | `15.305` | 1 | 0 | 1 | 0 | 911–923 (13) |
| 98 | Surveilans Luka Insisi | `surveilans_luka_insisi` | `15.306` | 1 | 0 | 0 | 0 | 924–936 (13) |
| 99 | Catatan Anestesia | `catatan_anestesia` | `15.307` | 1 | 0 | 1 | 0 | 937–954 (18) |
| 100 | Ceklist Keamanan Angiografi | `ceklist_keamanan_angiografi` | `15.308` | 1 | 0 | 1 | 0 | 955–967 (13) |
| 101 | Asesmen Katarak | `asesmen_katarak` | `15.309` | 0 | 1 | 0 | 0 | 968–979 (12) |

> **Id band 762–992 yang tidak terpakai:** 985–992 (8 id dicadangkan, tidak
> dideklarasikan di `$objeks`). Band objek tidak berubah dan `id_dash_menu` tetap
> `15.293`–`15.309`.
>
> Objek 980–982 (form 89) dan 983–984 (form 92) sengaja memakai id di ujung
> bawah band: sebelumnya objek 833 dipakai dua nama dalam form 89
> ("Antisipasi Kejadian Krisis" dan "Data Tindakan & Alat"), dan objek 853 dua
> nama dalam form 92 ("Jam Mulai Operasi" dan "Jam Selesai Operasi"). Sekarang
> setiap objek punya satu `nama_objek`.

### 1.1 Struktur menu

```php
// $menus
['dashboard_menu_id' => 15, 'nama_menu' => 'Bedah & Anestesi'],

// $subMenus — tanpa extra, id_dash_menu = "15.N"
['dashboard_menu_sub_id' => 293,  'dashboard_menu_id' => 15, 'nama_sub_menu' => 'Asesmen Pra Bedah'],
['dashboard_menu_sub_id' => 294,  'dashboard_menu_id' => 15, 'nama_sub_menu' => 'Evaluasi Pre Anestesi Sedasi'],
['dashboard_menu_sub_id' => 295,  'dashboard_menu_id' => 15, 'nama_sub_menu' => 'Asesmen Post Anestesi Sedasi'],
['dashboard_menu_sub_id' => 296,  'dashboard_menu_id' => 15, 'nama_sub_menu' => 'Monitoring Anestesi Sedasi'],
['dashboard_menu_sub_id' => 297,  'dashboard_menu_id' => 15, 'nama_sub_menu' => 'Checklist Keamanan Operasi'],
['dashboard_menu_sub_id' => 298,  'dashboard_menu_id' => 15, 'nama_sub_menu' => 'Checklist Kesiapan Anestesi'],
['dashboard_menu_sub_id' => 299,  'dashboard_menu_id' => 15, 'nama_sub_menu' => 'Pengkajian Operasi'],
['dashboard_menu_sub_id' => 300,  'dashboard_menu_id' => 15, 'nama_sub_menu' => 'Laporan Operasi'],
['dashboard_menu_sub_id' => 301,  'dashboard_menu_id' => 15, 'nama_sub_menu' => 'Catatan Bedah'],
['dashboard_menu_sub_id' => 302, 'dashboard_menu_id' => 15, 'nama_sub_menu' => 'Transfer Pasien'],
['dashboard_menu_sub_id' => 303, 'dashboard_menu_id' => 15, 'nama_sub_menu' => 'Penandaan Lokasi Operasi'],
['dashboard_menu_sub_id' => 304, 'dashboard_menu_id' => 15, 'nama_sub_menu' => 'Penandaan Pasien'],
['dashboard_menu_sub_id' => 305, 'dashboard_menu_id' => 15, 'nama_sub_menu' => 'Ceklis Tindakan Invasif'],
['dashboard_menu_sub_id' => 306, 'dashboard_menu_id' => 15, 'nama_sub_menu' => 'Surveilans Luka Insisi'],
['dashboard_menu_sub_id' => 307, 'dashboard_menu_id' => 15, 'nama_sub_menu' => 'Catatan Anestesia'],
['dashboard_menu_sub_id' => 308, 'dashboard_menu_id' => 15, 'nama_sub_menu' => 'Ceklist Keamanan Angiografi'],
['dashboard_menu_sub_id' => 309, 'dashboard_menu_id' => 15, 'nama_sub_menu' => 'Asesmen Katarak'],
```

### 1.2 Temuan penting dari legacy (relevan untuk desain)

| Temuan | Legacy | Keputusan desain NusaMedika |
|---|---|---|
| `check_list_kesiapan_anestesi.php` & `monitoring_anestesi_sedasi.php` | **hanya upload gambar** (scan formulir) ke `STORED/.../ARSIP/*.txt` | Dibuat **native structured** (form 90 & 88) + tetap simpan `lampiran` sebagai bukti scan bila perlu |
| `pengkajian_pre_intra_post_operasi.php` | accordion 3 panel (Pre/Intra/Post) **tanpa field** — hanya `data_image` | Dibuat native dengan section Pre/Intra/Post (form 91) |
| `penandaan_lokasi_operasi.php` | hanya `data_image` | Dibuat native dengan field lokasi & petugas (form 95) |
| `evaluasi_pra_anestesi_atau_sedasi.php` | 2.239 baris, ~100 variabel | Dipetakan ke 19 objek grup per sistem organ |
| `monitoring_post_anestesi.php` | Aldrete/Bromage/Steward/PADSS diisi `<select>0/1/2` **tanpa label kriteria** | Diimplementasikan dengan **tabel kriteria lengkap** (lihat §5 form 87) + hitung server |
| `laporan_operasi.php` | hanya memilih "tindakan" dari slot bedah | Digesti form 92 memuat field laporan operasi yang sebenarnya ada di `isi_laporan_operasi.php` |
| `ceklis_pre_dan_operatif_tindakan_infasif.php` | 1.365 baris, ~70 kolom_status | Dipetakan ke 13 objek grup (form 97) |
| `intra_operatif_anestesi.php` | 4.223 baris monitoring + kasa | Dipetakan ke form 88 & 93 |
| `surveilans_pasien_operasi_dengan_luka_insisi.php` | redirect ke `insisi_old.php` / `insisi_New.php` | Satu form 98 dengan tiga tahap (Pre/During/Post) |

---

## 2. Sumber Referensi Legacy

Path relatif terhadap `/simrs_tenriawaru/FE/lib/modul/`.

| Form | File legacy |
|---|---|
| 85 | `assement_pra_bedah.php` (1 baris `data_image` + sub-form), `pengkajian_perioperatif.php` |
| 86 | `evaluasi_pra_anestesi_atau_sedasi.php`, `assesmen_pra-anestesi_dan_sedasi.php` |
| 87 | `assesmen_post-anestesi_dan_sedasi.php`, `monitoring_post_anestesi.php` |
| 88 | `monitoring_anestesi_sedasi.php` (upload gambar), `intra_operatif_anestesi.php` |
| 89 | `check_list_keselamatan_pasien_operasi.php` + `sebelum_induksi_anestesi.php`, `sebelum_insisi.php`, `sebelum_pasien_meninggalkan_ruang.php` |
| 90 | `check_list_kesiapan_anestesi.php` (upload gambar) |
| 91 | `pengkajian_pre_intra_post_operasi.php` |
| 92 | `laporan_operasi.php`, `isi_laporan_operasi.php`, `laporan_operasi_resume.php` |
| 93 | `catatan_bedah.php` |
| 94 | `transfer_pasien_antar_ruangan.php`, `transfer_pasien_dari_igd_ke_ruangan.php` |
| 95 | `penandaan_lokasi_operasi.php` |
| 96 | `penandaan_pasien.php` |
| 97 | `ceklis_pre_dan_operatif_tindakan_infasif.php`, `assesmen_awal_keperawatan_tindakan_invasif_non_bedah.php` |
| 98 | `surveilans_pasien_operasi_dengan_luka_insisi.php` → `pre_op_insisi.php`, `durante_op_insisi.php`, `post_op_insisi.php` |
| 99 | `catatan_anestesia.php` |
| 100 | `ceklist_keamanan_angiosgrafi.php` |
| 101 | `asesmen_katarak.php` |

---

## 3. Form 85 — Asesmen Pra Bedah

### Rasional
Asesmen pra-operasi oleh dokter: riwayat penyakit, alergi, pemeriksaan fisik, hasil penunjang, rencana tindakan, kemungkinan komplikasi, dan rencana penanganan komplikasi. Mengiplikasikan `assement_pra_bedah.php` (Rencana tindakan / Kemungkinan Komplikasi / Penatalaksanaan Komplikasi / Back up plan / Dubia ad Bonam / Dubia ad Malam).

### Struktur Field

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `tanggal_asesmen` | 762 | Tanggal Asesmen Pra Bedah | date | ya | |
| `jam_asesmen` | 763 | Jam Asesmen Pra Bedah | time | ya | |
| `dokter_penilai_id` | 764 | Dokter Penilai Pra Bedah | `x-select_dokter` | ya | |
| `keluhan` | 13 | Keluhan Utama (reuse) | textarea | ya | |
| `diagnosa_pra_operasi` | 765 | Temuan Pemeriksaan Fisik & Diagnosis | textarea | ya | |
| `temuan_fisik_pra_bedah` | 765 | Temuan Pemeriksaan Fisik & Diagnosis | textarea | ya | |
| `riwayat_alergi` | 17 | Alergi (reuse) | textarea | ya | *"Tidak ada"* bila tidak ada |
| `tanggal_rencana_operasi` | 766 | Tanggal Rencana Operasi | date | ya | |
| `jam_rencana_operasi` | 766 | Tanggal Rencana Operasi | time | ya | |
| `jenis_operasi` | 767 | Rencana Tindakan Pra Bedah | select | ya | Efektif / Darurat |
| `rencana_tindakan_operasi` | 767 | Rencana Tindakan Pra Bedah | textarea | ya | |
| `kebutuhan_edukasi` | 768 | Kebutuhan Edukasi Pra Bedah | checkbox grup | ya |[item] |
| `kebutuhan_edukasi_lain` | 769 | Kebutuhan Edukasi Lainnya | text | ya bila Lainnya | |
| `hasil_laboratorium` | 770 | Hasil Laboratorium Pra Bedah | textarea | tidak | |
| `hasil_radiologi` | 771 | Hasil Radiologi Pra Bedah | textarea | tidak | |
| `hasil_penunjang_lain` | 771 | Hasil Radiologi Pra Bedah | textarea | tidak | idem |
| `kemungkinan_komplikasi` | 772 | Rencana Komplikasi | textarea | ya | |
| `rencana_komplikasi` | 772 | Rencana Komplikasi | textarea | ya | |
| `back_up_plan` | 773 | Back Up Plan | textarea | ya bila diperlukan | |
| `prognosis_dubia_ad_bonam` | 774 | Prognosis | radio | ya | Dubia ad Bonam / Dubia ad Malam |
| `catatan_prognosis` | 774 | Prognosis | textarea | tidak | |

### Dashboard & Form Master

```php
['form_id' => 85, 'nama_form' => 'Asesmen Pra Bedah', 'slug' => 'asesmen_pra_bedah', 'id_dash_menu' => '15.293', 'ri' => 1, 'rj' => 1, 'igd' => 1, 'mcu' => 0],
```

### Mapping

```php
85 => [
    'tanggal_asesmen' => 762, 'jam_asesmen' => 763, 'dokter_penilai_id' => 764,
    'keluhan' => 13,
    'diagnosa_pra_operasi' => 765, 'temuan_fisik_pra_bedah' => 765,
    'riwayat_alergi' => 17,
    'tanggal_rencana_operasi' => 766, 'jam_rencana_operasi' => 766,
    'jenis_operasi' => 767, 'rencana_tindakan_operasi' => 767,
    'kebutuhan_edukasi_lain' => 769,
    'hasil_laboratorium' => 770, 'hasil_radiologi' => 771, 'hasil_penunjang_lain' => 771,
    'kemungkinan_komplikasi' => 772, 'rencana_komplikasi' => 772,
    'back_up_plan' => 773,
    'prognosis_dubia_ad_bonam' => 774, 'catatan_prognosis' => 774,
],
// Kebutuhan edukasi (item tetap)
$itemEdukasi = ['Persiapan Mentas', 'Persiapan Lensa', 'Puasa', 'Pemberian Obat', 'Lainnya'];
foreach ($itemEdukasi as $e) { $mapping[85]['kebutuhan_edukasi_'.$e] = 768; }
```

> Ganti `$itemEdukasi` dengan label final bersama tim anestesi.

### Akses EHR

```php
['profesi_id' => 1, 'form_id' => 85, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 2, 'form_id' => 85, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
```

### Validasi

- `tanggal_rencana_operasi` `required|date|after_or_equal:today` bila `jenis_operasi = 'Efektif'`.
- `jenis_operasi` `required|in:Efektif,Darurat`.
- `prognosis_dubia_ad_bonam` `required|in:Dubia ad Bonam,Dubia ad Malam`.
- `back_up_plan` wajib bila ada komplikasi yang direncanakan.

### Cetak

`.../AsesmenPraBedah/print.blade.php`.

---

## 4. Form 86 — Evaluasi Pre-Anestesi & Sedasi

### Rasional
Evaluasi pra-anestesi sebelum operasi atau sedasi. Legacy `evaluasi_pra_anestesi_atau_sedasi.php` (2.239 baris) memuat anamnesis (~100 variabel) yang dikelompokkan per sistem organ. Desain ini mempertahankan seluruh cakupan dengan **19 objek grup**, satu per sistem.

### Struktur Field

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `tanggal_evaluasi` | 775 | Tanggal Evaluasi Pre-Anestesi | date | ya | |
| `jam_evaluasi` | 776 | Jam Evaluasi Pre-Anestesi | time | ya | |
| `dokter_anestesi_id` | 777 | Dokter Anestesi Penilai | `x-select_dokter` | ya | |
| `nama_pasien` | 778 | Anamnesis Pre-Anestesi | text | ya | prefilled |
| `tanggal_lahir` | 778 | Anamnesis Pre-Anestesi | date | ya | hitung umur |
| `jenis_kelamin` | 778 | Anamnesis Pre-Anestesi | radio | ya | |
| `pekerjaan` | 778 | Anamnesis Pre-Anestesi | select | ya | `SelectOption::pekerjaan` |
| `riwayat_penyakit_keluarga` | 778 | Anamnesis Pre-Anestesi | textarea | ya | |
| `riwayat_pengobatan` | 778 | Anamnesis Pre-Anestesi | textarea | ya | |
| `alergi_latraks` | 787 | Alergi Pre-Anestesi | radio | ya | Tidak / Ada |
| `alergi_makanan` | 787 | Alergi Pre-Anestesi | radio | ya | |
| `alergi_plaster` | 787 | Alergi Pre-Anestesi | radio | ya | |
| `alergi_obat_timbul` | 787 | Alergi Pre-Anestesi | textarea | ya bila ada | |
| `jalan_nafas` | 779 | Jalan Napas & Tidur Jalan | radio | ya | |
| `gangguan_pernafasan` | 779 | Jalan Napas & Tidur Jalan | textarea | ya bila ada | |
| `snoring` | 779 | Jalan Napas & Tidur Jalan | radio | ya | Mengorok //snoring saat tidur |
| `pernapasan` | 780 | Sistem Pernapasan | radio | ya | |
| `penyakit_paru` | 780 | Sistem Pernapasan | textarea | tidak | |
| `suhu` | 780 | Sistem Pernapasan | number | ya | °C |
| `td` | 780 | Sistem Pernapasan | text | ya | sistolik/diastolik |
| `nadi` | 780 | Sistem Pernapasan | number | ya | |
| `jantung` | 781 | Sistem Kardiovaskular | radio | ya | |
| `op_jantung_koroner` | 781 | Sistem Kardiovaskular | radio | ya | |
| `serangan_jantung` | 781 | Sistem Kardiovaskular | radio | ya | |
| `hipertensi` | 781 | Sistem Kardiovaskular | radio | ya | |
| `riwayat_transfusi` | 781 | Sistem Kardiovaskular | textarea | tidak | |
| `saraf_perifer` | 782 | Sistem Saraf | radio | ya | |
| `pingsan` | 782 | Sistem Saraf | radio | ya | |
| `kejang` | 782 | Sistem Saraf | radio | ya | |
| `stroke` | 782 | Sistem Saraf | radio | ya | |
| `saraf` | 782 | Sistem Saraf | textarea | tidak | |
| `hamil_bulan` | 783 | Sistem Endokrin & Metabolik | number | ya (bila P) | bulan kehamilan |
| `diabetes` | 783 | Sistem Endokrin & Metabolik | radio | ya | |
| `gangguan_tiroid` | 783 | Sistem Endokrin & Metabolik | radio | ya | |
| `obesitas` | 783 | Sistem Endokrin & Metabolik | radio | ya | |
| `gula_darah` | 783 | Sistem Endokrin & Metabolik | number | ya | |
| `ginjal_gagal` | 784 | Sistem Ginjal & Kencing | radio | ya | |
| `susah_kencing` | 784 | Sistem Ginjal & Kencing | radio | ya | |
| `kreatinin` | 784 | Sistem Ginjal & Kencing | number | ya | |
| `ureum` | 784 | Sistem Ginjal & Kencing | number | ya | |
| `abdomen` | 785 | Sistem Pencernaan | radio | ya | |
| `maag` | 785 | Sistem Pencernaan | radio | ya | |
| `muntah` | 785 | Sistem Pencernaan | radio | ya | |
| `faring` | 785 | Sistem Pencernaan | radio | ya | |
| `gigi_geligi` | 785 | Sistem Pencernaan | radio | ya | |
| `mobilitas` | 785 | Sistem Pencernaan | select | ya | |
| `pernafasan_2` | 786 | Sistem Darah & Koagulasi | — | — | *reassign ke 786* |
| `anemia` | 786 | Sistem Darah & Koagulasi | radio | ya | |
| `leukosit` | 786 | Sistem Darah & Koagulasi | number | ya | |
| `trombosit` | 786 | Sistem Darah & Koagulasi | number | ya | |
| `kadar_gula_darah` | 786 | Sistem Darah & Koagulasi | number | ya | |
| `prothrombin_time` | 786 | Sistem Darah & Koagulasi | number | ya | |
| `asa` | 789 | Skor ASA | select | ya | I–VI (lihat §Validasi) |
| `nyeri` | 14 | Nyeri (reuse) | radio | ya | |
| `kajar_aspirasi` | 788 | Kajar & Risiko Aspirasi | radio | ya | |
| `ceklis_equip_emergi` | 788 | Kajar & Risiko Aspirasi | radio | ya | |
| `perlu_alat_bantu` | 788 | Kajar & Risiko Aspirasi | textarea | ya bila perlu | |
| `tipe_anestesia` | 790 | Rencana Anestesi | select | ya | Umum / Regional / Lokal / Sedasi |
| `anes_umum` | 790 | Rencana Anestesi | radio | ya | |
| `anes_regional` | 790 | Rencana Anestesi | radio | ya | |
| `sedasi` | 791 | Rencana Sedasi | radio | ya | |
| `persetujuan_anestesi` | 792 | Persetujuan & Dokumentasi | radio | ya | Sudah / Belum |
| `catatan_evaluasi` | 793 | Catatan Evaluasi Pre-Anestesi | textarea | ya | |

### Dashboard & Form Master

```php
['form_id' => 86, 'nama_form' => 'Evaluasi Pre-Anestesi & Sedasi', 'slug' => 'evaluasi_pre_anestesi_sedasi', 'id_dash_menu' => '15.294', 'ri' => 1, 'rj' => 0, 'igd' => 1, 'mcu' => 0],
```

### Mapping

```php
86 => [
    'tanggal_evaluasi' => 775, 'jam_evaluasi' => 776, 'dokter_anestesi_id' => 777,
    'nama_pasien' => 778, 'tanggal_lahir' => 778, 'jenis_kelamin' => 778, 'pekerjaan' => 778,
    'riwayat_penyakit_keluarga' => 778, 'riwayat_pengobatan' => 778,
    'jalan_nafas' => 779, 'gangguan_pernafasan' => 779, 'snoring' => 779,
    'pernapasan' => 780, 'suhu' => 780, 'td' => 780, 'nadi' => 780, 'penyakit_paru' => 780,
    'jantung' => 781, 'op_jantung_koroner' => 781, 'serangan_jantung' => 781, 'hipertensi' => 781, 'riwayat_transfusi' => 781,
    'saraf_perifer' => 782, 'pingsan' => 782, 'kejang' => 782, 'stroke' => 782, 'saraf' => 782,
    'hamil_bulan' => 783, 'diabetes' => 783, 'gangguan_tiroid' => 783, 'obesitas' => 783, 'gula_darah' => 783,
    'ginjal_gagal' => 784, 'susah_kencing' => 784, 'kreatinin' => 784, 'ureum' => 784,
    'abdomen' => 785, 'maag' => 785, 'muntah' => 785, 'faring' => 785, 'gigi_geligi' => 785, 'mobilitas' => 785,
    'anemia' => 786, 'leukosit' => 786, 'trombosit' => 786, 'kadar_gula_darah' => 786, 'prothrombin_time' => 786,
    'alergi_latraks' => 787, 'alergi_makanan' => 787, 'alergi_plaster' => 787, 'alergi_obat_timbul' => 787,
    'kajar_aspirasi' => 788, 'ceklis_equip_emergi' => 788, 'perlu_alat_bantu' => 788,
    'asa' => 789, 'nyeri' => 14,
    'tipe_anestesia' => 790, 'anes_umum' => 790, 'anes_regional' => 790,
    'sedasi' => 791,
    'persetujuan_anestesi' => 792,
    'catatan_evaluasi' => 793,
],
```

### Tabel Skor ASA (American Society of Anesthesiologists)

| Kelas | Deskripsi |
|---|---|
| I | Pasien sehat |
| II | Penyakit ringan/sedang tanpa gangguan fungsional substantif |
| III | Penyakit sedang-berat atau morbiditas mayor |
| IV | Penyakit berat yang menetap terus-menerus |
| V | Moribund, tidak mungkin diselamatkan tanpa operasi |
| VI | Otak mati, organ donor |

> Bahasa tabel di atas perlu ditulis ulang dalam Bahasa Indonesia pada implementasi (lihat `asa` di legacy). Sumber: ASA 2020.

### Akses EHR

```php
['profesi_id' => 1, 'form_id' => 86, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 2, 'form_id' => 86, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
```

### Validasi

- `asa` `required|in:1,2,3,4,5,6`; `tipe_anestesia` `required`.
- `withValidator` (aturan keras keselamatan):
  - `jenis_kelamin = 'P'` & usia < 60 ⇒ `hamil_bulan` **wajib** (dicek `null`/`0`).
  - `persetujuan_anestesi ≠ 'Sudah'` ⇒ **`store()` DITOLAK** (safety gate, bukan sekadar pesan).
  - `td` wajib terisi; `td` > 180/110 ⇒ badge "Hipertensi berat — konsultasi anestesi".
  - `trombosit < 100.000` ⇒ badge "Risiko neuraksial"; `kadar_gula_darah > 200` ⇒ badge "DM tidak terkontrol".
- `filteredData()` membuang field yang tidak relevan (mis. seluruh field kehamilan bila Lakes)(bila Lakes).

### Cetak

`.../EvaluasiPreAnestesiSedasi/print.blade.php` — formulir pre-anestesi lengkap + halaman persetujuan (tanda tangan dokter anestesi & pasien/wali).

---

## 5. Form 87 — Asesmen Post-Anestesi & Sedasi

### Rasional
Asesmen setelah keluar dari ruang operasi / selesai sedasi, sebelum pasien pulang ke ruang perawatan atau ruang pulih. Memuat kriteria pemulihan (**Aldrete**, **Bromage**, **Steward**, **PADSS**), tanda vital, komplikasi, dan ExtremaduraLV.

### Struktur Field

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `tanggal_asesmen` | 794 | Tanggal Asesmen Post-Anestesi | date | ya | |
| `jam_asesmen` | 795 | Jam Asesmen Post-Anestesi | time | ya | |
| `dokter_anestesi_id` | 796 | Dokter Anestesi Penilai | `x-select_dokter` | ya | |
| `skor_alderete_kesadaran` | 797 | Skor Aldrete | select 0/1/2 | ya | |
| `skor_alderete_aktivitas` | 797 | Skor Aldrete | select 0/1/2 | ya | |
| `skor_alderete_ventilasi` | 797 | Skor Aldrete | select 0/1/2 | ya | |
| `skor_alderete_warna_kulit` | 797 | Skor Aldrete | select 0/1/2 | ya | |
| `skor_alderete_total` | 797 | Skor Aldrete | readonly | ya | **TURUNAN** |
| `skor_bromage_tangan` | 798 | Skor Bromage | select 0–3 | ya | |
| `skor_bromage_lengan` | 798 | Skor Bromage | select 0–3 | ya | |
| `skor_bromage_kaki` | 798 | Skor Bromage | select 0–3 | ya | |
| `skor_bromage_total` | 798 | Skor Bromage | readonly | ya | **TURUNAN** |
| `skor_steward_pergerakan` | 799 | Skor Steward | select 0–3 | ya | |
| `skor_steward_pernapasan` | 799 | Skor Steward | select 0–3 | ya | |
| `skor_steward_kesadaran` | 799 | Skor Steward | select 0–3 | ya | |
| `skor_steward_total` | 799 | Skor Steward | readonly | ya | **TURUNAN** |
| `skor_padss_vas` | 800 | Skor PADSS | select 0–3 | ya | |
| `skor_padss_aktivitas` | 800 | Skor PADSS | select 0–3 | ya | |
| `skor_padss_total` | 800 | Skor PADSS | readonly | ya | **TURUNAN** |
| `kriteria_pemulihan` | 801 | Kriteria Pemulihan | radio | ya | Terpenuhi / Belum |
| `kesadaran` | 51 | Kesadaran (reuse) | select | ya | |
| `sistolik` | 6 | TD Sistolik (reuse) | number | ya | |
| `diastolik` | 7 | TD Diastolik (reuse) | number | ya | |
| `nadi` | 10 | Nadi (reuse) | number | ya | |
| `pernapasan` | 12 | Pernapasan (reuse) | number | ya | |
| `suhu` | 11 | Suhu (reuse) | number | ya | |
| `saturasi` | 15 | Saturasi (reuse) | number | ya | |
| `komplikasi_1..6` | 802 | Komplikasi Post-Anestesi | checkbox grup | tidak | mual · muntah · menggigil · hipotensi · hipertensi · vasoplegia |
| `nyeri` | 14 | Nyeri (reuse) | radio | ya | |
| `skala_nyeri` | 802 | Komplikasi Post-Anestesi | number | ya bila nyeri | VAS 0–10 |
| `intake_oral_ada` | 803 | Intake Oral | radio | ya | |
| `intake_oral_catatan` | 803 | Intake Oral | text | ya bila Ya | |
| `mobilisasi` | 804 | Mobilisasi | radio | ya | |
| `mobilisasi_catatan` | 804 | Mobilisasi | text | ya bila tidak | |
| `keadaan_umum` | 805 | Keadaan Umum Pasien | select | ya | Baik / Cukup / Burden |
| `keselamatan_1..3` | 806 | Keselamatan Pasien | checkbox grup | ya | gelang identitas · lokasi operasi · site operasi |
| `ruangan_tujuan` | 807 | Perawatan Ruang Bedah | select | ya | |
| `formulir_tertunda` | 808 | Formulir Pasien Pulih | textarea | tidak | |
| `rekomendasi` | 809 | Rekomendasi Pasien Pulih | textarea | ya | |

### Tabel Skor Pasca-Anestesi

**Skor Aldrete (total 0–10; ≤ 5 → ICU, > 8 boleh pindah ruangan)**

| Parameter | 2 | 1 | 0 |
|---|---|---|---|
| Kesadaran | Sadar penuh | Dapat dibangunkan dengan rangsangan ringan | Tidak merespons |
| Aktivitas | Bergerak 4 ekstremitas | Bergerak 2 ekstremitas | Tidak bergerak |
| Ventilasi (deep) | 8–10 respirasi/menit, TKAS baik | 6–8 respirasi/menit | Butuh dukungan ventilasi |
| Warna kulit | Kemerahan (pink) | Pucat | Biru/abu-abu |

**Skor Bromage (total 0–10; 0 = mobile penuh)**

| Ekstremitas | 0 | 1 | 2 | 3 |
|---|---|---|---|---|
| Tangan | Fleksi penuh | Fleksi > 90°, sedikitmovemen | Fleksi < 90° | Tidak dapat bergerak |
| Lengan | Fleksi penuh | Fleksi > 90° | Fleksi < 90° | Tidak dapat bergerak |
| Kaki | Fleksi penuh | Fleksi > 90° | Fleksi < 90° | Tidak dapat bergerak |

**Skor Steward (total 0–12; ≥ 9 boleh ke ruang)**

| Parameter | 3 | 2 | 1 | 0 |
|---|---|---|---|---|
| Pergerakan | Bergerak 4 ekstremitas | Bergerak 2–3 ekstremitas | Tidak bergerak | — |
| Pernapasan | Napas baik | Dispnea ringan | Apnea | — |
| Kesadaran | Responsif | Tidur, dapat dibangunkan | — | — |

**Skor PADSS (Post-Anesthesia Discharge Scoring System, total 0–20; ≥ 9 boleh pulang)**

| Parameter | 2 | 1 | 0 |
|---|---|---|---|
| Tanda vital | TD ± 20% rata-rata pre-op, N 60–100, RR 8–14, T ± 1 °C, SpO2 > 92% | TD ± 20–49%, N 51–59 atau 101–110, RR 5–7 atau 15–20, T ± 1–2 °C, SpO2 90–92% | Lainnya |
| Aktivitas | Bergerak normal | Bergerak dengan bantuan | Tidak dapat |
| Nyeri | Tidak ada | Nyeri ringan | Nyeri sedang–berat |
| Mual/muntah | Tidak ada | Nausea ringan | Mual/muntah |
| Pendarahan | Tidak ada | Sedikit | Signifikan |

### Dashboard & Form Master

```php
['form_id' => 87, 'nama_form' => 'Asesmen Post-Anestesi & Sedasi', 'slug' => 'asesmen_post_anestesi_sedasi', 'id_dash_menu' => '15.295', 'ri' => 1, 'rj' => 0, 'igd' => 0, 'mcu' => 0],
```

### Mapping

```php
87 => [
    'tanggal_asesmen' => 794, 'jam_asesmen' => 795, 'dokter_anestesi_id' => 796,
    'skor_alderete_kesadaran' => 797, 'skor_alderete_aktivitas' => 797,
    'skor_alderete_ventilasi' => 797, 'skor_alderete_warna_kulit' => 797, 'skor_alderete_total' => 797,
    'skor_bromage_tangan' => 798, 'skor_bromage_lengan' => 798, 'skor_bromage_kaki' => 798, 'skor_bromage_total' => 798,
    'skor_steward_pergerakan' => 799, 'skor_steward_pernapasan' => 799, 'skor_steward_kesadaran' => 799, 'skor_steward_total' => 799,
    'skor_padss_vas' => 800, 'skor_padss_aktivitas' => 800, 'skor_padss_total' => 800,
    'kriteria_pemulihan' => 801,
    'kesadaran' => 51, 'sistolik' => 6, 'diastolik' => 7, 'nadi' => 10, 'pernapasan' => 12, 'suhu' => 11, 'saturasi' => 15,
    'nyeri' => 14,
    'komplikasi_1' => 802, 'komplikasi_2' => 802, 'komplikasi_3' => 802,
    'komplikasi_4' => 802, 'komplikasi_5' => 802, 'komplikasi_6' => 802, 'skala_nyeri' => 802,
    'intake_oral_ada' => 803, 'intake_oral_catatan' => 803,
    'mobilisasi' => 804, 'mobilisasi_catatan' => 804,
    'keadaan_umum' => 805,
    'keselamatan_id' => 806, 'keselamatan_lokasi' => 806, 'keselamatan_site' => 806,
    'ruangan_tujuan' => 807,
    'formulir_tertunda' => 808,
    'rekomendasi' => 809,
],
```

### Akses EHR

```php
['profesi_id' => 1, 'form_id' => 87, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 2, 'form_id' => 87, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
```

### Validasi & komputasi server

`McuHelper` tidak dipakai; buat `SkorPemulihanHelper::hitung($data)`:

```php
'alderete' => $k + $a + $v + $w,                          // 0..10
'bromage'  => $tangan + $lengan + $kaki,                  // 0..9 (legacy 3 item)
'steward'  => $pergerakan + $pernapasan + $kesadaran,     // 0..9
'padss'    => $vas + $aktivitas + $nyeri + $mual + $pendarahan, // 0..10 (legacy 5 item; PADSS penuh 20 bila item lengkap)
```

> **CATATAN:** legacy hanyaasksubset item. Implementasi **menambah** item PADSS lengkap (9 item) bila tim klinikhukum menganggap perlu; default mengikuti legacy (5 item).

- Semua 4 total dihitung ulang server; nilai browser dibuang.
- `kriteria_pemulihan` **dihitung server** dari ambang Aldrete (> 8) — user tidak bebas memilih:
  - `skor_alderete_total <= 5` ⇒ badge merah "Rujuk ICU".
  - `> 5 && <= 8` ⇒ badge kuning "Perlu pemantauan".
  - `> 8` ⇒ badge hijau "Layak ke ruang".

### Cetak

`.../AsesmenPostAnestesiSedasi/print.blade.php` — formulir + tanda tangan dokter anestesi & penerima pasien (ruang tujuan).

---

## 6. Form 88 — Monitoring Anestesi & Sedasi

### Rasional
Monitoring intra-operasi/intra-sedasi. Legacy hanya berupa upload gambar (`monitoring_anestesi.php`); `intra_operatif_anestesi.php` (4.223 baris) memuat tabel monitoring berkala (TD, N, RR, SpO2, suhu, kesadaran) per jam observasi serta catatan bahan/kasa. Desain ini membangun **native structured** + kolom lampiran gambar.

### Struktur Field

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `tanggal_monitoring` | 810 | Tanggal Monitoring Anestesi | date | ya | |
| `jam_mulai` | 811 | Jam Monitoring Anestesi | time | ya | |
| `jam_selesai` | 811 | Jam Monitoring Anestesi | time | ya | |
| `hemodinamik_1..20` | 812 | Hemodinamik Monitoring | text | ya | baris berulang: "TD/N/RR/SpO2" |
| `suhu_1..20` | 813 | Suhu Tubuh Monitoring | number | ya | °C |
| `oksigenasi_1..20` | 814 | Ventilasi & Oksigenasi | text | ya | SpO2, FiO2, EtCO2 |
| `kesadaran_1..20` | 815 | Kesadaran Monitoring | select | ya | AVPU |
| `nyeri_1..20` | 816 | Nyeri & Ketidaknyamanan | number | ya | VAS |
| `kejadian_kritis` | 817 | Catatan Peristiwa Kritis | textarea | ya bila ada | |
| `lampiran_monitoring` | 818 | Lampiran Monitoring Anestesi | file (base64/img) | tidak | bukti scan monitoring |
| `status_monitoring` | 819 | Status Monitoring Anestesi | text | ya | Completed / Ongoing |

> Baris monitoring memakai **suffix angka 1..20** (PANDUAN §2.2) — `variabel` wajib unik.

### Dashboard & Form Master

```php
['form_id' => 88, 'nama_form' => 'Monitoring Anestesi & Sedasi', 'slug' => 'monitoring_anestesi_sedasi', 'id_dash_menu' => '15.296', 'ri' => 1, 'rj' => 0, 'igd' => 1, 'mcu' => 0],
```

### Mapping

```php
88 => [
    'tanggal_monitoring' => 810, 'jam_mulai' => 811, 'jam_selesai' => 811,
    'kejadian_kritis' => 817, 'lampiran_monitoring' => 818, 'status_monitoring' => 819,
],
for ($i = 1; $i <= 20; $i++) {
    $mapping[88]['hemodinamik_'.$i] = 812;
    $mapping[88]['suhu_'.$i] = 813;
    $mapping[88]['oksigenasi_'.$i] = 814;
    $mapping[88]['kesadaran_'.$i] = 815;
    $mapping[88]['nyeri_'.$i] = 816;
}
```

### Akses EHR

```php
['profesi_id' => 1, 'form_id' => 88, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 2, 'form_id' => 88, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
```

### Validasi

- `jam_selesai >= jam_mulai` (divalidasi di `withValidator`).
- `hemodinamik_N` wajib terisi minimal pada menit 0, 5, 15, 30 (baris pertama).
- `status_monitoring` `required|in:Ongoing,Completed`.
- `kejadian_kritis` wajib bila `status_monitoring = 'Ongoing'` ditutup sebagai events.

### Cetak

`.../MonitoringAnestesiSedasi/print.blade.php` — grafik monitoring berkala + tabel.

---

## 7. Form 89 — Check List Keamanan Pasien Operasi (WHO)

### Rasional
**WHO Surgical Safety Checklist** — tiga fase wajib: *Sign In* (sebelum induksi anestesi), *Time Out* (sebelum insisi), *Sign Out* (sebelun pasien meninggalkan ruang operasi). Ini checklist keselamatan pasien tertinggi prioritas; seluruh butir wajib terisi sebelum operasi.

Struktur Butir

| Fase | Butir | Sumber legacy |
|---|---|---|
| **Sign In** | Pasien telah dikonfirmasikan (gelang identitas, lokasi, prosedur, informed consent operasi, informed consent anestesi) | `sebelum_induksi_anestesi.php` |
| **Sign In** | Lokasi operasi sudah diberi tanda | idem |
| **Sign In** | Mesin & obat anestesi sudah dicek lengkap | idem |
| **Sign In** | Pulse oximeter terpasang & berfungsi | idem |
| **Sign In** | Riwayat alergi pasien | idem |
| **Sign In** | Kesulitan bernapas / risiko Aspirasi &nbsp;alat bantu napas yang diperlukan (mis. CPAP) | idem |
| **Sign In** | Akses intravena / akses sentral & rencana terapi cairan | idem |
| **Time Out** | Konfirmasi seluruh anggota tim & perkenalan nama/peran | `sebelum_insisi.php` |
| **Time Out** | Dokter bedah, anestesi & perawat konfirmasi verbal | idem |
| **Time Out** | Antibiotik profilaksis sudah diberikan 60 menit sebelumnya (nama & dosis) | idem |
| **Time Out** | Antisipasi kejadiankrisis (review dokter bedah / anestesi / perawat) | idem |
| **Time Out** | Foto rontgen / CT-Scan / MRI sudah ditayangkan | idem |
| **Sign Out** | Perawat konfirmasi verbal dengan tim | `sebelum_pasien_meninggalkan_ruang.php` |
| **Sign Out** | Review masalah utama & hal yang perlu diperhatikan | idem |
| **Sign Out** | Kelengkapan alat & pelabelan specimen | idem |
| **Sign Out** | Nama tindakan & waktu | idem |

### Struktur Field

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `tanggal_checklist` | 820 | Tanggal Checklist Keamanan | date | ya | |
| `jam_sign_in` | 821 | Jam Sign In | time | ya | sebelum induksi |
| `jam_time_out` | 822 | Jam Time Out | time | ya | sebelum insisi |
| `jam_sign_out` | 823 | Jam Sign Out | time | ya | sebelum keluar ruang |
| `signin_konfirmasi_gelang` | 824 | Konfirmasi Identitas Pasien | radio | ya | Sudah / Belum |
| `signin_lokasi_operasi` | 824 | Konfirmasi Identitas Pasien | radio | ya | |
| `signin_prosedur` | 824 | Konfirmasi Identitas Pasien | radio | ya | |
| `signin_informed_consent_operasi` | 824 | Konfirmasi Identitas Pasien | radio | ya | informed consent operasi |
| `signin_informed_consent_anestesi` | 824 | Konfirmasi Identitas Pasien | radio | ya | informed consent anestesi |
| `signin_area_operasi_ditandai` | 825 | Penandaan Lokasi Operasi | radio | ya | |
| `signin_mesin_obat_dicek` | 826 | Mesin & Obat Anestesi | radio | ya | |
| `signin_pulse_oximeter` | 827 | Pulse Oximeter & Monitoring | radio | ya | |
| `signin_riwayat_alergi` | 828 | Riwayat Alergi Waktu Operasi | radio | ya | Tidak / Ya → lanjut |
| `signin_alergi_keterangan` | 828 | Riwayat Alergi Waktu Operasi | text | ya bila Ya | |
| `signin_kesulitan_napas` | 829 | Kesulitan Bernapas & Risiko Aspirasi | radio | ya | Tidak / Ya → lanjut |
| `signin_bantuan_napas` | 829 | Kesulitan Bernapas & Risiko Aspirasi | text | ya bila Ya | CPAP / O2 / suction |
| `signin_akses_vena` | 830 | Akses Vena & Terapi Cairan | radio | ya | Tidak / Ya → lanjut |
| `signin_rencana_cairan` | 830 | Akses Vena & Terapi Cairan | text | ya bila Ya | termasuk "500 ml (7 ml/kg BB pada anak)" |
| `timeout_konfirmasi_tim` | 831 | Konfirmasi Tim Operasi | radio | ya | Sudah / Belum |
| `timeout_perkenalan_peran` | 831 | Konfirmasi Tim Operasi | radio | ya | |
| `timeout_konfirmasi_verbal` | 831 | Konfirmasi Tim Operasi | radio | ya | |
| `timeout_profilaksis_ada` | 832 | Antibiotik Profilaksis | radio | ya | Tidak / Ya → lanjut |
| `timeout_profilaksis_nama` | 832 | Antibiotik Profilaksis | text | ya bila Ya | |
| `timeout_profilaksis_dosis` | 832 | Antibiotik Profilaksis | text | ya bila Ya | |
| `timeout_profilaksis_waktu` | 832 | Antibiotik Profilaksis | time | ya bila Ya | 60 menit sebelum insisi |
| `timeout_ypi_kritis` | 833 | Antisipasi Kejadian Krisis | text | ya | langkah bila kondisi kritis, durasi, dan lama operasi. |
| `timeout_review_anestesi` | 833 | Antisipasi Kejadian Krisis | textarea | ya | hal khusus pasien menurut tim anestesi |
| `timeout_review_perawat` | 833 | Antisipasi Kejadian Krisis | textarea | ya | sterilitas alat & alat yang perlu perhatian khusus |
| `timeout_foto_penunjang` | 980 | Penunjang & Persiapan | radio | ya | foto sudah ditayangkan |
| `signout_konfirmasi_verbal` | 981 | Konfirmasi Tim & Review | radio | ya | |
| `signout_review_masalah` | 981 | Konfirmasi Tim & Review | textarea | ya | masalah utama postoperative |
| `signout_nama_tindakan` | 982 | Data Tindakan & Alat | text | ya | |
| `signout_kelengkapan_alat` | 982 | Data Tindakan & Alat | radio | ya | Lengkap / Tidak lengkap |
| `signout_kelengkapan_alat_alasan` | 982 | Data Tindakan & Alat | text | ya bila tidak lengkap | |
| `signout_pelabelan_specimen` | 982 | Data Tindakan & Alat | radio | ya | |

### Dashboard & Form Master

```php
['form_id' => 89, 'nama_form' => 'Check List Keamanan Pasien Operasi', 'slug' => 'checklist_keamanan_operasi', 'id_dash_menu' => '15.297', 'ri' => 1, 'rj' => 0, 'igd' => 1, 'mcu' => 0],
```

### Mapping

```php
89 => [
    'tanggal_checklist' => 820, 'jam_sign_in' => 821, 'jam_time_out' => 822, 'jam_sign_out' => 823,
    'signin_konfirmasi_gelang' => 824, 'signin_lokasi_operasi' => 824, 'signin_prosedur' => 824,
    'signin_informed_consent_operasi' => 824, 'signin_informed_consent_anestesi' => 824,
    'signin_area_operasi_ditandai' => 825, 'signin_mesin_obat_dicek' => 826, 'signin_pulse_oximeter' => 827,
    'signin_riwayat_alergi' => 828, 'signin_alergi_keterangan' => 828,
    'signin_kesulitan_napas' => 829, 'signin_bantuan_napas' => 829,
    'signin_akses_vena' => 830, 'signin_rencana_cairan' => 830,
    'timeout_konfirmasi_tim' => 831, 'timeout_perkenalan_peran' => 831, 'timeout_konfirmasi_verbal' => 831,
    'timeout_profilaksis_ada' => 832, 'timeout_profilaksis_nama' => 832,
    'timeout_profilaksis_dosis' => 832, 'timeout_profilaksis_waktu' => 832,
    'timeout_ypi_kritis' => 833, 'timeout_review_anestesi' => 833,
    'timeout_review_perawat' => 833, 'timeout_foto_penunjang' => 980,
    'signout_konfirmasi_verbal' => 981, 'signout_review_masalah' => 981,
    'signout_nama_tindakan' => 982, 'signout_kelengkapan_alat' => 982,
    'signout_kelengkapan_alat_alasan' => 982, 'signout_pelabelan_specimen' => 982,
],
```

### Akses EHR

```php
['profesi_id' => 1, 'form_id' => 89, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 2, 'form_id' => 89, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
```

### Validasi — **Safety Gate (ketat)**

- **Seluruh 32 butir wajib** `required` — tidak boleh ada butir yang terlewat.
- Radio hanya boleh `Sudah` atau `Belum`. **Jika ada satu pun butir `Belum` di Sign In atau Time Out, `store()` DITOLAK** dengan pesan: *"Checklist keamanan operasi belum lengkap — operasi tidak boleh dilanjutkan."* Ini adalah **hard gate**, bukan validasi biasa.
- `jam_sign_in < jam_time_out < jam_sign_out` (divalidasi `withValidator`).
- `signin_riwayat_alergi = 'Ya'` ⇒ `signin_alergi_keterangan` wajib.
- `timeout_profilaksis_ada = 'Tidak'` ⇒ wajib menyertakan alasan (safety).
- `timeout_foto_penunjang = 'Ya'` ⇒ instruksi penunjang harus tampil.

### Cetak

`.../ChecklistKeamananOperasi/print.blade.php` — **WHO checklist 1 halaman** (3 kolom: Sign In / Time Out / Sign Out), wajib dipegang di ruang operasi.

---

## 8. Form 90 — Check List Kesiapan Anestesi

### Rasional
Legacy `check_list_kesiapan_anestesi.php` hanya upload gambar scan. Desain ini membangun **form native** — daftar periksa kesiapan induksi anestesi sebelum masuk operasi.

### Struktur Field

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `tanggal_checklist` | 834 | Tanggal Checklist Kesiapan Anestesi | date | ya | |
| `jam_checklist` | 835 | Jam Checklist Kesiapan Anestesi | time | ya | |
| `mesin_anestesi_ok` | 836 | Mesin & Alat Anestesi | radio | ya | Ok / Tidak Ok |
| `mesin_anestesi_keterangan` | 836 | Mesin & Alat Anestesi | text | ya bila tidak | |
| `oksigen_ventilasi_ok` | 837 | Oksigen & Ventilasi | radio | ya | |
| `monitor_akses_ok` | 838 | Monitor & Akses Vaskular | radio | ya | |
| `farmasi_persiapan_ok` | 839 | Farmasi & Persiapan | radio | ya | |
| `consent_dokumentasi_ok` | 840 | Persetujuan & Dokumentasi | radio | ya | |
| `catatan_kesiapan` | 841 | Catatan Kesiapan Anestesi | textarea | ya | |

### Dashboard & Form Master

```php
['form_id' => 90, 'nama_form' => 'Check List Kesiapan Anestesi', 'slug' => 'checklist_kesiapan_anestesi', 'id_dash_menu' => '15.298', 'ri' => 1, 'rj' => 0, 'igd' => 1, 'mcu' => 0],
```

### Mapping

```php
90 => [
    'tanggal_checklist' => 834, 'jam_checklist' => 835,
    'mesin_anestesi_ok' => 836, 'mesin_anestesi_keterangan' => 836,
    'oksigen_ventilasi_ok' => 837, 'monitor_akses_ok' => 838,
    'farmasi_persiapan_ok' => 839, 'consent_dokumentasi_ok' => 840,
    'catatan_kesiapan' => 841,
],
```

### Akses EHR

```php
['profesi_id' => 1, 'form_id' => 90, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 2, 'form_id' => 90, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
```

### Validasi

- Semua butir `required|in:Ok,Tidak Ok`.
- `consent_dokumentasi_ok = 'Tidak Ok'` ⇒ **hard gate**, `store()` ditolak.
- `catatan_kesiapan` wajib bila ada satu `Tidak Ok`.

### Cetak

`.../ChecklistKesiapanAnestesi/print.blade.php`.

---

## 9. Form 91 — Pengkajian Pre/Intra/Post Operasi

### Rasional
Pengkajian keperawatan perioperatif (legacy `pengkajian_perioperatif.php` + `pengkajian_pre_intra_post_operasi.php`). Tiga fase dengan parameter yang berbeda.

### Struktur Field

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `tanggal_pengkajian` | 842 | Tanggal Pengkajian Operasi | date | ya | |
| `jam_pengkajian` | 843 | Jam Pengkajian Operasi | time | ya | |
| `fase` | 844 | Tahap Pengkajian Operasi | radio | ya | Pre / Intra / Post |
| `keluhan_utama` | 13 | Keluhan Utama (reuse) | textarea | ya (Pre) | |
| `riwayat_penyakit` | 845 | Aspek Pre Operasi | textarea | ya (Pre) | |
| `riwayat_operasi` | 845 | Aspek Pre Operasi | textarea | ya (Pre) | |
| `riwayat_alergi` | 17 | Alergi (reuse) | textarea | ya (Pre) | |
| `jenis_operasi` | 845 | Aspek Pre Operasi | select | ya (Pre) | |
| `status_emosi` | 845 | Aspek Pre Operasi | radio | ya (Pre) | |
| `skala_cemas` | 845 | Aspek Pre Operasi | number | ya (Pre) | Hamilton Anxiety 0–4 |
| `data_penunjang` | 845 | Aspek Pre Operasi | textarea | ya (Pre) | |
| `persiapan_darah` | 845 | Aspek Pre Operasi | radio | ya (Pre) | |
| `pemeriksaan_fisik_intra` | 846 | Aspek Intra Operasi | textarea | ya (Intra) | |
| `posisi_operasi_intra` | 846 | Aspek Intra Operasi | select | ya (Intra) | |
| `keadaan_umum_intra` | 846 | Aspek Intra Operasi | select | ya (Intra) | |
| `asa_intra` | 846 | Aspek Intra Operasi | select | ya (Intra) | I–VI |
| `ruangan_pulih` | 846 | Aspek Intra Operasi | select | ya (Intra) | |
| `nyeri_post` | 847 | Aspek Post Operasi | radio | ya (Post) | reuse objek 14 |
| `skor_nyeri_post` | 847 | Aspek Post Operasi | number | ya (Post) | VAS |
| `mobilisasi_post` | 847 | Aspek Post Operasi | select | ya (Post) | |
| `luka_operasi` | 847 | Aspek Post Operasi | textarea | ya (Post) | kondisi luka |
| `rencana_keperawatan` | 848 | Rencana Keperawatan Operasi | textarea | ya | |
| `kebutuhan_nutrisi` | 849 | Kebutuhan Nutrisi Operasi | textarea | ya | |
| `edukasi_pasien` | 850 | Kebutuhan Edukasi Operasi | textarea | ya | |
| `catatan_pengkajian` | 851 | Catatan Pengkajian Operasi | textarea | ya | |

### Dashboard & Form Master

```php
['form_id' => 91, 'nama_form' => 'Pengkajian Pre/Intra/Post Operasi', 'slug' => 'pengkajian_operasi', 'id_dash_menu' => '15.299', 'ri' => 1, 'rj' => 0, 'igd' => 1, 'mcu' => 0],
```

### Mapping

```php
91 => [
    'tanggal_pengkajian' => 842, 'jam_pengkajian' => 843, 'fase' => 844,
    'keluhan_utama' => 13,
    'riwayat_alergi' => 17,
    'riwayat_penyakit' => 845, 'riwayat_operasi' => 845, 'jenis_operasi' => 845,
    'status_emosi' => 845, 'skala_cemas' => 845, 'data_penunjang' => 845, 'persiapan_darah' => 845,
    'pemeriksaan_fisik_intra' => 846, 'posisi_operasi_intra' => 846, 'keadaan_umum_intra' => 846,
    'asa_intra' => 846, 'ruangan_pulih' => 846,
    'nyeri_post' => 847, 'skor_nyeri_post' => 847, 'mobilisasi_post' => 847, 'luka_operasi' => 847,
    'rencana_keperawatan' => 848, 'kebutuhan_nutrisi' => 849, 'edukasi_pasien' => 850,
    'catatan_pengkajian' => 851,
],
```

### Akses EHR

```php
['profesi_id' => 1, 'form_id' => 91, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 2, 'form_id' => 91, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
```

### Validasi

- `fase` `required|in:Pre,Intra,Post`; field di luar fase **dibuang di `filteredData()`**.
- `withValidator` per fase: hanya field fase terpilih yang wajib.
- `skala_cemas` `nullable|integer|between:0,4` (hanya fase Pre).

### Cetak

`.../PengkajianOperasi/print.blade.php`.

---

## 10. Form 92 — Laporan Operasi

### Rasional
Dokumen resmi hasil operasi yang diisi dokter bedah setelah operasi. Menggabungkan `laporan_operasi.php` + `isi_laporan_operasi.php` legacy.

### Struktur Field

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `tanggal_laporan` | 852 | Tanggal Laporan Operasi | date | ya | |
| `jam_mulai_operasi` | 853 | Jam Mulai Operasi | time | ya | |
| `jam_selesai_operasi` | 983 | Jam Selesai Operasi | time | ya | |
| `lama_operasi` | 984 | Lama Operasi | text | ya | TURUNAN |
| `nama_tindakan_operasi` | 854 | Nama Tindakan Operasi | text | ya | |
| `jenis_operasi` | 855 | Jenis Operasi | radio | ya | Efektif / Darurat / Semi Darurat |
| `dokter_bedah_operator` | 856 | Dokter Bedah Operator | `x-select_dokter` | ya | |
| `dokter_anestesi` | 857 | Dokter Anestesi | `x-select_dokter` | ya | |
| `diagnosa_pra_operasi` | 858 | Diagnosa Pra Operasi | textarea | ya | reuse objek 40 |
| `diagnosa_pasca_operasi` | 859 | Diagnosa Pasca Operasi | textarea | ya | reuse objek 40 |
| `temuan_operasi` | 860 | Temuan Operasi | textarea | ya | |
| `prosedur_operasi` | 861 | Prosedur yang Dilakukan | textarea | ya | |
| `jenis_anas_umum` | 854 | Nama Tindakan Operasi | radio | ya | |
| `jenis_anas_lokal` | 854 | Nama Tindakan Operasi | radio | ya | |
| `komplikasi_operasi_1..6` | 862 | Komplikasi Operasi | checkbox grup | tidak | perdarahan, infeksi, atonia uteri, kerusakan organ, luka localize, reopen |
| `transfusi_darah` | 862 | Komplikasi Operasi | radio | ya | Tidak / Ya → lanjut |
| `jumlah_transfusi` | 862 | Komplikasi Operasi | number | ya bila Ya | |
| `implan_pasang` | 862 | Komplikasi Operasi | radio | ya | Tidak / Ya → lanjut |
| `jenis_implan` | 862 | Komplikasi Operasi | text | ya bila Ya | ORIF / OREF / Remove Implant / Lain |
| `instrumen_kasa` | 863 | Instrumen & Kasa Terpakai | textarea | ya | |
| `specimen_diterima` | 864 | Specimen | radio | ya | Ya/Tidak → nama |
| `nama_specimen` | 864 | Specimen | text | ya bila Ya | tipe spesimen |
| `kehilangan_darah` | 865 | Kehilangan Darah & Cairan | number | ya | cc |
| `cairan_masuk` | 865 | Kehilangan Darah & Cairan | number | ya | cc |
| `ruangan_pasca_operasi` | 865 | Kehilangan Darah & Cairan | select | ya | |
| `instruksi_pasca_operasi` | 866 | Instruksi Pasca Operasi | textarea | ya | |

### Dashboard & Form Master

```php
['form_id' => 92, 'nama_form' => 'Laporan Operasi', 'slug' => 'laporan_operasi', 'id_dash_menu' => '15.300', 'ri' => 1, 'rj' => 0, 'igd' => 0, 'mcu' => 0],
```

### Mapping

```php
92 => [
    'tanggal_laporan' => 852, 'jam_mulai_operasi' => 853, 'jam_selesai_operasi' => 983, 'lama_operasi' => 984,
    'nama_tindakan_operasi' => 854, 'jenis_anas_umum' => 854, 'jenis_anas_lokal' => 854,
    'jenis_operasi' => 855,
    'dokter_bedah_operator' => 856, 'dokter_anestesi' => 857,
    'diagnosa_pra_operasi' => 858, 'diagnosa_pasca_operasi' => 859,
    'temuan_operasi' => 860, 'prosedur_operasi' => 861,
    'transfusi_darah' => 862, 'jumlah_transfusi' => 862, 'implan_pasang' => 862, 'jenis_implan' => 862,
    'instrumen_kasa' => 863,
    'specimen_diterima' => 864, 'nama_specimen' => 864,
    'kehilangan_darah' => 865, 'cairan_masuk' => 865, 'ruangan_pasca_operasi' => 865,
    'instruksi_pasca_operasi' => 866,
],
for ($i = 1; $i <= 6; $i++) { $mapping[92]['komplikasi_operasi_'.$i] = 862; }
```

### Akses EHR

```php
['profesi_id' => 1, 'form_id' => 92, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 2, 'form_id' => 92, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
```

### Validasi

- `jam_selesai_operasi >= jam_mulai_operasi`; `lama_operasi` **dihitung server** (selisih).
- `diagnosa_pra_operasi` & `diagnosa_pasca_operasi` `required`.
- `implan_pasang = 'Ya'` ⇒ `jenis_implan` wajib (ORIF/OREF/Remove/Lain).
- `specimen_diterima = 'Ya'` ⇒ `nama_specimen` wajib.
- `transfusi_darah = 'Ya'` ⇒ `jumlah_transfusi > 0`.
- `kehilangan_darah > 500` cc ⇒ badge "Pendarahan signifikan — lengkapi laporan."

### Cetak

`.../LaporanOperasi/print.blade.php` — laporan operasi 1 halaman + lembar instruksi pasca operasi (terpisah, ditandatangani dokter).

---

## 11. Form 93 — Catatan Bedah

### Rasional
Catatan bedah yang didokumentasikan langsung di ruang operasi: pemeriksaan fisik, posisi, alat, pembedahan, pembiusan, drainase, torniquet, diathermi, sterilisasi, hingga instruksi pasca operasi.

### Struktur Field

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `tanggal_catatan` | 867 | Tanggal Catatan Bedah | date | ya | |
| `jam_catatan` | 868 | Jam Catatan Bedah | time | ya | |
| `dokter_bedah` | 869 | Dokter Bedah Penulis | `x-select_dokter` | ya | |
| `petugas_pendamping` | 870 | Petugas Pendamping | `x-select_pegawai` | ya | |
| `prabedah` | 871 | Aspek Pre Operasi Bedah | textarea | ya | |
| `pre_operasi` | 871 | Aspek Pre Operasi Bedah | textarea | ya | |
| `pemeriksaan_fisik` | 872 | Pemeriksaan Fisik Intra Operasi | textarea | ya | |
| `kulit` | 872 | Pemeriksaan Fisik Intra Operasi | text | ya | kondisi kulit |
| `warna_kulit` | 872 | Pemeriksaan Fisik Intra Operasi | text | ya | |
| `keadaan_umum` | 872 | Pemeriksaan Fisik Intra Operasi | select | ya | |
| `posisi_operasi` | 873 | Posisi Operasi | select | ya | supine/lateral/prone |
| `lokasi_operasi` | 873 | Posisi Operasi | text | ya | lokasi sayatan |
| `peralatan` | 874 | Alat & Perlengkapan Operasi | textarea | ya | |
| `pasang_alat` | 874 | Alat & Perlengkapan Operasi | text | ya | |
| `kebutuhan_kamar` | 875 | Kebutuhan Kamar & Klarifikasi | textarea | ya | |
| `klarifikasi` | 875 | Kebutuhan Kamar & Klarifikasi | text | ya | |
| `pembedahan` | 876 | Pembedahan, Pembiusan & Drainase | textarea | ya | |
| `pembiusan` | 876 | Pembedahan, Pembiusan & Drainase | textarea | ya | |
| `pemakaian_drain` | 876 | Pembedahan, Pembiusan & Drainase | text | ya | |
| `torniquet` | 877 | Torniquet & Diathermi | text | ya | durasi/diathermi |
| `diathermi` | 877 | Torniquet & Diathermi | text | ya | |
| `jumlah_cairan` | 878 | Cairan & Darah | number | ya | mL |
| `perawatan_darah` | 878 | Cairan & Darah | text | ya | tipe & jumlah |
| `sirkuler_a` | 879 | Sirkuler & Asisten | text | ya | nama |
| `asisten` | 879 | Sirkuler & Asisten | text | ya | nama |
| `instruksi` | 880 | Instruksi Pasca Operasi | textarea | ya | |
| `diagnosa_medis` | 881 | Diagnosa Medis (reuse) | textarea | ya | reuse objek 40 |
| `jenis_operasi` | 882 | Jenis Operasi | radio | ya | Efektif / Darurat |

### Dashboard & Form Master

```php
['form_id' => 93, 'nama_form' => 'Catatan Bedah', 'slug' => 'catatan_bedah', 'id_dash_menu' => '15.301', 'ri' => 1, 'rj' => 0, 'igd' => 0, 'mcu' => 0],
```

### Mapping

```php
93 => [
    'tanggal_catatan' => 867, 'jam_catatan' => 868, 'dokter_bedah' => 869, 'petugas_pendamping' => 870,
    'prabedah' => 871, 'pre_operasi' => 871,
    'pemeriksaan_fisik' => 872, 'kulit' => 872, 'warna_kulit' => 872, 'keadaan_umum' => 872,
    'posisi_operasi' => 873, 'lokasi_operasi' => 873,
    'peralatan' => 874, 'pasang_alat' => 874,
    'kebutuhan_kamar' => 875, 'klarifikasi' => 875,
    'pembedahan' => 876, 'pembiusan' => 876, 'pemakaian_drain' => 876,
    'torniquet' => 877, 'diathermi' => 877,
    'jumlah_cairan' => 878, 'perawatan_darah' => 878,
    'sirkuler_a' => 879, 'asisten' => 879,
    'instruksi' => 880, 'diagnosa_medis' => 881,
    'jenis_operasi' => 882,
],
```

### Akses EHR

```php
['profesi_id' => 1, 'form_id' => 93, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 2, 'form_id' => 93, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
```

### Validasi

- `dokter_bedah` & `petugas_pendamping` wajib.
- `jumlah_cairan` `required|numeric|min:0`.
- `instruksi` wajib (dokter wajib memberi instruksi pasca operasi).

### Cetak

`.../CatatanBedah/print.blade.php`.

---

## 12. Form 94 — Transfer Pasien Antar Ruangan

### Rasional
Serah terima pasien antar ruang (juga IGD → ruang, ruang → ruang, pre-operasi → ruang operasi). Legacy `transfer_pasien_antar_ruangan.php` sangat lengkap: identitas, diagnosis, masalah keperawatan, terapi, alat, kewaspadaan, penunjang.

### Struktur Field

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `tanggal_transfer` | 883 | Tanggal Transfer | date | ya | |
| `jam_transfer` | 884 | Jam Transfer | time | ya | |
| `ruangan_asal` | 885 | Ruangan Asal | select | ya | |
| `ruangan_tujuan` | 886 | Ruangan Tujuan | select | ya | |
| `kategori_transfer` | 887 | Kategori Transfer | select | ya | Rawat Inap / Rawat Jalan / IGD |
| `identitas_pasien` | 888 | Identitas Pasien | textarea | ya | |
| `dpjp_utama` | 888 | Identitas Pasien | text | ya | |
| `dokter_pengantar` | 889 | Dokter Pengantar | `x-select_dokter` | ya | |
| `dokter_rawat_1..5` | 889 | Dokter Pengantar | `x-select_dokter` | ya | maksimal 5 dokter penanggung rawat |
| `perawat_ruangan` | 889 | Dokter Pengantar | text | ya | |
| `diagnosa_medis` | 890 | Diagnosis Medis | textarea | ya | reuse objek 40 |
| `diagnosa_keperawatan` | 891 | Diagnosis Keperawatan | textarea | ya | |
| `masalah_keperawatan` | 892 | Masalah Keperawatan | textarea | ya | |
| `kebutuhan_khusus` | 893 | Kebutuhan Khusus | textarea | ya | |
| `kewaspadaan` | 893 | Kebutuhan Khusus | textarea | ya | |
| `nama_terapi_obat` | 894 | Prosedur & Peralatan | textarea | ya | |
| `terapi_infus` | 894 | Prosedur & Peralatan | text | ya | |
| `oksigen` | 894 | Prosedur & Peralatan | text | ya | |
| `gangguan_indra` | 894 | Prosedur & Peralatan | text | ya | |
| `prosedur_transfer` | 894 | Prosedur & Peralatan | text | ya | |
| `peralatan_transfer` | 894 | Prosedur & Peralatan | text | ya | |
| `perlu_alat_bantu` | 894 | Prosedur & Peralatan | text | ya | |
| `riwayat_alergi` | 17 | Alergi (reuse) | textarea | ya | |
| `sistolik` | 6 | TD Sistolik (reuse) | number | ya | |
| `diastolik` | 7 | TD Diastolik (reuse) | number | ya | |
| `nadi` | 10 | Nadi (reuse) | number | ya | |
| `pernapasan` | 12 | Pernapasan (reuse) | number | ya | |
| `suhu` | 11 | Suhu (reuse) | number | ya | |
| `kesadaran` | 51 | Kesadaran (reuse) | select | ya | |
| `gcs_eye` | 54 | GCS Eye (reuse) | number | ya | |
| `gcs_motorik` | 55 | GCS Motorik (reuse) | number | ya | |
| `gcs_verbal` | 56 | GCS Verbal (reuse) | number | ya | |
| `gcs_score` | 57 | GCS Score (reuse) | number | ya | |
| `dokumen_pendukung` | 895 | Dokumen Pendukung | checkbox grup | ya | |
| `hasil_lab` | 895 | Dokumen Pendukung | text | ya | |
| `hasil_usg` | 895 | Dokumen Pendukung | text | ya | |
| `hasil_radiologi` | 895 | Dokumen Pendukung | text | ya | |
| `konsultasi` | 895 | Dokumen Pendukung | textarea | ya | |
| `catatan_transfer` | 895 | Dokumen Pendukung | textarea | ya | |
| `nama_penerima` | 896 | Penerima Pasien | `x-select_pegawai` | ya | |
| `tanggal_penerimaan` | 897 | Tanda Tangan Penerimaan | date | ya | |
| `jam_penerimaan` | 897 | Tanda Tangan Penerimaan | time | ya | |

### Dashboard & Form Master

```php
['form_id' => 94, 'nama_form' => 'Transfer Pasien Antar Ruangan', 'slug' => 'transfer_pasien', 'id_dash_menu' => '15.302', 'ri' => 1, 'rj' => 0, 'igd' => 1, 'mcu' => 0],
```

### Mapping

```php
94 => [
    'tanggal_transfer' => 883, 'jam_transfer' => 884,
    'ruangan_asal' => 885, 'ruangan_tujuan' => 886, 'kategori_transfer' => 887,
    'identitas_pasien' => 888, 'dpjp_utama' => 888,
    'dokter_pengantar' => 889, 'perawat_ruangan' => 889,
    'diagnosa_medis' => 890, 'diagnosa_keperawatan' => 891, 'masalah_keperawatan' => 892,
    'kebutuhan_khusus' => 893, 'kewaspadaan' => 893,
    'nama_terapi_obat' => 894, 'terapi_infus' => 894, 'oksigen' => 894,
    'gangguan_indra' => 894, 'prosedur_transfer' => 894, 'peralatan' => 894, 'perlu_alat_bantu' => 894,
    'riwayat_alergi' => 17,
    'sistolik' => 6, 'diastolik' => 7, 'nadi' => 10, 'pernapasan' => 12, 'suhu' => 11, 'kesadaran' => 51,
    'gcs_eye' => 54, 'gcs_motorik' => 55, 'gcs_verbal' => 56, 'gcs_score' => 57,
    'dokumen_pendukung' => 895, 'hasil_lab' => 895, 'hasil_usg' => 895, 'hasil_radiologi' => 895,
    'konsultasi' => 895, 'catatan_transfer' => 895,
    'nama_penerima' => 896, 'tanggal_penerimaan' => 897, 'jam_penerimaan' => 897,
],
for ($i = 1; $i <= 5; $i++) { $mapping[94]['dokter_rawat_'.$i] = 889; }
```

### Akses EHR

```php
['profesi_id' => 1, 'form_id' => 94, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 2, 'form_id' => 94, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
```

### Validasi

- `ruangan_tujuan != ruang_asal` (divalidasi).
- Semua tanda vital wajib; di luar rentang → badge (bukan blocking).
- `dokumen_pendukung` minimal satu tercentang.
- `nama_penerima` wajib (penerimaan tidak boleh tanpa penerima).

### Cetak

`.../TransferPasien/print.blade.php` — formulir serah terima 2 halaman, kolom paraf pengantar & penerima.

---

## 13. Form 95 — Penandaan Lokasi Operasi

### Rasional
Penandaan lokasi operasi (*site marking*) oleh dokter bedah sebelum masuk ruang operasi — praktik *site marking* WHO. Legacy hanya upload gambar; desain ini native.

### Struktur Field

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `tanggal_penandaan` | 898 | Tanggal Penandaan Lokasi | date | ya | |
| `jam_penandaan` | 899 | Jam Penandaan Lokasi | time | ya | |
| `nama_tindakan` | 900 | Lokasi Operasi | text | ya | |
| `lokasi_anatomi` | 900 | Lokasi Operasi | text | ya | |
| `sisi_operasi` | 900 | Lokasi Operasi | radio | ya | Kanan / Kiri / Bilateral / Tidak ada sisi |
| `jenis_penandaan` | 901 | Penanda yang Digunakan | radio | ya | |
| `petugas_penanda` | 902 | Petugas Penanda | `x-select_dokter` | ya | |
| `konfirmasi_pasien` | 902 | Petugas Penanda | radio | ya | pasieneniumsetujui |
| `lampiran_penandaan` | 901 | Penanda yang Digunakan | file | tidak | foto penandaan |

### Dashboard & Form Master

```php
['form_id' => 95, 'nama_form' => 'Penandaan Lokasi Operasi', 'slug' => 'penandaan_lokasi_operasi', 'id_dash_menu' => '15.303', 'ri' => 1, 'rj' => 0, 'igd' => 1, 'mcu' => 0],
```

### Mapping

```php
95 => [
    'tanggal_penandaan' => 898, 'jam_penandaan' => 899,
    'nama_tindakan' => 900, 'lokasi_anatomi' => 900, 'sisi_operasi' => 900,
    'jenis_penandaan' => 901, 'lampiran_penandaan' => 901,
    'petugas_penanda' => 902, 'konfirmasi_pasien' => 902,
],
```

### Akses EHR

```php
['profesi_id' => 1, 'form_id' => 95, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
```

### Validasi

- `sisi_operasi` `required|in:Kanan,Kiri,Bilateral,Não sides` → gunakan `Tanpa Sisi`.
- `konfirmasi_pasien = 'Tidak'` ⇒ wajib menyertakan alasan (safety gate).

### Cetak

`.../PenandaanLokasiOperasi/print.blade.php` + lampiran foto.

---

## 14. Form 96 — Penandaan Pasien

### Rasional
Penandaan gelang risiko pasien (alergi, geriatri, hepatitis, HIV, TB). Legacy `penandaan_pasien.php` — checkbox YA + isian alergi.

### Struktur Field

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `tanggal_penandaan` | 903 | Tanggal Penandaan | date | ya | |
| `jam_penandaan` | 904 | Jam Penandaan | time | ya | |
| `penanda_alergi` | 905 | Penanda Alergi | checkbox | tidak | |
| `alergi_1..5` | 906 | Keterangan Alergi Ditandai | text | ya bila penanda_alergi | |
| `penanda_hepatitis` | 907 | Penanda Hepatitis | checkbox | tidak | |
| `jenis_hepatitis` | 907 | Penanda Hepatitis | select | ya bila aktif | B / C |
| `penanda_hiv` | 908 | Penanda HIV | checkbox | tidak | |
| `penanda_tb` | 909 | Penanda TB | checkbox | tidak | |
| `penanda_geriatri` | 910 | Penanda Geriatri | checkbox | tidak | |
| `alasan_geriatri` | 910 | Penanda Geriatri | text | ya bila aktif | |

### Dashboard & Form Master

```php
['form_id' => 96, 'nama_form' => 'Penandaan Pasien', 'slug' => 'penandaan_pasien', 'id_dash_menu' => '15.304', 'ri' => 1, 'rj' => 0, 'igd' => 1, 'mcu' => 0],
```

### Mapping

```php
96 => [
    'tanggal_penandaan' => 903, 'jam_penandaan' => 904,
    'penanda_alergi' => 905, 'penanda_hepatitis' => 907, 'jenis_hepatitis' => 907,
    'penanda_hiv' => 908, 'penanda_tb' => 909,
    'penanda_geriatri' => 910, 'alasan_geriatri' => 910,
],
for ($i = 1; $i <= 5; $i++) { $mapping[96]['alergi_'.$i] = 906; }
```

### Akses EHR

```php
['profesi_id' => 1, 'form_id' => 96, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 2, 'form_id' => 96, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
```

### Validasi

- `penanda_alergi` tercentang ⇒ minimal satu `alergi_N` wajib.
- `penanda_hepatitis` tercentang ⇒ `jenis_hepatitis` wajib.
- `penanda_geriatri` tercentang ⇒ `alasan_geriatri` wajib.

### Cetak

`.../PenandaanPasien/print.blade.php` — catatan penandaan risiko + instruksi higiyenis.

---

## 15. Form 97 — Ceklis Pre & Post Tindakan Invasif

### Rasional
Ceklis persiapan pre- dan post-tindakan invasif non-bedah (mis.pasang kateter, biopsy, injeksi), untuk pasien yang akan menjalani tindakan tersebut. Legacy `ceklis_pre_dan_operatif_tindakan_infasif.php` (1.365 baris) memakai sistem **3 status** per butir: ✓ / ● / ✗ (ya / tidak / tidak mendapatkan).

### Struktur Field

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `tanggal_ceklis` | 911 | Tanggal Ceklis Tindakan Invasif | date | ya | |
| `jam_ceklis` | 912 | Jam Ceklis Tindakan Invasif | time | ya | |
| `ruangan` | 913 | Ruang Pelaksanaan | select | ya | |
| `nama_tindakan` | 913 | Ruang Pelaksanaan | text | ya | |
| `gelang_identitas` | 914 | Gelang Identitas Pasien | select | ya | ✓ / ● / ✗ |
| `persiapan_pencernaan` | 915 | Persiapan Pencernaan | select | ya | Puasa, Cairan (berbobot) |
| `persiapan_kulit` | 916 | Persiapan Kulit | select | ya | Mandi, cukur |
| `persiapan_darah` | 917 | Persiapan Darah | select | ya | |
| `penandaan_area` | 918 | Penandaan Area Tindakan | select | ya | |
| `persetujuan_tindakan` | 919 | Persetujuan Tindakan | select | ya | |
| `berkas_rekam_medis` | 920 | Kelengkapan Berkas | select | ya | |
| `blanko_laporan` | 920 | Kelengkapan Berkas | select | ya | |
| `barang_milik_pasien` | 920 | Kelengkapan Berkas | select | ya | |
| `vital_sign` | 921 | Vital Sign Pra Tindakan | select | ya | |
| `foto_penunjang` | 922 | Foto Penunjang | select | ya | |
| `kelengkapan_alat` | 923 | Kelengkapan Alat | select | ya | |
| `keterangan_tambahan` | 923 | Kelengkapan Alat | textarea | tidak | |

### Dashboard & Form Master

```php
['form_id' => 97, 'nama_form' => 'Ceklis Pre & Post Tindakan Invasif', 'slug' => 'ceklis_tindakan_invasif', 'id_dash_menu' => '15.305', 'ri' => 1, 'rj' => 0, 'igd' => 1, 'mcu' => 0],
```

### Mapping

```php
97 => [
    'tanggal_ceklis' => 911, 'jam_ceklis' => 912, 'ruangan' => 913, 'nama_tindakan' => 913,
    'gelang_identitas' => 914, 'persiapan_pencernaan' => 915, 'persiapan_kulit' => 916,
    'persiapan_darah' => 917, 'penandaan_area' => 918, 'persetujuan_tindakan' => 919,
    'berkas_rekam_medis' => 920, 'blanko_laporan' => 920, 'barang_milik_pasien' => 920,
    'vital_sign' => 921, 'foto_penunjang' => 922,
    'kelengkapan_alat' => 923, 'keterangan_tambahan' => 923,
],
```

### Akses EHR

```php
['profesi_id' => 1, 'form_id' => 97, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 2, 'form_id' => 97, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
```

### Validasi

- Setiap status `required|in:1,0,-1` (✓ Ya / ● Tidak / ✗ Tidak Didapatkan).
- Status **✗ (tidak mendapatkan)** pada butir `gelang_identitas`, `persetujuan_tindakan`, atau `berkas_rekam_medis` ⇒ **hard gate**, `store()` ditolak.

### Cetak

`.../CeklisTindakanInvasif/print.blade.php`.

---

## 16. Form 98 — Surveilans Luka Insisi

### Rasional
Surveilans infeksi luka insisi (SSI) sesuai WHO/INCDC. Tiga tahap: **Pre OP** (sebelum operasi), **Durante OP**, **Post OP** (klasifikasi & tanda infeksi).

### Struktur Field

**Tahap Pre OP (924–931)**

| variabel | objek_id | nama_objek | tipe kontrol | wajib |
|---|---|---|---|---|
| `tgl_mrs` | 926 | Tanggal MRS | date | ya |
| `tgl_operasi` | 927 | Tanggal Operasi | date | ya |
| `jam_operasi` | 928 | Jam Operasi | time | ya |
| `lama_operasi` | 929 | Lama Operasi | text | ya |
| `jenis_operasi` | 924 | Identitas Operasi | select | ya |
| `kamar_operasi` | 924 | Identitas Operasi | select | ya |
| `dokter_bedah` | 930 | Kualifikasi Dokter Bedah | radio | ya |
| `trauma_pada` | 925 | Trauma Pada | radio | ya |

**Tahap Post OP (931–936)**

| variabel | objek_id | nama_objek | tipe kontrol | wajib |
|---|---|---|---|---|
| `klasifikasi_luka` | 931 | Klasifikasi Luka Insisi | select | ya |
| `profilaksis_1` | 932 | Pencegahan Infeksi Luka | checkbox | ya |
| `tanda_infeksi_1..5` | 933 | Tanda Infeksi Luka | checkbox | ya |
| `kondisi_luka_post` | 934 | Kondisi Luka Postsurgikal | select | ya |
| `drainase_luka` | 935 | Drainase Luka | radio | ya |
| `keterangan_surveilans` | 936 | Keterangan Surveilans | textarea | tidak |

### Tabel Klasifikasi Luka Insisi

| Grade | Kriteria |
|---|---|
| **I** | Luka bersih, inflamasi minimal, tidak ada eksudat/pengeringan |
| **II** | Luka dengan kemerahan dan pembengkakan, tanpa eksudat purulen, tidak melebar ke jaringan dalam |
| **III** | Luka dengan eksudat purulen, melebar hingga jaringan dalam, atau adanya abses |
| **IV** | Luka dengan gangren, nekrosis, atau kerusakan jaringan vital |

**Klasifikasi SSI menurut CDC (ringkas):**

| Kategori | Definisi |
|---|---|
| Superficial | Involusi hanya kulit dan jaringan subkutan |
| Deep | Involusi jaringan dalam (fascia, otot) |
| Organ/space | Involusi organ atau rongga |

> Tabel lengkap klasifikasi SSI perlu diselaraskan dengan pedoman CDC/INCDC terbaru saat implementasi.

### Dashboard & Form Master

```php
['form_id' => 98, 'nama_form' => 'Surveilans Luka Insisi', 'slug' => 'surveilans_luka_insisi', 'id_dash_menu' => '15.306', 'ri' => 1, 'rj' => 0, 'igd' => 0, 'mcu' => 0],
```

### Mapping

```php
98 => [
    'jenis_operasi' => 924, 'kamar_operasi' => 924, 'trauma_pada' => 925,
    'tgl_mrs' => 926, 'tgl_operasi' => 927, 'jam_operasi' => 928, 'lama_operasi' => 929,
    'dokter_bedah' => 930, 'klasifikasi_luka' => 931,
    'kondisi_luka_post' => 934, 'drainase_luka' => 935, 'keterangan_surveilans' => 936,
],
for ($i = 1; $i <= 5; $i++) { $mapping[98]['profilaksis_'.$i] = 932; $mapping[98]['tanda_infeksi_'.$i] = 933; }
```

### Akses EHR

```php
['profesi_id' => 1, 'form_id' => 98, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 2, 'form_id' => 98, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
```

### Validasi

- `tgl_operasi >= tgl_mrs`.
- `trauma_pada = 'Ada'` ⇒ wajib menyertakan keterangan.
- `tanda_inflamasi_*` minimal satu tercentang bila `klasifikasi_luka >= II`.

### Cetak

`.../SurveilansLukaInsisi/print.blade.php`.

---

## 17. Form 99 — Catatan Anestesia

### Rasional
Catatan anestesi lengkap yang diisi oleh anestesiologist: data pasien, jenis anestesi, jalan napas, ventilasi, sirkuit, posisi, monitoring, penunjang, obat & cairan intraoperatif, komplikasi, kondisi akhir.

### Struktur Field

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `tanggal_catatan` | 937 | Tanggal Catatan Anestesia | date | ya | |
| `jam_mulai` | 938 | Jam Mulai Anestesia | time | ya | |
| `jam_selesai` | 939 | Jam Selesai Anestesia | time | ya | |
| `lama_anestesia` | 940 | Lama Anestesia | text | ya | |
| `dokter_anestesi` | 941 | Dokter Anestesia | `x-select_dokter` | ya | |
| `perawat_anestesi` | 942 | Perawat Anestesia | `x-select_pegawai` | ya | |
| `jenis_anestesi` | 943 | Jenis Anestesia | radio | ya | Umum / Regional / Epidural / Spinal / Sedasi |
| `airway` | 944 | Jalan Napas & Ventilasi | radio | ya | |
| `airway_2` | 944 | Jalan Napas & Ventilasi | text | ya | |
| `ventilasi` | 944 | Jalan Napas & Ventilasi | radio | ya | spontan / assisted / controlled |
| `sirkuit` | 945 | Sirkuit & Perlengkapan | text | ya | |
| `caudel` | 945 | Sirkuit & Perlengkapan | text | ya | ukuran |
| `posisi` | 946 | Posisi & Fixasi | select | ya | |
| `monitor` | 947 | Monitoring | text | ya | |
| `asa` | 947 | Monitoring | select | ya | I–VI |
| `penunjang_lab` | 948 | Hasil Laboratorium Penunjang | textarea | ya | |
| `riwayat_penyakit` | 949 | Riwayat Penyakit & Alergi | textarea | ya | |
| `saraf_perifer` | 949 | Riwayat Penyakit & Alergi | text | ya | |
| `vaskular` | 950 | Vaskular & Monitoring | textarea | ya | |
| `saturasi` | 15 | Saturasi (reuse) | number | ya | |
| `sistolik` | 6 | TD Sistolik (reuse) | number | ya | |
| `diastolik` | 7 | TD Diastolik (reuse) | number | ya | |
| `nadi` | 10 | Nadi (reuse) | number | ya | |
| `pernapasan` | 12 | Pernapasan (reuse) | number | ya | |
| `suhu` | 11 | Suhu (reuse) | number | ya | |
| `kesadaran` | 51 | Kesadaran (reuse) | select | ya | |
| `obat_cairan_intraop` | 950 | Vaskular & Monitoring | textarea | ya | |
| `kejadian_tidak_terduga` | 951 | Kejadian Tidak Terduga | textarea | ya | |
| `penanganan_intraop` | 952 | Penanganan Intraoperatif | textarea | ya | |
| `kondisi_akhir` | 953 | Kondisi Akhir Anestesia | textarea | ya | |
| `instruksi_pasca` | 954 | Instruksi Pasca Anestesia | textarea | ya | |
| `pesakit_ruang` | 954 | Instruksi Pasca Anestesia | select | ya | ruang tujuan |

### Dashboard & Form Master

```php
['form_id' => 99, 'nama_form' => 'Catatan Anestesia', 'slug' => 'catatan_anestesia', 'id_dash_menu' => '15.307', 'ri' => 1, 'rj' => 0, 'igd' => 1, 'mcu' => 0],
```

### Mapping

```php
99 => [
    'tanggal_catatan' => 937, 'jam_mulai' => 938, 'jam_selesai' => 939, 'lama_anestesia' => 940,
    'dokter_anestesi' => 941, 'perawat_anestesi' => 942, 'jenis_anestesi' => 943,
    'airway' => 944, 'airway_2' => 944, 'ventilasi' => 944,
    'sirkuit' => 945, 'caudel' => 945,
    'posisi' => 946, 'monitor' => 947, 'asa' => 947,
    'penunjang_lab' => 948, 'riwayat_penyakit' => 949, 'saraf_perifer' => 949,
    'vaskular' => 950, 'obat_cairan_intraop' => 950,
    'sistolik' => 6, 'diastolik' => 7, 'nadi' => 10, 'pernapasan' => 12, 'suhu' => 11, 'saturasi' => 15, 'kesadaran' => 51,
    'kejadian_tidak_terduga' => 951, 'penanganan_intraop' => 952, 'kondisi_akhir' => 953,
    'instruksi_pasca' => 954, 'ruang_tujuan' => 954,
],
```

### Akses EHR

```php
['profesi_id' => 1, 'form_id' => 99, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 2, 'form_id' => 99, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
```

### Validasi

- `jam_selesai >= jam_mulai`; `lama_anestesia` **dihitung server**.
- `jenis_anestesi = 'Epidural' | 'Spinal'` ⇒ `caudel` wajib.
- `kejadian_tidak_terduga` wajib ⇒ `penanganan_intraop` wajib (chain).

### Cetak

`.../CatatanAnestesia/print.blade.php`.

---

## 18. Form 100 — Ceklist Keamanan Angiografi

### Rasional
Ceklist keamanan sebelum angiografi kateter (cardiac cath) — identifikasi dengan MSCT. Fokus: identifikasi & gelang, informed consent, jalur IV, obat, instrumen, EKG 12 lead, hasil laboratorium, radiologi, steril.

### Struktur Field

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `tanggal_tiba_ruangan` | 955 | Tanggal Tiba Ruangan | date | ya | |
| `jam_sign_in` | 956 | Jam Sign In | time | ya | |
| `jam_time_out` | 957 | Jam Time Out | time | ya | |
| `jam_sign_out` | 958 | Jam Sign Out | time | ya | |
| `gelang_pasien` | 959 | Identitas & Gelang Pasien | radio | ya | Terpasang / Tidak → alasan |
| `tidak_dipasang_alasan` | 959 | Identitas & Gelang Pasien | text | ya bila tidak | |
| `baca_secara_verbal` | 960 | Pembacaan Verbal | radio | ya | |
| `informed_consent` | 960 | Pembacaan Verbal | radio | ya | Tidak Ada / Ada → nama |
| `nama_pasien` | 959 | Identitas & Gelang Pasien | text | ya | |
| `tanggal_tindakan` | 961 | Identitas Tindakan | date | ya | |
| `nama_tindakan` | 961 | Identitas Tindakan | text | ya | |
| `tim_operator` | 962 | Tim Operator | text | ya | |
| `prosedur_tindakan` | 962 | Tim Operator | text | ya | |
| `iv_line` | 963 | IV Line & Obat | radio | ya | Dipasang / Tidak → alasan |
| `tidak_dipasang_alasan` | 963 | IV Line & Obat | text | ya bila tidak | |
| `nama_obat` | 963 | IV Line & Obat | text | ya | |
| `alat_instrumen` | 964 | Alat & Instrumen | radio | ya | Lengkap / Tidak → alasan |
| `tidak_lengkap_alasan` | 964 | Alat & Instrumen | text | ya bila tidak | |
| `ekg_12lead` | 965 | EKG 12 Lead | radio | ya | |
| `hasil_laboratorium` | 966 | Hasil Laboratorium | radio | ya | Ada / Tidak Ada |
| `hasil_radiologi` | 966 | Hasil Laboratorium | radio | ya | |
| `hasil_pemeriksaan_lain` | 966 | Hasil Laboratorium | textarea | ya | |
| `formulir_permintaan` | 967 | Formulir & Dokumentasi | radio | ya | Sudah dilengkapi identitas / Tidak |
| `diberikan_edukasi` | 967 | Formulir & Dokumentasi | radio | ya | Diberikan / Tidak → alasan |
| `tidak_diberikan_alasan` | 967 | Formulir & Dokumentasi | text | ya bila tidak | |
| `disinfectant_intra` | 964 | Alat & Instrumen | radio | ya | |

### Dashboard & Form Master

```php
['form_id' => 100, 'nama_form' => 'Ceklist Keamanan Angiografi', 'slug' => 'ceklist_keamanan_angiografi', 'id_dash_menu' => '15.308', 'ri' => 1, 'rj' => 0, 'igd' => 1, 'mcu' => 0],
```

### Mapping

```php
100 => [
    'tanggal_tiba_ruangan' => 955, 'jam_sign_in' => 956, 'jam_time_out' => 957, 'jam_sign_out' => 958,
    'gelang_pasien' => 959, 'tidak_dipasang_alasan' => 959, 'nama_pasien' => 959,
    'baca_secara_verbal' => 960, 'informed_consent' => 960,
    'tanggal_tindakan' => 961, 'nama_tindakan' => 961,
    'tim_operator' => 962, 'prosedur_tindakan' => 962,
    'iv_line' => 963, 'nama_obat' => 963,
    'alat_instrumen' => 964, 'tidak_lengkap_alasan' => 964, 'disinfectant_intra' => 964,
    'ekg_12lead' => 965,
    'hasil_laboratorium' => 966, 'hasil_radiologi' => 966, 'hasil_pemeriksaan_lain' => 966,
    'formulir_permintaan' => 967, 'diberikan_edukasi' => 967, 'tidak_diberikan_alasan' => 967,
],
```

### Akses EHR

```php
['profesi_id' => 1, 'form_id' => 100, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 2, 'form_id' => 100, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
```

### Validasi

- Semua butir wajib; `gelang_pasien = 'Tidak'` ⇒ **hard gate** (tindakan tidak boleh tanpa gelang).
- `jam_sign_in < jam_time_out < jam_sign_out`.
- `informed_consent = 'Ada'` wajib.

### Cetak

`.../CeklistKeamananAngiografi/print.blade.php`.

---

## 19. Form 101 — Asesmen Katarak

### Rasional
Asesmen keperawatan khusus pasien operasi katarak (legacy `asesmen_katarak.php`): risiko jatuh, nyeri, gangguan penglihatan, diagnosis keperawatan, tujuan, intervensi pre/post operasi, jadwal pemberian obat, dan TTV.

### Struktur Field

| variabel | objek_id | nama_objek | tipe kontrol | wajib | keterangan |
|---|---|---|---|---|---|
| `tanggal_asesmen` | 968 | Tanggal Asesmen Katarak | date | ya | |
| `jam_asesmen` | 969 | Jam Asesmen Katarak | time | ya | |
| `dpjp_utama` | 970 | DPJP Utama | `x-select_dokter` | ya | |
| `keluhan` | 13 | Keluhan Utama (reuse) | textarea | ya | |
| `kesadaran` | 51 | Kesadaran (reuse) | select | ya | |
| `resiko_jatuh` | 971 | Skrining Risiko Jatuh | radio | ya | Risiko Tinggi / Tidak Berisiko |
| `edukasi` | 971 | Skrining Risiko Jatuh | textarea | ya | |
| `diagnosa_keperawatan` | 972 | Diagnosis Keperawatan | textarea | ya | |
| `tujuan_kriteria_hasil` | 973 | Tujuan & Kriteria Hasil | textarea | ya | |
| `intervensi_pre_operasi` | 974 | Intervensi Pre Operasi | textarea | ya | |
| `intervensi_post_operasi` | 975 | Intervensi Post Operasi | textarea | ya | |
| `skala_nyeri` | 976 | Skala Nyeri | number | ya | VAS |
| `jam_pemberian_obat_1..5` | 977 | Jadwal Pemberian Obat | time | tidak | baris berulang |
| `nama_obat_1..5` | 977 | Jadwal Pemberian Obat | text | tidak | idem |
| `sistolik_1..5` | 6 | TD Sistolik (reuse) | number | ya | TTV pasca operasi |
| `diastolik_1..5` | 7 | TD Diastolik (reuse) | number | ya | idem |
| `nadi_1..5` | 10 | Nadi (reuse) | number | ya | |
| `suhu_1..5` | 11 | Suhu (reuse) | number | ya | |
| `pernapasan_1..5` | 12 | Pernapasan (reuse) | number | ya | |
| `skala_nyeri_1..5` | 976 | Skala Nyeri | number | ya | |
| `visus_kanan_1..5` | 978 | Pemeriksaan Mata | text | ya | visus mata kanan setelah operasi |
| `visus_kiri_1..5` | 979 | Pemeriksaan Mata | text | ya | idem |

### Dashboard & Form Master

```php
['form_id' => 101, 'nama_form' => 'Asesmen Katarak', 'slug' => 'asesmen_katarak', 'id_dash_menu' => '15.309', 'ri' => 0, 'rj' => 1, 'igd' => 0, 'mcu' => 0],
```

### Mapping

```php
101 => [
    'tanggal_asesmen' => 968, 'jam_asesmen' => 969, 'dpjp_utama' => 970,
    'keluhan' => 13, 'kesadaran' => 51,
    'resiko_jatuh' => 971, 'edukasi' => 971,
    'diagnosa_keperawatan' => 972, 'tujuan_kriteria_hasil' => 973,
    'intervensi_pre_operasi' => 974, 'intervensi_post_operasi' => 975,
    'skala_nyeri' => 976,
],
for ($i = 1; $i <= 5; $i++) {
    $mapping[101]['jam_pemberian_obat_'.$i] = 977;
    $mapping[101]['nama_obat_'.$i] = 977;
    $mapping[101]['sistolik_'.$i] = 6;
    $mapping[101]['diastolik_'.$i] = 7;
    $mapping[101]['nadi_'.$i] = 10;
    $mapping[101]['suhu_'.$i] = 11;
    $mapping[101]['pernapasan_'.$i] = 12;
    $mapping[101]['skala_nyeri_'.$i] = 976;
    $mapping[101]['visus_kanan_'.$i] = 978;
    $mapping[101]['visus_kiri_'.$i] = 979;
}
```

### Akses EHR

```php
['profesi_id' => 1, 'form_id' => 101, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
['profesi_id' => 2, 'form_id' => 101, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
```

### Validasi

- `resiko_jatuh` `required`; bila `Risiko Tinggi` ⇒ `intervensi_pre_operasi` wajib memuat intervensi.
- `skala_nyeri 1..5` wajib (evaluasi pasca operasi).
- Diagnosis keperawatan minimal 1 baris.

### Cetak

`.../AsesmenKatarak/print.blade.php` — lembar asesmen + jadwal pemberian obat + TTV harian.

---

## 20. Implementasi

| # | Path | Keterangan |
|---|---|---|
| 1 | `app/Helpers/SkorPemulihanHelper.php` | Aldrete, Bromage, Steward, PADSS (form 87) |
| 2 | `app/Helpers/ChecklistKeselamatanHelper.php` | hard-gate evaluator WHO checklist (form 89), angiografi (form 100), invasif (form 97) |
| 3 | `app/Http/Controllers/EMR/AsesmenPraBedah/AsesmenPraBedahController.php` | form 85 |
| 4 | `.../EvaluasiPreAnestesiSedasi/...Controller.php` | form 86 |
| 5 | `.../AsesmenPostAnestesiSedasi/...Controller.php` | form 87 |
| 6 | `.../MonitoringAnestesiSedasi/...Controller.php` | form 88 |
| 7 | `.../ChecklistKeamananOperasi/...Controller.php` | form 89 |
| 8 | `.../ChecklistKesiapanAnestesi/...Controller.php` | form 90 |
| 9 | `.../PengkajianOperasi/...Controller.php` | form 91 |
| 10 | `.../LaporanOperasi/...Controller.php` | form 92 |
| 11 | `.../CatatanBedah/...Controller.php` | form 93 |
| 12 | `.../TransferPasien/...Controller.php` | form 94 |
| 13 | `.../PenandaanLokasiOperasi/...Controller.php` | form 95 |
| 14 | `.../PenandaanPasien/...Controller.php` | form 96 |
| 15 | `.../CeklisTindakanInvasif/...Controller.php` | form 97 |
| 16 | `.../SurveilansLukaInsisi/...Controller.php` | form 98 |
| 17 | `.../CatatanAnestesia/...Controller.php` | form 99 |
| 18 | `.../CeklistKeamananAngiografi/...Controller.php` | form 100 |
| 19 | `.../AsesmenKatarak/...Controller.php` | form 101 |
| 20 | `resources/views/moduls/EMR/<Folder>/index.blade.php` × 17 | 17 view |
| 21 | `resources/views/moduls/EMR/<Folder>/print.blade.php` × 17 | 17 view cetak |
| 22 | `resources/views/moduls/EMR/PartialForm/skor_pemulihan.blade.php` | partial 4 skor (form 87) |
| 23 | `resources/views/moduls/EMR/PartialForm/ttv_berulang.blade.php` | partial TTV baris berulang (form 88, 101) |
| 24 | `app/Helpers/SelectOption.php` | tambah key `jenis_operasi`, `jenis_anestesia`, `posisi_operasi`, `klasifikasi_luka`, `asa_kelas` |
| 25 | `database/seeders/EmrMasterSeeder.php` | `$menus`, `$subMenus`, `$forms`, `$objeks`, `$mapping`, `$akses` |
| 26 | `AGENTS.md` | entri form 85–101 |

**Migration / master baru:** **tidak ada migration**. Seluruh dropdown memakai `bagian` (ruang/kamar operasi), `pegawai`, dan `SelectOption`. Bila dibutuhkan master **Tindakan Bedah** (katalog operasi) atau **Alat/Instrument Bedah** (`pemakaian_alkes_bedah.php` legacy), tambahkan sebagai iterasi lanjutan.

Eksekusi:

```bash
docker compose exec app php artisan db:seed --class=EmrMasterSeeder
# tambah EmrHelper::backfillObjekId(85..101)
```

---

## 21. Catatan & Risiko

| # | Risiko | Mitigasi |
|---|---|---|
| 1 | `id_dash_menu` `'15.293'`..`'15.309'` — `"15.293" == "15.302"` true. | Semua query `id_dash_menu` WAJIB `===` atau `where` di SQL. |
| 2 | **Safety gate keras** (form 89, 90, 97, 100) menolak `store()` bila checklist tidak lengkap. Salah konfigurasi bisa memblokir operasi. | Gate hanya menolak bila ada `Belum`/✗ **pada butir kritis** (gelang, consent, profilaksis).-testing di rumah sakit sebelum go-live; pesan error harus sangat eksplisit. |
| 3 | Suffix angka (form 88 `*_1..20`, form 94 `dokter_rawat_1..5`, form 96 `alergi_1..5`, form 101 `*_1..5`, form 92 `komplikasi_operasi_1..6`) wajib konsisten. | Mapping digenerate dengan loop di seeder. |
| 4 | Skor pemulihan (Aldrete/Bromage/Steward/PADSS) diisi browser bisa dimanipulasi. | `SkorPemulihanHelper::hitung()` dihitung ulang server; nilai browser dibuang (PANDUAN §2.4). |
| 5 | Tabel skor (ASA, Aldrete, Bromage, Steward, PADSS, klasifikasi luka) dalam dokumen ini **mengikuti standar internasional** — beberapa label tertulis dalam bahasa Inggris/campuran. | **Tulis ulang seluruh label dalam Bahasa Indonesia** saat seeding `SelectOption` dan di blade; konfirmasi ke klinis. |
| 6 | `gCS_score` (form 94) harus = `gcs_eye + gcs_motorik + gcs_verbal` — **dihitung server**. | `filteredData()` menghitung ulang; nilai browser dibuang. |
| 7 | Gambar (`lampiran_monitoring`, `lampiran_penandaan`) disimpan sebagai base64 di `emr_detail.value` ( pola legacy). | Untuk file besar, better simpan path di STORAGE & simpan path (bukan base64) di `emr_detail`. |
| 8 | `form 89` mapping di atas belum final (ada variabel sementara). | Saat implementasi, pastikan setiap butir WHO punya variabel unik + objek yang sesuai. |
| 9 | Form 88 & 90 mengganti pola legacy "upload gambar" dengan form native — **perubahan alur kerja**, bukan sekadar port. | Koordinasi dengan tim anestesi & bedah; sediakan **kolom lampiran gambar** di kedua form sebagai fallback. |
| 10 | 218 objek baru dipakai (id band 762–992, terpakai 762–979); total objek mendekati 1.100. | Filter per menu di Manajemen EMR → Form sudah tersedia; pertimbangkan pencarian objek. |
| 11 | `dokter_rawat_1..5` (form 94) memakai objek 889 bersama `dokter_pengantar` & `perawat_ruangan` — laporan perlu differentiates. | Dokumentasikan; bila perlu pisahkan objek di iterasi berikutnya. |