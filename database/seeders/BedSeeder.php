<?php

namespace Database\Seeders;

use App\Models\Bed;
use App\Models\KelasRuang;
use Illuminate\Database\Seeder;

class BedSeeder extends Seeder
{
    /**
     * Pemetaan ruang perawatan (bagian_id ranap) -> kelas ruang.
     * 16-21 (bangsal umum) Kelas 3; ICU/NICU/PICU/IBS/KHUSUS Kelas 1; BERSALIN/ISOLASI Kelas 2.
     */
    private function kelasMapping(): array
    {
        return [
            16 => 3, 17 => 3, 18 => 3, 19 => 3, 20 => 3, 21 => 3,
            22 => 1, 23 => 1, 24 => 1,
            25 => 2,
            26 => 1,
            27 => 2,
            28 => 1,
        ];
    }

    public function run(): void
    {
        $now = now();
        $kelas = KelasRuang::aktif()->get()->keyBy('kelas_ruang_id');

        foreach ($this->kelasMapping() as $bagianId => $kelasId) {
            $kelasRuang = $kelas->get($kelasId);

            if (! $kelasRuang) {
                continue;
            }

            for ($no = 1; $no <= 4; $no++) {
                Bed::updateOrCreate(
                    ['bagian_id' => $bagianId, 'nama_bed' => 'B-'.str_pad((string) $no, 2, '0', STR_PAD_LEFT)],
                    [
                        'no_kamar' => 'R'.$bagianId.'-'.str_pad((string) $no, 2, '0', STR_PAD_LEFT),
                        'status_bed' => 0,
                        'kelas_id' => $kelasRuang->kelas_ruang_id,
                        'kodekelas' => $kelasRuang->kelas_bpjs,
                        'namakelas' => $kelasRuang->nama_kelas_ruang,
                        'flag_isolasi' => 2,
                        'flag_isolasi_pressure' => 2,
                        'flag_ventilator' => 2,
                        'flag_neonatus' => 0,
                        'flag_persiapan_pulang' => 2,
                        'input_time' => $now,
                        'input_user_id' => 1,
                        'status_batal' => 0,
                    ]
                );
            }
        }
    }
}
