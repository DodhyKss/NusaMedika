<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('peresepan_obat', function (Blueprint $table) {
            $table->integer('peresepan_obat_id');
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->timestamp('tgl_resep', 6)->nullable();
            $table->integer('user_id')->nullable();
            $table->integer('pasien_id')->nullable();
            $table->integer('registrasi_detail_id')->nullable();
            $table->smallInteger('flag_cito')->nullable()->comment('0 = Biasa
1 = Cito
2 = Terapi baru
3 = Trolly emergency
4 = Persiapan pulang
5 = Floor stock');
            $table->smallInteger('status_selesai')->nullable()->comment('1 = resep sedang diproses
2 = resep selesai
3 = resep di-dispense sebagian
4 = resep dibatalkan');
            $table->smallInteger('peresepan_obat_jenis')->nullable();
            $table->string('nik', 20)->nullable();
            $table->string('nama_pembeli')->nullable();
            $table->integer('pegawai_id')->nullable();
            $table->string('nama_dokter_pj')->nullable();
            $table->text('sebelum_konfirmasi')->nullable();
            $table->text('setelah_konfirmasi')->nullable();
            $table->integer('urutan_resep')->nullable();
            $table->string('kode_resep', 100)->nullable();
            $table->timestamp('start_tracking', 6)->nullable();
            $table->integer('user_id_start')->nullable();
            $table->timestamp('end_tracking', 6)->nullable();
            $table->integer('user_id_end')->nullable();
            $table->smallInteger('flag_resep_manual')->nullable();
            $table->text('alamat_pembeli')->nullable();
            $table->string('no_telepon_pembeli', 13)->nullable();
            $table->string('keterangan_batal')->nullable();
            $table->integer('flagging_resep')->nullable()->comment('1 = obat akut
2 = kronis
3 = kemo
4 = alkes capd
5 = insulin
6 = inhaler
7 = racikan');
            $table->integer('resep_iter')->nullable();
            $table->integer('referensi_peresepan_obat_id')->nullable();
            $table->smallInteger('jenis_resep_obat')->nullable();
            $table->string('no_resep', 5)->nullable();
            $table->smallInteger('iterasi')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peresepan_obat');
    }
};
