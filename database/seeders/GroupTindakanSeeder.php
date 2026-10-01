<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GroupTindakanSeeder extends Seeder
{
    /**
     * Master Group Tindakan menggantikan kolom `tindakan.bagian_id`: satu group
     * (panel pemeriksaan) milik satu unit penunjang dan berisi banyak tindakan.
     *
     * Baseline 10 tindakan dipecah menjadi panel per pemeriksaan. Menambah
     * pemeriksaan lain ke dalam panel cukup dilakukan lewat UI Master Group
     * Tindakan (dropdown multi-select).
     */
    public function run(): void
    {
        $now = now();

        // Unit penunjang lookup per NAMA — id bagian bisa bergeser karena
        // BagianSeeder bersifat append-only.
        $bagian = fn (string $nama) => DB::table('bagian')
            ->where('nama_bagian', $nama)
            ->where(function ($q) {
                $q->where('status_batal', '!=', 1)->orWhereNull('status_batal');
            })
            ->value('bagian_id');

        $idLab = $bagian('INSTALASI LABORATORIUM');
        $idRad = $bagian('INSTALASI RADIOLOGI');

        $groups = [
            // ============ LABORATORIUM ============
            ['nama' => 'DARAH LENGKAP', 'bagian' => $idLab, 'tindakan' => ['LAB-0001']],
            ['nama' => 'URIN LENGKAP', 'bagian' => $idLab, 'tindakan' => ['LAB-0002']],
            ['nama' => 'GLUKOSA DARAH', 'bagian' => $idLab, 'tindakan' => ['LAB-0003']],
            ['nama' => 'FUNGSI HATI', 'bagian' => $idLab, 'tindakan' => ['LAB-0004']],
            ['nama' => 'FUNGSI GINJAL', 'bagian' => $idLab, 'tindakan' => ['LAB-0005']],
            ['nama' => 'MEDICAL CHECK UP', 'bagian' => $idLab, 'tindakan' => ['LAB-0006']],
            // ============ RADIOLOGI ============
            ['nama' => 'RONTGEN THORAX', 'bagian' => $idRad, 'tindakan' => ['RAD-0001']],
            ['nama' => 'RONTGEN EKSTREMITAS', 'bagian' => $idRad, 'tindakan' => ['RAD-0002']],
            ['nama' => 'USG ABDOMEN', 'bagian' => $idRad, 'tindakan' => ['RAD-0003']],
            ['nama' => 'CT SCAN KEPALA', 'bagian' => $idRad, 'tindakan' => ['RAD-0004']],
        ];

        foreach ($groups as $group) {
            if (! $group['bagian']) {
                continue;
            }

            DB::table('group_tindakan')->updateOrInsert(
                ['nama_group_tindakan' => $group['nama'], 'bagian_id' => $group['bagian']],
                [
                    'input_time' => $now,
                    'input_user_id' => 1,
                    'status_batal' => 0,
                ]
            );

            $groupId = (int) DB::table('group_tindakan')
                ->where('nama_group_tindakan', $group['nama'])
                ->where('bagian_id', $group['bagian'])
                ->value('group_tindakan_id');

            foreach ($group['tindakan'] as $kode) {
                $tindakanId = DB::table('tindakan')->where('kode_tindakan', $kode)->value('tindakan_id');
                if (! $tindakanId) {
                    continue;
                }

                DB::table('group_tindakan_tindakan')->updateOrInsert(
                    ['group_tindakan_id' => $groupId, 'tindakan_id' => $tindakanId],
                    [
                        'input_time' => $now,
                        'input_user_id' => 1,
                        'status_batal' => 0,
                    ]
                );
            }
        }
    }
}
