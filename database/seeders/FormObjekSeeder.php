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
        // FORM_ID_* di .env (id berurutan tanpa gap karena primary key)
        $forms = [
            // Catatan Awal Medis (id_dash_menu: SOAP -> Catatan Awal Medis)
            ['form_id' => (int) env('FORM_ID_CATATAN_AWAL_MEDIS', 1), 'nama_form' => 'Catatan Awal Medis', 'id_dash_menu' => '1.2', 'ri' => 1, 'rj' => 1, 'igd' => 1, 'mcu' => 0],
            // SOAP / CPPT (id_dash_menu: SOAP -> SOAP CPPT)
            ['form_id' => (int) env('FORM_ID_SOAP', 2), 'nama_form' => 'SOAP (CPPT)', 'id_dash_menu' => '1.1', 'ri' => 1, 'rj' => 1, 'igd' => 1, 'mcu' => 1],
            // Pengkajian Awal Keperawatan
            ['form_id' => (int) env('FORM_ID_PENGKAJIAN_AWAL_KEPERAWATAN', 3), 'nama_form' => 'Pengkajian Awal Keperawatan', 'id_dash_menu' => '2.3', 'ri' => 1, 'rj' => 1, 'igd' => 1, 'mcu' => 0],
            // Pengkajian Harian Keperawatan
            ['form_id' => (int) env('FORM_ID_PENGKAJIAN_HARIAN_KEPERAWATAN', 4), 'nama_form' => 'Pengkajian Harian Keperawatan', 'id_dash_menu' => '2.4', 'ri' => 1, 'rj' => 1, 'igd' => 1, 'mcu' => 0],
        ];

        // ================== OBJEK ==================
        // OBJEK_ID_* di .env — id berurutan 1..68 tanpa gap (primary key)
        $objeks = [
            1 => 'Subjective',
            2 => 'Objective',
            3 => 'Assessment',
            4 => 'Planning',
            5 => 'Instruksi',
            6 => 'Sistolik',
            7 => 'Diastolik',
            8 => 'Berat Badan',
            9 => 'Tinggi Badan',
            10 => 'Nadi',
            11 => 'Suhu',
            12 => 'Pernapasan',
            13 => 'Keluhan',
            14 => 'Nyeri',
            15 => 'Saturasi',
            16 => 'EWS',
            17 => 'Alergi',
            18 => 'Oksigen',
            19 => 'Cara Pemberian',
            20 => 'ETT',
            21 => 'Agama',
            22 => 'Kegiatan Ibadah/Budaya',
            23 => 'Tingkat Pendidikan',
            24 => 'Pekerjaan',
            25 => 'Suku Bangsa',
            26 => 'Kebangsaan',
            27 => 'Aktifitas Sebelum Makan',
            28 => 'Pantangan Pulang',
            29 => 'Pantangan Transfusi Darah',
            30 => 'Pantangan Makan',
            31 => 'Nama Pasangan',
            32 => 'Usia Pasangan',
            33 => 'Pendidikan Pasangan',
            34 => 'Pekerjaan Pasangan',
            35 => 'Suku Bangsa Pasangan',
            36 => 'Kebangsaan Pasangan',
            37 => 'Tinggal Bersama',
            38 => 'Penanggung Jawab Pasien',
            39 => 'Hubungan Pasien',
            40 => 'Diagnosa Medis',
            41 => 'Riwayat Penyakit Sebelumnya',
            42 => 'Riwayat Penyakit Sekarang',
            43 => 'Infeksius Flag',
            44 => 'Menular Melalui',
            45 => 'Infeksius Memerlukan Isolasi',
            46 => 'Infeksius Hasil Penunjang',
            47 => 'Imunologi Flag',
            48 => 'Imunologi Memerlukan Isolasi',
            49 => 'Imunologi Pembatasan Pengunjung',
            50 => 'Imunologi Hasil Penunjang',
            51 => 'Kesadaran',
            52 => 'Riwayat Kemoterapi',
            53 => 'Riwayat Radioterapi',
            54 => 'GCS E',
            55 => 'GCS M',
            56 => 'GCS V',
            57 => 'GCS Score',
            58 => 'BMI',
            59 => 'DPO',
            60 => 'Handphone',
            61 => 'Riwayat Operasi Kemo',
            62 => 'Vaksin Covid',
            63 => 'Allo Anamnesa',
            64 => 'Nama Allo',
            65 => 'Hubungan Allo',
            66 => 'UP GO 1A',
            67 => 'UP GO 1B',
            68 => 'UP GO 2',
        ];

        // ================== PEMBERSIHAN ID LAMA ==================
        // Sebelumnya id tidak berurutan (form 1/3/6/112, objek hingga 1351/3006).
        // Hapus baris ber-id lama agar penomoran baru 1..n berurutan tanpa gap.
        $oldFormIds = [1, 3, 6, 112];
        $oldObjekIds = [1, 2, 3, 4, 5, 6, 7, 10, 11, 12, 13, 14, 15, 17, 28, 69, 141, 157, 158, 159, 169, 170, 171, 172, 173, 174, 175, 176, 177, 178, 179, 180, 181, 182, 183, 184, 185, 186, 187, 224, 225, 226, 227, 228, 229, 230, 231, 232, 233, 234, 235, 236, 237, 238, 239, 240, 241, 255, 281, 417, 443, 1351, 3001, 3002, 3003, 3004, 3005, 3006];

        $seededFormIds = array_unique(array_merge($oldFormIds, array_column($forms, 'form_id')));

        DB::table('objek_form_control')->whereIn('form_id', $seededFormIds)->delete();
        DB::table('akses_ehr')->whereIn('form_id', $seededFormIds)->delete();
        DB::table('objek')->whereIn('objek_id', $oldObjekIds)->delete();
        DB::table('form')->whereIn('form_id', $seededFormIds)->delete();

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
            (int) env('FORM_ID_SOAP', 2) => [1, 2, 3, 4, 13, 14, 40],
            (int) env('FORM_ID_CATATAN_AWAL_MEDIS', 1) => [13, 41, 42, 40],
            (int) env('FORM_ID_PENGKAJIAN_AWAL_KEPERAWATAN', 3) => [
                21, 22, 23, 24, 25, 26, 60,
                31, 32, 33, 34, 35, 36, 37, 38, 39,
                27, 28, 29, 30,
                41, 42, 40,
                43, 44, 45, 46, 47, 48, 49, 50,
                51, 54, 55, 56, 57,
                59, 6, 7, 10, 11, 12, 8, 9, 18, 19, 20, 15, 16, 58, 14, 17,
                63, 64, 65, 66, 67, 68,
            ],
            (int) env('FORM_ID_PENGKAJIAN_HARIAN_KEPERAWATAN', 4) => [
                6, 7, 10, 11, 12, 15, 16, 14, 13,
            ],
        ];

        $objekFormControlId = (int) DB::table('objek_form_control')->max('objek_form_control_id') + 1;
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
            ['profesi_id' => 1, 'form_id' => (int) env('FORM_ID_SOAP', 2), 'level_id' => 1, 'bagian_id' => 1],
            ['profesi_id' => 1, 'form_id' => (int) env('FORM_ID_CATATAN_AWAL_MEDIS', 1), 'level_id' => 1, 'bagian_id' => 1],
            ['profesi_id' => 1, 'form_id' => (int) env('FORM_ID_PENGKAJIAN_AWAL_KEPERAWATAN', 3), 'level_id' => 1, 'bagian_id' => 1],
            ['profesi_id' => 1, 'form_id' => (int) env('FORM_ID_PENGKAJIAN_HARIAN_KEPERAWATAN', 4), 'level_id' => 1, 'bagian_id' => 1],
            ['profesi_id' => 2, 'form_id' => (int) env('FORM_ID_SOAP', 2), 'level_id' => 1, 'bagian_id' => 1],
            ['profesi_id' => 2, 'form_id' => (int) env('FORM_ID_PENGKAJIAN_AWAL_KEPERAWATAN', 3), 'level_id' => 1, 'bagian_id' => 1],
            ['profesi_id' => 2, 'form_id' => (int) env('FORM_ID_PENGKAJIAN_HARIAN_KEPERAWATAN', 4), 'level_id' => 1, 'bagian_id' => 1],
        ];

        $aksesEhrId = (int) DB::table('akses_ehr')->max('akses_ehr_id') + 1;
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
