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
        DB::statement("CREATE VIEW \"merge_emr_emr_detail_v\" AS SELECT merge_table.emr_id,
    merge_table.pasien_id,
    merge_table.registrasi_id,
    merge_table.registrasi_detail_id,
    merge_table.form_id,
    merge_table.objek_id,
    merge_table.variabel,
    merge_table.value,
    merge_table.flag_abnormal,
    merge_table.data
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
