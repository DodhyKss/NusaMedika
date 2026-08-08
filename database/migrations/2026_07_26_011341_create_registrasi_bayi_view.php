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
        DB::statement("CREATE OR REPLACE VIEW \"registrasi_bayi\" AS SELECT registrasi.pasien_id,
    registrasi_detail.registrasi_detail_id,
    registrasi_detail.bagian_id
   FROM (registrasi
     JOIN registrasi_detail ON ((registrasi.registrasi_id = registrasi_detail.registrasi_id)))
  WHERE ((registrasi_detail.bagian_id = ANY (ARRAY[64, 38, 39, 42])) AND (registrasi.status_batal IS NULL) AND (registrasi_detail.status_batal IS NULL));");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS \"registrasi_bayi\"");
    }
};
