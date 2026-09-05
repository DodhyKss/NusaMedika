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
            ['dashboard_menu_id' => 2, 'nama_menu' => 'Pengkajian'],
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

        $subMenus = [
            // Menu 1 "Catatan Medis"
            ['dashboard_menu_sub_id' => 1, 'dashboard_menu_id' => 1, 'nama_sub_menu' => 'Soap'],
            // Menu 2 "Pengkajian"
            ['dashboard_menu_sub_id' => 2, 'dashboard_menu_id' => 2, 'nama_sub_menu' => 'Pengkajian Keperawatan'],
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

        $extras = [
            // Sub Menu 2 "Pengkajian Keperawatan"
            ['dashboard_menu_sub_extra_id' => 1, 'dashboard_menu_sub_id' => 2, 'nama_sub_menu_extra' => 'Pengkajian Awal Keperawatan'],
            ['dashboard_menu_sub_extra_id' => 2, 'dashboard_menu_sub_id' => 2, 'nama_sub_menu_extra' => 'Pengkajian Harian Keperawatan'],
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
                'nyeri' => 14, 'alergi' => 17, 'up_go_1_a' => 66, 'up_go_1_b' => 67, 'up_go_2' => 68,
            ],
            4 => [
                'keluhan' => 13, 'kesadaran' => 51, 'td' => 6, 'nadi' => 10, 'suhu' => 11,
                'pernapasan' => 12, 'saturasi' => 15, 'berat_badan' => 8, 'tinggi_badan' => 9,
                'ews' => 16, 'gcs_jumlah' => 57, 'pemberian_o2' => 18,
                'cara_pemberian_o2' => 19, 'ett' => 20,
            ],
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

        // Isi objek_id data legacy yang tadinya NULL (dulu env('OBJEK_ID_*') kosong),
        // agar baca berbasis objek tetap cocok dengan data lama.
        EmrHelper::backfillObjekId(1);
        EmrHelper::backfillObjekId(2);
        EmrHelper::backfillObjekId(3);
        EmrHelper::backfillObjekId(4);

        // ======== Akses EHR per profesi ========
        // Idempotent: lewati kombinasi profesi+form yang sudah ada (tanpa bentrok dengan level/bagian lain).
        $akses = [
            // Dokter (profesi 1): seluruh form
            ['profesi_id' => 1, 'form_id' => 1, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 1, 'form_id' => 2, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 1, 'form_id' => 3, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 1, 'form_id' => 4, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            // Perawat (profesi 2): form pengkajian saja
            ['profesi_id' => 2, 'form_id' => 3, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
            ['profesi_id' => 2, 'form_id' => 4, 'level_id' => 1, 'bagian_id' => null, 'akses_create' => 1, 'akses_read' => 1, 'akses_update' => 1, 'akses_delete' => 1],
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
