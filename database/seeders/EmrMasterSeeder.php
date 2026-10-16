<?php

namespace Database\Seeders;

use App\Helpers\EmrHelper;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmrMasterSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        // ======== Dashboard Pasien (dashboard_menu -> dashboard_menu_sub -> dashboard_menu_sub_extra) ========
        $menus = [
            ['dashboard_menu_id' => 1, 'nama_menu' => 'Catatan Medis'],
            ['dashboard_menu_id' => 2, 'nama_menu' => 'Catatan Keperawatan'],
            ['dashboard_menu_id' => 3, 'nama_menu' => 'Resep'],
            ['dashboard_menu_id' => 4, 'nama_menu' => 'Order'],
            ['dashboard_menu_id' => 5, 'nama_menu' => 'Formulir'],
            // Menu BARU Fase P1.
            ['dashboard_menu_id' => 6, 'nama_menu' => 'Resume & Discharge'],
            ['dashboard_menu_id' => 7, 'nama_menu' => 'Gawat Darurat'],
            // ===== Fase P2 — Asuhan Keperawatan, Cairan, Nyeri, Skala =====
            ['dashboard_menu_id' => 8, 'nama_menu' => 'Penilaian Klinis'],
            ['dashboard_menu_id' => 9, 'nama_menu' => 'Monitoring'],
            // ===== Fase P3 — Gizi & Nutrisi, Farmasi, Order Penunjang =====
            ['dashboard_menu_id' => 10, 'nama_menu' => 'Gizi & Nutrisi'],
            ['dashboard_menu_id' => 11, 'nama_menu' => 'Farmasi'],
            // ===== Fase P4 — Kebidanan & Neonatal, Medical Check Up =====
            ['dashboard_menu_id' => 13, 'nama_menu' => 'Kebidanan & Neonatal'],
            ['dashboard_menu_id' => 14, 'nama_menu' => 'Medical Check Up'],
            // ===== Fase P5 — ICU & Hemodialisa =====
            ['dashboard_menu_id' => 12, 'nama_menu' => 'ICU & Hemodialisa'],
            // ===== Fase P5 — Bedah & Anestesi =====
            ['dashboard_menu_id' => 15, 'nama_menu' => 'Bedah & Anestesi'],
            // ===== Fase P6 — Onkologi & Darah =====
            ['dashboard_menu_id' => 18, 'nama_menu' => 'Onkologi & Darah'],
            // ===== Fase P6 — Psikososial & Kerohanian =====
            ['dashboard_menu_id' => 16, 'nama_menu' => 'Psikososial & Kerohanian'],
            ['dashboard_menu_id' => 17, 'nama_menu' => 'Surveilans Infeksi'],
        ];
        foreach ($menus as $menu) {
            DB::table('dashboard_menu')->updateOrInsert(
                ['dashboard_menu_id' => $menu['dashboard_menu_id']],
                array_merge($menu, [
                    'input_time' => $now,
                    'input_user_id' => 1,
                    'status_batal' => 0,
                ])
            );
        }
        // Menu lama "Resep" (3) digantikan menu "Order" (4) — soft-delete agar
        // tidak ada menu kosong yang tampil di dashboard pasien.
        DB::table('dashboard_menu')
            ->where('dashboard_menu_id', 3)
            ->update([
                'status_batal' => 1,
                'mod_time' => $now,
                'mod_user_id' => 1,
            ]);
        $subMenus = [
            // Menu 1 "Catatan Medis"
            ['dashboard_menu_sub_id' => 1, 'dashboard_menu_id' => 1, 'nama_sub_menu' => 'Soap'],
            // Menu 2 "Catatan Keperawatan"
            // Sub menu TANPA extra: header_ehr memakai CONCAT_WS sehingga id_dash_menu
            // = "2.8", dan EmrDashboard merender sub ini sebagai link langsung dengan
            // Str::slug(nama_sub_menu) — WAJIB sama dengan slug form.
            ['dashboard_menu_sub_id' => 8, 'dashboard_menu_id' => 2, 'nama_sub_menu' => 'Implementasi Keperawatan'],
            // Menu 1 "Catatan Medis" — Tindakan Medis (tanpa extra, link langsung).
            // Str::slug('Tindakan Medis','_') WAJIB sama dengan form.slug = tindakan_medis.
            ['dashboard_menu_sub_id' => 9, 'dashboard_menu_id' => 1, 'nama_sub_menu' => 'Tindakan Medis'],
            // Menu 1 "Catatan Medis" — Assesmen Awal Medis Rawat Jalan (tanpa
            // extra, link langsung). Str::slug(nama,'_') WAJIB sama dengan
            // form.slug = assesmen_awal_medis_rawat_jalan.
            ['dashboard_menu_sub_id' => 10, 'dashboard_menu_id' => 1, 'nama_sub_menu' => 'Assesmen Awal Medis Rawat Jalan'],
            // Menu 1 "Catatan Medis" — SBAR (tanpa extra, link langsung).
            // Str::slug('SBAR','_') = "sbar" WAJIB sama dengan form.slug = sbar
            // (Str::studly('sbar') = "Sbar", bukan "SBAR" — jadi nama folder
            // controller/view tetap Sbar).
            ['dashboard_menu_sub_id' => 11, 'dashboard_menu_id' => 1, 'nama_sub_menu' => 'SBAR'],
            // Menu 2 "Catatan Keperawatan" — Observasi Harian. Sub menu DENGAN
            // extra, jadi id_dash_menu = "2.12.3" dan form_name di dashboard
            // diambil dari Str::slug(nama_sub_menu_extra) = "tanda_vital".
            ['dashboard_menu_sub_id' => 12, 'dashboard_menu_id' => 2, 'nama_sub_menu' => 'Observasi Harian'],
            // Menu 2 "Catatan Keperawatan"
            ['dashboard_menu_sub_id' => 2, 'dashboard_menu_id' => 2, 'nama_sub_menu' => 'Pengkajian Keperawatan'],
            // Menu 3 "Resep" (lama, soft-delete) — sub 3 "Peresepan Obat" digantikan "Order Resep"
            ['dashboard_menu_sub_id' => 3, 'dashboard_menu_id' => 3, 'nama_sub_menu' => 'Peresepan Obat'],
            // Menu 4 "Order" — nama sub menu harus sama dengan slug form agar
            // dashboard meneruskan form_name yang benar (Str::slug nama sub di view).
            ['dashboard_menu_sub_id' => 4, 'dashboard_menu_id' => 4, 'nama_sub_menu' => 'Order Resep'],
            ['dashboard_menu_sub_id' => 5, 'dashboard_menu_id' => 4, 'nama_sub_menu' => 'Laboratorium'],
            ['dashboard_menu_sub_id' => 6, 'dashboard_menu_id' => 4, 'nama_sub_menu' => 'Radiologi'],
            // Menu 5 "Formulir" — tanpa extra; id_dash_menu form = "5.7".
            ['dashboard_menu_sub_id' => 7, 'dashboard_menu_id' => 5, 'nama_sub_menu' => 'Konsultasi'],
            // ===== Fase P1 — Catatan Medis LanjutAN =====
            // Menu 1 "Catatan Medis". Sub TANPA extra (link langsung), jadi
            // id_dash_menu = "1.{sub}" dan Str::slug(nama_sub_menu,'_') WAJIB
            // sama dengan form.slug.
            ['dashboard_menu_sub_id' => 13, 'dashboard_menu_id' => 1, 'nama_sub_menu' => 'Resume Medis'],
            ['dashboard_menu_sub_id' => 14, 'dashboard_menu_id' => 1, 'nama_sub_menu' => 'Care Plan'],
            // Sub 15 "Catatan Medis Visum" punya dua leaf: form 57 tanpa extra
            // (id_dash_menu "1.15") dan form 21 lewat extra 6 "DAR" ("1.15.6").
            // PERHATIKAN: Str::slug('Catatan Medis Visum','_') =
            // 'catatan_medis_visum' = form 57.slug, BUKAN form 21.
            ['dashboard_menu_sub_id' => 15, 'dashboard_menu_id' => 1, 'nama_sub_menu' => 'Catatan Medis Visum'],
            // Menu 6 "Resume & Discharge" — sub TANPA extra (link langsung).
            // dashboard_menu_sub_id bersifat GLOBAL, jadi bukan 1/2 melainkan 33/34
            // (lihat docs/ALOKASI_ID_GLOBAL.md §3).
            ['dashboard_menu_sub_id' => 33, 'dashboard_menu_id' => 6, 'nama_sub_menu' => 'Discharge Planning'],
            ['dashboard_menu_sub_id' => 34, 'dashboard_menu_id' => 6, 'nama_sub_menu' => 'Pemulangan Pasien'],
            // Menu 7 "Gawat Darurat" — sub 53 dipakai form 19 Triage IGD.
            // Sub 54 dicadangkan untuk Triage IGD OBGYN (form berikutnya).
            ['dashboard_menu_sub_id' => 53, 'dashboard_menu_id' => 7, 'nama_sub_menu' => 'Triage IGD'],
            ['dashboard_menu_sub_id' => 54, 'dashboard_menu_id' => 7, 'nama_sub_menu' => 'Triage IGD OBGYN'],
            // ===== Sub-menu Fase P2 =====
            // dashboard_menu_sub_id bersifat GLOBAL (lihat docs/ALOKASI_ID_GLOBAL.md §0).
            // Str::slug(nama_sub_menu,'_') WAJIB sama dengan form.slug untuk sub TANPA extra.
            ['dashboard_menu_sub_id' => 73, 'dashboard_menu_id' => 2, 'nama_sub_menu' => 'Asuhan Keperawatan'],
            ['dashboard_menu_sub_id' => 74, 'dashboard_menu_id' => 2, 'nama_sub_menu' => 'Serah Terima'],
            ['dashboard_menu_sub_id' => 75, 'dashboard_menu_id' => 2, 'nama_sub_menu' => 'Asesmen Klinis Keperawatan'],
            ['dashboard_menu_sub_id' => 76, 'dashboard_menu_id' => 8, 'nama_sub_menu' => 'Asesmen Pasien'],
            ['dashboard_menu_sub_id' => 77, 'dashboard_menu_id' => 8, 'nama_sub_menu' => 'Anamnesis'],
            ['dashboard_menu_sub_id' => 93, 'dashboard_menu_id' => 2, 'nama_sub_menu' => 'Balance Cairan'],
            ['dashboard_menu_sub_id' => 94, 'dashboard_menu_id' => 9, 'nama_sub_menu' => 'Monitoring Cairan'],
            ['dashboard_menu_sub_id' => 95, 'dashboard_menu_id' => 9, 'nama_sub_menu' => 'Kesadaran Oksigen'],
            ['dashboard_menu_sub_id' => 113, 'dashboard_menu_id' => 9, 'nama_sub_menu' => 'Nyeri'],
            ['dashboard_menu_sub_id' => 114, 'dashboard_menu_id' => 9, 'nama_sub_menu' => 'Body Map Nyeri'],
            ['dashboard_menu_sub_id' => 133, 'dashboard_menu_id' => 8, 'nama_sub_menu' => 'Risiko Jatuh'],
            ['dashboard_menu_sub_id' => 134, 'dashboard_menu_id' => 8, 'nama_sub_menu' => 'Penilaian Dekubitus'],
            // ===== Fase P3 — Gizi & Nutrisi (menu 10) =====
            // nama_sub_menu WAJIB menghasilkan slug = form.slug (dipakai sebagai
            // form_name di URL dashboard pasien).
            ['dashboard_menu_sub_id' => 153, 'dashboard_menu_id' => 10, 'nama_sub_menu' => 'Asesmen Gizi'],
            ['dashboard_menu_sub_id' => 154, 'dashboard_menu_id' => 10, 'nama_sub_menu' => 'Detail Nutrisi'],
            ['dashboard_menu_sub_id' => 155, 'dashboard_menu_id' => 10, 'nama_sub_menu' => 'Rekap Asesmen Nutrisi'],
            ['dashboard_menu_sub_id' => 156, 'dashboard_menu_id' => 10, 'nama_sub_menu' => 'Pesan Makanan'],
            ['dashboard_menu_sub_id' => 157, 'dashboard_menu_id' => 10, 'nama_sub_menu' => 'Diet Pasien'],
            ['dashboard_menu_sub_id' => 158, 'dashboard_menu_id' => 10, 'nama_sub_menu' => 'ADIME'],
            // ===== Fase P3 — Farmasi (menu 11) =====
            ['dashboard_menu_sub_id' => 173, 'dashboard_menu_id' => 11, 'nama_sub_menu' => 'Daftar Terapi Obat'],
            ['dashboard_menu_sub_id' => 174, 'dashboard_menu_id' => 11, 'nama_sub_menu' => 'Rekonsiliasi Obat'],
            ['dashboard_menu_sub_id' => 175, 'dashboard_menu_id' => 11, 'nama_sub_menu' => 'Telaah Resep'],
            ['dashboard_menu_sub_id' => 176, 'dashboard_menu_id' => 11, 'nama_sub_menu' => 'Telaah Obat'],
            // ===== Fase P3 — Order Penunjang (menu 4 "Order") =====
            ['dashboard_menu_sub_id' => 193, 'dashboard_menu_id' => 4, 'nama_sub_menu' => 'Order Patologi'],
            ['dashboard_menu_sub_id' => 194, 'dashboard_menu_id' => 4, 'nama_sub_menu' => 'Order Mikrobiologi'],
            ['dashboard_menu_sub_id' => 195, 'dashboard_menu_id' => 4, 'nama_sub_menu' => 'Order POCT'],
            // ===== Fase P4 — Kebidanan & Neonatal (menu 13) =====
            // nama_sub_menu WAJIB menghasilkan slug == form.slug (dipakai
            // sebagai form_name di URL dashboard pasien).
            ['dashboard_menu_sub_id' => 253, 'dashboard_menu_id' => 13, 'nama_sub_menu' => 'Asesmen Kebidanan'],
            ['dashboard_menu_sub_id' => 254, 'dashboard_menu_id' => 13, 'nama_sub_menu' => 'Pengkajian Perina'],
            ['dashboard_menu_sub_id' => 255, 'dashboard_menu_id' => 13, 'nama_sub_menu' => 'Pengkajian Non Perina'],
            ['dashboard_menu_sub_id' => 256, 'dashboard_menu_id' => 13, 'nama_sub_menu' => 'Kala Persalinan'],
            ['dashboard_menu_sub_id' => 257, 'dashboard_menu_id' => 13, 'nama_sub_menu' => 'Riwayat Persalinan'],
            ['dashboard_menu_sub_id' => 258, 'dashboard_menu_id' => 13, 'nama_sub_menu' => 'Bayi Baru Lahir'],
            ['dashboard_menu_sub_id' => 259, 'dashboard_menu_id' => 13, 'nama_sub_menu' => 'Riwayat Resusitasi Bayi'],
            ['dashboard_menu_sub_id' => 260, 'dashboard_menu_id' => 13, 'nama_sub_menu' => 'Penilaian Bayi dengan APGAR Score'],
            ['dashboard_menu_sub_id' => 262, 'dashboard_menu_id' => 13, 'nama_sub_menu' => 'Inisiasi Menyusui Dini'],
            ['dashboard_menu_sub_id' => 263, 'dashboard_menu_id' => 13, 'nama_sub_menu' => 'Skrining MPP'],
            // ===== Fase P4 — Medical Check Up (menu 14) =====
            ['dashboard_menu_sub_id' => 273, 'dashboard_menu_id' => 14, 'nama_sub_menu' => 'Kesimpulan MCU'],
            ['dashboard_menu_sub_id' => 274, 'dashboard_menu_id' => 14, 'nama_sub_menu' => 'TTV MCU'],
            ['dashboard_menu_sub_id' => 275, 'dashboard_menu_id' => 14, 'nama_sub_menu' => 'Visus Mata MCU'],
            ['dashboard_menu_sub_id' => 276, 'dashboard_menu_id' => 14, 'nama_sub_menu' => 'Obstetri MCU'],
            ['dashboard_menu_sub_id' => 277, 'dashboard_menu_id' => 14, 'nama_sub_menu' => 'Penyakit Dalam MCU'],
            ['dashboard_menu_sub_id' => 278, 'dashboard_menu_id' => 14, 'nama_sub_menu' => 'Jantung MCU'],
            ['dashboard_menu_sub_id' => 279, 'dashboard_menu_id' => 14, 'nama_sub_menu' => 'THT MCU'],
            ['dashboard_menu_sub_id' => 280, 'dashboard_menu_id' => 14, 'nama_sub_menu' => 'Mata MCU'],
            ['dashboard_menu_sub_id' => 281, 'dashboard_menu_id' => 14, 'nama_sub_menu' => 'Gigi MCU'],
            ['dashboard_menu_sub_id' => 282, 'dashboard_menu_id' => 14, 'nama_sub_menu' => 'OBGYN MCU'],
            ['dashboard_menu_sub_id' => 283, 'dashboard_menu_id' => 14, 'nama_sub_menu' => 'Bedah MCU'],
            ['dashboard_menu_sub_id' => 284, 'dashboard_menu_id' => 14, 'nama_sub_menu' => 'Neurologi MCU'],
            ['dashboard_menu_sub_id' => 285, 'dashboard_menu_id' => 14, 'nama_sub_menu' => 'Gizi MCU'],
            ['dashboard_menu_sub_id' => 286, 'dashboard_menu_id' => 14, 'nama_sub_menu' => 'Urologi MCU'],
            ['dashboard_menu_sub_id' => 287, 'dashboard_menu_id' => 14, 'nama_sub_menu' => 'Psikologi MCU'],
            // ===== Fase P5 — ICU & Hemodialisa (menu 12) =====
            ['dashboard_menu_sub_id' => 213, 'dashboard_menu_id' => 12, 'nama_sub_menu' => 'Kriteria Masuk ICU'],
            ['dashboard_menu_sub_id' => 214, 'dashboard_menu_id' => 12, 'nama_sub_menu' => 'Kriteria Keluar ICU'],
            ['dashboard_menu_sub_id' => 215, 'dashboard_menu_id' => 12, 'nama_sub_menu' => 'Pre Hemodialisa'],
            ['dashboard_menu_sub_id' => 216, 'dashboard_menu_id' => 12, 'nama_sub_menu' => 'Intra Hemodialisa'],
            ['dashboard_menu_sub_id' => 217, 'dashboard_menu_id' => 12, 'nama_sub_menu' => 'Post Hemodialisa'],
            ['dashboard_menu_sub_id' => 218, 'dashboard_menu_id' => 12, 'nama_sub_menu' => 'Triage Hemodialisa'],
            // ===== Fase P5 — Bedah & Anestesi (menu 15) =====
            // Sub 297 (form 89 WHO), 305 (form 97 invasif), 308 (form 100
            // angiografi) TIDAK di-seed — form ⛔ menunggu keputusan klinis.
            ['dashboard_menu_sub_id' => 293, 'dashboard_menu_id' => 15, 'nama_sub_menu' => 'Asesmen Pra Bedah'],
            ['dashboard_menu_sub_id' => 294, 'dashboard_menu_id' => 15, 'nama_sub_menu' => 'Evaluasi Pre-Anestesi & Sedasi'],
            ['dashboard_menu_sub_id' => 295, 'dashboard_menu_id' => 15, 'nama_sub_menu' => 'Asesmen Post-Anestesi & Sedasi'],
            ['dashboard_menu_sub_id' => 296, 'dashboard_menu_id' => 15, 'nama_sub_menu' => 'Monitoring Anestesi & Sedasi'],
            ['dashboard_menu_sub_id' => 298, 'dashboard_menu_id' => 15, 'nama_sub_menu' => 'Check List Kesiapan Anestesi'],
            ['dashboard_menu_sub_id' => 299, 'dashboard_menu_id' => 15, 'nama_sub_menu' => 'Pengkajian Operasi'],
            ['dashboard_menu_sub_id' => 300, 'dashboard_menu_id' => 15, 'nama_sub_menu' => 'Laporan Operasi'],
            ['dashboard_menu_sub_id' => 301, 'dashboard_menu_id' => 15, 'nama_sub_menu' => 'Catatan Bedah'],
            ['dashboard_menu_sub_id' => 302, 'dashboard_menu_id' => 15, 'nama_sub_menu' => 'Transfer Pasien'],
            ['dashboard_menu_sub_id' => 303, 'dashboard_menu_id' => 15, 'nama_sub_menu' => 'Penandaan Lokasi Operasi'],
            ['dashboard_menu_sub_id' => 304, 'dashboard_menu_id' => 15, 'nama_sub_menu' => 'Penandaan Pasien'],
            ['dashboard_menu_sub_id' => 306, 'dashboard_menu_id' => 15, 'nama_sub_menu' => 'Surveilans Luka Insisi'],
            ['dashboard_menu_sub_id' => 307, 'dashboard_menu_id' => 15, 'nama_sub_menu' => 'Catatan Anestesia'],
            ['dashboard_menu_sub_id' => 309, 'dashboard_menu_id' => 15, 'nama_sub_menu' => 'Asesmen Katarak'],
            // ===== Fase P6 — Onkologi & Darah (menu 18) =====
            ['dashboard_menu_sub_id' => 353, 'dashboard_menu_id' => 18, 'nama_sub_menu' => 'Asesmen Kemoterapi'],
            ['dashboard_menu_sub_id' => 354, 'dashboard_menu_id' => 18, 'nama_sub_menu' => 'Pemantauan Kemoterapi'],
            ['dashboard_menu_sub_id' => 355, 'dashboard_menu_id' => 18, 'nama_sub_menu' => 'Asesmen Pemberian Darah'],
            // Sub 356 memakai ejaan "Assesmen" (dua s) supaya Str::slug() persis
            // sama dengan form.slug `assesmen_transfusi` (terkunci ALOKASI §7).
            // Nama satu-s ("Asesmen Transfusi") membuat DynamicFormController::
            // delegateToSpecificController() 404 saat store/update dari dashboard.
            ['dashboard_menu_sub_id' => 356, 'dashboard_menu_id' => 18, 'nama_sub_menu' => 'Assesmen Transfusi'],
            ['dashboard_menu_sub_id' => 357, 'dashboard_menu_id' => 18, 'nama_sub_menu' => 'Monitoring Transfusi'],
            ['dashboard_menu_sub_id' => 358, 'dashboard_menu_id' => 18, 'nama_sub_menu' => 'Double Check High Alert'],
            // ===== Fase P6 — Psikososial & Kerohanian (menu 16) =====
            // Sub 313/314 memakai ejaan "Assesmen" (dua s) supaya Str::slug()
            // persis sama dengan form.slug (terkunci ALOKASI §7) dan
            // Str::studly() cocok dengan folder controller AssesmenPsikologi /
            // AssesmenSpiritual. Nama satu-s ("Asesmen Psikologi") membuat
            // dashboard menampilkan halaman Unsupported: fallback view
            // `moduls.EMR.{slug}.index` tidak ada dan studly-nya salah folder.
            // Preseden: sub 10 produksi ("Assesmen Awal Medis Rawat Jalan",
            // dua s) & sub 356 Batch A.
            ['dashboard_menu_sub_id' => 313, 'dashboard_menu_id' => 16, 'nama_sub_menu' => 'Assesmen Psikologi'],
            ['dashboard_menu_sub_id' => 314, 'dashboard_menu_id' => 16, 'nama_sub_menu' => 'Assesmen Spiritual'],
            ['dashboard_menu_sub_id' => 315, 'dashboard_menu_id' => 16, 'nama_sub_menu' => 'Permintaan Kerohanian'],
            ['dashboard_menu_sub_id' => 316, 'dashboard_menu_id' => 16, 'nama_sub_menu' => 'Bukti Kerohanian'],
            // ===== Fase P6 — Surveilans Infeksi (menu 17, KONSEP_SURVEILANS_INFEKSI.md) =====
            // Sub 333 tanpa "/" (Str::slug membuang slash tanpa separator); sub 338
            // "Asesmen Restraint" satu s = form.slug `asesmen_restraint` (lihat §1 KONSEP).
            ['dashboard_menu_sub_id' => 333, 'dashboard_menu_id' => 17, 'nama_sub_menu' => 'Surveilans CVC PICC'],
            ['dashboard_menu_sub_id' => 334, 'dashboard_menu_id' => 17, 'nama_sub_menu' => 'Surveilans IV Catheter'],
            ['dashboard_menu_sub_id' => 335, 'dashboard_menu_id' => 17, 'nama_sub_menu' => 'Surveilans Kateter Urine'],
            ['dashboard_menu_sub_id' => 336, 'dashboard_menu_id' => 17, 'nama_sub_menu' => 'Surveilans Tirah Baring'],
            ['dashboard_menu_sub_id' => 337, 'dashboard_menu_id' => 17, 'nama_sub_menu' => 'Screening Akses'],
            ['dashboard_menu_sub_id' => 338, 'dashboard_menu_id' => 17, 'nama_sub_menu' => 'Asesmen Restraint'],
            ['dashboard_menu_sub_id' => 339, 'dashboard_menu_id' => 17, 'nama_sub_menu' => 'Observasi Restraint'],
            // ===== Fase P6 — Formulir & Rujukan (menu 5, KONSEP_FORMULIR_RUJUKAN.md) =====
            // Sub 236 sengaja TANPA "&" maupun kata penghubung — Str::slug()
            // membuang karakter tsb, bukan menggantinya (lihat §1 KONSEP).
            ['dashboard_menu_sub_id' => 233, 'dashboard_menu_id' => 5, 'nama_sub_menu' => 'Rekam Medis Rujukan'],
            ['dashboard_menu_sub_id' => 234, 'dashboard_menu_id' => 5, 'nama_sub_menu' => 'Surat Keterangan Sehat'],
            ['dashboard_menu_sub_id' => 235, 'dashboard_menu_id' => 5, 'nama_sub_menu' => 'Pesan Penunjang'],
            ['dashboard_menu_sub_id' => 236, 'dashboard_menu_id' => 5, 'nama_sub_menu' => 'SPRI Rujukan Balik'],
            // ===== Fase P6 — Katalog Form Blanko (form 129, KONSEP_KATALOG_FORM_BLANKO.md §7.1) =====
            ['dashboard_menu_sub_id' => 373, 'dashboard_menu_id' => 5, 'nama_sub_menu' => 'Katalog Form Blanko'],
        ];
        foreach ($subMenus as $subMenu) {
            DB::table('dashboard_menu_sub')->updateOrInsert(
                ['dashboard_menu_sub_id' => $subMenu['dashboard_menu_sub_id']],
                array_merge($subMenu, [
                    'input_time' => $now,
                    'input_user_id' => 1,
                    'status_batal' => 0,
                ])
            );
        }
        // Sub menu lama "Peresepan Obat" (3) ikut di-soft-delete bersama menu "Resep".
        DB::table('dashboard_menu_sub')
            ->where('dashboard_menu_sub_id', 3)
            ->update([
                'status_batal' => 1,
                'mod_time' => $now,
                'mod_user_id' => 1,
            ]);
        $extras = [
            // Sub Menu 2 "Pengkajian Keperawatan"
            ['dashboard_menu_sub_extra_id' => 1, 'dashboard_menu_sub_id' => 2, 'nama_sub_menu_extra' => 'Pengkajian Awal Keperawatan'],
            ['dashboard_menu_sub_extra_id' => 2, 'dashboard_menu_sub_id' => 2, 'nama_sub_menu_extra' => 'Pengkajian Harian Keperawatan'],
            ['dashboard_menu_sub_extra_id' => 3, 'dashboard_menu_sub_id' => 12, 'nama_sub_menu_extra' => 'Tanda Vital'],
            ['dashboard_menu_sub_extra_id' => 4, 'dashboard_menu_sub_id' => 12, 'nama_sub_menu_extra' => 'Bundle VAP'],
            ['dashboard_menu_sub_extra_id' => 5, 'dashboard_menu_sub_id' => 12, 'nama_sub_menu_extra' => 'Alat Invasif'],
            // Sub Menu 15 "Catatan Medis Visum" — extra untuk form 21 DAR.
            // `nama_sub_menu_extra` WAJIB persis 'DAR' (TANPA keterangan dalam
            // kurung): Str::slug('DAR','_') = 'dar' harus sama dengan form.slug
            // form 21. Kalau ditulis 'DAR (D-Rekognisi)' slug-nya jadi
            // 'dar_d_rekognisi' dan form 21 jadi yatim dari dashboard.
            ['dashboard_menu_sub_extra_id' => 6, 'dashboard_menu_sub_id' => 15, 'nama_sub_menu_extra' => 'DAR'],
            // ===== Sub-extra Fase P2 (slug form diambil dari NAMA EXTRA) =====
            ['dashboard_menu_sub_extra_id' => 26, 'dashboard_menu_sub_id' => 73, 'nama_sub_menu_extra' => 'Rencana Asuhan Keperawatan'],
            ['dashboard_menu_sub_extra_id' => 27, 'dashboard_menu_sub_id' => 73, 'nama_sub_menu_extra' => 'Issues Keperawatan'],
            ['dashboard_menu_sub_extra_id' => 28, 'dashboard_menu_sub_id' => 74, 'nama_sub_menu_extra' => 'Handover'],
            ['dashboard_menu_sub_extra_id' => 29, 'dashboard_menu_sub_id' => 75, 'nama_sub_menu_extra' => 'Asesmen Kebutuhan Edukasi'],
            ['dashboard_menu_sub_extra_id' => 30, 'dashboard_menu_sub_id' => 75, 'nama_sub_menu_extra' => 'Pengkajian Keperawatan Kritis'],
            ['dashboard_menu_sub_extra_id' => 31, 'dashboard_menu_sub_id' => 76, 'nama_sub_menu_extra' => 'Asesmen Status Fungsional'],
            ['dashboard_menu_sub_extra_id' => 32, 'dashboard_menu_sub_id' => 76, 'nama_sub_menu_extra' => 'Respon Emosi'],
            ['dashboard_menu_sub_extra_id' => 33, 'dashboard_menu_sub_id' => 76, 'nama_sub_menu_extra' => 'Asesmen Akhir Kehidupan'],
            ['dashboard_menu_sub_extra_id' => 34, 'dashboard_menu_sub_id' => 76, 'nama_sub_menu_extra' => 'Uji Fungsi Asesment'],
            ['dashboard_menu_sub_extra_id' => 35, 'dashboard_menu_sub_id' => 77, 'nama_sub_menu_extra' => 'Riwayat Penyakit'],
            ['dashboard_menu_sub_extra_id' => 36, 'dashboard_menu_sub_id' => 77, 'nama_sub_menu_extra' => 'Riwayat Keluarga'],
            ['dashboard_menu_sub_extra_id' => 37, 'dashboard_menu_sub_id' => 77, 'nama_sub_menu_extra' => 'Catatan Keperawatan Rajal'],
            ['dashboard_menu_sub_extra_id' => 46, 'dashboard_menu_sub_id' => 93, 'nama_sub_menu_extra' => 'Intake'],
            ['dashboard_menu_sub_extra_id' => 47, 'dashboard_menu_sub_id' => 93, 'nama_sub_menu_extra' => 'Output'],
            ['dashboard_menu_sub_extra_id' => 66, 'dashboard_menu_sub_id' => 113, 'nama_sub_menu_extra' => 'Penilaian Nyeri'],
            ['dashboard_menu_sub_extra_id' => 67, 'dashboard_menu_sub_id' => 113, 'nama_sub_menu_extra' => 'Metode Nyeri'],
            ['dashboard_menu_sub_extra_id' => 68, 'dashboard_menu_sub_id' => 113, 'nama_sub_menu_extra' => 'Skrining Nyeri Perina'],
            ['dashboard_menu_sub_extra_id' => 86, 'dashboard_menu_sub_id' => 133, 'nama_sub_menu_extra' => 'Monitoring Risiko Jatuh'],
            ['dashboard_menu_sub_extra_id' => 87, 'dashboard_menu_sub_id' => 134, 'nama_sub_menu_extra' => 'Penilaian Risiko Dekubitus'],
            ['dashboard_menu_sub_extra_id' => 88, 'dashboard_menu_sub_id' => 134, 'nama_sub_menu_extra' => 'Barthel Index'],
        ];
        foreach ($extras as $extra) {
            DB::table('dashboard_menu_sub_extra')->updateOrInsert(
                ['dashboard_menu_sub_extra_id' => $extra['dashboard_menu_sub_extra_id']],
                array_merge($extra, [
                    'input_time' => $now,
                    'input_user_id' => 1,
                    'status_batal' => 0,
                ])
            );
        }
        // ======== Form EMR ========
        // id_dash_menu = "menu.sub.extra" sesuai view header_ehr (concat_ws('.', ...)); null berarti tanpa dashboard.
        // slug = basename folder/file EMR (form_name di URL /emr/form/{form_name}/...).
        $forms = [
            ['form_id' => 1, 'nama_form' => 'Catatan Awal Medis', 'slug' => 'catatan_awal_medis', 'id_dash_menu' => null, 'ri' => 1, 'rj' => 1, 'igd' => 1, 'mcu' => 1],
            ['form_id' => 2, 'nama_form' => 'SOAP / CPPT', 'slug' => 'soap', 'id_dash_menu' => '1.1', 'ri' => 1, 'rj' => 1, 'igd' => 1, 'mcu' => 1],
            ['form_id' => 3, 'nama_form' => 'Pengkajian Awal Keperawatan', 'slug' => 'pengkajian_awal_keperawatan', 'id_dash_menu' => '2.2.1', 'ri' => 1, 'rj' => 1, 'igd' => 1, 'mcu' => 1],
            ['form_id' => 4, 'nama_form' => 'Pengkajian Harian Keperawatan', 'slug' => 'pengkajian_harian_keperawatan', 'id_dash_menu' => '2.2.2', 'ri' => 1, 'rj' => 1, 'igd' => 1, 'mcu' => 1],
            ['form_id' => 5, 'nama_form' => 'Order Resep', 'slug' => 'order_resep', 'id_dash_menu' => '4.4', 'ri' => 1, 'rj' => 1, 'igd' => 1, 'mcu' => 1],
            ['form_id' => 6, 'nama_form' => 'Order Laboratorium', 'slug' => 'laboratorium', 'id_dash_menu' => '4.5', 'ri' => 1, 'rj' => 1, 'igd' => 1, 'mcu' => 1],
            ['form_id' => 7, 'nama_form' => 'Order Radiologi', 'slug' => 'radiologi', 'id_dash_menu' => '4.6', 'ri' => 1, 'rj' => 1, 'igd' => 1, 'mcu' => 1],
            // Formulir Konsultasi (multi-guna: Rehabilitasi Medik / Konsul Layanan / Rencana Kontrol).
            ['form_id' => 8, 'nama_form' => 'Konsultasi', 'slug' => 'konsultasi', 'id_dash_menu' => '5.7', 'ri' => 1, 'rj' => 1, 'igd' => 1, 'mcu' => 0],
            // Implementasi Keperawatan: satu baris per pengisian — Implementasi (dari
            // Master Implementasi) + tanggal + jam + keterangan + respon (free text).
            ['form_id' => 9, 'nama_form' => 'Implementasi Keperawatan', 'slug' => 'implementasi_keperawatan', 'id_dash_menu' => '2.8', 'ri' => 1, 'rj' => 1, 'igd' => 1, 'mcu' => 1],
            // Tindakan Medis: permintaan tindakan atas persetujuan dokter + hasil/kondisi
            // pasca tindakan + pemakaian obat/BMHP. id_dash_menu "1.9".
            ['form_id' => 10, 'nama_form' => 'Tindakan Medis', 'slug' => 'tindakan_medis', 'id_dash_menu' => '1.9', 'ri' => 1, 'rj' => 1, 'igd' => 1, 'mcu' => 1],
            // Assesmen Awal Medis Rawat Jalan: keluhan utama + tanda vital +
            // diagnosa kerja. KHUSUS rawat jalan (rj=1, ri/igd/mcu=0).
            // id_dash_menu "1.10" (menu 1 "Catatan Medis", sub 10 tanpa extra).
            ['form_id' => 11, 'nama_form' => 'Assesmen Awal Medis Rawat Jalan', 'slug' => 'assesmen_awal_medis_rawat_jalan', 'id_dash_menu' => '1.10', 'ri' => 0, 'rj' => 1, 'igd' => 0, 'mcu' => 0],
            // SBAR (Situation-Background-Assessment-Recommendation): format
            // komunikasi terstruktur untuk serah terima antar shift / permintaan
            // konsultasi. Tersedia di semua jenis rawat. id_dash_menu "1.11".
            ['form_id' => 12, 'nama_form' => 'SBAR',                                 'slug' => 'sbar',                               'id_dash_menu' => '1.11',  'ri' => 1, 'rj' => 1, 'igd' => 1, 'mcu' => 1],
            ['form_id' => 13, 'nama_form' => 'Tanda Vital',                          'slug' => 'tanda_vital',                        'id_dash_menu' => '2.12.3', 'ri' => 1, 'rj' => 1, 'igd' => 1, 'mcu' => 1],
            ['form_id' => 14, 'nama_form' => 'Bundle VAP',                           'slug' => 'bundle_vap',                         'id_dash_menu' => '2.12.4', 'ri' => 1, 'rj' => 1, 'igd' => 1, 'mcu' => 1],
            ['form_id' => 15, 'nama_form' => 'Alat Invasif',                         'slug' => 'alat_invasif',                       'id_dash_menu' => '2.12.5', 'ri' => 1, 'rj' => 1, 'igd' => 1, 'mcu' => 1],
            // ===== Fase P1 — Catatan Medis Lanjutan =====
            // Resume Medis: ringkasan episode rawat inap. Tersedia di RI/RJ/IGD.
            // id_dash_menu "1.13" (menu 1 "Catatan Medis", sub 13 tanpa extra).
            ['form_id' => 16, 'nama_form' => 'Resume Medis', 'slug' => 'resume_medis', 'id_dash_menu' => '1.13', 'ri' => 1, 'rj' => 0, 'igd' => 0, 'mcu' => 0],
            // Care Plan: rencana asuhan medis + kriteria Discharge Planning.
            // id_dash_menu "1.14".
            ['form_id' => 20, 'nama_form' => 'Care Plan', 'slug' => 'care_plan', 'id_dash_menu' => '1.14', 'ri' => 1, 'rj' => 1, 'igd' => 1, 'mcu' => 0],
            // DAR: catatan kejadian forensik. 3 tingkat: sub 15 + extra 6.
            // id_dash_menu "1.15.6".
            ['form_id' => 21, 'nama_form' => 'DAR', 'slug' => 'dar', 'id_dash_menu' => '1.15.6', 'ri' => 1, 'rj' => 0, 'igd' => 1, 'mcu' => 0],
            // Catatan Medis Visum: KHUSUS IGD (dokter IGD yang membuat, dokumen
            // bermeterai). id_dash_menu "1.15" (sub 15 tanpa extra).
            ['form_id' => 57, 'nama_form' => 'Catatan Medis Visum', 'slug' => 'catatan_medis_visum', 'id_dash_menu' => '1.15', 'ri' => 0, 'rj' => 0, 'igd' => 1, 'mcu' => 0],
            // Discharge Planning: perencanaan pulang. id_dash_menu "6.33".
            ['form_id' => 17, 'nama_form' => 'Discharge Planning', 'slug' => 'discharge_planning', 'id_dash_menu' => '6.33', 'ri' => 1, 'rj' => 1, 'igd' => 1, 'mcu' => 0],
            // Pemulangan Pasien: ringkasan & instruksi saat dipulangkan.
            // id_dash_menu "6.34".
            ['form_id' => 18, 'nama_form' => 'Pemulangan Pasien', 'slug' => 'pemulangan_pasien', 'id_dash_menu' => '6.34', 'ri' => 1, 'rj' => 0, 'igd' => 0, 'mcu' => 0],
            // Triage IGD: KHUSUS IGD. id_dash_menu "7.53".
            ['form_id' => 19, 'nama_form' => 'Triage IGD', 'slug' => 'triage_igd', 'id_dash_menu' => '7.53', 'ri' => 0, 'rj' => 0, 'igd' => 1, 'mcu' => 0],
            // ===== Form Fase P2 =====
            ['form_id' => 22, 'nama_form' => 'Rencana Asuhan Keperawatan', 'slug' => 'rencana_asuhan_keperawatan', 'id_dash_menu' => '2.73.26', 'ri' => 1, 'rj' => 0, 'igd' => 0, 'mcu' => 0],
            ['form_id' => 23, 'nama_form' => 'Issues Keperawatan', 'slug' => 'issues_keperawatan', 'id_dash_menu' => '2.73.27', 'ri' => 1, 'rj' => 0, 'igd' => 0, 'mcu' => 0],
            ['form_id' => 24, 'nama_form' => 'Intake', 'slug' => 'intake', 'id_dash_menu' => '2.93.46', 'ri' => 1, 'rj' => 0, 'igd' => 0, 'mcu' => 0],
            ['form_id' => 25, 'nama_form' => 'Output', 'slug' => 'output', 'id_dash_menu' => '2.93.47', 'ri' => 1, 'rj' => 0, 'igd' => 0, 'mcu' => 0],
            ['form_id' => 26, 'nama_form' => 'Monitoring Cairan', 'slug' => 'monitoring_cairan', 'id_dash_menu' => '9.94', 'ri' => 1, 'rj' => 0, 'igd' => 0, 'mcu' => 0],
            ['form_id' => 27, 'nama_form' => 'Penilaian Nyeri', 'slug' => 'penilaian_nyeri', 'id_dash_menu' => '9.113.66', 'ri' => 1, 'rj' => 1, 'igd' => 1, 'mcu' => 0],
            ['form_id' => 28, 'nama_form' => 'Kesadaran & Pemberian O2', 'slug' => 'kesadaran_oksigen', 'id_dash_menu' => '9.95', 'ri' => 1, 'rj' => 0, 'igd' => 0, 'mcu' => 0],
            ['form_id' => 29, 'nama_form' => 'Handover / Serah Terima', 'slug' => 'handover', 'id_dash_menu' => '2.74.28', 'ri' => 1, 'rj' => 0, 'igd' => 0, 'mcu' => 0],
            ['form_id' => 30, 'nama_form' => 'Monitoring & Evaluasi Risiko Jatuh', 'slug' => 'monitoring_risiko_jatuh', 'id_dash_menu' => '8.133.86', 'ri' => 1, 'rj' => 0, 'igd' => 0, 'mcu' => 0],
            ['form_id' => 31, 'nama_form' => 'Asesmen Kebutuhan Edukasi', 'slug' => 'asesmen_kebutuhan_edukasi', 'id_dash_menu' => '2.75.29', 'ri' => 1, 'rj' => 1, 'igd' => 0, 'mcu' => 0],
            ['form_id' => 32, 'nama_form' => 'Penilaian Risiko Dekubitus (Norton)', 'slug' => 'penilaian_risiko_dekubitus', 'id_dash_menu' => '8.134.87', 'ri' => 1, 'rj' => 0, 'igd' => 0, 'mcu' => 0],
            ['form_id' => 33, 'nama_form' => 'Barthel Index', 'slug' => 'barthel_index', 'id_dash_menu' => '8.134.88', 'ri' => 1, 'rj' => 0, 'igd' => 0, 'mcu' => 0],
            ['form_id' => 34, 'nama_form' => 'Asesmen Status Fungsional', 'slug' => 'asesmen_status_fungsional', 'id_dash_menu' => '8.76.31', 'ri' => 1, 'rj' => 1, 'igd' => 0, 'mcu' => 0],
            ['form_id' => 35, 'nama_form' => 'Pengkajian Keperawatan Kritis', 'slug' => 'pengkajian_keperawatan_kritis', 'id_dash_menu' => '2.75.30', 'ri' => 1, 'rj' => 0, 'igd' => 1, 'mcu' => 0],
            ['form_id' => 36, 'nama_form' => 'Metode Nyeri', 'slug' => 'metode_nyeri', 'id_dash_menu' => '9.113.67', 'ri' => 1, 'rj' => 1, 'igd' => 1, 'mcu' => 0],
            ['form_id' => 37, 'nama_form' => 'Body Map Nyeri', 'slug' => 'body_map_nyeri', 'id_dash_menu' => '9.114', 'ri' => 1, 'rj' => 1, 'igd' => 1, 'mcu' => 0],
            ['form_id' => 38, 'nama_form' => 'Skrining Nyeri Perina', 'slug' => 'skrining_nyeri_perina', 'id_dash_menu' => '9.113.68', 'ri' => 1, 'rj' => 0, 'igd' => 0, 'mcu' => 0],
            // ===== Fase P3 — Gizi & Nutrisi (menu 10) =====
            ['form_id' => 39, 'nama_form' => 'Asesmen Gizi', 'slug' => 'asesmen_gizi', 'id_dash_menu' => '10.153', 'ri' => 1, 'rj' => 1, 'igd' => 1, 'mcu' => 0],
            ['form_id' => 40, 'nama_form' => 'Detail Nutrisi', 'slug' => 'detail_nutrisi', 'id_dash_menu' => '10.154', 'ri' => 1, 'rj' => 0, 'igd' => 0, 'mcu' => 0],
            // form 41 (Rekap Asesmen Nutrisi) BELUM di-seed: legacy tidak punya
            // tabel konversi ambang SGA sehingga `kesimpulan` tak bisa diisi
            // (lihat docs/TASK_FORM.md EMR-041, status ⛔).
            ['form_id' => 42, 'nama_form' => 'Pesan Makanan & Monitoring Asupan', 'slug' => 'pesan_makanan', 'id_dash_menu' => '10.156', 'ri' => 1, 'rj' => 0, 'igd' => 0, 'mcu' => 0],
            ['form_id' => 43, 'nama_form' => 'Diet Pasien', 'slug' => 'diet_pasien', 'id_dash_menu' => '10.157', 'ri' => 1, 'rj' => 1, 'igd' => 1, 'mcu' => 0],
            ['form_id' => 44, 'nama_form' => 'ADIME', 'slug' => 'adime', 'id_dash_menu' => '10.158', 'ri' => 1, 'rj' => 0, 'igd' => 0, 'mcu' => 0],
            // ===== Fase P3 — Farmasi (menu 11) =====
            ['form_id' => 45, 'nama_form' => 'Daftar Terapi Obat', 'slug' => 'daftar_terapi_obat', 'id_dash_menu' => '11.173', 'ri' => 1, 'rj' => 1, 'igd' => 1, 'mcu' => 0],
            ['form_id' => 46, 'nama_form' => 'Rekonsiliasi Obat', 'slug' => 'rekonsiliasi_obat', 'id_dash_menu' => '11.174', 'ri' => 1, 'rj' => 1, 'igd' => 1, 'mcu' => 0],
            ['form_id' => 47, 'nama_form' => 'Telaah Resep', 'slug' => 'telaah_resep', 'id_dash_menu' => '11.175', 'ri' => 0, 'rj' => 1, 'igd' => 1, 'mcu' => 0],
            ['form_id' => 48, 'nama_form' => 'Telaah Obat', 'slug' => 'telaah_obat', 'id_dash_menu' => '11.176', 'ri' => 1, 'rj' => 0, 'igd' => 0, 'mcu' => 0],
            // ===== Fase P3 — Order Penunjang Medis (menu 4 "Order") =====
            ['form_id' => 49, 'nama_form' => 'Order Patologi', 'slug' => 'order_patologi', 'id_dash_menu' => '4.193', 'ri' => 1, 'rj' => 1, 'igd' => 1, 'mcu' => 0],
            ['form_id' => 50, 'nama_form' => 'Order Mikrobiologi', 'slug' => 'order_mikrobiologi', 'id_dash_menu' => '4.194', 'ri' => 1, 'rj' => 1, 'igd' => 1, 'mcu' => 0],
            ['form_id' => 51, 'nama_form' => 'Order POCT', 'slug' => 'order_poct', 'id_dash_menu' => '4.195', 'ri' => 1, 'rj' => 1, 'igd' => 1, 'mcu' => 0],
            // ===== Fase P4 — Kebidanan & Neonatal (menu 13) =====
            ['form_id' => 59, 'nama_form' => 'Asesmen Khusus Kebidanan & Penyakit Kandungan', 'slug' => 'asesmen_kebidanan', 'id_dash_menu' => '13.253', 'ri' => 1, 'rj' => 1, 'igd' => 0, 'mcu' => 0],
            ['form_id' => 60, 'nama_form' => 'Pengkajian Awal Keperawatan Perina', 'slug' => 'pengkajian_perina', 'id_dash_menu' => '13.254', 'ri' => 1, 'rj' => 0, 'igd' => 0, 'mcu' => 0],
            ['form_id' => 61, 'nama_form' => 'Pengkajian Awal Keperawatan Non-Perina', 'slug' => 'pengkajian_non_perina', 'id_dash_menu' => '13.255', 'ri' => 1, 'rj' => 0, 'igd' => 0, 'mcu' => 0],
            ['form_id' => 62, 'nama_form' => 'Kala I-IV', 'slug' => 'kala_persalinan', 'id_dash_menu' => '13.256', 'ri' => 1, 'rj' => 0, 'igd' => 0, 'mcu' => 0],
            ['form_id' => 63, 'nama_form' => 'Riwayat Persalinan Sekarang', 'slug' => 'riwayat_persalinan', 'id_dash_menu' => '13.257', 'ri' => 1, 'rj' => 0, 'igd' => 0, 'mcu' => 0],
            ['form_id' => 64, 'nama_form' => 'Pelaksanaan Bayi Baru Lahir', 'slug' => 'bayi_baru_lahir', 'id_dash_menu' => '13.258', 'ri' => 1, 'rj' => 0, 'igd' => 0, 'mcu' => 0],
            ['form_id' => 65, 'nama_form' => 'Riwayat Resusitasi Bayi', 'slug' => 'riwayat_resusitasi_bayi', 'id_dash_menu' => '13.259', 'ri' => 1, 'rj' => 0, 'igd' => 0, 'mcu' => 0],
            ['form_id' => 66, 'nama_form' => 'Penilaian Bayi dengan APGAR Score', 'slug' => 'penilaian_bayi_dengan_apgar_score', 'id_dash_menu' => '13.260', 'ri' => 1, 'rj' => 0, 'igd' => 0, 'mcu' => 0],
            // form 67 (Down Score) BELUM di-seed: tabel interpretasi legacy bolong
            // (skor 6 tak tercakup) — lihat docs/TASK_FORM.md EMR-067, status ⛔.
            ['form_id' => 68, 'nama_form' => 'Inisiasi Menyusui Dini', 'slug' => 'inisiasi_menyusui_dini', 'id_dash_menu' => '13.262', 'ri' => 1, 'rj' => 0, 'igd' => 0, 'mcu' => 0],
            ['form_id' => 69, 'nama_form' => 'Skrining MDPK/MPP', 'slug' => 'skrining_mpp', 'id_dash_menu' => '13.263', 'ri' => 1, 'rj' => 1, 'igd' => 0, 'mcu' => 0],
            // ===== Fase P5 — ICU & Hemodialisa (menu 12) =====
            ['form_id' => 52, 'nama_form' => 'Kriteria Masuk ICU', 'slug' => 'kriteria_masuk_icu', 'id_dash_menu' => '12.213', 'ri' => 1, 'rj' => 0, 'igd' => 1, 'mcu' => 0],
            ['form_id' => 53, 'nama_form' => 'Kriteria Keluar ICU', 'slug' => 'kriteria_keluar_icu', 'id_dash_menu' => '12.214', 'ri' => 1, 'rj' => 0, 'igd' => 0, 'mcu' => 0],
            ['form_id' => 102, 'nama_form' => 'Pre Hemodialisa', 'slug' => 'pre_hemodialisa', 'id_dash_menu' => '12.215', 'ri' => 1, 'rj' => 0, 'igd' => 1, 'mcu' => 0],
            ['form_id' => 103, 'nama_form' => 'Intra Hemodialisa', 'slug' => 'intra_hemodialisa', 'id_dash_menu' => '12.216', 'ri' => 1, 'rj' => 0, 'igd' => 1, 'mcu' => 0],
            ['form_id' => 104, 'nama_form' => 'Post Hemodialisa', 'slug' => 'post_hemodialisa', 'id_dash_menu' => '12.217', 'ri' => 1, 'rj' => 0, 'igd' => 0, 'mcu' => 0],
            ['form_id' => 105, 'nama_form' => 'Triage Hemodialisa', 'slug' => 'triage_hemodialisa', 'id_dash_menu' => '12.218', 'ri' => 0, 'rj' => 1, 'igd' => 0, 'mcu' => 0],
            // ===== Fase P5 — Bedah & Anestesi (menu 15) =====
            // Form 89/97/100 ⛔ (hard-gate WHO/invasif/angiografi) TIDAK di-seed.
            ['form_id' => 85, 'nama_form' => 'Asesmen Pra Bedah', 'slug' => 'asesmen_pra_bedah', 'id_dash_menu' => '15.293', 'ri' => 1, 'rj' => 1, 'igd' => 1, 'mcu' => 0],
            ['form_id' => 86, 'nama_form' => 'Evaluasi Pre-Anestesi & Sedasi', 'slug' => 'evaluasi_pre_anestesi_sedasi', 'id_dash_menu' => '15.294', 'ri' => 1, 'rj' => 0, 'igd' => 1, 'mcu' => 0],
            ['form_id' => 87, 'nama_form' => 'Asesmen Post-Anestesi & Sedasi', 'slug' => 'asesmen_post_anestesi_sedasi', 'id_dash_menu' => '15.295', 'ri' => 1, 'rj' => 0, 'igd' => 0, 'mcu' => 0],
            ['form_id' => 88, 'nama_form' => 'Monitoring Anestesi & Sedasi', 'slug' => 'monitoring_anestesi_sedasi', 'id_dash_menu' => '15.296', 'ri' => 1, 'rj' => 0, 'igd' => 1, 'mcu' => 0],
            ['form_id' => 90, 'nama_form' => 'Check List Kesiapan Anestesi', 'slug' => 'checklist_kesiapan_anestesi', 'id_dash_menu' => '15.298', 'ri' => 1, 'rj' => 0, 'igd' => 1, 'mcu' => 0],
            ['form_id' => 91, 'nama_form' => 'Pengkajian Operasi', 'slug' => 'pengkajian_operasi', 'id_dash_menu' => '15.299', 'ri' => 1, 'rj' => 0, 'igd' => 1, 'mcu' => 0],
            ['form_id' => 92, 'nama_form' => 'Laporan Operasi', 'slug' => 'laporan_operasi', 'id_dash_menu' => '15.300', 'ri' => 1, 'rj' => 0, 'igd' => 0, 'mcu' => 0],
            ['form_id' => 93, 'nama_form' => 'Catatan Bedah', 'slug' => 'catatan_bedah', 'id_dash_menu' => '15.301', 'ri' => 1, 'rj' => 0, 'igd' => 0, 'mcu' => 0],
            ['form_id' => 94, 'nama_form' => 'Transfer Pasien Antar Ruangan', 'slug' => 'transfer_pasien', 'id_dash_menu' => '15.302', 'ri' => 1, 'rj' => 0, 'igd' => 1, 'mcu' => 0],
            ['form_id' => 95, 'nama_form' => 'Penandaan Lokasi Operasi', 'slug' => 'penandaan_lokasi_operasi', 'id_dash_menu' => '15.303', 'ri' => 1, 'rj' => 0, 'igd' => 1, 'mcu' => 0],
            ['form_id' => 96, 'nama_form' => 'Penandaan Pasien', 'slug' => 'penandaan_pasien', 'id_dash_menu' => '15.304', 'ri' => 1, 'rj' => 0, 'igd' => 1, 'mcu' => 0],
            ['form_id' => 98, 'nama_form' => 'Surveilans Luka Insisi', 'slug' => 'surveilans_luka_insisi', 'id_dash_menu' => '15.306', 'ri' => 1, 'rj' => 0, 'igd' => 0, 'mcu' => 0],
            ['form_id' => 99, 'nama_form' => 'Catatan Anestesia', 'slug' => 'catatan_anestesia', 'id_dash_menu' => '15.307', 'ri' => 1, 'rj' => 0, 'igd' => 1, 'mcu' => 0],
            ['form_id' => 101, 'nama_form' => 'Asesmen Katarak', 'slug' => 'asesmen_katarak', 'id_dash_menu' => '15.309', 'ri' => 0, 'rj' => 1, 'igd' => 0, 'mcu' => 0],
            // ===== Fase P6 — Onkologi & Darah (menu 18) =====
            ['form_id' => 106, 'nama_form' => 'Asesmen Kemoterapi', 'slug' => 'asesmen_kemoterapi', 'id_dash_menu' => '18.353', 'ri' => 1, 'rj' => 1, 'igd' => 0, 'mcu' => 0],
            ['form_id' => 107, 'nama_form' => 'Pemantauan Kemoterapi', 'slug' => 'pemantauan_kemoterapi', 'id_dash_menu' => '18.354', 'ri' => 1, 'rj' => 0, 'igd' => 0, 'mcu' => 0],
            ['form_id' => 108, 'nama_form' => 'Asesmen Pasien dengan Darah', 'slug' => 'asesmen_pemberian_darah', 'id_dash_menu' => '18.355', 'ri' => 1, 'rj' => 1, 'igd' => 1, 'mcu' => 0],
            ['form_id' => 109, 'nama_form' => 'Assesmen Transfusi', 'slug' => 'assesmen_transfusi', 'id_dash_menu' => '18.356', 'ri' => 1, 'rj' => 0, 'igd' => 0, 'mcu' => 0],
            ['form_id' => 110, 'nama_form' => 'Monitoring Transfusi Darah', 'slug' => 'monitoring_transfusi', 'id_dash_menu' => '18.357', 'ri' => 1, 'rj' => 0, 'igd' => 0, 'mcu' => 0],
            ['form_id' => 111, 'nama_form' => 'Double Check Obat High Alert', 'slug' => 'double_check_high_alert', 'id_dash_menu' => '18.358', 'ri' => 1, 'rj' => 0, 'igd' => 1, 'mcu' => 0],
            // ===== Fase P6 — Psikososial & Kerohanian (menu 16) =====
            ['form_id' => 112, 'nama_form' => 'Asesmen Psikologi Pasien & Keluarga', 'slug' => 'assesmen_psikologi', 'id_dash_menu' => '16.313', 'ri' => 1, 'rj' => 1, 'igd' => 1, 'mcu' => 0],
            ['form_id' => 113, 'nama_form' => 'Asesmen Spiritual', 'slug' => 'assesmen_spiritual', 'id_dash_menu' => '16.314', 'ri' => 1, 'rj' => 1, 'igd' => 1, 'mcu' => 0],
            ['form_id' => 114, 'nama_form' => 'Permintaan Pelayanan Kerohanian', 'slug' => 'permintaan_kerohanian', 'id_dash_menu' => '16.315', 'ri' => 1, 'rj' => 1, 'igd' => 1, 'mcu' => 0],
            ['form_id' => 115, 'nama_form' => 'Bukti Pelayanan Kerohanian', 'slug' => 'bukti_kerohanian', 'id_dash_menu' => '16.316', 'ri' => 1, 'rj' => 1, 'igd' => 1, 'mcu' => 0],
            // ===== Fase P6 — Surveilans Infeksi & Restraint (116–122, KONSEP_SURVEILANS_INFEKSI.md) =====
            ['form_id' => 116, 'nama_form' => 'Surveilans Pasien Terpasang CVC/PICC', 'slug' => 'surveilans_cvc_picc', 'id_dash_menu' => '17.333', 'ri' => 1, 'rj' => 0, 'igd' => 1, 'mcu' => 0],
            ['form_id' => 117, 'nama_form' => 'Surveilans Pasien Terpasang IV Catheter Perifer', 'slug' => 'surveilans_iv_catheter', 'id_dash_menu' => '17.334', 'ri' => 1, 'rj' => 0, 'igd' => 1, 'mcu' => 0],
            ['form_id' => 118, 'nama_form' => 'Surveilans Pasien Terpasang Kateter Urine', 'slug' => 'surveilans_kateter_urine', 'id_dash_menu' => '17.335', 'ri' => 1, 'rj' => 0, 'igd' => 1, 'mcu' => 0],
            ['form_id' => 119, 'nama_form' => 'Surveilans Pasien Tirah Baring Total', 'slug' => 'surveilans_tirah_baring', 'id_dash_menu' => '17.336', 'ri' => 1, 'rj' => 0, 'igd' => 0, 'mcu' => 0],
            ['form_id' => 120, 'nama_form' => 'Screening Akses Vaskular', 'slug' => 'screening_akses', 'id_dash_menu' => '17.337', 'ri' => 1, 'rj' => 0, 'igd' => 1, 'mcu' => 0],
            ['form_id' => 121, 'nama_form' => 'Asesmen Pasien Restraint', 'slug' => 'asesmen_restraint', 'id_dash_menu' => '17.338', 'ri' => 1, 'rj' => 0, 'igd' => 0, 'mcu' => 0],
            ['form_id' => 122, 'nama_form' => 'Observasi Restraint', 'slug' => 'observasi_restraint', 'id_dash_menu' => '17.339', 'ri' => 1, 'rj' => 0, 'igd' => 0, 'mcu' => 0],
            // ===== Fase P6 — Formulir & Rujukan (58 & 126–128, KONSEP_FORMULIR_RUJUKAN.md) =====
            ['form_id' => 58, 'nama_form' => 'Rekam Medis Rujukan', 'slug' => 'rekam_medis_rujukan', 'id_dash_menu' => '5.233', 'ri' => 1, 'rj' => 1, 'igd' => 1, 'mcu' => 0],
            ['form_id' => 126, 'nama_form' => 'Surat Keterangan Sehat', 'slug' => 'surat_keterangan_sehat', 'id_dash_menu' => '5.234', 'ri' => 0, 'rj' => 1, 'igd' => 0, 'mcu' => 0],
            ['form_id' => 127, 'nama_form' => 'Pesan Bedah / Cathlab / Endoscopy / ESWL', 'slug' => 'pesan_penunjang', 'id_dash_menu' => '5.235', 'ri' => 1, 'rj' => 1, 'igd' => 1, 'mcu' => 0],
            // nama_form pakai "&" (ALOKASI §6) — slug tetap dari sub 236 `SPRI Rujukan Balik`.
            ['form_id' => 128, 'nama_form' => 'SPRI & Program Rujukan Balik', 'slug' => 'spri_rujukan_balik', 'id_dash_menu' => '5.236', 'ri' => 1, 'rj' => 1, 'igd' => 0, 'mcu' => 0],
            // ===== Fase P6 — Katalog Form Blanko (129) — TANPA objek & TANPA mapping =====
            ['form_id' => 129, 'nama_form' => 'Katalog Form Blanko', 'slug' => 'katalog_form_blanko', 'id_dash_menu' => '5.373', 'ri' => 1, 'rj' => 1, 'igd' => 1, 'mcu' => 1],
            // ===== Fase P4 — Medical Check Up (menu 14) =====
            ['form_id' => 70, 'nama_form' => 'Kesimpulan MCU', 'slug' => 'kesimpulan_mcu', 'id_dash_menu' => '14.273', 'ri' => 0, 'rj' => 0, 'igd' => 0, 'mcu' => 1],
            ['form_id' => 71, 'nama_form' => 'Catatan Keperawatan / TTV MCU', 'slug' => 'ttv_mcu', 'id_dash_menu' => '14.274', 'ri' => 0, 'rj' => 0, 'igd' => 0, 'mcu' => 1],
            ['form_id' => 72, 'nama_form' => 'Visus Mata MCU', 'slug' => 'visus_mata_mcu', 'id_dash_menu' => '14.275', 'ri' => 0, 'rj' => 0, 'igd' => 0, 'mcu' => 1],
            ['form_id' => 73, 'nama_form' => 'Obstetri MCU', 'slug' => 'obstetri_mcu', 'id_dash_menu' => '14.276', 'ri' => 0, 'rj' => 0, 'igd' => 0, 'mcu' => 1],
            ['form_id' => 74, 'nama_form' => 'Penyakit Dalam MCU', 'slug' => 'penyakit_dalam_mcu', 'id_dash_menu' => '14.277', 'ri' => 0, 'rj' => 0, 'igd' => 0, 'mcu' => 1],
            ['form_id' => 75, 'nama_form' => 'Jantung MCU', 'slug' => 'jantung_mcu', 'id_dash_menu' => '14.278', 'ri' => 0, 'rj' => 0, 'igd' => 0, 'mcu' => 1],
            ['form_id' => 76, 'nama_form' => 'THT MCU', 'slug' => 'tht_mcu', 'id_dash_menu' => '14.279', 'ri' => 0, 'rj' => 0, 'igd' => 0, 'mcu' => 1],
            ['form_id' => 77, 'nama_form' => 'Mata MCU', 'slug' => 'mata_mcu', 'id_dash_menu' => '14.280', 'ri' => 0, 'rj' => 0, 'igd' => 0, 'mcu' => 1],
            ['form_id' => 78, 'nama_form' => 'Gigi MCU', 'slug' => 'gigi_mcu', 'id_dash_menu' => '14.281', 'ri' => 0, 'rj' => 0, 'igd' => 0, 'mcu' => 1],
            ['form_id' => 79, 'nama_form' => 'OBGYN MCU', 'slug' => 'obgyn_mcu', 'id_dash_menu' => '14.282', 'ri' => 0, 'rj' => 0, 'igd' => 0, 'mcu' => 1],
            ['form_id' => 80, 'nama_form' => 'Bedah MCU', 'slug' => 'bedah_mcu', 'id_dash_menu' => '14.283', 'ri' => 0, 'rj' => 0, 'igd' => 0, 'mcu' => 1],
            ['form_id' => 81, 'nama_form' => 'Neurologi MCU', 'slug' => 'neurologi_mcu', 'id_dash_menu' => '14.284', 'ri' => 0, 'rj' => 0, 'igd' => 0, 'mcu' => 1],
            ['form_id' => 82, 'nama_form' => 'Gizi MCU', 'slug' => 'gizi_mcu', 'id_dash_menu' => '14.285', 'ri' => 0, 'rj' => 0, 'igd' => 0, 'mcu' => 1],
            ['form_id' => 83, 'nama_form' => 'Urologi MCU', 'slug' => 'urologi_mcu', 'id_dash_menu' => '14.286', 'ri' => 0, 'rj' => 0, 'igd' => 0, 'mcu' => 1],
            ['form_id' => 84, 'nama_form' => 'Psikologi MCU', 'slug' => 'psikologi_mcu', 'id_dash_menu' => '14.287', 'ri' => 0, 'rj' => 0, 'igd' => 0, 'mcu' => 1],
            ['form_id' => 54, 'nama_form' => 'Catatan Keperawatan Rawat Jalan', 'slug' => 'catatan_keperawatan_rajal', 'id_dash_menu' => '8.77.37', 'ri' => 0, 'rj' => 1, 'igd' => 0, 'mcu' => 0],
            ['form_id' => 55, 'nama_form' => 'Riwayat Penyakit', 'slug' => 'riwayat_penyakit', 'id_dash_menu' => '8.77.35', 'ri' => 1, 'rj' => 1, 'igd' => 1, 'mcu' => 0],
            ['form_id' => 56, 'nama_form' => 'Riwayat Keluarga', 'slug' => 'riwayat_keluarga', 'id_dash_menu' => '8.77.36', 'ri' => 1, 'rj' => 1, 'igd' => 1, 'mcu' => 0],
            ['form_id' => 123, 'nama_form' => 'Asesmen Akhir Kehidupan', 'slug' => 'asesmen_akhir_kehidupan', 'id_dash_menu' => '8.76.33', 'ri' => 1, 'rj' => 0, 'igd' => 1, 'mcu' => 0],
            ['form_id' => 124, 'nama_form' => 'Uji Fungsi & Asesment', 'slug' => 'uji_fungsi_asesment', 'id_dash_menu' => '8.76.34', 'ri' => 0, 'rj' => 1, 'igd' => 0, 'mcu' => 1],
            ['form_id' => 125, 'nama_form' => 'Respon Emosi & Status Mental', 'slug' => 'respon_emosi', 'id_dash_menu' => '8.76.32', 'ri' => 1, 'rj' => 1, 'igd' => 1, 'mcu' => 0],
        ];
        foreach ($forms as $form) {
            DB::table('form')->updateOrInsert(
                ['form_id' => $form['form_id']],
                array_merge($form, [
                    'input_time' => $now,
                    'input_user_id' => 1,
                    'status_batal' => 0,
                ])
            );
        }
        // ======== Objek Master ========
        // Basis data objek mengikuti penomoran OBJEK_ID_* di .env.example lama (1-68).
        $objeks = [
            1 => 'Subjective (S)', 2 => 'Objective (O)', 3 => 'Assessment (A)', 4 => 'Planning (P)', 5 => 'Instruksi (I)',
            6 => 'Tekanan Darah Sistolik', 7 => 'Tekanan Darah Diastolik', 8 => 'Berat Badan', 9 => 'Tinggi Badan', 10 => 'Nadi',
            11 => 'Suhu', 12 => 'Pernapasan', 13 => 'Keluhan Utama', 14 => 'Nyeri', 15 => 'Saturasi Oksigen',
            16 => 'EWS', 17 => 'Alergi', 18 => 'Pemberian Oksigen', 19 => 'Cara Pemberian Oksigen', 20 => 'ETT',
            21 => 'Agama', 22 => 'Kegiatan Ibadah / Budaya', 23 => 'Tingkat Pendidikan', 24 => 'Pekerjaan', 25 => 'Suku Bangsa',
            26 => 'Kebangsaan', 27 => 'Aktifitas Sebelum Makan', 28 => 'Pantangan Pulang', 29 => 'Pantangan Transfusi Darah', 30 => 'Pantangan Makan',
            31 => 'Nama Pasangan', 32 => 'Usia Pasangan', 33 => 'Pendidikan Pasangan', 34 => 'Pekerjaan Pasangan', 35 => 'Suku Bangsa Pasangan',
            36 => 'Kebangsaan Pasangan', 37 => 'Tinggal Bersama', 38 => 'Penanggung Jawab Pasien', 39 => 'Hubungan Pasien', 40 => 'Diagnosa Medis',
            41 => 'Riwayat Penyakit Sebelumnya', 42 => 'Riwayat Penyakit Sekarang', 43 => 'Infeksius (Flag)', 44 => 'Menular Melalui', 45 => 'Infeksius Memerlukan Isolasi',
            46 => 'Infeksius Hasil Penunjang', 47 => 'Imunologi (Flag)', 48 => 'Imunologi Memerlukan Isolasi', 49 => 'Imunologi Pembatasan Pengunjung', 50 => 'Imunologi Hasil Penunjang',
            51 => 'Kesadaran', 52 => 'Riwayat Kemoterapi', 53 => 'Riwayat Radioterapi', 54 => 'GCS Eye', 55 => 'GCS Motorik',
            56 => 'GCS Verbal', 57 => 'GCS Score', 58 => 'BMI', 59 => 'DPO', 60 => 'Nomor Handphone',
            61 => 'Riwayat Operasi Kemo', 62 => 'Vaksin COVID', 63 => 'Alloanamnesa', 64 => 'Nama Alloanamnesa', 65 => 'Hubungan Alloanamnesa',
            66 => 'UP GO 1a', 67 => 'UP GO 1b', 68 => 'UP GO 2',
            69 => 'Obat', 70 => 'Jumlah', 71 => 'S 1', 72 => 'S 2', 73 => 'Aturan Pakai', 74 => 'Rute Pemberian',
            75 => 'Tindakan', 76 => 'Prioritas', 77 => 'Keterangan', 78 => 'Hasil',
            79 => 'Nilai Normal', 80 => 'Satuan Hasil', 81 => 'Flag Abnormal', 82 => 'Petugas Pelaksana',
            83 => 'Jenis Konsultasi', 84 => 'Bagian Tujuan Konsultasi',
            85 => 'Dokter Tujuan Konsultasi', 86 => 'Tanggal Kontrol',
            87 => 'Indikasi Konsultasi', 88 => 'Bagian Rehabilitasi Medik',
            // Pengkajian risiko jatuh (lihat App\Helpers\RisikoJatuhHelper).
            // Instrumen dipilih otomatis dari usia: HDS anak, MFS dewasa, TUG untuk pasien lanjut usia.
            89 => 'Risiko Jatuh - Instrumen', 90 => 'Risiko Jatuh - Skor',
            91 => 'Risiko Jatuh - Tingkat Risiko', 92 => 'Risiko Jatuh - Intervensi',
            93 => 'Risiko Jatuh - Ringkasan',
            // HDS (Humpty Dumpty) item 1-7
            94 => 'HDS 1 - Sangat Hiperaktif', 95 => 'HDS 2 - Sering Jatuh',
            96 => 'HDS 3 - Menggunakan Alat Bantu', 97 => 'HDS 4 - Hambatan Komunikasi',
            98 => 'HDS 5 - Pusing Saat Berpindah', 99 => 'HDS 6 - Riwayat Fraktur',
            100 => 'HDS 7 - Pernah di-Restraint',
            // MFS (Morse Fall Scale) item 1-6
            101 => 'MFS 1 - Riwayat Jatuh', 102 => 'MFS 2 - Diagnosa Sekunder',
            103 => 'MFS 3 - Alat Bantu Ambulasi', 104 => 'MFS 4 - Terpasang Infus',
            105 => 'MFS 5 - Gait dan Transfer', 106 => 'MFS 6 - Status Mental',
            // Sydney Scoring: Transfer Score + Mobility Score
            107 => 'Sydney TS - Tempat Tidur', 108 => 'Sydney TS - Kursi Roda',
            109 => 'Sydney TS - Bantuan Orang', 110 => 'Sydney MS - Bantuan Orang',
            111 => 'Sydney MS - Kursi Roda', 112 => 'Sydney MS - Imobil',
            // Timed Up and Go
            113 => 'Durasi Timed Up and Go',
            // Pengkajian harian keperawatan
            114 => 'Keluhan Utama Harian', 115 => 'Catatan Keperawatan',
            116 => 'Eliminasi (Bj)', 117 => 'Intake Cairan (ml)',
            118 => 'Intake Makanan (persen)', 119 => 'Tidur (jam)', 120 => 'Catatan Tambahan',
            // Implementasi Keperawatan (form 9). `nama_implementasi` disimpan sebagai
            // snapshot supaya riwayat tetap terbaca bila master di-rename atau
            // di-soft-delete (pola sama dengan order_laboratorium_detail.nama_tindakan).
            121 => 'Implementasi', 122 => 'Nama Implementasi',
            123 => 'Tanggal Implementasi', 124 => 'Waktu Implementasi',
            125 => 'Keterangan Implementasi', 126 => 'Respon Implementasi',
            // Tindakan Medis (form 10)
            127 => 'Dokter Penyetuju', 128 => 'Jenis Permintaan',
            129 => 'Tanggal Tindakan', 130 => 'Waktu Tindakan',
            131 => 'Hasil/Kondisi Pasca Tindakan', 132 => 'Pemakaian Obat/BMHP',
            133 => 'Jenis Barang', 134 => 'Nomor Batch',
            // Assesmen Awal Medis Rawat Jalan (form 11). Sisanya (keluhan
            // utama, seluruh tanda vital, kesadaran, nyeri, BMI, keterangan)
            // REUSE objek yang sudah ada.
            135 => 'Diagnosa Kerja', 136 => 'Tujuan Kunjungan',
            137 => 'Tanggal Assesmen', 138 => 'Waktu Assesmen',
            139 => 'Dokter Pemeriksa', 140 => 'Skor Nyeri',
            // Assesmen Awal Medis Rawat Jalan (form 11) — pelengkap.
            141 => 'Anamnesis', 142 => 'Total Skor EWS',
            143 => 'Kategori Risiko EWS',
            144 => 'Parameter EWS Tidak Diukur',
            // SBAR (form 12). `alergi` & `keterangan` reuse objek 17 & 77.
            145 => 'Tanggal SBAR', 146 => 'Waktu SBAR',
            147 => 'Shift', 148 => 'Urgensi SBAR',
            149 => 'Cara Komunikasi', 150 => 'Penerima Informasi',
            151 => 'S - Situation', 152 => 'B - Background',
            153 => 'A - Assessment', 154 => 'R - Recommendation',
            // Tanda Vital / Observasi Harian (form 13). Sisanya (seluruh tanda
            // vital, GCS, kesadaran, oksigen, EWS, nyeri) REUSE objek yang
            // sudah ada.
            155 => 'Tanggal Observasi', 156 => 'Waktu Observasi',
            157 => 'Flow Rate Oksigen',
            // Bundle Pencegahan VAP (form 14): 10 butir bundle (vap_1..vap_10)
            // + 3 field ringkasan kepatuhan.
            158 => 'Bundle VAP 1 - Elevasi Kepala 30-45 Derajat',
            159 => 'Bundle VAP 2 - Sedasi Minimal / SAT-SBT',
            160 => 'Bundle VAP 3 - Oral Care Chlorhexidine',
            161 => 'Bundle VAP 4 - Profilaksis Tukak Lambung',
            162 => 'Bundle VAP 5 - Profilaksis DVT',
            163 => 'Bundle VAP 6 - Suction Subglottik',
            164 => 'Bundle VAP 7 - Hand Hygiene',
            165 => 'Bundle VAP 8 - Tekanan Cuff',
            166 => 'Bundle VAP 9 - Ganti Circuit Ventilator',
            167 => 'Bundle VAP 10 - Evaluasi Weaning',
            168 => 'Skor Kepatuhan Bundle VAP',
            169 => 'Persen Kepatuhan Bundle VAP',
            170 => 'Kategori Kepatuhan Bundle VAP',
            // Alat Invasif (form 15). Sengaja objek BARU semua — bukan reuse
            // objek 155/156 ("Tanggal/Waktu Observasi") yang maknanya berbeda
            // dan akan membingungkan saat laporan antar form.
            171 => 'Tanggal Pemasangan Alat Invasif', 172 => 'Jam Pemasangan Alat Invasif',
            173 => 'Alat Invasif', 174 => 'Lokasi Pemasangan Alat Invasif',
            175 => 'Tanggal Lepas Alat Invasif', 176 => 'Jam Lepas Alat Invasif',
            177 => 'Lama Pemasangan Alat Invasif (Hari)',
            // ===== Objek 178-199: Fase P1 (Resume Medis, Care Plan, DAR, Visum) =====
            // ID dikunci di docs/ALOKASI_ID_GLOBAL.md §1 — jangan diubah/digeser.
            // --- Form 16 Resume Medis ---
            178 => 'Tanggal Resume Medis', 179 => 'Waktu Resume Medis',
            180 => 'Indikasi Rawat Inap', 181 => 'Diagnosa Primer Resume',
            182 => 'Diagnosa Sekunder Resume', 183 => 'Prosedur yang Dilakukan',
            184 => 'Komplikasi Penyakit', 185 => 'Kondisi Saat Pulang',
            186 => 'Dirujuk Ke', 187 => 'Meninggal',
            188 => 'Pemeriksaan Fisik', // 12 organ -> 12 variabel bersuffix pf_*
            // --- Form 20 Care Plan ---
            189 => 'Tempat Pertemuan', 190 => 'Intervensi Non Farmakologis',
            191 => 'Intervensi Farmakologis', 192 => 'Perkiraan Lama Rawat (Hari)',
            193 => 'Target Perawatan', 194 => 'Kriteria Pemulangan Pasien',
            195 => 'Tanggal Evaluasi Care Plan',
            // --- Form 21 DAR ---
            196 => 'Tanggal DAR',
            // --- Form 57 Catatan Medis Visum ---
            197 => 'Jenis Visum', 198 => 'Nomor Visum Et Repertum',
            199 => 'Permintaan Visum',
            // ===== Objek 200-224: Fase P1 Discharge Planning & Pemulangan =====
            200 => 'Tanggal Masuk RS', 201 => 'Estimasi / Rencana Hari Rawat',
            202 => 'Rencana Tanggal Pemulangan', 203 => 'Pengaruh Perubahan Kondisi',
            204 => 'Antisipasi Masalah', 205 => 'Kebutuhan Bantuan Sehari-hari',
            206 => 'Tinggal & Bantuan Pelayan', 207 => 'Menggunakan Alat Medis di Rumah',
            208 => 'Perlu Alat Bantu', 209 => 'Perlu Perawatan Khusus',
            210 => 'Masalah Kebutuhan Pribadi', 211 => 'Nyeri Kronis',
            212 => 'Perlu Edukasi', 213 => 'Keterampilan Khusus',
            214 => 'Kebutuhan Edukasi',
            215 => 'Kondisi Saat Pulang', 216 => 'Meninggal',
            217 => 'Diet / Nutrisi', 218 => 'Eliminasi BAB & BAK',
            219 => 'Luka / Operasi', 220 => 'Transfer & Mobilisasi',
            221 => 'Edukasi / Penyuluhan yang Sudah Diberikan', 222 => 'Manajemen Nyeri',
            223 => 'Barang & Hasil yang Disyerahkan',
            224 => 'Masalah Keperawatan / Kebidanan Selama Dirawat',
            // ===== Objek 225-235: Fase P1 Triage IGD =====
            225 => 'Keadaan Umum', 226 => 'Jalan Nafas', 227 => 'Sirkulasi',
            228 => 'Akral', 229 => 'Pupil', 230 => 'Refleks Cahaya',
            231 => 'Jenis Anamnesis', 232 => 'Lokasi Nyeri',
            233 => 'Frekuensi Nyeri', 234 => 'Karakteristik Nyeri',
            235 => 'Prioritas Triase IGD',
            // ===== Objek 236-361 — Fase P2 (Asuhan Keperawatan, Cairan, Nyeri, Skala) =====
            236 => 'Diagnosa Keperawatan', 237 => 'Nama Diagnosa Keperawatan', 238 => 'Tujuan Keperawatan', 239 => 'Satuan Waktu Tujuan',
            240 => 'Kriteria Hasil Keperawatan', 241 => 'Intervensi Keperawatan', 242 => 'Masalah Keperawatan', 243 => 'Faktor Berhubungan Dengan',
            244 => 'Tanggal Serah Terima', 245 => 'Waktu Serah Terima', 246 => 'Petugas Serah Terima', 247 => 'Nama Petugas Serah Terima',
            248 => 'Petugas Terima', 249 => 'Nama Petugas Terima', 250 => 'Ruang Tujuan Serah Terima', 251 => 'Waktu Konfirmasi Penerima',
            252 => 'Jumlah Pasien Diserahterimakan', 253 => 'Status Kelengkapan Serah Terima', 254 => 'Kemampuan Bicara', 255 => 'Jenis Bahasa',
            256 => 'Perlu Penerjemah', 257 => 'Nilai Kebudayaan', 258 => 'Keterangan Kebudayaan', 259 => 'Hambatan Belajar',
            260 => 'Kemampuan Baca', 261 => 'Kebutuhan Belajar', 262 => 'Fungsional Makan (Feeding)', 263 => 'Fungsional Mandi (Bathing)',
            264 => 'Fungsional Perawatan Diri (Grooming)', 265 => 'Fungsional Berpakaian', 266 => 'Fungsional Buang Air Kecil', 267 => 'Fungsional Buang Air Besar',
            268 => 'Fungsional Penggunaan Toilet', 269 => 'Fungsional Transfer', 270 => 'Fungsional Mobilitas', 271 => 'Fungsional Naik Turun Tangga',
            272 => 'Skor Status Fungsional', 273 => 'Interpretasi Status Fungsional', 274 => 'Asal Masuk Pasien', 275 => 'Rincian Asal Masuk',
            276 => 'Jalan Nafas', 277 => 'Deviasi Trakea', 278 => 'Suara Napas Terdengar', 279 => 'Sirkulasi dan Perfusi',
            280 => 'Nilai Kritis', 281 => 'Riwayat Perawatan Berulang', 282 => 'Skor Nyeri Kritis', 283 => 'Riwayat Penyakit Keluarga',
            284 => 'Hubungan Keluarga', 285 => 'Waktu Asesmen Awal Kehidupan', 286 => 'Waktu Asesmen Akhir Kehidupan', 287 => 'Respon Emosi',
            288 => 'Status Mental', 289 => 'Kebutuhan Perlindungan', 290 => 'Kebutuhan Teridentifikasi', 291 => '`total_intake > kebutuhan_cairan` ( Indonesian: intake melebihi kebutuhan )',
            292 => 'Nama Cairan Infus', 293 => 'Volume Masukkan Infus (cc)', 294 => '`sisa_infus < 0` (infus terlampaui)', 295 => 'Intake Oral (cc)',
            296 => 'Intake Spoel (cc)', 297 => 'Intake Transfusi (cc)', 298 => 'Intake Syringe Pump (cc)', 299 => 'Intake Lainnya (cc)',
            300 => 'Output Urine (cc)', 301 => 'Output Drain (cc)', 302 => 'Output NGT (cc)', 303 => 'Output WSD (cc)',
            304 => 'Output IWL (cc)', 305 => 'Output Lainnya (cc)', 306 => '`total_output < 0.5 × kebutuhan_cairan` (oliguria)', 307 => 'Total Intake Cairan 24 Jam (cc)',
            308 => 'Dosis Infus (cc)', 309 => 'IVFD (cc/menit)', 310 => 'Kebutuhan Cairan Harian (cc)', 311 => '`balance_cairan` di luar rentang ±500 cc/hari, **atau** kumulation > ±2000 cc',
            312 => 'Keterangan Monitoring Cairan', 313 => 'Frekuensi Nyeri', 314 => 'Karakteristik Nyeri', 315 => 'Faktor Pemicu yang Memperberat Nyeri',
            316 => 'Faktor yang Mengurangi Nyeri', 317 => 'Lokasi Nyeri (Teks)', 318 => 'Skor FLACC', 319 => 'Metode Skala Nyeri',
            320 => 'FLACC Wajah (Face)', 321 => 'FLACC Kaki (Legs)', 322 => 'FLACC Aktifitas (Activity)', 323 => 'FLACC Menangis (Cry)',
            324 => 'FLACC Konsolabilitas', 325 => 'Total Skor FLACC', 326 => 'Gambar Lokasi Nyeri', 327 => 'Keterangan Lokasi Nyeri',
            328 => 'NIPS Ekspresi Wajah', 329 => 'NIPS Tangisan', 330 => 'NIPS Pola Napas', 331 => 'NIPS Gerakan Lengan',
            332 => 'NIPS Gerakan Tungkai', 333 => 'NIPS Status Jaga', 334 => 'Total Skor NIPS', 335 => 'Interpretasi Skor Nyeri',
            336 => 'Tanggal Monitoring Risiko Jatuh', 337 => 'Waktu Monitoring Risiko Jatuh', 338 => 'Status Monitoring Risiko Jatuh', 339 => 'Kejadian Jatuh',
            340 => 'Waktu Kejadian Jatuh', 341 => 'Keterangan Kejadian Jatuh', 342 => 'Tindak Lanjut Kejadian Jatuh', 343 => 'Norton Kondisi Fisik Umum',
            344 => 'Norton Kesadaran', 345 => 'Norton Aktivitas', 346 => 'Norton Mobilitas', 347 => 'Norton Inkontinensia',
            348 => 'Total Skor Norton', 349 => 'Kategori Risiko Dekubitus', 350 => 'Barthel Transfer', 351 => 'Barthel Mobilisasi',
            352 => 'Barthel Toilet', 353 => 'Barthel Bersih', 354 => 'Barthel Buang Air Besar', 355 => 'Barthel Buang Air Kecil',
            356 => 'Barthel Mandi', 357 => 'Barthel Berpakaian', 358 => 'Barthel Makan', 359 => 'Barthel Naik Turun Tangga',
            360 => 'Total Skor Barthel', 361 => 'Tingkat Ketergantungan Barthel',
            362 => 'Tanggal Asesmen Gizi', 363 => 'Waktu Asesmen Gizi', 364 => 'Jenis Asesmen Gizi',
            365 => 'Riwayat Penyakit Keluarga', 366 => 'Ketidaksukaan Makanan', 367 => 'Pengalaman Diet / Konsul Sebelumnya',
            368 => 'Asupan Makanan SMRS', 369 => 'Z-Score', 370 => 'Lingkar Lengan Atas',
            371 => 'Riwayat Penurunan Berat Badan', 372 => 'Biokimia Terkait Gizi', 373 => 'Nafsu Makan',
            374 => 'Gigi Geligi', 375 => 'Gangguan Menelan / Menghisap / Mengunyah', 376 => 'Keluhan Fisik Klinis Gizi',
            377 => 'Diagnosa Gizi', 378 => 'Kebutuhan Energi Pasien', 379 => 'Intervensi Gizi',
            380 => 'Monitoring & Evaluasi (MONEV)', 381 => 'Sumber Rekomendasi',
            382 => 'Instrumen Skrining Gizi', 383 => 'Jawaban Item Instrumen Gizi', 384 => 'Skor Skrining Gizi',
            385 => 'Kesimpulan Gizi', 386 => 'Tanggal Evaluasi Ahli Gizi',
            388 => 'Tanggal Pesan Makanan', 389 => 'Jenis Pesanan Makanan', 390 => 'Waktu Distribusi Pagi',
            391 => 'Waktu Distribusi Siang', 392 => 'Waktu Distribusi Sore', 393 => 'Petugas Distribusi Pagi',
            394 => 'Petugas Distribusi Siang', 395 => 'Petugas Distribusi Sore', 396 => 'Jenis Diet Pasien',
            // ===== Fase P3 — Farmasi (KONSEP_FARMAKASI.md) =====
            397 => 'Tanggal Daftar Terapi Obat', 398 => 'Waktu Daftar Terapi Obat', 399 => 'Status Pemberian Obat',
            400 => 'Alasan Stop / Tunda Obat', 401 => 'Jam Pemberian Obat', 402 => 'Instrumen Rekonsiliasi',
            403 => 'Lanjut pada Perawatan', 404 => 'Satuan Aturan Pakai', 405 => 'Dokter Preskripsi',
            406 => 'Tanggal Resep / Mulai', 407 => 'Edukasi Obat Saat Pulang', 408 => 'Catatan Rekonsiliasi',
            409 => 'Nomor Resep (ID Resep Farmasi)', 410 => 'Telaah 1 - Kejelasan Penulisan Resep',
            411 => 'Telaah 2 - Tepat Cara Pemberian', 412 => 'Telaah 3 - Tepat Dosis',
            413 => 'Telaah 4 - Tepat Frekuensi Pemberian', 414 => 'Telaah 5 - Tepat Obat',
            415 => 'Telaah 6 - Tidak Ada Duplikasi', 416 => 'Telaah 7 - Tidak Ada Interaksi Obat',
            417 => 'Catatan / Keterangan Telaah', 418 => 'Tanggal Telaah', 419 => 'Waktu Telaah',
            420 => 'Telaah Obat 1 - Tepat Pasien', 421 => 'Telaah Obat 2 - Tepat Obat',
            422 => 'Telaah Obat 3 - Tepat Dosis', 423 => 'Telaah Obat 4 - Tepat Cara Pemberian',
            424 => 'Telaah Obat 5 - Tepat Waktu Pemberian', 425 => 'Catatan / Keterangan Telaah Obat',
            // ===== Fase P3 — Order Penunjang Medis (KONSEP_PENUNJANG_MEDIS_LANJUTAN.md) =====
            432 => 'Tanggal Pemeriksaan Patologi Anatomi', 433 => 'Jenis Pesanan Patologi Anatomi',
            434 => 'Tanggal Pemeriksaan Mikrobiologi', 435 => 'Jenis Pemeriksaan Mikrobiologi',
            436 => 'Unit Mikrobiologi', 437 => 'Spesimen Sumber Mikrobiologi', 438 => 'Tanggal Order POCT',
            439 => 'Indikasi POCT', 440 => 'Tanggal Hasil Terakhir POCT', 441 => 'Waktu Hasil Terakhir POCT',
            442 => 'Hasil Terakhir POCT',

            536 => 'Tanggal Asesmen Kebidanan',             537 => 'Jam Asesmen Kebidanan',             538 => 'Dokter Pemeriksa Kebidanan',
            539 => 'Obstetrik G',             540 => 'Obstetrik P',             541 => 'Obstetrik A',
            543 => 'Komplikasi & Keluhan Kebidanan',             544 => 'Riwayat Operasi Ibu',             545 => 'Riwayat Penyakit Lain Ibu',
            546 => 'Riwayat Reproduksi & Menstruasi',             547 => 'Tanggal Pengkajian Perina',             548 => 'Jam Pengkajian Perina',
            549 => 'Identitas Bayi',             550 => 'Identitas Ibu',             551 => 'Identitas Ayah',
            552 => 'Kehamilan Sekarang',             553 => 'Pemeriksaan Antenatal',             554 => 'USG Ibu',
            555 => 'HPHT',             556 => 'Taksiran Partus',             557 => 'Imunisasi Selama Kehamilan',
            558 => 'Penyakit Selama Hamil',             559 => 'Komplikasi Kehamilan',             560 => 'Laboratorium Ibu',
            561 => 'Butir Pengkajian Perina',             562 => 'Jenis Pengkajian Awal',             563 => 'Kala Persalinan',
            564 => 'Tanggal Kala',             565 => 'Jam Kala',             566 => 'Masalah Kala',
            567 => 'Penatalaksanaan Kala',             568 => 'Pemeriksaan Dalam (VT)',             569 => 'HIS',
            570 => 'DJJ',             571 => 'Penolong Persalinan',             572 => 'Durasi Kala I',
            573 => 'Durasi Kala II',             574 => 'Durasi Kala III',             575 => 'Ketuban',
            576 => 'Kelahiran Kala II',             577 => 'Plasenta Lahir',             578 => 'Kehilangan Darah & Keadaan Ibu',
            579 => 'Penilaian Bayi Baru Lahir',             580 => 'Pelaksanaan Penilaian Bayi Baru Lahir',             581 => 'Tindakan Bayi Lahir Normal',
            582 => 'Tindakan Bayi Lahir Tidak Normal',             583 => 'Riwayat Resusitasi',             584 => 'Tindakan Resusitasi',
            585 => 'Parameter Ventilasi CPAP',             586 => 'Catatan Resusitasi',             587 => 'APGAR - Frekuensi Jantung',
            588 => 'APGAR - Usaha Napas',             589 => 'APGAR - Tonus Otot',             590 => 'APGAR - Refleks',
            591 => 'APGAR - Warna',             592 => 'APGAR - Total Skor',             593 => 'Down Score - Frekuensi Nafas',
            594 => 'Down Score - Retraksi',             595 => 'Down Score - Sianosis',             596 => 'Down Score - Air Entry',
            597 => 'Down Score - Merintih',             598 => 'Total Down Score',             599 => 'Inisiasi Menyususui Dini',
            611 => 'Tanggal Kesimpulan MCU',             612 => 'Jam Kesimpulan MCU',             613 => 'Kesimpulan MCU',
            614 => 'Interpretasi Hasil MCU',             615 => 'Catatan Kesimpulan MCU',             616 => 'Rekomendasi MCU',
            617 => 'Tanggal Pemeriksaan TTV MCU',             618 => 'Jam Pemeriksaan TTV MCU',             619 => 'Keluhan Saat Ini MCU',
            620 => 'Riwayat Penyakit MCU',             621 => 'Riwayat Keluarga MCU',             622 => 'Riwayat Kebiasaan MCU',
            623 => 'Riwayat Alergi MCU',             624 => 'Data Diri MCU',             625 => 'Visus Mata Kanan MCU',
            626 => 'Visus Mata Kiri MCU',             627 => 'Keterangan Visus MCU',             628 => 'Konfrontasi & Buta Warna MCU',
            629 => 'Kehamilan MCU',             630 => 'Riwayat Kehamilan MCU',             631 => 'Riwayat Kehamilan MCU',
            632 => 'Riwayat Kehamilan MCU',             633 => 'Riwayat Penyakit Dalam MCU',             634 => 'Kelainan Inspasial MCU',
            635 => 'Kelainan Toraks MCU',             636 => 'Kelainan Abdominal MCU',             637 => 'Pemeriksaan Jantung MCU',
            638 => 'EKG MCU',             639 => 'Treadmill MCU',             640 => 'Echocardiography MCU',
            641 => 'Telinga MCU',             642 => 'Hidung MCU',             643 => 'Tenggorokan MCU',
            644 => 'Kelainan THT Lainnya MCU',             645 => 'Pemeriksaan Mata Kanan MCU',             646 => 'Pemeriksaan Mata Kiri MCU',
            647 => 'Kelainan Mata MCU',             649 => 'Pemeriksaan Gigi MCU',             650 => 'Kebutuhan Perawatan Gigi MCU',
            652 => 'Pemeriksaan Lokalis OBGYN MCU',             655 => 'Pemeriksaan Localis Bedah MCU',             658 => 'Pemeriksaan Neurologi MCU',
            661 => 'Objektif Gizi MCU',             662 => 'Status Gizi & Pola Diet MCU',             664 => 'Pemeriksaan Fisik Urologi MCU',
            667 => 'Status Psikologis MCU',             668 => 'Skala Cemas & Depresi MCU',             669 => 'Interpretasi Psikologi MCU',
            670 => 'Saran Psikologi MCU',

            // ===== Fase P5 — Kriteria Masuk ICU (447–457) =====
            447 => 'Tanggal Kriteria ICU', 448 => 'Waktu Kriteria ICU', 449 => 'Nilai Kriteria Klinis',
            450 => 'Status Kriteria Klinis', 451 => 'Jumlah Kriteria Terpenuhi', 452 => 'Kesimpulan Kriteria Masuk ICU',
            453 => 'DPJP / Dokter Jaga', 454 => 'DPJP ICU', 455 => 'Informed Consent Tindakan Invasif',
            456 => 'Prioritas Masuk ICU', 457 => 'Kontra Indikasi Masuk ICU',
            // ===== Fase P5 — Kriteria Keluar ICU (458–471) =====
            458 => 'Tanggal Keluar ICU', 459 => 'Waktu Keluar ICU', 460 => 'Diagnosa Keluar ICU',
            461 => 'Dokter Konsultan ICU', 462 => 'Kelayakan Life Support', 463 => 'Status Kriteria Keluar ICU',
            464 => 'Parameter Stabil — Tekanan Darah', 465 => 'Parameter Stabil — Nadi',
            466 => 'Parameter Stabil — Frekuensi Pernapasan', 467 => 'Parameter Stabil — Suhu',
            468 => 'Parameter Stabil — CVP', 469 => 'Keterangan Kriteria IV',
            470 => 'Kesimpulan Keluar ICU', 471 => 'Status Kelayakan Keluar ICU',
            // ===== Fase P5 — Hemodialisa (473–535, 472 reserved) =====
            473 => 'Tanggal Pengkajian HD', 474 => 'Waktu Pengkajian HD', 475 => 'Type Mesin HD',
            476 => 'Dialiser', 477 => 'Berat Badan Awal Sesi HD', 478 => 'Berat Badan Kering',
            479 => 'Lama HD Rencana (jam)', 480 => 'Tanggal Screening Akses', 481 => 'Catatan Screening Akses',
            482 => 'Jenis Dialyzer', 483 => 'Jenis Dialysat', 484 => 'Jenis Akses Vaskuler',
            485 => 'Dosis Sirkulasi (Heparin)', 486 => 'Dosis Awal / Bolus (Heparin)',
            487 => 'Dosis Maintenance (Heparin)', 488 => 'Tanpa Heparinisasi — Penyebab',
            489 => 'Prog Bilas NS 0,9% 100 cc/jam', 490 => 'Jam Mulai HD', 491 => 'Jam Selesai HD',
            492 => 'Lama Dialisis (menit)', 493 => 'Ultra Filtrasi (ml)', 494 => 'Qb / Aliran Darah (ml/mnt)',
            495 => 'TBV / Total Blood Volume (ml)', 496 => 'Tanggal Laboratorium', 497 => 'Waktu Laboratorium',
            498 => 'Hb (g/dL)', 499 => 'Ureum (mg/dL)', 500 => 'Creatinine (mg/dL)', 501 => 'HBsAg',
            502 => 'Kalium (mEq/L)', 503 => 'SGOT (U/L)', 504 => 'SGPT (U/L)', 505 => 'Transfusi Darah',
            506 => 'D 40%', 507 => 'Ca. Gluconas', 508 => 'Hasil Pemeriksaan Lain', 509 => 'KCL',
            510 => 'Renxamin', 511 => 'Erythropoietin', 512 => 'Riwayat Kesehatan Saat Ini',
            513 => 'Kendala Komunikasi', 514 => 'Riwayat Psikososial / Perawat Rumah',
            515 => 'Asesmen Gaduh Gelisah', 516 => 'Diagnosa Keperawatan', 517 => 'Evaluasi Keperawatan',
            518 => 'Tanggal HD Berikutnya', 519 => 'Data Focus', 520 => 'KU Pasien',
            521 => 'Berat Badan Sesi Sebelumnya', 522 => 'Qb / Aliran Darah Intra (ml/mnt)',
            523 => 'UFR / Ultrafiltration Rate (ml/jam)', 524 => 'Volume Ultrafiltrasi (L)',
            525 => 'D 40% (ampul)', 526 => 'Masalah Keperawatan', 527 => 'Tindakan Keperawatan',
            528 => 'Terapi Obat', 529 => 'TBV Post HD (ml)', 530 => 'UFG / Target Ultrafiltration (L)',
            531 => 'Kt/V', 532 => 'Convective Volume (L)', 533 => 'Care Plan',
            534 => 'Informed Consent', 535 => 'Administrasi',
            // ===== Fase P5 — Bedah & Anestesi (762–979, tanpa band 89/97/100) =====
            // Form 85 Asesmen Pra Bedah (762–774)
            762 => 'Tanggal Asesmen Pra Bedah', 763 => 'Jam Asesmen Pra Bedah',
            764 => 'Dokter Penilai Pra Bedah', 765 => 'Temuan & Diagnosis Pra Operasi',
            766 => 'Tanggal & Jam Rencana Operasi', 767 => 'Rencana Tindakan Operasi',
            768 => 'Kebutuhan Edukasi Pra Bedah', 769 => 'Kebutuhan Edukasi Lainnya',
            770 => 'Hasil Laboratorium Pra Bedah', 771 => 'Hasil Radiologi & Penunjang Lain',
            772 => 'Rencana Komplikasi', 773 => 'Back Up Plan',
            774 => 'Prognosis Dubia',
            // Form 86 Evaluasi Pre-Anestesi (775–793)
            775 => 'Tanggal Evaluasi Pre-Anestesi', 776 => 'Jam Evaluasi Pre-Anestesi',
            777 => 'Dokter Anestesi Penilai Pre-Anestesi', 778 => 'Anamnesis Pre-Anestesi',
            779 => 'Jalan Napas & Tidur Jalan', 780 => 'Sistem Pernapasan Pre-Anestesi',
            781 => 'Sistem Kardiovaskular Pre-Anestesi', 782 => 'Sistem Saraf Pre-Anestesi',
            783 => 'Sistem Endokrin & Metabolik', 784 => 'Sistem Ginjal & Kencing',
            785 => 'Sistem Pencernaan Pre-Anestesi', 786 => 'Sistem Darah & Koagulasi',
            787 => 'Alergi Pre-Anestesi', 788 => 'Kajar & Risiko Aspirasi',
            789 => 'Skor ASA', 790 => 'Rencana Anestesi', 791 => 'Rencana Sedasi',
            792 => 'Persetujuan Anestesi', 793 => 'Catatan Evaluasi Pre-Anestesi',
            // Form 87 Asesmen Post-Anestesi (794–809)
            794 => 'Tanggal Asesmen Post-Anestesi', 795 => 'Jam Asesmen Post-Anestesi',
            796 => 'Dokter Anestesi Penilai Post-Anestesi', 797 => 'Skor Aldrete',
            798 => 'Skor Bromage', 799 => 'Skor Steward', 800 => 'Skor PADSS',
            801 => 'Kriteria Pemulihan', 802 => 'Komplikasi Post-Anestesi',
            803 => 'Intake Oral', 804 => 'Mobilisasi Post-Anestesi',
            805 => 'Keadaan Umum Pasien Pulih', 806 => 'Keselamatan Pasien Pulih',
            807 => 'Perawatan Ruang Bedah', 808 => 'Formulir Pasien Pulih',
            809 => 'Rekomendasi Pasien Pulih',
            // Form 88 Monitoring Anestesi (810–819)
            810 => 'Tanggal Monitoring Anestesi', 811 => 'Jam Monitoring Anestesi',
            812 => 'Hemodinamik Monitoring', 813 => 'Suhu Tubuh Monitoring',
            814 => 'Ventilasi & Oksigenasi Monitoring', 815 => 'Kesadaran Monitoring',
            816 => 'Nyeri & Ketidaknyamanan Monitoring', 817 => 'Catatan Peristiwa Kritis',
            818 => 'Lampiran Monitoring Anestesi', 819 => 'Status Monitoring Anestesi',
            // Form 90 Check List Kesiapan Anestesi (834–841)
            834 => 'Tanggal Checklist Kesiapan Anestesi', 835 => 'Jam Checklist Kesiapan Anestesi',
            836 => 'Mesin & Alat Anestesi', 837 => 'Oksigen & Ventilasi',
            838 => 'Monitor & Akses Vaskular', 839 => 'Farmasi & Persiapan',
            840 => 'Persetujuan & Dokumentasi', 841 => 'Catatan Kesiapan Anestesi',
            // Form 91 Pengkajian Operasi (842–851)
            842 => 'Tanggal Pengkajian Operasi', 843 => 'Jam Pengkajian Operasi',
            844 => 'Tahap Pengkajian Operasi', 845 => 'Aspek Pre Operasi',
            846 => 'Aspek Intra Operasi', 847 => 'Aspek Post Operasi',
            848 => 'Rencana Keperawatan Operasi', 849 => 'Kebutuhan Nutrisi Operasi',
            850 => 'Kebutuhan Edukasi Operasi', 851 => 'Catatan Pengkajian Operasi',
            // Form 92 Laporan Operasi (852–866, 983–984)
            852 => 'Tanggal Laporan Operasi', 853 => 'Jam Mulai Operasi',
            854 => 'Nama Tindakan Operasi', 855 => 'Jenis Operasi Laporan',
            856 => 'Dokter Bedah Operator', 857 => 'Dokter Anestesi Laporan',
            858 => 'Diagnosa Pra Operasi', 859 => 'Diagnosa Pasca Operasi',
            860 => 'Temuan Operasi', 861 => 'Prosedur Operasi',
            862 => 'Komplikasi & Transfusi Operasi', 863 => 'Instrumen & Kasa Terpakai',
            864 => 'Specimen Operasi', 865 => 'Kehilangan Darah & Ruang Pasca Operasi',
            866 => 'Instruksi Pasca Operasi',
            983 => 'Jam Selesai Operasi', 984 => 'Lama Operasi',
            // Form 93 Catatan Bedah (867–882)
            867 => 'Tanggal Catatan Bedah', 868 => 'Jam Catatan Bedah',
            869 => 'Dokter Bedah Penulis Catatan', 870 => 'Petugas Pendamping Catatan Bedah',
            871 => 'Aspek Pre Operasi Bedah', 872 => 'Pemeriksaan Fisik Intra Operasi Bedah',
            873 => 'Posisi & Lokasi Operasi', 874 => 'Alat & Perlengkapan Operasi',
            875 => 'Kebutuhan Kamar & Klarifikasi', 876 => 'Pembedahan, Pembiusan & Drainase',
            877 => 'Torniquet & Diathermi', 878 => 'Cairan & Darah',
            879 => 'Sirkuler & Asisten', 880 => 'Instruksi Pasca Operasi Catatan Bedah',
            881 => 'Diagnosa Medis Catatan Bedah', 882 => 'Jenis Operasi Catatan Bedah',
            // Form 94 Transfer Pasien (883–897)
            883 => 'Tanggal Transfer', 884 => 'Jam Transfer',
            885 => 'Ruangan Asal Transfer', 886 => 'Ruangan Tujuan Transfer',
            887 => 'Kategori Transfer', 888 => 'Identitas Pasien Transfer',
            889 => 'Dokter & Perawat Transfer', 890 => 'Diagnosis Medis Transfer',
            891 => 'Diagnosis Keperawatan Transfer', 892 => 'Masalah Keperawatan Transfer',
            893 => 'Kebutuhan Khusus & Kewaspadaan', 894 => 'Prosedur & Peralatan Transfer',
            895 => 'Dokumen Pendukung Transfer', 896 => 'Penerima Pasien Transfer',
            897 => 'Tanda Tangan Penerimaan',
            // Form 95 Penandaan Lokasi Operasi (898–902)
            898 => 'Tanggal Penandaan Lokasi', 899 => 'Jam Penandaan Lokasi',
            900 => 'Lokasi Operasi', 901 => 'Penanda & Lampiran', 902 => 'Petugas & Konfirmasi Penandaan',
            // Form 96 Penandaan Pasien (903–910)
            903 => 'Tanggal Penandaan Pasien', 904 => 'Jam Penandaan Pasien',
            905 => 'Penanda Alergi', 906 => 'Keterangan Alergi Ditandai',
            907 => 'Penanda Hepatitis', 908 => 'Penanda HIV',
            909 => 'Penanda TB', 910 => 'Penanda Geriatri',
            // Form 98 Surveilans Luka Insisi (924–936)
            924 => 'Identitas Operasi Surveilans', 925 => 'Trauma Pada Surveilans',
            926 => 'Tanggal MRS Surveilans', 927 => 'Tanggal Operasi Surveilans',
            928 => 'Jam Operasi Surveilans', 929 => 'Lama Operasi Surveilans',
            930 => 'Kualifikasi Dokter Bedah Surveilans', 931 => 'Klasifikasi Luka Insisi',
            932 => 'Pencegahan Infeksi Luka', 933 => 'Tanda Infeksi Luka',
            934 => 'Kondisi Luka Postsurgikal', 935 => 'Drainase Luka',
            936 => 'Keterangan Surveilans Luka',
            // Form 99 Catatan Anestesia (937–954)
            937 => 'Tanggal Catatan Anestesia', 938 => 'Jam Mulai Anestesia',
            939 => 'Jam Selesai Anestesia', 940 => 'Lama Anestesia',
            941 => 'Dokter Anestesia', 942 => 'Perawat Anestesia',
            943 => 'Jenis Anestesia Catatan', 944 => 'Jalan Napas & Ventilasi Anestesia',
            945 => 'Sirkuit & Perlengkapan Anestesia', 946 => 'Posisi & Fixasi Anestesia',
            947 => 'Monitoring Anestesia', 948 => 'Hasil Laboratorium Penunjang Anestesia',
            949 => 'Riwayat Penyakit & Alergi Anestesia', 950 => 'Vaskular, Monitoring & Obat Intraop',
            951 => 'Kejadian Tidak Terduga Anestesia', 952 => 'Penanganan Intraoperatif Anestesia',
            953 => 'Kondisi Akhir Anestesia', 954 => 'Instruksi Pasca Anestesia',
            // Form 101 Asesmen Katarak (968–979)
            968 => 'Tanggal Asesmen Katarak', 969 => 'Jam Asesmen Katarak',
            970 => 'DPJP Utama Asesmen Katarak', 971 => 'Skrining Risiko Jatuh Katarak',
            972 => 'Diagnosis Keperawatan Katarak', 973 => 'Tujuan & Kriteria Hasil Katarak',
            974 => 'Intervensi Pre Operasi Katarak', 975 => 'Intervensi Post Operasi Katarak',
            976 => 'Skala Nyeri Katarak', 977 => 'Jadwal Pemberian Obat Katarak',
            978 => 'Pemeriksaan Mata Katarak', 979 => 'Visus Pasca Operasi Katarak',
            // ===== Fase P6 — Onkologi & Darah (993–1032, KONSEP_ONKOLOGI_TRANSFUSI.md) =====
            // Form 106 Asesmen Kemoterapi
            993 => 'Siklus & Regimen Kemoterapi',
            994 => 'DPJP Utama',
            995 => 'Hasil Penunjang Terjadwal',
            996 => 'Tanggal Pemberian Obat Kemoterapi',
            997 => 'Pemasangan Alat Akses',
            998 => 'Jenis Obat Kemoterapi',
            999 => 'Reaksi Kemoterapi',
            1000 => 'Kondisi Rahim',
            1001 => 'Perawat & Keluarga Pendamping',
            1002 => 'Masalah Keperawatan Kemoterapi',
            // Form 107 Pemantauan Kemoterapi
            1003 => 'Tanggal & Jam Pemantauan Kemoterapi',
            1004 => 'Pemberian Premedikasi',
            1005 => 'Jam Monitoring Kemoterapi',
            1006 => 'Cairan & Obat Kemoterapi',
            1007 => 'Keluhan & Efek Samping Obat',
            1008 => 'Total Cairan Kemoterapi (cc)',
            // Form 108 Asesmen Pasien dengan Darah
            1009 => 'Golongan Darah & Rh',
            1010 => 'Dasar Penentuan Golongan Darah',
            1011 => 'Jenis Darah yang Diminta',
            1012 => 'Jumlah Darah yang Diminta',
            1013 => 'Alasan Transfusi',
            1014 => 'Riwayat Transfusi & Reaksi Alergi',
            1015 => 'Tanggal Permintaan Darah',
            1016 => 'Dokter Pemohon Darah',
            // Form 109 Assesmen Transfusi
            1017 => 'Tanggal & Waktu Persiapan Transfusi',
            1018 => 'Identitas Labu Darah',
            1019 => 'Keadaan Umum & Keluhan',
            1020 => 'Verifikasi Ganda Darah - Perawat 1',
            1021 => 'Verifikasi Ganda Darah - Perawat 2',
            1022 => 'Petugas Verifikasi Transfusi',
            // Form 110 Monitoring Transfusi Darah
            1023 => 'No. Labu Darah (Monitoring)',
            1024 => 'Darah Masuk per Ronde (cc)',
            1025 => 'Komponen Darah',
            1026 => 'Gejala Fisik Transfusi',
            1027 => 'Tanda Tanda Vital during Transfusi',
            1028 => 'Tindakan & Catatan Transfusi',
            1029 => 'Keluhan Pasien',
            // Form 111 Double Check Obat High Alert
            1030 => 'Tanggal & Jam Pemberian High Alert',
            1031 => 'Pengecekan 5 Benar - Perawat 1',
            1032 => 'Pengecekan 5 Benar & Verifikasi Ganda',
            // ===== Fase P6 — Psikososial & Kerohanian (671–690, KONSEP_PSIKOSOSIAL_KEROHANIAN.md) =====
            // Form 112 Asesmen Psikologi
            671 => 'Ketersediaan Orang yang Dapat Dihubungi',
            672 => 'Nama Keluarga yang Dihubungi',
            673 => 'Status/Hubungan Keluarga',
            674 => 'Alamat Keluarga',
            675 => 'No. HP/Telp. Keluarga',
            676 => 'Rencana Perawatan Selanjutnya',
            677 => 'Persiapan Lingkungan Rumah',
            678 => 'Nama Perawat di Rumah',
            679 => 'Perlu Fasilitasi Rumah Sakit',
            680 => 'Reaksi Pasien atas Penyakitnya',
            681 => 'Kebutuhan Pribadi & Reaksi Keluarga',
            // Form 113 Asesmen Spiritual
            682 => 'Masalah Keperawatan (Manajemen Gejala)',
            683 => 'Perlu Pelayanan & Kebutuhan Kerohanian',
            684 => 'Petugas Kerohanian',
            // Form 114 & 115 — Permintaan / Bukti Pelayanan Kerohanian
            685 => 'Nama Petugas Kerohanian',
            686 => 'Umur Petugas Kerohanian',
            687 => 'Jenis Kelamin Petugas Kerohanian',
            688 => 'Agama Petugas Kerohanian',
            689 => 'Tanggal & Waktu Pelayanan Kerohanian',
            690 => 'Hari & Tempat Pelayanan Kerohanian',
            // ===== Fase P6 — Surveilans Infeksi & Restraint (691–729, KONSEP_SURVEILANS_INFEKSI.md §10) =====
            // Form 116 Surveilans CVC/PICC
            691 => 'Lokasi Pemasangan CVC/PICC',
            692 => 'Nomor Kateter CVC/PICC',
            693 => 'Jenis CVC/PICC (Lumen)',
            694 => 'Status Pemasangan & Pelepasan CVC/PICC',
            695 => 'Tanggal Pemasangan & Pelepasan CVC/PICC',
            696 => 'Item Pencegahan BSI - Kebersihan Tangan & Aseptik',
            697 => 'Item Pencegahan BSI - Alat Pelindung, Antiseptik & Dressing',
            698 => 'Gejala & Tanda BSI (Kriteria CLABSI)',
            // Form 117 Surveilans IV Catheter Perifer
            699 => 'Lokasi IV Catheter',
            700 => 'Nomor IV Catheter',
            701 => 'Jenis Cairan/Terapi',
            702 => 'Item Pencegahan IV - Pemasangan, Nomor & Cairan',
            703 => 'Item Pencegahan IV - Fiksasi, Tangan, Dressing & Dokumentasi',
            704 => 'Skor & Gejala PIVAS',
            705 => 'Tanggal Pemasangan & Pelepasan IV Catheter',
            // Form 118 Surveilans Kateter Urine
            706 => 'Jenis Kateter Urine',
            707 => 'Nomor Kateter Urine',
            708 => 'Item Pencegahan ISK - Aseptik, Posisi Kateter & Perawatan Urin Bag',
            709 => 'Item Pencegahan ISK - Gelas Ukur & Indikasi',
            710 => 'Gejala & Tanda ISK',
            711 => 'Tanggal Pemasangan & Pelepasan Kateter Urine',
            // Form 119 Surveilans Tirah Baring Total (HAP)
            712 => 'Bundle Pencegahan HAP',
            713 => 'Suhu Tubuh & Leukosit pada Gejala HAP',
            714 => 'Antibiotik yang Diberikan',
            715 => 'Sekresi Trakea & Hasil Kultur Sputum',
            // Form 120 Screening Akses Vaskular
            716 => 'Jenis Screening Akses',
            717 => 'Kondisi Akses & Steal Syndrome',
            718 => 'Kondisi CDL, Exit Site & Kondisi Pasien',
            719 => 'Tanda CDL Disfungsi/Malfungsi & Terkait Pasien',
            // Form 121 Asesmen Pasien Restraint
            720 => 'Penilaian Gaduh Gelisah & Order Dokter',
            721 => 'Kebutuhan Restraint Non Farmakologis',
            722 => 'Restraint Farmakologis',
            723 => 'Kriteria Penghentian Restraint',
            724 => 'Status Mental',
            725 => 'Informasi & Edukasi Pasien/Keluarga',
            // Form 122 Observasi Restraint (objek formula, KONSEP §9)
            726 => 'Fungsi Kognitif',
            727 => 'Tanda Cedera / Luka',
            728 => 'Asupan Makanan, Minuman & Posisi Fiksasi',
            729 => 'PANSS-EC (Dokter)',
            // ===== Fase P6 — Formulir & Rujukan (730–761, KONSEP_FORMULIR_RUJUKAN.md) =====
            // Form 58 Rekam Medis Rujukan
            730 => 'Tanggal Pengantar & Rencana Rawat',
            731 => 'Tingkat Urgensi Rujukan',
            732 => 'Indikasi Rawat Inap',
            733 => 'Rencana Pemeriksaan Penunjang',
            734 => 'Terapi Perawatan',
            735 => 'Dokter Rawat Pengirim',
            736 => 'Kebutuhan Pelayanan',
            737 => 'Kebutuhan Kamar',
            738 => 'Data Pendamping Pasien',
            // Form 126 Surat Keterangan Sehat
            739 => 'Nomor Surat Keterangan Sehat',
            740 => 'Hasil Pemeriksaan Fisik',
            741 => 'Pemeriksaan Mata',
            // Form 127 Pesan Bedah / Cathlab / Endoscopy / ESWL
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
            // Form 128 SPRI & Program Rujukan Balik
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
        ];
        foreach ($objeks as $objekId => $namaObjek) {
            DB::table('objek')->updateOrInsert(
                ['objek_id' => $objekId],
                [
                    'nama_objek' => $namaObjek,
                    'input_time' => $now,
                    'input_user_id' => 1,
                    'status_batal' => 0,
                ]
            );
        }
        // ======== Mapping Form <-> Objek (objek_form_control) ========
        // Key = variabel (nama field di form EMR); value = objek_id.
        $mapping = [
            1 => [
                'keluhan' => 13, 'diagnosa_medis' => 40,
                'riwayat_penyakit_sebelumnya' => 41, 'riwayat_penyakit_sekarang' => 42,
                'kesadaran' => 51, 'td' => 6, 'nadi' => 10, 'suhu' => 11, 'pernapasan' => 12,
                'berat_badan' => 8, 'tinggi_badan' => 9, 'saturasi' => 15, 'ews' => 16,
                'nyeri' => 14, 'alergi' => 17,
            ],
            2 => [
                'subjective' => 1, 'objective' => 2, 'assessment' => 3, 'planning' => 4, 'instruksi' => 5,
            ],
            3 => [
                'agama' => 21, 'kegiatan_ibadah' => 22, 'tingkat_pendidikan' => 23, 'pekerjaan' => 24,
                'suku_bangsa' => 25, 'kebangsaan' => 26, 'handphone' => 60,
                'nama_pasangan' => 31, 'usia_pasangan' => 32, 'pendidikan_pasangan' => 33,
                'pekerjaan_pasangan' => 34, 'suku_bangsa_pasangan' => 35, 'kebangsaan_pasangan' => 36,
                'tinggal_bersama' => 37, 'penanggung_jawab_pasien' => 38, 'hubungan_pasien' => 39,
                'aktifitas_sebelum_makan' => 27, 'pantangan_pulang' => 28,
                'pantangan_transfusi_darah' => 29, 'pantangan_makan' => 30,
                'diagnosa_medis' => 40, 'keluhan' => 13,
                'riwayat_penyakit_sebelumnya' => 41, 'riwayat_penyakit_sekarang' => 42,
                'infeksius_flag' => 43, 'menular_melalui' => 44,
                'infeksius_memerlukan_isolasi' => 45, 'infeksius_hasil_penunjang' => 46,
                'imunologi_flag' => 47, 'imunologi_memerlukan_isolasi' => 48,
                'imunologi_pembatasan_pengunjung' => 49, 'imunologi_hasil_penunjang' => 50,
                'vaksin_covid' => 62, 'tanggal_covid_1' => 62, 'tanggal_covid_2' => 62,
                'riw_ope_kemo' => 61, 'riwayat_operasi' => 61,
                'riwayat_kemoterapi' => 52, 'riwayat_radioterapi' => 53,
                'kesadaran' => 51, 'gcs_e' => 54, 'gcs_m' => 55, 'gcs_v' => 56, 'gcs_jumlah' => 57,
                'dpo' => 59, 'td' => 6, 'nadi' => 10, 'suhu' => 11, 'pernapasan' => 12,
                'berat_badan' => 8, 'tinggi_badan' => 9, 'pemberian_o2' => 18,
                'cara_pemberian_o2' => 19, 'ett' => 20, 'saturasi' => 15, 'ews' => 16,
                'allo_anamnesa' => 63, 'nama_allo' => 64, 'hubungan_allo' => 65, 'bmi' => 58,
                'nyeri' => 14, 'alergi' => 17,
                // Pengkajian risiko jatuh (instrumen dipilih dari usia, lihat
                // App\Helpers\RisikoJatuhHelper). Objek 66-68 (UP GO lama) tidak
                // lagi dipakai form ini; mapping lamanya dibersihkan di bawah.
                'risiko_jatuh_instrumen' => 89, 'risiko_jatuh_skor' => 90,
                'risiko_jatuh_label' => 91, 'risiko_jatuh_intervensi' => 92,
                'risiko_jatuh_ringkasan' => 93,
                // HDS (anak)
                'hds_1' => 94, 'hds_2' => 95, 'hds_3' => 96, 'hds_4' => 97,
                'hds_5' => 98, 'hds_6' => 99, 'hds_7' => 100,
                // Morse Fall Scale (dewasa)
                'mfs_1' => 101, 'mfs_2' => 102, 'mfs_3' => 103,
                'mfs_4' => 104, 'mfs_5' => 105, 'mfs_6' => 106,
                // Sydney Scoring (alternatif)
                'syd_ts_bed' => 107, 'syd_ts_bangku' => 108, 'syd_ts_bantuan' => 109,
                'syd_ms_bantuan' => 110, 'syd_ms_kursi_roda' => 111, 'syd_ms_imobil' => 112,
                // Timed Up and Go (lansia)
                'tug_detik' => 113,
            ],
            // Pengkajian harian keperawatan: vital + keluhan +yeri + risiko jatuh
            // (dinilai ulang tiap hari) + balance cairan/eliminasi.
            4 => [
                'keluhan' => 114, 'catatan_keperawatan' => 115,
                'kesadaran' => 51, 'dpo' => 59,
                'gcs_e' => 54, 'gcs_m' => 55, 'gcs_v' => 56, 'gcs_jumlah' => 57,
                'td' => 6, 'nadi' => 10, 'suhu' => 11, 'pernapasan' => 12,
                'berat_badan' => 8, 'tinggi_badan' => 9, 'saturasi' => 15, 'ews' => 16,
                'pemberian_o2' => 18, 'cara_pemberian_o2' => 19, 'ett' => 20,
                'nyeri' => 14, 'alergi' => 17,
                'eliminasi' => 116, 'intake_cairan' => 117,
                'intake_makanan' => 118, 'tidur' => 119, 'catatan_tambahan' => 120,
                'risiko_jatuh_instrumen' => 89, 'risiko_jatuh_skor' => 90,
                'risiko_jatuh_label' => 91, 'risiko_jatuh_intervensi' => 92,
                'risiko_jatuh_ringkasan' => 93,
                'hds_1' => 94, 'hds_2' => 95, 'hds_3' => 96, 'hds_4' => 97,
                'hds_5' => 98, 'hds_6' => 99, 'hds_7' => 100,
                'mfs_1' => 101, 'mfs_2' => 102, 'mfs_3' => 103,
                'mfs_4' => 104, 'mfs_5' => 105, 'mfs_6' => 106,
                'syd_ts_bed' => 107, 'syd_ts_bangku' => 108, 'syd_ts_bantuan' => 109,
                'syd_ms_bantuan' => 110, 'syd_ms_kursi_roda' => 111, 'syd_ms_imobil' => 112,
                'tug_detik' => 113,
            ],
            5 => [
                'barang_id' => 69, 'jumlah' => 70, 's_1' => 71, 's_2' => 72,
                'aturan_pakai' => 73, 'rute_pemberian' => 74,
            ],
            6 => [
                'tindakan_id' => 75, 'prioritas' => 76, 'keterangan' => 77, 'hasil' => 78,
                'nilai_normal' => 79, 'satuan_hasil' => 80, 'flag_abnormal' => 81, 'petugas_id' => 82,
            ],
            7 => [
                'tindakan_id' => 75, 'prioritas' => 76, 'keterangan' => 77, 'hasil' => 78,
                'flag_abnormal' => 81, 'petugas_id' => 82,
            ],
            8 => [
                'jenis_konsultasi' => 83,
                'bagian_tujuan_id' => 84,    // Konsul Layanan (wajib) / Rencana Kontrol (wajib) → bagian Poli (referensi 1)
                'dokter_tujuan_id' => 85,    // Konsul Layanan (WAJIB) / Rencana Kontrol (wajib) → pegawai dokter
                'tanggal_kontrol' => 86,     // Rencana Kontrol (wajib)
                'indikasi_konsultasi' => 87, // semua jenis
                'bagian_rehab_id' => 88,     // Konsultasi Rehabilitasi Medik (wajib) → bagian penunjang rehab
                'catatan' => 77,             // opsional, reuse objek 77 "Keterangan"
            ],
            // Implementasi Keperawatan: satu baris per pengisian form.
            9 => [
                'implementasi_id' => 121,     // wajib, dari Master Implementasi
                'nama_implementasi' => 122,   // snapshot, diisi server
                'tanggal_implementasi' => 123, // wajib (date)
                'waktu_implementasi' => 124,  // wajib (jam, H:i)
                'keterangan_implementasi' => 125, // opsional
                'respon_implementasi' => 126,     // opsional, free text
            ],
            // Tindakan Medis. Baris obat/BMHP memakai variabel BERSUFFIX
            // (obat_1, jumlah_1, obat_2, ...) — lihat $mappingBarisObat di bawah.
            10 => [
                'dokter_persetujuan_id' => 127,  // wajib, dari pegawai (dokter)
                'tindakan_id' => 75,             // wajib, reuse objek "Tindakan"
                'jenis_permintaan' => 128,       // wajib, CITO / BIASA
                'tanggal_tindakan' => 129,       // wajib (date)
                'waktu_tindakan' => 130,         // wajib (jam, H:i)
                'hasil_kondisi' => 131,           // opsional, free text
                'keterangan' => 77,              // opsional, reuse objek "Keterangan"
                'pemakaian_obat_bmhp' => 132,    // wajib, Ya / Tidak
            ],
            // Assesmen Awal Medis Rawat Jalan: keluhan utama + tanda vital +
            // asesmen. Field turunan `bmi` dihitung server dari berat/tinggi
            // (filteredData), jadi wajib ada di mapping.
            11 => [
                'keluhan_utama' => 13,           // wajib, reuse objek "Keluhan Utama"
                'tanggal_assesmen' => 137,       // wajib (date)
                'waktu_assesmen' => 138,         // wajib (jam, H:i)
                'dokter_pemeriksa_id' => 139,    // wajib, dari pegawai (dokter)
                'td_sistolik' => 6,              // reuse objek "Tekanan Darah Sistolik"
                'td_diastolik' => 7,             // reuse objek "Tekanan Darah Diastolik"
                'nadi' => 10,                    // reuse
                'suhu' => 11,                    // reuse
                'pernapasan' => 12,              // reuse
                'saturasi' => 15,                // reuse
                'berat_badan' => 8,              // reuse
                'tinggi_badan' => 9,             // reuse
                'bmi' => 58,                     // reuse, TURUNAN (dihitung server)
                'kesadaran' => 51,                // reuse
                'nyeri' => 14,                   // Ya / Tidak
                'skor_nyeri' => 140,             // 0-10, tampil bila nyeri = Ya
                'diagnosa_kerja' => 135,         // wajib
                'tujuan_kunjungan' => 136,        // wajib
                'catatan_tambahan' => 77,        // opsional, reuse objek "Keterangan"
                'anamnesis' => 141,              // opsional, hasil anamnesis bebas
                'riwayat_penyakit_dahulu' => 41, // opsional, reuse objek 41 "Riwayat Penyakit Sebelumnya"
                'oksigen' => 18,                 // parameter EWS: Air / Oksigen (reuse objek 18)
                'total_ews' => 142,              // TURUNAN (App\Helpers\EwsHelper)
                'kategori_ews' => 143,           // TURUNAN (App\Helpers\EwsHelper)
                'ews_tidak_diukur' => 144,       // daftar parameter yang ditandai tidak diukur
            ],
            // SBAR (form 12): 4 unsur wajib + metadata komunikasi. Teks S & B
            // di-prefill otomatis dari EMR form lain (lihat SbarController),
            // tetap bisa diedit petugas sebelum disimpan.
            12 => [
                'tanggal_sbar' => 145,        // wajib (date)
                'waktu_sbar' => 146,          // wajib (jam, H:i)
                'shift' => 147,               // wajib: Pagi / Siang / Sore / Malam
                'urgensi' => 148,             // wajib: Biasa / Segera / Mendesak
                'cara_komunikasi' => 149,     // wajib: Tatap Muka / Telepon / dst.
                'penerima_id' => 150,         // wajib, dari pegawai (penerima informasi)
                's_situation' => 151,         // wajib, prefill dari data klinis
                'b_background' => 152,        // wajib, prefill dari data klinis
                'a_assessment' => 153,        // wajib
                'r_recommendation' => 154,    // wajib
                'alergi' => 17,               // opsional, reuse objek 17 "Alergi"
                'catatan_tambahan' => 77,     // opsional, reuse objek 77 "Keterangan"
            ],
            // Tanda Vital / Observasi Harian (form 13).
            //
            // Sengaja HANYA 3 objek baru (tanggal, waktu observasi, dan flow
            // rate oksigen); seluruh tanda vital, BMI, kesadaran, oksigen, EWS,
            // dan nyeri memakai objek yang sudah ada supaya laporan antar form
            // konsisten.
            13 => [
                'tanggal_observasi' => 155,    // wajib (date)
                'waktu_observasi' => 156,      // wajib (jam, H:i)
                // Tanda vital
                'td_sistolik' => 6,            // wajib, parameter EWS
                'td_diastolik' => 7,           // wajib
                'nadi' => 10,                  // wajib, parameter EWS
                'pernapasan' => 12,            // wajib, parameter EWS
                'suhu' => 11,                  // wajib, parameter EWS
                'saturasi' => 15,              // wajib, parameter EWS
                'berat_badan' => 8,            // opsional
                'tinggi_badan' => 9,           // opsional
                'bmi' => 58,                   // TURUNAN, dihitung ulang di server
                // Kesadaran & pemberian oksigen
                'kesadaran' => 51,             // wajib, parameter EWS
                'oksigen' => 18,               // wajib, parameter EWS (Air/Oksigen)
                'cara_oksigen' => 19,          // wajib bila oksigen = Oksigen
                'flow_rate' => 157,            // L/menit, opsional
                'ett' => 20,                   // radio Ya/Tidak
                // Penilaian nyeri
                'nyeri' => 14,
                'skor_nyeri' => 140,           // opsional, relevan bila nyeri = Ya
                // EVM (lihat App\Helpers\EwsHelper)
                'total_ews' => 142,            // TURUNAN
                'kategori_ews' => 143,         // TURUNAN
                'ews_tidak_diukur' => 144,     // daftar parameter "tidak diukur"
                'keterangan' => 77,            // reuse objek 77 "Keterangan"
            ],
            // Bundle Pencegahan VAP (form 14).
            //
            // 10 butir bundle memakai variabel BERSUFFIX vap_1..vap_10, bukan
            // satu variabel `vap` dengan 10 baris, karena
            // EmrHelper::emrDetailByVariabel() melakukan pluck('value','variabel')
            // — 10 baris untuk variabel sama akan tertimpa oleh baris terakhir
            // sehingga butir kedua dst. HILANG saat form dibuka lagi.
            14 => [
                'tanggal_bundle' => 155,       // wajib (date), reuse objek 155
                'waktu_bundle' => 156,         // wajib (jam, H:i), reuse objek 156
                'vap_skor' => 168,             // TURUNAN, jumlah butir "Ya"
                'vap_persen' => 169,           // TURUNAN, persen kepatuhan
                'vap_kategori' => 170,         // TURUNAN, kategori kepatuhan
                'catatan' => 77,               // reuse objek 77 "Keterangan"
            ],
            // Alat Invasif (form 15). Bentuknya SATU baris per EMR (satu alat),
            // jadi tidak perlu variabel bersuffix seperti butir bundle VAP.
            15 => [
                'tanggal_pasang' => 171,      // opsional (alat bisa sudah terpasang sebelum masuk)
                'jam_pasang' => 172,          // opsional
                'alat_invasif' => 173,        // wajib, opsi dari SelectOption::alat_invasif
                'lokasi_pemasangan' => 174,   // wajib, opsi dari SelectOption::lokasi_alat_invasif
                'tanggal_lepas' => 175,       // opsional, kosong = masih terpasang
                'jam_lepas' => 176,           // opsional
                'lama_pemasangan' => 177,     // TURUNAN, jumlah hari pasang -> lepas
            ],
            // ===== Fase P1 — Catatan Medis Lanjutan =====
            // Resume Medis (form 16). Pemeriksaan fisik 12 organ memakai SATU
            // objek (188) dengan 12 variabel bersuffix — sama seperti pola
            // butir bundle VAP: emrDetailByVariabel() melakukan
            // pluck('value','variabel') sehingga satu variabel untuk 12 organ
            // akan tertimpa. Laporan antar-form WAJIB query per variabel,
            // bukan per objek_id.
            16 => [
                'tanggal_resume' => 178,
                'waktu_resume' => 179,
                'keluhan_utama' => 13,   // reuse "Keluhan Utama"
                'indikasi_rawat_inap' => 180,
                'riwayat_kesehatan_saat_ini' => 42,   // reuse "Riwayat Penyakit Sekarang"
                'riwayat_penyakit_dahulu' => 41,   // reuse "Riwayat Penyakit Sebelumnya"
                // Tanda vital & skor — seluruhnya reuse objek yang sudah ada.
                'td_sistolik' => 6,
                'td_diastolik' => 7,
                'nadi' => 10,
                'pernapasan' => 12,
                'suhu' => 11,
                'skor_nyeri' => 140,  // reuse
                'total_ews' => 142,  // TURUNAN (EwsHelper)
                'kategori_ews' => 143,  // TURUNAN (EwsHelper)
                'gcs_e' => 54,
                'gcs_m' => 55,
                'gcs_v' => 56,
                'gcs_jumlah' => 57,   // TURUNAN
                // Pemeriksaan fisik: 12 organ -> satu objek 188.
                'pf_kepala' => 188,
                'pf_mata' => 188,
                'pf_tht' => 188,
                'pf_gigidanmulut' => 188,
                'pf_leher' => 188,
                'pf_toraks' => 188,
                'pf_jantung' => 188,
                'pf_paru' => 188,
                'pf_abdomen' => 188,
                'pf_kelenjar' => 188,
                'pf_genitalia' => 188,
                'pf_ekstremitas' => 188,
                // Diagnosa resume.
                'diagnosa_primer' => 181,
                'diagnosa_sekunder' => 182,
                'prosedur' => 183,
                'komplikasi' => 184,
                // Kondisi pulang / rujukan / kematian.
                'kondisi_saat_pulang' => 185,
                'catatan_alasan_pulang' => 77,   // reuse "Keterangan"
                'dirujuk_ke' => 186,
                'meninggal' => 187,
                'catatan_kematian' => 77,   // reuse "Keterangan"
            ],
            // Care Plan (form 20).
            20 => [
                'tanggal_care_plan' => 195,
                'perkiraan_lama_rawat' => 192,
                'tempat_pertemuan' => 189,
                'intervensi_non_farmakologis' => 190,
                'intervensi_farmakologis' => 191,
                'target_perawatan' => 193,
                'kriteria_pemulangan' => 194,
                'catatan' => 77,   // reuse "Keterangan"
            ],
            // DAR (form 21) — catatan kejadian forensik.
            21 => [
                'tanggal_dar' => 196,
                'dasar_laporan' => 13,   // reuse "Keluhan Utama"
                'kronologi_kejadian' => 1,    // reuse "Subjective (S)"
                'temuan_pemeriksaan' => 2,    // reuse "Objective (O)"
                'kesimpulan_dar' => 3,    // reuse "Assessment (A)"
                'tindakan_dar' => 5,    // reuse "Instruksi (I)"
                'meninggal' => 187,  // reuse objek form 16
            ],
            // Catatan Medis Visum (form 57).
            57 => [
                'tanggal_visum' => 155,  // reuse "Tanggal Observasi"
                'waktu_visum' => 156,  // reuse "Waktu Observasi"
                'jenis_visum' => 197,
                'nomor_visum' => 198,
                'dokter_visum' => 139,  // reuse "Dokter Pemeriksa"
                'permintaan_visum' => 199,
                'benda_bukti_identifisir' => 77,   // reuse "Keterangan"
                'hasil_visum' => 2,    // reuse "Objective (O)"
                'kesimpulan_visum' => 3,    // reuse "Assessment (A)"
                'kelainan_sebab' => 1,    // reuse "Subjective (S)"
                'kelainan_akibat' => 4,    // reuse "Planning (P)"
            ],
            // Discharge Planning (form 17).
            17 => [
                'tanggal_masuk_rs' => 200,
                'waktu_masuk_rs' => 156, // reuse "Waktu Observasi"
                'diagnosis_medis' => 40, // reuse "Diagnosa Medis"
                'estimasi_hari_rawat' => 201,
                'tanggal_ren_pulang' => 202,
                'waktu_ren_pulang' => 156,
                'pengaruh_pasien_kel' => 203,
                'pengaruh_kerja' => 203,
                'pengaruh_keuangan' => 203,
                'antisipati_masalah' => 204,
                'tinggal_sendiri' => 206,
                'membantu_pasien' => 206,
                'gunakan_alat_medis' => 207,
                'perlu_alat_bantu' => 208,
                'perlu_perawatan_khusus' => 209,
                'masalah_kebutuhan_pribadi' => 210,
                'nyeri_kronis' => 211,
                'perlu_edukasi' => 212,
                'keterampilan_khusus' => 213,
                'adl_menyiapkan_makanan' => 205,
                'adl_makan' => 205,
                'adl_diet' => 205,
                'adl_menyiapkan_obat' => 205,
                'adl_minum_obat' => 205,
                'adl_mandi' => 205,
                'adl_berpakaian' => 205,
                'adl_transportasi' => 205,
                'adl_edukasi_kesehatan' => 205,
                'adl_edukasi_lain' => 205,
                'edukasi_obat_obat' => 214,
                'edukasi_nutrisi' => 214,
                'edukasi_perawatan_luka' => 214,
                'edukasi_mobilisasi' => 214,
                'edukasi_manajemen_nyeri' => 214,
                'edukasi_insulin_sc' => 214,
                'edukasi_lain_1' => 214,
                'edukasi_lain_2' => 214,
                'catatan' => 77, // reuse "Keterangan"
            ],
            // Pemulangan Pasien (form 18).
            18 => [
                'tanggal_pulang' => 155, // reuse "Tanggal Observasi"
                'waktu_pulang' => 156,
                'kondisi_pulang' => 215,
                'catatan_alasan_pulang' => 77,
                // Objek 186 "Dirujuk Ke" dideklarasikan di form 16 dan hanya
                // di-reuse di sini — satu-satunya objek > 177 lintas dokumen.
                'dirujuk_ke' => 186,
                'rujuk_di_area_sama' => 186,
                'meninggal' => 216,
                'catatan_meninggal' => 77,
                'td_sistolik' => 6,
                'td_diastolik' => 7,
                'nadi' => 10,
                'pernapasan' => 12,
                'suhu' => 11,
                'skor_nyeri' => 140,
                'total_ews' => 142, // TURUNAN
                'kategori_ews' => 143, // TURUNAN
                'gcs_e' => 54,
                'gcs_m' => 55,
                'gcs_v' => 56,
                'gcs_jumlah' => 57, // TURUNAN
                'diet_jenis' => 217,
                'diet_keterangan' => 217,
                'bab' => 218,
                'bak' => 218,
                'bak_keterangan' => 218,
                'luka' => 219,
                'luka_keterangan' => 219,
                'transfer' => 220,
                'edukasi_1' => 221,
                'edukasi_2' => 221,
                'edukasi_3' => 221,
                'edukasi_4' => 221,
                'edukasi_5' => 221,
                'edukasi_6' => 221,
                'edukasi_7' => 221,
                'edukasi_keterangan' => 221,
                'nyeri_terapi' => 222,
                'nyeri_efek_samping' => 222,
                'nyeri_kapan_ke_rs' => 222,
                'masalah_keperawatan' => 224,
                'anjuran_pulang' => 5, // reuse "Instruksi (I)"
                'keterangan' => 77,
                'serah_lab' => 223,
                'serah_rontgen' => 223,
                'serah_ct_scan' => 223,
                'serah_mri' => 223,
                'serah_usg' => 223,
                'serah_surat_sakit' => 223,
                'serah_surat_asuransi' => 223,
                'serah_resume' => 223,
                'serah_buku_bayi' => 223,
                'serah_gol_darah' => 223,
                'serah_skl_bayi' => 223,
                'serah_penyerah_bayi' => 223,
                'serah_lainnya' => 223,
            ],
            // Triage IGD (form 19). Hampir semua field me-reuse objek lama.
            19 => [
                'tanggal_triase' => 155,
                'waktu_triase' => 156,
                'keadaan_umum' => 225,
                'berat_badan' => 8,
                'tinggi_badan' => 9,
                'suhu' => 11,
                'jalan_nafas' => 226,
                'tipe_nafas' => 226,
                'frekuensi_nafas' => 12,
                'saturasi' => 15,
                'sirkulasi' => 227,
                'akral' => 228,
                'nadi' => 10,
                'td_sistolik' => 6,
                'td_diastolik' => 7,
                'kesadaran' => 51,
                'pupil' => 229,
                'refleks_cahaya' => 230,
                'jenis_anamnesis' => 231,
                'alasan_kunjungan' => 13, // reuse "Keluhan Utama"
                'alasan_kunjungan_lain' => 13,
                'nyeri' => 14,
                'skor_nyeri' => 140,
                'lokasi_nyeri' => 232,
                'frekuensi_nyeri' => 233,
                'karakteristik_nyeri' => 234,
                'riwayat_penyakit_1' => 42,
                'riwayat_penyakit_2' => 42,
                'riwayat_penyakit_3' => 42,
                'riwayat_penyakit_4' => 42,
                'riwayat_penyakit_5' => 42,
                'riwayat_penyakit_6' => 42,
                'riwayat_penyakit_7' => 42,
                'riwayat_penyakit_8' => 42,
                'riwayat_penyakit_lain' => 42,
                'risiko_jatuh' => 91, // reuse "Risiko Jatuh - Tingkat Risiko"
                'prioritas_triase' => 235,
                'catatan' => 77,
            ],
            // ==== Fase P2 — form 22 ====
            22 => [
                'diagnosa_keperawatan_id' => 236, 'nama_diagnosa_keperawatan' => 237, 'tujuan_keperawatan' => 238,
                'satuan_waktu_tujuan' => 239, 'kriteria_hasil' => 240, 'intervensi_keperawatan' => 241,
                'indikasi_keperawatan' => 1, 'objektif_keperawatan' => 2, 'catatan_rencana' => 77,
            ],
            // ==== Fase P2 — form 23 ====
            23 => [
                'masalah_keperawatan_1' => 242, 'faktor_berhubungan_1' => 243, 'masalah_keperawatan_2' => 242,
                'faktor_berhubungan_2' => 243, 'masalah_keperawatan_3' => 242, 'faktor_berhubungan_3' => 243,
                'masalah_keperawatan_4' => 242, 'faktor_berhubungan_4' => 243, 'masalah_keperawatan_5' => 242,
                'faktor_berhubungan_5' => 243, 'masalah_keperawatan_6' => 242, 'faktor_berhubungan_6' => 243,
                'masalah_keperawatan_7' => 242, 'faktor_berhubungan_7' => 243, 'masalah_keperawatan_8' => 242,
                'faktor_berhubungan_8' => 243, 'masalah_keperawatan_9' => 242, 'faktor_berhubungan_9' => 243,
                'masalah_keperawatan_10' => 242, 'faktor_berhubungan_10' => 243,
            ],
            // ==== Fase P2 — form 24 ====
            24 => [
                'total_intake' => 291, 'nama_infus_1' => 292, 'volume_infus_1' => 293,
                'sisa_infus_1' => 294, 'nama_infus_2' => 292, 'volume_infus_2' => 293,
                'sisa_infus_2' => 294, 'nama_infus_3' => 292, 'volume_infus_3' => 293,
                'sisa_infus_3' => 294, 'nama_infus_4' => 292, 'volume_infus_4' => 293,
                'sisa_infus_4' => 294, 'nama_infus_5' => 292, 'volume_infus_5' => 293,
                'sisa_infus_5' => 294, 'nama_infus_6' => 292, 'volume_infus_6' => 293,
                'sisa_infus_6' => 294, 'oral' => 295, 'spoel' => 296,
                'transfusi' => 297, 'syringe_pump_1' => 298, 'syringe_pump_2' => 298,
                'syringe_pump_3' => 298, 'syringe_pump_4' => 298, 'syringe_pump_5' => 298,
                'syringe_pump_6' => 298, 'intake_lainnya' => 299, 'ivfd_1' => 309,
                'ivfd_2' => 309, 'ivfd_3' => 309, 'ivfd_4' => 309,
                'ivfd_5' => 309, 'ivfd_6' => 309, 'catatan' => 77,
            ],
            // ==== Fase P2 — form 25 ====
            25 => [
                'urine' => 300, 'drain' => 301, 'ngt' => 302,
                'wsd' => 303, 'iwl' => 304, 'output_lainnya' => 305,
                'total_output' => 306, 'frekuensi_bab' => 116, 'karakter_bab' => 116,
                'frekuensi_bak' => 116, 'karakter_bak' => 116, 'catatan' => 77,
            ],
            // ==== Fase P2 — form 26 ====
            26 => [
                'tanggal_monitoring' => 155, 'waktu_monitoring' => 156, 'berat_badan' => 8,
                'suhu' => 11, 'nama_infus_1' => 292, 'dosis_infus_1' => 308,
                'volume_infus_1' => 293, 'nama_infus_2' => 292, 'dosis_infus_2' => 308,
                'volume_infus_2' => 293, 'nama_infus_3' => 292, 'dosis_infus_3' => 308,
                'volume_infus_3' => 293, 'nama_infus_4' => 292, 'dosis_infus_4' => 308,
                'volume_infus_4' => 293, 'nama_infus_5' => 292, 'dosis_infus_5' => 308,
                'volume_infus_5' => 293, 'nama_infus_6' => 292, 'dosis_infus_6' => 308,
                'volume_infus_6' => 293, 'sisa_infus_1' => 294, 'sisa_infus_2' => 294,
                'sisa_infus_3' => 294, 'sisa_infus_4' => 294, 'sisa_infus_5' => 294,
                'sisa_infus_6' => 294, 'ivfd_1' => 309, 'ivfd_2' => 309,
                'ivfd_3' => 309, 'ivfd_4' => 309, 'ivfd_5' => 309,
                'ivfd_6' => 309, 'intake_oral' => 295, 'intake_spoel' => 296,
                'intake_transfusi' => 297, 'intake_lainnya' => 299, 'total_intake' => 291,
                'output_urine' => 300, 'output_drain' => 301, 'output_ngt' => 302,
                'output_wsd' => 303, 'output_iwl' => 304, 'output_lainnya' => 305,
                'total_output' => 306, 'kebutuhan_cairan' => 310, 'balance_cairan' => 311,
                'total_intake_24jam' => 307, 'keterangan_monitoring' => 312,
            ],
            // ==== Fase P2 — form 27 ====
            27 => [
                'tanggal_nyeri' => 155, 'waktu_nyeri' => 156, 'nyeri' => 14,
                'metode_skala_nyeri' => 319, 'skor_nyeri' => 140, 'skor_flacc' => 318,
                'frekuensi_nyeri' => 313, 'karakteristik_nyeri' => 314, 'pemicu_nyeri' => 315,
                'meringankan_nyeri' => 316, 'lokasi_nyeri' => 317, 'gambar_lokasi_nyeri' => 326,
                'intervensi_nyeri' => 5, 'catatan' => 77,
            ],
            // ==== Fase P2 — form 28 ====
            28 => [
                'tanggal_kesadaran' => 155, 'waktu_kesadaran' => 156, 'gcs_e' => 54,
                'gcs_m' => 55, 'gcs_v' => 56, 'gcs_jumlah' => 57,
                'kesadaran' => 51, 'dpo' => 59, 'pemberian_o2' => 18,
                'flow_rate' => 157, 'cara_oksigen' => 19, 'ett' => 20,
                'persentase_oksigen' => 20, 'saturasi' => 15, 'keterangan' => 77,
            ],
            // ==== Fase P2 — form 29 ====
            29 => [
                'tanggal_serah_terima' => 244, 'waktu_serah_terima' => 245, 'shift' => 147,
                'jenis_serah_terima' => 40, 'petugas_serah_id' => 246, 'nama_petugas_serah' => 247,
                'petugas_terima_id' => 248, 'nama_petugas_terima' => 249, 'ruang_tujuan' => 250,
                'waktu_konfirmasi_terima' => 251, 'jumlah_pasien_serah_terima' => 252, 'status_kelengkapan_serah_terima' => 253,
                'isi_serah_terima' => 2, 'catatan_serah_terima' => 77,
            ],
            // ==== Fase P2 — form 30 ====
            30 => [
                'tanggal_monitoring_jatuh' => 336, 'waktu_monitoring_jatuh' => 337, 'risiko_jatuh_instrumen' => 89,
                'risiko_jatuh_skor' => 90, 'risiko_jatuh_label' => 91, 'risiko_jatuh_intervensi' => 92,
                'risiko_jatuh_ringkasan' => 93, 'hds_1' => 94, 'hds_2' => 95,
                'hds_3' => 96, 'hds_4' => 97, 'hds_5' => 98,
                'hds_6' => 99, 'hds_7' => 100, 'mfs_1' => 101,
                'mfs_2' => 102, 'mfs_3' => 103, 'mfs_4' => 104,
                'mfs_5' => 105, 'mfs_6' => 106, 'syd_ts_bed' => 107,
                'syd_ts_bangku' => 108, 'syd_ts_bantuan' => 109, 'syd_ms_bantuan' => 110,
                'syd_ms_kursi_roda' => 111, 'syd_ms_imobil' => 112, 'tug_detik' => 113,
                'status_monitoring' => 338, 'kejadian_jatuh' => 339, 'waktu_kejadian_jatuh' => 340,
                'keterangan_kejadian_jatuh' => 341, 'tindak_lanjut_jatuh' => 342, 'intervensi_pencegahan' => 92,
                'berat_badan' => 8, 'kesadaran' => 51, 'catatan' => 77,
            ],
            // ==== Fase P2 — form 31 ====
            31 => [
                'tingkat_pendidikan' => 23, 'kemampuan_bicara' => 254, 'jenis_bahasa' => 255,
                'perlu_penerjemah' => 256, 'nilai_kebudayaan' => 257, 'keterangan_kebudayaan' => 258,
                'kemampuan_baca' => 260, 'hambatan_belajar' => 259, 'kebutuhan_belajar' => 261,
                'sedia_terima_edukasi' => 40, 'alasan_asesmen_ulang' => 77, 'materi_edukasi' => 114,
                'respon_emosi_edukasi' => 3,
            ],
            // ==== Fase P2 — form 32 ====
            32 => [
                'tanggal_norton' => 155, 'waktu_norton' => 156, 'norton_kondisi_fisik_umum' => 343,
                'norton_kesadaran' => 344, 'norton_aktivitas' => 345, 'norton_mobilitas' => 346,
                'norton_inkontinensia' => 347, 'skor_norton' => 348, 'kategori_dekubitus' => 349,
                'kesadaran' => 51, 'catatan' => 77,
            ],
            // ==== Fase P2 — form 33 ====
            33 => [
                'tanggal_barthel' => 155, 'waktu_barthel' => 156, 'barthel_transfer' => 350,
                'barthel_mobilisasi' => 351, 'barthel_toilet' => 352, 'barthel_bersih' => 353,
                'barthel_bab' => 354, 'barthel_bak' => 355, 'barthel_mandi' => 356,
                'barthel_berpakaian' => 357, 'barthel_makan' => 358, 'barthel_tangga' => 359,
                'barthel_skor' => 360, 'tingkat_ketergantungan' => 361, 'catatan' => 77,
            ],
            // ==== Fase P2 — form 34 ====
            34 => [
                'sf_makan' => 262, 'sf_mandi' => 263, 'sf_grooming' => 264,
                'sf_berpakaian' => 265, 'sf_buang_air_kecil' => 266, 'sf_buang_air_besar' => 267,
                'sf_toilet' => 268, 'sf_transfer' => 269, 'sf_mobilitas' => 270,
                'sf_tangga' => 271, 'sf_skor' => 272, 'sf_interpretasi' => 273,
            ],
            // ==== Fase P2 — form 35 ====
            35 => [
                'asal_masuk' => 274, 'asal_masuk_detail' => 275, 'diagnosa_medis_kritis' => 40,
                'jalan_nafas' => 276, 'deviasi_trakea' => 277, 'pernapasan_kritis' => 12,
                'suara_nafas_terdengar' => 278, 'sirkulasi_perfusi' => 279, 'kesadaran_kritis' => 51,
                'gcs_e' => 54, 'gcs_m' => 55, 'gcs_v' => 56,
                'gcs_jumlah' => 57, 'skor_nyeri_kritis' => 282, 'nilai_kritis' => 280,
                'eksposisi_kritis' => 2, 'catatan_kritis' => 115, 'masalah_keperawatan_kritis' => 242,
                'dpo' => 59, 'alergi' => 17,
            ],
            // ==== Fase P2 — form 36 ====
            36 => [
                'metode_skala_nyeri' => 319, 'skor_nyeri' => 140, 'flacc_wajah' => 320,
                'flacc_kaki' => 321, 'flacc_aktivitas' => 322, 'flacc_menangis' => 323,
                'flacc_konsolabilitas' => 324, 'skor_flacc' => 325, 'interpretasi_nyeri' => 335,
                'keterangan' => 77,
            ],
            // ==== Fase P2 — form 37 ====
            37 => [
                'tanggal_body_map' => 155, 'waktu_body_map' => 156, 'gambar_lokasi_nyeri' => 326,
                'keterangan_lokasi' => 327, 'sisi_lokasi' => 327, 'catatan' => 77,
            ],
            // ==== Fase P2 — form 38 ====
            38 => [
                'tanggal_nips' => 155, 'waktu_nips' => 156, 'nips_ekspresi_wajah' => 328,
                'nips_tangisan' => 329, 'nips_pola_napas' => 330, 'nips_gerakan_lengan' => 331,
                'nips_gerakan_tungkai' => 332, 'nips_status_jaga' => 333, 'total_nips' => 334,
                'interpretasi_nyeri' => 335, 'catatan' => 77,
            ],
            // ==== Fase P2 — form 54 ====
            // ===== Fase P3 — Gizi & Nutrisi (KONSEP_ASESMEN_GIZI.md) =====
            39 => [
                'tanggal_asesmen' => 362, 'waktu_asesmen' => 363, 'jenis_asesmen' => 364,
                'riwayat_penyakit' => 41, 'riwayat_penyakit_keluarga' => 365, 'diagnosa_medis' => 40,
                'alergi' => 17, 'ketidaksukaan_makanan' => 366, 'pantangan_makan' => 30,
                'pengalaman_diit' => 367, 'asupan_smrs' => 368, 'berat_badan' => 8, 'tinggi_badan' => 9,
                'bmi' => 58, 'z_score' => 369, 'lingkar_lengan' => 370, 'bb_biasanya' => 371,
                'biokimia' => 372, 'nafsu_makan' => 373, 'gigi_geligi' => 374,
                'gangguan_menelan' => 375, 'gangguan_menghisap' => 375, 'gangguan_mengunyah' => 375,
                'fkg_diare' => 376, 'fkg_edema_ascites' => 376, 'fkg_muntah' => 376,
                'fkg_konstipasi' => 376, 'fkg_mual' => 376, 'fkg_hilang_lemak' => 376,
                'td_sistolik' => 6, 'td_diastolik' => 7, 'nadi' => 10, 'suhu' => 11,
                'pernapasan' => 12, 'saturasi' => 15, 'diagnosa_gizi' => 377,
                'kebutuhan_energi' => 378, 'intervensi_gizi' => 379, 'monev' => 380, 'rekomendasi' => 381,
            ],
            40 => [
                'instrumen' => 382, 'skor' => 384, 'kesimpulan' => 385,
                'ahli_gizi_id' => 82, 'tanggal_evaluasi' => 386,
            ],
            42 => [
                'tanggal_pesan' => 388, 'jenis_pesanan' => 389,
                'waktu_pagi' => 390, 'waktu_siang' => 391, 'waktu_sore' => 392,
                'petugas_pagi' => 393, 'petugas_siang' => 394, 'petugas_sore' => 395,
                'prioritas' => 76, 'keterangan' => 77,
            ],
            43 => [
                'jenis_diet' => 396, 'jenis_diagnosa_diet' => 377, 'keterangan' => 77,
            ],
            44 => [
                'assessment' => 3, 'diagnosa_gizi' => 377, 'intervensi' => 379,
                'monitor_asupan_makanan' => 380, 'monitor_hasil_lab' => 380,
                'monitor_berat_badan' => 380, 'evaluasi' => 380, 'instruksi' => 5,
                'tanggal_asesmen' => 362, 'waktu_asesmen' => 363,
            ],
            // ===== Fase P3 — Farmasi (KONSEP_FARMAKASI.md) =====
            45 => [
                'tanggal_terapi' => 397, 'waktu_terapi' => 398,
                'dokter_instruksi_id' => 82, 'keterangan' => 77,
            ],
            46 => [
                'rek_tab_aktif' => 402,
                'pulang_edukasi_aturan_minum' => 407, 'pulang_edukasi_dosis' => 407,
                'pulang_edukasi_cara_penggunaan' => 407, 'pulang_edukasi_penyimpanan' => 407,
                'catatan_rekonsiliasi' => 408,
            ],
            47 => [
                'nomor_resep_id' => 409, 'tanggal_telaah' => 418, 'waktu_telaah' => 419,
                'petugas_id' => 82,
                'telaah_1' => 410, 'telaah_2' => 411, 'telaah_3' => 412, 'telaah_4' => 413,
                'telaah_5' => 414, 'telaah_6' => 415, 'telaah_7' => 416, 'telaah_catatan' => 417,
                'prioritas' => 76, 'keterangan' => 77,
            ],
            48 => [
                'nomor_resep_id' => 409,
                'tobat_1' => 420, 'tobat_2' => 421, 'tobat_3' => 422, 'tobat_4' => 423,
                'tobat_5' => 424, 'tobat_catatan' => 425,
                'petugas_id' => 82, 'tanggal_telaah' => 418, 'waktu_telaah' => 419,
                'keterangan' => 77,
            ],
            // ===== Fase P3 — Order Penunjang Medis (KONSEP_PENUNJANG_MEDIS_LANJUTAN.md) =====
            49 => [
                'tanggal_pemeriksaan' => 432, 'diagnosa_patologi' => 40, 'catatan' => 77,
                'jenis_pesanan' => 433, 'prioritas' => 76,
            ],
            50 => [
                'tanggal_pemeriksaan' => 434, 'diagnosa' => 40, 'catatan' => 77,
                'unit_pemeriksaan' => 436, 'jenis_pemeriksaan' => 435, 'spesimen' => 437,
                'jumlah_spesimen' => 70, 'prioritas' => 76,
            ],
            51 => [
                'tanggal_order' => 438, 'diagnosa_poct' => 40, 'indikasi' => 439, 'catatan' => 77,
                'prioritas' => 76, 'tanggal_hasil_terakhir' => 440, 'waktu_hasil_terakhir' => 441,
                'hasil_terakhir' => 442,
            ],            // ===== Fase P4 — Kebidanan & MCU (hasil tools/gen_p4_seed.php) =====

            59 => [
                'tanggal_asesmen' => 536,
                'jam_asesmen' => 537,
                'dokter_pemeriksa_id' => 538,
                'obstetrik_g' => 539,
                'obstetrik_p' => 540,
                'obstetrik_a' => 541,
                'komplikasi_hyperemesis' => 543,
                'komplikasi_toksemia' => 543,
                'komplikasi_eklampsi' => 543,
                'komplikasi_pph' => 543,
                'riwayat_operasi_pernah' => 544,
                'riwayat_operasi_jenis' => 544,
                'riwayat_operasi_detail' => 544,
                'penyakit_jantung' => 545,
                'penyakit_diabetes' => 545,
                'penyakit_paru' => 545,
                'penyakit_vemerik' => 545,
                'penyakit_lainnya' => 545,
                'penyakit_keterangan' => 545,
                'program_kb_pernah' => 546,
                'program_kb_jenis' => 546,
                'program_kb_sejak' => 546,
                'menarche' => 546,
                'haid_terakhir' => 546,
                'haid_lama' => 546,
                'haid_sebelumnya' => 546,
                'haid_lama_2' => 546,
                'siklus_haid' => 546,
                'dismenore' => 546,
                'dismenore_sejak' => 546,
                'keteraturan_haid' => 546,
                'keluhan' => 13,
                'riwayat_obst_tempat_1' => 542,
                'riwayat_obst_tempat_2' => 542,
                'riwayat_obst_tempat_3' => 542,
                'riwayat_obst_tempat_4' => 542,
                'riwayat_obst_tempat_5' => 542,
                'riwayat_obst_tahun_1' => 542,
                'riwayat_obst_tahun_2' => 542,
                'riwayat_obst_tahun_3' => 542,
                'riwayat_obst_tahun_4' => 542,
                'riwayat_obst_tahun_5' => 542,
                'riwayat_obst_kehamilan_1' => 542,
                'riwayat_obst_kehamilan_2' => 542,
                'riwayat_obst_kehamilan_3' => 542,
                'riwayat_obst_kehamilan_4' => 542,
                'riwayat_obst_kehamilan_5' => 542,
                'riwayat_obst_jenis_persalinan_1' => 542,
                'riwayat_obst_jenis_persalinan_2' => 542,
                'riwayat_obst_jenis_persalinan_3' => 542,
                'riwayat_obst_jenis_persalinan_4' => 542,
                'riwayat_obst_jenis_persalinan_5' => 542,
                'riwayat_obst_penyulit_1' => 542,
                'riwayat_obst_penyulit_2' => 542,
                'riwayat_obst_penyulit_3' => 542,
                'riwayat_obst_penyulit_4' => 542,
                'riwayat_obst_penyulit_5' => 542,
                'riwayat_obst_nifas_1' => 542,
                'riwayat_obst_nifas_2' => 542,
                'riwayat_obst_nifas_3' => 542,
                'riwayat_obst_nifas_4' => 542,
                'riwayat_obst_nifas_5' => 542,
                'riwayat_obst_sex_1' => 542,
                'riwayat_obst_sex_2' => 542,
                'riwayat_obst_sex_3' => 542,
                'riwayat_obst_sex_4' => 542,
                'riwayat_obst_sex_5' => 542,
                'riwayat_obst_bb_1' => 542,
                'riwayat_obst_bb_2' => 542,
                'riwayat_obst_bb_3' => 542,
                'riwayat_obst_bb_4' => 542,
                'riwayat_obst_bb_5' => 542,
                'riwayat_obst_keadaan_1' => 542,
                'riwayat_obst_keadaan_2' => 542,
                'riwayat_obst_keadaan_3' => 542,
                'riwayat_obst_keadaan_4' => 542,
                'riwayat_obst_keadaan_5' => 542,
            ],
            60 => [
                'tanggal_pengkajian' => 547,
                'jam_pengkajian' => 548,
                'nama_bayi' => 549,
                'tanggal_lahir_bayi' => 549,
                'jam_lahir_bayi' => 549,
                'jenis_kelamin_bayi' => 549,
                'berat_badan_lahir' => 8,
                'tinggi_badan_lahir' => 9,
                'lingkar_kepala_bayi' => 549,
                'denyut_jantung_bayi' => 10,
                'pernapasan_bayi' => 12,
                'suhu_rectal_bayi' => 549,
                'tanda_pengenal_bayi' => 549,
                'tempat_lahir_bayi' => 549,
                'nama_ibu' => 550,
                'umur_ibu' => 550,
                'agama_ibu' => 21,
                'pendidikan_ibu' => 23,
                'pekerjaan_ibu' => 24,
                'alamat_ibu' => 550,
                'nama_ayah' => 551,
                'umur_ayah' => 551,
                'agama_ayah' => 21,
                'pendidikan_ayah' => 23,
                'pekerjaan_ayah' => 24,
                'tinggi_ibu' => 552,
                'berat_ibu' => 552,
                'presentasi_bayi' => 552,
                'antenatal_care' => 553,
                'lokasi_antenatal_care' => 553,
                'hpht' => 555,
                'taksiran_partus' => 556,
                'imunisasi_hamil' => 557,
                'penyakit_hamil_anemia' => 558,
                'penyakit_hamil_jantung' => 558,
                'penyakit_hamil_hipertensi' => 558,
                'penyakit_hamil_tb' => 558,
                'penyakit_hamil_diabetes' => 558,
                'penyakit_hamil_sifilis' => 558,
                'penyakit_hamil_lain' => 558,
                'penyakit_hamil_lainnya' => 558,
                'komplikasi_pendarahan' => 559,
                'komplikasi_peb' => 559,
                'komplikasi_infeksi' => 559,
                'komplikasi_disproporsi' => 559,
                'komplikasi_lain' => 559,
                'komplikasi_lainnya' => 559,
                'golongan_darah_ibu' => 560,
                'hb_ibu' => 560,
                'leukosit_ibu' => 560,
                'gula_darah_ibu' => 560,
                'urine_lengkap_ibu' => 560,
                'usg_ibu_1' => 554,
                'usg_ibu_2' => 554,
                'usg_ibu_3' => 554,
                'usg_ibu_4' => 554,
                'usg_ibu_5' => 554,
                'kepala_1' => 561,
                'kepala_2' => 561,
                'kepala_3' => 561,
                'kepala_4' => 561,
                'kepala_5' => 561,
                'ubun_ubun_1' => 561,
                'ubun_ubun_2' => 561,
                'ubun_ubun_3' => 561,
                'mata_1' => 561,
                'mata_2' => 561,
                'mata_3' => 561,
                'mata_4' => 561,
                'mata_5' => 561,
                'telinga_1' => 561,
                'telinga_2' => 561,
                'telinga_3' => 561,
                'mulut_1' => 561,
                'mulut_2' => 561,
                'mulut_3' => 561,
                'mulut_4' => 561,
                'mulut_5' => 561,
                'hidung_1' => 561,
                'hidung_2' => 561,
                'hidung_3' => 561,
                'hidung_4' => 561,
                'leher_1' => 561,
                'leher_2' => 561,
                'leher_3' => 561,
                'menangis_1' => 561,
                'menangis_2' => 561,
                'menangis_3' => 561,
                'punggung_1' => 561,
                'punggung_2' => 561,
                'punggung_3' => 561,
                'punggung_4' => 561,
                'punggung_5' => 561,
                'punggung_6' => 561,
                'anus_1' => 561,
                'anus_2' => 561,
                'anus_3' => 561,
                'genitalia_pria_1' => 561,
                'genitalia_pria_2' => 561,
                'genitalia_pria_3' => 561,
                'genitalia_pria_4' => 561,
                'genitalia_pria_5' => 561,
                'keluaran_genitalia_1' => 561,
                'keluaran_genitalia_2' => 561,
                'keluaran_genitalia_3' => 561,
                'kepala_lain' => 561,
                'telinga_lain' => 561,
                'mulut_lain' => 561,
                'punggung_lain' => 561,
                'anus_lain' => 561,
                'genitalia_pria_lain' => 561,
                'warna_kulit' => 561,
                'pergerakan_bayi' => 561,
                'meconium' => 561,
                'dada_bayi' => 561,
                'perut_bayi' => 561,
                'umbilikal' => 561,
                'bising_usus' => 561,
                'jari_tangan' => 561,
                'pergerakan_tangan' => 561,
                'jari_kaki' => 561,
                'pergerakan_kaki' => 561,
                'garis_tangan' => 561,
                'tangan_keriput' => 561,
                'tangan_terkelupas' => 561,
                'garis_kaki' => 561,
                'kaki_keriput' => 561,
                'kaki_terkelupas' => 561,
                'kuku' => 561,
                'labio_mayora' => 561,
                'labio_minora' => 561,
                'tgl_bab_pertama' => 561,
                'jam_bab_pertama' => 561,
                'tgl_bak_pertama' => 561,
                'jam_bak_pertama' => 561,
            ],
            61 => [
                'jenis_pengkajian' => 562,

                'agama' => 21, 'kegiatan_ibadah' => 22, 'tingkat_pendidikan' => 23, 'pekerjaan' => 24,
                'suku_bangsa' => 25, 'kebangsaan' => 26, 'handphone' => 60,
                'nama_pasangan' => 31, 'usia_pasangan' => 32, 'pendidikan_pasangan' => 33,
                'pekerjaan_pasangan' => 34, 'suku_bangsa_pasangan' => 35, 'kebangsaan_pasangan' => 36,
                'tinggal_bersama' => 37, 'penanggung_jawab_pasien' => 38, 'hubungan_pasien' => 39,
                'aktifitas_sebelum_makan' => 27, 'pantangan_pulang' => 28,
                'pantangan_transfusi_darah' => 29, 'pantangan_makan' => 30,
                'diagnosa_medis' => 40, 'keluhan' => 13,
                'riwayat_penyakit_sebelumnya' => 41, 'riwayat_penyakit_sekarang' => 42,
                'infeksius_flag' => 43, 'menular_melalui' => 44,
                'infeksius_memerlukan_isolasi' => 45, 'infeksius_hasil_penunjang' => 46,
                'imunologi_flag' => 47, 'imunologi_memerlukan_isolasi' => 48,
                'imunologi_pembatasan_pengunjung' => 49, 'imunologi_hasil_penunjang' => 50,
                'vaksin_covid' => 62, 'tanggal_covid_1' => 62, 'tanggal_covid_2' => 62,
                'riw_ope_kemo' => 61, 'riwayat_operasi' => 61,
                'riwayat_kemoterapi' => 52, 'riwayat_radioterapi' => 53,
                'kesadaran' => 51, 'gcs_e' => 54, 'gcs_m' => 55, 'gcs_v' => 56, 'gcs_jumlah' => 57,
                'dpo' => 59, 'td' => 6, 'nadi' => 10, 'suhu' => 11, 'pernapasan' => 12,
                'berat_badan' => 8, 'tinggi_badan' => 9, 'pemberian_o2' => 18,
                'cara_pemberian_o2' => 19, 'ett' => 20, 'saturasi' => 15, 'ews' => 16,
                'allo_anamnesa' => 63, 'nama_allo' => 64, 'hubungan_allo' => 65, 'bmi' => 58,
                'nyeri' => 14, 'alergi' => 17,
                // Pengkajian risiko jatuh (instrumen dipilih dari usia, lihat
                // App\Helpers\RisikoJatuhHelper). Objek 66-68 (UP GO lama) tidak
                // lagi dipakai form ini; mapping lamanya dibersihkan di bawah.
                'risiko_jatuh_instrumen' => 89, 'risiko_jatuh_skor' => 90,
                'risiko_jatuh_label' => 91, 'risiko_jatuh_intervensi' => 92,
                'risiko_jatuh_ringkasan' => 93,
                // HDS (anak)
                'hds_1' => 94, 'hds_2' => 95, 'hds_3' => 96, 'hds_4' => 97,
                'hds_5' => 98, 'hds_6' => 99, 'hds_7' => 100,
                // Morse Fall Scale (dewasa)
                'mfs_1' => 101, 'mfs_2' => 102, 'mfs_3' => 103,
                'mfs_4' => 104, 'mfs_5' => 105, 'mfs_6' => 106,
                // Sydney Scoring (alternatif)
                'syd_ts_bed' => 107, 'syd_ts_bangku' => 108, 'syd_ts_bantuan' => 109,
                'syd_ms_bantuan' => 110, 'syd_ms_kursi_roda' => 111, 'syd_ms_imobil' => 112,
                // Timed Up and Go (lansia)
                'tug_detik' => 113,
            ],
            62 => [
                'kala' => 563,
                'tanggal_kala' => 564,
                'jam_kala' => 565,
                'pemeriksaan_dalam' => 568,
                'his' => 569,
                'djj' => 570,
                'masalah_kala_1' => 566,
                'masalah_kala_detail_1' => 566,
                'penatalaksanaan_kala_1' => 567,
                'masalah_kala_2' => 566,
                'masalah_kala_detail_2' => 566,
                'penatalaksanaan_kala_2' => 567,
                'masalah_kala_3' => 566,
                'masalah_kala_detail_3' => 566,
                'penatalaksanaan_kala_3' => 567,
                'masalah_kala_4' => 566,
                'masalah_kala_detail_4' => 566,
                'penatalaksanaan_kala_4' => 567,
            ],
            63 => [
                'penolong_persalinan' => 571,
                'penolong_persalinan_lain' => 571,
                'durasi_kala_1' => 572,
                'durasi_kala_2' => 573,
                'durasi_kala_3' => 574,
                'ketuban' => 575,
                'warna_ketuban' => 575,
                'ketuban_lain' => 575,
                'jam_persalinan_kala_2' => 576,
                'tanggal_lahir_bayi' => 576,
                'jenis_kelamin_kala_2' => 576,
                'as_bayi' => 576,
                'berat_badan_kala_2' => 8,
                'tinggi_badan_kala_2' => 9,
                'lingkar_kepala_kala_2' => 576,
                'kelainan_bayi' => 576,
                'plasenta_lahir' => 577,
                'infark_plasenta' => 577,
                'keadaan_umum_ibu' => 578,
                'kehilangan_darah' => 578,
            ],
            64 => [
                'penilaian_bayi_baru_lahir' => 579,
                'penilaian_bayi_baru_lahir_ya' => 580,
                'waktu_penilaian_bayi' => 580,
                'alasan_tidak_penilaian' => 580,
                'tindakan_bayi_normal_1' => 581,
                'tindakan_bayi_normal_2' => 581,
                'tindakan_bayi_normal_3' => 581,
                'tindakan_bayi_normal_4' => 581,
                'tindakan_bayi_abnormal_1' => 582,
                'tindakan_bayi_abnormal_2' => 582,
                'tindakan_bayi_abnormal_3' => 582,
                'tindakan_bayi_abnormal_4' => 582,
                'tindakan_bayi_abnormal_5' => 582,
                'tindakan_bayi_abnormal_6' => 582,
                'tindakan_bayi_abnormal_7' => 582,
                'tindakan_bayi_abnormal_8' => 582,
                'tindakan_bayi_abnormal_9' => 582,
            ],
            65 => [
                'riwayat_resusitasi' => 583,
                'peep_cpap' => 585,
                'flow_cpap' => 585,
                'fio2_cpap' => 585,
                'durasi_cpap' => 585,
                'catatan_resusitasi' => 586,
                'tindakan_resusitasi_1' => 584,
                'tindakan_resusitasi_2' => 584,
                'tindakan_resusitasi_3' => 584,
                'tindakan_resusitasi_4' => 584,
                'tindakan_resusitasi_5' => 584,
                'tindakan_resusitasi_6' => 584,
            ],
            66 => [
                'apgar_frekuensi_jantung_1' => 587,
                'apgar_frekuensi_jantung_5' => 587,
                'apgar_frekuensi_jantung_10' => 587,
                'apgar_frekuensi_jantung_15' => 587,
                'apgar_frekuensi_jantung_20' => 587,
                'apgar_usaha_napas_1' => 588,
                'apgar_usaha_napas_5' => 588,
                'apgar_usaha_napas_10' => 588,
                'apgar_usaha_napas_15' => 588,
                'apgar_usaha_napas_20' => 588,
                'apgar_tonus_otot_1' => 589,
                'apgar_tonus_otot_5' => 589,
                'apgar_tonus_otot_10' => 589,
                'apgar_tonus_otot_15' => 589,
                'apgar_tonus_otot_20' => 589,
                'apgar_refleks_1' => 590,
                'apgar_refleks_5' => 590,
                'apgar_refleks_10' => 590,
                'apgar_refleks_15' => 590,
                'apgar_refleks_20' => 590,
                'apgar_warna_1' => 591,
                'apgar_warna_5' => 591,
                'apgar_warna_10' => 591,
                'apgar_warna_15' => 591,
                'apgar_warna_20' => 591,
                'apgar_total_1' => 592,
                'apgar_total_5' => 592,
                'apgar_total_10' => 592,
                'apgar_total_15' => 592,
                'apgar_total_20' => 592,
            ],
            67 => [
                'down_frekuensi_nafas' => 593,
                'down_retraksi' => 594,
                'down_sianosis' => 595,
                'down_air_entry' => 596,
                'down_merintih' => 597,
                'down_total' => 598,
                'down_interpretasi' => 598,
            ],
            68 => [
                'inisisasi_menyususui_dini' => 599,
                'waktu_menyusui_dini' => 599,
                'durasi_menyusui_dini' => 599,
                'alasan_tidak_menyusui_dini' => 599,
            ],
            69 => [
                'paraf_skrining_1' => 606,
                'paraf_skrining_2' => 606,
                'paraf_skrining_3' => 606,
                'paraf_skrining_4' => 606,
                'paraf_skrining_5' => 606,
                'forma_plan_1' => 607,
                'forma_paraf_1' => 608,
                'forma_plan_2' => 607,
                'forma_paraf_2' => 608,
                'forma_plan_3' => 607,
                'forma_paraf_3' => 608,
                'forma_plan_4' => 607,
                'forma_paraf_4' => 608,
                'forma_plan_5' => 607,
                'forma_paraf_5' => 608,
                'forma_plan_6' => 607,
                'forma_paraf_6' => 608,
                'formb_waktu_1' => 609,
                'formb_waktu_2' => 609,
                'formb_waktu_3' => 609,
                'formb_waktu_4' => 609,
                'formb_waktu_5' => 609,
            ],
            70 => [
                'tanggal_kesimpulan' => 611,
                'jam_kesimpulan' => 612,
                'interpretasi' => 614,
                'kesimpulan_kategori' => 613,
                'kesimpulan_catatan' => 615,
                'rekomendasi' => 616,
                'dokter_pemeriksa_id' => 82,
            ],
            71 => [
                'tanggal_ttv' => 617,
                'jam_ttv' => 618,
                'keluhan_saat_ini' => 619,
                'riwayat_penyakit' => 620,
                'riwayat_keluarga_ayah' => 621,
                'riwayat_keluarga_ibu' => 621,
                'riwayat_keluarga_lain' => 621,
                'riwayat_merokok' => 622,
                'riwayat_merokok_detail' => 622,
                'riwayat_alkohol' => 622,
                'riwayat_alkohol_detail' => 622,
                'riwayat_olahraga' => 622,
                'riwayat_olahraga_detail' => 622,
                'riwayat_alergi' => 623,
                'jenis_pekerjaan' => 624,
                'sistolik' => 6,
                'diastolik' => 7,
                'nadi' => 10,
                'pernapasan' => 12,
                'suhu' => 11,
                'berat_badan' => 8,
                'tinggi_badan' => 9,
                'bmi' => 58,
                'lingkar_perut' => 624,
                'kategori_bmi' => 624,
            ],
            72 => [
                'visus_od_avod' => 625,
                'visus_od_c' => 625,
                'visus_od_add' => 625,
                'visus_od_tod' => 625,
                'visus_od_tos' => 625,
                'visus_os_avos' => 626,
                'visus_os_c' => 626,
                'visus_os_add' => 626,
                'visus_os_pd' => 626,
                'tolak' => 627,
                'tes_konfrontasi' => 628,
                'buta_warna' => 628,
            ],
            73 => [
                'jenis_kelamin_pasien' => 629,
                'menarche' => 629,
                'haid_terakhir' => 629,
                'siklus_haid' => 629,
                'nyeri_haid' => 629,
                'kb_metode' => 630,
                'kb_aktif' => 630,
                'kehamilan_g' => 630,
                'kehamilan_p' => 630,
                'kehamilan_a' => 630,
                'keguguran_terakhir' => 630,
                'keguguran_keterangan' => 631,
                'hamil_terakhir' => 631,
                'hamil_terakhir_lama' => 631,
                'jml_anak_lahir' => 631,
                'jml_anak_hidup' => 631,
                'waktu_paps' => 632,
                'sc' => 632,
                'coitus' => 632,
                'coitus_terakhir' => 632,
                'discharge' => 632,
                'gatal' => 632,
                'hari' => 632,
                'hormon' => 632,
                'normal' => 632,
            ],
            74 => [
                'keluhan_saat_ini' => 13,
                'keluhan_uraian' => 633,
                'riwayat_penyakit' => 633,
                'kepala_ada' => 634,
                'kepala' => 634,
                'kulit_ada' => 634,
                'kulit' => 634,
                'mata_ada' => 634,
                'mata' => 634,
                'mulut_ada' => 634,
                'mulut' => 634,
                'hidung_ada' => 634,
                'hidung' => 634,
                'tenggorokan_ada' => 634,
                'tenggorokan' => 634,
                'telinga_ada' => 634,
                'telinga' => 634,
                'leher_ada' => 634,
                'leher' => 634,
                'mamae_ada' => 634,
                'mamae' => 634,
                'thorax_ada' => 635,
                'thorax' => 635,
                'jantung_ada' => 635,
                'jantung' => 635,
                'paru_ada' => 635,
                'paru' => 635,
                'abdomen_ada' => 636,
                'abdomen' => 636,
                'ekstremitas_ada' => 636,
                'ekstremitas' => 636,
                'lain_ada' => 636,
                'lain' => 636,
                'diagnosa' => 40,
                'rekomendasi' => 77,
            ],
            75 => [
                'keluhan_saat_ini' => 13,
                'jantung_ada' => 637,
                'jantung' => 637,
                'ekg_hr' => 638,
                'ekg_rhythm' => 638,
                'ekg_kelainan' => 638,
                'ekg_kesimpulan' => 638,
                'threadmill_case' => 639,
                'threadmill_kesimpulan' => 639,
                'echocardiography' => 640,
                'echocardiography_kesimpulan' => 640,
                'diagnosa' => 40,
                'rekomendasi' => 77,
            ],
            76 => [
                'keluhan_saat_ini' => 13,
                'telinga_kanan_ada' => 641,
                'telinga_kanan' => 641,
                'telinga_kiri_ada' => 641,
                'telinga_kiri' => 641,
                'hidung_ada' => 642,
                'hidung' => 642,
                'tenggorokan_ada' => 643,
                'tenggorokan' => 643,
                'tht_lainnya_ada' => 644,
                'tht_lainnya' => 644,
                'diagnosa' => 40,
                'rekomendasi' => 77,
            ],
            77 => [
                'keluhan_saat_ini' => 13,
                'visus_od' => 645,
                'mata_kanan_ada' => 645,
                'mata_kanan' => 645,
                'visus_os' => 646,
                'mata_kiri_ada' => 646,
                'mata_kiri' => 646,
                'kelainan_mata_lainnya_ada' => 647,
                'kelainan_mata_lainnya' => 647,
                'diagnosa' => 40,
                'rekomendasi' => 77,
            ],
            78 => [
                'keluhan_saat_ini' => 13,
                'gigi_ada' => 649,
                'gigi' => 649,
                'kebutuhan_perawatan_ada' => 650,
                'kebutuhan_perawatan' => 650,
                'diagnosa' => 40,
                'rekomendasi' => 77,
            ],
            79 => [
                'keluhan_saat_ini' => 13,
                'lokalis_ada' => 652,
                'lokalis' => 652,
                'diagnosa' => 40,
                'rekomendasi' => 77,
            ],
            80 => [
                'keluhan_saat_ini' => 13,
                'lokalis_ada' => 655,
                'lokalis' => 655,
                'diagnosa' => 40,
                'rekomendasi' => 77,
            ],
            81 => [
                'keluhan_saat_ini' => 13,
                'neurology_ada' => 658,
                'neurology' => 658,
                'diagnosa' => 40,
                'rekomendasi' => 77,
            ],
            82 => [
                'tinggi_badan' => 9,
                'berat_badan' => 8,
                'bmi' => 58,
                'kategori_bmi' => 58,
                'lingkar_lengan_atas' => 661,
                'status_gizi' => 661,
                'pola_diet' => 662,
                'risiko_gizi' => 662,
                'diagnosa' => 40,
                'rekomendasi' => 77,
            ],
            83 => [
                'keluhan_saat_ini' => 13,
                'fisik_ada' => 664,
                'fisik' => 664,
                'diagnosa' => 40,
                'rekomendasi' => 77,
            ],
            84 => [
                'gangguan_kehidupan_ada' => 667,
                'gangguan_kehidupan' => 667,
                'skala_cemas' => 668,
                'skala_depresi' => 668,
                'interpretasi' => 669,
                'saran' => 670,
            ],
            54 => [
                'keluhan' => 114, 'kesadaran' => 51, 'td_sistolik' => 6,
                'td_diastolik' => 7, 'nadi' => 10, 'pernapasan' => 12,
                'suhu' => 11, 'saturasi' => 15, 'catatan_keperawatan' => 115,
                'alergi' => 17, 'tanggal_catatan' => 155, 'waktu_catatan' => 156,
            ],
            // ==== Fase P2 — form 55 ====
            55 => [
                'diagnosa_medis' => 40, 'keluhan' => 13, 'riwayat_penyakit_sebelumnya' => 41,
                'riwayat_penyakit_sekarang' => 42, 'infeksius_flag' => 43, 'menular_melalui' => 44,
                'infeksius_memerlukan_isolasi' => 45, 'infeksius_hasil_penunjang' => 46, 'imunologi_flag' => 47,
                'imunologi_memerlukan_isolasi' => 48, 'imunologi_pembatasan_pengunjung' => 49, 'imunologi_hasil_penunjang' => 50,
                'riwayat_kemoterapi' => 52, 'riwayat_radioterapi' => 53, 'riwayat_operasi' => 61,
                'vaksin_covid' => 62, 'alergi' => 17, 'riwayat_perawatan_berulang' => 281,
                'catatan_riwayat' => 77,
            ],
            // ==== Fase P2 — form 56 ====
            56 => [
                'penyakit_keluarga' => 283, 'hubungan_keluarga' => 284, 'catatan_keluarga' => 77,
            ],
            // ==== Fase P2 — form 123 ====
            123 => [
                'tanggal_asesmen_akhir' => 137, 'waktu_asesmen_akhir' => 138, 'waktu_asesmen_awal' => 285,
                'waktu_asesmen_akhir_hari' => 286, 'kesadaran_terminal' => 51, 'nyeri_terakhir' => 14,
                'skor_nyeri' => 140, 'gejala_terminal' => 115, 'tindakan_paliatif' => 77,
                'catatan_keluarga' => 120,
            ],
            // ==== Fase P2 — form 124 ====
            124 => [
                'tanggal_pelayanan' => 155, 'waktu_pelayanan' => 156, 'subjective' => 1,
                'objective' => 2, 'diagnosis_fungsi' => 3, 'goal' => 4,
                'diagnosis_medis' => 40, 'instruksi' => 5, 'evaluasi' => 77,
                'edukasi_anjuran' => 77, 'frekuensi_kunjungan' => 77,
            ],
            // ==== Fase P2 — form 125 ====
            125 => [
                'respon_emosi' => 287, 'status_mental' => 288, 'kebutuhan_perlindungan' => 289,
                'kebutuhan_teridentifikasi' => 290,
            ],
            // ==== Fase P5 — form 52 Kriteria Masuk ICU ====
            // `dpjp` (objek 453) TIDAK di-mapping: hanya tampil read-only dari
            // penanggung_rawat, tidak disimpan (KONSEP §3.2).
            52 => [
                'tanggal_kriteria' => 447, 'waktu_kriteria' => 448, 'diagnosa' => 40,
                'dpjp_icu' => 454, 'td_nilai' => 449, 'td_status' => 450,
                'gcs_e' => 54, 'gcs_m' => 55, 'gcs_v' => 56, 'gcs_jumlah' => 57,
                'informed_consent' => 455, 'prioritas_icu' => 456, 'kontra_indikasi' => 457,
                'jumlah_kriteria_terpenuhi' => 451, 'kesimpulan' => 452, 'catatan' => 77,
            ],
            // ==== Fase P5 — form 53 Kriteria Keluar ICU ====
            53 => [
                'tanggal_keluar_icu' => 458, 'waktu_keluar_icu' => 459, 'diagnosa_keluar_icu' => 460,
                'dokter_konsultan_icu' => 461, 'kr1_status' => 471, 'kr2_status' => 463,
                'kr3_tensi' => 464, 'kr3_nadi' => 465, 'kr3_rr' => 466, 'kr3_suhu' => 467,
                'kr3_gcs' => 57, 'kr3_cvp' => 468, 'kr3_status' => 471,
                'kr4_status' => 471, 'kr4_lain' => 469, 'kesimpulan' => 470, 'catatan' => 77,
            ],

            // ==== Fase P5 — form 102 Pre Hemodialisa (KONSEP §5.5, 80 baris) ====
            102 => [
                'tanggal_pertemuan' => 473, 'waktu_pertemuan' => 474,
                'subjective' => 1, 'objective' => 2,
                'bb_awal' => 477, 'berat_badan' => 8, 'bb_kering' => 478,
                'type_hd' => 475, 'riwayat_alergi' => 17, 'dialiser' => 476,
                'td_sistolik' => 6, 'td_diastolik' => 7, 'nadi' => 10,
                'pernapasan' => 12, 'suhu' => 11, 'saturasi' => 15,
                'ews' => 16, 'kesadaran' => 51,
                'gcs_e' => 54, 'gcs_m' => 55, 'gcs_v' => 56, 'gcs_jumlah' => 57,
                'dializer' => 482, 'dialisat' => 483,
                'akses_fistula' => 484, 'akses_graft' => 484, 'akses_sndl' => 484,
                'akses_cdl' => 484, 'akses_cdl_thermo' => 484, 'akses_femoral' => 484,
                'dosis_sirkulasi' => 485, 'dosis_bolus' => 486,
                'dosis_maintenance' => 487, 'tanpa_heparinisasi' => 488, 'prog_bilas' => 489,
                'jam_mulai' => 490, 'jam_selesai' => 491, 'lama_hd' => 492,
                'uf' => 493, 'qb' => 494, 'tbv' => 495,
                'tgl_laborat' => 496, 'jam_laborat' => 497,
                'hb' => 498, 'ureum' => 499, 'creatinine' => 500,
                'hbsag' => 501, 'kalium' => 502, 'sgot' => 503, 'sgpt' => 504,
                'hasil_pemeriksaan_lain' => 508,
                'transfusi' => 505, 'd40' => 506, 'ca_glukonas' => 507,
                'kcl' => 509, 'renxamin' => 510, 'erythropoietin' => 511,
                'riwayat_kesehatan_saat_ini' => 512,
                'komunikasi_ada_kendala' => 513, 'komunikasi_tidak_ada' => 513,
                'komunikasi_lain' => 513, 'komunikasi_keterangan' => 513,
                'perawat_rumah_ada' => 514, 'perawat_rumah_tidak' => 514,
                'perawat_rumah_lain' => 514, 'perawat_rumah_keterangan' => 514,
                'gaduh_kondisi_saat_ini' => 515, 'gaduh_tenang' => 515,
                'gaduh_gelisah' => 515, 'gaduh_takut_tindakan' => 515,
                'gaduh_marah' => 515, 'gaduh_mudah_tersinggung' => 515,
                'diagnosa_keperawatan' => 516, 'planning' => 4, 'evaluasi' => 517,
                'tanggal_hd_berikutnya' => 518, 'lama_hd_rencana' => 479,
                'screening_akses_tanggal' => 480, 'screening_akses_catatan' => 481,
                'keterangan' => 77,
            ],

            // ==== Fase P5 — form 103 Intra Hemodialisa (KONSEP §6.4, 31 baris) ====
            103 => [
                'tanggal_pertemuan' => 473, 'waktu_pertemuan' => 474,
                'data_focus' => 519, 'ku_pasien' => 520,
                'td_sistolik' => 6, 'td_diastolik' => 7,
                'nadi' => 10, 'pernapasan' => 12, 'suhu' => 11, 'saturasi' => 15, 'ews' => 16,
                'berat_badan' => 8, 'bb_kering' => 478, 'bb_sebelumnya' => 521,
                'qb' => 522, 'ufr' => 523, 'ufv' => 524, 'd40' => 525,
                'hb' => 498, 'ureum' => 499, 'creatinine' => 500,
                'hbsag' => 501, 'kalium' => 502, 'sgot' => 503, 'sgpt' => 504,
                'hasil_pemeriksaan_lain' => 508,
                'masalah_keperawatan' => 526, 'tindakan_keperawatan' => 527, 'terapi_obat' => 528,
                'evaluasi' => 517, 'keterangan' => 77,
            ],

            // ==== Fase P5 — form 104 Post Hemodialisa (KONSEP §7.4, 37 baris) ====
            104 => [
                'tanggal_pertemuan' => 473, 'waktu_pertemuan' => 474,
                'data_focus' => 519, 'ku_pasien' => 520,
                'td_sistolik' => 6, 'td_diastolik' => 7,
                'nadi' => 10, 'pernapasan' => 12, 'suhu' => 11, 'saturasi' => 15, 'ews' => 16,
                'berat_badan' => 8, 'bb_kering' => 478, 'bb_sebelumnya' => 521,
                'hb' => 498, 'ureum' => 499, 'creatinine' => 500,
                'hbsag' => 501, 'kalium' => 502, 'sgot' => 503, 'sgpt' => 504,
                'hasil_pemeriksaan_lain' => 508,
                'masalah_keperawatan' => 526, 'tindakan_keperawatan' => 527, 'terapi_obat' => 528,
                'evaluasi' => 517, 'keterangan' => 77,
                'tbv' => 529, 'ufg' => 530, 'kt_v' => 531, 'convective_volume' => 532,
                'transfusi' => 505, 'd40' => 506, 'ca_glukonas' => 507,
                'kcl' => 509, 'renxamin' => 510, 'erythropoietin' => 511,
            ],

            // ==== Fase P5 — form 105 Triage Hemodialisa (KONSEP §8.4, 6 baris) ====
            105 => [
                'keluhan' => 13, 'bb_kering' => 478, 'berat_badan' => 8,
                'care_plan' => 533, 'informed_consent' => 534, 'administrasi' => 535,
            ],

            // ==== Fase P5 — form 85 Asesmen Pra Bedah (KONSEP §3, 25 baris) ====
            // 5 variabel checkbox kebutuhan_edukasi_* (objek 768) memakai nama
            // snake_case — label item edukasi masih provisional (KONSEP §3).
            85 => [
                'tanggal_asesmen' => 762, 'jam_asesmen' => 763, 'dokter_penilai_id' => 764,
                'keluhan' => 13,
                'diagnosa_pra_operasi' => 765, 'temuan_fisik_pra_bedah' => 765,
                'riwayat_alergi' => 17,
                'tanggal_rencana_operasi' => 766, 'jam_rencana_operasi' => 766,
                'jenis_operasi' => 767, 'rencana_tindakan_operasi' => 767,
                'kebutuhan_edukasi_persiapan_mentas' => 768,
                'kebutuhan_edukasi_persiapan_lensa' => 768,
                'kebutuhan_edukasi_puasa' => 768,
                'kebutuhan_edukasi_pemberian_obat' => 768,
                'kebutuhan_edukasi_lainnya' => 768,
                'kebutuhan_edukasi_lain' => 769,
                'hasil_laboratorium' => 770, 'hasil_radiologi' => 771, 'hasil_penunjang_lain' => 771,
                'kemungkinan_komplikasi' => 772, 'rencana_komplikasi' => 772,
                'back_up_plan' => 773,
                'prognosis_dubia_ad_bonam' => 774, 'catatan_prognosis' => 774,
            ],

            // ==== Fase P5 — form 86 Evaluasi Pre-Anestesi & Sedasi (KONSEP §4, 62 baris) ====
            // `pernafasan_2` dari tabel field TIDAK dimapping (hanya catatan
            // "reassign" di KONSEP; blok Mapping §4 tidak memuatnya).
            86 => [
                'tanggal_evaluasi' => 775, 'jam_evaluasi' => 776,
                'dokter_anestesi_id' => 777,
                'nama_pasien' => 778, 'tanggal_lahir' => 778, 'jenis_kelamin' => 778,
                'pekerjaan' => 778, 'riwayat_penyakit_keluarga' => 778, 'riwayat_pengobatan' => 778,
                'jalan_nafas' => 779, 'gangguan_pernafasan' => 779, 'snoring' => 779,
                'pernapasan' => 780, 'penyakit_paru' => 780, 'suhu' => 780, 'td' => 780, 'nadi' => 780,
                'jantung' => 781, 'op_jantung_koroner' => 781, 'serangan_jantung' => 781,
                'hipertensi' => 781, 'riwayat_transfusi' => 781,
                'saraf_perifer' => 782, 'pingsan' => 782, 'kejang' => 782, 'stroke' => 782, 'saraf' => 782,
                'hamil_bulan' => 783, 'diabetes' => 783, 'gangguan_tiroid' => 783,
                'obesitas' => 783, 'gula_darah' => 783,
                'ginjal_gagal' => 784, 'susah_kencing' => 784, 'kreatinin' => 784, 'ureum' => 784,
                'abdomen' => 785, 'maag' => 785, 'muntah' => 785, 'faring' => 785,
                'gigi_geligi' => 785, 'mobilitas' => 785,
                'anemia' => 786, 'leukosit' => 786, 'trombosit' => 786,
                'kadar_gula_darah' => 786, 'prothrombin_time' => 786,
                'alergi_latraks' => 787, 'alergi_makanan' => 787, 'alergi_plaster' => 787,
                'alergi_obat_timbul' => 787,
                'kajar_aspirasi' => 788, 'ceklis_equip_emergi' => 788, 'perlu_alat_bantu' => 788,
                'asa' => 789, 'nyeri' => 14,
                'tipe_anestesia' => 790, 'anes_umum' => 790, 'anes_regional' => 790,
                'sedasi' => 791,
                'persetujuan_anestesi' => 792,
                'catatan_evaluasi' => 793,
            ],

            // ==== Fase P5 — form 87 Asesmen Post-Anestesi & Sedasi (KONSEP §5, 46 baris) ====
            // 4 total skor (alderete/bromage/steward/padss) + kriteria_pemulihan
            // adalah TURUNAN — dihitung ulang oleh SkorPemulihanHelper di server.
            87 => [
                'tanggal_asesmen' => 794, 'jam_asesmen' => 795, 'dokter_anestesi_id' => 796,
                'skor_alderete_kesadaran' => 797, 'skor_alderete_aktivitas' => 797,
                'skor_alderete_ventilasi' => 797, 'skor_alderete_warna_kulit' => 797,
                'skor_alderete_total' => 797,
                'skor_bromage_tangan' => 798, 'skor_bromage_lengan' => 798,
                'skor_bromage_kaki' => 798, 'skor_bromage_total' => 798,
                'skor_steward_pergerakan' => 799, 'skor_steward_pernapasan' => 799,
                'skor_steward_kesadaran' => 799, 'skor_steward_total' => 799,
                'skor_padss_vas' => 800, 'skor_padss_aktivitas' => 800, 'skor_padss_total' => 800,
                'kriteria_pemulihan' => 801,
                'kesadaran' => 51,
                'sistolik' => 6, 'diastolik' => 7, 'nadi' => 10, 'pernapasan' => 12,
                'suhu' => 11, 'saturasi' => 15,
                'nyeri' => 14,
                'komplikasi_1' => 802, 'komplikasi_2' => 802, 'komplikasi_3' => 802,
                'komplikasi_4' => 802, 'komplikasi_5' => 802, 'komplikasi_6' => 802,
                'skala_nyeri' => 802,
                'intake_oral_ada' => 803, 'intake_oral_catatan' => 803,
                'mobilisasi' => 804, 'mobilisasi_catatan' => 804,
                'keadaan_umum' => 805,
                'keselamatan_id' => 806, 'keselamatan_lokasi' => 806, 'keselamatan_site' => 806,
                'ruangan_tujuan' => 807,
                'formulir_tertunda' => 808,
                'rekomendasi' => 809,
            ],
        ];
        // Baris obat/BMHP (maks 20 baris per tindakan): variabel obat_1..obat_20
        // memakai objek 69 (Barang) dan jumlah_1..jumlah_20 memakai objek 70 (Jumlah).
        //
        // Kenapa bersuffix, bukan N baris dengan variabel sama? Karena
        // EmrHelper::emrDetailByVariabel() melakukan pluck('value','variabel'),
        // sehingga N baris untuk variabel yang sama akan tertimpa oleh baris
        // terakhir — item kedua dan seterusnya HILANG saat form dibuka lagi.
        // Dengan suffix, semua key unik sehingga seluruh baris terbaca utuh,
        // dan array_intersect_key() di controller tetap menyisakannya.
        $mappingBarisObat = [];
        for ($baris = 1; $baris <= 20; $baris++) {
            $mappingBarisObat['jenis_barang_'.$baris] = 133; // Obat / BMHP
            $mappingBarisObat['obat_'.$baris] = 69;
            $mappingBarisObat['batch_'.$baris] = 134;
            $mappingBarisObat['jumlah_'.$baris] = 70;
        }
        $mapping[10] = array_merge($mapping[10], $mappingBarisObat);
        // Butir bundle VAP (form 14): 10 variabel, satu per butir, dipetakan ke
        // objek 158-167. Suffiks dipakai dengan alasan yang sama seperti baris
        // obat/BMHP di atas — key HARUS unik agar seluruh butir ikut terbaca
        // saat form dibuka lagi.
        $mappingButirVap = [];
        for ($butir = 1; $butir <= 10; $butir++) {
            $mappingButirVap['vap_'.$butir] = 157 + $butir;
        }
        $mapping[14] = array_merge($mapping[14], $mappingButirVap);
        // ===== Fase P3 — mapping bersuffix =====
        //
        // Alasan suffix sama seperti baris obat/BMHP form 10:
        // EmrHelper::emrDetailByVariabel() melakukan pluck('value','variabel'),
        // sehingga N baris untuk variabel yang sama akan tertimpa baris terakhir.
        //
        // Form 40 — Detail Nutrisi: 4 instrumen (SGA / MNA-SF / MST / MST-Anak).
        // Semua item instrumen dipetakan ke objek 383 ("Jawaban Item Instrumen
        // Gizi"); skor & kesimpulan (384/385) selalu dihitung ulang di server.
        $mappingItemNutrisi = [];
        for ($i = 1; $i <= 5; $i++) {          // SGA/NRS 5 parameter (skor 1-5)
            $mappingItemNutrisi['sga_'.$i] = 383;
        }
        for ($i = 1; $i <= 4; $i++) {          // MNA-SF A-D (skor 0-3)
            $mappingItemNutrisi['mna_'.$i] = 383;
        }
        for ($i = 1; $i <= 15; $i++) {         // MNA diagnosis khusus dewasa
            $mappingItemNutrisi['mna_diag_'.$i] = 383;
        }
        foreach (['mst_a', 'mst_b', 'mst_c', 'mst_d', 'mst_e', 'mst_f1', 'mst_f2'] as $mst) {
            $mappingItemNutrisi[$mst] = 383;  // MST 7 parameter
        }
        for ($i = 1; $i <= 4; $i++) {          // MST-Anak 4 item
            $mappingItemNutrisi['mstak_'.$i] = 383;
        }
        for ($i = 1; $i <= 16; $i++) {         // MST-Anak diagnosis khusus
            $mappingItemNutrisi['mstak_diag_'.$i] = 383;
        }
        $mapping[40] = array_merge($mapping[40], $mappingItemNutrisi);
        // Form 45 — Daftar Terapi Obat: 20 baris obat, tiap baris 9 variabel
        // bersuffix. Hanya barang_id yang disimpan (nama di-resolve dari master).
        $mappingBarisTerapi = [];
        for ($b = 1; $b <= 20; $b++) {
            $mappingBarisTerapi['obat_'.$b] = 69;
            $mappingBarisTerapi['jumlah_'.$b] = 70;
            $mappingBarisTerapi['s_1_'.$b] = 71;
            $mappingBarisTerapi['s_2_'.$b] = 72;
            $mappingBarisTerapi['aturan_pakai_'.$b] = 73;
            $mappingBarisTerapi['rute_'.$b] = 74;
            $mappingBarisTerapi['status_'.$b] = 399;
            $mappingBarisTerapi['alasan_stop_'.$b] = 400;
            $mappingBarisTerapi['jam_masuk_'.$b] = 401;
        }
        $mapping[45] = array_merge($mapping[45], $mappingBarisTerapi);
        // Form 46 — Rekonsiliasi Obat: 3 grup baris x 10 baris
        // (riwayat_ / resep_ / pulang_). Suffiks agar tiap obat punya key unik.
        $mappingBarisRekonsiliasi = [];
        for ($b = 1; $b <= 10; $b++) {
            // Grup riwayat — obat sebelum admisi
            $mappingBarisRekonsiliasi['riwayat_nama_obat_'.$b] = 69;
            $mappingBarisRekonsiliasi['riwayat_rute_'.$b] = 74;
            $mappingBarisRekonsiliasi['riwayat_dosis_'.$b] = 73;
            $mappingBarisRekonsiliasi['riwayat_freq_1_'.$b] = 71;
            $mappingBarisRekonsiliasi['riwayat_freq_2_'.$b] = 72;
            $mappingBarisRekonsiliasi['riwayat_lanjut_'.$b] = 403;
            // Grup resep — obat dari resep/RS saat di rumah
            $mappingBarisRekonsiliasi['resep_nama_obat_'.$b] = 69;
            $mappingBarisRekonsiliasi['resep_rute_'.$b] = 74;
            $mappingBarisRekonsiliasi['resep_dosis_'.$b] = 73;
            $mappingBarisRekonsiliasi['resep_satuan_'.$b] = 404;
            $mappingBarisRekonsiliasi['resep_freq_1_'.$b] = 71;
            $mappingBarisRekonsiliasi['resep_freq_2_'.$b] = 72;
            $mappingBarisRekonsiliasi['resep_dokter_'.$b] = 405;
            $mappingBarisRekonsiliasi['resep_tgl_mulai_'.$b] = 406;
            $mappingBarisRekonsiliasi['resep_keterangan_'.$b] = 77;
            // Grup pulang — obat yang dibawa pulang
            $mappingBarisRekonsiliasi['pulang_nama_obat_'.$b] = 69;
            $mappingBarisRekonsiliasi['pulang_rute_'.$b] = 74;
            $mappingBarisRekonsiliasi['pulang_dosis_'.$b] = 73;
            $mappingBarisRekonsiliasi['pulang_satuan_'.$b] = 404;
            $mappingBarisRekonsiliasi['pulang_freq_1_'.$b] = 71;
            $mappingBarisRekonsiliasi['pulang_freq_2_'.$b] = 72;
            $mappingBarisRekonsiliasi['pulang_dokter_'.$b] = 405;
            $mappingBarisRekonsiliasi['pulang_tgl_mulai_'.$b] = 406;
            $mappingBarisRekonsiliasi['pulang_keterangan_'.$b] = 77;
        }
        $mapping[46] = array_merge($mapping[46], $mappingBarisRekonsiliasi);
        // ===== Fase P4 — Form 69 Skrining MDPK/MPP =====
        //
        // Literal di atas ($mapping[69]) hanya memuat paraf_skrining, forma_*
        // dan formb_waktu_1..5. Seluruh variabel sisanya (tanggal/jam skrining,
        // checkbox risiko, "lainnya", formb_waktu_6..10 + implementasi +
        // evaluasi + paraf) dipetakan lewat loop di sini, mengikuti konsep
        // KONSEP_OBSTETRI_NEONATAL.md §13. Suffiks angka wajib unik.
        $mappingSkriningMpp = [
            'tanggal_skrining' => 155, 'jam_skrining' => 156,
        ];
        for ($i = 1; $i <= 4; $i++) {
            $mappingSkriningMpp['risiko_tinggi_'.$i] = 600;
        }
        for ($i = 1; $i <= 4; $i++) {
            $mappingSkriningMpp['risiko_pembiayaan_'.$i] = 601;
        }
        for ($i = 1; $i <= 4; $i++) {
            $mappingSkriningMpp['kasus_kompleks_'.$i] = 602;
        }
        for ($i = 1; $i <= 2; $i++) {
            $mappingSkriningMpp['risiko_lama_rawat_'.$i] = 603;
        }
        for ($i = 1; $i <= 4; $i++) {
            $mappingSkriningMpp['risiko_psikososial_'.$i] = 604;
        }
        $mappingSkriningMpp['risiko_tinggi_lainnya'] = 605;
        $mappingSkriningMpp['risiko_pembiayaan_lainnya'] = 605;
        $mappingSkriningMpp['kasus_kompleks_lainnya'] = 605;
        $mappingSkriningMpp['risiko_lama_rawat_waktu_lebih'] = 605;
        $mappingSkriningMpp['risiko_psikososial_lainnya'] = 605;
        for ($i = 1; $i <= 10; $i++) {
            $mappingSkriningMpp['formb_waktu_'.$i] = 609;
            $mappingSkriningMpp['formb_implementasi_'.$i] = 609;
            $mappingSkriningMpp['formb_evaluasi_'.$i] = 609;
            $mappingSkriningMpp['formb_paraf_'.$i] = 610;
        }
        $mapping[69] = array_merge($mapping[69], $mappingSkriningMpp);
        // ===== Fase P5 — form 52: variabel bersuffix nilai/status kriteria =====
        // 18 nilai (objek 449) + 18 status (objek 450) berbagi objek — pola
        // "satu objek, banyak variabel" seperti EWS/Nyeri. Suffix wajib
        // (emrDetailByVariabel pluck per-variabel). Grup 1 butir (td,
        // pasca_bedah, obat) memakai nama polos tanpa index (td_nilai dst.)
        // mengikuti KONSEP §3.2.
        $mappingIcu52 = [];
        $jumlahPerGrup = ['resp' => 7, 'td' => 1, 'nadi' => 6, 'ekg' => 2, 'pasca_bedah' => 1, 'obat' => 1];
        foreach ($jumlahPerGrup as $prefix => $n) {
            for ($i = 1; $i <= $n; $i++) {
                $key = $n === 1 ? $prefix : $prefix.'_'.$i;
                $mappingIcu52[$key.'_nilai'] = 449;
                $mappingIcu52[$key.'_status'] = 450;
            }
        }
        $mapping[52] = array_merge($mapping[52], $mappingIcu52);
        // ===== Fase P5 — form 53: 8 butir kelayakan life support (objek 462) =====
        $mappingIcu53 = [];
        foreach (['masker_nrm', 'masker_rm', 'jackson_rees', 'ventilator', 'dopamin', 'dobutamin', 'vascon', 'adrenalin'] as $butir) {
            $mappingIcu53['kr1_'.$butir] = 462;
        }
        $mapping[53] = array_merge($mapping[53], $mappingIcu53);
        // ===== Fase P5 — form 88 Monitoring Anestesi & Sedasi (KONSEP §6, 106 baris) =====
        // Baris monitoring berkala memakai suffix 1..20 (menit 0,5,15,30 wajib,
        // sisanya opsional) — variabel wajib unik per baris.
        $mapping[88] = [
            'tanggal_monitoring' => 810, 'jam_mulai' => 811, 'jam_selesai' => 811,
            'kejadian_kritis' => 817, 'lampiran_monitoring' => 818, 'status_monitoring' => 819,
        ];
        for ($i = 1; $i <= 20; $i++) {
            $mapping[88]['hemodinamik_'.$i] = 812;
            $mapping[88]['suhu_'.$i] = 813;
            $mapping[88]['oksigenasi_'.$i] = 814;
            $mapping[88]['kesadaran_'.$i] = 815;
            $mapping[88]['nyeri_'.$i] = 816;
        }
        // ===== Fase P5 — form 90 Check List Kesiapan Anestesi (KONSEP §8, 9 baris) =====
        $mapping[90] = [
            'tanggal_checklist' => 834, 'jam_checklist' => 835,
            'mesin_anestesi_ok' => 836, 'mesin_anestesi_keterangan' => 836,
            'oksigen_ventilasi_ok' => 837, 'monitor_akses_ok' => 838,
            'farmasi_persiapan_ok' => 839, 'consent_dokumentasi_ok' => 840,
            'catatan_kesiapan' => 841,
        ];
        // ===== Fase P5 — form 91 Pengkajian Pre/Intra/Post Operasi (KONSEP §9, 25 baris) =====
        $mapping[91] = [
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
        ];
        // ===== Fase P5 — form 92 Laporan Operasi (KONSEP §10, 25 baris + loop komplikasi) =====
        $mapping[92] = [
            'tanggal_laporan' => 852, 'jam_mulai_operasi' => 853,
            'jam_selesai_operasi' => 983, 'lama_operasi' => 984,
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
        ];
        for ($i = 1; $i <= 6; $i++) {
            $mapping[92]['komplikasi_operasi_'.$i] = 862;
        }
        // ===== Fase P5 — form 93 Catatan Bedah (KONSEP §11, 28 baris) =====
        $mapping[93] = [
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
        ];
        // ===== Fase P5 — form 94 Transfer Pasien Antar Ruangan (KONSEP §12, 41 baris + loop dokter) =====
        $mapping[94] = [
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
        ];
        for ($i = 1; $i <= 5; $i++) {
            $mapping[94]['dokter_rawat_'.$i] = 889;
        }
        // ===== Fase P5 — form 95 Penandaan Lokasi Operasi (KONSEP §13, 9 var + 1 var tanpa objek) =====
        // `alasan_penandaan` objek_id NULL — field safety gate KONSEP §13
        // ("konfirmasi_pasien = Tidak ⇒ wajib menyertakan alasan") yang
        // terlewat dari tabel field / Mapping §13.
        $mapping[95] = [
            'tanggal_penandaan' => 898, 'jam_penandaan' => 899,
            'nama_tindakan' => 900, 'lokasi_anatomi' => 900, 'sisi_operasi' => 900,
            'jenis_penandaan' => 901, 'lampiran_penandaan' => 901,
            'petugas_penanda' => 902, 'konfirmasi_pasien' => 902,
            'alasan_penandaan' => null,
        ];
        // ===== Fase P5 — form 96 Penandaan Pasien (KONSEP §14, 9 var + loop alergi 1..5) =====
        $mapping[96] = [
            'tanggal_penandaan' => 903, 'jam_penandaan' => 904,
            'penanda_alergi' => 905, 'penanda_hepatitis' => 907, 'jenis_hepatitis' => 907,
            'penanda_hiv' => 908, 'penanda_tb' => 909,
            'penanda_geriatri' => 910, 'alasan_geriatri' => 910,
        ];
        for ($i = 1; $i <= 5; $i++) {
            $mapping[96]['alergi_'.$i] = 906;
        }
        // ===== Fase P5 — form 98 Surveilans Luka Insisi (KONSEP §16, 12 var + loop 2×5) =====
        // `keterangan_trauma` objek_id NULL — field safety gate KONSEP §16
        // ("trauma_pada = Ada ⇒ wajib keterangan") yang terlewat dari tabel field.
        $mapping[98] = [
            'jenis_operasi' => 924, 'kamar_operasi' => 924, 'trauma_pada' => 925,
            'tgl_mrs' => 926, 'tgl_operasi' => 927, 'jam_operasi' => 928, 'lama_operasi' => 929,
            'dokter_bedah' => 930, 'klasifikasi_luka' => 931,
            'kondisi_luka_post' => 934, 'drainase_luka' => 935, 'keterangan_surveilans' => 936,
            'keterangan_trauma' => null,
        ];
        for ($i = 1; $i <= 5; $i++) {
            $mapping[98]['profilaksis_'.$i] = 932;
            $mapping[98]['tanda_infeksi_'.$i] = 933;
        }
        // ===== Fase P5 — form 99 Catatan Anestesia (KONSEP §17, 32 var) =====
        // Variabel ikut blok Mapping — `ruang_tujuan` (bukan `pesakit_ruang`
        // yang tertulis di tabel field §17, preseden form 87/94).
        $mapping[99] = [
            'tanggal_catatan' => 937, 'jam_mulai' => 938, 'jam_selesai' => 939, 'lama_anestesia' => 940,
            'dokter_anestesi' => 941, 'perawat_anestesi' => 942, 'jenis_anestesi' => 943,
            'airway' => 944, 'airway_2' => 944, 'ventilasi' => 944,
            'sirkuit' => 945, 'caudel' => 945,
            'posisi' => 946, 'monitor' => 947, 'asa' => 947,
            'penunjang_lab' => 948, 'riwayat_penyakit' => 949, 'saraf_perifer' => 949,
            'vaskular' => 950, 'obat_cairan_intraop' => 950,
            'sistolik' => 6, 'diastolik' => 7, 'nadi' => 10, 'pernapasan' => 12, 'suhu' => 11,
            'saturasi' => 15, 'kesadaran' => 51,
            'kejadian_tidak_terduga' => 951, 'penanganan_intraop' => 952, 'kondisi_akhir' => 953,
            'instruksi_pasca' => 954, 'ruang_tujuan' => 954,
        ];
        // ===== Fase P5 — form 101 Asesmen Katarak (KONSEP §19, 62 var) =====
        $mapping[101] = [
            'tanggal_asesmen' => 968, 'jam_asesmen' => 969, 'dpjp_utama' => 970,
            'keluhan' => 13, 'kesadaran' => 51,
            'resiko_jatuh' => 971, 'edukasi' => 971,
            'diagnosa_keperawatan' => 972, 'tujuan_kriteria_hasil' => 973,
            'intervensi_pre_operasi' => 974, 'intervensi_post_operasi' => 975,
            'skala_nyeri' => 976,
        ];
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
        // ===== Fase P6 — form 106 Asesmen Kemoterapi (KONSEP §3, 32 var) =====
        $mapping[106] = [
            'tanggal_asesmen' => 155, 'waktu_asesmen' => 156,
            'riwayat_operasi' => 61, 'riwayat_kemoterapi' => 52, 'riwayat_radioterapi' => 53,
            'siklus' => 993, 'protokol' => 993, 'dpjp_utama' => 994, 'diagnosis' => 40,
            'penunjang_1' => 995, 'penunjang_2' => 995, 'penunjang_3' => 995, 'penunjang_4' => 995,
            'tanggal_pemberian_obat' => 996, 'pasang_alat' => 997, 'jenis_obat' => 998,
            'reaksi_kemoterapi' => 999, 'ket_reaksi' => 999, 'kondisi_rahim' => 1000,
            'perawat_kemoterapi' => 1001, 'keluarga_kemoterapi' => 1001,
            'nyeri_kemoterapi' => 1002, 'isi_nyeri_kemoterapi' => 1002,
            'resiko_kemoterapi' => 1002, 'isi_resiko_kemoterapi' => 1002,
            'cemas_kemoterapi' => 1002, 'isi_cemas_kemoterapi' => 1002,
            'nutrisi_kemoterapi' => 1002, 'isi_nutrisi_kemoterapi' => 1002,
            'infeksi_kemoterapi' => 1002, 'isi_infeksi_kemoterapi' => 1002,
            'skala_nyeri' => 140,
        ];
        // ===== Fase P6 — form 107 Pemantauan Kemoterapi (KONSEP §4, 254 var) =====
        // Var TTV tanpa suffix TIDAK dideklarasi — tiap baris memakai suffix
        // (sistolik_1..dst) supaya tidak ada var mati yang tak pernah dikirim.
        $mapping[107] = [
            'tanggal_pemantauan' => 1003, 'jam_pemantauan' => 1003,
            'total_cairan' => 1008, 'perawat_ruangan' => 82,
        ];
        for ($baris = 1; $baris <= 5; $baris++) {
            $mapping[107]['premed_'.$baris] = 1004;
            $mapping[107]['premed_dosis_'.$baris] = 1004;
        }
        for ($baris = 1; $baris <= 20; $baris++) {
            $mapping[107]['jam_'.$baris] = 1005;
            $mapping[107]['cairan_'.$baris] = 1006;
            $mapping[107]['obat_'.$baris] = 1006;
            $mapping[107]['dosis_'.$baris] = 1006;
            $mapping[107]['sistolik_'.$baris] = 6;
            $mapping[107]['diastolik_'.$baris] = 7;
            $mapping[107]['nadi_'.$baris] = 10;
            $mapping[107]['pernapasan_'.$baris] = 12;
            $mapping[107]['suhu_'.$baris] = 11;
            $mapping[107]['skala_nyeri_'.$baris] = 140;
            $mapping[107]['keluhan_'.$baris] = 1007;
            $mapping[107]['efek_samping_'.$baris] = 1007;
        }
        // ===== Fase P6 — form 108 Asesmen Pasien dengan Darah (KONSEP §5, 12 var) =====
        $mapping[108] = [
            'golongan_darah' => 1009, 'rhesus' => 1009, 'dasar_penentuan' => 1010,
            'nomor_hasil_lab' => 1010,
            'jenis_darah' => 1011, 'jumlah_darah' => 1012, 'alasan_transfusi' => 1013,
            'pernah_transfusi' => 1014, 'reaksi_alergi' => 1014,
            'pantangan_transfusi' => 29, 'tanggal_permintaan' => 1015,
            'dokter_pemohon_id' => 1016,
        ];
        // ===== Fase P6 — form 109 Assesmen Transfusi (KONSEP §6, 22 var) =====
        $mapping[109] = [
            'tanggal_persiapan' => 1017, 'waktu_persiapan' => 1017,
            'no_labu_darah' => 1018, 'jumlah_labu_darah' => 1018, 'jenis_labu_darah' => 1018,
            'keadaan_umum' => 1019, 'keluhan' => 1019,
            'dc1_identitas' => 1020, 'dc1_jumlah' => 1020, 'dc1_jenis' => 1020, 'dc1_golongan' => 1020,
            'dc2_identitas' => 1021, 'dc2_jumlah' => 1021, 'dc2_jenis' => 1021, 'dc2_golongan' => 1021,
            'perawat_1_id' => 1022, 'perawat_2_id' => 1022,
            'sistolik' => 6, 'diastolik' => 7, 'nadi' => 10, 'pernapasan' => 12, 'suhu' => 11,
        ];
        // ===== Fase P6 — form 110 Monitoring Transfusi Darah (KONSEP §7, 33 var) =====
        $mapping[110] = [
            'tanggal_evaluasi' => 155, 'jam' => 156,
            'no_labu_darah' => 1023, 'darah_masuk' => 1024, 'komponen_darah' => 1025,
            'tindakan' => 1028, 'keluhan' => 1029, 'skala_nyeri' => 140,
            'sistolik' => 6, 'diastolik' => 7, 'nadi' => 10, 'pernapasan' => 12, 'suhu' => 11,
        ];
        for ($i = 1; $i <= 10; $i++) {
            $mapping[110]['gejala_'.$i] = 1026;
            $mapping[110]['tanda_'.$i] = 1027;
        }
        // ===== Fase P6 — form 111 Double Check Obat High Alert (KONSEP §8, 231 var) =====
        $mapping[111] = [
            'tanggal_pemberian' => 1030, 'waktu_pemberian' => 1030,
            'cek_identitas_p1' => 1032, 'cek_identitas_p2' => 1032,
            'cek_jumlah_p1' => 1032, 'cek_jumlah_p2' => 1032,
            'cek_jenis_p1' => 1032, 'cek_jenis_p2' => 1032,
            'perawat_1_id' => 82, 'perawat_2_id' => 82,
            'jumlah_baris' => null, // TURUNAN — objek_id NULL (PANDUAN §2.6)
        ];
        for ($baris = 1; $baris <= 20; $baris++) {
            $mapping[111]['ha_obat_'.$baris] = 69;
            $mapping[111]['ha_p1_identitas_'.$baris] = 1031;
            $mapping[111]['ha_p1_obat_'.$baris] = 1031;
            $mapping[111]['ha_p1_dosis_'.$baris] = 1031;
            $mapping[111]['ha_p1_cara_'.$baris] = 1031;
            $mapping[111]['ha_p1_waktu_'.$baris] = 1031;
            $mapping[111]['ha_p2_identitas_'.$baris] = 1032;
            $mapping[111]['ha_p2_obat_'.$baris] = 1032;
            $mapping[111]['ha_p2_dosis_'.$baris] = 1032;
            $mapping[111]['ha_p2_cara_'.$baris] = 1032;
            $mapping[111]['ha_p2_waktu_'.$baris] = 1032;
        }
        // ===== Fase P6 — form 112 Asesmen Psikologi (KONSEP §3, 34 var) =====
        // Item checkbox WAJIB bersuffix (emrDetailByVariabel menimpa baris
        // dengan variabel sama) — pola butir Bundle VAP form 14.
        $mapping[112] = [
            'hubungan_keluarga' => 671, 'nama_keluarga' => 672, 'status_keluarga' => 673,
            'alamat_keluarga' => 674, 'hp_keluarga' => 675, 'rencana_rawat' => 676,
            'lingkungan' => 677, 'perawat_rumah' => 678, 'fasilitasi_rs' => 679,
            'tanggal_asesmen' => 155, 'waktu_asesmen' => 156,
        ];
        for ($i = 1; $i <= 8; $i++) {
            $mapping[112]['psi_info_'.$i] = 680;
        }
        $mapping[112]['psi_masalah_1'] = 681;
        $mapping[112]['psi_masalah_2'] = 681;
        for ($i = 1; $i <= 10; $i++) {
            $mapping[112]['psi_keluarga_'.$i] = 681;
        }
        for ($i = 1; $i <= 3; $i++) {
            $mapping[112]['psi_mk_keluarga_'.$i] = 681;
        }
        // ===== Fase P6 — form 113 Asesmen Spiritual (KONSEP §4, 16 var) =====
        $mapping[113] = [
            'spiritual' => 683, 'rohani_1' => 683, 'rohani_2' => 683, 'rohani_3' => 683,
            'petugas_spiritual' => 684, 'petugas_rohani_1' => 684,
            'petugas_rohani_2' => 684, 'petugas_rohani_3' => 684,
        ];
        for ($i = 1; $i <= 8; $i++) {
            $mapping[113]['masalah_'.$i] = 682;
        }
        // ===== Fase P6 — form 114 Permintaan Pelayanan Kerohanian (KONSEP §5, 4 var) =====
        $mapping[114] = [
            'nama_rohani' => 685, 'umur_rohani' => 686,
            'jk_rohani' => 687, 'agama_rohani' => 688,
        ];
        // ===== Fase P6 — form 115 Bukti Pelayanan Kerohanian (KONSEP §6, 8 var) =====
        $mapping[115] = [
            'nama_anggota_rohani' => 685, 'umur_anggota_rohani' => 686,
            'jk_anggota_rohani' => 687, 'agama_anggota_rohani' => 688,
            'tanggal_rohani' => 689, 'jam_rohani' => 689,
            'hari_rohani' => 690, 'tempat_rohani' => 690,
        ];
        // ===== Fase P6 — form 116 Surveilans CVC/PICC (KONSEP §3, 34 var) =====
        // Checkbox/opsi "satu Ya" divalidasi di controller (guard after), bukan di rule.
        $mapping[116] = [
            'tanggal_evaluasi' => 155, 'waktu_evaluasi' => 156,
            'lokasi_subclavia' => 691, 'lokasi_jugularis' => 691,
            'lokasi_femoralis' => 691, 'lokasi_lainnya' => 691,
            'cath_12' => 692, 'cath_7' => 692, 'cath_5' => 692,
            'cath_4' => 692, 'cath_3' => 692, 'cath_lainnya' => 692,
            'cvc_4lumen' => 693, 'cvc_3lumen' => 693, 'cvc_2lumen' => 693, 'cvc_1lumen' => 693,
            'bsi_pasang' => 694, 'bsi_lepas' => 694,
            'tanggal_pasang' => 695, 'tanggal_lepas' => 695,
            'keterangan' => 77,
        ];
        for ($i = 1; $i <= 2; $i++) {
            $mapping[116]['bsi_'.$i] = 696;
        }
        for ($i = 3; $i <= 7; $i++) {
            $mapping[116]['bsi_'.$i] = 697;
        }
        for ($i = 8; $i <= 13; $i++) {
            $mapping[116]['bsi_'.$i] = 698;
        }
        // ===== Fase P6 — form 117 Surveilans IV Catheter (KONSEP §4, 19 var) =====
        $mapping[117] = [
            'tanggal_evaluasi' => 155, 'waktu_evaluasi' => 156,
            'lokasi_iv' => 699, 'nomor_iv' => 700, 'jenis_cairan' => 701,
            'iv_1' => 702, 'iv_2' => 702, 'iv_3' => 702,
            'iv_4' => 703, 'iv_5' => 703, 'iv_6' => 703, 'iv_7' => 703,
            'pivas_skor' => 704, 'pivas_gejala_1' => 704, 'pivas_gejala_2' => 704,
            'pivas_gejala_3' => 704, 'pivas_gejala_4' => 704,
            'tanggal_pasang' => 705, 'tanggal_lepas' => 705,
        ];
        // ===== Fase P6 — form 118 Surveilans Kateter Urine (KONSEP §5, 30 var) =====
        $mapping[118] = [
            'tanggal_evaluasi' => 155, 'waktu_evaluasi' => 156,
            'jenis_kath_silikon' => 706, 'jenis_kath_folly' => 706, 'jenis_kath_lainnya' => 706,
            'no_kath_6' => 707, 'no_kath_8' => 707, 'no_kath_10' => 707,
            'no_kath_12' => 707, 'no_kath_14' => 707,
            'isk_10' => 709, 'isk_11' => 709,
            'tanggal_pasang' => 711, 'tanggal_lepas' => 711,
        ];
        for ($i = 1; $i <= 9; $i++) {
            $mapping[118]['isk_'.$i] = 708;
        }
        for ($i = 1; $i <= 7; $i++) {
            $mapping[118]['isk_gejala_'.$i] = 710;
        }
        // ===== Fase P6 — form 119 Surveilans Tirah Baring/HAP (KONSEP §6, 21 var) =====
        $mapping[119] = [
            'tanggal_evaluasi' => 155, 'waktu_evaluasi' => 156,
            'hap_suhu_1' => 713, 'hap_suhu_2' => 713, 'hap_suhu_3' => 713,
            'hap_leukosit_1' => 713, 'hap_leukosit_2' => 713, 'hap_leukosit_3' => 713,
            'hap_sekresi_1' => 715, 'hap_sekresi_2' => 715,
            'hap_kultur_1' => 715, 'hap_kultur_2' => 715, 'hap_kultur_3' => 715,
            'keterangan' => 77,
        ];
        for ($i = 1; $i <= 4; $i++) {
            $mapping[119]['hap_'.$i] = 712;
        }
        for ($i = 1; $i <= 3; $i++) {
            $mapping[119]['hap_ab_'.$i] = 714;
        }
        // ===== Fase P6 — form 120 Screening Akses Vaskular (KONSEP §7, 31 var) =====
        $mapping[120] = [
            'jenis_spesimen' => 716,
            'aks_disfungsi_1' => 719, 'aks_disfungsi_2' => 719, 'aks_disfungsi_3' => 719,
            'aks_nyeri_1' => 719, 'aks_nyeri_2' => 719, 'aks_nyeri_3' => 719, 'aks_nyeri_4' => 719,
        ];
        for ($i = 1; $i <= 3; $i++) {
            $mapping[120]['aks_area_'.$i] = 717;
        }
        for ($i = 1; $i <= 9; $i++) {
            $mapping[120]['aks_steal_'.$i] = 717;
        }
        for ($i = 1; $i <= 4; $i++) {
            $mapping[120]['aks_cdl_'.$i] = 718;
        }
        for ($i = 1; $i <= 6; $i++) {
            $mapping[120]['aks_exit_'.$i] = 718;
        }
        $mapping[120]['aks_pasien_1'] = 718;
        // ===== Fase P6 — form 121 Asesmen Pasien Restraint (KONSEP §8, 55 var) =====
        // Catatan KONSEP: field asesmen adalah `tgl_edukasi` (mapping KONSEP `tgl_edikasi`
        // adalah typo) — seeder/controller/view memakai `tgl_edukasi`.
        $mapping[121] = [
            'tanggal_asesmen' => 155, 'waktu_asesmen' => 156,
            'gaduh_1' => 720, 'gaduh_2' => 720, 'gaduh_3' => 720,
            'gaduh_4' => 720, 'gaduh_5' => 720, 'order_dokter' => 720,
            'nonfarm_1' => 721, 'nonfarm_2' => 721, 'nonfarm_3' => 721,
            'nonfarm_4' => 721, 'nonfarm_5' => 721,
            'stop_1' => 723, 'stop_2' => 723, 'stop_3' => 723,
            'stop_4' => 723, 'stop_5' => 723, 'stop_6' => 723, 'stop_lainnya' => 723,
            'status_mental' => 724, 'status_mental_lainnya' => 724,
            'kesadaran' => 51, 'sistolik' => 6, 'diastolik' => 7,
            'nadi' => 10, 'pernapasan' => 12, 'suhu' => 11,
            'tgl_edukasi' => 725, 'jam_edukasi' => 725, 'nama_petugas' => 725,
            'nama_keluarga' => 725, 'nama_petugas_melepas' => 725,
            'tgl_melepas' => 725, 'jam_melepas' => 725,
        ];
        for ($i = 1; $i <= 10; $i++) {
            $mapping[121]['farmak_'.$i] = 722;
            $mapping[121]['farmak_dosis_'.$i] = 722;
        }
        // ===== Fase P6 — form 122 Observasi Restraint (KONSEP §9, 11 var) =====
        // Payload FLAT + indeks `ronde` (pola Monitoring Transfusi form 110), bukan
        // `ronde[N][var]` seperti KONSEP §9 — deviasi terdokumentasi. Indeks ronde
        // disimpan di emr_detail.flag_abnormal, bukan di mapping.
        $mapping[122] = [
            'waktu_observasi' => 156,
            'sistolik' => 6, 'diastolik' => 7, 'nadi' => 10,
            'pernapasan' => 12, 'suhu' => 11,
            'kognitif' => 726, 'luka' => 727,
            'asupan_makanan' => 728, 'posisi_fiksasi' => 728,
            'panses_ec' => 729,
        ];
        // ===== Fase P6 — form 58 Rekam Medis Rujukan (KONSEP §3, 20 var) =====
        $mapping[58] = [
            'tanggal_pengantar' => 730, 'tgl_rencana_ranap' => 730,
            'tingkat_urgensi' => 731,
            'indikasi_rawat_inap' => 732,
            'rencana_pemeriksaan_penunjang' => 733,
            'terapi_keperawatan' => 734,
            'dokter_rawat' => 735,
            'kebutuhan_pelayanan' => 736,
            'kebutuhan_kamar' => 737,
            'screening_covid' => 738, 'kriteria_covid' => 738, 'ews' => 738,
            'oksigen' => 738, 'ventilator' => 738, 'monitor' => 738, 'syringe_pump' => 738,
            'diagnosis' => 40,   // reuse Diagnosa Medis
            'handphone' => 60,   // reuse Nomor Handphone
            'alasan' => 77,      // reuse Keterangan
            'dirujuk_ke' => 186, // reuse Dirujuk Ke (milik form 16; TASK_FORM EMR-058)
        ];
        // ===== Fase P6 — form 126 Surat Keterangan Sehat (KONSEP §4, 8 var) =====
        $mapping[126] = [
            'no_surat' => 739,
            'pemeriksaan_fisik' => 740,
            'mata' => 741,
            'keterangan' => 77,  // reuse Keterangan
            'bb' => 8, 'tb' => 9, 'sistolik' => 6, 'diastolik' => 7,
        ];
        // ===== Fase P6 — form 127 Pesan Penunjang (KONSEP §5, 23 var; kelas_id TIDAK dimapping) =====
        $mapping[127] = [
            'jenis_pesan' => 742,
            'bagian_asal' => 743, 'unit_pelaksanaan_id' => 743,
            'diagnosa_medis' => 40,   // reuse Diagnosa Medis
            'jenis_tindakan' => 744,
            'tgl_rencana_tindakan' => 745, 'jam_rencana_tindakan' => 745,
            'jam_keputusan_sc' => 746,
            'jenis_rawat_rencana' => 747, 'tgl_rencana_ranap' => 747,
            'handphone' => 60, 'handphone_keluarga' => 748,
            'pasang_alat' => 749, 'alat_khusus' => 749,
            'sc' => 750, 'sc_emergency' => 750, 'ic' => 750,
            'pra_tindakan' => 751, 'jenis_anestesi' => 751,
            'dokter_operator_id' => 751, 'dpjp_utama' => 751,
            'bb' => 8, 'tb' => 9,
        ];
        // ===== Fase P6 — form 128 SPRI & Program Rujukan Balik (KONSEP §6, 14 var) =====
        $mapping[128] = [
            'jenis_formulir' => 752,
            'no_sep' => 753, 'no_rujukan' => 753,
            'alasan' => 754,
            'persiapan_tindakan' => 755,
            'rencana_pemeriksaan_penunjang' => 756,
            'terapi_obat' => 757,
            'anjuran' => 758,
            'tanggal_kontrol' => 759,
            'dokter_pengarah_id' => 760,
            'faskes_tujuan' => 761,
            'diagnosis' => 40,    // reuse Diagnosa Medis
            'anamnesis' => 1,     // reuse Subjective (S)
            'pemeriksaan_fisik' => 2, // reuse Objective (O)
        ];
        foreach ($mapping as $formId => $variabels) {
            foreach ($variabels as $variabel => $objekId) {
                $exists = DB::table('objek_form_control')
                    ->where('form_id', $formId)
                    ->where('variabel', $variabel)
                    ->exists();
                if ($exists) {
                    continue;
                }
                DB::table('objek_form_control')->insert([
                    'form_id' => $formId,
                    'objek_id' => $objekId,
                    'variabel' => $variabel,
                    'input_time' => $now,
                    'input_user_id' => 1,
                    'status_batal' => 0,
                ]);
            }
        }
        // Soft-delete mapping aktif yang variabelnya sudah TIDAK lagi ada di
        // $mapping. Tanpa ini baris lama tetap ikut terbaca (EmrHelper hanya
        // memfilter status_batal), sehingga form punya variabel "hantu" yang
        // tidak pernah dikirim controller — gejalanya `simpan` kurang dari
        // jumlah variabel di e2e. Contoh nyata: `prioritas` pada form 48 yang
        // dihapus dari konsep §6.5.
        foreach ($mapping as $formId => $variabels) {
            $aktif = DB::table('objek_form_control')
                ->where('form_id', $formId)
                ->where(function ($q) {
                    $q->whereNull('status_batal')->orWhere('status_batal', 0);
                })
                ->pluck('variabel')
                ->all();

            foreach (array_diff($aktif, array_keys($variabels)) as $variabelLama) {
                DB::table('objek_form_control')
                    ->where('form_id', $formId)
                    ->where('variabel', $variabelLama)
                    ->where(function ($q) {
                        $q->whereNull('status_batal')->orWhere('status_batal', 0);
                    })
                    ->update(['status_batal' => 1, 'mod_time' => $now]);
            }
        }
        // Mapping variabel UP GO lama (objek 66-68) tidak lagi dipakai form 3:
        // bagian F diganti Pengkajian Risiko Jatuh berbasis usia. Soft-delete agar
        // tidak ikut ter-simpan kalau masih ada data lama.
        foreach (['up_go_1_a', 'up_go_1_b', 'up_go_2'] as $variabelLama) {
            DB::table('objek_form_control')
                ->where('variabel', $variabelLama)
                ->where(function ($q) {
                    $q->whereNull('status_batal')->orWhere('status_batal', 0);
                })
                ->update(['status_batal' => 1, 'mod_time' => $now]);
        }
        // Isi objek_id data legacy yang tadinya NULL (dulu env('OBJEK_ID_*') kosong),
        // agar baca berbasis objek tetap cocok dengan data lama.
        EmrHelper::backfillObjekId(1);
        EmrHelper::backfillObjekId(2);
        EmrHelper::backfillObjekId(3);
        EmrHelper::backfillObjekId(4);
        EmrHelper::backfillObjekId(5);
        EmrHelper::backfillObjekId(6);
        EmrHelper::backfillObjekId(7);
        EmrHelper::backfillObjekId(8);
        EmrHelper::backfillObjekId(9);
        EmrHelper::backfillObjekId(10);
        EmrHelper::backfillObjekId(11);
        EmrHelper::backfillObjekId(12);
        EmrHelper::backfillObjekId(13);
        EmrHelper::backfillObjekId(14);
        EmrHelper::backfillObjekId(15);
        EmrHelper::backfillObjekId(16);
        EmrHelper::backfillObjekId(20);
        EmrHelper::backfillObjekId(21);
        EmrHelper::backfillObjekId(57);
        EmrHelper::backfillObjekId(17);
        EmrHelper::backfillObjekId(18);
        EmrHelper::backfillObjekId(19);
        EmrHelper::backfillObjekId(22);
        EmrHelper::backfillObjekId(23);
        EmrHelper::backfillObjekId(24);
        EmrHelper::backfillObjekId(25);
        EmrHelper::backfillObjekId(26);
        EmrHelper::backfillObjekId(27);
        EmrHelper::backfillObjekId(28);
        EmrHelper::backfillObjekId(29);
        EmrHelper::backfillObjekId(30);
        EmrHelper::backfillObjekId(31);
        EmrHelper::backfillObjekId(32);
        EmrHelper::backfillObjekId(33);
        EmrHelper::backfillObjekId(34);
        EmrHelper::backfillObjekId(35);
        EmrHelper::backfillObjekId(36);
        EmrHelper::backfillObjekId(37);
        EmrHelper::backfillObjekId(38);
        EmrHelper::backfillObjekId(54);
        EmrHelper::backfillObjekId(55);
        EmrHelper::backfillObjekId(56);
        EmrHelper::backfillObjekId(123);
        EmrHelper::backfillObjekId(124);
        EmrHelper::backfillObjekId(125);
        // ===== Fase P3 =====
        foreach ([39, 40, 42, 43, 44, 45, 46, 47, 48, 49, 50, 51] as $formIdP3) {
            EmrHelper::backfillObjekId($formIdP3);
        }
        // ===== Fase P4 =====
        foreach ([59, 60, 61, 62, 63, 64, 65, 66, 68, 69,
            70, 71, 72, 73, 74, 75, 76, 77, 78, 79, 80, 81, 82, 83, 84] as $formIdP4) {
            EmrHelper::backfillObjekId($formIdP4);
        }
        // ===== Fase P5 — ICU & Hemodialisa =====
        foreach ([52, 53, 102, 103, 104, 105, 85] as $formIdP5) {
            EmrHelper::backfillObjekId($formIdP5);
        }
        // ===== Fase P6 — Onkologi & Darah =====
        foreach ([106, 107, 108, 109, 110, 111] as $formIdP6) {
            EmrHelper::backfillObjekId($formIdP6);
        }
        // ===== Fase P6 — Formulir & Rujukan =====
        foreach ([58, 126, 127, 128] as $formIdRujukan) {
            EmrHelper::backfillObjekId($formIdRujukan);
        }
        // ======== Akses EHR per profesi ========
        // Idempotent: lewati kombinasi profesi+form yang sudah ada (tanpa bentrok dengan level/bagian lain).
        $akses = [
            // ===== Fase P3 — Gizi & Nutrisi =====
            // Asesmen Gizi (39): Dokter + Perawat full CRUD.
            ['profesi_id' => 1, 'form_id' => 39, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 39, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            // Detail Nutrisi (40): Dokter + Ahli Gizi (10) full CRUD; Perawat READ-ONLY
            // (skor MNA-SF/MST diisi petugas gizi, bukan perawat).
            ['profesi_id' => 1, 'form_id' => 40, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 40, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
            ['profesi_id' => 10, 'form_id' => 40, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            // Pesan Makanan (42): diisi dapur/Ahli Gizi (10) & Perawat (2) full;
            // Dokter READ-ONLY (dokter tidak menata jadwal distribusi).
            ['profesi_id' => 1, 'form_id' => 42, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
            ['profesi_id' => 2, 'form_id' => 42, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 10, 'form_id' => 42, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            // Diet Pasien (43): Ahli Gizi (10) & Dokter full; Perawat READ-ONLY.
            ['profesi_id' => 1, 'form_id' => 43, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 43, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
            ['profesi_id' => 10, 'form_id' => 43, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            // ADIME (44): Perawat + Ahli Gizi full; Dokter read.
            ['profesi_id' => 1, 'form_id' => 44, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 44, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 10, 'form_id' => 44, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            // ===== Fase P3 — Farmasi =====
            // Daftar Terapi Obat (45): Dokter full; Perawat create/read/update
            // TANPA delete (rekam terapi tak boleh dihapus perawat);
            // Apoteker (4) READ-ONLY.
            ['profesi_id' => 1, 'form_id' => 45, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 45, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 0],
            ['profesi_id' => 4, 'form_id' => 45, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
            // Rekonsiliasi Obat (46): Dokter + Apoteker full; Perawat read.
            ['profesi_id' => 1, 'form_id' => 46, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 46, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
            ['profesi_id' => 4, 'form_id' => 46, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            // Telaah Resep (47): Apoteker + Dokter full; Perawat read.
            ['profesi_id' => 1, 'form_id' => 47, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 47, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
            ['profesi_id' => 4, 'form_id' => 47, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            // Telaah Obat (48): Apoteker full; Dokter read.
            ['profesi_id' => 1, 'form_id' => 48, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
            ['profesi_id' => 4, 'form_id' => 48, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            // ===== Fase P3 — Order Penunjang Medis =====
            // Pola identik dengan Order Laboratorium (form 6): order tetap dokter,
            // profesi penunjang read-only.
            // Order Patologi (49): Dokter CRUD; Perawat + Patolog Anatomi (15) read.
            ['profesi_id' => 1, 'form_id' => 49, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 49, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
            ['profesi_id' => 15, 'form_id' => 49, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
            // Order Mikrobiologi (50): Dokter CRUD; Perawat + Analis Lab (13) read.
            ['profesi_id' => 1, 'form_id' => 50, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 50, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
            ['profesi_id' => 13, 'form_id' => 50, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
            // Order POCT (51): Dokter CRUD; Perawat + Analis Lab (13) read.
            ['profesi_id' => 1, 'form_id' => 51, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 51, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
            ['profesi_id' => 13, 'form_id' => 51, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
            // ===== Fase P5 — ICU & Hemodialisa =====
            // Form 52 (Kriteria Masuk ICU): keputusan transfer — Dokter full;
            // Perawat & Dokter Intensivis READ (bukan kewenangan menulis).
            ['profesi_id' => 1, 'form_id' => 52, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 52, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
            ['profesi_id' => 16, 'form_id' => 52, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
            // Form 53 (Kriteria Keluar ICU): Dokter & Dokter Anestesi (17) yang
            // memutuskan; Perawat & Dokter Intensivis READ.
            ['profesi_id' => 1, 'form_id' => 53, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 53, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
            ['profesi_id' => 16, 'form_id' => 53, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
            ['profesi_id' => 17, 'form_id' => 53, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 0],
            // Form 102 Pre Hemodialisa: Dokter full; Perawat (2) & Perawat HD (18) CRUD tanpa delete.
            ['profesi_id' => 1, 'form_id' => 102, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 102, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 0],
            ['profesi_id' => 18, 'form_id' => 102, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 0],
            // Form 103 Intra Hemodialisa: identik 102.
            ['profesi_id' => 1, 'form_id' => 103, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 18, 'form_id' => 103, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 0],
            ['profesi_id' => 2, 'form_id' => 103, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 0],
            // Form 104 Post Hemodialisa: identik 103.
            ['profesi_id' => 1, 'form_id' => 104, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 18, 'form_id' => 104, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 0],
            ['profesi_id' => 2, 'form_id' => 104, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 0],
            // Form 105 Triage Hemodialisa: Dokter full; Perawat & Perawat HD CRUD tanpa delete.
            ['profesi_id' => 1, 'form_id' => 105, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 105, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 0],
            ['profesi_id' => 18, 'form_id' => 105, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 0],
            // Form 85 Asesmen Pra Bedah (KONSEP §3): Dokter full CRUD;
            // Perawat READ-ONLY (diisi dokter penilai pra bedah).
            ['profesi_id' => 1, 'form_id' => 85, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 85, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
            // ===== Fase P5 — Bedah & Anestesi (band 86) =====
            ['profesi_id' => 1, 'form_id' => 86, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 86, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
            // ===== Fase P5 — Bedah & Anestesi (band 87) =====
            ['profesi_id' => 1, 'form_id' => 87, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 87, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            // ===== Fase P5 — Bedah & Anestesi (band 88) =====
            ['profesi_id' => 1, 'form_id' => 88, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 88, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            // ===== Fase P5 — Bedah & Anestesi (band 90) =====
            ['profesi_id' => 1, 'form_id' => 90, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 90, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            // ===== Fase P5 — Bedah & Anestesi (band 91) =====
            ['profesi_id' => 1, 'form_id' => 91, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 91, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 1, 'form_id' => 92, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 92, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
            ['profesi_id' => 1, 'form_id' => 93, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 93, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
            ['profesi_id' => 1, 'form_id' => 94, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 94, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 1, 'form_id' => 95, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 1, 'form_id' => 96, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 96, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 1, 'form_id' => 98, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 98, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 1, 'form_id' => 99, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 99, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 1, 'form_id' => 101, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 101, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            // ===== Fase P6 — Onkologi & Darah =====
            // Asesmen Kemoterapi (106): Dokter + Perawat full CRUD.
            ['profesi_id' => 1, 'form_id' => 106, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 106, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            // Pemantauan Kemoterapi (107): Dokter + Perawat full CRUD.
            ['profesi_id' => 1, 'form_id' => 107, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 107, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            // Asesmen Pasien dengan Darah (108): Dokter + Perawat full; Analis Lab (13) read.
            ['profesi_id' => 1, 'form_id' => 108, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 108, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 13, 'form_id' => 108, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
            // Assesmen Transfusi (109): Dokter + Perawat full; Analis Lab (13) read.
            ['profesi_id' => 1, 'form_id' => 109, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 109, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 13, 'form_id' => 109, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
            // Monitoring Transfusi Darah (110): Dokter + Perawat full CRUD.
            ['profesi_id' => 1, 'form_id' => 110, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 110, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            // Double Check Obat High Alert (111): Perawat + Apoteker (4) full;
            // Dokter (1) read-only (diisi petugas, bukan DPJP).
            ['profesi_id' => 1, 'form_id' => 111, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
            ['profesi_id' => 2, 'form_id' => 111, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 4, 'form_id' => 111, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            // ===== Fase P6 — Psikososial & Kerohanian =====
            // Asesmen Psikologi (112): Dokter + Perawat full; Perekam Medis (7) read.
            ['profesi_id' => 1, 'form_id' => 112, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 112, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 7, 'form_id' => 112, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
            // Asesmen Spiritual (113): Dokter + Perawat + Kerohanian (14) full.
            ['profesi_id' => 1, 'form_id' => 113, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 113, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 14, 'form_id' => 113, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            // Permintaan Kerohanian (114): Dokter + Kerohanian (14) full;
            // Perawat (2) create/read (pengajuan unit, revisi oleh petugas rohani).
            ['profesi_id' => 1, 'form_id' => 114, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 114, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
            ['profesi_id' => 14, 'form_id' => 114, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            // Bukti Kerohanian (115): Dokter + Kerohanian (14) full; Perawat (2) read.
            ['profesi_id' => 1, 'form_id' => 115, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 14, 'form_id' => 115, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 115, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
            // ===== Fase P6 — Surveilans Infeksi & Restraint =====
            // 116–119 Surveilans: Dokter + Perawat full; Sanitarian (9) create/read/update
            // tanpa delete (penyusun klasifikasi BSI/PIVAS/ISK/HAP).
            ['profesi_id' => 1, 'form_id' => 116, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 116, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 9, 'form_id' => 116, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 0],
            ['profesi_id' => 1, 'form_id' => 117, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 117, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 9, 'form_id' => 117, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 0],
            ['profesi_id' => 1, 'form_id' => 118, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 118, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 9, 'form_id' => 118, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 0],
            ['profesi_id' => 1, 'form_id' => 119, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 119, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 9, 'form_id' => 119, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 0],
            // 120–122 Screening Akses + Restraint: Dokter + Perawat full CRUD.
            ['profesi_id' => 1, 'form_id' => 120, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 120, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 1, 'form_id' => 121, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 121, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 1, 'form_id' => 122, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 122, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            // ===== Fase P6 — Formulir & Rujukan (KONSEP_FORMULIR_RUJUKAN.md §Akses EHR) =====
            // 58 Rekam Medis Rujukan: Dokter full, Perawat read-only.
            ['profesi_id' => 1, 'form_id' => 58, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 58, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
            // 126 Surat Keterangan Sehat: Dokter full, Perekam Medis (7) buat+baca.
            ['profesi_id' => 1, 'form_id' => 126, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 7, 'form_id' => 126, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
            // 127 Pesan Penunjang: Dokter full, Perawat read-only.
            ['profesi_id' => 1, 'form_id' => 127, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 127, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
            // 128 SPRI & Program Rujukan Balik: Dokter full, Perawat read-only.
            ['profesi_id' => 1, 'form_id' => 128, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 128, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
            // ===== Fase P6 — Katalog Form Blanko (129, KONSEP §7.1) — wajib utk header_ehr =====
            // Dokter full; Perawat isi+baca+ubah (tanpa hapus).
            ['profesi_id' => 1, 'form_id' => 129, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 129, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 0],
            // ===== Fase P4 — Kebidanan & MCU =====
            // Form 67 (Down Score) tidak di-seed — status ⛔ di docs/TASK_FORM.md.
            ['profesi_id' => 1, 'form_id' => 59, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 59, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 1, 'form_id' => 60, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 60, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            // Pengkajian Non-Perina: entry point memakai ulang partial form 3 —
            // Perawat READ-ONLY (hanya Dokter yang memulai pengkajian).
            ['profesi_id' => 1, 'form_id' => 61, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 61, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
            ['profesi_id' => 1, 'form_id' => 62, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 62, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 1, 'form_id' => 63, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 63, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 1, 'form_id' => 64, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 64, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 1, 'form_id' => 65, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 65, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 1, 'form_id' => 66, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 66, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 1, 'form_id' => 68, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 68, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 1, 'form_id' => 69, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 69, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            // Kesimpulan MCU: Dokter full; Perawat READ-ONLY (membaca hasil untuk
            // edukasi & rujukan, tidak boleh mengisi).
            ['profesi_id' => 1, 'form_id' => 70, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 70, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
            // TTV MCU: Dokter + Perawat full (perawat MCU yang mengisi TTV).
            ['profesi_id' => 1, 'form_id' => 71, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 71, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 1, 'form_id' => 72, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 72, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 1, 'form_id' => 73, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 73, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 1, 'form_id' => 74, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 74, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
            ['profesi_id' => 1, 'form_id' => 75, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 75, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
            ['profesi_id' => 1, 'form_id' => 76, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 76, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
            ['profesi_id' => 1, 'form_id' => 77, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 77, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
            ['profesi_id' => 1, 'form_id' => 78, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 78, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
            ['profesi_id' => 1, 'form_id' => 79, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 79, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
            ['profesi_id' => 1, 'form_id' => 80, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 80, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
            ['profesi_id' => 1, 'form_id' => 81, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 81, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
            ['profesi_id' => 1, 'form_id' => 82, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 82, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
            ['profesi_id' => 1, 'form_id' => 83, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 83, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
            ['profesi_id' => 1, 'form_id' => 84, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 84, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],

            // Dokter (profesi 1): seluruh form
            ['profesi_id' => 1, 'form_id' => 1, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 1, 'form_id' => 2, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 1, 'form_id' => 3, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 1, 'form_id' => 4, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 1, 'form_id' => 5, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            // Order Laboratorium (form 6): Dokter create/read/update/delete; Perawat read; Analis Laboratorium read.
            ['profesi_id' => 1, 'form_id' => 6, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 6, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
            ['profesi_id' => 13, 'form_id' => 6, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
            // Order Radiologi (form 7): Dokter create/read/update/delete; Perawat read; Radiografer read.
            ['profesi_id' => 1, 'form_id' => 7, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 7, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
            ['profesi_id' => 5, 'form_id' => 7, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
            // Formulir Konsultasi (form 8): Dokter create/read/update/delete; Perawat read.
            ['profesi_id' => 1, 'form_id' => 8, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 8, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
            // Perawat (profesi 2): form pengkajian saja
            ['profesi_id' => 2, 'form_id' => 3, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 4, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            // Implementasi Keperawatan (form 9): Dokter & Perawat create/read/update/delete.
            // WAJIB di-seed: EmrDashboardController INNER JOIN akses_ehr, jadi tanpa
            // baris ini form tidak muncul di dashboard dan selalu 403.
            ['profesi_id' => 1, 'form_id' => 9, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 9, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            // Tindakan Medis (form 10): Dokter & Perawat create/read/update/delete.
            ['profesi_id' => 1, 'form_id' => 10, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 10, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            // Assesmen Awal Medis Rawat Jalan (form 11): Dokter & Perawat create/read/update/delete.
            ['profesi_id' => 1, 'form_id' => 11, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 11, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            // SBAR (form 12): Dokter & Perawat create/read/update/delete, semua
            // jenis rawat — format komunikasi antar petugas.
            ['profesi_id' => 1, 'form_id' => 12, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 12, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            // Tanda Vital / Observasi Harian (form 13): Dokter & Perawat
            // create/read/update/delete, semua jenis rawat.
            ['profesi_id' => 1, 'form_id' => 13, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 13, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            // Bundle VAP (form 14): Dokter & Perawat create/read/update/delete,
            // semua jenis rawat.
            ['profesi_id' => 1, 'form_id' => 14, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 14, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            // Alat Invasif (form 15): Dokter & Perawat create/read/update/delete,
            // semua jenis rawat.
            ['profesi_id' => 1, 'form_id' => 15, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 15, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            // ===== Fase P1 — Catatan Medis Lanjutan =====
            // Resume Medis (form 16): Dokter & Perawat full CRUD.
            ['profesi_id' => 1, 'form_id' => 16, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 16, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            // Care Plan (form 20): Dokter & Perawat full CRUD.
            ['profesi_id' => 1, 'form_id' => 20, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 20, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            // DAR (form 21): Dokter full CRUD; Perawat READ SAJA (dokumen
            // forensik hanya boleh dibuat dokter).
            ['profesi_id' => 1, 'form_id' => 21, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 21, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
            // Catatan Medis Visum (form 57): HANYA Dokter (1). Sengaja TIDAK ada
            // baris untuk Perawat — dokumen bermeterai & bertanda tangan. Tanpa
            // baris akses, form tidak muncul di dashboard perawat & URL 403.
            ['profesi_id' => 1, 'form_id' => 57, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            // Discharge Planning (form 17): Dokter & Perawat full CRUD.
            ['profesi_id' => 1, 'form_id' => 17, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 17, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            // Pemulangan Pasien (form 18): Dokter & Perawat full CRUD.
            ['profesi_id' => 1, 'form_id' => 18, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 18, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            // Triage IGD (form 19): Dokter & Perawat full CRUD.
            ['profesi_id' => 1, 'form_id' => 19, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 19, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 1, 'form_id' => 22, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
            ['profesi_id' => 2, 'form_id' => 22, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 1, 'form_id' => 23, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
            ['profesi_id' => 2, 'form_id' => 23, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 1, 'form_id' => 24, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 24, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 1, 'form_id' => 25, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 25, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 1, 'form_id' => 26, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 26, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 1, 'form_id' => 27, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 27, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 1, 'form_id' => 28, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 28, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 1, 'form_id' => 29, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
            ['profesi_id' => 2, 'form_id' => 29, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 1],
            ['profesi_id' => 1, 'form_id' => 30, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 0],
            ['profesi_id' => 2, 'form_id' => 30, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 0],
            ['profesi_id' => 1, 'form_id' => 31, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 0],
            ['profesi_id' => 2, 'form_id' => 31, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 0],
            ['profesi_id' => 1, 'form_id' => 32, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 0],
            ['profesi_id' => 2, 'form_id' => 32, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 0],
            ['profesi_id' => 1, 'form_id' => 33, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 0],
            ['profesi_id' => 2, 'form_id' => 33, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 0],
            ['profesi_id' => 1, 'form_id' => 34, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 0],
            ['profesi_id' => 2, 'form_id' => 34, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 0],
            ['profesi_id' => 1, 'form_id' => 35, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 0],
            ['profesi_id' => 2, 'form_id' => 35, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 0],
            ['profesi_id' => 1, 'form_id' => 36, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 36, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 1, 'form_id' => 37, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 37, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 1, 'form_id' => 38, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 38, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 1, 'form_id' => 54, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 54, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 1, 'form_id' => 55, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 55, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 1, 'form_id' => 56, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 56, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 1, 'form_id' => 123, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 0],
            ['profesi_id' => 2, 'form_id' => 123, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 0],
            ['profesi_id' => 1, 'form_id' => 124, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 124, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 0, 'akses_read' => 1, 'akses_update' => 0, 'akses_delete' => 0],
            ['profesi_id' => 1, 'form_id' => 125, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 0],
            ['profesi_id' => 2, 'form_id' => 125, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 0],
        ];
        foreach ($akses as $row) {
            $exists = DB::table('akses_ehr')
                ->where('profesi_id', $row['profesi_id'])
                ->where('form_id', $row['form_id'])
                ->exists();
            if ($exists) {
                continue;
            }
            DB::table('akses_ehr')->insert(array_merge($row, [
                'input_time' => $now,
                'input_user_id' => 1,
                'status_batal' => 0,
            ]));
        }
    }
}
