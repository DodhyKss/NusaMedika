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
        Schema::create('zyx_order_rehab_medik', function (Blueprint $table) {
            $table->increments('zyx_order_rehab_medik_id');
            $table->integer('order_rehab_medik_id');
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('registrasi_id')->nullable();
            $table->integer('diagnosa_primary')->nullable()->comment('berisi icd_id');
            $table->json('diagnosa_secondary')->nullable()->comment('berisi icd_id');
            $table->string('jenis_terapi', 5)->nullable();
            $table->integer('jumlah_tindakan')->nullable();
            $table->json('hari_terapi')->nullable();
            $table->json('tindakan')->nullable();
            $table->integer('flag_selesai')->nullable()->comment('NULL = belum selesai
1 = selesai');
            $table->timestamp('mod_change', 6)->nullable();
            $table->json('urutan')->nullable()->comment('berdasarkan urutan referensi_urut');
            $table->json('registrasi_id_ref')->nullable()->comment('referensi registrasi id pada table registrasi');
            $table->integer('bagian_id_asal')->nullable();
            $table->integer('bagian_id_slot')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zyx_order_rehab_medik');
    }
};
