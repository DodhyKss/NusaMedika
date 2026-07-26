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
        DB::statement("CREATE VIEW \"view_bill_group_detail\" AS SELECT kategori_inacbg,
    bill_temp_id,
    input_time,
    input_user_id,
    nama_bagian,
    jenis_tindakan,
    tindakan_id,
    jenis_tindakan_id,
    nama_tindakan,
    barang_golongan_detail_id,
    jumlah,
    tgl_tindakan,
    nama_tindakan_detail,
    nama_pegawai,
    biaya
   FROM ( SELECT DISTINCT
                CASE
                    WHEN (bagian.bagian_id = 139) THEN 'REHABILITASI'::text
                    ELSE
                    CASE
                        WHEN (jenis_tindakan.jenis_tindakan_id = 2) THEN
                        CASE
                            WHEN (barang_jenis.barang_jenis_id = 3) THEN
                            CASE
                                WHEN (barang_golongan_detail.barang_golongan_detail_id IS NULL) THEN
                                CASE
                                    WHEN (peresepan_obat_dispense.flag_kronis IS NOT NULL) THEN 'OBAT KRONIS'::text
                                    ELSE 'OBAT'::text
                                END
                                ELSE 'OBAT KEMOTERAPI'::text
                            END
                            ELSE 'ALKES'::text
                        END
                        WHEN (jenis_tindakan.jenis_tindakan_id = ANY (ARRAY[3, 7])) THEN 'BMHP'::text
                        WHEN (jenis_tindakan.jenis_tindakan_id = 12) THEN 'SEWA ALAT'::text
                        WHEN (jenis_tindakan.jenis_tindakan_id = ANY (ARRAY[4, 8])) THEN 'KAMAR & AKOMODASI'::text
                        WHEN (jenis_tindakan.jenis_tindakan_id = 9) THEN 'PELAYANAN DARAH'::text
                        WHEN ((jenis_tindakan.jenis_tindakan_id = ANY (ARRAY[1, 14])) AND (bagian.referensi_bagian <> ALL (ARRAY[10, 11])) AND (bagian.bagian_id <> 139)) THEN
                        CASE
                            WHEN (bagian.bagian_id = 64) THEN 'PROSEDUR BEDAH'::text
                            ELSE 'PROSEDUR NON BEDAH'::text
                        END
                        ELSE
                        CASE
                            WHEN (bagian.referensi_bagian = 10) THEN 'LABORATORIUM'::text
                            WHEN (bagian.referensi_bagian = 11) THEN 'RADIOLOGI'::text
                            ELSE 'UNKNOWN'::text
                        END
                    END
                END AS kategori_inacbg,
            bill_temp.bill_temp_id,
            bill_temp_detail.input_time,
            bill_temp_detail.input_user_id,
            bagian.nama_bagian,
            jenis_tindakan.jenis_tindakan,
            tindakan.tindakan_id,
            tindakan.jenis_tindakan_id,
                CASE
                    WHEN (jenis_tindakan.jenis_tindakan_id = ANY (ARRAY[2, 3])) THEN barang.nama_barang
                    ELSE tindakan.nama_tindakan
                END AS nama_tindakan,
            barang_golongan_detail.barang_golongan_detail_id,
            bill_temp_detail.jumlah,
            bill_temp_detail.tgl_tindakan,
            tindakan_detail.nama_tindakan_detail,
                CASE
                    WHEN (bagian.referensi_bagian = ANY (ARRAY[10, 11])) THEN NULL::character varying
                    ELSE pegawai.nama_pegawai
                END AS nama_pegawai,
            bill_temp_detail.biaya
           FROM (((((((((((((registrasi
             JOIN registrasi_detail ON ((registrasi.registrasi_id = registrasi_detail.registrasi_id)))
             JOIN bagian ON ((registrasi_detail.bagian_id = bagian.bagian_id)))
             JOIN bill_temp ON ((bill_temp.registrasi_detail_id = registrasi_detail.registrasi_detail_id)))
             JOIN bill_temp_detail ON ((bill_temp.bill_temp_id = bill_temp_detail.bill_temp_id)))
             JOIN jenis_tindakan ON ((bill_temp_detail.jenis_tindakan_id = jenis_tindakan.jenis_tindakan_id)))
             LEFT JOIN tindakan ON ((tindakan.tindakan_id = bill_temp_detail.tindakan_id)))
             LEFT JOIN barang ON ((barang.barang_id = bill_temp_detail.tindakan_id)))
             LEFT JOIN tarif ON ((tarif.tarif_id = bill_temp_detail.tarif_id)))
             LEFT JOIN tindakan_detail ON ((tindakan_detail.tindakan_detail_id = tarif.tindakan_detail_id)))
             LEFT JOIN pegawai ON ((bill_temp_detail.pegawai_id = pegawai.pegawai_id)))
             LEFT JOIN barang_golongan_detail ON (((barang_golongan_detail.barang_id = barang.barang_id) AND (barang_golongan_detail.barang_golongan_id = ANY (ARRAY[85, 86])))))
             LEFT JOIN peresepan_obat_dispense ON ((peresepan_obat_dispense.peresepan_obat_dispense_id = bill_temp_detail.peresepan_obat_dispense_id)))
             LEFT JOIN barang_jenis ON ((barang.barang_jenis_id = barang_jenis.barang_jenis_id)))
          WHERE ((registrasi.registrasi_id = 67127) AND (registrasi_detail.bagian_id = ANY (ARRAY[91, 83, 65])))) level_one
  ORDER BY
        CASE
            WHEN (tgl_tindakan IS NULL) THEN input_time
            ELSE tgl_tindakan
        END;");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS \"view_bill_group_detail\"");
    }
};
