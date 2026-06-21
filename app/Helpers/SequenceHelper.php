<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

class SequenceHelper
{
    /**
     * Mengambil ID berikutnya dari sequence PostgreSQL.
     * Secara otomatis mencari dengan berbagai format nama sequence.
     *
     * @param string $tableName Nama tabel
     * @param string|null $primaryKey Kolom primary key (opsional)
     * @return int
     * @throws \Exception
     */
    public static function getNextId($tableName, $primaryKey = null)
    {
        if (!$primaryKey) {
            $primaryKey = $tableName . '_id';
        }

        // Daftar kemungkinan format nama sequence (diurutkan berdasarkan prioritas)
        $possibleSequences = [
            $tableName . '_' . $primaryKey . '_seq', // Format standar Postgres (paling sering digunakan)
            $tableName . '_squence',           // Format typo squence
            $tableName . '_sequence',          // Format standar english
            $primaryKey . '_seq',              // Format bawaan alternatif
        ];

        foreach ($possibleSequences as $seq) {
            try {
                // Gunakan query select nextval
                $result = DB::selectOne("SELECT nextval(?) as next_id", [$seq]);
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
}
