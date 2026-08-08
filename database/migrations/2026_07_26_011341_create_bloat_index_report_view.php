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
        DB::statement("CREATE OR REPLACE VIEW \"bloat_index_report\" AS WITH index_info AS (
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
        )
 SELECT schema_name,
    table_name,
    index_name,
    pg_size_pretty(index_size_bytes) AS index_size,
    round(
        CASE
            WHEN ((num_pages = 0) OR (num_rows = 0)) THEN (0)::numeric
            ELSE ((1)::numeric - (((num_rows)::numeric * 8.0) / ((num_pages * (current_setting('block_size'::text))::integer))::numeric))
        END, 3) AS bloat_ratio
   FROM index_info
  WHERE (index_size_bytes > ((500 * 1024) * 1024))
  ORDER BY (round(
        CASE
            WHEN ((num_pages = 0) OR (num_rows = 0)) THEN (0)::numeric
            ELSE ((1)::numeric - (((num_rows)::numeric * 8.0) / ((num_pages * (current_setting('block_size'::text))::integer))::numeric))
        END, 3)) DESC, index_size_bytes DESC;");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS \"bloat_index_report\"");
    }
};
