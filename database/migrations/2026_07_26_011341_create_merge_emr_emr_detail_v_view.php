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
        DB::statement("CREATE OR REPLACE VIEW \"merge_emr_emr_detail_v\" AS SELECT emr_id,
    pasien_id,
    registrasi_id,
    registrasi_detail_id,
    form_id,
    objek_id,
    variabel,
    value,
    flag_abnormal,
    data
   FROM ( SELECT emr.emr_id,
            emr.pasien_id,
            emr.registrasi_id,
            emr.registrasi_detail_id,
            emr.form_id,
            emr.data,
            json_object_keys((emr.data -> 'emr_detail'::text)) AS objek_id,
            json_object_keys(((emr.data -> 'emr_detail'::text) -> json_object_keys((emr.data -> 'emr_detail'::text)))) AS variabel,
            ((((emr.data -> 'emr_detail'::text) -> json_object_keys((emr.data -> 'emr_detail'::text))) -> json_object_keys(((emr.data -> 'emr_detail'::text) -> json_object_keys((emr.data -> 'emr_detail'::text))))) ->> 'value'::text) AS value,
            ((((emr.data -> 'emr_detail'::text) -> json_object_keys((emr.data -> 'emr_detail'::text))) -> json_object_keys(((emr.data -> 'emr_detail'::text) -> json_object_keys((emr.data -> 'emr_detail'::text))))) ->> 'flag_abnormal'::text) AS flag_abnormal
           FROM emr) merge_table;");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS \"merge_emr_emr_detail_v\"");
    }
};
