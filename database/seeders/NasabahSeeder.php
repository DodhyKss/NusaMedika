<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NasabahSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $nasabahList = [
            ['nasabah_id' => 1, 'nama_nasabah' => 'BPJS Kesehatan'],
            ['nasabah_id' => 2, 'nama_nasabah' => 'Umum / Mandiri'],
        ];

        foreach ($nasabahList as $nasabah) {
            DB::table('nasabah')->updateOrInsert(
                ['nasabah_id' => $nasabah['nasabah_id']],
                array_merge($nasabah, [
                    'input_time' => $now,
                    'input_user_id' => 1,
                    'status_batal' => 0,
                ])
            );
        }
    }
}