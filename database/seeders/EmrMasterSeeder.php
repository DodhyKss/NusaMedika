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

        // ======== Akses EHR per profesi ========
        // Idempotent: lewati kombinasi profesi+form yang sudah ada (tanpa bentrok dengan level/bagian lain).
        $akses = [
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
