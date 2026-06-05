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
        DB::statement("CREATE VIEW \"bloat_table_report\" AS WITH table_stats AS (
         SELECT n.nspname AS schema_name,
            c.relname AS table_name,
            (c.reltuples)::bigint AS est_rows,
            (c.relpages)::bigint AS pages,
            pg_relation_size((c.oid)::regclass) AS table_size_bytes,
            c.oid AS table_oid
           FROM (pg_class c
             JOIN pg_namespace n ON ((n.oid = c.relnamespace)))
          WHERE ((c.relkind = 'r'::\"char\") AND (n.nspname <> ALL (ARRAY['pg_catalog'::name, 'information_schema'::name])))
        ), bloat_calc AS (
         SELECT s.schema_name,
            s.table_name,
            s.est_rows,
            s.pages,
            s.table_size_bytes,
            s.bs,
            ceil((((((s.est_rows * ((s.datahdr + s.ma) -
                CASE
                    WHEN ((s.datahdr % s.ma) = 0) THEN s.ma
                    ELSE (s.datahdr % s.ma)
                END)) + s.nullhdr2) + 24))::double precision / ((s.bs - 20))::double precision)) AS est_pages
           FROM ( SELECT ts.schema_name,
                    ts.table_name,
                    ts.est_rows,
                    ts.pages,
                    ts.table_size_bytes,
                    ts.table_oid,
                    (current_setting('block_size'::text))::integer AS bs,
                    24 AS datahdr,
                    8 AS ma,
                    24 AS nullhdr2
                   FROM table_stats ts) s
        )
 SELECT bloat_calc.schema_name,
    bloat_calc.table_name,
    pg_size_pretty(bloat_calc.table_size_bytes) AS table_size,
    bloat_calc.est_rows,
    bloat_calc.pages AS real_pages,
    bloat_calc.est_pages,
    round(
        CASE
            WHEN (((bloat_calc.pages)::double precision - bloat_calc.est_pages) > (0)::double precision) THEN ((((bloat_calc.pages)::double precision - bloat_calc.est_pages))::numeric / (NULLIF(bloat_calc.pages, 0))::numeric)
            ELSE (0)::numeric
        END, 3) AS bloat_ratio
   FROM bloat_calc
  WHERE ((bloat_calc.pages > 0) AND (((bloat_calc.pages)::double precision - bloat_calc.est_pages) > (0)::double precision))
  ORDER BY (round(
        CASE
            WHEN (((bloat_calc.pages)::double precision - bloat_calc.est_pages) > (0)::double precision) THEN ((((bloat_calc.pages)::double precision - bloat_calc.est_pages))::numeric / (NULLIF(bloat_calc.pages, 0))::numeric)
            ELSE (0)::numeric
        END, 3)) DESC, bloat_calc.table_size_bytes DESC;");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS \"bloat_table_report\"");
    }
};
