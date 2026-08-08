<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BagianSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        // referensi_bagian: 1 = Rawat Jalan (Poli), 2 = Rawat Inap (Ruang Perawatan), 3 = IGD
        $polies = [
            'POLI INTERNA',
            'POLI ANAK',
            'POLI BEDAH',
            'POLI KANDUNGAN',
            'POLI MATA',
            'POLI THT',
            'POLI JANTUNG',
            'POLI SYARAF',
            'POLI PARU',
            'POLI KULIT DAN KELAMIN',
            'POLI GIGI DAN MULUT',
            'POLI UROLOGI',
            'POLI ORTOPEDI',
            'POLI UMUM',
            'POLI REHABILITASI MEDIK',
            'POLI JIWA',
            'POLI GIZI',
            'POLI KIA',
        ];

        $ruangPerawatan = [
            'RUANG MAWAR',
            'RUANG MELATI',
            'RUANG ANGGREK',
            'RUANG CEMPAKA',
            'RUANG DAHLIA',
            'RUANG KENANGA',
            'RUANG FLAMBOYAN',
            'RUANG TERATAI',
            'RUANG NUSA INDAH',
            'RUANG IRIS',
            'RUANG BOUGENVILLE',
            'RUANG CENDANA',
            'RUANG ICU',
            'RUANG NICU',
            'RUANG PICU',
            'RUANG BERSALIN',
            'RUANG OPERASI',
            'RUANG ISOLASI',
            'RUANG PERAWATAN KHUSUS',
        ];

        // IGD hanya ada dua bagian: Instalasi Gawat Darurat dan IRD Obgyn
        $igd = [
            'INSTALASI GAWAT DARURAT',
            'IRD OBGYN',
        ];

        $bagians = [];
        $id = 1;
        foreach ($polies as $nama) {
            $bagians[] = ['bagian_id' => $id++, 'nama_bagian' => $nama, 'referensi_bagian' => 1, 'group_bagian' => 'RJ'];
        }
        foreach ($ruangPerawatan as $nama) {
            $bagians[] = ['bagian_id' => $id++, 'nama_bagian' => $nama, 'referensi_bagian' => 2, 'group_bagian' => 'RI'];
        }
        foreach ($igd as $nama) {
            $bagians[] = ['bagian_id' => $id++, 'nama_bagian' => $nama, 'referensi_bagian' => 3, 'group_bagian' => 'IGD'];
        }

        // Hapus permanen record lama (placeholder Rawat Jalan/Rawat Inap/IGD + data lama) lalu seed ulang
        // ber-id berurutan agar konsisten dengan data yang dipakai di tabel.
        DB::table('bagian')->delete();

        foreach ($bagians as $bagian) {
            DB::table('bagian')->updateOrInsert(
                ['bagian_id' => $bagian['bagian_id']],
                array_merge($bagian, [
                    'input_time' => $now,
                    'input_user_id' => 1,
                    'status_batal' => 0,
                ])
            );
        }
    }
}
