<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WilayahSeeder extends Seeder
{
    public function run(): void
    {
        $provinsis = [
            ['provinsi_id' => 1, 'nama_provinsi' => 'DKI Jakarta', 'kode_wilayah_provinsi' => 31],
            ['provinsi_id' => 2, 'nama_provinsi' => 'Jawa Barat', 'kode_wilayah_provinsi' => 32],
            ['provinsi_id' => 3, 'nama_provinsi' => 'Banten', 'kode_wilayah_provinsi' => 36],
        ];

        foreach ($provinsis as $row) {
            DB::table('provinsi')->updateOrInsert(
                ['provinsi_id' => $row['provinsi_id']],
                array_merge($row, [
                    'status_batal' => 0,
                ])
            );
        }

        $kabupatens = [
            // DKI Jakarta (1)
            ['kabupaten_id' => 1, 'provinsi_id' => 1, 'nama_kabupaten' => 'Kab. Kepulauan Seribu', 'kode_wilayah_kabupaten' => 3101],
            ['kabupaten_id' => 2, 'provinsi_id' => 1, 'nama_kabupaten' => 'Kota Jakarta Pusat', 'kode_wilayah_kabupaten' => 3171],
            ['kabupaten_id' => 3, 'provinsi_id' => 1, 'nama_kabupaten' => 'Kota Jakarta Utara', 'kode_wilayah_kabupaten' => 3172],
            ['kabupaten_id' => 4, 'provinsi_id' => 1, 'nama_kabupaten' => 'Kota Jakarta Barat', 'kode_wilayah_kabupaten' => 3173],
            ['kabupaten_id' => 5, 'provinsi_id' => 1, 'nama_kabupaten' => 'Kota Jakarta Selatan', 'kode_wilayah_kabupaten' => 3174],
            ['kabupaten_id' => 6, 'provinsi_id' => 1, 'nama_kabupaten' => 'Kota Jakarta Timur', 'kode_wilayah_kabupaten' => 3175],
            // Jawa Barat (2)
            ['kabupaten_id' => 7, 'provinsi_id' => 2, 'nama_kabupaten' => 'Kab. Bogor', 'kode_wilayah_kabupaten' => 3201],
            ['kabupaten_id' => 8, 'provinsi_id' => 2, 'nama_kabupaten' => 'Kab. Bandung', 'kode_wilayah_kabupaten' => 3204],
            ['kabupaten_id' => 9, 'provinsi_id' => 2, 'nama_kabupaten' => 'Kota Bogor', 'kode_wilayah_kabupaten' => 3271],
            ['kabupaten_id' => 10, 'provinsi_id' => 2, 'nama_kabupaten' => 'Kota Bandung', 'kode_wilayah_kabupaten' => 3273],
            ['kabupaten_id' => 11, 'provinsi_id' => 2, 'nama_kabupaten' => 'Kota Cimahi', 'kode_wilayah_kabupaten' => 3277],
            // Banten (3)
            ['kabupaten_id' => 12, 'provinsi_id' => 3, 'nama_kabupaten' => 'Kota Tangerang', 'kode_wilayah_kabupaten' => 3671],
        ];

        foreach ($kabupatens as $row) {
            DB::table('kabupaten')->updateOrInsert(
                ['kabupaten_id' => $row['kabupaten_id']],
                array_merge($row, [
                    'status_batal' => 0,
                ])
            );
        }

        $kecamatans = [
            // Jakarta Pusat (2)
            ['kecamatan_id' => 1, 'kabupaten_id' => 2, 'nama_kecamatan' => 'Gambir', 'kode_wilayah_kecamatan' => 3171010],
            ['kecamatan_id' => 2, 'kabupaten_id' => 2, 'nama_kecamatan' => 'Tanah Abang', 'kode_wilayah_kecamatan' => 3171020],
            ['kecamatan_id' => 3, 'kabupaten_id' => 2, 'nama_kecamatan' => 'Senen', 'kode_wilayah_kecamatan' => 3171030],
            ['kecamatan_id' => 4, 'kabupaten_id' => 2, 'nama_kecamatan' => 'Menteng', 'kode_wilayah_kecamatan' => 3171040],
            ['kecamatan_id' => 5, 'kabupaten_id' => 2, 'nama_kecamatan' => 'Johar Baru', 'kode_wilayah_kecamatan' => 3171050],
            // Kota Bandung (10)
            ['kecamatan_id' => 6, 'kabupaten_id' => 10, 'nama_kecamatan' => 'Coblong', 'kode_wilayah_kecamatan' => 3273120],
            ['kecamatan_id' => 7, 'kabupaten_id' => 10, 'nama_kecamatan' => 'Bandung Wetan', 'kode_wilayah_kecamatan' => 3273130],
            ['kecamatan_id' => 8, 'kabupaten_id' => 10, 'nama_kecamatan' => 'Sumur Bandung', 'kode_wilayah_kecamatan' => 3273140],
            ['kecamatan_id' => 9, 'kabupaten_id' => 10, 'nama_kecamatan' => 'Regol', 'kode_wilayah_kecamatan' => 3273150],
            ['kecamatan_id' => 10, 'kabupaten_id' => 10, 'nama_kecamatan' => 'Bojongloa Kidul', 'kode_wilayah_kecamatan' => 3273160],
        ];

        foreach ($kecamatans as $row) {
            DB::table('kecamatan')->updateOrInsert(
                ['kecamatan_id' => $row['kecamatan_id']],
                array_merge($row, [
                    'status_batal' => 0,
                ])
            );
        }

        $kelurahans = [
            // Gambir (1)
            ['kelurahan_id' => 1, 'kecamatan_id' => 1, 'nama_kelurahan' => 'Gambir', 'kode_wilayah_kelurahan' => 3171010001],
            ['kelurahan_id' => 2, 'kecamatan_id' => 1, 'nama_kelurahan' => 'Kebon Kelapa', 'kode_wilayah_kelurahan' => 3171010002],
            ['kelurahan_id' => 3, 'kecamatan_id' => 1, 'nama_kelurahan' => 'Petojo Utara', 'kode_wilayah_kelurahan' => 3171010003],
            ['kelurahan_id' => 4, 'kecamatan_id' => 1, 'nama_kelurahan' => 'Petojo Selatan', 'kode_wilayah_kelurahan' => 3171010004],
            ['kelurahan_id' => 5, 'kecamatan_id' => 1, 'nama_kelurahan' => 'Cideng', 'kode_wilayah_kelurahan' => 3171010005],
            // Menteng (4)
            ['kelurahan_id' => 6, 'kecamatan_id' => 4, 'nama_kelurahan' => 'Menteng', 'kode_wilayah_kelurahan' => 3171040001],
            ['kelurahan_id' => 7, 'kecamatan_id' => 4, 'nama_kelurahan' => 'Pegangsaan', 'kode_wilayah_kelurahan' => 3171040002],
            ['kelurahan_id' => 8, 'kecamatan_id' => 4, 'nama_kelurahan' => 'Cikini', 'kode_wilayah_kelurahan' => 3171040003],
            ['kelurahan_id' => 9, 'kecamatan_id' => 4, 'nama_kelurahan' => 'Kebon Sirih', 'kode_wilayah_kelurahan' => 3171040004],
            ['kelurahan_id' => 10, 'kecamatan_id' => 4, 'nama_kelurahan' => 'Gondangdia', 'kode_wilayah_kelurahan' => 3171040005],
            // Coblong (6)
            ['kelurahan_id' => 11, 'kecamatan_id' => 6, 'nama_kelurahan' => 'Cipaganti', 'kode_wilayah_kelurahan' => 3273120001],
            ['kelurahan_id' => 12, 'kecamatan_id' => 6, 'nama_kelurahan' => 'Dago', 'kode_wilayah_kelurahan' => 3273120002],
            ['kelurahan_id' => 13, 'kecamatan_id' => 6, 'nama_kelurahan' => 'Lebakgede', 'kode_wilayah_kelurahan' => 3273120003],
            ['kelurahan_id' => 14, 'kecamatan_id' => 6, 'nama_kelurahan' => 'Lebaksiliwangi', 'kode_wilayah_kelurahan' => 3273120004],
            ['kelurahan_id' => 15, 'kecamatan_id' => 6, 'nama_kelurahan' => 'Sadang Serang', 'kode_wilayah_kelurahan' => 3273120005],
            ['kelurahan_id' => 16, 'kecamatan_id' => 6, 'nama_kelurahan' => 'Sekeloa', 'kode_wilayah_kelurahan' => 3273120006],
            // Bandung Wetan (7)
            ['kelurahan_id' => 17, 'kecamatan_id' => 7, 'nama_kelurahan' => 'Citarum', 'kode_wilayah_kelurahan' => 3273130001],
            ['kelurahan_id' => 18, 'kecamatan_id' => 7, 'nama_kelurahan' => 'Tamansari', 'kode_wilayah_kelurahan' => 3273130002],
            ['kelurahan_id' => 19, 'kecamatan_id' => 7, 'nama_kelurahan' => 'Braga', 'kode_wilayah_kelurahan' => 3273130003],
            ['kelurahan_id' => 20, 'kecamatan_id' => 7, 'nama_kelurahan' => 'Kebon Pisang', 'kode_wilayah_kelurahan' => 3273130004],
        ];

        foreach ($kelurahans as $row) {
            DB::table('kelurahan')->updateOrInsert(
                ['kelurahan_id' => $row['kelurahan_id']],
                array_merge($row, [
                    'status_batal' => 0,
                ])
            );
        }
    }
}
