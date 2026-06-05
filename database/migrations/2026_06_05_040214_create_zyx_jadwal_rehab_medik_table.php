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
        Schema::create('zyx_jadwal_rehab_medik', function (Blueprint $table) {
            $table->increments('zyx_jadwal_rehab_medik_id');
            $table->integer('jadwal_rehab_medik_id');
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('bagian_id')->nullable();
            $table->time('jam_mulai')->nullable();
            $table->time('jam_akhir')->nullable();
            $table->timestamp('tanggal_slot', 6)->nullable();
            $table->string('jenis_terapi', 2)->nullable();
            $table->integer('kloter')->nullable();
            $table->integer('slot')->nullable();
            $table->timestamp('mod_change', 6)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zyx_jadwal_rehab_medik');
    }
};
