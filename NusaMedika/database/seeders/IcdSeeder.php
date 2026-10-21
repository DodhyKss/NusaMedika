<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IcdSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $icds = [
            ['kode' => 'A00', 'nama' => 'Cholera'],
            ['kode' => 'A01', 'nama' => 'Typhoid and paratyphoid fevers'],
            ['kode' => 'A09', 'nama' => 'Infectious gastroenteritis and colitis, unspecified'],
            ['kode' => 'E10', 'nama' => 'Type 1 diabetes mellitus'],
            ['kode' => 'E11', 'nama' => 'Type 2 diabetes mellitus'],
            ['kode' => 'I10', 'nama' => 'Essential (primary) hypertension'],
            ['kode' => 'J00', 'nama' => 'Acute nasopharyngitis [common cold]'],
            ['kode' => 'J02', 'nama' => 'Acute pharyngitis'],
            ['kode' => 'J03', 'nama' => 'Acute tonsillitis'],
            ['kode' => 'J45', 'nama' => 'Asthma'],
            ['kode' => 'K29', 'nama' => 'Gastritis and duodenitis'],
            ['kode' => 'K30', 'nama' => 'Functional dyspepsia'],
        ];

        foreach ($icds as $icd) {
            DB::table('icd')->updateOrInsert(
                ['kode_diagnosa' => $icd['kode']],
                [
                    'nama_diagnosa' => $icd['nama'],
                    'kategori' => 'ICD-10',
                    'status_batal' => 0,
                ]
            );
        }
    }
}
