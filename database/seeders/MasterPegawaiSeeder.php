<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MasterPegawaiSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $jabatans = [
            ['jabatan_id' => 1, 'nama_jabatan' => 'Direktur'],
            ['jabatan_id' => 2, 'nama_jabatan' => 'Wakil Direktur'],
            ['jabatan_id' => 3, 'nama_jabatan' => 'Kepala Bagian'],
            ['jabatan_id' => 4, 'nama_jabatan' => 'Kepala Ruangan'],
            ['jabatan_id' => 5, 'nama_jabatan' => 'Dokter'],
            ['jabatan_id' => 6, 'nama_jabatan' => 'Perawat'],
            ['jabatan_id' => 7, 'nama_jabatan' => 'Bidan'],
            ['jabatan_id' => 8, 'nama_jabatan' => 'Administrasi'],
        ];

        foreach ($jabatans as $jabatan) {
            DB::table('jabatan')->updateOrInsert(
                ['jabatan_id' => $jabatan['jabatan_id']],
                array_merge($jabatan, [
                    'input_time' => $now,
                    'input_user_id' => 1,
                    'status_batal' => 0,
                ])
            );
        }

        $statuses = [
            ['status_kepegawaian_id' => 1, 'nama_status_kepegawaian' => 'PNS'],
            ['status_kepegawaian_id' => 2, 'nama_status_kepegawaian' => 'PPPK'],
            ['status_kepegawaian_id' => 3, 'nama_status_kepegawaian' => 'Kontrak'],
            ['status_kepegawaian_id' => 4, 'nama_status_kepegawaian' => 'Honorer'],
        ];

        foreach ($statuses as $status) {
            DB::table('status_kepegawaian')->updateOrInsert(
                ['status_kepegawaian_id' => $status['status_kepegawaian_id']],
                array_merge($status, [
                    'input_time' => $now,
                    'input_user_id' => 1,
                    'status_batal' => 0,
                ])
            );
        }
    }
}
