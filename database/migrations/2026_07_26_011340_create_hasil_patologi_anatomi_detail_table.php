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
        Schema::create('hasil_patologi_anatomi_detail', function (Blueprint $table) {
            $table->integer('hasil_patologi_anatomi_detail_id');
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('hasil_patologi_anatomi_id')->nullable();
            $table->integer('tindakan_id')->nullable();
            $table->integer('tindakan_group_id')->nullable();
            $table->string('deskripsi_tahap_1', 20000)->nullable();
            $table->integer('user_deskripsi_id')->nullable();
            $table->string('deskripsi_hasil', 20000)->nullable();
            $table->string('kesimpulan', 20000)->nullable();
            $table->string('saran', 20000)->nullable();
            $table->integer('user_hasil_id')->nullable();
            $table->integer('flag_hasil')->nullable();
            $table->integer('flag_abnormal')->nullable();
            $table->integer('pegawai_id')->nullable();
            $table->json('content')->nullable();
            $table->string('nomor_pa')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hasil_patologi_anatomi_detail');
    }
};
