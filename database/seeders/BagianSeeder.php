<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BagianSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        // referensi_bagian_id: 1 = Rawat Jalan (Poli), 2 = Rawat Inap (Ruang Perawatan), 3 = IGD, 4 = Gudang
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
            'POLI ORTOPEDI',
            'POLI REHABILITASI MEDIK',
            'POLI JIWA',
            'POLI GIZI',
        ];

        $ruangPerawatan = [
            'RUANG PERAWATAN MAWAR',
            'RUANG PERAWATAN MELATI',
            'RUANG PERAWATAN ANGGREK',
            'RUANG PERAWATAN DAHLIA',
            'RUANG PERAWATAN KENANGA',
            'RUANG PERAWATAN BOUGENVILLE',
            'RUANG ICU',
            'RUANG NICU',
            'RUANG PICU',
            'RUANG BERSALIN',
            'INSTALASI BEDAH SENTRAL',
            'RUANG ISOLASI',
            'RUANG PERAWATAN KHUSUS',
        ];

        // IGD hanya ada dua bagian: Instalasi Gawat Darurat dan IRD Obgyn
        $igd = [
            'INSTALASI GAWAT DARURAT',
            'IRD OBGYN',
        ];

        // Gudang/Depo: tujuan utama stok masuk (penerimaan barang) sebelum didistribusikan ke bagian lain.
        // Gudang dibiarkan satu saja: GUDANG (referensi_bagian_id 4).
        $gudangs = [
            'GUDANG',
        ];

        // Depo: tempat apoteker bertugas & mengeluarkan obat (dispense resep)
        $depos = [
            'DEPO RAWAT JALAN',
            'DEPO RAWAT INAP',
            'DEPO IGD',
            'DEPO LAIN',
        ];

        // Penunjang Medis (referensi_bagian_id 6): Lab & Radiologi.
        // SELALU APPEND di akhir — jangan sisipkan di tengah list agar id existing
        // (yang sudah direferensikan) tidak berubah.
        $penunjangs = [
            'INSTALASI LABORATORIUM',
            'INSTALASI RADIOLOGI',
        ];

        $bagians = [];
        $id = 1;
        foreach ($polies as $nama) {
            $bagians[] = ['bagian_id' => $id++, 'nama_bagian' => $nama, 'referensi_bagian_id' => 1];
        }
        foreach ($ruangPerawatan as $nama) {
            $bagians[] = ['bagian_id' => $id++, 'nama_bagian' => $nama, 'referensi_bagian_id' => 2];
        }
        foreach ($igd as $nama) {
            $bagians[] = ['bagian_id' => $id++, 'nama_bagian' => $nama, 'referensi_bagian_id' => 3];
        }
        foreach ($gudangs as $nama) {
            $bagians[] = ['bagian_id' => $id++, 'nama_bagian' => $nama, 'referensi_bagian_id' => 4];
        }
        foreach ($depos as $nama) {
            $bagians[] = ['bagian_id' => $id++, 'nama_bagian' => $nama, 'referensi_bagian_id' => 5];
        }
        foreach ($penunjangs as $nama) {
            $bagians[] = ['bagian_id' => $id++, 'nama_bagian' => $nama, 'referensi_bagian_id' => 6];
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
