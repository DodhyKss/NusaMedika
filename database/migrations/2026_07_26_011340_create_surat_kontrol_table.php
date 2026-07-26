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
        Schema::create('surat_kontrol', function (Blueprint $table) {
            $table->integer('surat_kontrol_id');
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('emr_id')->nullable();
            $table->string('sep_layanan', 20)->nullable();
            $table->string('sep_kontrol', 20)->nullable();
            $table->string('no_surat_kontrol', 32)->nullable();
            $table->integer('registrasi_id_layanan')->nullable();
            $table->integer('registrasi_id_kontrol')->nullable();
            $table->string('jenis_kontrol', 32)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat_kontrol');
    }
};
