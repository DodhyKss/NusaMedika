<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ModulMenuSubMenuSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $moduls = [
            [
                'modul_id' => 1,
                'nama_modul' => 'Registrasi',
                'icon_modul' => 'fa-solid fa-clipboard-list',
                'urutan_modul' => 1,
            ],
            [
                'modul_id' => 2,
                'nama_modul' => 'EMR',
                'icon_modul' => 'fa-solid fa-notes-medical',
                'urutan_modul' => 2,
            ],
            [
                'modul_id' => 3,
                'nama_modul' => 'Rawat Jalan',
                'icon_modul' => 'fa-solid fa-user-injured',
                'urutan_modul' => 3,
            ],
            [
                'modul_id' => 4,
                'nama_modul' => 'Rawat Inap',
                'icon_modul' => 'fa-solid fa-bed',
                'urutan_modul' => 4,
            ],
            [
                'modul_id' => 5,
                'nama_modul' => 'Gawat Darurat',
                'icon_modul' => 'fa-solid fa-truck-medical',
                'urutan_modul' => 5,
            ],
            [
                'modul_id' => 6,
                'nama_modul' => 'Administrator',
                'icon_modul' => 'fa-solid fa-gear',
                'urutan_modul' => 6,
            ],
        ];

        foreach ($moduls as $modul) {
            DB::table('modul')->updateOrInsert(
                ['modul_id' => $modul['modul_id']],
                array_merge($modul, [
                    'input_time' => $now,
                    'input_user_id' => 1,
                    'status_batal' => 0,
                ])
            );
        }

        $menus = [
            // Modul Registrasi (1)
            ['menu_id' => 1, 'modul_id' => 1, 'nama_menu' => 'Pasien', 'urutan_menu' => 1],
            ['menu_id' => 2, 'modul_id' => 1, 'nama_menu' => 'Pendaftaran', 'urutan_menu' => 2],
            // Modul EMR (2)
            ['menu_id' => 3, 'modul_id' => 2, 'nama_menu' => 'Dashboard Pasien', 'urutan_menu' => 1],
            ['menu_id' => 4, 'modul_id' => 2, 'nama_menu' => 'SOAP (CPPT)', 'urutan_menu' => 2],
            ['menu_id' => 5, 'modul_id' => 2, 'nama_menu' => 'Pengkajian Keperawatan', 'urutan_menu' => 3],
            // Modul Rawat Jalan (3)
            ['menu_id' => 6, 'modul_id' => 3, 'nama_menu' => 'Pasien', 'urutan_menu' => 1],
            // Modul Rawat Inap (4)
            ['menu_id' => 7, 'modul_id' => 4, 'nama_menu' => 'Pasien', 'urutan_menu' => 1],
            // Modul Gawat Darurat (5)
            ['menu_id' => 8, 'modul_id' => 5, 'nama_menu' => 'Pasien', 'urutan_menu' => 1],
            // Modul Administrator (6)
            ['menu_id' => 9, 'modul_id' => 6, 'nama_menu' => 'Manajemen Master', 'urutan_menu' => 1],
            ['menu_id' => 10, 'modul_id' => 6, 'nama_menu' => 'Manajemen User', 'urutan_menu' => 2],
        ];

        foreach ($menus as $menu) {
            DB::table('menu')->updateOrInsert(
                ['menu_id' => $menu['menu_id']],
                array_merge($menu, [
                    'input_time' => $now,
                    'input_user_id' => 1,
                    'status_batal' => 0,
                ])
            );
        }

        $subMenus = [
            // Menu Pasien (1) - Registrasi
            ['sub_menu_id' => 1, 'menu_id' => 1, 'nama_sub_menu' => 'Daftar Pasien', 'file_sub_menu' => 'daftar_pasien', 'urutan_sub_menu' => 1],
            ['sub_menu_id' => 2, 'menu_id' => 1, 'nama_sub_menu' => 'Nasabah Pasien', 'file_sub_menu' => 'nasabah_pasien', 'urutan_sub_menu' => 2],
            // Menu Pendaftaran (2) - Registrasi
            ['sub_menu_id' => 3, 'menu_id' => 2, 'nama_sub_menu' => 'List Pelayanan Pasien', 'file_sub_menu' => 'list_pelayanan_pasien', 'urutan_sub_menu' => 1],
            ['sub_menu_id' => 4, 'menu_id' => 2, 'nama_sub_menu' => 'Daftar Rawat Jalan', 'file_sub_menu' => 'daftar_rajal', 'urutan_sub_menu' => 2],
            ['sub_menu_id' => 5, 'menu_id' => 2, 'nama_sub_menu' => 'Daftar Rawat Inap', 'file_sub_menu' => 'daftar_ranap', 'urutan_sub_menu' => 3],
            ['sub_menu_id' => 6, 'menu_id' => 2, 'nama_sub_menu' => 'Registrasi IGD', 'file_sub_menu' => 'registrasi_igd', 'urutan_sub_menu' => 4],
            ['sub_menu_id' => 7, 'menu_id' => 2, 'nama_sub_menu' => 'Registrasi IGD Obgyn', 'file_sub_menu' => 'registrasi_igd_obgyn', 'urutan_sub_menu' => 5],
            // Menu Dashboard Pasien (3) - EMR
            ['sub_menu_id' => 8, 'menu_id' => 3, 'nama_sub_menu' => 'Dashboard Pasien', 'file_sub_menu' => '#', 'urutan_sub_menu' => 1],
            // Menu SOAP (4) - EMR
            ['sub_menu_id' => 9, 'menu_id' => 4, 'nama_sub_menu' => 'SOAP (CPPT)', 'file_sub_menu' => '#', 'urutan_sub_menu' => 1],
            // Menu Pengkajian Keperawatan (5) - EMR
            ['sub_menu_id' => 10, 'menu_id' => 5, 'nama_sub_menu' => 'Pengkajian Awal Keperawatan', 'file_sub_menu' => '#', 'urutan_sub_menu' => 1],
            ['sub_menu_id' => 11, 'menu_id' => 5, 'nama_sub_menu' => 'Pengkajian Harian Keperawatan', 'file_sub_menu' => '#', 'urutan_sub_menu' => 2],
            // Menu Pasien (6) - Rawat Jalan
            ['sub_menu_id' => 12, 'menu_id' => 6, 'nama_sub_menu' => 'List Pasien Dokter', 'file_sub_menu' => 'list_pasien_dokter', 'urutan_sub_menu' => 1],
            // Menu Pasien (7) - Rawat Inap
            ['sub_menu_id' => 13, 'menu_id' => 7, 'nama_sub_menu' => 'List Pasien Ranap', 'file_sub_menu' => 'list_pasien_ranap', 'urutan_sub_menu' => 1],
            // Menu Pasien (8) - Gawat Darurat
            ['sub_menu_id' => 14, 'menu_id' => 8, 'nama_sub_menu' => 'List Pasien IGD', 'file_sub_menu' => 'list_pasien_igd', 'urutan_sub_menu' => 1],
            // Menu Manajemen Master (9) - Administrator
            ['sub_menu_id' => 15, 'menu_id' => 9, 'nama_sub_menu' => 'Modul', 'file_sub_menu' => 'modul', 'urutan_sub_menu' => 1],
            ['sub_menu_id' => 16, 'menu_id' => 9, 'nama_sub_menu' => 'Menu', 'file_sub_menu' => 'menu', 'urutan_sub_menu' => 2],
            ['sub_menu_id' => 17, 'menu_id' => 9, 'nama_sub_menu' => 'Sub Menu', 'file_sub_menu' => 'sub_menu', 'urutan_sub_menu' => 3],
            ['sub_menu_id' => 19, 'menu_id' => 9, 'nama_sub_menu' => 'Bagian', 'file_sub_menu' => 'bagian', 'urutan_sub_menu' => 4],
            ['sub_menu_id' => 20, 'menu_id' => 9, 'nama_sub_menu' => 'Profesi', 'file_sub_menu' => 'profesi', 'urutan_sub_menu' => 5],
            ['sub_menu_id' => 21, 'menu_id' => 9, 'nama_sub_menu' => 'Jabatan', 'file_sub_menu' => 'jabatan', 'urutan_sub_menu' => 6],
            ['sub_menu_id' => 22, 'menu_id' => 9, 'nama_sub_menu' => 'Pegawai', 'file_sub_menu' => 'pegawai', 'urutan_sub_menu' => 7],
            ['sub_menu_id' => 23, 'menu_id' => 9, 'nama_sub_menu' => 'Referensi Bagian', 'file_sub_menu' => 'referensi_bagian', 'urutan_sub_menu' => 8],
            // Menu Manajemen User (10) - Administrator
            ['sub_menu_id' => 18, 'menu_id' => 10, 'nama_sub_menu' => 'User', 'file_sub_menu' => 'user', 'urutan_sub_menu' => 1],
        ];

        foreach ($subMenus as $subMenu) {
            DB::table('sub_menu')->updateOrInsert(
                ['sub_menu_id' => $subMenu['sub_menu_id']],
                array_merge($subMenu, [
                    'input_time' => $now,
                    'input_user_id' => 1,
                    'status_batal' => 0,
                ])
            );
        }
    }
}
