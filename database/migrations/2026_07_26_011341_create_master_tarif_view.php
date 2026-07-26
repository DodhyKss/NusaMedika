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
        DB::statement("CREATE VIEW \"master_tarif\" AS SELECT sum(biaya) AS tarif_tindakan,
    nama_tindakan,
    nama_kelas_ruang,
    tindakan_id,
    bagian_id,
    kelas_ruang_id
   FROM ( SELECT tarif.biaya,
            tindakan.nama_tindakan,
            tindakan_detail.nama_tindakan_detail,
            kelas_ruang.nama_kelas_ruang,
            tindakan.tindakan_id,
            tindakan_detail.bagian_id,
            kelas_ruang.kelas_ruang_id
           FROM (((tarif
             LEFT JOIN tindakan_detail ON ((tarif.tindakan_detail_id = tindakan_detail.tindakan_detail_id)))
             LEFT JOIN tindakan ON ((tindakan.tindakan_id = tindakan_detail.tindakan_id)))
             LEFT JOIN kelas_ruang ON ((tarif.kelas_ruang_id = kelas_ruang.kelas_ruang_id)))) master_tarif
  GROUP BY tindakan_id, nama_tindakan, nama_kelas_ruang, bagian_id, kelas_ruang_id;");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS \"master_tarif\"");
    }
};
