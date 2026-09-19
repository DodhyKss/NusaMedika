<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TindakanSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        // Baseline master tindakan penunjang (Lab: bagian 36, Rad: bagian 37).
        // Idempotent: updateOrInsert berbasis kode_tindakan (unik).
        $tindakans = [
            // ============ LABORATORIUM ============
            ['kode_tindakan' => 'LAB-0001', 'nama_tindakan' => 'DARAH RUTIN', 'bagian_id' => 36, 'kategori' => 'LAB', 'satuan_hasil' => null, 'nilai_normal' => null],
            ['kode_tindakan' => 'LAB-0002', 'nama_tindakan' => 'URIN LENGKAP', 'bagian_id' => 36, 'kategori' => 'LAB', 'satuan_hasil' => null, 'nilai_normal' => null],
            ['kode_tindakan' => 'LAB-0003', 'nama_tindakan' => 'GLUKOSA DARAH', 'bagian_id' => 36, 'kategori' => 'LAB', 'satuan_hasil' => 'mg/dL', 'nilai_normal' => '70-130'],
            ['kode_tindakan' => 'LAB-0004', 'nama_tindakan' => 'SGOT/SGPT', 'bagian_id' => 36, 'kategori' => 'LAB', 'satuan_hasil' => 'U/L', 'nilai_normal' => 'SGOT 5-40 / SGPT 7-56'],
            ['kode_tindakan' => 'LAB-0005', 'nama_tindakan' => 'KREATININ', 'bagian_id' => 36, 'kategori' => 'LAB', 'satuan_hasil' => 'mg/dL', 'nilai_normal' => 'L 0.6-1.2 / P 0.5-1.1'],
            ['kode_tindakan' => 'LAB-0006', 'nama_tindakan' => 'MCU', 'bagian_id' => 36, 'kategori' => 'LAB', 'satuan_hasil' => null, 'nilai_normal' => null],
            // ============ RADIOLOGI ============
            ['kode_tindakan' => 'RAD-0001', 'nama_tindakan' => 'RONTGEN THORAX', 'bagian_id' => 37, 'kategori' => 'RAD', 'satuan_hasil' => null, 'nilai_normal' => null],
            ['kode_tindakan' => 'RAD-0002', 'nama_tindakan' => 'RONTGEN EKSTREMITAS', 'bagian_id' => 37, 'kategori' => 'RAD', 'satuan_hasil' => null, 'nilai_normal' => null],
            ['kode_tindakan' => 'RAD-0003', 'nama_tindakan' => 'USG ABDOMEN', 'bagian_id' => 37, 'kategori' => 'RAD', 'satuan_hasil' => null, 'nilai_normal' => null],
            ['kode_tindakan' => 'RAD-0004', 'nama_tindakan' => 'CT SCAN KEPALA', 'bagian_id' => 37, 'kategori' => 'RAD', 'satuan_hasil' => null, 'nilai_normal' => null],
        ];

        foreach ($tindakans as $tindakan) {
            DB::table('tindakan')->updateOrInsert(
                ['kode_tindakan' => $tindakan['kode_tindakan']],
                array_merge($tindakan, [
                    'kode_bpjs' => null,
                    'kode_inacbg' => null,
                    'kode_loinc' => null,
                    'keterangan' => null,
                    'input_time' => $now,
                    'input_user_id' => 1,
                    'status_batal' => 0,
                ])
            );
        }

        // ======== Tarif per kelas (tindakan_harga) ========
        // Kelas ruang 1..5 (Kelas 1/2/3, VIP, VVIP) + baris default (kelas_ruang_id NULL).
        $tarifPerKelas = [
            1 => 75000, 2 => 65000, 3 => 50000, 4 => 100000, 5 => 120000,
        ];

        foreach ($tindakans as $tindakan) {
            $tindakanId = (int) DB::table('tindakan')
                ->where('kode_tindakan', $tindakan['kode_tindakan'])
                ->value('tindakan_id');

            $isLab = $tindakan['kategori'] === 'LAB';
            $base = $isLab ? 50000 : 100000;

            // Baris default (fallback bila kelas spesifik belum diisi).
            if (! DB::table('tindakan_harga')
                ->where('tindakan_id', $tindakanId)
                ->whereNull('kelas_ruang_id')
                ->exists()) {
                DB::table('tindakan_harga')->insert([
                    'tindakan_id' => $tindakanId,
                    'kelas_ruang_id' => null,
                    'tarif' => $base,
                    'tarif_bpjs' => $isLab ? (int) round($base * 0.6) : (int) round($base * 0.6),
                    'input_time' => $now,
                    'input_user_id' => 1,
                    'status_batal' => 0,
                ]);
            }

            foreach ($tarifPerKelas as $kelasId => $tarif) {
                DB::table('tindakan_harga')->updateOrInsert(
                    ['tindakan_id' => $tindakanId, 'kelas_ruang_id' => $kelasId],
                    [
                        'tarif' => $tarif,
                        'tarif_bpjs' => (int) round($tarif * 0.6),
                        'input_time' => $now,
                        'input_user_id' => 1,
                        'status_batal' => 0,
                    ]
                );
            }
        }
    }
}
