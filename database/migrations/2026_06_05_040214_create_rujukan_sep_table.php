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
        Schema::create('rujukan_sep', function (Blueprint $table) {
            $table->integer('rujukan_sep_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('registrasi_id')->nullable()->index('rujukan_sep_registrasi_id_idx');
            $table->string('sep', 20)->nullable()->index('rujukan_sep_sep_idx');
            $table->string('no_rujukan', 20)->nullable()->index('rujukan_sep_no_rujukan_idx');
            $table->string('faskes_rujukan', 1)->nullable();
            $table->string('appr_code_2', 25)->nullable();
            $table->string('appr_code_3', 25)->nullable();
            $table->integer('cetakan_ke')->nullable();
            $table->timestamp('tgl_cetakan')->nullable();
            $table->smallInteger('flag_surat_kontrol')->nullable();

            $table->index(['sep', 'no_rujukan'], 'idx_rujukan_sep01');
            $table->index(['rujukan_sep_id', 'registrasi_id', 'no_rujukan', 'sep', 'tgl_cetakan'], 'rujukan_sep_rujukan_sep_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rujukan_sep');
    }
};
