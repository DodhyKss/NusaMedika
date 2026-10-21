<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Baseline Master Jenis Barang: pemisahan Obat dan BMHP.
 *
 * Idempotent: updateOrInsert berdasarkan nama_jenis_barang.
 */
class JenisBarangSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $jenis = [
            ['barang_jenis_id' => 1, 'nama_jenis_barang' => 'Obat'],
            ['barang_jenis_id' => 2, 'nama_jenis_barang' => 'BMHP'],
        ];

        foreach ($jenis as $row) {
            DB::table('barang_jenis')->updateOrInsert(
                ['barang_jenis_id' => $row['barang_jenis_id']],
                array_merge($row, [
                    'input_time' => $now,
                    'input_user_id' => 1,
                    'status_batal' => 0,
                ])
            );
        }
    }
}
