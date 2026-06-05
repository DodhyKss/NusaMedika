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
        Schema::create('order_mikrobiologi', function (Blueprint $table) {
            $table->integer('order_mikrobiologi_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('registrasi_detail_id')->nullable();
            $table->integer('pasien_id')->nullable();
            $table->integer('kirim_user_id')->nullable();
            $table->integer('bagian_id')->nullable();
            $table->string('jenis_rawat')->nullable();
            $table->smallInteger('flag_cito')->nullable();
            $table->smallInteger('status')->nullable();
            $table->timestamp('tgl_order_lab', 6)->nullable();
            $table->smallInteger('tindakan_luar')->nullable();
            $table->timestamp('tgl_checkin', 6)->nullable();
            $table->smallInteger('flag_checkin')->nullable();
            $table->text('diagnosa')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_mikrobiologi');
    }
};
