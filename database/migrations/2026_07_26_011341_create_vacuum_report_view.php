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
        DB::statement("CREATE OR REPLACE VIEW \"vacuum_report\" AS WITH table_stats AS (
         SELECT pg_stat_user_tables.schemaname,
            pg_stat_user_tables.relname,
            pg_stat_user_tables.n_live_tup,
            pg_stat_user_tables.n_dead_tup,
                CASE
                    WHEN (pg_stat_user_tables.n_live_tup = 0) THEN (0)::numeric
                    ELSE round((((pg_stat_user_tables.n_dead_tup)::numeric / (pg_stat_user_tables.n_live_tup)::numeric) * (100)::numeric), 2)
                END AS dead_pct,
            pg_size_pretty(pg_total_relation_size((pg_stat_user_tables.relid)::regclass)) AS total_size,
            pg_size_pretty(pg_relation_size((pg_stat_user_tables.relid)::regclass)) AS table_size,
            pg_size_pretty((pg_total_relation_size((pg_stat_user_tables.relid)::regclass) - pg_relation_size((pg_stat_user_tables.relid)::regclass))) AS index_size
           FROM pg_stat_user_tables
        )
 SELECT (((schemaname)::text || '.'::text) || (relname)::text) AS tbl_name,
    n_live_tup AS live_rows,
    n_dead_tup AS dead_rows,
    dead_pct,
    total_size,
    table_size,
    index_size,
        CASE
            WHEN (dead_pct > (20)::numeric) THEN format('VACUUM FULL ANALYZE \"%I\".\"%I\";'::text, schemaname, relname)
            WHEN (dead_pct > (5)::numeric) THEN format('VACUUM (ANALYZE) \"%I\".\"%I\";'::text, schemaname, relname)
            ELSE format('-- Healthy: no action for \"%I\".\"%I\"'::text, schemaname, relname)
        END AS maintenance_sql
   FROM table_stats
  ORDER BY dead_pct DESC;");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS \"vacuum_report\"");
    }
};
