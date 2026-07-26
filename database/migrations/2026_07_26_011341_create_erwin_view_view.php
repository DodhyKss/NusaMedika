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
        DB::statement("CREATE VIEW \"erwin_view\" AS SELECT tindakan_id,
    nama_tindakan,
    tindakan_detail_id,
    nama_tindakan_detail,
    tarif_id,
    flag,
        CASE
            WHEN (flag = 'child'::text) THEN ( SELECT tindakan.tindakan_id
               FROM tindakan
              WHERE ((tindakan.tindakan_id < z.tindakan_id) AND ((tindakan.nama_tindakan)::text = (z.nama_tindakan)::text))
             LIMIT 1)
            ELSE 0
        END AS tindakan_id_parent,
        CASE
            WHEN (flag = 'child'::text) THEN ( SELECT tarif.tarif_id
               FROM ((tarif
                 JOIN tindakan_detail ON ((tarif.tindakan_detail_id = tindakan_detail.tindakan_detail_id)))
                 JOIN tindakan ON ((tindakan_detail.tindakan_id = tindakan.tindakan_id)))
              WHERE ((tindakan_detail.tindakan_id < z.tindakan_id) AND ((tindakan.nama_tindakan)::text = (z.nama_tindakan)::text) AND ((tindakan_detail.nama_tindakan_detail)::text = (z.nama_tindakan_detail)::text))
             LIMIT 1)
            ELSE tarif_id
        END AS tarif_id_parent
   FROM ( SELECT a.tindakan_id,
            a.nama_tindakan,
            b.tindakan_detail_id,
            b.nama_tindakan_detail,
            c.tarif_id,
                CASE
                    WHEN (( SELECT tindakan.tindakan_id
                       FROM tindakan
                      WHERE ((tindakan.tindakan_id < a.tindakan_id) AND ((tindakan.nama_tindakan)::text = (a.nama_tindakan)::text))
                     LIMIT 1) IS NOT NULL) THEN 'child'::text
                    ELSE 'parent'::text
                END AS flag
           FROM ((tindakan a
             LEFT JOIN tindakan_detail b ON ((a.tindakan_id = b.tindakan_id)))
             LEFT JOIN tarif c ON ((c.tindakan_detail_id = b.tindakan_detail_id)))
          WHERE ((a.nama_tindakan)::text = ANY (ARRAY[('EEG'::character varying)::text, ('Angkat Jahitan ( 1 - 5 jahitan ) (dr. Spesialis )'::character varying)::text, ('EKG'::character varying)::text]))) z;");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS \"erwin_view\"");
    }
};
