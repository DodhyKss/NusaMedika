<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Baseline Master Implementasi Keperawatan: katalog intervensi yang dipilih
 * perawat pada form "Implementasi Keperawatan" di Dashboard Pasien.
 *
 * Idempotent: updateOrInsert berdasarkan kode_implementasi (unik).
 */
class ImplementasiSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $implementasi = [
            ['I-001', 'Perawatan Luka (Irigasi dan Balans)'],
            ['I-002', 'Perawatan Kateter Urin'],
            ['I-003', 'Mobilisasi dan Latihan Jalan'],
            ['I-004', 'Latihan Pernapasan Diafragma'],
            ['I-005', 'Pencegahan Risiko Jatuh'],
            ['I-006', 'Perubahan Posisi Tubuh'],
            ['I-007', 'Intake Cairan Oral'],
            ['I-008', 'Edukasi Nutrisi'],
            ['I-009', 'Latihan Buang Air Besar'],
            ['I-010', 'Pencegahan Dekubitus'],
            ['I-011', 'Latihan Menelan'],
            ['I-012', 'Perawatan Mulut dan Oral Hygiene'],
            ['I-013', 'Pemantauan Tanda Vital'],
            ['I-014', 'Dukungan Emosional'],
            ['I-015', 'Persiapan Pulang Pasien'],
        ];

        foreach ($implementasi as [$kode, $nama]) {
            DB::table('implementasi')->updateOrInsert(
                ['kode_implementasi' => $kode],
                [
                    'nama_implementasi' => $nama,
                    'input_time' => $now,
                    'input_user_id' => 1,
                    'status_batal' => 0,
                ]
            );
        }
    }
}
