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
        Schema::create('hasil_mikrobiologi', function (Blueprint $table) {
            $table->integer('hasil_mikrobiologi_id');
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('registrasi_detail_id')->nullable();
            $table->integer('pasien_id')->nullable();
            $table->integer('order_mikrobiologi_detail_id')->nullable();
            $table->timestamp('tgl_hasil', 6)->nullable();
            $table->timestamp('tgl_hasil_dokter', 6)->nullable();
            $table->smallInteger('flag_selesai')->nullable();
            $table->smallInteger('flag_validasi')->nullable();
            $table->text('makroskopik')->nullable();
            $table->text('mikroskopik')->nullable();
            $table->text('kesimpulan')->nullable();
            $table->text('anjuran')->nullable();
            $table->integer('tindakan_id')->nullable();
            $table->integer('tindakan_group_id')->nullable();
            $table->string('nama_tindakan')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hasil_mikrobiologi');
    }
};
