<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReferensiBagianSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        // ID menyesuaikan nilai referensi_bagian_id di FormObjekSeeder:
        // 1 = Rawat Jalan, 2 = Rawat Inap, 3 = IGD (sesuai REF_BAGIAN_RAJAL=1, REF_BAGIAN_RANAP=2)
        $referensiBagians = [
            ['referensi_bagian_id_id' => 1, 'nama_referensi_bagian_id' => 'RAWAT JALAN'],
            ['referensi_bagian_id_id' => 2, 'nama_referensi_bagian_id' => 'RAWAT INAP'],
            ['referensi_bagian_id_id' => 3, 'nama_referensi_bagian_id' => 'IGD'],
        ];

        foreach ($referensiBagians as $referensi) {
            DB::table('referensi_bagian_id')->updateOrInsert(
                ['referensi_bagian_id_id' => $referensi['referensi_bagian_id_id']],
                array_merge($referensi, [
                    'input_time' => $now,
                    'input_user_id' => 1,
                    'status_batal' => 0,
                ])
            );
        }
    }
}
