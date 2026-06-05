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
        Schema::create('zyx_pasien_activity', function (Blueprint $table) {
            $table->increments('zyx_pasien_activity_id');
            $table->integer('pasien_activity_id');
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('registrasi_id')->nullable();
            $table->integer('registrasi_detail_id')->nullable();
            $table->string('tabel', 200)->nullable();
            $table->integer('identity_tabel_id')->nullable();
            $table->text('keterangan')->nullable();
            $table->integer('user_id')->nullable();
            $table->timestamp('mod_change', 6)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zyx_pasien_activity');
    }
};
