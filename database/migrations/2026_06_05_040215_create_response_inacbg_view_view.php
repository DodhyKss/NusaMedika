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
        DB::statement("CREATE VIEW \"response_inacbg_view\" AS SELECT (((integrasi_simrs_inacbg.json_data -> 'response'::text) -> 'data'::text) ->> 'nomor_sep'::text) AS nomor_sep,
    ((((((integrasi_simrs_inacbg.json_data -> 'response'::text) -> 'data'::text) -> 'grouper'::text) -> 'response'::text) -> 'cbg'::text) ->> 'code'::text) AS cbg_code,
    ((((((integrasi_simrs_inacbg.json_data -> 'response'::text) -> 'data'::text) -> 'grouper'::text) -> 'response'::text) -> 'cbg'::text) ->> 'description'::text) AS cbg_name,
    (((integrasi_simrs_inacbg.json_data -> 'response'::text) -> 'data'::text) ->> 'kemenkes_dc_status_cd'::text) AS kemenkes_dc_status_cd,
    (((integrasi_simrs_inacbg.json_data -> 'response'::text) -> 'data'::text) ->> 'kemenkes_dc_sent_dttm'::text) AS kemenkes_dc_sent_dttm,
    (((integrasi_simrs_inacbg.json_data -> 'response'::text) -> 'data'::text) ->> 'bpjs_dc_status_cd'::text) AS bpjs_dc_status_cd,
    (((integrasi_simrs_inacbg.json_data -> 'response'::text) -> 'data'::text) ->> 'bpjs_dc_sent_dttm'::text) AS bpjs_dc_sent_dttm,
    (((integrasi_simrs_inacbg.json_data -> 'response'::text) -> 'data'::text) ->> 'klaim_status_cd'::text) AS klaim_status_cd,
    (((integrasi_simrs_inacbg.json_data -> 'response'::text) -> 'data'::text) ->> 'bpjs_klaim_status_cd'::text) AS bpjs_klaim_status_cd,
    (((integrasi_simrs_inacbg.json_data -> 'response'::text) -> 'data'::text) ->> 'bpjs_klaim_status_nm'::text) AS bpjs_klaim_status_nm,
    (((integrasi_simrs_inacbg.json_data -> 'response'::text) -> 'data'::text) ->> 'coder_nm'::text) AS coder_nm
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
