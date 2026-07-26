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
        DB::statement("CREATE VIEW \"pasien_masuk_view\" AS SELECT table_two.pasien_id,
    table_two.registrasi_id,
    table_two.pasien_nasabah_id,
    table_two.registrasi_detail_id,
    table_two.bed_log_id_x,
    table_two.emr_id,
    bed_log.bed_id,
    bagian.nama_bagian,
    kelas_ruang.nama_kelas_ruang,
    bed.nama_bed,
    bed.no_kamar,
    nasabah.nama_nasabah,
    pasien.no_mr
   FROM (((((((( SELECT table_one.pasien_id,
            table_one.registrasi_id,
            table_one.pasien_nasabah_id,
            table_one.registrasi_detail_id,
            ( SELECT bed_log_1.bed_id
                   FROM bed_log bed_log_1
                  WHERE ((bed_log_1.registrasi_detail_id = table_one.registrasi_detail_id) AND (bed_log_1.status_bed_log = 3))
                  ORDER BY bed_log_1.bed_log_id
                 LIMIT 1) AS bed_log_id_x,
            ( SELECT emr.emr_id
                   FROM (emr
                     JOIN registrasi_detail ON ((registrasi_detail.registrasi_detail_id = emr.registrasi_detail_id)))
                  WHERE ((registrasi_detail.registrasi_id = table_one.registrasi_id) AND (emr.pasien_id = table_one.pasien_id))
                 LIMIT 1) AS emr_id
           FROM ( SELECT registrasi.pasien_id,
                    registrasi.registrasi_id,
                    registrasi.pasien_nasabah_id,
                    ( SELECT registrasi_detail.registrasi_detail_id
                           FROM (registrasi_detail
                             JOIN bagian bagian_1 ON (((registrasi_detail.bagian_id = bagian_1.bagian_id) AND (bagian_1.referensi_bagian = 2))))
                          WHERE (registrasi_detail.registrasi_id = registrasi.registrasi_id)
                          ORDER BY registrasi_detail.registrasi_detail_id
                         LIMIT 1) AS registrasi_detail_id
                   FROM registrasi
                  WHERE ((registrasi.status_batal IS NULL) AND ((registrasi.jenis_rawat)::text = 'RI'::text) AND (registrasi.pasien_id = ANY (ARRAY[857473, 857435, 1000021, 451343, 519518, 853196, 762629, 1000016, 820386, 857372, 770733, 766260, 852528, 553119, 494393, 854233, 805043, 828503, 857474, 593978, 817909, 732310, 811927, 664472, 1000028, 653396, 646840, 1000004, 733455, 670358, 164372, 367550, 849588, 1000009, 1000010, 1000011, 808799, 857330, 749699, 94417, 1000013, 1000012, 670325, 1000014, 1000015, 845155, 1000017, 1000019, 707117, 473472, 1000020, 660470, 857600, 611730, 27701, 564797, 1000026, 727183, 789213, 1000050, 1000049, 1000048, 851899, 1000046, 462125, 774440, 852557, 512971, 549437, 91798, 1000036, 437751, 732963, 288649, 788622, 1000030, 1000032, 49342, 1000034, 1000035, 503164, 487164, 847111, 722178, 479074, 1000038, 1000040, 564997, 1000041, 1000042, 857164, 1000043, 1000044, 798892, 815382, 1000047, 811386, 1000045, 1000052, 1000033])))) table_one) table_two
     LEFT JOIN bed_log ON ((bed_log.bed_log_id = table_two.bed_log_id_x)))
     LEFT JOIN bed ON ((bed.bed_id = bed_log.bed_id)))
     LEFT JOIN bagian ON ((bagian.bagian_id = bed.bagian_id)))
     LEFT JOIN kelas_ruang ON ((kelas_ruang.kelas_ruang_id = bed.kelas_id)))
     LEFT JOIN pasien_nasabah ON ((pasien_nasabah.pasien_nasabah_id = table_two.pasien_nasabah_id)))
     LEFT JOIN nasabah ON ((pasien_nasabah.nasabah_id = nasabah.nasabah_id)))
     LEFT JOIN pasien ON ((pasien.pasien_id = table_two.pasien_id)))
  WHERE (table_two.emr_id IS NOT NULL)
  ORDER BY kelas_ruang.nama_kelas_ruang;");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS \"pasien_masuk_view\"");
    }
};
