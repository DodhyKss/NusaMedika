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
        DB::statement("CREATE VIEW \"jenis_konsultasi_view\" AS SELECT DISTINCT emr_detail_konsul.value,
    registrasi_detail.registrasi_detail_id,
    registrasi.pasien_id
   FROM (((registrasi
     LEFT JOIN registrasi_detail ON ((registrasi.registrasi_id = registrasi_detail.registrasi_id)))
     LEFT JOIN emr ON (((registrasi_detail.registrasi_detail_id = emr.registrasi_detail_id) AND (emr.form_id = 26))))
     LEFT JOIN emr_detail emr_detail_konsul ON ((emr.emr_id = emr_detail_konsul.emr_id)))
  WHERE ((registrasi.tgl_keluar IS NULL) AND (registrasi.status_batal IS NULL) AND (registrasi_detail.status_batal IS NULL) AND (emr.status_batal IS NULL) AND (emr_detail_konsul.status_batal IS NULL) AND (emr_detail_konsul.objek_id = 151));");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS \"jenis_konsultasi_view\"");
    }
};
