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
        for ($i = 1; $i <= 4; $i++) { $mappingSkriningMpp['risiko_tinggi_'.$i] = 600; }
        for ($i = 1; $i <= 4; $i++) { $mappingSkriningMpp['risiko_pembiayaan_'.$i] = 601; }
        for ($i = 1; $i <= 4; $i++) { $mappingSkriningMpp['kasus_kompleks_'.$i] = 602; }
        for ($i = 1; $i <= 2; $i++) { $mappingSkriningMpp['risiko_lama_rawat_'.$i] = 603; }
        for ($i = 1; $i <= 4; $i++) { $mappingSkriningMpp['risiko_psikososial_'.$i] = 604; }
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
