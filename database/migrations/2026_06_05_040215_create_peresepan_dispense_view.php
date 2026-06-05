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
        DB::statement("CREATE VIEW \"peresepan_dispense\" AS SELECT DISTINCT peresepan_obat_dispense.barang_id,
    barang.nama_barang,
    peresepan_obat.registrasi_detail_id,
    peresepan_obat_dispense.peresepan_obat_detail_id,
        CASE
            WHEN (peresepan_obat_detail.obat_racikan IS NOT NULL) THEN peresepan_obat_detail.flag_stop
            ELSE (peresepan_obat_dispense.flag_stop)::integer
        END AS flag_stop,
        CASE
            WHEN (peresepan_obat_detail.obat_racikan IS NOT NULL) THEN peresepan_obat_detail.flag_stop_time
            ELSE peresepan_obat_dispense.flag_stop_time
        END AS flag_stop_time,
        CASE
            WHEN (peresepan_obat_detail.obat_racikan IS NOT NULL) THEN peresepan_obat_detail.stop_user_id
            ELSE (peresepan_obat_dispense.stop_user_id)::integer
        END AS stop_user_id,
    peresepan_obat_detail.peresepan_obat_id,
    barang.barang_jenis_id,
    peresepan_obat_dispense.sigma_1,
    peresepan_obat_dispense.sigma_2,
    peresepan_obat_detail.obat_racikan,
    peresepan_obat_detail.input_time,
    peresepan_obat_detail.input_user_id
   FROM (((peresepan_obat
     JOIN peresepan_obat_detail ON ((peresepan_obat.peresepan_obat_id = peresepan_obat_detail.peresepan_obat_id)))
     JOIN peresepan_obat_dispense ON ((peresepan_obat_detail.peresepan_obat_detail_id = peresepan_obat_dispense.peresepan_obat_detail_id)))
     JOIN barang ON ((peresepan_obat_dispense.barang_id = barang.barang_id)));");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS \"peresepan_dispense\"");
    }
};
