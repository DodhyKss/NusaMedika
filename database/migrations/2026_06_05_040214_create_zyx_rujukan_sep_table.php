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
        Schema::create('zyx_rujukan_sep', function (Blueprint $table) {
            $table->increments('zyx_rujukan_sep_id');
            $table->integer('rujukan_sep_id');
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('registrasi_id')->nullable();
            $table->string('sep', 20)->nullable();
            $table->string('no_rujukan', 20)->nullable();
            $table->string('faskes_rujukan', 1)->nullable();
            $table->string('appr_code_2', 25)->nullable();
            $table->string('appr_code_3', 25)->nullable();
            $table->integer('cetakan_ke')->nullable();
            $table->timestamp('tgl_cetakan')->nullable();
            $table->smallInteger('flag_surat_kontrol')->nullable();
            $table->timestamp('mod_change', 6)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zyx_rujukan_sep');
    }
};
