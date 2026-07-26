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
        DB::statement("CREATE VIEW \"perbaikan_harga_jual\" AS SELECT DISTINCT harga_jual_obat_id,
    barang_id,
    tgl_expired,
    nomor_batch,
    harga_jual,
    harga_jual_baru
   FROM ( SELECT harga_jual_obat.harga_jual_obat_id,
            harga_jual_obat.barang_id,
            harga_jual_obat.tgl_expired,
            harga_jual_obat.nomor_batch,
            harga_jual_obat.harga_jual,
            round(((harga_jual_obat.harga_beli + (pemesanan_brg_detail.ppn / penerimaan_brg_detail.total_terima_pakai)) * 1.31), 2) AS harga_jual_baru
           FROM (((((penerimaan_brg
             JOIN pemesanan_brg ON ((penerimaan_brg.pemesanan_brg_id = pemesanan_brg.pemesanan_brg_id)))
             JOIN penerimaan_brg_detail ON ((penerimaan_brg.penerimaan_brg_id = penerimaan_brg_detail.penerimaan_brg_id)))
             JOIN pemesanan_brg_detail ON (((pemesanan_brg_detail.barang_id = penerimaan_brg_detail.barang_id) AND (pemesanan_brg_detail.pemesanan_brg_id = pemesanan_brg.pemesanan_brg_id))))
             JOIN barang ON ((barang.barang_id = penerimaan_brg_detail.barang_id)))
             JOIN harga_jual_obat ON ((harga_jual_obat.penerimaan_brg_detail_id = penerimaan_brg_detail.penerimaan_brg_detail_id)))) bbb;");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS \"perbaikan_harga_jual\"");
    }
};
