<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Master jenis diet & faktor penyakit penyerta diet (prasyarat PRE-05,
 * dipakai form 43 "Diet Pasien").
 *
 * Idempotent: `updateOrInsert` dikunci pada `kode_diet` /
 * `kode_diagnosa_diet`, jadi dijalankan berkali-kali tidak menambah baris dan
 * perubahan isi tetap terpakai.
 *
 * PENTING: `jenis_diet` pada form 43 divalidasi
 * `exists:master_diet,nama_diet`, jadi `nama_diet` di sini adalah nilai yang
 * BENAR-BENAR tersimpan di `emr_detail`. Mengubah nama di sini membuat data
 * lama tidak lagi cocok — karena itu penamaan dibuat stabil.
 */
class MasterDietSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $diet = [
            ['DIET-001', 'Diet Mual', 'Makan sedikit sering, hindari bau menyengat'],
            ['DIET-002', 'Diet Diabetes Mellitus', 'Kontrol karbohidrat, jadwal makan teratur'],
            ['DIET-003', 'Diet Rendah Garam', 'Batasi natrium di bawah 2 gram per hari'],
            ['DIET-004', 'Diet Rendah Lemak', 'Batasi lemak total dan lemak jenuh'],
            ['DIET-005', 'Diet Rendah Kolesterol', 'Batasi lemak jenuh dan lemak trans'],
            ['DIET-006', 'Diet Rendah Protein', 'Batasi protein sesuai anjuran dokter'],
            ['DIET-007', 'Diet Tinggi Protein', 'Untuk pemulihan dan pertumbuhan'],
            ['DIET-008', 'Diet Tinggi Kalsium', 'Untuk pencegahan osteoporosis'],
            ['DIET-009', 'Diet Vegetarian', 'Tanpa daging'],
            ['DIET-010', 'Diet Diabetes Mellitus dan Hipertensi', 'Kontrol karbohidrat dan natrium'],
            ['DIET-011', 'Diet Rendah Kalori', 'Untuk obesitas'],
            ['DIET-012', 'Diet Pasca Operasi', 'Makanan bertahap dengan tekstur lembut'],
            ['DIET-013', 'Diet Cairan', 'Untuk pasien dengan gangguan usus'],
            ['DIET-014', 'Diet Gizi Buruk', 'Asupan tinggi protein dan energi'],
            ['DIET-015', 'Diet Renal', 'Batasi protein, natrium, kalium, dan fosfor'],
            ['DIET-016', 'Diet Ginjal', 'Batasi protein, natrium, dan cairan'],
            ['DIET-017', 'Diet Jantung', 'Batasi lemak dan natrium'],
            ['DIET-018', 'Diet Nasi Tim', 'Makanan mudah dicerna'],
            ['DIET-019', 'Diet Enteral', 'Pemberian lewat sonda'],
            ['DIET-020', 'Diet Oral', 'Pemberian lewat mulut'],
        ];

        foreach ($diet as [$kode, $nama, $keterangan]) {
            DB::table('master_diet')->updateOrInsert(
                ['kode_diet' => $kode],
                [
                    'nama_diet' => $nama,
                    'keterangan' => $keterangan,
                    'input_time' => $now,
                    'input_user_id' => 1,
                    'status_batal' => 0,
                ]
            );
        }

        $diagnosa = [
            ['DD-001', 'Diabetes Mellitus Tipe 1'],
            ['DD-002', 'Diabetes Mellitus Tipe 2'],
            ['DD-003', 'Hipertensi'],
            ['DD-004', 'Dislipidemia'],
            ['DD-005', 'Gagal Ginjal Kronis'],
            ['DD-006', 'Penyakit Hati Kronis'],
            ['DD-007', 'Gagal Jantung'],
            ['DD-008', 'Tuberkulosis Paru'],
            ['DD-009', 'Infeksi Saluran Pernapasan'],
            ['DD-010', 'Gangguan Sistem Cerna'],
            ['DD-011', 'Kanker atau Neoplasma'],
            ['DD-012', 'HIV/AIDS'],
            ['DD-013', 'Stroke'],
            ['DD-014', 'Demam Berdarah'],
            ['DD-015', 'Osteoporosis'],
            ['DD-016', 'Anemia'],
        ];

        foreach ($diagnosa as [$kode, $nama]) {
            DB::table('master_diagnosa_diet')->updateOrInsert(
                ['kode_diagnosa_diet' => $kode],
                [
                    'nama_diagnosa_diet' => $nama,
                    'keterangan' => null,
                    'input_time' => $now,
                    'input_user_id' => 1,
                    'status_batal' => 0,
                ]
            );
        }
    }
}
