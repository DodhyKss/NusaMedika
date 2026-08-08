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
        DB::statement("CREATE OR REPLACE VIEW \"response_inacbg_view\" AS SELECT (((json_data -> 'response'::text) -> 'data'::text) ->> 'nomor_sep'::text) AS nomor_sep,
    ((((((json_data -> 'response'::text) -> 'data'::text) -> 'grouper'::text) -> 'response'::text) -> 'cbg'::text) ->> 'code'::text) AS cbg_code,
    ((((((json_data -> 'response'::text) -> 'data'::text) -> 'grouper'::text) -> 'response'::text) -> 'cbg'::text) ->> 'description'::text) AS cbg_name,
    (((json_data -> 'response'::text) -> 'data'::text) ->> 'kemenkes_dc_status_cd'::text) AS kemenkes_dc_status_cd,
    (((json_data -> 'response'::text) -> 'data'::text) ->> 'kemenkes_dc_sent_dttm'::text) AS kemenkes_dc_sent_dttm,
    (((json_data -> 'response'::text) -> 'data'::text) ->> 'bpjs_dc_status_cd'::text) AS bpjs_dc_status_cd,
    (((json_data -> 'response'::text) -> 'data'::text) ->> 'bpjs_dc_sent_dttm'::text) AS bpjs_dc_sent_dttm,
    (((json_data -> 'response'::text) -> 'data'::text) ->> 'klaim_status_cd'::text) AS klaim_status_cd,
    (((json_data -> 'response'::text) -> 'data'::text) ->> 'bpjs_klaim_status_cd'::text) AS bpjs_klaim_status_cd,
    (((json_data -> 'response'::text) -> 'data'::text) ->> 'bpjs_klaim_status_nm'::text) AS bpjs_klaim_status_nm,
    (((json_data -> 'response'::text) -> 'data'::text) ->> 'coder_nm'::text) AS coder_nm
   FROM integrasi_simrs_inacbg;");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS \"response_inacbg_view\"");
    }
};
