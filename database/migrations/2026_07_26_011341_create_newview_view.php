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
        DB::statement("CREATE OR REPLACE VIEW \"newview\" AS SELECT (arr.item_object ->> 'SEP'::text) AS sep,
    (arr.item_object ->> 'nilai_klaim'::text) AS nilai_klaim,
    (arr.item_object ->> 'klaim_dibayar'::text) AS klaim_dibayar,
    'SEP'::text AS alias_
   FROM upload_fpk,
    (LATERAL jsonb_array_elements((upload_fpk.data_upload)::jsonb) WITH ORDINALITY arr(item_object, index)
     LEFT JOIN rujukan_sep ON (((arr.item_object ->> 'SEP'::text) = (rujukan_sep.sep)::text)))
  WHERE ((upload_fpk.status_batal IS NULL) AND ((arr.item_object ->> 'SEP'::text) IS NOT NULL) AND (rujukan_sep.status_batal IS NULL))
  GROUP BY (arr.item_object ->> 'SEP'::text), (arr.item_object ->> 'nilai_klaim'::text), (arr.item_object ->> 'klaim_dibayar'::text), upload_fpk.jenis_rawat
UNION
 SELECT (arr.item_object ->> 'CCN'::text) AS sep,
    (arr.item_object ->> 'nilai_klaim'::text) AS nilai_klaim,
    (arr.item_object ->> 'klaim_dibayar'::text) AS klaim_dibayar,
    'CCN'::text AS alias_
   FROM upload_fpk,
    (LATERAL jsonb_array_elements((upload_fpk.data_upload)::jsonb) WITH ORDINALITY arr(item_object, index)
     LEFT JOIN covid_claim ON (((arr.item_object ->> 'CCN'::text) = (covid_claim.covid_claim_number)::text)))
  WHERE ((upload_fpk.status_batal IS NULL) AND ((arr.item_object ->> 'CCN'::text) IS NOT NULL) AND (covid_claim.status_batal IS NULL))
  GROUP BY (arr.item_object ->> 'CCN'::text), (arr.item_object ->> 'nilai_klaim'::text), (arr.item_object ->> 'klaim_dibayar'::text);");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS \"newview\"");
    }
};
