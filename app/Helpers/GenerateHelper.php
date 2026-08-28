<?php

namespace App\Helpers;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GenerateHelper
{
    /**
     * Menyetel ulang sequence auto-increment agar mengikuti ID terbesar saat ini.
     * Dipakai setelah seeder mengisi data dengan ID eksplisit agar insert baru
     * (yang memakai auto-increment) tidak bentrok dengan ID yang sudah dipakai.
     *
     * @param  string  $tableName  Nama tabel
     * @param  string|null  $primaryKey  Kolom primary key (opsional)
     */
    public static function resetSequence($tableName, $primaryKey = null)
    {
        $primaryKey = $primaryKey ?: $tableName.'_id';

        try {
            DB::statement(
                'SELECT setval(pg_get_serial_sequence(?, ?), COALESCE((SELECT MAX('.$primaryKey.') FROM '.$tableName.') + 1, 1), false)',
                [$tableName, $primaryKey]
            );
        } catch (\Exception $e) {
            // Abaikan jika sequence tidak ditemukan.
        }
    }

    /**
     * Membuat nomor rekam medis (No. MR) baru.
     * Berdasarkan MAX(no_mr) numerik pada semua pasien + 1,
     * diformat 7 digit dengan leading zero.
     */
    public static function generateNoMr(): string
    {
        $max = (int) DB::table('pasien')
            ->where('no_mr', '~', '^[0-9]+$')
            ->max('no_mr');

        return str_pad((string) ($max + 1), 7, '0', STR_PAD_LEFT);
    }

    /**
     * Membuat nomor urut antrian baru untuk rawat jalan.
     * Berdasarkan MAX(urutan) + 1 untuk kombinasi pegawai (dokter),
     * bagian (poliklinik), dan tanggal kunjungan.
     *
     * @param  int  $pegawaiId
     * @param  int  $bagianId
     * @param  string  $tglKunjungan
     */
    public static function generateNoUrut($pegawaiId, $bagianId, $tglKunjungan): int
    {
        $urutanTerakhir = DB::table('registrasi_urut')
            ->where('pegawai_id', $pegawaiId)
            ->where('bagian_id', $bagianId)
            ->where(function ($q) {
                $q->whereNull('status_batal')->orWhere('status_batal', 0);
            })
            ->whereDate('tgl_urut', $tglKunjungan)
            ->max('urutan');

        return ($urutanTerakhir ? (int) $urutanTerakhir : 0) + 1;
    }

    /**
     * Menghitung estimasi waktu layanan pasien rawat jalan.
     * Jam mulai dari jadwal dokter ditambah rentang 60 menit
     * untuk setiap urutan antrian berikutnya.
     *
     * @param  string  $tglKunjungan
     * @param  string  $waktuMulai  Jam mulai jadwal dokter (format H:i)
     * @param  int  $urutan
     */
    public static function hitungEstimasi($tglKunjungan, $waktuMulai, $urutan)
    {
        return Carbon::parse($tglKunjungan.' '.Carbon::parse($waktuMulai)->format('H:i'))
            ->addMinutes(($urutan - 1) * 60);
    }
}
