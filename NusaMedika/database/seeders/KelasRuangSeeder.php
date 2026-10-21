<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KelasRuangSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $kelasList = [
            ['kelas_ruang_id' => 1, 'nama_kelas_ruang' => 'Kelas 1', 'kelas_khusus' => null, 'kelas_bpjs' => 1],
            ['kelas_ruang_id' => 2, 'nama_kelas_ruang' => 'Kelas 2', 'kelas_khusus' => null, 'kelas_bpjs' => 2],
            ['kelas_ruang_id' => 3, 'nama_kelas_ruang' => 'Kelas 3', 'kelas_khusus' => null, 'kelas_bpjs' => 3],
            ['kelas_ruang_id' => 4, 'nama_kelas_ruang' => 'VIP', 'kelas_khusus' => 'KHUSUS', 'kelas_bpjs' => null],
            ['kelas_ruang_id' => 5, 'nama_kelas_ruang' => 'VVIP', 'kelas_khusus' => 'KHUSUS', 'kelas_bpjs' => null],
        ];

        foreach ($kelasList as $kelas) {
            DB::table('kelas_ruang')->updateOrInsert(
                ['kelas_ruang_id' => $kelas['kelas_ruang_id']],
                array_merge($kelas, [
                    'input_time' => $now,
                    'input_user_id' => 1,
                    'status_batal' => 0,
                ])
            );
        }
    }
}
