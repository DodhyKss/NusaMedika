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
        Schema::create('zyx_implant', function (Blueprint $table) {
            $table->increments('zyx_implant_id');
            $table->integer('implant_id');
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('pesan_slot_bedah_id')->nullable();
            $table->string('nomor_kartu', 100)->nullable();
            $table->string('implan_terpasang')->nullable();
            $table->string('lokasi_pemasangan', 100)->nullable();
            $table->string('kode_implant', 100)->nullable();
            $table->string('pencabutan_implant', 100)->nullable();
            $table->json('user_id_bedah')->nullable();
            $table->json('user_id_rr')->nullable();
            $table->json('user_id_ruangan')->nullable();
            $table->timestamp('mod_change', 6)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zyx_implant');
    }
};
