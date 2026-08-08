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
        DB::statement("CREATE OR REPLACE VIEW \"golongan_obat\" AS SELECT barang_sub_golongan.barang_sub_golongan_id,
    barang_golongan_detail.barang_id,
    barang_sub_golongan.barang_golongan_id
   FROM ((barang_sub_golongan
     JOIN barang_golongan_detail ON ((barang_sub_golongan.barang_sub_golongan_id = barang_golongan_detail.barang_golongan_id)))
     LEFT JOIN barang_golongan ON (((barang_sub_golongan.barang_golongan_id = barang_golongan.barang_golongan_id) AND (barang_sub_golongan.barang_golongan_id = 2) AND (barang_sub_golongan.barang_golongan_id <> 4))));");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS \"golongan_obat\"");
    }
};
