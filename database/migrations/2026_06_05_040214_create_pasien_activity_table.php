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
        Schema::create('pasien_activity', function (Blueprint $table) {
            $table->integer('pasien_activity_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('registrasi_id')->nullable()->index('pasien_activity_registrasi_id_idx');
            $table->integer('registrasi_detail_id')->nullable()->index('pasien_activity_registrasi_detail_id_idx');
            $table->string('tabel', 200)->nullable();
            $table->integer('identity_tabel_id')->nullable();
            $table->text('keterangan')->nullable();
            $table->integer('user_id')->nullable();

            $table->index(['pasien_activity_id', 'registrasi_id', 'registrasi_detail_id', 'identity_tabel_id', 'user_id'], 'pasien_activity_pasien_activity_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pasien_activity');
    }
};
