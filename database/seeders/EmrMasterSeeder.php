<?php

namespace Database\Seeders;

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
        $forms = [
            ['form_id' => 1, 'nama_form' => 'Catatan Awal Medis', 'id_dash_menu' => null, 'ri' => 1, 'rj' => 1, 'igd' => 1, 'mcu' => 1],
            ['form_id' => 2, 'nama_form' => 'SOAP / CPPT', 'id_dash_menu' => '1.1', 'ri' => 1, 'rj' => 1, 'igd' => 1, 'mcu' => 1],
            ['form_id' => 3, 'nama_form' => 'Pengkajian Awal Keperawatan', 'id_dash_menu' => '2.2.1', 'ri' => 1, 'rj' => 1, 'igd' => 1, 'mcu' => 1],
            ['form_id' => 4, 'nama_form' => 'Pengkajian Harian Keperawatan', 'id_dash_menu' => '2.2.2', 'ri' => 1, 'rj' => 1, 'igd' => 1, 'mcu' => 1],
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
