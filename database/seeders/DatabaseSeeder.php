<?php

namespace Database\Seeders;

use App\Helpers\GenerateHelper;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            ReferensiBagianSeeder::class,
            BagianSeeder::class,
            ModulMenuSubMenuSeeder::class,
            MasterPegawaiSeeder::class,
            UserSeeder::class,
            WilayahSeeder::class,
            KelasRuangSeeder::class,
            IcdSeeder::class,
            EmrMasterSeeder::class,
        ]);

        // Seeder di atas mengisi ID eksplisit (updateOrInsert), sehingga sequence
        // auto-increment tidak ikut maju. Setel ulang sequence agar insert baru
        // via aplikasi (yang memakai auto-increment) tidak bentrok dengan ID seeder.
        $this->resetSequences();
    }

    private function resetSequences(): void
    {
        $tables = [
            'referensi_bagian', 'bagian', 'modul', 'menu', 'sub_menu',
            'profesi', 'jabatan', 'status_kepegawaian', 'pegawai', 'users',
            'provinsi', 'kabupaten', 'kecamatan', 'kelurahan', 'kelas_ruang',
            'dashboard_menu', 'dashboard_menu_sub', 'dashboard_menu_sub_extra', 'form', 'akses_ehr',
            'objek', 'objek_form_control',
        ];

        foreach ($tables as $table) {
            GenerateHelper::resetSequence($table);
        }
    }
}
