<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SatuanSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $satuans = [
            ['pcs', 'Pcs'], ['tablet', 'Tablet'], ['kapsul', 'Kapsul'], ['kaptab', 'Kapsul Tablet'],
            ['ampul', 'Ampul'], ['vial', 'Vial'], ['flakon', 'Flakon'], ['tube', 'Tube'],
            ['pot', 'Pot'], ['botol', 'Botol'], ['botol iv', 'Botol Infus'], ['flask', 'Flask'],
            ['sirup', 'Sirup'], ['suspensi', 'Suspensi'], ['krim', 'Krim'], ['salep', 'Salep'],
            ['losion', 'Losion'], ['gele', 'Gel'], ['suppositoria', 'Suppositoria'], ['obat tetes', 'Obat Tetes (Tetes)'],
            ['lembar', 'Lembar'], ['box', 'Box'], ['dus', 'Dus'], ['karton', 'Karton'],
            ['roll', 'Roll'], ['set', 'Set'], ['pasang', 'Pasang'], ['strip', 'Strip'],
            ['sachet', 'Sachet'], ['kaleng', 'Kaleng'], ['paket', 'Paket'], ['unit', 'Unit'],
            ['gram', 'Gram'], ['mg', 'Miligram'], ['ml', 'Mililiter'], ['liter', 'Liter'],
            ['cc', 'CC (Cm3)'], ['ons', 'Ons'], ['kg', 'Kilogram'], ['meter', 'Meter'],
            ['cm', 'Centimeter'],
        ];

        foreach ($satuans as [$singkatan, $nama]) {
            DB::table('satuan')->updateOrInsert(
                ['singkatan' => $singkatan],
                array_merge([
                    'nama_satuan' => $nama,
                    'singkatan' => $singkatan,
                ], $this->audit($now))
            );
        }
    }

    private function audit($now): array
    {
        return [
            'input_time' => $now,
            'input_user_id' => 1,
            'status_batal' => 0,
        ];
    }
}
