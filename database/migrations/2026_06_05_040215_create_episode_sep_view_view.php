<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("CREATE VIEW \"episode_sep_view\" AS SELECT episode_registrasi.registrasi_id1,
    episode_registrasi.registrasi_id2,
    episode_registrasi.registrasi_id3,
    episode_registrasi.registrasi_id4,
    episode_registrasi.registrasi_id5
   FROM ( SELECT registrasi1.registrasi_id AS registrasi_id1,
            registrasi2.registrasi_id AS registrasi_id2,
            registrasi3.registrasi_id AS registrasi_id3,
            registrasi4.registrasi_id AS registrasi_id4,
            registrasi5.registrasi_id AS registrasi_id5
           FROM ((((registrasi registrasi1
             LEFT JOIN registrasi registrasi2 ON (((registrasi1.registrasi_id = registrasi2.referensi_registrasi_id) AND (registrasi2.status_batal IS NULL))))
             LEFT JOIN registrasi registrasi3 ON (((registrasi2.registrasi_id = registrasi3.referensi_registrasi_id) AND (registrasi3.status_batal IS NULL))))
             LEFT JOIN registrasi registrasi4 ON (((registrasi3.registrasi_id = registrasi4.referensi_registrasi_id) AND (registrasi4.status_batal IS NULL))))
             LEFT JOIN registrasi registrasi5 ON (((registrasi4.registrasi_id = registrasi5.referensi_registrasi_id) AND (registrasi5.status_batal IS NULL))))) episode_registrasi;");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS \"episode_sep_view\"");
    }
};
