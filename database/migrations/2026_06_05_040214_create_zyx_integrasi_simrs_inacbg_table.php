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
        Schema::create('zyx_integrasi_simrs_inacbg', function (Blueprint $table) {
            $table->increments('zyx_integrasi_simrs_inacbg_id');
            $table->integer('integrasi_simrs_inacbg_id');
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->string('sep', 20)->nullable();
            $table->string('cbg', 200)->nullable();
            $table->string('cbg_name', 200)->nullable();
            $table->decimal('obat', 18)->nullable();
            $table->decimal('bhp', 18)->nullable();
            $table->decimal('makanan', 18)->nullable();
            $table->decimal('bhp_lab', 18)->nullable();
            $table->decimal('bhp_rad', 18)->nullable();
            $table->decimal('bhp_fis', 18)->nullable();
            $table->decimal('kantung_darah', 18)->nullable();
            $table->decimal('total_tarif_inacbg', 18)->nullable();
            $table->string('kemenkes_dc_status_cd', 20)->nullable();
            $table->string('klaim_status_cd', 20)->nullable();
            $table->json('json_data')->nullable();
            $table->json('last_request_claim')->nullable();
            $table->decimal('total_tarif_rawat_covid', 18)->nullable();
            $table->decimal('total_tarif_pemulasaran_covid', 18)->nullable();
            $table->integer('registrasi_id_head')->nullable();
            $table->string('jenis_rawat', 20)->nullable();
            $table->timestamp('tgl_masuk')->nullable();
            $table->timestamp('tgl_keluar')->nullable();
            $table->timestamp('mod_change', 6)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zyx_integrasi_simrs_inacbg');
    }
};
