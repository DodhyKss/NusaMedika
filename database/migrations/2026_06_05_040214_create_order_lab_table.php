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
        Schema::create('order_lab', function (Blueprint $table) {
            $table->integer('order_lab_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('registrasi_detail_id')->nullable();
            $table->integer('pasien_id')->nullable();
            $table->integer('kirim_user_id')->nullable();
            $table->integer('bagian_id')->nullable();
            $table->string('jenis_rawat', 3)->nullable();
            $table->smallInteger('flag_cito')->nullable();
            $table->smallInteger('status')->nullable();
            $table->timestamp('tgl_order_lab', 6)->nullable();
            $table->text('deskripsi')->nullable();
            $table->text('kesan')->nullable();
            $table->string('version_order', 10)->nullable();
            $table->text('diagnosa')->nullable();
            $table->integer('tindakan_luar')->nullable();
            $table->string('nama_file_tind_luar')->nullable();
            $table->integer('flag_bridging')->nullable();
            $table->timestamp('input_time_bridging', 6)->nullable();
            $table->integer('user_id_bridging')->nullable();
            $table->string('id_bridging', 100)->nullable();
            $table->timestamp('tgl_checkin', 6)->nullable();
            $table->smallInteger('flag_checkin')->nullable();
            $table->integer('user_id_checkin')->nullable();
            $table->integer('flag_glukometer')->nullable();
            $table->smallInteger('flag_manual')->nullable()->comment('1 = order dokter2 = setelah hasil oleh perawat');
            $table->integer('manual_user_id')->nullable();
            $table->integer('flag_lis')->nullable();

            $table->index(['order_lab_id', 'registrasi_detail_id', 'pasien_id', 'kirim_user_id', 'bagian_id'], 'order_lab_order_lab_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_lab');
    }
};
