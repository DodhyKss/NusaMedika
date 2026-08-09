<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

class GenerateHelper
{
    /**
     * Mengambil ID berikutnya dari sequence PostgreSQL.
     * Secara otomatis mencari dengan berbagai format nama sequence.
     *
     * @param  string  $tableName  Nama tabel
     * @param  string|null  $primaryKey  Kolom primary key (opsional)
     * @return int
     *
     * @throws \Exception
     */
    public static function getNextId($tableName, $primaryKey = null)
    {
        if (! $primaryKey) {
            $primaryKey = $tableName.'_id';
        }

        // Daftar kemungkinan format nama sequence (diurutkan berdasarkan prioritas)
        $possibleSequences = [
            $tableName.'_'.$primaryKey.'_seq', // Format standar Postgres (paling sering digunakan)
            $tableName.'_squence',           // Format typo squence
            $tableName.'_sequence',          // Format standar english
            $primaryKey.'_seq',              // Format bawaan alternatif
        ];

        foreach ($possibleSequences as $seq) {
            try {
                // Cek eksistensi sequence terlebih dahulu agar nextval tidak
                // memicu error yang bisa mengabort transaksi PostgreSQL aktif.
                $exists = DB::selectOne(
                    "SELECT 1 FROM pg_class c WHERE c.relkind = 'S' AND c.relname = ? AND pg_table_is_visible(c.oid)",
                    [$seq]
                );

                if (! $exists) {
                    continue;
                }

                // Gunakan query select nextval
                $result = DB::selectOne('SELECT nextval(?) as next_id', [$seq]);
                if ($result && $result->next_id) {
                    return (int) $result->next_id;
                }
            } catch (\Exception $e) {
                // Jika error (sequence tidak ada), lanjut ke format nama berikutnya
                continue;
            }
        }

        // Jika semua format gagal, gunakan MAX + 1 sebagai fallback terakhir
        try {
            $max = DB::table($tableName)->max($primaryKey);

            return ($max ? (int) $max : 0) + 1;
        } catch (\Exception $e) {
            throw new \Exception("Gagal mendapatkan sequence ID untuk tabel {$tableName}. Pastikan sequence atau tabel valid.");
        }
    }

    /**
     * Membuat nomor rekam medis (No. MR) baru.
     * Berdasarkan MAX(no_mr) numerik pada semua pasien + 1,
     * diformat 7 digit dengan leading zero.
     *
     * @return string
     */
    public static function generateNoMr(): string
    {
        $max = (int) DB::table('pasien')
            ->where('no_mr', '~', '^[0-9]+$')
            ->max('no_mr');

        return str_pad((string) ($max + 1), 7, '0', STR_PAD_LEFT);
    }
}
