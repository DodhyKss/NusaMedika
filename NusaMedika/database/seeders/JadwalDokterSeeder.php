<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JadwalDokterSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        // Hari: 1=Senin, 2=Selasa, 3=Rabu, 4=Kamis, 5=Jumat, 6=Sabtu, 7=Minggu
        $jadwals = [
            ['jadwal_dokter_id' => 1, 'pegawai_id' => 3, 'hari' => 1, 'waktu_mulai' => '08:00', 'waktu_selesai' => '12:00', 'kuota' => 20, 'bagian_id' => 1, 'ruang_praktek' => 'RJ-01'],
            ['jadwal_dokter_id' => 2, 'pegawai_id' => 3, 'hari' => 2, 'waktu_mulai' => '09:00', 'waktu_selesai' => '13:00', 'kuota' => 15, 'bagian_id' => 2, 'ruang_praktek' => 'RJ-02'],
            ['jadwal_dokter_id' => 3, 'pegawai_id' => 3, 'hari' => 3, 'waktu_mulai' => '08:00', 'waktu_selesai' => '12:00', 'kuota' => 18, 'bagian_id' => 3, 'ruang_praktek' => 'RJ-03'],
            ['jadwal_dokter_id' => 4, 'pegawai_id' => 3, 'hari' => 4, 'waktu_mulai' => '10:00', 'waktu_selesai' => '14:00', 'kuota' => 12, 'bagian_id' => 4, 'ruang_praktek' => 'RJ-04'],
            ['jadwal_dokter_id' => 5, 'pegawai_id' => 3, 'hari' => 5, 'waktu_mulai' => '08:00', 'waktu_selesai' => '11:00', 'kuota' => 10, 'bagian_id' => 5, 'ruang_praktek' => 'RJ-05'],
            // Jadwal Medical Checkup (bagian 39 = POLI MEDICAL CHECKUP) — Sabtu, Senin, & Minggu.
            // Nama poli mengandung "MEDICAL CHECKUP" agar dikenali sebagai jadwal MCU.
            ['jadwal_dokter_id' => 6, 'pegawai_id' => 3, 'hari' => 6, 'waktu_mulai' => '07:00', 'waktu_selesai' => '12:00', 'kuota' => 15, 'bagian_id' => 39, 'ruang_praktek' => 'MCU-01'],
            ['jadwal_dokter_id' => 7, 'pegawai_id' => 3, 'hari' => 1, 'waktu_mulai' => '07:00', 'waktu_selesai' => '12:00', 'kuota' => 15, 'bagian_id' => 39, 'ruang_praktek' => 'MCU-01'],
            ['jadwal_dokter_id' => 8, 'pegawai_id' => 3, 'hari' => 7, 'waktu_mulai' => '07:00', 'waktu_selesai' => '12:00', 'kuota' => 15, 'bagian_id' => 39, 'ruang_praktek' => 'MCU-01'],
        ];

        foreach ($jadwals as $jadwal) {
            DB::table('jadwal_dokter')->updateOrInsert(
                ['jadwal_dokter_id' => $jadwal['jadwal_dokter_id']],
                array_merge($jadwal, [
                    'input_time' => $now,
                    'input_user_id' => 1,
                    'status_batal' => 0,
                ])
            );
        }
    }
}
