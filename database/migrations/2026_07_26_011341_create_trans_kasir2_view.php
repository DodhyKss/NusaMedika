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
        DB::statement("CREATE OR REPLACE VIEW \"trans_kasir2\" AS SELECT bill_kasir.pasien_id,
    registrasi.registrasi_id,
    bill_kasir.bill_kasir_id,
    bill_kasir.bagian_id,
    kuitansi.kuitansi_id,
    kuitansi.kuitansi_tipe,
    kuitansi.kuitansi_no,
    kuitansi.tipe_bayar_id,
    kuitansi.total_tagihan,
    kuitansi.input_user_id,
    nasabah.jenis_nasabah,
    (kuitansi.tanggal_kuitansi)::date AS tanggal_kuitansi,
    bill_kasir.nasabah_id,
    kuitansi.kuitansi_code,
        CASE
            WHEN ((nasabah.jenis_nasabah)::text = 'PRI'::text) THEN 'Bukan nk'::text
            ELSE 'nk'::text
        END AS status
   FROM ((((bill_kasir
     JOIN registrasi_detail ON ((registrasi_detail.registrasi_detail_id = bill_kasir.registrasi_detail_id)))
     JOIN registrasi ON ((registrasi_detail.registrasi_id = registrasi.registrasi_id)))
     JOIN kuitansi ON ((registrasi.registrasi_id = kuitansi.registrasi_id)))
     JOIN nasabah ON ((nasabah.nasabah_id = bill_kasir.nasabah_id)));");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS \"trans_kasir2\"");
    }
};
