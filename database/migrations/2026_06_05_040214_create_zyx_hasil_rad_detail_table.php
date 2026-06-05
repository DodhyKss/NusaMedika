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
        Schema::create('zyx_hasil_rad_detail', function (Blueprint $table) {
            $table->increments('zyx_hasil_rad_detail_id');
            $table->integer('hasil_rad_detail_id');
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('hasil_rad_id')->nullable();
            $table->integer('tindakan_id')->nullable();
            $table->string('hasil_isian')->nullable();
            $table->integer('flag_abnormal')->nullable();
            $table->string('deskripsi', 15000)->nullable();
            $table->string('kesan', 10000)->nullable();
            $table->string('saran', 10000)->nullable();
            $table->smallInteger('flag_bridging')->nullable();
            $table->string('url_view')->nullable();
            $table->json('receive_json')->nullable();
            $table->string('nomor_bridging')->nullable();
            $table->timestamp('mod_change', 6)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zyx_hasil_rad_detail');
    }
};
