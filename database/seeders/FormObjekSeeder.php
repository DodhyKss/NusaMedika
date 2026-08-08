<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FormObjekSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        // ================== PROFESI ==================
        // PROFESI_ID_DOKTER=1 di .env
        $profesis = [
            ['profesi_id' => 1, 'nama_profesi' => 'Dokter', 'ehr' => 1],
            ['profesi_id' => 2, 'nama_profesi' => 'Perawat', 'ehr' => 1],
            ['profesi_id' => 3, 'nama_profesi' => 'Admin', 'ehr' => 0],
        ];

        foreach ($profesis as $profesi) {
            DB::table('profesi')->updateOrInsert(
                ['profesi_id' => $profesi['profesi_id']],
                array_merge($profesi, [
                    'input_time' => $now,
                    'input_user_id' => 1,
                    'status_batal' => 0,
                ])
            );
        }

        // ================== BAGIAN ==================
        // REF_BAGIAN_RAJAL=1 di .env
        $bagians = [
            ['bagian_id' => 1, 'nama_bagian' => 'Rawat Jalan', 'referensi_bagian' => 1, 'group_bagian' => 'RJ'],
            ['bagian_id' => 2, 'nama_bagian' => 'Rawat Inap', 'referensi_bagian' => 2, 'group_bagian' => 'RI'],
            ['bagian_id' => 3, 'nama_bagian' => 'IGD', 'referensi_bagian' => 3, 'group_bagian' => 'IGD'],
        ];

        foreach ($bagians as $bagian) {
            DB::table('bagian')->updateOrInsert(
                ['bagian_id' => $bagian['bagian_id']],
                array_merge($bagian, [
                    'input_time' => $now,
                    'input_user_id' => 1,
                    'status_batal' => 0,
                ])
            );
        }

        // ================== DASHBOARD MENU (EMR) ==================
        // id_dash_menu format: "dashboard_menu_id.dashboard_menu_sub_id.dashboard_menu_sub_extra_id"
        $dashboardMenus = [
            ['dashboard_menu_id' => 1, 'nama_menu' => 'SOAP'],
            ['dashboard_menu_id' => 2, 'nama_menu' => 'Pengkajian Keperawatan'],
        ];

        foreach ($dashboardMenus as $menu) {
            DB::table('dashboard_menu')->updateOrInsert(
                ['dashboard_menu_id' => $menu['dashboard_menu_id']],
                array_merge($menu, [
                    'input_time' => $now,
                    'input_user_id' => 1,
                    'status_batal' => 0,
                ])
            );
        }

        $dashboardMenuSubs = [
            ['dashboard_menu_sub_id' => 1, 'dashboard_menu_id' => 1, 'nama_sub_menu' => 'SOAP (CPPT)'],
            ['dashboard_menu_sub_id' => 2, 'dashboard_menu_id' => 1, 'nama_sub_menu' => 'Catatan Awal Medis'],
            ['dashboard_menu_sub_id' => 3, 'dashboard_menu_id' => 2, 'nama_sub_menu' => 'Pengkajian Awal Keperawatan'],
            ['dashboard_menu_sub_id' => 4, 'dashboard_menu_id' => 2, 'nama_sub_menu' => 'Pengkajian Harian Keperawatan'],
        ];

        foreach ($dashboardMenuSubs as $sub) {
            DB::table('dashboard_menu_sub')->updateOrInsert(
                ['dashboard_menu_sub_id' => $sub['dashboard_menu_sub_id']],
                array_merge($sub, [
                    'input_time' => $now,
                    'input_user_id' => 1,
                    'status_batal' => 0,
                ])
            );
        }

        // ================== FORM ==================
        // FORM_ID_* di .env
        $forms = [
            // Catatan Awal Medis (id_dash_menu: SOAP -> Catatan Awal Medis)
            ['form_id' => (int) env('FORM_ID_CATATAN_AWAL_MEDIS', 1), 'nama_form' => 'Catatan Awal Medis', 'id_dash_menu' => '1.2', 'ri' => 1, 'rj' => 1, 'igd' => 1, 'mcu' => 0],
            // SOAP / CPPT (id_dash_menu: SOAP -> SOAP CPPT)
            ['form_id' => (int) env('FORM_ID_SOAP', 3), 'nama_form' => 'SOAP (CPPT)', 'id_dash_menu' => '1.1', 'ri' => 1, 'rj' => 1, 'igd' => 1, 'mcu' => 1],
            // Pengkajian Awal Keperawatan
            ['form_id' => (int) env('FORM_ID_PENGKAJIAN_AWAL_KEPERAWATAN', 6), 'nama_form' => 'Pengkajian Awal Keperawatan', 'id_dash_menu' => '2.3', 'ri' => 1, 'rj' => 1, 'igd' => 1, 'mcu' => 0],
            // Pengkajian Harian Keperawatan
            ['form_id' => (int) env('FORM_ID_PENGKAJIAN_HARIAN_KEPERAWATAN', 112), 'nama_form' => 'Pengkajian Harian Keperawatan', 'id_dash_menu' => '2.4', 'ri' => 1, 'rj' => 1, 'igd' => 1, 'mcu' => 0],
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

        // ================== OBJEK ==================
        // OBJEK_ID_* di .env (nama diambil dari komentar .env)
        $objeks = [
            1 => 'Subjective',
            2 => 'Objective',
            3 => 'Assessment',
            4 => 'Planning',
            5 => 'Instruksi',
            6 => 'Sistolik',
            7 => 'Diastolik',
            10 => 'Berat Badan',
            11 => 'Tinggi Badan',
            12 => 'Nadi',
            13 => 'Suhu',
            14 => 'Pernapasan',
            15 => 'Keluhan',
            17 => 'Nyeri',
            28 => 'Saturasi',
            69 => 'EWS',
            141 => 'Alergi',
            157 => 'Oksigen',
            158 => 'Cara Pemberian',
            159 => 'ETT',
            169 => 'Agama',
            170 => 'Kegiatan Ibadah/Budaya',
            171 => 'Tingkat Pendidikan',
            172 => 'Pekerjaan',
            173 => 'Suku Bangsa',
            174 => 'Kebangsaan',
            175 => 'Aktifitas Sebelum Makan',
            176 => 'Pantangan Pulang',
            177 => 'Pantangan Transfusi Darah',
            178 => 'Pantangan Makan',
            179 => 'Nama Pasangan',
            180 => 'Usia Pasangan',
            181 => 'Pendidikan Pasangan',
            182 => 'Pekerjaan Pasangan',
            183 => 'Suku Bangsa Pasangan',
            184 => 'Kebangsaan Pasangan',
            185 => 'Tinggal Bersama',
            186 => 'Penanggung Jawab Pasien',
            187 => 'Hubungan Pasien',
            224 => 'Diagnosa Medis',
            225 => 'Riwayat Penyakit Sebelumnya',
            226 => 'Riwayat Penyakit Sekarang',
            227 => 'Infeksius Flag',
            228 => 'Menular Melalui',
            229 => 'Infeksius Memerlukan Isolasi',
            230 => 'Infeksius Hasil Penunjang',
            231 => 'Imunologi Flag',
            232 => 'Imunologi Memerlukan Isolasi',
            233 => 'Imunologi Pembatasan Pengunjung',
            234 => 'Imunologi Hasil Penunjang',
            235 => 'Kesadaran',
            236 => 'Riwayat Kemoterapi',
            237 => 'Riwayat Radioterapi',
            238 => 'GCS E',
            239 => 'GCS M',
            240 => 'GCS V',
            241 => 'GCS Score',
            255 => 'BMI',
            281 => 'DPO',
            417 => 'Handphone',
            443 => 'Riwayat Operasi Kemo',
            1351 => 'Vaksin Covid',
            3001 => 'Allo Anamnesa',
            3002 => 'Nama Allo',
            3003 => 'Hubungan Allo',
            3004 => 'UP GO 1A',
            3005 => 'UP GO 1B',
            3006 => 'UP GO 2',
        ];

        foreach ($objeks as $objekId => $namaObjek) {
            DB::table('objek')->updateOrInsert(
                ['objek_id' => $objekId],
                [
                    'objek_id' => $objekId,
                    'nama_objek' => $namaObjek,
                    'input_time' => $now,
                    'input_user_id' => 1,
                    'status_batal' => 0,
                ]
            );
        }

        // ================== OBJEK FORM CONTROL ==================
        // bagian_id 1 (Rawat Jalan) - mapping form ke objek
        $formObjeks = [
            (int) env('FORM_ID_SOAP', 3) => [1, 2, 3, 4, 15, 17, 224],
            (int) env('FORM_ID_CATATAN_AWAL_MEDIS', 1) => [15, 225, 226, 224],
            (int) env('FORM_ID_PENGKAJIAN_AWAL_KEPERAWATAN', 6) => [
                169, 170, 171, 172, 173, 174, 417,
                179, 180, 181, 182, 183, 184, 185, 186, 187,
                175, 176, 177, 178,
                225, 226, 224,
                227, 228, 229, 230, 231, 232, 233, 234,
                235, 238, 239, 240, 241,
                281, 6, 7, 12, 13, 14, 10, 11, 157, 158, 159, 28, 69, 255, 17, 141,
                3001, 3002, 3003, 3004, 3005, 3006,
            ],
            (int) env('FORM_ID_PENGKAJIAN_HARIAN_KEPERAWATAN', 112) => [
                6, 7, 12, 13, 14, 28, 69, 17, 15,
            ],
        ];

        $objekFormControlId = 1;
        foreach ($formObjeks as $formId => $objekIds) {
            foreach ($objekIds as $objekId) {
                DB::table('objek_form_control')->updateOrInsert(
                    [
                        'form_id' => $formId,
                        'objek_id' => $objekId,
                        'bagian_id' => 1,
                    ],
                    [
                        'objek_form_control_id' => $objekFormControlId++,
                        'input_time' => $now,
                        'input_user_id' => 1,
                        'status_batal' => 0,
                    ]
                );
            }
        }

        // ================== AKSES EHR ==================
        // Dokter (1) bisa akses semua form; Perawat (2) form pengkajian + SOAP
        $aksesEhrs = [
            ['profesi_id' => 1, 'form_id' => (int) env('FORM_ID_SOAP', 3), 'level_id' => 1, 'bagian_id' => 1],
            ['profesi_id' => 1, 'form_id' => (int) env('FORM_ID_CATATAN_AWAL_MEDIS', 1), 'level_id' => 1, 'bagian_id' => 1],
            ['profesi_id' => 1, 'form_id' => (int) env('FORM_ID_PENGKAJIAN_AWAL_KEPERAWATAN', 6), 'level_id' => 1, 'bagian_id' => 1],
            ['profesi_id' => 1, 'form_id' => (int) env('FORM_ID_PENGKAJIAN_HARIAN_KEPERAWATAN', 112), 'level_id' => 1, 'bagian_id' => 1],
            ['profesi_id' => 2, 'form_id' => (int) env('FORM_ID_SOAP', 3), 'level_id' => 1, 'bagian_id' => 1],
            ['profesi_id' => 2, 'form_id' => (int) env('FORM_ID_PENGKAJIAN_AWAL_KEPERAWATAN', 6), 'level_id' => 1, 'bagian_id' => 1],
            ['profesi_id' => 2, 'form_id' => (int) env('FORM_ID_PENGKAJIAN_HARIAN_KEPERAWATAN', 112), 'level_id' => 1, 'bagian_id' => 1],
        ];

        $aksesEhrId = 1;
        foreach ($aksesEhrs as $aksesEhr) {
            DB::table('akses_ehr')->updateOrInsert(
                [
                    'profesi_id' => $aksesEhr['profesi_id'],
                    'form_id' => $aksesEhr['form_id'],
                    'bagian_id' => $aksesEhr['bagian_id'],
                ],
                array_merge($aksesEhr, [
                    'akses_ehr_id' => $aksesEhrId++,
                    'input_time' => $now,
                    'input_user_id' => 1,
                    'status_batal' => 0,
                ])
            );
        }
    }
}
