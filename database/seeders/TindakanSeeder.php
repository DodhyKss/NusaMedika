<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TindakanSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        // Lookup kategori lewat NAMA, bukan id hardcode — id kategoriauto-increment
        // dan bisa bergeser bila data lokal sudah pernah diisi.
        $kategori = fn (string $nama) => DB::table('kategori_tindakan')
            ->where('nama_kategori_tindakan', $nama)
            ->value('kategori_tindakan_id');

        $idLab = $kategori('Laboratorium');
        $idRad = $kategori('Radiologi');

        // Baseline master tindakan penunjang. Master ini katalog saja: kode + nama +
        // kategori. Unit penunjang dipetakan lewat Master Group Tindakan (lihat
        // GroupTindakanSeeder), tarif lewat MasterTarifSeeder.
        $tindakans = [
            // ============ LABORATORIUM ============
            ['kode_tindakan' => 'LAB-0001', 'nama_tindakan' => 'DARAH RUTIN', 'kategori' => $idLab],
            ['kode_tindakan' => 'LAB-0002', 'nama_tindakan' => 'URIN LENGKAP', 'kategori' => $idLab],
            ['kode_tindakan' => 'LAB-0003', 'nama_tindakan' => 'GLUKOSA DARAH', 'kategori' => $idLab],
            ['kode_tindakan' => 'LAB-0004', 'nama_tindakan' => 'SGOT/SGPT', 'kategori' => $idLab],
            ['kode_tindakan' => 'LAB-0005', 'nama_tindakan' => 'KREATININ', 'kategori' => $idLab],
            ['kode_tindakan' => 'LAB-0006', 'nama_tindakan' => 'MCU', 'kategori' => $idLab],
            // ============ RADIOLOGI ============
            ['kode_tindakan' => 'RAD-0001', 'nama_tindakan' => 'RONTGEN THORAX', 'kategori' => $idRad],
            ['kode_tindakan' => 'RAD-0002', 'nama_tindakan' => 'RONTGEN EKSTREMITAS', 'kategori' => $idRad],
            ['kode_tindakan' => 'RAD-0003', 'nama_tindakan' => 'USG ABDOMEN', 'kategori' => $idRad],
            ['kode_tindakan' => 'RAD-0004', 'nama_tindakan' => 'CT SCAN KEPALA', 'kategori' => $idRad],
        ];

        foreach ($tindakans as $tindakan) {
            DB::table('tindakan')->updateOrInsert(
                ['kode_tindakan' => $tindakan['kode_tindakan']],
                [
                    'nama_tindakan' => $tindakan['nama_tindakan'],
                    'kategori_tindakan_id' => $tindakan['kategori'],
                    'input_time' => $now,
                    'input_user_id' => 1,
                    'status_batal' => 0,
                ]
            );
        }
    }
}
