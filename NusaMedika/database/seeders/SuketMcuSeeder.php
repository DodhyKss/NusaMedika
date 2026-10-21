<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SuketMcuSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $sukets = [
            ['suket_mcu_id' => 1, 'nama_suket' => 'Surat Keterangan Sehat', 'harga' => 50000],
            ['suket_mcu_id' => 2, 'nama_suket' => 'Surat Keterangan Bebas Narkoba', 'harga' => 100000],
        ];

        foreach ($sukets as $suket) {
            DB::table('suket_mcu')->updateOrInsert(
                ['suket_mcu_id' => $suket['suket_mcu_id']],
                array_merge($suket, [
                    'input_time' => $now,
                    'input_user_id' => 1,
                    'status_batal' => 0,
                ])
            );
        }
    }
}
