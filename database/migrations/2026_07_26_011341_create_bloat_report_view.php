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
        DB::statement("CREATE OR REPLACE VIEW \"bloat_report\" AS WITH index_info AS (
         SELECT n.nspname AS schema_name,
            c.relname AS index_name,
            t.relname AS table_name,
            pg_relation_size((c.oid)::regclass) AS index_size_bytes,
            (c.reltuples)::bigint AS num_rows,
            (c.relpages)::bigint AS num_pages
           FROM (((pg_class c
             JOIN pg_namespace n ON ((n.oid = c.relnamespace)))
             JOIN pg_index i ON ((i.indexrelid = c.oid)))
             JOIN pg_class t ON ((t.oid = i.indrelid)))
          WHERE ((c.relkind = 'i'::\"char\") AND (n.nspname <> ALL (ARRAY['pg_catalog'::name, 'information_schema'::name])))
        ), index_bloat AS (
         SELECT 'INDEX'::text AS type,
            index_info.schema_name,
            index_info.table_name,
            index_info.index_name AS object_name,
            pg_size_pretty(index_info.index_size_bytes) AS object_size,
            round(
                CASE
                    WHEN ((index_info.num_pages = 0) OR (index_info.num_rows = 0)) THEN (0)::numeric
                    ELSE ((1)::numeric - (((index_info.num_rows)::numeric * 8.0) / ((index_info.num_pages * (current_setting('block_size'::text))::integer))::numeric))
                END, 3) AS bloat_ratio
           FROM index_info
          WHERE (index_info.index_size_bytes > ((500 * 1024) * 1024))
        ), table_stats AS (
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
        ), table_bloat AS (
         SELECT 'TABLE'::text AS type,
            bloat_calc.schema_name,
            bloat_calc.table_name,
            bloat_calc.table_name AS object_name,
            pg_size_pretty(bloat_calc.table_size_bytes) AS object_size,
            round(
                CASE
                    WHEN (((bloat_calc.pages)::double precision - bloat_calc.est_pages) > (0)::double precision) THEN ((((bloat_calc.pages)::double precision - bloat_calc.est_pages))::numeric / (NULLIF(bloat_calc.pages, 0))::numeric)
                    ELSE (0)::numeric
                END, 3) AS bloat_ratio
           FROM bloat_calc
          WHERE ((bloat_calc.pages > 0) AND (((bloat_calc.pages)::double precision - bloat_calc.est_pages) > (0)::double precision))
        )
 SELECT index_bloat.type,
    index_bloat.schema_name,
    index_bloat.table_name,
    index_bloat.object_name,
    index_bloat.object_size,
    index_bloat.bloat_ratio
   FROM index_bloat
UNION ALL
 SELECT table_bloat.type,
    table_bloat.schema_name,
    table_bloat.table_name,
    table_bloat.object_name,
    table_bloat.object_size,
    table_bloat.bloat_ratio
   FROM table_bloat;");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS \"bloat_report\"");
    }
};
