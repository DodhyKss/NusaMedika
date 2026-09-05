<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ModulMenuSubMenuSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        // ======== Seeder Module =========
        $moduls = [
            ['modul_id' => 1, 'nama_modul' => 'Registrasi', 'icon_modul' => 'fa-solid fa-clipboard-list', 'urutan_modul' => 1],
            ['modul_id' => 2, 'nama_modul' => 'Rawat Jalan', 'icon_modul' => 'fa-solid fa-user-injured', 'urutan_modul' => 3],
            ['modul_id' => 3, 'nama_modul' => 'Rawat Inap', 'icon_modul' => 'fa-solid fa-bed', 'urutan_modul' => 4],
            ['modul_id' => 4, 'nama_modul' => 'Gawat Darurat', 'icon_modul' => 'fa-solid fa-truck-medical', 'urutan_modul' => 5],
            ['modul_id' => 5, 'nama_modul' => 'Administrator', 'icon_modul' => 'fa-solid fa-gear', 'urutan_modul' => 6],
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

        // ========= Seeder Menu ==========
        $menus = [
            // Modul Registrasi (1)
            ['menu_id' => 1, 'modul_id' => 1, 'nama_menu' => 'Pasien', 'urutan_menu' => 1],
            ['menu_id' => 2, 'modul_id' => 1, 'nama_menu' => 'Pendaftaran', 'urutan_menu' => 2],
            // Modul Rawat Jalan (2)
            ['menu_id' => 3, 'modul_id' => 2, 'nama_menu' => 'Pasien', 'urutan_menu' => 1],
            // Modul Rawat Inap (3)
            ['menu_id' => 4, 'modul_id' => 3, 'nama_menu' => 'Pasien', 'urutan_menu' => 1],
            // Modul Gawat Darurat (4)
            ['menu_id' => 5, 'modul_id' => 4, 'nama_menu' => 'Pasien', 'urutan_menu' => 1],
            // Modul Administrator (5)
            ['menu_id' => 6, 'modul_id' => 5, 'nama_menu' => 'Manajemen Master', 'urutan_menu' => 1],
            ['menu_id' => 7, 'modul_id' => 5, 'nama_menu' => 'Manajemen User', 'urutan_menu' => 2],
            ['menu_id' => 8, 'modul_id' => 5, 'nama_menu' => 'Manajemen EMR', 'urutan_menu' => 3],
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

        // ========= Seeder Sub Menu ==========
        // file_sub_menu = path view lengkap BESERTA nama file blade (tanpa .blade.php).
        // Segmen terakhir (basename) = URI & nama route; folder = namespace controller & path view.
        $subMenus = [
            // Menu Pasien (1) - Registrasi
            ['sub_menu_id' => 1, 'menu_id' => 1, 'nama_sub_menu' => 'Daftar Pasien', 'file_sub_menu' => 'Registrasi/Pasien/DaftarPasien/daftar_pasien', 'urutan_sub_menu' => 1],
            ['sub_menu_id' => 2, 'menu_id' => 1, 'nama_sub_menu' => 'Nasabah Pasien', 'file_sub_menu' => 'Registrasi/Pasien/NasabahPasien/nasabah_pasien', 'urutan_sub_menu' => 2],
            // Menu Pendaftaran (2) - Registrasi
            ['sub_menu_id' => 3, 'menu_id' => 2, 'nama_sub_menu' => 'List Pelayanan Pasien', 'file_sub_menu' => 'Registrasi/Pendaftaran/ListPelayananPasien/list_pelayanan_pasien', 'urutan_sub_menu' => 1],
            ['sub_menu_id' => 4, 'menu_id' => 2, 'nama_sub_menu' => 'Daftar Rawat Jalan', 'file_sub_menu' => 'Registrasi/Pendaftaran/DaftarRajal/daftar_rajal', 'urutan_sub_menu' => 2],
            ['sub_menu_id' => 5, 'menu_id' => 2, 'nama_sub_menu' => 'Daftar Rawat Inap', 'file_sub_menu' => 'Registrasi/Pendaftaran/DaftarRanap/daftar_ranap', 'urutan_sub_menu' => 3],
            ['sub_menu_id' => 6, 'menu_id' => 2, 'nama_sub_menu' => 'Registrasi IGD', 'file_sub_menu' => 'Registrasi/Pendaftaran/DaftarGawatDarurat/daftar_gawat_darurat', 'urutan_sub_menu' => 4],
            ['sub_menu_id' => 7, 'menu_id' => 2, 'nama_sub_menu' => 'Registrasi IGD Obgyn', 'file_sub_menu' => 'Registrasi/Pendaftaran/DaftarGawatDaruratObstetriGinekologi/daftar_gawat_darurat_obstetri_ginekologi', 'urutan_sub_menu' => 5],
            // Menu Pasien (3) - Rawat Jalan
            ['sub_menu_id' => 12, 'menu_id' => 3, 'nama_sub_menu' => 'List Pasien Dokter', 'file_sub_menu' => 'RawatJalan/Pasien/ListPasienDokter/list_pasien_dokter', 'urutan_sub_menu' => 1],
            ['sub_menu_id' => 29, 'menu_id' => 3, 'nama_sub_menu' => 'List Pasien', 'file_sub_menu' => 'RawatJalan/Pasien/ListPasienRajal/list_pasien_rajal', 'urutan_sub_menu' => 2],
            // Menu Pasien (4) - Rawat Inap
            ['sub_menu_id' => 13, 'menu_id' => 4, 'nama_sub_menu' => 'List Pasien Ranap', 'file_sub_menu' => 'RawatInap/Pasien/ListPasienRanap/list_pasien_ranap', 'urutan_sub_menu' => 1],
            // Menu Pasien (5) - Gawat Darurat
            ['sub_menu_id' => 14, 'menu_id' => 5, 'nama_sub_menu' => 'List Pasien IGD', 'file_sub_menu' => 'GawatDarurat/Pasien/ListPasienGawatDarurat/list_pasien_gawat_darurat', 'urutan_sub_menu' => 1],
            // Menu Manajemen Master (6) - Administrator
            ['sub_menu_id' => 15, 'menu_id' => 6, 'nama_sub_menu' => 'Modul', 'file_sub_menu' => 'Administrator/ManajemenMaster/Modul/modul', 'urutan_sub_menu' => 1],
            ['sub_menu_id' => 16, 'menu_id' => 6, 'nama_sub_menu' => 'Menu', 'file_sub_menu' => 'Administrator/ManajemenMaster/Menu/menu', 'urutan_sub_menu' => 2],
            ['sub_menu_id' => 17, 'menu_id' => 6, 'nama_sub_menu' => 'Sub Menu', 'file_sub_menu' => 'Administrator/ManajemenMaster/SubMenu/sub_menu', 'urutan_sub_menu' => 3],
            ['sub_menu_id' => 19, 'menu_id' => 6, 'nama_sub_menu' => 'Bagian', 'file_sub_menu' => 'Administrator/ManajemenMaster/Bagian/bagian', 'urutan_sub_menu' => 4],
            ['sub_menu_id' => 20, 'menu_id' => 6, 'nama_sub_menu' => 'Profesi', 'file_sub_menu' => 'Administrator/ManajemenMaster/Profesi/profesi', 'urutan_sub_menu' => 5],
            ['sub_menu_id' => 21, 'menu_id' => 6, 'nama_sub_menu' => 'Jabatan', 'file_sub_menu' => 'Administrator/ManajemenMaster/Jabatan/jabatan', 'urutan_sub_menu' => 6],
            ['sub_menu_id' => 22, 'menu_id' => 6, 'nama_sub_menu' => 'Pegawai', 'file_sub_menu' => 'Administrator/ManajemenMaster/Pegawai/pegawai', 'urutan_sub_menu' => 7],
            ['sub_menu_id' => 23, 'menu_id' => 6, 'nama_sub_menu' => 'Referensi Bagian', 'file_sub_menu' => 'Administrator/ManajemenMaster/ReferensiBagianId/referensi_bagian_id', 'urutan_sub_menu' => 8],
            ['sub_menu_id' => 24, 'menu_id' => 6, 'nama_sub_menu' => 'Wilayah', 'file_sub_menu' => 'Administrator/ManajemenMaster/Wilayah/wilayah', 'urutan_sub_menu' => 9],
            ['sub_menu_id' => 25, 'menu_id' => 6, 'nama_sub_menu' => 'Master Nasabah', 'file_sub_menu' => 'Administrator/ManajemenMaster/Nasabah/nasabah', 'urutan_sub_menu' => 10],
            ['sub_menu_id' => 26, 'menu_id' => 6, 'nama_sub_menu' => 'Master Kelas', 'file_sub_menu' => 'Administrator/ManajemenMaster/Kelas/kelas', 'urutan_sub_menu' => 11],
            ['sub_menu_id' => 27, 'menu_id' => 6, 'nama_sub_menu' => 'Jadwal Dokter', 'file_sub_menu' => 'Administrator/ManajemenMaster/JadwalDokter/jadwal_dokter', 'urutan_sub_menu' => 12],
            ['sub_menu_id' => 28, 'menu_id' => 6, 'nama_sub_menu' => 'ICD', 'file_sub_menu' => 'Administrator/ManajemenMaster/Icd/icd', 'urutan_sub_menu' => 13],
            // Menu Manajemen User (7) - Administrator
            ['sub_menu_id' => 18, 'menu_id' => 7, 'nama_sub_menu' => 'User', 'file_sub_menu' => 'Administrator/ManajemenUser/User/user', 'urutan_sub_menu' => 1],
            // Menu Manajemen EMR (8) - Administrator
            ['sub_menu_id' => 30, 'menu_id' => 8, 'nama_sub_menu' => 'Dashboard Menu', 'file_sub_menu' => 'Administrator/ManajemenEMR/DashboardMenu/dashboard_menu', 'urutan_sub_menu' => 1],
            ['sub_menu_id' => 31, 'menu_id' => 8, 'nama_sub_menu' => 'Akses EHR', 'file_sub_menu' => 'Administrator/ManajemenEMR/AksesEhr/akses_ehr', 'urutan_sub_menu' => 2],
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
