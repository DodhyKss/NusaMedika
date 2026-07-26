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
        Schema::create('notif_penyakit', function (Blueprint $table) {
            $table->integer('notif_penyakit_id');
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('order_lab_id')->nullable();
            $table->integer('pasien_id')->nullable();
            $table->integer('status_level')->nullable();
            $table->string('pemeriksaan', 100)->nullable();
            $table->string('nilai', 100)->nullable();
            $table->integer('start_user_id')->nullable();
            $table->timestamp('tgl_start', 6)->nullable();
            $table->integer('finish_user_id')->nullable();
            $table->timestamp('tgl_finish', 6)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notif_penyakit');
    }
};
